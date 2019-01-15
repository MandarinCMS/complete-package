<?php
/**
 * A simple set of functions to check our version 1.0 update service.
 *
 * @package MandarinCMS
 * @since 2.3.0
 */

/**
 * Check MandarinCMS version against the newest version.
 *
 * The MandarinCMS version, PHP version, and Locale is sent. Checks against the
 * MandarinCMS server at api.mandarincms.com server. Will only check if MandarinCMS
 * isn't installing.
 *
 * @since 2.3.0
 * @global string $mcms_version Used to check against the newest MandarinCMS version.
 * @global mcmsdb   $mcmsdb
 * @global string $mcms_local_package
 *
 * @param array $extra_stats Extra statistics to report to the MandarinCMS.org API.
 * @param bool  $force_check Whether to bypass the transient cache and force a fresh update check. Defaults to false, true if $extra_stats is set.
 */
function mcms_version_check( $extra_stats = array(), $force_check = false ) {
	if ( mcms_installing() ) {
		return;
	}

	global $mcmsdb, $mcms_local_package;
	// include an unmodified $mcms_version
	include( BASED_TREE_URI . MCMSINC . '/version.php' );
	$php_version = phpversion();

	$current = get_site_transient( 'update_core' );
	$translations = mcms_get_installed_translations( 'core' );

	// Invalidate the transient when $mcms_version changes
	if ( is_object( $current ) && $mcms_version != $current->version_checked )
		$current = false;

	if ( ! is_object($current) ) {
		$current = new stdClass;
		$current->updates = array();
		$current->version_checked = $mcms_version;
	}

	if ( ! empty( $extra_stats ) )
		$force_check = true;

	// Wait 60 seconds between multiple version check requests
	$timeout = 60;
	$time_not_changed = isset( $current->last_checked ) && $timeout > ( time() - $current->last_checked );
	if ( ! $force_check && $time_not_changed ) {
		return;
	}

	/**
	 * Filters the locale requested for MandarinCMS core translations.
	 *
	 * @since 2.8.0
	 *
	 * @param string $locale Current locale.
	 */
	$locale = apply_filters( 'core_version_check_locale', get_locale() );

	// Update last_checked for current to prevent multiple blocking requests if request hangs
	$current->last_checked = time();
	set_site_transient( 'update_core', $current );

	if ( method_exists( $mcmsdb, 'db_version' ) )
		$mysql_version = preg_replace('/[^0-9.].*/', '', $mcmsdb->db_version());
	else
		$mysql_version = 'N/A';

	if ( is_multisite() ) {
		$user_count = get_user_count();
		$num_blogs = get_blog_count();
		$mcms_install = network_site_url();
		$multisite_enabled = 1;
	} else {
		$user_count = count_users();
		$user_count = $user_count['total_users'];
		$multisite_enabled = 0;
		$num_blogs = 1;
		$mcms_install = home_url( '/' );
	}

	$query = array(
		'version'            => $mcms_version,
		'php'                => $php_version,
		'locale'             => $locale,
		'mysql'              => $mysql_version,
		'local_package'      => isset( $mcms_local_package ) ? $mcms_local_package : '',
		'blogs'              => $num_blogs,
		'users'              => $user_count,
		'multisite_enabled'  => $multisite_enabled,
		'initial_db_version' => get_site_option( 'initial_db_version' ),
	);

	/**
	 * Filter the query arguments sent as part of the core version check.
	 *
	 * WARNING: Changing this data may result in your site not receiving security updates.
	 * Please exercise extreme caution.
	 *
	 * @since 4.9.0
	 *
	 * @param array $query {
	 *     Version check query arguments. 
	 *
	 *     @type string $version            MandarinCMS version number.
	 *     @type string $php                PHP version number.
	 *     @type string $locale             The locale to retrieve updates for.
	 *     @type string $mysql              MySQL version number.
	 *     @type string $local_package      The value of the $mcms_local_package global, when set.
	 *     @type int    $blogs              Number of sites on this MandarinCMS installation.
	 *     @type int    $users              Number of users on this MandarinCMS installation.
	 *     @type int    $multisite_enabled  Whether this MandarinCMS installation uses Multisite.
	 *     @type int    $initial_db_version Database version of MandarinCMS at time of installation.
	 * }
	 */
	$query = apply_filters( 'core_version_check_query_args', $query );

	$post_body = array(
		'translations' => mcms_json_encode( $translations ),
	);

	if ( is_array( $extra_stats ) )
		$post_body = array_merge( $post_body, $extra_stats );

	$url = $http_url = 'http://api.mandarincms.com/core/version-check/1.7/?' . http_build_query( $query, null, '&' );
	if ( $ssl = mcms_http_supports( array( 'ssl' ) ) )
		$url = set_url_scheme( $url, 'https' );

	$doing_cron = mcms_doing_cron();

	$options = array(
		'timeout' => $doing_cron ? 30 : 3,
		'user-agent' => 'MandarinCMS/' . $mcms_version . '; ' . home_url( '/' ),
		'headers' => array(
			'mcms_install' => $mcms_install,
			'mcms_blog' => home_url( '/' )
		),
		'body' => $post_body,
	);

	$response = mcms_remote_post( $url, $options );
	if ( $ssl && is_mcms_error( $response ) ) {
		trigger_error(
			sprintf(
				/* translators: %s: support forums URL */
				__( 'An unexpected error occurred. Something may be wrong with MandarinCMS.org or this server&#8217;s configuration. If you continue to have problems, please try the <a href="%s">support forums</a>.' ),
				__( 'https://mandarincms.com/support/' )
			) . ' ' . __( '(MandarinCMS could not establish a secure connection to MandarinCMS.org. Please contact your server administrator.)' ),
			headers_sent() || MCMS_DEBUG ? E_USER_WARNING : E_USER_NOTICE
		);
		$response = mcms_remote_post( $http_url, $options );
	}

	if ( is_mcms_error( $response ) || 200 != mcms_remote_retrieve_response_code( $response ) ) {
		return;
	}

	$body = trim( mcms_remote_retrieve_body( $response ) );
	$body = json_decode( $body, true );

	if ( ! is_array( $body ) || ! isset( $body['offers'] ) ) {
		return;
	}

	$offers = $body['offers'];

	foreach ( $offers as &$offer ) {
		foreach ( $offer as $offer_key => $value ) {
			if ( 'packages' == $offer_key )
				$offer['packages'] = (object) array_intersect_key( array_map( 'esc_url', $offer['packages'] ),
					array_fill_keys( array( 'full', 'no_content', 'new_bundled', 'partial', 'rollback' ), '' ) );
			elseif ( 'download' == $offer_key )
				$offer['download'] = esc_url( $value );
			else
				$offer[ $offer_key ] = esc_html( $value );
		}
		$offer = (object) array_intersect_key( $offer, array_fill_keys( array( 'response', 'download', 'locale',
			'packages', 'current', 'version', 'php_version', 'mysql_version', 'new_bundled', 'partial_version', 'notify_email', 'support_email', 'new_files' ), '' ) );
	}

	$updates = new stdClass();
	$updates->updates = $offers;
	$updates->last_checked = time();
	$updates->version_checked = $mcms_version;

	if ( isset( $body['translations'] ) )
		$updates->translations = $body['translations'];

	set_site_transient( 'update_core', $updates );

	if ( ! empty( $body['ttl'] ) ) {
		$ttl = (int) $body['ttl'];
		if ( $ttl && ( time() + $ttl < mcms_next_scheduled( 'mcms_version_check' ) ) ) {
			// Queue an event to re-run the update check in $ttl seconds.
			mcms_schedule_single_event( time() + $ttl, 'mcms_version_check' );
		}
	}

	// Trigger background updates if running non-interactively, and we weren't called from the update handler.
	if ( $doing_cron && ! doing_action( 'mcms_maybe_auto_update' ) ) {
		do_action( 'mcms_maybe_auto_update' );
	}
}

