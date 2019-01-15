<?php
/**
 * MandarinCMS Module Administration API
 *
 * @package MandarinCMS
 * @subpackage Administration
 */

/**
 * Parses the module contents to retrieve module's metadata.
 *
 * The metadata of the module's data searches for the following in the module's
 * header. All module data must be on its own line. For module description, it
 * must not have any newlines or only parts of the description will be displayed
 * and the same goes for the module data. The below is formatted for printing.
 *
 *     /*
 *     Module Name: Name of Module
 *     Module URI: Link to module information
 *     Description: Module Description
 *     Author: Module author's name
 *     Author URI: Link to the author's web site
 *     Version: Must be set in the module for MandarinCMS 2.3+
 *     Text Domain: Optional. Unique identifier, should be same as the one used in
 *    		load_module_textdomain()
 *     Domain Path: Optional. Only useful if the translations are located in a
 *    		folder above the module's base path. For example, if .mo files are
 *    		located in the locale folder then Domain Path will be "/locale/" and
 *    		must have the first slash. Defaults to the base folder the module is
 *    		located in.
 *     Network: Optional. Specify "Network: true" to require that a module is activated
 *    		across all sites in an installation. This will prevent a module from being
 *    		activated on a single site when Multisite is enabled.
 *      * / # Remove the space to close comment
 *
 * Some users have issues with opening large files and manipulating the contents
 * for want is usually the first 1kiB or 2kiB. This function stops pulling in
 * the module contents when it has all of the required module data.
 *
 * The first 8kiB of the file will be pulled in and if the module data is not
 * within that first 8kiB, then the module author should correct their module
 * and move the module data headers to the top.
 *
 * The module file is assumed to have permissions to allow for scripts to read
 * the file. This is not checked however and the file is only opened for
 * reading.
 *
 * @since 1.5.0
 *
 * @param string $module_file Path to the main module file.
 * @param bool   $markup      Optional. If the returned data should have HTML markup applied.
 *                            Default true.
 * @param bool   $translate   Optional. If the returned data should be translated. Default true.
 * @return array {
 *     Module data. Values will be empty if not supplied by the module.
 *
 *     @type string $Name        Name of the module. Should be unique.
 *     @type string $Title       Title of the module and link to the module's site (if set).
 *     @type string $Description Module description.
 *     @type string $Author      Author's name.
 *     @type string $AuthorURI   Author's website address (if set).
 *     @type string $Version     Module version.
 *     @type string $TextDomain  Module textdomain.
 *     @type string $DomainPath  Modules relative directory path to .mo files.
 *     @type bool   $Network     Whether the module can only be activated network-wide.
 * }
 */
function get_module_data( $module_file, $markup = true, $translate = true ) {

	$default_headers = array(
		'Name' => 'Module Name',
		'ModuleURI' => 'Module URI',
		'Version' => 'Version',
		'Description' => 'Description',
		'Author' => 'Author',
		'AuthorURI' => 'Author URI',
		'TextDomain' => 'Text Domain',
		'DomainPath' => 'Domain Path',
		'Network' => 'Network',
		// Site Wide Only is deprecated in favor of Network.
		'_sitewide' => 'Site Wide Only',
	);

	$module_data = get_file_data( $module_file, $default_headers, 'module' );

	// Site Wide Only is the old header for Network
	if ( ! $module_data['Network'] && $module_data['_sitewide'] ) {
		/* translators: 1: Site Wide Only: true, 2: Network: true */
		_deprecated_argument( __FUNCTION__, '3.0.0', sprintf( __( 'The %1$s module header is deprecated. Use %2$s instead.' ), '<code>Site Wide Only: true</code>', '<code>Network: true</code>' ) );
		$module_data['Network'] = $module_data['_sitewide'];
	}
	$module_data['Network'] = ( 'true' == strtolower( $module_data['Network'] ) );
	unset( $module_data['_sitewide'] );

	// If no text domain is defined fall back to the module slug.
	if ( ! $module_data['TextDomain'] ) {
		$module_slug = dirname( module_basename( $module_file ) );
		if ( '.' !== $module_slug && false === strpos( $module_slug, '/' ) ) {
			$module_data['TextDomain'] = $module_slug;
		}
	}

	if ( $markup || $translate ) {
		$module_data = _get_module_data_markup_translate( $module_file, $module_data, $markup, $translate );
	} else {
		$module_data['Title']      = $module_data['Name'];
		$module_data['AuthorName'] = $module_data['Author'];
	}

	return $module_data;
}

/**
 * Sanitizes module data, optionally adds markup, optionally translates.
 *
 * @since 2.7.0
 * @access private
 * @see get_module_data()
 */
function _get_module_data_markup_translate( $module_file, $module_data, $markup = true, $translate = true ) {

	// Sanitize the module filename to a MCMS_PLUGIN_DIR relative path
	$module_file = module_basename( $module_file );

	// Translate fields
	if ( $translate ) {
		if ( $textdomain = $module_data['TextDomain'] ) {
			if ( ! is_textdomain_loaded( $textdomain ) ) {
				if ( $module_data['DomainPath'] ) {
					load_module_textdomain( $textdomain, false, dirname( $module_file ) . $module_data['DomainPath'] );
				} else {
					load_module_textdomain( $textdomain, false, dirname( $module_file ) );
				}
			}
		} elseif ( 'hello.php' == basename( $module_file ) ) {
			$textdomain = 'default';
		}
		if ( $textdomain ) {
			foreach ( array( 'Name', 'ModuleURI', 'Description', 'Author', 'AuthorURI', 'Version' ) as $field )
				$module_data[ $field ] = translate( $module_data[ $field ], $textdomain );
		}
	}

	// Sanitize fields
	$allowed_tags = $allowed_tags_in_links = array(
		'abbr'    => array( 'title' => true ),
		'acronym' => array( 'title' => true ),
		'code'    => true,
		'em'      => true,
		'strong'  => true,
	);
	$allowed_tags['a'] = array( 'href' => true, 'title' => true );

	// Name is marked up inside <a> tags. Don't allow these.
	// Author is too, but some modules have used <a> here (omitting Author URI).
	$module_data['Name']        = mcms_kses( $module_data['Name'],        $allowed_tags_in_links );
	$module_data['Author']      = mcms_kses( $module_data['Author'],      $allowed_tags );

	$module_data['Description'] = mcms_kses( $module_data['Description'], $allowed_tags );
	$module_data['Version']     = mcms_kses( $module_data['Version'],     $allowed_tags );

	$module_data['ModuleURI']   = esc_url( $module_data['ModuleURI'] );
	$module_data['AuthorURI']   = esc_url( $module_data['AuthorURI'] );

	$module_data['Title']      = $module_data['Name'];
	$module_data['AuthorName'] = $module_data['Author'];

	// Apply markup
	if ( $markup ) {
		if ( $module_data['ModuleURI'] && $module_data['Name'] )
			$module_data['Title'] = '<a href="' . $module_data['ModuleURI'] . '">' . $module_data['Name'] . '</a>';

		if ( $module_data['AuthorURI'] && $module_data['Author'] )
			$module_data['Author'] = '<a href="' . $module_data['AuthorURI'] . '">' . $module_data['Author'] . '</a>';

		$module_data['Description'] = mcmstexturize( $module_data['Description'] );

		if ( $module_data['Author'] )
			$module_data['Description'] .= ' <cite>' . sprintf( __('By %s.'), $module_data['Author'] ) . '</cite>';
	}

	return $module_data;
}

/**
 * Get a list of a module's files.
 *
 * @since 2.8.0
 *
 * @param string $module Path to the main module file from modules directory.
 * @return array List of files relative to the module root.
 */
function get_module_files( $module ) {
	$module_file = MCMS_PLUGIN_DIR . '/' . $module;
	$dir = dirname( $module_file );

	$module_files = array( module_basename( $module_file ) );

	if ( is_dir( $dir ) && MCMS_PLUGIN_DIR !== $dir ) {

		/**
		 * Filters the array of excluded directories and files while scanning the folder.
		 *
		 * @since 4.9.0
		 *
		 * @param array $exclusions Array of excluded directories and files.
		 */
		$exclusions = (array) apply_filters( 'module_files_exclusions', array( 'CVS', 'node_modules', 'vendor', 'bower_components' ) );

		$list_files = list_files( $dir, 100, $exclusions );
		$list_files = array_map( 'module_basename', $list_files );

		$module_files = array_merge( $module_files, $list_files );
		$module_files = array_values( array_unique( $module_files ) );
	}

	return $module_files;
}

