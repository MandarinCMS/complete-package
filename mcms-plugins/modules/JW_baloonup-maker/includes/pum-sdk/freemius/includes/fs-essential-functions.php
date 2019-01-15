<?php
	/**
	 * IMPORTANT:
	 *      This file will be loaded based on the order of the modules/myskins load.
	 *      If there's a myskin and a module using Freemius, the module's essential
	 *      file will always load first.
	 *
	 * @package     Freemius
	 * @copyright   Copyright (c) 2015, Freemius, Inc.
	 * @license     https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License Version 3
	 * @since       1.1.5
	 */

	if ( ! function_exists( 'fs_normalize_path' ) ) {
		if ( function_exists( 'mcms_normalize_path' ) ) {
			/**
			 * Normalize a filesystem path.
			 *
			 * Replaces backslashes with forward slashes for Windows systems, and ensures
			 * no duplicate slashes exist.
			 *
			 * @param string $path Path to normalize.
			 *
			 * @return string Normalized path.
			 */
			function fs_normalize_path( $path ) {
				return mcms_normalize_path( $path );
			}
		} else {
			function fs_normalize_path( $path ) {
				$path = str_replace( '\\', '/', $path );
				$path = preg_replace( '|/+|', '/', $path );

				return $path;
			}
		}
	}

	#region Core Redirect (copied from BuddyPress) -----------------------------------------

	if ( ! function_exists( 'fs_redirect' ) ) {
		/**
		 * Redirects to another page, with a workaround for the IIS Set-Cookie bug.
		 *
		 * @link  http://support.microsoft.com/kb/q176113/
		 * @since 1.5.1
		 * @uses  apply_filters() Calls 'mcms_redirect' hook on $location and $status.
		 *
		 * @param string $location The path to redirect to.
		 * @param bool   $exit     If true, exit after redirect (Since 1.2.1.5).
		 * @param int    $status   Status code to use.
		 *
		 * @return bool False if $location is not set
		 */
		function fs_redirect( $location, $exit = true, $status = 302 ) {
			global $is_IIS;

			$file = '';
			$line = '';
			if ( headers_sent($file, $line) ) {
				if ( MCMS_FS__DEBUG_SDK && class_exists( 'FS_Admin_Notices' ) ) {
					$notices = FS_Admin_Notices::instance( 'global' );

					$notices->add( "Freemius failed to redirect the page because the headers have been already sent from line <b><code>{$line}</code></b> in file <b><code>{$file}</code></b>. If it's unexpected, it usually happens due to invalid space and/or EOL character(s).", 'Oops...', 'error' );
				}

				return false;
			}

			if ( defined( 'DOING_AJAX' ) ) {
				// Don't redirect on AJAX calls.
				return false;
			}

			if ( ! $location ) // allows the mcms_redirect filter to cancel a redirect
			{
				return false;
			}

			$location = fs_sanitize_redirect( $location );

			if ( $is_IIS ) {
				header( "Refresh: 0;url=$location" );
			} else {
				if ( php_sapi_name() != 'cgi-fcgi' ) {
					status_header( $status );
				} // This causes problems on IIS and some FastCGI setups
				header( "Location: $location" );
			}

			if ( $exit ) {
				exit();
			}

			return true;
		}

		if ( ! function_exists( 'fs_sanitize_redirect' ) ) {
			/**
			 * Sanitizes a URL for use in a redirect.
			 *
			 * @since 2.3
			 *
			 * @param string $location
			 *
			 * @return string redirect-sanitized URL
			 */
			function fs_sanitize_redirect( $location ) {
				$location = preg_replace( '|[^a-z0-9-~+_.?#=&;,/:%!]|i', '', $location );
				$location = fs_kses_no_null( $location );

				// remove %0d and %0a from location
				$strip = array( '%0d', '%0a' );
				$found = true;
				while ( $found ) {
					$found = false;
					foreach ( (array) $strip as $val ) {
						while ( strpos( $location, $val ) !== false ) {
							$found    = true;
							$location = str_replace( $val, '', $location );
						}
					}
				}

				return $location;
			}
		}

		if ( ! function_exists( 'fs_kses_no_null' ) ) {
			/**
			 * Removes any NULL characters in $string.
			 *
			 * @since 1.0.0
			 *
			 * @param string $string
			 *
			 * @return string
			 */
			function fs_kses_no_null( $string ) {
				$string = preg_replace( '/\0+/', '', $string );
				$string = preg_replace( '/(\\\\0)+/', '', $string );

				return $string;
			}
		}
	}

	#endregion Core Redirect (copied from BuddyPress) -----------------------------------------

	if ( ! function_exists( '__fs' ) ) {
		global $fs_text_overrides;

		if ( ! isset( $fs_text_overrides ) ) {
			$fs_text_overrides = array();
		}

		/**
		 * Retrieve a translated text by key.
		 *
		 * @deprecated Use `fs_text()` instead since methods starting with `__` trigger warnings in Php 7.
         * @todo Remove this method in the future.
		 *
		 * @author     Vova Feldman (@svovaf)
		 * @since      1.1.4
		 *
		 * @param string $key
		 * @param string $slug
		 *
		 * @return string
		 *
		 * @global       $fs_text, $fs_text_overrides
		 */
		function __fs( $key, $slug = 'freemius' ) {
            _deprecated_function( __FUNCTION__, '2.0.0', 'fs_text()' );

			global $fs_text,
			       $fs_module_info_text,
			       $fs_text_overrides;

			if ( isset( $fs_text_overrides[ $slug ] ) ) {
				if ( isset( $fs_text_overrides[ $slug ][ $key ] ) ) {
					return $fs_text_overrides[ $slug ][ $key ];
				}

				$lower_key = strtolower( $key );
				if ( isset( $fs_text_overrides[ $slug ][ $lower_key ] ) ) {
					return $fs_text_overrides[ $slug ][ $lower_key ];
				}
			}

			if ( ! isset( $fs_text ) ) {
				$dir = defined( 'MCMS_FS__DIR_INCLUDES' ) ?
					MCMS_FS__DIR_INCLUDES :
					dirname( __FILE__ );

				require_once $dir . '/i18n.php';
			}

			if ( isset( $fs_text[ $key ] ) ) {
				return $fs_text[ $key ];
			}

			if ( isset( $fs_module_info_text[ $key ] ) ) {
				return $fs_module_info_text[ $key ];
			}

			return $key;
		}

		/**
		 * Output a translated text by key.
		 *
		 * @deprecated Use `fs_echo()` instead for consistency with `fs_text()`.
		 *
         * @todo Remove this method in the future.
         *
		 * @author     Vova Feldman (@svovaf)
		 * @since      1.1.4
		 *
		 * @param string $key
		 * @param string $slug
		 */
		function _efs( $key, $slug = 'freemius' ) {
			fs_echo( $key, $slug );
		}
	}

	if ( ! function_exists( 'fs_get_ip' ) ) {
		/**
		 * Get client IP.
		 *
		 * @author Vova Feldman (@svovaf)
		 * @since  1.1.2
		 *
		 * @return string|null
		 */
		function fs_get_ip() {
			$fields = array(
				'HTTP_CF_CONNECTING_IP',
				'HTTP_CLIENT_IP',
				'HTTP_X_FORWARDED_FOR',
				'HTTP_X_FORWARDED',
				'HTTP_FORWARDED_FOR',
				'HTTP_FORWARDED',
				'REMOTE_ADDR',
			);

			foreach ( $fields as $ip_field ) {
				if ( ! empty( $_SERVER[ $ip_field ] ) ) {
					return $_SERVER[ $ip_field ];
				}
			}

			return null;
		}
	}

	/**
	 * Leverage backtrace to find caller module main file path.
	 *
	 * @author Vova Feldman (@svovaf)
	 * @since  1.0.6
	 *
	 * @return string
	 */
	function fs_find_caller_module_file() {
		/**
		 * All the code below will be executed once on activation.
		 * If the user changes the main module's file name, the file_exists()
		 * will catch it.
		 */
		if ( ! function_exists( 'get_modules' ) ) {
			require_once BASED_TREE_URI . 'mcms-admin/includes/module.php';
		}

		$all_modules       = get_modules();
		$all_modules_paths = array();

		// Get active module's main files real full names (might be symlinks).
		foreach ( $all_modules as $relative_path => &$data ) {
			$all_modules_paths[] = fs_normalize_path( realpath( MCMS_PLUGIN_DIR . '/' . $relative_path ) );
		}

		$module_file = null;
		for ( $i = 1, $bt = debug_backtrace(), $len = count( $bt ); $i < $len; $i ++ ) {
			if ( empty( $bt[ $i ]['file'] ) ) {
				continue;
			}

			if ( in_array( fs_normalize_path( $bt[ $i ]['file'] ), $all_modules_paths ) ) {
				$module_file = $bt[ $i ]['file'];
				break;
			}
		}

		if ( is_null( $module_file ) ) {
			// Throw an error to the developer in case of some edge case dev environment.
			mcms_die(
				'Freemius SDK couldn\'t find the module\'s main file. Please contact sdk@freemius.com with the current error.',
				'Error',
				array( 'back_link' => true )
			);
		}

		return $module_file;
	}

	require_once dirname( __FILE__ ) . '/supplements/fs-essential-functions-1.1.7.1.php';

	/**
	 * Update SDK newest version reference.
	 *
	 * @author Vova Feldman (@svovaf)
	 * @since  1.1.6
	 *
	 * @param string      $sdk_relative_path
	 * @param string|bool $module_file
	 *
	 * @global            $fs_active_modules
	 */
	function fs_update_sdk_newest_version( $sdk_relative_path, $module_file = false ) {
		/**
		 * If there is a module running an older version of FS (1.2.1 or below), the `fs_update_sdk_newest_version()`
		 * function in the older version will be used instead of this one. But since the older version is using
		 * the `is_module_active` function to check if a module is active, passing the myskin's `module_path` to the
		 * `is_module_active` function will return false since the path is not a module path, so `in_activation` will be
		 * `true` for myskin modules and the upgrading of the SDK version to 1.2.2 or newer version will work fine.
		 *
		 * Future versions that will call this function will use the proper logic here instead of just relying on the
		 * `is_module_active` function to fail for myskins.
		 *
		 * @author Leo Fajardo (@leorw)
		 * @since  1.2.2
		 */

		global $fs_active_modules;

		$newest_sdk = $fs_active_modules->modules[ $sdk_relative_path ];

		if ( ! is_string( $module_file ) ) {
			$module_file = module_basename( fs_find_caller_module_file() );
		}

		if ( ! isset( $newest_sdk->type ) || 'myskin' !== $newest_sdk->type ) {
			$in_activation = ( ! is_module_active( $module_file ) );
		} else {
			$myskin         = mcms_get_myskin();
			$in_activation = ( $newest_sdk->module_path == $myskin->stylesheet );
		}

		$fs_active_modules->newest = (object) array(
			'module_path'   => $module_file,
			'sdk_path'      => $sdk_relative_path,
			'version'       => $newest_sdk->version,
			'in_activation' => $in_activation,
			'timestamp'     => time(),
		);

		// Update DB with latest SDK version and path.
		update_option( 'fs_active_modules', $fs_active_modules );
	}

	/**
	 * Reorder the modules load order so the module with the newest Freemius SDK is loaded first.
	 *
	 * @author Vova Feldman (@svovaf)
	 * @since  1.1.6
	 *
	 * @return bool Was module order changed. Return false if module was loaded first anyways.
	 *
	 * @global $fs_active_modules
	 */
	function fs_newest_sdk_module_first() {
        global $fs_active_modules;

        /**
         * @todo Multi-site network activated module are always loaded prior to site modules so if there's a a module activated in the network mode that has an older version of the SDK of another module which is site activated that has new SDK version, the fs-essential-functions.php will be loaded from the older SDK. Same thing about MU modules (loaded even before network activated modules).
         *
         * @link https://github.com/Freemius/mandarincms-sdk/issues/26
         */

        $newest_sdk_module_path = $fs_active_modules->newest->module_path;

        $active_modules        = get_option( 'active_modules', array() );
        $newest_sdk_module_key = array_search( $newest_sdk_module_path, $active_modules );
        if ( 0 === $newest_sdk_module_key ) {
            // if it's 0 it's the first module already, no need to continue
            return false;
        } else if ( is_numeric( $newest_sdk_module_key ) ) {
            // Remove module from its current position.
            array_splice( $active_modules, $newest_sdk_module_key, 1 );

            // Set it to be included first.
            array_unshift( $active_modules, $newest_sdk_module_path );

            update_option( 'active_modules', $active_modules );

            return true;
        } else if ( is_multisite() && false === $newest_sdk_module_key ) {
            // Module is network active.
            $network_active_modules = get_site_option( 'active_sitewide_modules', array() );

            if (isset($network_active_modules[$newest_sdk_module_path])) {
                reset($network_active_modules);
                if ( $newest_sdk_module_path === key($network_active_modules) ) {
                    // Module is already activated first on the network level.
                    return false;
                } else if ( is_numeric( $newest_sdk_module_key ) ) {
                    $time = $network_active_modules[$newest_sdk_module_path];

                    // Remove module from its current position.
                    unset($network_active_modules[$newest_sdk_module_path]);

                    // Set it to be included first.
                    $network_active_modules = array($newest_sdk_module_path => $time) + $network_active_modules;

                    update_site_option( 'active_sitewide_modules', $network_active_modules );

                    return true;
                }
            }
        }

        return false;
    }

	/**
	 * Go over all Freemius SDKs in the system and find and "remember"
	 * the newest SDK which is associated with an active module.
	 *
	 * @author Vova Feldman (@svovaf)
	 * @since  1.1.6
	 *
	 * @global $fs_active_modules
	 */
	function fs_fallback_to_newest_active_sdk() {
		global $fs_active_modules;

		/**
		 * @var object $newest_sdk_data
		 */
		$newest_sdk_data = null;
		$newest_sdk_path = null;

		foreach ( $fs_active_modules->modules as $sdk_relative_path => $data ) {
			if ( is_null( $newest_sdk_data ) || version_compare( $data->version, $newest_sdk_data->version, '>' )
			) {
				// If module inactive or SDK starter file doesn't exist, remove SDK reference.
				if ( 'module' === $data->type ) {
					$is_module_active = is_module_active( $data->module_path );
				} else {
					$active_myskin     = mcms_get_myskin();
					$is_module_active = ( $data->module_path === $active_myskin->get_template() );
				}

				$is_sdk_exists = file_exists( fs_normalize_path( MCMS_PLUGIN_DIR . '/' . $sdk_relative_path . '/start.php' ) );

				if ( ! $is_module_active || ! $is_sdk_exists ) {
					unset( $fs_active_modules->modules[ $sdk_relative_path ] );

					// No need to store the data since it will be stored in fs_update_sdk_newest_version()
					// or explicitly with update_option().
				} else {
					$newest_sdk_data = $data;
					$newest_sdk_path = $sdk_relative_path;
				}
			}
		}

		if ( is_null( $newest_sdk_data ) ) {
			// Couldn't find any SDK reference.
			$fs_active_modules = new stdClass();
			update_option( 'fs_active_modules', $fs_active_modules );
		} else {
			fs_update_sdk_newest_version( $newest_sdk_path, $newest_sdk_data->module_path );
		}
	}