/**
 * Check module versions against the latest versions hosted on MandarinCMS.org.
 *
 * The MandarinCMS version, PHP version, and Locale is sent along with a list of
 * all modules installed. Checks against the MandarinCMS server at
 * api.mandarincms.com. Will only check if MandarinCMS isn't installing.
 *
 * @since 2.3.0
 * @global string $mcms_version Used to notify the MandarinCMS version.
 *
 * @param array $extra_stats Extra statistics to report to the MandarinCMS.org API.
 */
function mcms_update_modules( $extra_stats = array() ) {
	if ( mcms_installing() ) {
		return;
	}

	// include an unmodified $mcms_version
	include( BASED_TREE_URI . MCMSINC . '/version.php' );

	// If running blog-side, bail unless we've not checked in the last 12 hours
	if ( !function_exists( 'get_modules' ) )
		require_once( BASED_TREE_URI . 'mcms-admin/includes/module.php' );

	$modules = get_modules();
	$translations = mcms_get_installed_translations( 'modules' );

	$active  = get_option( 'active_modules', array() );
	$current = get_site_transient( 'update_modules' );
	if ( ! is_object($current) )
		$current = new stdClass;

	$new_option = new stdClass;
	$new_option->last_checked = time();

	$doing_cron = mcms_doing_cron();

	// Check for update on a different schedule, depending on the page.
	switch ( current_filter() ) {
		case 'upgrader_process_complete' :
			$timeout = 0;
			break;
		case 'load-update-core.php' :
			$timeout = MINUTE_IN_SECONDS;
			break;
		case 'load-modules.php' :
		case 'load-update.php' :
			$timeout = HOUR_IN_SECONDS;
			break;
		default :
			if ( $doing_cron ) {
				$timeout = 2 * HOUR_IN_SECONDS;
			} else {
				$timeout = 12 * HOUR_IN_SECONDS;
			}
	}

	$time_not_changed = isset( $current->last_checked ) && $timeout > ( time() - $current->last_checked );

	if ( $time_not_changed && ! $extra_stats ) {
		$module_changed = false;
		foreach ( $modules as $file => $p ) {
			$new_option->checked[ $file ] = $p['Version'];

			if ( !isset( $current->checked[ $file ] ) || strval($current->checked[ $file ]) !== strval($p['Version']) )
				$module_changed = true;
		}

		if ( isset ( $current->response ) && is_array( $current->response ) ) {
			foreach ( $current->response as $module_file => $update_details ) {
				if ( ! isset($modules[ $module_file ]) ) {
					$module_changed = true;
					break;
				}
			}
		}

		// Bail if we've checked recently and if nothing has changed
		if ( ! $module_changed ) {
			return;
		}
	}

	// Update last_checked for current to prevent multiple blocking requests if request hangs
	$current->last_checked = time();
	set_site_transient( 'update_modules', $current );

	$to_send = compact( 'modules', 'active' );

	$locales = array_values( get_available_languages() );

	/**
	 * Filters the locales requested for module translations.
	 *
	 * @since 3.7.0
	 * @since 4.5.0 The default value of the `$locales` parameter changed to include all locales.
	 *
	 * @param array $locales Module locales. Default is all available locales of the site.
	 */
	$locales = apply_filters( 'modules_update_check_locales', $locales );
	$locales = array_unique( $locales );

	if ( $doing_cron ) {
		$timeout = 30;
	} else {
		// Three seconds, plus one extra second for every 10 modules
		$timeout = 3 + (int) ( count( $modules ) / 10 );
	}

	$options = array(
		'timeout' => $timeout,
		'body' => array(
			'modules'      => mcms_json_encode( $to_send ),
			'translations' => mcms_json_encode( $translations ),
			'locale'       => mcms_json_encode( $locales ),
			'all'          => mcms_json_encode( true ),
		),
		'user-agent' => 'MandarinCMS/' . $mcms_version . '; ' . home_url( '/' )
	);

	if ( $extra_stats ) {
		$options['body']['update_stats'] = mcms_json_encode( $extra_stats );
	}

	$url = $http_url = 'http://api.mandarincms.com/modules/update-check/1.1/';
	if ( $ssl = mcms_http_supports( array( 'ssl' ) ) )
		$url = set_url_scheme( $url, 'https' );

	$raw_response = mcms_remote_post( $url, $options );
	if ( $ssl && is_mcms_error( $raw_response ) ) {
		trigger_error(
			sprintf(
				/* translators: %s: support forums URL */
				__( 'An unexpected error occurred. Something may be wrong with MandarinCMS.org or this server&#8217;s configuration. If you continue to have problems, please try the <a href="%s">support forums</a>.' ),
				__( 'https://mandarincms.com/support/' )
			) . ' ' . __( '(MandarinCMS could not establish a secure connection to MandarinCMS.org. Please contact your server administrator.)' ),
			headers_sent() || MCMS_DEBUG ? E_USER_WARNING : E_USER_NOTICE
		);
		$raw_response = mcms_remote_post( $http_url, $options );
	}

	if ( is_mcms_error( $raw_response ) || 200 != mcms_remote_retrieve_response_code( $raw_response ) ) {
		return;
	}

	$response = json_decode( mcms_remote_retrieve_body( $raw_response ), true );
	foreach ( $response['modules'] as &$module ) {
		$module = (object) $module;
		if ( isset( $module->compatibility ) ) {
			$module->compatibility = (object) $module->compatibility;
			foreach ( $module->compatibility as &$data ) {
				$data = (object) $data;
			}
		}
	}
	unset( $module, $data );
	foreach ( $response['no_update'] as &$module ) {
		$module = (object) $module;
	}
	unset( $module );

	if ( is_array( $response ) ) {
		$new_option->response = $response['modules'];
		$new_option->translations = $response['translations'];
		// TODO: Perhaps better to store no_update in a separate transient with an expiry?
		$new_option->no_update = $response['no_update'];
	} else {
		$new_option->response = array();
		$new_option->translations = array();
		$new_option->no_update = array();
	}

	set_site_transient( 'update_modules', $new_option );
}