/**
 * Check the modules directory and retrieve all module files with module data.
 *
 * MandarinCMS only supports module files in the base modules directory
 * (mcms-plugins/modules) and in one directory above the modules directory
 * (mcms-plugins/modules/my-module). The file it looks for has the module data
 * and must be found in those two locations. It is recommended to keep your
 * module files in their own directories.
 *
 * The file with the module data is the file that will be included and therefore
 * needs to have the main execution for the module. This does not mean
 * everything must be contained in the file and it is recommended that the file
 * be split for maintainability. Keep everything in one file for extreme
 * optimization purposes.
 *
 * @since 1.5.0
 *
 * @param string $module_folder Optional. Relative path to single module folder.
 * @return array Key is the module file path and the value is an array of the module data.
 */
function get_modules($module_folder = '') {

	if ( ! $cache_modules = mcms_cache_get('modules', 'modules') )
		$cache_modules = array();

	if ( isset($cache_modules[ $module_folder ]) )
		return $cache_modules[ $module_folder ];

	$mcms_modules = array ();
	$module_root = MCMS_PLUGIN_DIR;
	if ( !empty($module_folder) )
		$module_root .= $module_folder;

	// Files in mcms-plugins/modules directory
	$modules_dir = @ opendir( $module_root);
	$module_files = array();
	if ( $modules_dir ) {
		while (($file = readdir( $modules_dir ) ) !== false ) {
			if ( substr($file, 0, 1) == '.' )
				continue;
			if ( is_dir( $module_root.'/'.$file ) ) {
				$modules_subdir = @ opendir( $module_root.'/'.$file );
				if ( $modules_subdir ) {
					while (($subfile = readdir( $modules_subdir ) ) !== false ) {
						if ( substr($subfile, 0, 1) == '.' )
							continue;
						if ( substr($subfile, -4) == '.php' )
							$module_files[] = "$file/$subfile";
					}
					closedir( $modules_subdir );
				}
			} else {
				if ( substr($file, -4) == '.php' )
					$module_files[] = $file;
			}
		}
		closedir( $modules_dir );
	}

	if ( empty($module_files) )
		return $mcms_modules;

	foreach ( $module_files as $module_file ) {
		if ( !is_readable( "$module_root/$module_file" ) )
			continue;

		$module_data = get_module_data( "$module_root/$module_file", false, false ); //Do not apply markup/translate as it'll be cached.

		if ( empty ( $module_data['Name'] ) )
			continue;

		$mcms_modules[module_basename( $module_file )] = $module_data;
	}

	uasort( $mcms_modules, '_sort_uname_callback' );

	$cache_modules[ $module_folder ] = $mcms_modules;
	mcms_cache_set('modules', $cache_modules, 'modules');

	return $mcms_modules;
}

/**
 * Check the mu-modules directory and retrieve all mu-module files with any module data.
 *
 * MandarinCMS only includes mu-module files in the base mu-modules directory (mcms-plugins/mu-modules).
 *
 * @since 3.0.0
 * @return array Key is the mu-module file path and the value is an array of the mu-module data.
 */
function get_mu_modules() {
	$mcms_modules = array();
	// Files in mcms-plugins/mu-modules directory
	$module_files = array();

	if ( ! is_dir( MCMSMU_PLUGIN_DIR ) )
		return $mcms_modules;
	if ( $modules_dir = @ opendir( MCMSMU_PLUGIN_DIR ) ) {
		while ( ( $file = readdir( $modules_dir ) ) !== false ) {
			if ( substr( $file, -4 ) == '.php' )
				$module_files[] = $file;
		}
	} else {
		return $mcms_modules;
	}

	@closedir( $modules_dir );

	if ( empty($module_files) )
		return $mcms_modules;

	foreach ( $module_files as $module_file ) {
		if ( !is_readable( MCMSMU_PLUGIN_DIR . "/$module_file" ) )
			continue;

		$module_data = get_module_data( MCMSMU_PLUGIN_DIR . "/$module_file", false, false ); //Do not apply markup/translate as it'll be cached.

		if ( empty ( $module_data['Name'] ) )
			$module_data['Name'] = $module_file;

		$mcms_modules[ $module_file ] = $module_data;
	}

	if ( isset( $mcms_modules['index.php'] ) && filesize( MCMSMU_PLUGIN_DIR . '/index.php') <= 30 ) // silence is golden
		unset( $mcms_modules['index.php'] );

	uasort( $mcms_modules, '_sort_uname_callback' );

	return $mcms_modules;
}

/**
 * Callback to sort array by a 'Name' key.
 *
 * @since 3.1.0
 * @access private
 */
function _sort_uname_callback( $a, $b ) {
	return strnatcasecmp( $a['Name'], $b['Name'] );
}

/**
 * Check the mcms-plugins directory and retrieve all drop-ins with any module data.
 *
 * @since 3.0.0
 * @return array Key is the file path and the value is an array of the module data.
 */
function get_dropins() {
	$dropins = array();
	$module_files = array();

	$_dropins = _get_dropins();

	// These exist in the mcms-plugins directory
	if ( $modules_dir = @ opendir( MCMS_CONTENT_DIR ) ) {
		while ( ( $file = readdir( $modules_dir ) ) !== false ) {
			if ( isset( $_dropins[ $file ] ) )
				$module_files[] = $file;
		}
	} else {
		return $dropins;
	}

	@closedir( $modules_dir );

	if ( empty($module_files) )
		return $dropins;

	foreach ( $module_files as $module_file ) {
		if ( !is_readable( MCMS_CONTENT_DIR . "/$module_file" ) )
			continue;
		$module_data = get_module_data( MCMS_CONTENT_DIR . "/$module_file", false, false ); //Do not apply markup/translate as it'll be cached.
		if ( empty( $module_data['Name'] ) )
			$module_data['Name'] = $module_file;
		$dropins[ $module_file ] = $module_data;
	}

	uksort( $dropins, 'strnatcasecmp' );

	return $dropins;
}

/**
 * Returns drop-ins that MandarinCMS uses.
 *
 * Includes Multisite drop-ins only when is_multisite()
 *
 * @since 3.0.0
 * @return array Key is file name. The value is an array, with the first value the
 *	purpose of the drop-in and the second value the name of the constant that must be
 *	true for the drop-in to be used, or true if no constant is required.
 */
function _get_dropins() {
	$dropins = array(
		'advanced-cache.php' => array( __( 'Advanced caching module.'       ), 'MCMS_CACHE' ), // MCMS_CACHE
		'db.php'             => array( __( 'Custom database class.'         ), true ), // auto on load
		'db-error.php'       => array( __( 'Custom database error message.' ), true ), // auto on error
		'install.php'        => array( __( 'Custom installation script.'    ), true ), // auto on installation
		'maintenance.php'    => array( __( 'Custom maintenance message.'    ), true ), // auto on maintenance
		'object-cache.php'   => array( __( 'External object cache.'         ), true ), // auto on load
	);

	if ( is_multisite() ) {
		$dropins['sunrise.php'       ] = array( __( 'Executed before Multisite is loaded.' ), 'SUNRISE' ); // SUNRISE
		$dropins['blog-deleted.php'  ] = array( __( 'Custom site deleted message.'   ), true ); // auto on deleted blog
		$dropins['blog-inactive.php' ] = array( __( 'Custom site inactive message.'  ), true ); // auto on inactive blog
		$dropins['blog-suspended.php'] = array( __( 'Custom site suspended message.' ), true ); // auto on archived or spammed blog
	}

	return $dropins;
}

/**
 * Check whether a module is active.
 *
 * Only modules installed in the modules/ folder can be active.
 *
 * Modules in the mu-modules/ folder can't be "activated," so this function will
 * return false for those modules.
 *
 * @since 2.5.0
 *
 * @param string $module Path to the main module file from modules directory.
 * @return bool True, if in the active modules list. False, not in the list.
 */
function is_module_active( $module ) {
	return in_array( $module, (array) get_option( 'active_modules', array() ) ) || is_module_active_for_network( $module );
}

/**
 * Check whether the module is inactive.
 *
 * Reverse of is_module_active(). Used as a callback.
 *
 * @since 3.1.0
 * @see is_module_active()
 *
 * @param string $module Path to the main module file from modules directory.
 * @return bool True if inactive. False if active.
 */
function is_module_inactive( $module ) {
	return ! is_module_active( $module );
}

