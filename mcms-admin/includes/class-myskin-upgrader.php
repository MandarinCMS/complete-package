<?php
/**
 * Upgrade API: MySkin_Upgrader class
 *
 * @package MandarinCMS
 * @subpackage Upgrader
 * @since 4.6.0
 */

/**
 * Core class used for upgrading/installing myskins.
 *
 * It is designed to upgrade/install myskins from a local zip, remote zip URL,
 * or uploaded zip file.
 *
 * @since 2.8.0
 * @since 4.6.0 Moved to its own file from mcms-admin/includes/class-mcms-upgrader.php.
 *
 * @see MCMS_Upgrader
 */
class MySkin_Upgrader extends MCMS_Upgrader {

	/**
	 * Result of the myskin upgrade offer.
	 *
	 * @since 2.8.0
	 * @var array|MCMS_Error $result
	 * @see MCMS_Upgrader::$result
	 */
	public $result;

	/**
	 * Whether multiple myskins are being upgraded/installed in bulk.
	 *
	 * @since 2.9.0
	 * @var bool $bulk
	 */
	public $bulk = false;

	/**
	 * Initialize the upgrade strings.
	 *
	 * @since 2.8.0
	 */
	public function upgrade_strings() {
		$this->strings['up_to_date'] = __('The myskin is at the latest version.');
		$this->strings['no_package'] = __('Update package not available.');
		/* translators: %s: package URL */
		$this->strings['downloading_package'] = sprintf( __( 'Downloading update from %s&#8230;' ), '<span class="code">%s</span>' );
		$this->strings['unpack_package'] = __('Unpacking the update&#8230;');
		$this->strings['remove_old'] = __('Removing the old version of the myskin&#8230;');
		$this->strings['remove_old_failed'] = __('Could not remove the old myskin.');
		$this->strings['process_failed'] = __('MySkin update failed.');
		$this->strings['process_success'] = __('MySkin updated successfully.');
	}

	/**
	 * Initialize the installation strings.
	 *
	 * @since 2.8.0
	 */
	public function install_strings() {
		$this->strings['no_package'] = __('Installation package not available.');
		/* translators: %s: package URL */
		$this->strings['downloading_package'] = sprintf( __( 'Downloading installation package from %s&#8230;' ), '<span class="code">%s</span>' );
		$this->strings['unpack_package'] = __('Unpacking the package&#8230;');
		$this->strings['installing_package'] = __('Installing the myskin&#8230;');
		$this->strings['no_files'] = __('The myskin contains no files.');
		$this->strings['process_failed'] = __('MySkin installation failed.');
		$this->strings['process_success'] = __('MySkin installed successfully.');
		/* translators: 1: myskin name, 2: version */
		$this->strings['process_success_specific'] = __('Successfully installed the myskin <strong>%1$s %2$s</strong>.');
		$this->strings['parent_myskin_search'] = __('This myskin requires a parent myskin. Checking if it is installed&#8230;');
		/* translators: 1: myskin name, 2: version */
		$this->strings['parent_myskin_prepare_install'] = __('Preparing to install <strong>%1$s %2$s</strong>&#8230;');
		/* translators: 1: myskin name, 2: version */
		$this->strings['parent_myskin_currently_installed'] = __('The parent myskin, <strong>%1$s %2$s</strong>, is currently installed.');
		/* translators: 1: myskin name, 2: version */
		$this->strings['parent_myskin_install_success'] = __('Successfully installed the parent myskin, <strong>%1$s %2$s</strong>.');
		/* translators: %s: myskin name */
		$this->strings['parent_myskin_not_found'] = sprintf( __( '<strong>The parent myskin could not be found.</strong> You will need to install the parent myskin, %s, before you can use this child myskin.' ), '<strong>%s</strong>' );
	}