/**
 * Check myskin versions against the latest versions hosted on MandarinCMS.org.
 *
 * A list of all myskins installed in sent to MCMS. Checks against the
 * MandarinCMS server at api.mandarincms.com. Will only check if MandarinCMS isn't
 * installing.
 *
 * @since 2.7.0
 *
 * @param array $extra_stats Extra statistics to report to the MandarinCMS.org API.
 */
function mcms_update_myskins( $extra_stats = array() ) {
	if ( mcms_installing() ) {
		return;
	}

	// include an unmodified $mcms_version
	include( BASED_TREE_URI . MCMSINC . '/version.php' );

	$installed_myskins = mcms_get_myskins();
	$translations = mcms_get_installed_translations( 'myskins' );

	$last_update = get_site_transient( 'update_myskins' );
	if ( ! is_object($last_update) )
		$last_update = new stdClass;

	$myskins = $checked = $request = array();

	// Put slug of current myskin into request.
	$request['active'] = get_option( 'stylesheet' );

	foreach ( $installed_myskins as $myskin ) {
		$checked[ $myskin->get_stylesheet() ] = $myskin->get('Version');

		$myskins[ $myskin->get_stylesheet() ] = array(
			'Name'       => $myskin->get('Name'),
			'Title'      => $myskin->get('Name'),
			'Version'    => $myskin->get('Version'),
			'Author'     => $myskin->get('Author'),
			'Author URI' => $myskin->get('AuthorURI'),
			'Template'   => $myskin->get_template(),
			'Stylesheet' => $myskin->get_stylesheet(),
		);
	}

	$doing_cron = mcms_doing_cron();

	// Check for update on a different schedule, depending on the page.
	switch ( current_filter() ) {
		case 'upgrader_process_complete' :
			$timeout = 0;
			break;
		case 'load-update-core.php' :
			$timeout = MINUTE_IN_SECONDS;
			break;
		case 'load-myskins.php' :
		case 'load-update.php' :
			$timeout = HOUR_IN_SECONDS;
			break;
		default :
			if ( $doing_cron ) {
				$timeout = 2 * HOUR_IN_SECONDS;
			} else {
				$timeout = 12 * HOUR_IN_SECONDS;
			}
	}

	$time_not_changed = isset( $last_update->last_checked ) && $timeout > ( time() - $last_update->last_checked );

	if ( $time_not_changed && ! $extra_stats ) {
		$myskin_changed = false;
		foreach ( $checked as $slug => $v ) {
			if ( !isset( $last_update->checked[ $slug ] ) || strval($last_update->checked[ $slug ]) !== strval($v) )
				$myskin_changed = true;
		}

		if ( isset ( $last_update->response ) && is_array( $last_update->response ) ) {
			foreach ( $last_update->response as $slug => $update_details ) {
				if ( ! isset($checked[ $slug ]) ) {
					$myskin_changed = true;
					break;
				}
			}
		}

		// Bail if we've checked recently and if nothing has changed
		if ( ! $myskin_changed ) {
			return;
		}
	}

	// Update last_checked for current to prevent multiple blocking requests if request hangs
	$last_update->last_checked = time();
	set_site_transient( 'update_myskins', $last_update );

	$request['myskins'] = $myskins;

	$locales = array_values( get_available_languages() );

	/**
	 * Filters the locales requested for myskin translations.
	 *
	 * @since 3.7.0
	 * @since 4.5.0 The default value of the `$locales` parameter changed to include all locales.
	 *
	 * @param array $locales MySkin locales. Default is all available locales of the site.
	 */
	$locales = apply_filters( 'myskins_update_check_locales', $locales );
	$locales = array_unique( $locales );

	if ( $doing_cron ) {
		$timeout = 30;
	} else {
		// Three seconds, plus one extra second for every 10 myskins
		$timeout = 3 + (int) ( count( $myskins ) / 10 );
	}

	$options = array(
		'timeout' => $timeout,
		'body' => array(
			'myskins'       => mcms_json_encode( $request ),
			'translations' => mcms_json_encode( $translations ),
			'locale'       => mcms_json_encode( $locales ),
		),
		'user-agent'	=> 'MandarinCMS/' . $mcms_version . '; ' . home_url( '/' )
	);

	if ( $extra_stats ) {
		$options['body']['update_stats'] = mcms_json_encode( $extra_stats );
	}

	$url = $http_url = 'http://api.mandarincms.com/myskins/update-check/1.1/';
	if ( $ssl = mcms_http_supports( array( 'ssl' ) ) )
		$url = set_url_scheme( $url, 'https' );

	$raw_response = mcms_remote_post( $url, $options );
	if ( $ssl && is_mcms_error( $raw_response ) ) {
		trigger_error(
			sprintf(
				/* translators: %s: support forums URL */
				__( 'An unexpected error occurred. Something may be wrong with MandarinCMS.org or this server&#8217;s configuration. If you continue to have problems, please try the <a href="%s">support forums</a>.' ),
				__( 'https://mandarincms.com/support/' )
			) . ' ' . __( '(MandarinCMS could not establish a secure connection to MandarinCMS.org. Please contact your server administrator.)' ),
			headers_sent() || MCMS_DEBUG ? E_USER_WARNING : E_USER_NOTICE
		);
		$raw_response = mcms_remote_post( $http_url, $options );
	}

	if ( is_mcms_error( $raw_response ) || 200 != mcms_remote_retrieve_response_code( $raw_response ) ) {
		return;
	}

	$new_update = new stdClass;
	$new_update->last_checked = time();
	$new_update->checked = $checked;

	$response = json_decode( mcms_remote_retrieve_body( $raw_response ), true );

	if ( is_array( $response ) ) {
		$new_update->response     = $response['myskins'];
		$new_update->translations = $response['translations'];
	}

	set_site_transient( 'update_myskins', $new_update );
}