/**
 * Check whether the module is active for the entire network.
 *
 * Only modules installed in the modules/ folder can be active.
 *
 * Modules in the mu-modules/ folder can't be "activated," so this function will
 * return false for those modules.
 *
 * @since 3.0.0
 *
 * @param string $module Path to the main module file from modules directory.
 * @return bool True, if active for the network, otherwise false.
 */
function is_module_active_for_network( $module ) {
	if ( !is_multisite() )
		return false;

	$modules = get_site_option( 'active_sitewide_modules');
	if ( isset($modules[$module]) )
		return true;

	return false;
}

/**
 * Checks for "Network: true" in the module header to see if this should
 * be activated only as a network wide module. The module would also work
 * when Multisite is not enabled.
 *
 * Checks for "Site Wide Only: true" for backward compatibility.
 *
 * @since 3.0.0
 *
 * @param string $module Path to the main module file from modules directory.
 * @return bool True if module is network only, false otherwise.
 */
function is_network_only_module( $module ) {
	$module_data = get_module_data( MCMS_PLUGIN_DIR . '/' . $module );
	if ( $module_data )
		return $module_data['Network'];
	return false;
}

/**
 * Attempts activation of module in a "sandbox" and redirects on success.
 *
 * A module that is already activated will not attempt to be activated again.
 *
 * The way it works is by setting the redirection to the error before trying to
 * include the module file. If the module fails, then the redirection will not
 * be overwritten with the success message. Also, the options will not be
 * updated and the activation hook will not be called on module error.
 *
 * It should be noted that in no way the below code will actually prevent errors
 * within the file. The code should not be used elsewhere to replicate the
 * "sandbox", which uses redirection to work.
 * {@source 13 1}
 *
 * If any errors are found or text is outputted, then it will be captured to
 * ensure that the success redirection will update the error redirection.
 *
 * @since 2.5.0
 *
 * @param string $module       Path to the main module file from modules directory.
 * @param string $redirect     Optional. URL to redirect to.
 * @param bool   $network_wide Optional. Whether to enable the module for all sites in the network
 *                             or just the current site. Multisite only. Default false.
 * @param bool   $silent       Optional. Whether to prevent calling activation hooks. Default false.
 * @return MCMS_Error|null MCMS_Error on invalid file or null on success.
 */
function activate_module( $module, $redirect = '', $network_wide = false, $silent = false ) {
	$module = module_basename( trim( $module ) );

	if ( is_multisite() && ( $network_wide || is_network_only_module($module) ) ) {
		$network_wide = true;
		$current = get_site_option( 'active_sitewide_modules', array() );
		$_GET['networkwide'] = 1; // Back compat for modules looking for this value.
	} else {
		$current = get_option( 'active_modules', array() );
	}

	$valid = validate_module($module);
	if ( is_mcms_error($valid) )
		return $valid;

	if ( ( $network_wide && ! isset( $current[ $module ] ) ) || ( ! $network_wide && ! in_array( $module, $current ) ) ) {
		if ( !empty($redirect) )
			mcms_redirect(add_query_arg('_error_nonce', mcms_create_nonce('module-activation-error_' . $module), $redirect)); // we'll override this later if the module can be included without fatal error
		ob_start();
		mcms_register_module_realpath( MCMS_PLUGIN_DIR . '/' . $module );
		$_mcms_module_file = $module;
		include_once( MCMS_PLUGIN_DIR . '/' . $module );
		$module = $_mcms_module_file; // Avoid stomping of the $module variable in a module.

		if ( ! $silent ) {
			/**
			 * Fires before a module is activated.
			 *
			 * If a module is silently activated (such as during an update),
			 * this hook does not fire.
			 *
			 * @since 2.9.0
			 *
			 * @param string $module       Path to the main module file from modules directory.
			 * @param bool   $network_wide Whether to enable the module for all sites in the network
			 *                             or just the current site. Multisite only. Default is false.
			 */
			do_action( 'activate_module', $module, $network_wide );

			/**
			 * Fires as a specific module is being activated.
			 *
			 * This hook is the "activation" hook used internally by register_activation_hook().
			 * The dynamic portion of the hook name, `$module`, refers to the module basename.
			 *
			 * If a module is silently activated (such as during an update), this hook does not fire.
			 *
			 * @since 2.0.0
			 *
			 * @param bool $network_wide Whether to enable the module for all sites in the network
			 *                           or just the current site. Multisite only. Default is false.
			 */
			do_action( "activate_{$module}", $network_wide );
		}

		if ( $network_wide ) {
			$current = get_site_option( 'active_sitewide_modules', array() );
			$current[$module] = time();
			update_site_option( 'active_sitewide_modules', $current );
		} else {
			$current = get_option( 'active_modules', array() );
			$current[] = $module;
			sort($current);
			update_option('active_modules', $current);
		}

		if ( ! $silent ) {
			/**
			 * Fires after a module has been activated.
			 *
			 * If a module is silently activated (such as during an update),
			 * this hook does not fire.
			 *
			 * @since 2.9.0
			 *
			 * @param string $module       Path to the main module file from modules directory.
			 * @param bool   $network_wide Whether to enable the module for all sites in the network
			 *                             or just the current site. Multisite only. Default is false.
			 */
			do_action( 'activated_module', $module, $network_wide );
		}

		if ( ob_get_length() > 0 ) {
			$output = ob_get_clean();
			return new MCMS_Error('unexpected_output', __('The module generated unexpected output.'), $output);
		}
		ob_end_clean();
	}

	return null;
}

/**
 * Deactivate a single module or multiple modules.
 *
 * The deactivation hook is disabled by the module upgrader by using the $silent
 * parameter.
 *
 * @since 2.5.0
 *
 * @param string|array $modules Single module or list of modules to deactivate.
 * @param bool $silent Prevent calling deactivation hooks. Default is false.
 * @param mixed $network_wide Whether to deactivate the module for all sites in the network.
 * 	A value of null (the default) will deactivate modules for both the site and the network.
 */
function deactivate_modules( $modules, $silent = false, $network_wide = null ) {
	if ( is_multisite() )
		$network_current = get_site_option( 'active_sitewide_modules', array() );
	$current = get_option( 'active_modules', array() );
	$do_blog = $do_network = false;

	foreach ( (array) $modules as $module ) {
		$module = module_basename( trim( $module ) );
		if ( ! is_module_active($module) )
			continue;

		$network_deactivating = false !== $network_wide && is_module_active_for_network( $module );

		if ( ! $silent ) {
			/**
			 * Fires before a module is deactivated.
			 *
			 * If a module is silently deactivated (such as during an update),
			 * this hook does not fire.
			 *
			 * @since 2.9.0
			 *
			 * @param string $module               Path to the main module file from modules directory.
			 * @param bool   $network_deactivating Whether the module is deactivated for all sites in the network
			 *                                     or just the current site. Multisite only. Default is false.
			 */
			do_action( 'deactivate_module', $module, $network_deactivating );
		}

		if ( false !== $network_wide ) {
			if ( is_module_active_for_network( $module ) ) {
				$do_network = true;
				unset( $network_current[ $module ] );
			} elseif ( $network_wide ) {
				continue;
			}
		}

		if ( true !== $network_wide ) {
			$key = array_search( $module, $current );
			if ( false !== $key ) {
				$do_blog = true;
				unset( $current[ $key ] );
			}
		}

		if ( ! $silent ) {
			/**
			 * Fires as a specific module is being deactivated.
			 *
			 * This hook is the "deactivation" hook used internally by register_deactivation_hook().
			 * The dynamic portion of the hook name, `$module`, refers to the module basename.
			 *
			 * If a module is silently deactivated (such as during an update), this hook does not fire.
			 *
			 * @since 2.0.0
			 *
			 * @param bool $network_deactivating Whether the module is deactivated for all sites in the network
			 *                                   or just the current site. Multisite only. Default is false.
			 */
			do_action( "deactivate_{$module}", $network_deactivating );

			/**
			 * Fires after a module is deactivated.
			 *
			 * If a module is silently deactivated (such as during an update),
			 * this hook does not fire.
			 *
			 * @since 2.9.0
			 *
			 * @param string $module               Path to the main module file from modules directory.
			 * @param bool   $network_deactivating Whether the module is deactivated for all sites in the network.
			 *                                     or just the current site. Multisite only. Default false.
			 */
			do_action( 'deactivated_module', $module, $network_deactivating );
		}
	}

	if ( $do_blog )
		update_option('active_modules', $current);
	if ( $do_network )
		update_site_option( 'active_sitewide_modules', $network_current );
}