	/**
	 * Check if a child myskin is being installed and we need to install its parent.
	 *
	 * Hooked to the {@see 'upgrader_post_install'} filter by MySkin_Upgrader::install().
	 *
	 * @since 3.4.0
	 *
	 * @param bool  $install_result
	 * @param array $hook_extra
	 * @param array $child_result
	 * @return type
	 */
	public function check_parent_myskin_filter( $install_result, $hook_extra, $child_result ) {
		// Check to see if we need to install a parent myskin
		$myskin_info = $this->myskin_info();

		if ( ! $myskin_info->parent() )
			return $install_result;

		$this->skin->feedback( 'parent_myskin_search' );

		if ( ! $myskin_info->parent()->errors() ) {
			$this->skin->feedback( 'parent_myskin_currently_installed', $myskin_info->parent()->display('Name'), $myskin_info->parent()->display('Version') );
			// We already have the myskin, fall through.
			return $install_result;
		}

		// We don't have the parent myskin, let's install it.
		$api = myskins_api('myskin_information', array('slug' => $myskin_info->get('Template'), 'fields' => array('sections' => false, 'tags' => false) ) ); //Save on a bit of bandwidth.

		if ( ! $api || is_mcms_error($api) ) {
			$this->skin->feedback( 'parent_myskin_not_found', $myskin_info->get('Template') );
			// Don't show activate or preview actions after installation
			add_filter('install_myskin_complete_actions', array($this, 'hide_activate_preview_actions') );
			return $install_result;
		}

		// Backup required data we're going to override:
		$child_api = $this->skin->api;
		$child_success_message = $this->strings['process_success'];

		// Override them
		$this->skin->api = $api;
		$this->strings['process_success_specific'] = $this->strings['parent_myskin_install_success'];//, $api->name, $api->version);

		$this->skin->feedback('parent_myskin_prepare_install', $api->name, $api->version);

		add_filter('install_myskin_complete_actions', '__return_false', 999); // Don't show any actions after installing the myskin.

		// Install the parent myskin
		$parent_result = $this->run( array(
			'package' => $api->download_link,
			'destination' => get_myskin_root(),
			'clear_destination' => false, //Do not overwrite files.
			'clear_working' => true
		) );

		if ( is_mcms_error($parent_result) )
			add_filter('install_myskin_complete_actions', array($this, 'hide_activate_preview_actions') );

		// Start cleaning up after the parents installation
		remove_filter('install_myskin_complete_actions', '__return_false', 999);

		// Reset child's result and data
		$this->result = $child_result;
		$this->skin->api = $child_api;
		$this->strings['process_success'] = $child_success_message;

		return $install_result;
	}

	/**
	 * Don't display the activate and preview actions to the user.
	 *
	 * Hooked to the {@see 'install_myskin_complete_actions'} filter by
	 * MySkin_Upgrader::check_parent_myskin_filter() when installing
	 * a child myskin and installing the parent myskin fails.
	 *
	 * @since 3.4.0
	 *
	 * @param array $actions Preview actions.
	 * @return array
	 */
	public function hide_activate_preview_actions( $actions ) {
		unset($actions['activate'], $actions['preview']);
		return $actions;
	}

	/**
	 * Install a myskin package.
	 *
	 * @since 2.8.0
	 * @since 3.7.0 The `$args` parameter was added, making clearing the update cache optional.
	 *
	 * @param string $package The full local path or URI of the package.
	 * @param array  $args {
	 *     Optional. Other arguments for installing a myskin package. Default empty array.
	 *
	 *     @type bool $clear_update_cache Whether to clear the updates cache if successful.
	 *                                    Default true.
	 * }
	 *
	 * @return bool|MCMS_Error True if the installation was successful, false or a MCMS_Error object otherwise.
	 */
	public function install( $package, $args = array() ) {

		$defaults = array(
			'clear_update_cache' => true,
		);
		$parsed_args = mcms_parse_args( $args, $defaults );

		$this->init();
		$this->install_strings();

		add_filter('upgrader_source_selection', array($this, 'check_package') );
		add_filter('upgrader_post_install', array($this, 'check_parent_myskin_filter'), 10, 3);
		if ( $parsed_args['clear_update_cache'] ) {
			// Clear cache so mcms_update_myskins() knows about the new myskin.
			add_action( 'upgrader_process_complete', 'mcms_clean_myskins_cache', 9, 0 );
		}

		$this->run( array(
			'package' => $package,
			'destination' => get_myskin_root(),
			'clear_destination' => false, //Do not overwrite files.
			'clear_working' => true,
			'hook_extra' => array(
				'type' => 'myskin',
				'action' => 'install',
			),
		) );

		remove_action( 'upgrader_process_complete', 'mcms_clean_myskins_cache', 9 );
		remove_filter('upgrader_source_selection', array($this, 'check_package') );
		remove_filter('upgrader_post_install', array($this, 'check_parent_myskin_filter'));

		if ( ! $this->result || is_mcms_error($this->result) )
			return $this->result;

		// Refresh the MySkin Update information
		mcms_clean_myskins_cache( $parsed_args['clear_update_cache'] );

		return true;
	}