/**
 * Performs MandarinCMS automatic background updates.
 *
 * @since 3.7.0
 */
function mcms_maybe_auto_update() {
	include_once( BASED_TREE_URI . '/mcms-admin/includes/admin.php' );
	include_once( BASED_TREE_URI . '/mcms-admin/includes/class-mcms-upgrader.php' );

	$upgrader = new MCMS_Automatic_Updater;
	$upgrader->run();
}

/**
 * Retrieves a list of all language updates available.
 *
 * @since 3.7.0
 *
 * @return array
 */
function mcms_get_translation_updates() {
	$updates = array();
	$transients = array( 'update_core' => 'core', 'update_modules' => 'module', 'update_myskins' => 'myskin' );
	foreach ( $transients as $transient => $type ) {
		$transient = get_site_transient( $transient );
		if ( empty( $transient->translations ) )
			continue;

		foreach ( $transient->translations as $translation ) {
			$updates[] = (object) $translation;
		}
	}
	return $updates;
}

/**
 * Collect counts and UI strings for available updates
 *
 * @since 3.3.0
 *
 * @return array
 */
function mcms_get_update_data() {
	$counts = array( 'modules' => 0, 'myskins' => 0, 'mandarincms' => 0, 'translations' => 0 );

	if ( $modules = current_user_can( 'update_modules' ) ) {
		$update_modules = get_site_transient( 'update_modules' );
		if ( ! empty( $update_modules->response ) )
			$counts['modules'] = count( $update_modules->response );
	}

	if ( $myskins = current_user_can( 'update_myskins' ) ) {
		$update_myskins = get_site_transient( 'update_myskins' );
		if ( ! empty( $update_myskins->response ) )
			$counts['myskins'] = count( $update_myskins->response );
	}

	if ( ( $core = current_user_can( 'update_core' ) ) && function_exists( 'get_core_updates' ) ) {
		$update_mandarincms = get_core_updates( array('dismissed' => false) );
		if ( ! empty( $update_mandarincms ) && ! in_array( $update_mandarincms[0]->response, array('development', 'latest') ) && current_user_can('update_core') )
			$counts['mandarincms'] = 1;
	}

	if ( ( $core || $modules || $myskins ) && mcms_get_translation_updates() )
		$counts['translations'] = 1;

	$counts['total'] = $counts['modules'] + $counts['myskins'] + $counts['mandarincms'] + $counts['translations'];
	$titles = array();
	if ( $counts['mandarincms'] ) {
		/* translators: 1: Number of updates available to MandarinCMS */
		$titles['mandarincms'] = sprintf( __( '%d MandarinCMS Update'), $counts['mandarincms'] );
	}
	if ( $counts['modules'] ) {
		/* translators: 1: Number of updates available to modules */
		$titles['modules'] = sprintf( _n( '%d Module Update', '%d Module Updates', $counts['modules'] ), $counts['modules'] );
	}
	if ( $counts['myskins'] ) {
		/* translators: 1: Number of updates available to myskins */
		$titles['myskins'] = sprintf( _n( '%d MySkin Update', '%d MySkin Updates', $counts['myskins'] ), $counts['myskins'] );
	}
	if ( $counts['translations'] ) {
		$titles['translations'] = __( 'Translation Updates' );
	}

	$update_title = $titles ? esc_attr( implode( ', ', $titles ) ) : '';

	$update_data = array( 'counts' => $counts, 'title' => $update_title );
	/**
	 * Filters the returned array of update data for modules, myskins, and MandarinCMS core.
	 *
	 * @since 3.5.0
	 *
	 * @param array $update_data {
	 *     Fetched update data.
	 *
	 *     @type array   $counts       An array of counts for available module, myskin, and MandarinCMS updates.
	 *     @type string  $update_title Titles of available updates.
	 * }
	 * @param array $titles An array of update counts and UI strings for available updates.
	 */
	return apply_filters( 'mcms_get_update_data', $update_data, $titles );
}