/**
 * Activate multiple modules.
 *
 * When MCMS_Error is returned, it does not mean that one of the modules had
 * errors. It means that one or more of the modules file path was invalid.
 *
 * The execution will be halted as soon as one of the modules has an error.
 *
 * @since 2.6.0
 *
 * @param string|array $modules Single module or list of modules to activate.
 * @param string $redirect Redirect to page after successful activation.
 * @param bool $network_wide Whether to enable the module for all sites in the network.
 * @param bool $silent Prevent calling activation hooks. Default is false.
 * @return bool|MCMS_Error True when finished or MCMS_Error if there were errors during a module activation.
 */
function activate_modules( $modules, $redirect = '', $network_wide = false, $silent = false ) {
	if ( !is_array($modules) )
		$modules = array($modules);

	$errors = array();
	foreach ( $modules as $module ) {
		if ( !empty($redirect) )
			$redirect = add_query_arg('module', $module, $redirect);
		$result = activate_module($module, $redirect, $network_wide, $silent);
		if ( is_mcms_error($result) )
			$errors[$module] = $result;
	}

	if ( !empty($errors) )
		return new MCMS_Error('modules_invalid', __('One of the modules is invalid.'), $errors);

	return true;
}

/**
 * Remove directory and files of a module for a list of modules.
 *
 * @since 2.6.0
 *
 * @global MCMS_Filesystem_Base $mcms_filesystem
 *
 * @param array  $modules    List of modules to delete.
 * @param string $deprecated Deprecated.
 * @return bool|null|MCMS_Error True on success, false is $modules is empty, MCMS_Error on failure.
 *                            Null if filesystem credentials are required to proceed.
 */
function delete_modules( $modules, $deprecated = '' ) {
	global $mcms_filesystem;

	if ( empty($modules) )
		return false;

	$checked = array();
	foreach ( $modules as $module )
		$checked[] = 'checked[]=' . $module;

	$url = mcms_nonce_url('modules.php?action=delete-selected&verify-delete=1&' . implode('&', $checked), 'bulk-modules');

	ob_start();
	$credentials = request_filesystem_credentials( $url );
	$data = ob_get_clean();

	if ( false === $credentials ) {
		if ( ! empty($data) ){
			include_once( BASED_TREE_URI . 'mcms-admin/admin-header.php');
			echo $data;
			include( BASED_TREE_URI . 'mcms-admin/admin-footer.php');
			exit;
		}
		return;
	}

	if ( ! MCMS_Filesystem( $credentials ) ) {
		ob_start();
		request_filesystem_credentials( $url, '', true ); // Failed to connect, Error and request again.
		$data = ob_get_clean();

		if ( ! empty($data) ){
			include_once( BASED_TREE_URI . 'mcms-admin/admin-header.php');
			echo $data;
			include( BASED_TREE_URI . 'mcms-admin/admin-footer.php');
			exit;
		}
		return;
	}

	if ( ! is_object($mcms_filesystem) )
		return new MCMS_Error('fs_unavailable', __('Could not access filesystem.'));

	if ( is_mcms_error($mcms_filesystem->errors) && $mcms_filesystem->errors->get_error_code() )
		return new MCMS_Error('fs_error', __('Filesystem error.'), $mcms_filesystem->errors);

	// Get the base module folder.
	$modules_dir = $mcms_filesystem->mcms_modules_dir();
	if ( empty( $modules_dir ) ) {
		return new MCMS_Error( 'fs_no_modules_dir', __( 'Unable to locate MandarinCMS module directory.' ) );
	}

	$modules_dir = trailingslashit( $modules_dir );

	$module_translations = mcms_get_installed_translations( 'modules' );

	$errors = array();

	foreach ( $modules as $module_file ) {
		// Run Uninstall hook.
		if ( is_uninstallable_module( $module_file ) ) {
			uninstall_module($module_file);
		}

		/**
		 * Fires immediately before a module deletion attempt.
		 *
		 * @since 4.4.0
		 *
		 * @param string $module_file Module file name.
		 */
		do_action( 'delete_module', $module_file );

		$this_module_dir = trailingslashit( dirname( $modules_dir . $module_file ) );

		// If module is in its own directory, recursively delete the directory.
		if ( strpos( $module_file, '/' ) && $this_module_dir != $modules_dir ) { //base check on if module includes directory separator AND that it's not the root module folder
			$deleted = $mcms_filesystem->delete( $this_module_dir, true );
		} else {
			$deleted = $mcms_filesystem->delete( $modules_dir . $module_file );
		}

		/**
		 * Fires immediately after a module deletion attempt.
		 *
		 * @since 4.4.0
		 *
		 * @param string $module_file Module file name.
		 * @param bool   $deleted     Whether the module deletion was successful.
		 */
		do_action( 'deleted_module', $module_file, $deleted );

		if ( ! $deleted ) {
			$errors[] = $module_file;
			continue;
		}

		// Remove language files, silently.
		$module_slug = dirname( $module_file );
		if ( '.' !== $module_slug && ! empty( $module_translations[ $module_slug ] ) ) {
			$translations = $module_translations[ $module_slug ];

			foreach ( $translations as $translation => $data ) {
				$mcms_filesystem->delete( MCMS_LANG_DIR . '/modules/' . $module_slug . '-' . $translation . '.po' );
				$mcms_filesystem->delete( MCMS_LANG_DIR . '/modules/' . $module_slug . '-' . $translation . '.mo' );
			}
		}
	}

	// Remove deleted modules from the module updates list.
	if ( $current = get_site_transient('update_modules') ) {
		// Don't remove the modules that weren't deleted.
		$deleted = array_diff( $modules, $errors );

		foreach ( $deleted as $module_file ) {
			unset( $current->response[ $module_file ] );
		}

		set_site_transient( 'update_modules', $current );
	}

	if ( ! empty( $errors ) ) {
		if ( 1 === count( $errors ) ) {
			/* translators: %s: module filename */
			$message = __( 'Could not fully remove the module %s.' );
		} else {
			/* translators: %s: comma-separated list of module filenames */
			$message = __( 'Could not fully remove the modules %s.' );
		}

		return new MCMS_Error( 'could_not_remove_module', sprintf( $message, implode( ', ', $errors ) ) );
	}

	return true;
}

/**
 * Validate active modules
 *
 * Validate all active modules, deactivates invalid and
 * returns an array of deactivated ones.
 *
 * @since 2.5.0
 * @return array invalid modules, module as key, error as value
 */
function validate_active_modules() {
	$modules = get_option( 'active_modules', array() );
	// Validate vartype: array.
	if ( ! is_array( $modules ) ) {
		update_option( 'active_modules', array() );
		$modules = array();
	}

	if ( is_multisite() && current_user_can( 'manage_network_modules' ) ) {
		$network_modules = (array) get_site_option( 'active_sitewide_modules', array() );
		$modules = array_merge( $modules, array_keys( $network_modules ) );
	}

	if ( empty( $modules ) )
		return array();

	$invalid = array();

	// Invalid modules get deactivated.
	foreach ( $modules as $module ) {
		$result = validate_module( $module );
		if ( is_mcms_error( $result ) ) {
			$invalid[$module] = $result;
			deactivate_modules( $module, true );
		}
	}
	return $invalid;
}

/**
 * Validate the module path.
 *
 * Checks that the main module file exists and is a valid module. See validate_file().
 *
 * @since 2.5.0
 *
 * @param string $module Path to the main module file from modules directory.
 * @return MCMS_Error|int 0 on success, MCMS_Error on failure.
 */
function validate_module($module) {
	if ( validate_file($module) )
		return new MCMS_Error('module_invalid', __('Invalid module path.'));
	if ( ! file_exists(MCMS_PLUGIN_DIR . '/' . $module) )
		return new MCMS_Error('module_not_found', __('Module file does not exist.'));

	$installed_modules = get_modules();
	if ( ! isset($installed_modules[$module]) )
		return new MCMS_Error('no_module_header', __('The module does not have a valid header.'));
	return 0;
}

/**
 * Whether the module can be uninstalled.
 *
 * @since 2.7.0
 *
 * @param string $module Path to the main module file from modules directory.
 * @return bool Whether module can be uninstalled.
 */