	/**
	 * Upgrade a myskin.
	 *
	 * @since 2.8.0
	 * @since 3.7.0 The `$args` parameter was added, making clearing the update cache optional.
	 *
	 * @param string $myskin The myskin slug.
	 * @param array  $args {
	 *     Optional. Other arguments for upgrading a myskin. Default empty array.
	 *
	 *     @type bool $clear_update_cache Whether to clear the update cache if successful.
	 *                                    Default true.
	 * }
	 * @return bool|MCMS_Error True if the upgrade was successful, false or a MCMS_Error object otherwise.
	 */
	public function upgrade( $myskin, $args = array() ) {

		$defaults = array(
			'clear_update_cache' => true,
		);
		$parsed_args = mcms_parse_args( $args, $defaults );

		$this->init();
		$this->upgrade_strings();

		// Is an update available?
		$current = get_site_transient( 'update_myskins' );
		if ( !isset( $current->response[ $myskin ] ) ) {
			$this->skin->before();
			$this->skin->set_result(false);
			$this->skin->error( 'up_to_date' );
			$this->skin->after();
			return false;
		}

		$r = $current->response[ $myskin ];

		add_filter('upgrader_pre_install', array($this, 'current_before'), 10, 2);
		add_filter('upgrader_post_install', array($this, 'current_after'), 10, 2);
		add_filter('upgrader_clear_destination', array($this, 'delete_old_myskin'), 10, 4);
		if ( $parsed_args['clear_update_cache'] ) {
			// Clear cache so mcms_update_myskins() knows about the new myskin.
			add_action( 'upgrader_process_complete', 'mcms_clean_myskins_cache', 9, 0 );
		}

		$this->run( array(
			'package' => $r['package'],
			'destination' => get_myskin_root( $myskin ),
			'clear_destination' => true,
			'clear_working' => true,
			'hook_extra' => array(
				'myskin' => $myskin,
				'type' => 'myskin',
				'action' => 'update',
			),
		) );

		remove_action( 'upgrader_process_complete', 'mcms_clean_myskins_cache', 9 );
		remove_filter('upgrader_pre_install', array($this, 'current_before'));
		remove_filter('upgrader_post_install', array($this, 'current_after'));
		remove_filter('upgrader_clear_destination', array($this, 'delete_old_myskin'));

		if ( ! $this->result || is_mcms_error($this->result) )
			return $this->result;

		mcms_clean_myskins_cache( $parsed_args['clear_update_cache'] );

		return true;
	}