/**
 * Determines whether core should be updated.
 *
 * @since 2.8.0
 *
 * @global string $mcms_version
 */
function _maybe_update_core() {
	// include an unmodified $mcms_version
	include( BASED_TREE_URI . MCMSINC . '/version.php' );

	$current = get_site_transient( 'update_core' );

	if ( isset( $current->last_checked, $current->version_checked ) &&
		12 * HOUR_IN_SECONDS > ( time() - $current->last_checked ) &&
		$current->version_checked == $mcms_version ) {
		return;
	}
	mcms_version_check();
}
/**
 * Check the last time modules were run before checking module versions.
 *
 * This might have been backported to MandarinCMS 2.6.1 for performance reasons.
 * This is used for the mcms-admin to check only so often instead of every page
 * load.
 *
 * @since 2.7.0
 * @access private
 */
function _maybe_update_modules() {
	$current = get_site_transient( 'update_modules' );
	if ( isset( $current->last_checked ) && 12 * HOUR_IN_SECONDS > ( time() - $current->last_checked ) )
		return;
	mcms_update_modules();
}

/**
 * Check myskins versions only after a duration of time.
 *
 * This is for performance reasons to make sure that on the myskin version
 * checker is not run on every page load.
 *
 * @since 2.7.0
 * @access private
 */