function is_uninstallable_module($module) {
	$file = module_basename($module);

	$uninstallable_modules = (array) get_option('uninstall_modules');
	if ( isset( $uninstallable_modules[$file] ) || file_exists( MCMS_PLUGIN_DIR . '/' . dirname($file) . '/uninstall.php' ) )
		return true;

	return false;
}

/**
 * Uninstall a single module.
 *
 * Calls the uninstall hook, if it is available.
 *
 * @since 2.7.0
 *
 * @param string $module Path to the main module file from modules directory.
 * @return true True if a module's uninstall.php file has been found and included.
 */
function uninstall_module($module) {
	$file = module_basename($module);

	$uninstallable_modules = (array) get_option('uninstall_modules');

	/**
	 * Fires in uninstall_module() immediately before the module is uninstalled.
	 *
	 * @since 4.5.0
	 *
	 * @param string $module                Path to the main module file from modules directory.
	 * @param array  $uninstallable_modules Uninstallable modules.
	 */
	do_action( 'pre_uninstall_module', $module, $uninstallable_modules );

	if ( file_exists( MCMS_PLUGIN_DIR . '/' . dirname($file) . '/uninstall.php' ) ) {
		if ( isset( $uninstallable_modules[$file] ) ) {
			unset($uninstallable_modules[$file]);
			update_option('uninstall_modules', $uninstallable_modules);
		}
		unset($uninstallable_modules);

		define('MCMS_UNINSTALL_PLUGIN', $file);
		mcms_register_module_realpath( MCMS_PLUGIN_DIR . '/' . $file );
		include( MCMS_PLUGIN_DIR . '/' . dirname($file) . '/uninstall.php' );

		return true;
	}

	if ( isset( $uninstallable_modules[$file] ) ) {
		$callable = $uninstallable_modules[$file];
		unset($uninstallable_modules[$file]);
		update_option('uninstall_modules', $uninstallable_modules);
		unset($uninstallable_modules);

		mcms_register_module_realpath( MCMS_PLUGIN_DIR . '/' . $file );
		include( MCMS_PLUGIN_DIR . '/' . $file );

		add_action( "uninstall_{$file}", $callable );

		/**
		 * Fires in uninstall_module() once the module has been uninstalled.
		 *
		 * The action concatenates the 'uninstall_' prefix with the basename of the
		 * module passed to uninstall_module() to create a dynamically-named action.
		 *
		 * @since 2.7.0
		 */
		do_action( "uninstall_{$file}" );
	}
}

//
// Menu
//

/**
 * Add a top-level menu page.
 *
 * This function takes a capability which will be used to determine whether
 * or not a page is included in the menu.
 *
 * The function which is hooked in to handle the output of the page must check
 * that the user has the required capability as well.
 *
 * @global array $menu
 * @global array $admin_page_hooks
 * @global array $_registered_pages
 * @global array $_parent_pages
 *
 * @param string   $page_title The text to be displayed in the title tags of the page when the menu is selected.
 * @param string   $menu_title The text to be used for the menu.
 * @param string   $capability The capability required for this menu to be displayed to the user.
 * @param string   $menu_slug  The slug name to refer to this menu by. Should be unique for this menu page and only
 *                             include lowercase alphanumeric, dashes, and underscores characters to be compatible
 *                             with sanitize_key().
 * @param callable $function   The function to be called to output the content for this page.
 * @param string   $icon_url   The URL to the icon to be used for this menu.
 *                             * Pass a base64-encoded SVG using a data URI, which will be colored to match
 *                               the color scheme. This should begin with 'data:image/svg+xml;base64,'.
 *                             * Pass the name of a Dashicons helper class to use a font icon,
 *                               e.g. 'dashicons-chart-pie'.
 *                             * Pass 'none' to leave div.mcms-menu-image empty so an icon can be added via CSS.
 * @param int      $position   The position in the menu order this one should appear.
 * @return string The resulting page's hook_suffix.
 */
function add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function = '', $icon_url = '', $position = null ) {
	global $menu, $admin_page_hooks, $_registered_pages, $_parent_pages;

	$menu_slug = module_basename( $menu_slug );

	$admin_page_hooks[$menu_slug] = sanitize_title( $menu_title );

	$hookname = get_module_page_hookname( $menu_slug, '' );

	if ( !empty( $function ) && !empty( $hookname ) && current_user_can( $capability ) )
		add_action( $hookname, $function );

	if ( empty($icon_url) ) {
		$icon_url = 'dashicons-admin-generic';
		$icon_class = 'menu-icon-generic ';
	} else {
		$icon_url = set_url_scheme( $icon_url );
		$icon_class = '';
	}

	$new_menu = array( $menu_title, $capability, $menu_slug, $page_title, 'menu-top ' . $icon_class . $hookname, $hookname, $icon_url );

	if ( null === $position ) {
		$menu[] = $new_menu;
	} elseif ( isset( $menu[ "$position" ] ) ) {
	 	$position = $position + substr( base_convert( md5( $menu_slug . $menu_title ), 16, 10 ) , -5 ) * 0.00001;
		$menu[ "$position" ] = $new_menu;
	} else {
		$menu[ $position ] = $new_menu;
	}

	$_registered_pages[$hookname] = true;

	// No parent as top level
	$_parent_pages[$menu_slug] = false;

	return $hookname;
}

/**
 * Add a submenu page.
 *
 * This function takes a capability which will be used to determine whether
 * or not a page is included in the menu.
 *
 * The function which is hooked in to handle the output of the page must check
 * that the user has the required capability as well.
 *
 * @global array $submenu
 * @global array $menu
 * @global array $_mcms_real_parent_file
 * @global bool  $_mcms_submenu_nopriv
 * @global array $_registered_pages
 * @global array $_parent_pages
 *
 * @param string   $parent_slug The slug name for the parent menu (or the file name of a standard
 *                              MandarinCMS admin page).
 * @param string   $page_title  The text to be displayed in the title tags of the page when the menu
 *                              is selected.
 * @param string   $menu_title  The text to be used for the menu.
 * @param string   $capability  The capability required for this menu to be displayed to the user.
 * @param string   $menu_slug   The slug name to refer to this menu by. Should be unique for this menu
 *                              and only include lowercase alphanumeric, dashes, and underscores characters
 *                              to be compatible with sanitize_key().
 * @param callable $function    The function to be called to output the content for this page.
 * @return false|string The resulting page's hook_suffix, or false if the user does not have the capability required.
 */
function add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function = '' ) {
	global $submenu, $menu, $_mcms_real_parent_file, $_mcms_submenu_nopriv,
		$_registered_pages, $_parent_pages;

	$menu_slug = module_basename( $menu_slug );
	$parent_slug = module_basename( $parent_slug);

	if ( isset( $_mcms_real_parent_file[$parent_slug] ) )
		$parent_slug = $_mcms_real_parent_file[$parent_slug];

	if ( !current_user_can( $capability ) ) {
		$_mcms_submenu_nopriv[$parent_slug][$menu_slug] = true;
		return false;
	}

	/*
	 * If the parent doesn't already have a submenu, add a link to the parent
	 * as the first item in the submenu. If the submenu file is the same as the
	 * parent file someone is trying to link back to the parent manually. In
	 * this case, don't automatically add a link back to avoid duplication.
	 */
	if (!isset( $submenu[$parent_slug] ) && $menu_slug != $parent_slug ) {
		foreach ( (array)$menu as $parent_menu ) {
			if ( $parent_menu[2] == $parent_slug && current_user_can( $parent_menu[1] ) )
				$submenu[$parent_slug][] = array_slice( $parent_menu, 0, 4 );
		}
	}

	$submenu[$parent_slug][] = array ( $menu_title, $capability, $menu_slug, $page_title );

	$hookname = get_module_page_hookname( $menu_slug, $parent_slug);
	if (!empty ( $function ) && !empty ( $hookname ))
		add_action( $hookname, $function );

	$_registered_pages[$hookname] = true;

	/*
	 * Backward-compatibility for modules using add_management page.
	 * See mcms-admin/admin.php for redirect from edit.php to tools.php
	 */
	if ( 'tools.php' == $parent_slug )
		$_registered_pages[get_module_page_hookname( $menu_slug, 'edit.php')] = true;

	// No parent as top level.
	$_parent_pages[$menu_slug] = $parent_slug;

	return $hookname;
}