	/**
	 * Upgrade several myskins at once.
	 *
	 * @since 3.0.0
	 * @since 3.7.0 The `$args` parameter was added, making clearing the update cache optional.
	 *
	 * @param array $myskins The myskin slugs.
	 * @param array $args {
	 *     Optional. Other arguments for upgrading several myskins at once. Default empty array.
	 *
	 *     @type bool $clear_update_cache Whether to clear the update cache if successful.
	 *                                    Default true.
	 * }
	 * @return array[]|false An array of results, or false if unable to connect to the filesystem.
	 */
	public function bulk_upgrade( $myskins, $args = array() ) {

		$defaults = array(
			'clear_update_cache' => true,
		);
		$parsed_args = mcms_parse_args( $args, $defaults );

		$this->init();
		$this->bulk = true;
		$this->upgrade_strings();

		$current = get_site_transient( 'update_myskins' );

		add_filter('upgrader_pre_install', array($this, 'current_before'), 10, 2);
		add_filter('upgrader_post_install', array($this, 'current_after'), 10, 2);
		add_filter('upgrader_clear_destination', array($this, 'delete_old_myskin'), 10, 4);

		$this->skin->header();

		// Connect to the Filesystem first.
		$res = $this->fs_connect( array(MCMS_CONTENT_DIR) );
		if ( ! $res ) {
			$this->skin->footer();
			return false;
		}

		$this->skin->bulk_header();

		// Only start maintenance mode if:
		// - running Multisite and there are one or more myskins specified, OR
		// - a myskin with an update available is currently in use.
		// @TODO: For multisite, maintenance mode should only kick in for individual sites if at all possible.
		$maintenance = ( is_multisite() && ! empty( $myskins ) );
		foreach ( $myskins as $myskin )
			$maintenance = $maintenance || $myskin == get_stylesheet() || $myskin == get_template();
		if ( $maintenance )
			$this->maintenance_mode(true);

		$results = array();

		$this->update_count = count($myskins);
		$this->update_current = 0;
		foreach ( $myskins as $myskin ) {
			$this->update_current++;

			$this->skin->myskin_info = $this->myskin_info($myskin);

			if ( !isset( $current->response[ $myskin ] ) ) {
				$this->skin->set_result(true);
				$this->skin->before();
				$this->skin->feedback( 'up_to_date' );
				$this->skin->after();
				$results[$myskin] = true;
				continue;
			}

			// Get the URL to the zip file
			$r = $current->response[ $myskin ];

			$result = $this->run( array(
				'package' => $r['package'],
				'destination' => get_myskin_root( $myskin ),
				'clear_destination' => true,
				'clear_working' => true,
				'is_multi' => true,
				'hook_extra' => array(
					'myskin' => $myskin
				),
			) );

			$results[$myskin] = $this->result;

			// Prevent credentials auth screen from displaying multiple times
			if ( false === $result )
				break;
		} //end foreach $modules

		$this->maintenance_mode(false);

		// Refresh the MySkin Update information
		mcms_clean_myskins_cache( $parsed_args['clear_update_cache'] );

		/** This action is documented in mcms-admin/includes/class-mcms-upgrader.php */
		do_action( 'upgrader_process_complete', $this, array(
			'action' => 'update',
			'type' => 'myskin',
			'bulk' => true,
			'myskins' => $myskins,
		) );

		$this->skin->bulk_footer();

		$this->skin->footer();

		// Cleanup our hooks, in case something else does a upgrade on this connection.
		remove_filter('upgrader_pre_install', array($this, 'current_before'));
		remove_filter('upgrader_post_install', array($this, 'current_after'));
		remove_filter('upgrader_clear_destination', array($this, 'delete_old_myskin'));

		return $results;
	}

	/**
	 * Check that the package source contains a valid myskin.
	 *
	 * Hooked to the {@see 'upgrader_source_selection'} filter by MySkin_Upgrader::install().
	 * It will return an error if the myskin doesn't have style.css or index.php
	 * files.
	 *
	 * @since 3.3.0
	 *
	 * @global MCMS_Filesystem_Base $mcms_filesystem Subclass
	 *
	 * @param string $source The full path to the package source.
	 * @return string|MCMS_Error The source or a MCMS_Error.
	 */
	public function check_package( $source ) {
		global $mcms_filesystem;

		if ( is_mcms_error($source) )
			return $source;

		// Check the folder contains a valid myskin
		$working_directory = str_replace( $mcms_filesystem->mcms_content_dir(), trailingslashit(MCMS_CONTENT_DIR), $source);
		if ( ! is_dir($working_directory) ) // Sanity check, if the above fails, let's not prevent installation.
			return $source;

		// A proper archive should have a style.css file in the single subdirectory
		if ( ! file_exists( $working_directory . 'style.css' ) ) {
			return new MCMS_Error( 'incompatible_archive_myskin_no_style', $this->strings['incompatible_archive'],
				/* translators: %s: style.css */
				sprintf( __( 'The myskin is missing the %s stylesheet.' ),
					'<code>style.css</code>'
				)
			);
		}

		$info = get_file_data( $working_directory . 'style.css', array( 'Name' => 'MySkin Name', 'Template' => 'Template' ) );

		if ( empty( $info['Name'] ) ) {
			return new MCMS_Error( 'incompatible_archive_myskin_no_name', $this->strings['incompatible_archive'],
				/* translators: %s: style.css */
				sprintf( __( 'The %s stylesheet doesn&#8217;t contain a valid myskin header.' ),
					'<code>style.css</code>'
				)
			);
		}

		// If it's not a child myskin, it must have at least an index.php to be legit.
		if ( empty( $info['Template'] ) && ! file_exists( $working_directory . 'index.php' ) ) {
			return new MCMS_Error( 'incompatible_archive_myskin_no_index', $this->strings['incompatible_archive'],
				/* translators: %s: index.php */
				sprintf( __( 'The myskin is missing the %s file.' ),
					'<code>index.php</code>'
				)
			);
		}

		return $source;
	}

