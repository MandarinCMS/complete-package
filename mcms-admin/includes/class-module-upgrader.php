<?php
/**
 * Upgrade API: Module_Upgrader class
 *
 * @package MandarinCMS
 * @subpackage Upgrader
 * @since 4.6.0
 */

/**
 * Core class used for upgrading/installing modules.
 *
 * It is designed to upgrade/install modules from a local zip, remote zip URL,
 * or uploaded zip file.
 *
 * @since 2.8.0
 * @since 4.6.0 Moved to its own file from mcms-admin/includes/class-mcms-upgrader.php.
 *
 * @see MCMS_Upgrader
 */
class Module_Upgrader extends MCMS_Upgrader {

	/**
	 * Module upgrade result.
	 *
	 * @since 2.8.0
	 * @var array|MCMS_Error $result
	 *
	 * @see MCMS_Upgrader::$result
	 */
	public $result;

	/**
	 * Whether a bulk upgrade/installation is being performed.
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
		$this->strings['up_to_date'] = __('The module is at the latest version.');
		$this->strings['no_package'] = __('Update package not available.');
		/* translators: %s: package URL */
		$this->strings['downloading_package'] = sprintf( __( 'Downloading update from %s&#8230;' ), '<span class="code">%s</span>' );
		$this->strings['unpack_package'] = __('Unpacking the update&#8230;');
		$this->strings['remove_old'] = __('Removing the old version of the module&#8230;');
		$this->strings['remove_old_failed'] = __('Could not remove the old module.');
		$this->strings['process_failed'] = __('Module update failed.');
		$this->strings['process_success'] = __('Module updated successfully.');
		$this->strings['process_bulk_success'] = __('Modules updated successfully.');
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
		$this->strings['installing_package'] = __('Installing the module&#8230;');
		$this->strings['no_files'] = __('The module contains no files.');
		$this->strings['process_failed'] = __('Module installation failed.');
		$this->strings['process_success'] = __('Module installed successfully.');
	}

	/**
	 * Install a module package.
	 *
	 * @since 2.8.0
	 * @since 3.7.0 The `$args` parameter was added, making clearing the module update cache optional.
	 *
	 * @param string $package The full local path or URI of the package.
	 * @param array  $args {
	 *     Optional. Other arguments for installing a module package. Default empty array.
	 *
	 *     @type bool $clear_update_cache Whether to clear the module updates cache if successful.
	 *                                    Default true.
	 * }
	 * @return bool|MCMS_Error True if the installation was successful, false or a MCMS_Error otherwise.
	 */
	public function install( $package, $args = array() ) {

		$defaults = array(
			'clear_update_cache' => true,
		);
		$parsed_args = mcms_parse_args( $args, $defaults );

		$this->init();
		$this->install_strings();

		add_filter('upgrader_source_selection', array($this, 'check_package') );
		if ( $parsed_args['clear_update_cache'] ) {
			// Clear cache so mcms_update_modules() knows about the new module.
			add_action( 'upgrader_process_complete', 'mcms_clean_modules_cache', 9, 0 );
		}

		$this->run( array(
			'package' => $package,
			'destination' => MCMS_PLUGIN_DIR,
			'clear_destination' => false, // Do not overwrite files.
			'clear_working' => true,
			'hook_extra' => array(
				'type' => 'module',
				'action' => 'install',
			)
		) );

		remove_action( 'upgrader_process_complete', 'mcms_clean_modules_cache', 9 );
		remove_filter('upgrader_source_selection', array($this, 'check_package') );

		if ( ! $this->result || is_mcms_error($this->result) )
			return $this->result;

		// Force refresh of module update information
		mcms_clean_modules_cache( $parsed_args['clear_update_cache'] );

		return true;
	}

	/**
	 * Upgrade a module.
	 *
	 * @since 2.8.0
	 * @since 3.7.0 The `$args` parameter was added, making clearing the module update cache optional.
	 *
	 * @param string $module The basename path to the main module file.
	 * @param array  $args {
	 *     Optional. Other arguments for upgrading a module package. Default empty array.
	 *
	 *     @type bool $clear_update_cache Whether to clear the module updates cache if successful.
	 *                                    Default true.
	 * }
	 * @return bool|MCMS_Error True if the upgrade was successful, false or a MCMS_Error object otherwise.
	 */
	public function upgrade( $module, $args = array() ) {

		$defaults = array(
			'clear_update_cache' => true,
		);
		$parsed_args = mcms_parse_args( $args, $defaults );

		$this->init();
		$this->upgrade_strings();

		$current = get_site_transient( 'update_modules' );
		if ( !isset( $current->response[ $module ] ) ) {
			$this->skin->before();
			$this->skin->set_result(false);
			$this->skin->error('up_to_date');
			$this->skin->after();
			return false;
		}

		// Get the URL to the zip file
		$r = $current->response[ $module ];

		add_filter('upgrader_pre_install', array($this, 'deactivate_module_before_upgrade'), 10, 2);
		add_filter('upgrader_clear_destination', array($this, 'delete_old_module'), 10, 4);
		//'source_selection' => array($this, 'source_selection'), //there's a trac ticket to move up the directory for zip's which are made a bit differently, useful for non-.org modules.
		if ( $parsed_args['clear_update_cache'] ) {
			// Clear cache so mcms_update_modules() knows about the new module.
			add_action( 'upgrader_process_complete', 'mcms_clean_modules_cache', 9, 0 );
		}

		$this->run( array(
			'package' => $r->package,
			'destination' => MCMS_PLUGIN_DIR,
			'clear_destination' => true,
			'clear_working' => true,
			'hook_extra' => array(
				'module' => $module,
				'type' => 'module',
				'action' => 'update',
			),
		) );

		// Cleanup our hooks, in case something else does a upgrade on this connection.
		remove_action( 'upgrader_process_complete', 'mcms_clean_modules_cache', 9 );
		remove_filter('upgrader_pre_install', array($this, 'deactivate_module_before_upgrade'));
		remove_filter('upgrader_clear_destination', array($this, 'delete_old_module'));

		if ( ! $this->result || is_mcms_error($this->result) )
			return $this->result;

		// Force refresh of module update information
		mcms_clean_modules_cache( $parsed_args['clear_update_cache'] );

		return true;
	}

	/**
	 * Bulk upgrade several modules at once.
	 *
	 * @since 2.8.0
	 * @since 3.7.0 The `$args` parameter was added, making clearing the module update cache optional.
	 *
	 * @param array $modules Array of the basename paths of the modules' main files.
	 * @param array $args {
	 *     Optional. Other arguments for upgrading several modules at once. Default empty array.
	 *
	 *     @type bool $clear_update_cache Whether to clear the module updates cache if successful.
	 *                                    Default true.
	 * }
	 * @return array|false An array of results indexed by module file, or false if unable to connect to the filesystem.
	 */
	public function bulk_upgrade( $modules, $args = array() ) {

		$defaults = array(
			'clear_update_cache' => true,
		);
		$parsed_args = mcms_parse_args( $args, $defaults );

		$this->init();
		$this->bulk = true;
		$this->upgrade_strings();

		$current = get_site_transient( 'update_modules' );

		add_filter('upgrader_clear_destination', array($this, 'delete_old_module'), 10, 4);

		$this->skin->header();

		// Connect to the Filesystem first.
		$res = $this->fs_connect( array(MCMS_CONTENT_DIR, MCMS_PLUGIN_DIR) );
		if ( ! $res ) {
			$this->skin->footer();
			return false;
		}

		$this->skin->bulk_header();

		/*
		 * Only start maintenance mode if:
		 * - running Multisite and there are one or more modules specified, OR
		 * - a module with an update available is currently active.
		 * @TODO: For multisite, maintenance mode should only kick in for individual sites if at all possible.
		 */
		$maintenance = ( is_multisite() && ! empty( $modules ) );
		foreach ( $modules as $module )
			$maintenance = $maintenance || ( is_module_active( $module ) && isset( $current->response[ $module] ) );
		if ( $maintenance )
			$this->maintenance_mode(true);

		$results = array();

		$this->update_count = count($modules);
		$this->update_current = 0;
		foreach ( $modules as $module ) {
			$this->update_current++;
			$this->skin->module_info = get_module_data( MCMS_PLUGIN_DIR . '/' . $module, false, true);

			if ( !isset( $current->response[ $module ] ) ) {
				$this->skin->set_result('up_to_date');
				$this->skin->before();
				$this->skin->feedback('up_to_date');
				$this->skin->after();
				$results[$module] = true;
				continue;
			}

			// Get the URL to the zip file.
			$r = $current->response[ $module ];

			$this->skin->module_active = is_module_active($module);

			$result = $this->run( array(
				'package' => $r->package,
				'destination' => MCMS_PLUGIN_DIR,
				'clear_destination' => true,
				'clear_working' => true,
				'is_multi' => true,
				'hook_extra' => array(
					'module' => $module
				)
			) );

			$results[$module] = $this->result;

			// Prevent credentials auth screen from displaying multiple times
			if ( false === $result )
				break;
		} //end foreach $modules

		$this->maintenance_mode(false);

		// Force refresh of module update information.
		mcms_clean_modules_cache( $parsed_args['clear_update_cache'] );

		/** This action is documented in mcms-admin/includes/class-mcms-upgrader.php */
		do_action( 'upgrader_process_complete', $this, array(
			'action' => 'update',
			'type' => 'module',
			'bulk' => true,
			'modules' => $modules,
		) );

		$this->skin->bulk_footer();

		$this->skin->footer();

		// Cleanup our hooks, in case something else does a upgrade on this connection.
		remove_filter('upgrader_clear_destination', array($this, 'delete_old_module'));

		return $results;
	}

	/**
	 * Check a source package to be sure it contains a module.
	 *
	 * This function is added to the {@see 'upgrader_source_selection'} filter by
	 * Module_Upgrader::install().
	 *
	 * @since 3.3.0
	 *
	 * @global MCMS_Filesystem_Base $mcms_filesystem Subclass
	 *
	 * @param string $source The path to the downloaded package source.
	 * @return string|MCMS_Error The source as passed, or a MCMS_Error object
	 *                         if no modules were found.
	 */
	public function check_package($source) {
		global $mcms_filesystem;

		if ( is_mcms_error($source) )
			return $source;

		$working_directory = str_replace( $mcms_filesystem->mcms_content_dir(), trailingslashit(MCMS_CONTENT_DIR), $source);
		if ( ! is_dir($working_directory) ) // Sanity check, if the above fails, let's not prevent installation.
			return $source;

		// Check the folder contains at least 1 valid module.
		$modules_found = false;
		$files = glob( $working_directory . '*.php' );
		if ( $files ) {
			foreach ( $files as $file ) {
				$info = get_module_data( $file, false, false );
				if ( ! empty( $info['Name'] ) ) {
					$modules_found = true;
					break;
				}
			}
		}

		if ( ! $modules_found )
			return new MCMS_Error( 'incompatible_archive_no_modules', $this->strings['incompatible_archive'], __( 'No valid modules were found.' ) );

		return $source;
	}

	/**
	 * Retrieve the path to the file that contains the module info.
	 *
	 * This isn't used internally in the class, but is called by the skins.
	 *
	 * @since 2.8.0
	 *
	 * @return string|false The full path to the main module file, or false.
	 */
	public function module_info() {
		if ( ! is_array($this->result) )
			return false;
		if ( empty($this->result['destination_name']) )
			return false;

		$module = get_modules('/' . $this->result['destination_name']); //Ensure to pass with leading slash
		if ( empty($module) )
			return false;

		$modulefiles = array_keys($module); //Assume the requested module is the first in the list

		return $this->result['destination_name'] . '/' . $modulefiles[0];
	}

	/**
	 * Deactivates a module before it is upgraded.
	 *
	 * Hooked to the {@see 'upgrader_pre_install'} filter by Module_Upgrader::upgrade().
	 *
	 * @since 2.8.0
	 * @since 4.1.0 Added a return value.
	 *
	 * @param bool|MCMS_Error  $return Upgrade offer return.
	 * @param array          $module Module package arguments.
	 * @return bool|MCMS_Error The passed in $return param or MCMS_Error.
	 */
	public function deactivate_module_before_upgrade($return, $module) {

		if ( is_mcms_error($return) ) //Bypass.
			return $return;

		// When in cron (background updates) don't deactivate the module, as we require a browser to reactivate it
		if ( mcms_doing_cron() )
			return $return;

		$module = isset($module['module']) ? $module['module'] : '';
		if ( empty($module) )
			return new MCMS_Error('bad_request', $this->strings['bad_request']);

		if ( is_module_active($module) ) {
			//Deactivate the module silently, Prevent deactivation hooks from running.
			deactivate_modules($module, true);
		}

		return $return;
	}

	/**
	 * Delete the old module during an upgrade.
	 *
	 * Hooked to the {@see 'upgrader_clear_destination'} filter by
	 * Module_Upgrader::upgrade() and Module_Upgrader::bulk_upgrade().
	 *
	 * @since 2.8.0
	 *
	 * @global MCMS_Filesystem_Base $mcms_filesystem Subclass
     *
	 * @param bool|MCMS_Error $removed
	 * @param string        $local_destination
	 * @param string        $remote_destination
	 * @param array         $module
	 * @return MCMS_Error|bool
	 */
	public function delete_old_module($removed, $local_destination, $remote_destination, $module) {
		global $mcms_filesystem;

		if ( is_mcms_error($removed) )
			return $removed; //Pass errors through.

		$module = isset($module['module']) ? $module['module'] : '';
		if ( empty($module) )
			return new MCMS_Error('bad_request', $this->strings['bad_request']);

		$modules_dir = $mcms_filesystem->mcms_modules_dir();
		$this_module_dir = trailingslashit( dirname($modules_dir . $module) );

		if ( ! $mcms_filesystem->exists($this_module_dir) ) //If it's already vanished.
			return $removed;

		// If module is in its own directory, recursively delete the directory.
		if ( strpos($module, '/') && $this_module_dir != $modules_dir ) //base check on if module includes directory separator AND that it's not the root module folder
			$deleted = $mcms_filesystem->delete($this_module_dir, true);
		else
			$deleted = $mcms_filesystem->delete($modules_dir . $module);

		if ( ! $deleted )
			return new MCMS_Error('remove_old_failed', $this->strings['remove_old_failed']);

		return true;
	}
}