/**
 * Add submenu page to the Tools main menu.
 *
 * This function takes a capability which will be used to determine whether
 * or not a page is included in the menu.
 *
 * The function which is hooked in to handle the output of the page must check
 * that the user has the required capability as well.
 *
 * @param string   $page_title The text to be displayed in the title tags of the page when the menu is selected.
 * @param string   $menu_title The text to be used for the menu.
 * @param string   $capability The capability required for this menu to be displayed to the user.
 * @param string   $menu_slug  The slug name to refer to this menu by (should be unique for this menu).
 * @param callable $function   The function to be called to output the content for this page.
 * @return false|string The resulting page's hook_suffix, or false if the user does not have the capability required.
 */
function add_management_page( $page_title, $menu_title, $capability, $menu_slug, $function = '' ) {
	return add_submenu_page( 'tools.php', $page_title, $menu_title, $capability, $menu_slug, $function );
}

/**
 * Add submenu page to the Settings main menu.
 *
 * This function takes a capability which will be used to determine whether
 * or not a page is included in the menu.
 *
 * The function which is hooked in to handle the output of the page must check
 * that the user has the required capability as well.
 *
 * @param string   $page_title The text to be displayed in the title tags of the page when the menu is selected.
 * @param string   $menu_title The text to be used for the menu.
 * @param string   $capability The capability required for this menu to be displayed to the user.
 * @param string   $menu_slug  The slug name to refer to this menu by (should be unique for this menu).
 * @param callable $function   The function to be called to output the content for this page.
 * @return false|string The resulting page's hook_suffix, or false if the user does not have the capability required.
 */
function add_options_page( $page_title, $menu_title, $capability, $menu_slug, $function = '' ) {
	return add_submenu_page( 'options-general.php', $page_title, $menu_title, $capability, $menu_slug, $function );
}

/**
 * Add submenu page to the Dexign main menu.
 *
 * This function takes a capability which will be used to determine whether
 * or not a page is included in the menu.
 *
 * The function which is hooked in to handle the output of the page must check
 * that the user has the required capability as well.
 *
 * @param string   $page_title The text to be displayed in the title tags of the page when the menu is selected.
 * @param string   $menu_title The text to be used for the menu.
 * @param string   $capability The capability required for this menu to be displayed to the user.
 * @param string   $menu_slug  The slug name to refer to this menu by (should be unique for this menu).
 * @param callable $function   The function to be called to output the content for this page.
 * @return false|string The resulting page's hook_suffix, or false if the user does not have the capability required.
 */
function add_myskin_page( $page_title, $menu_title, $capability, $menu_slug, $function = '' ) {
	return add_submenu_page( 'myskins.php', $page_title, $menu_title, $capability, $menu_slug, $function );
}

/**
 * Add submenu page to the Modules main menu.
 *
 * This function takes a capability which will be used to determine whether
 * or not a page is included in the menu.
 *
 * The function which is hooked in to handle the output of the page must check
 * that the user has the required capability as well.
 *
 * @param string   $page_title The text to be displayed in the title tags of the page when the menu is selected.
 * @param string   $menu_title The text to be used for the menu.
 * @param string   $capability The capability required for this menu to be displayed to the user.
 * @param string   $menu_slug  The slug name to refer to this menu by (should be unique for this menu).
 * @param callable $function   The function to be called to output the content for this page.
 * @return false|string The resulting page's hook_suffix, or false if the user does not have the capability required.
 */
function add_modules_page( $page_title, $menu_title, $capability, $menu_slug, $function = '' ) {
	return add_submenu_page( 'modules.php', $page_title, $menu_title, $capability, $menu_slug, $function );
}

/**
 * Add submenu page to the Users/Profile main menu.
 *
 * This function takes a capability which will be used to determine whether
 * or not a page is included in the menu.
 *
 * The function which is hooked in to handle the output of the page must check
 * that the user has the required capability as well.
 *
 * @param string   $page_title The text to be displayed in the title tags of the page when the menu is selected.
 * @param string   $menu_title The text to be used for the menu.
 * @param string   $capability The capability required for this menu to be displayed to the user.
 * @param string   $menu_slug  The slug name to refer to this menu by (should be unique for this menu).
 * @param callable $function   The function to be called to output the content for this page.
 * @return false|string The resulting page's hook_suffix, or false if the user does not have the capability required.
 */
function add_users_page( $page_title, $menu_title, $capability, $menu_slug, $function = '' ) {
	if ( current_user_can('edit_users') )
		$parent = 'users.php';
	else
		$parent = 'profile.php';
	return add_submenu_page( $parent, $page_title, $menu_title, $capability, $menu_slug, $function );
}
/**
 * Add submenu page to the Dashboard main menu.
 *
 * This function takes a capability which will be used to determine whether
 * or not a page is included in the menu.
 *
 * The function which is hooked in to handle the output of the page must check
 * that the user has the required capability as well.
 *
 * @param string   $page_title The text to be displayed in the title tags of the page when the menu is selected.
 * @param string   $menu_title The text to be used for the menu.
 * @param string   $capability The capability required for this menu to be displayed to the user.
 * @param string   $menu_slug  The slug name to refer to this menu by (should be unique for this menu).
 * @param callable $function   The function to be called to output the content for this page.
 * @return false|string The resulting page's hook_suffix, or false if the user does not have the capability required.
 */
function add_dashboard_page( $page_title, $menu_title, $capability, $menu_slug, $function = '' ) {
	return add_submenu_page( 'index.php', $page_title, $menu_title, $capability, $menu_slug, $function );
}

/**
 * Add submenu page to the Posts main menu.
 *
 * This function takes a capability which will be used to determine whether
 * or not a page is included in the menu.
 *
 * The function which is hooked in to handle the output of the page must check
 * that the user has the required capability as well.
 *
 * @param string   $page_title The text to be displayed in the title tags of the page when the menu is selected.
 * @param string   $menu_title The text to be used for the menu.
 * @param string   $capability The capability required for this menu to be displayed to the user.
 * @param string   $menu_slug  The slug name to refer to this menu by (should be unique for this menu).
 * @param callable $function   The function to be called to output the content for this page.
 * @return false|string The resulting page's hook_suffix, or false if the user does not have the capability required.
 */
function add_posts_page( $page_title, $menu_title, $capability, $menu_slug, $function = '' ) {
	return add_submenu_page( 'edit.php', $page_title, $menu_title, $capability, $menu_slug, $function );
}

/**
 * Add submenu page to the Media main menu.
 *
 * This function takes a capability which will be used to determine whether
 * or not a page is included in the menu.
 *
 * The function which is hooked in to handle the output of the page must check
 * that the user has the required capability as well.
 *
 * @param string   $page_title The text to be displayed in the title tags of the page when the menu is selected.
 * @param string   $menu_title The text to be used for the menu.
 * @param string   $capability The capability required for this menu to be displayed to the user.
 * @param string   $menu_slug  The slug name to refer to this menu by (should be unique for this menu).
 * @param callable $function   The function to be called to output the content for this page.
 * @return false|string The resulting page's hook_suffix, or false if the user does not have the capability required.
 */
function add_media_page( $page_title, $menu_title, $capability, $menu_slug, $function = '' ) {
	return add_submenu_page( 'upload.php', $page_title, $menu_title, $capability, $menu_slug, $function );
}

/**
 * Add submenu page to the Links main menu.
 *
 * This function takes a capability which will be used to determine whether
 * or not a page is included in the menu.
 *
 * The function which is hooked in to handle the output of the page must check
 * that the user has the required capability as well.
 *
 * @param string   $page_title The text to be displayed in the title tags of the page when the menu is selected.
 * @param string   $menu_title The text to be used for the menu.
 * @param string   $capability The capability required for this menu to be displayed to the user.
 * @param string   $menu_slug  The slug name to refer to this menu by (should be unique for this menu).
 * @param callable $function   The function to be called to output the content for this page.
 * @return false|string The resulting page's hook_suffix, or false if the user does not have the capability required.
 */
function add_links_page( $page_title, $menu_title, $capability, $menu_slug, $function = '' ) {
	return add_submenu_page( 'link-manager.php', $page_title, $menu_title, $capability, $menu_slug, $function );
}

/**
 * Add submenu page to the Pages main menu.
 *
 * This function takes a capability which will be used to determine whether
 * or not a page is included in the menu.
 *
 * The function which is hooked in to handle the output of the page must check
 * that the user has the required capability as well.
 *
 * @param string   $page_title The text to be displayed in the title tags of the page when the menu is selected.
 * @param string   $menu_title The text to be used for the menu.
 * @param string   $capability The capability required for this menu to be displayed to the user.
 * @param string   $menu_slug  The slug name to refer to this menu by (should be unique for this menu).
 * @param callable $function   The function to be called to output the content for this page.
 * @return false|string The resulting page's hook_suffix, or false if the user does not have the capability required.
 */