	/**
	 * Turn on maintenance mode before attempting to upgrade the current myskin.
	 *
	 * Hooked to the {@see 'upgrader_pre_install'} filter by MySkin_Upgrader::upgrade() and
	 * MySkin_Upgrader::bulk_upgrade().
	 *
	 * @since 2.8.0
	 *
	 * @param bool|MCMS_Error  $return
	 * @param array          $myskin
	 * @return bool|MCMS_Error
	 */
	public function current_before($return, $myskin) {
		if ( is_mcms_error($return) )
			return $return;

		$myskin = isset($myskin['myskin']) ? $myskin['myskin'] : '';

		if ( $myskin != get_stylesheet() ) //If not current
			return $return;
		//Change to maintenance mode now.
		if ( ! $this->bulk )
			$this->maintenance_mode(true);

		return $return;
	}

	/**
	 * Turn off maintenance mode after upgrading the current myskin.
	 *
	 * Hooked to the {@see 'upgrader_post_install'} filter by MySkin_Upgrader::upgrade()
	 * and MySkin_Upgrader::bulk_upgrade().
	 *
	 * @since 2.8.0
	 *
	 * @param bool|MCMS_Error  $return
	 * @param array          $myskin
	 * @return bool|MCMS_Error
	 */
	public function current_after($return, $myskin) {
		if ( is_mcms_error($return) )
			return $return;

		$myskin = isset($myskin['myskin']) ? $myskin['myskin'] : '';

		if ( $myskin != get_stylesheet() ) // If not current
			return $return;

		// Ensure stylesheet name hasn't changed after the upgrade:
		if ( $myskin == get_stylesheet() && $myskin != $this->result['destination_name'] ) {
			mcms_clean_myskins_cache();
			$stylesheet = $this->result['destination_name'];
			switch_myskin( $stylesheet );
		}

		//Time to remove maintenance mode
		if ( ! $this->bulk )
			$this->maintenance_mode(false);
		return $return;
	}

	/**
	 * Delete the old myskin during an upgrade.
	 *
	 * Hooked to the {@see 'upgrader_clear_destination'} filter by MySkin_Upgrader::upgrade()
	 * and MySkin_Upgrader::bulk_upgrade().
	 *
	 * @since 2.8.0
	 *
	 * @global MCMS_Filesystem_Base $mcms_filesystem Subclass
	 *
	 * @param bool   $removed
	 * @param string $local_destination
	 * @param string $remote_destination
	 * @param array  $myskin
	 * @return bool
	 */
	public function delete_old_myskin( $removed, $local_destination, $remote_destination, $myskin ) {
		global $mcms_filesystem;

		if ( is_mcms_error( $removed ) )
			return $removed; // Pass errors through.

		if ( ! isset( $myskin['myskin'] ) )
			return $removed;

		$myskin = $myskin['myskin'];
		$myskins_dir = trailingslashit( $mcms_filesystem->mcms_myskins_dir( $myskin ) );
		if ( $mcms_filesystem->exists( $myskins_dir . $myskin ) ) {
			if ( ! $mcms_filesystem->delete( $myskins_dir . $myskin, true ) )
				return false;
		}

		return true;
	}

	/**
	 * Get the MCMS_MySkin object for a myskin.
	 *
	 * @since 2.8.0
	 * @since 3.0.0 The `$myskin` argument was added.
	 *
	 * @param string $myskin The directory name of the myskin. This is optional, and if not supplied,
	 *                      the directory name from the last result will be used.
	 * @return MCMS_MySkin|false The myskin's info object, or false `$myskin` is not supplied
	 *                        and the last result isn't set.
	 */
	public function myskin_info($myskin = null) {

		if ( empty($myskin) ) {
			if ( !empty($this->result['destination_name']) )
				$myskin = $this->result['destination_name'];
			else
				return false;
		}
		return mcms_get_myskin( $myskin );
	}

}