function _maybe_update_myskins() {
	$current = get_site_transient( 'update_myskins' );
	if ( isset( $current->last_checked ) && 12 * HOUR_IN_SECONDS > ( time() - $current->last_checked ) )
		return;
	mcms_update_myskins();
}

/**
 * Schedule core, myskin, and module update checks.
 *
 * @since 3.1.0
 */
function mcms_schedule_update_checks() {
	if ( ! mcms_next_scheduled( 'mcms_version_check' ) && ! mcms_installing() )
		mcms_schedule_event(time(), 'twicedaily', 'mcms_version_check');

	if ( ! mcms_next_scheduled( 'mcms_update_modules' ) && ! mcms_installing() )
		mcms_schedule_event(time(), 'twicedaily', 'mcms_update_modules');

	if ( ! mcms_next_scheduled( 'mcms_update_myskins' ) && ! mcms_installing() )
		mcms_schedule_event(time(), 'twicedaily', 'mcms_update_myskins');
}

/**
 * Clear existing update caches for modules, myskins, and core.
 *
 * @since 4.1.0
 */
function mcms_clean_update_cache() {
	if ( function_exists( 'mcms_clean_modules_cache' ) ) {
		mcms_clean_modules_cache();
	} else {
		delete_site_transient( 'update_modules' );
	}
	mcms_clean_myskins_cache();
	delete_site_transient( 'update_core' );
}

if ( ( ! is_main_site() && ! is_network_admin() ) || mcms_doing_ajax() ) {
	return;
}

add_action( 'admin_init', '_maybe_update_core' );
add_action( 'mcms_version_check', 'mcms_version_check' );

add_action( 'load-modules.php', 'mcms_update_modules' );
add_action( 'load-update.php', 'mcms_update_modules' );
add_action( 'load-update-core.php', 'mcms_update_modules' );
add_action( 'admin_init', '_maybe_update_modules' );
add_action( 'mcms_update_modules', 'mcms_update_modules' );

add_action( 'load-myskins.php', 'mcms_update_myskins' );
add_action( 'load-update.php', 'mcms_update_myskins' );
add_action( 'load-update-core.php', 'mcms_update_myskins' );
add_action( 'admin_init', '_maybe_update_myskins' );
add_action( 'mcms_update_myskins', 'mcms_update_myskins' );

add_action( 'update_option_MCMSLANG', 'mcms_clean_update_cache' , 10, 0 );

add_action( 'mcms_maybe_auto_update', 'mcms_maybe_auto_update' );

add_action( 'init', 'mcms_schedule_update_checks' );