function add_pages_page( $page_title, $menu_title, $capability, $menu_slug, $function = '' ) {
	return add_submenu_page( 'edit.php?post_type=page', $page_title, $menu_title, $capability, $menu_slug, $function );
}

/**
 * Add submenu page to the Comments main menu.
 *
 * This function takes a capability which will be used to determine whether
 * or not a page is included in the menu.
 *
 * The function which is hooked in to handle the output of the page must check
 * that the user has the required capability as well.
 *
 * @param string   $page_title The text to be displayed in the title tags of the page when the menu is selected.
 * @param string   $menu_title The text to be used for the menu.
 * @param string   $capability The capability required for this menu to be displayed to the user.
 * @param string   $menu_slug  The slug name to refer to this menu by (should be unique for this menu).
 * @param callable $function   The function to be called to output the content for this page.
 * @return false|string The resulting page's hook_suffix, or false if the user does not have the capability required.
 */
function add_comments_page( $page_title, $menu_title, $capability, $menu_slug, $function = '' ) {
	return add_submenu_page( 'edit-comments.php', $page_title, $menu_title, $capability, $menu_slug, $function );
}

/**
 * Remove a top-level admin menu.
 *
 * @since 3.1.0
 *
 * @global array $menu
 *
 * @param string $menu_slug The slug of the menu.
 * @return array|bool The removed menu on success, false if not found.
 */
function remove_menu_page( $menu_slug ) {
	global $menu;

	foreach ( $menu as $i => $item ) {
		if ( $menu_slug == $item[2] ) {
			unset( $menu[$i] );
			return $item;
		}
	}

	return false;
}

/**
 * Remove an admin submenu.
 *
 * @since 3.1.0
 *
 * @global array $submenu
 *
 * @param string $menu_slug    The slug for the parent menu.
 * @param string $submenu_slug The slug of the submenu.
 * @return array|bool The removed submenu on success, false if not found.
 */
function remove_submenu_page( $menu_slug, $submenu_slug ) {
	global $submenu;

	if ( !isset( $submenu[$menu_slug] ) )
		return false;

	foreach ( $submenu[$menu_slug] as $i => $item ) {
		if ( $submenu_slug == $item[2] ) {
			unset( $submenu[$menu_slug][$i] );
			return $item;
		}
	}

	return false;
}

/**
 * Get the url to access a particular menu page based on the slug it was registered with.
 *
 * If the slug hasn't been registered properly no url will be returned
 *
 * @since 3.0.0
 *
 * @global array $_parent_pages
 *
 * @param string $menu_slug The slug name to refer to this menu by (should be unique for this menu)
 * @param bool $echo Whether or not to echo the url - default is true
 * @return string the url
 */
function menu_page_url($menu_slug, $echo = true) {
	global $_parent_pages;

	if ( isset( $_parent_pages[$menu_slug] ) ) {
		$parent_slug = $_parent_pages[$menu_slug];
		if ( $parent_slug && ! isset( $_parent_pages[$parent_slug] ) ) {
			$url = admin_url( add_query_arg( 'page', $menu_slug, $parent_slug ) );
		} else {
			$url = admin_url( 'admin.php?page=' . $menu_slug );
		}
	} else {
		$url = '';
	}

	$url = esc_url($url);

	if ( $echo )
		echo $url;

	return $url;
}

//
// Pluggable Menu Support -- Private
//
/**
 *
 * @global string $parent_file
 * @global array $menu
 * @global array $submenu
 * @global string $pagenow
 * @global string $typenow
 * @global string $module_page
 * @global array $_mcms_real_parent_file
 * @global array $_mcms_menu_nopriv
 * @global array $_mcms_submenu_nopriv
 */
function get_admin_page_parent( $parent = '' ) {
	global $parent_file, $menu, $submenu, $pagenow, $typenow,
		$module_page, $_mcms_real_parent_file, $_mcms_menu_nopriv, $_mcms_submenu_nopriv;

	if ( !empty ( $parent ) && 'admin.php' != $parent ) {
		if ( isset( $_mcms_real_parent_file[$parent] ) )
			$parent = $_mcms_real_parent_file[$parent];
		return $parent;
	}

	if ( $pagenow == 'admin.php' && isset( $module_page ) ) {
		foreach ( (array)$menu as $parent_menu ) {
			if ( $parent_menu[2] == $module_page ) {
				$parent_file = $module_page;
				if ( isset( $_mcms_real_parent_file[$parent_file] ) )
					$parent_file = $_mcms_real_parent_file[$parent_file];
				return $parent_file;
			}
		}
		if ( isset( $_mcms_menu_nopriv[$module_page] ) ) {
			$parent_file = $module_page;
			if ( isset( $_mcms_real_parent_file[$parent_file] ) )
					$parent_file = $_mcms_real_parent_file[$parent_file];
			return $parent_file;
		}
	}

	if ( isset( $module_page ) && isset( $_mcms_submenu_nopriv[$pagenow][$module_page] ) ) {
		$parent_file = $pagenow;
		if ( isset( $_mcms_real_parent_file[$parent_file] ) )
			$parent_file = $_mcms_real_parent_file[$parent_file];
		return $parent_file;
	}

	foreach (array_keys( (array)$submenu ) as $parent) {
		foreach ( $submenu[$parent] as $submenu_array ) {
			if ( isset( $_mcms_real_parent_file[$parent] ) )
				$parent = $_mcms_real_parent_file[$parent];
			if ( !empty($typenow) && ($submenu_array[2] == "$pagenow?post_type=$typenow") ) {
				$parent_file = $parent;
				return $parent;
			} elseif ( $submenu_array[2] == $pagenow && empty($typenow) && ( empty($parent_file) || false === strpos($parent_file, '?') ) ) {
				$parent_file = $parent;
				return $parent;
			} elseif ( isset( $module_page ) && ($module_page == $submenu_array[2] ) ) {
				$parent_file = $parent;
				return $parent;
			}
		}
	}

	if ( empty($parent_file) )
		$parent_file = '';
	return '';
}

/**
 *
 * @global string $title
 * @global array $menu
 * @global array $submenu
 * @global string $pagenow
 * @global string $module_page
 * @global string $typenow
 */
function get_admin_page_title() {
	global $title, $menu, $submenu, $pagenow, $module_page, $typenow;

	if ( ! empty ( $title ) )
		return $title;

	$hook = get_module_page_hook( $module_page, $pagenow );

	$parent = $parent1 = get_admin_page_parent();

	if ( empty ( $parent) ) {
		foreach ( (array)$menu as $menu_array ) {
			if ( isset( $menu_array[3] ) ) {
				if ( $menu_array[2] == $pagenow ) {
					$title = $menu_array[3];
					return $menu_array[3];
				} elseif ( isset( $module_page ) && ($module_page == $menu_array[2] ) && ($hook == $menu_array[3] ) ) {
					$title = $menu_array[3];
					return $menu_array[3];
				}
			} else {
				$title = $menu_array[0];
				return $title;
			}
		}
	} else {
		foreach ( array_keys( $submenu ) as $parent ) {
			foreach ( $submenu[$parent] as $submenu_array ) {
				if ( isset( $module_page ) &&
					( $module_page == $submenu_array[2] ) &&
					(
						( $parent == $pagenow ) ||
						( $parent == $module_page ) ||
						( $module_page == $hook ) ||
						( $pagenow == 'admin.php' && $parent1 != $submenu_array[2] ) ||
						( !empty($typenow) && $parent == $pagenow . '?post_type=' . $typenow)
					)
					) {
						$title = $submenu_array[3];
						return $submenu_array[3];
					}

				if ( $submenu_array[2] != $pagenow || isset( $_GET['page'] ) ) // not the current page
					continue;

				if ( isset( $submenu_array[3] ) ) {
					$title = $submenu_array[3];
					return $submenu_array[3];
				} else {
					$title = $submenu_array[0];
					return $title;
				}
			}
		}
		if ( empty ( $title ) ) {
			foreach ( $menu as $menu_array ) {
				if ( isset( $module_page ) &&
					( $module_page == $menu_array[2] ) &&
					( $pagenow == 'admin.php' ) &&
					( $parent1 == $menu_array[2] ) )
					{
						$title = $menu_array[3];
						return $menu_array[3];
					}
			}
		}
	}

	return $title;
}

/**
 * @since 2.3.0
 *
 * @param string $module_page
 * @param string $parent_page
 * @return string|null
 */
function get_module_page_hook( $module_page, $parent_page ) {
	$hook = get_module_page_hookname( $module_page, $parent_page );
	if ( has_action($hook) )
		return $hook;
	else
		return null;
}

/**
 *
 * @global array $admin_page_hooks
 * @param string $module_page
 * @param string $parent_page
 */
function get_module_page_hookname( $module_page, $parent_page ) {
	global $admin_page_hooks;

	$parent = get_admin_page_parent( $parent_page );

	$page_type = 'admin';
	if ( empty ( $parent_page ) || 'admin.php' == $parent_page || isset( $admin_page_hooks[$module_page] ) ) {
		if ( isset( $admin_page_hooks[$module_page] ) ) {
			$page_type = 'toplevel';
		} elseif ( isset( $admin_page_hooks[$parent] )) {
			$page_type = $admin_page_hooks[$parent];
		}
	} elseif ( isset( $admin_page_hooks[$parent] ) ) {
		$page_type = $admin_page_hooks[$parent];
	}

	$module_name = preg_replace( '!\.php!', '', $module_page );

	return $page_type . '_page_' . $module_name;
}

/**
 *
 * @global string $pagenow
 * @global array $menu
 * @global array $submenu
 * @global array $_mcms_menu_nopriv
 * @global array $_mcms_submenu_nopriv
 * @global string $module_page
 * @global array $_registered_pages
 */
function user_can_access_admin_page() {
	global $pagenow, $menu, $submenu, $_mcms_menu_nopriv, $_mcms_submenu_nopriv,
		$module_page, $_registered_pages;

	$parent = get_admin_page_parent();

	if ( !isset( $module_page ) && isset( $_mcms_submenu_nopriv[$parent][$pagenow] ) )
		return false;

	if ( isset( $module_page ) ) {
		if ( isset( $_mcms_submenu_nopriv[$parent][$module_page] ) )
			return false;

		$hookname = get_module_page_hookname($module_page, $parent);

		if ( !isset($_registered_pages[$hookname]) )
			return false;
	}

	if ( empty( $parent) ) {
		if ( isset( $_mcms_menu_nopriv[$pagenow] ) )
			return false;
		if ( isset( $_mcms_submenu_nopriv[$pagenow][$pagenow] ) )
			return false;
		if ( isset( $module_page ) && isset( $_mcms_submenu_nopriv[$pagenow][$module_page] ) )
			return false;
		if ( isset( $module_page ) && isset( $_mcms_menu_nopriv[$module_page] ) )
			return false;
		foreach (array_keys( $_mcms_submenu_nopriv ) as $key ) {
			if ( isset( $_mcms_submenu_nopriv[$key][$pagenow] ) )
				return false;
			if ( isset( $module_page ) && isset( $_mcms_submenu_nopriv[$key][$module_page] ) )
			return false;
		}
		return true;
	}

	if ( isset( $module_page ) && ( $module_page == $parent ) && isset( $_mcms_menu_nopriv[$module_page] ) )
		return false;

	if ( isset( $submenu[$parent] ) ) {
		foreach ( $submenu[$parent] as $submenu_array ) {
			if ( isset( $module_page ) && ( $submenu_array[2] == $module_page ) ) {
				if ( current_user_can( $submenu_array[1] ))
					return true;
				else
					return false;
			} elseif ( $submenu_array[2] == $pagenow ) {
				if ( current_user_can( $submenu_array[1] ))
					return true;
				else
					return false;
			}
		}
	}

	foreach ( $menu as $menu_array ) {
		if ( $menu_array[2] == $parent) {
			if ( current_user_can( $menu_array[1] ))
				return true;
			else
				return false;
		}
	}

	return true;
}

/* Whitelist functions */

/**
 * Refreshes the value of the options whitelist available via the 'whitelist_options' hook.
 *
 * See the {@see 'whitelist_options'} filter.
 *
 * @since 2.7.0
 *
 * @global array $new_whitelist_options
 *
 * @param array $options
 * @return array
 */
function option_update_filter( $options ) {
	global $new_whitelist_options;

	if ( is_array( $new_whitelist_options ) )
		$options = add_option_whitelist( $new_whitelist_options, $options );

	return $options;
}

/**
 * Adds an array of options to the options whitelist.
 *
 * @since 2.7.0
 *
 * @global array $whitelist_options
 *
 * @param array        $new_options
 * @param string|array $options
 * @return array
 */
function add_option_whitelist( $new_options, $options = '' ) {
	if ( $options == '' )
		global $whitelist_options;
	else
		$whitelist_options = $options;

	foreach ( $new_options as $page => $keys ) {
		foreach ( $keys as $key ) {
			if ( !isset($whitelist_options[ $page ]) || !is_array($whitelist_options[ $page ]) ) {
				$whitelist_options[ $page ] = array();
				$whitelist_options[ $page ][] = $key;
			} else {
				$pos = array_search( $key, $whitelist_options[ $page ] );
				if ( $pos === false )
					$whitelist_options[ $page ][] = $key;
			}
		}
	}

	return $whitelist_options;
}

/**
 * Removes a list of options from the options whitelist.
 *
 * @since 2.7.0
 *
 * @global array $whitelist_options
 *
 * @param array        $del_options
 * @param string|array $options
 * @return array
 */
function remove_option_whitelist( $del_options, $options = '' ) {
	if ( $options == '' )
		global $whitelist_options;
	else
		$whitelist_options = $options;

	foreach ( $del_options as $page => $keys ) {
		foreach ( $keys as $key ) {
			if ( isset($whitelist_options[ $page ]) && is_array($whitelist_options[ $page ]) ) {
				$pos = array_search( $key, $whitelist_options[ $page ] );
				if ( $pos !== false )
					unset( $whitelist_options[ $page ][ $pos ] );
			}
		}
	}

	return $whitelist_options;
}

/**
 * Output nonce, action, and option_page fields for a settings page.
 *
 * @since 2.7.0
 *
 * @param string $option_group A settings group name. This should match the group name used in register_setting().
 */
function settings_fields($option_group) {
	echo "<input type='hidden' name='option_page' value='" . esc_attr($option_group) . "' />";
	echo '<input type="hidden" name="action" value="update" />';
	mcms_nonce_field("$option_group-options");
}

/**
 * Clears the Modules cache used by get_modules() and by default, the Module Update cache.
 *
 * @since 3.7.0
 *
 * @param bool $clear_update_cache Whether to clear the Module updates cache
 */
function mcms_clean_modules_cache( $clear_update_cache = true ) {
	if ( $clear_update_cache )
		delete_site_transient( 'update_modules' );
	mcms_cache_delete( 'modules', 'modules' );
}

/**
 * Load a given module attempt to generate errors.
 *
 * @since 3.0.0
 * @since 4.4.0 Function was moved into the `mcms-admin/includes/module.php` file.
 *
 * @param string $module Module file to load.
 */
function module_sandbox_scrape( $module ) {
	mcms_register_module_realpath( MCMS_PLUGIN_DIR . '/' . $module );
	include( MCMS_PLUGIN_DIR . '/' . $module );
}

/**
 * Helper function for adding content to the postbox shown when editing the privacy policy.
 *
 * Modules and myskins should suggest text for inclusion in the site's privacy policy.
 * The suggested text should contain information about any functionality that affects user privacy,
 * and will be shown in the Suggested Privacy Policy Content postbox.
 *
 * A module or myskin can use this function multiple times as long as it will help to better present
 * the suggested policy content. For example modular modules such as WooCommerse or Jetpack
 * can add or remove suggested content depending on the modules/extensions that are enabled.
 *
 * Intended for use with the `'admin_init'` action.
 *
 * @since 4.9.6
 *
 * @param string $module_name The name of the module or myskin that is suggesting content for the site's privacy policy.
 * @param string $policy_text The suggested content for inclusion in the policy.
 *                            For more information see the Modules Handbook https://developer.mandarincms.com/modules/. 
 */
function mcms_add_privacy_policy_content( $module_name, $policy_text ) {
	if ( ! class_exists( 'MCMS_Privacy_Policy_Content' ) ) {
		require_once( BASED_TREE_URI . 'mcms-admin/includes/misc.php' );
	}

	MCMS_Privacy_Policy_Content::add( $module_name, $policy_text );
}
