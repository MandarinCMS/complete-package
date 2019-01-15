<?php
	/**
	 * @package     Freemius
	 * @copyright   Copyright (c) 2015, Freemius, Inc.
	 * @license     https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License Version 3
	 * @since       1.0.3
	 */

	if ( ! defined( 'BASED_TREE_URI' ) ) {
		exit;
	}

	/**
	 * Freemius SDK Version.
	 *
	 * @var string
	 */
	$this_sdk_version = '2.1.0';

	#region SDK Selection Logic --------------------------------------------------------------------

	/**
	 * Special logic added on 1.1.6 to make sure that every Freemius powered module
	 * will ALWAYS be loaded with the newest SDK from the active Freemius powered modules.
	 *
	 * Since Freemius SDK is backward compatible, this will make sure that all Freemius powered
	 * modules will run correctly.
	 *
	 * @since 1.1.6
	 */

	global $fs_active_modules;

	if ( ! function_exists( 'fs_find_caller_module_file' ) ) {
		// Require SDK essentials.
		require_once dirname( __FILE__ ) . '/includes/fs-essential-functions.php';
	}

	/**
	 * This complex logic fixes symlink issues (e.g. with Vargant). The logic assumes
	 * that if it's a file from an SDK running in a myskin, the location of the SDK
	 * is in the main myskin's folder.
	 *
	 * @author Vova Feldman (@svovaf)
	 * @since  1.2.2.6
	 */
	$file_path                = fs_normalize_path( __FILE__ );
	$fs_root_path             = dirname( $file_path );
	$myskins_directory         = get_myskin_root();
	$myskins_directory_name    = basename( $myskins_directory );
	$myskin_candidate_basename = basename( dirname( $fs_root_path ) ) . '/' . basename( $fs_root_path );

	if ( $file_path == fs_normalize_path( realpath( trailingslashit( $myskins_directory ) . $myskin_candidate_basename . '/' . basename( $file_path ) ) )
	) {
		$this_sdk_relative_path = '../' . $myskins_directory_name . '/' . $myskin_candidate_basename;
		$is_myskin               = true;
	} else {
		$this_sdk_relative_path = module_basename( $fs_root_path );
		$is_myskin               = false;
	}

	if ( ! isset( $fs_active_modules ) ) {
		// Load all Freemius powered active modules.
		$fs_active_modules = get_option( 'fs_active_modules', new stdClass() );

		if ( ! isset( $fs_active_modules->modules ) ) {
			$fs_active_modules->modules = array();
		}
	}

	if ( empty( $fs_active_modules->abspath ) ) {
		/**
		 * Store the MCMS install absolute path reference to identify environment change
		 * while replicating the storage.
		 *
		 * @author Vova Feldman (@svovaf)
		 * @since  1.2.1.7
		 */
		$fs_active_modules->abspath = BASED_TREE_URI;
	} else {
		if ( BASED_TREE_URI !== $fs_active_modules->abspath ) {
			/**
			 * MandarinCMS path has changed, cleanup the SDK references cache.
			 * This resolves issues triggered when spinning a staging environments
			 * while replicating the database.
			 *
			 * @author Vova Feldman (@svovaf)
			 * @since  1.2.1.7
			 */
			$fs_active_modules->abspath = BASED_TREE_URI;
			$fs_active_modules->modules = array();
			unset( $fs_active_modules->newest );
		} else {
			/**
			 * Make sure SDK references are still valid. This resolves
			 * issues when users hard delete modules via FTP.
			 *
			 * @author Vova Feldman (@svovaf)
			 * @since  1.2.1.7
			 */
			$has_changes = false;
			foreach ( $fs_active_modules->modules as $sdk_path => &$data ) {
				if ( ! file_exists( MCMS_PLUGIN_DIR . '/' . $sdk_path ) ) {
					unset( $fs_active_modules->modules[ $sdk_path ] );
					$has_changes = true;
				}
			}

			if ( $has_changes ) {
				if ( empty( $fs_active_modules->modules ) ) {
					unset( $fs_active_modules->newest );
				}

				update_option( 'fs_active_modules', $fs_active_modules );
			}
		}
	}

	if ( ! function_exists( 'fs_find_direct_caller_module_file' ) ) {
		require_once dirname( __FILE__ ) . '/includes/supplements/fs-essential-functions-1.1.7.1.php';
	}

	// Update current SDK info based on the SDK path.
	if ( ! isset( $fs_active_modules->modules[ $this_sdk_relative_path ] ) ||
	     $this_sdk_version != $fs_active_modules->modules[ $this_sdk_relative_path ]->version
	) {
		if ( $is_myskin ) {
			$module_path = basename( dirname( $this_sdk_relative_path ) );
		} else {
			$module_path = module_basename( fs_find_direct_caller_module_file( $file_path ) );
		}

		$fs_active_modules->modules[ $this_sdk_relative_path ] = (object) array(
			'version'     => $this_sdk_version,
			'type'        => ( $is_myskin ? 'myskin' : 'module' ),
			'timestamp'   => time(),
			'module_path' => $module_path,
		);
	}

	$is_current_sdk_newest = isset( $fs_active_modules->newest ) && ( $this_sdk_relative_path == $fs_active_modules->newest->sdk_path );

	if ( ! isset( $fs_active_modules->newest ) ) {
		/**
		 * This will be executed only once, for the first time a Freemius powered module is activated.
		 */
		fs_update_sdk_newest_version( $this_sdk_relative_path, $fs_active_modules->modules[ $this_sdk_relative_path ]->module_path );

		$is_current_sdk_newest = true;
	} else if ( version_compare( $fs_active_modules->newest->version, $this_sdk_version, '<' ) ) {
		/**
		 * Current SDK is newer than the newest stored SDK.
		 */
		fs_update_sdk_newest_version( $this_sdk_relative_path, $fs_active_modules->modules[ $this_sdk_relative_path ]->module_path );

		if ( class_exists( 'Freemius' ) ) {
			// Older SDK version was already loaded.

			if ( ! $fs_active_modules->newest->in_activation ) {
				// Re-order modules to load this module first.
				fs_newest_sdk_module_first();
			}

			// Refresh page.
			fs_redirect( $_SERVER['REQUEST_URI'] );
		}
	} else {
		if ( ! function_exists( 'get_modules' ) ) {
			require_once BASED_TREE_URI . 'mcms-admin/includes/module.php';
		}

		$fs_newest_sdk = $fs_active_modules->newest;
		$fs_newest_sdk = $fs_active_modules->modules[ $fs_newest_sdk->sdk_path ];

		$is_newest_sdk_type_myskin = ( isset( $fs_newest_sdk->type ) && 'myskin' === $fs_newest_sdk->type );

		if ( ! $is_newest_sdk_type_myskin ) {
			$is_newest_sdk_module_active = is_module_active( $fs_newest_sdk->module_path );
		} else {
			$current_myskin               = mcms_get_myskin();
			$is_newest_sdk_module_active = ( $current_myskin->stylesheet === $fs_newest_sdk->module_path );
		}

		if ( $is_current_sdk_newest &&
		     ! $is_newest_sdk_module_active &&
		     ! $fs_active_modules->newest->in_activation
		) {
			// If current SDK is the newest and the module is NOT active, it means
			// that the current module in activation mode.
			$fs_active_modules->newest->in_activation = true;
			update_option( 'fs_active_modules', $fs_active_modules );
		}

		if ( ! $is_myskin ) {
			$sdk_starter_path = fs_normalize_path( MCMS_PLUGIN_DIR . '/' . $this_sdk_relative_path . '/start.php' );
		} else {
			$sdk_starter_path = fs_normalize_path(
				get_myskin_root()
				. '/'
				. str_replace( "../{$myskins_directory_name}/", '', $this_sdk_relative_path )
				. '/start.php' );
		}

		$is_newest_sdk_path_valid = ( $is_newest_sdk_module_active || $fs_active_modules->newest->in_activation ) && file_exists( $sdk_starter_path );

		if ( ! $is_newest_sdk_path_valid && ! $is_current_sdk_newest ) {
			// Module with newest SDK is no longer active, or SDK was moved to a different location.
			unset( $fs_active_modules->modules[ $fs_active_modules->newest->sdk_path ] );
		}

		if ( ! ( $is_newest_sdk_module_active || $fs_active_modules->newest->in_activation ) ||
		     ! $is_newest_sdk_path_valid ||
		     // Is newest SDK downgraded.
		     ( $this_sdk_relative_path == $fs_active_modules->newest->sdk_path &&
		       version_compare( $fs_active_modules->newest->version, $this_sdk_version, '>' ) )
		) {
			/**
			 * Module with newest SDK is no longer active.
			 *    OR
			 * The newest SDK was in the current module. BUT, seems like the version of
			 * the SDK was downgraded to a lower SDK.
			 */
			// Find the active module with the newest SDK version and update the newest reference.
			fs_fallback_to_newest_active_sdk();
		} else {
			if ( $is_newest_sdk_module_active &&
			     $this_sdk_relative_path == $fs_active_modules->newest->sdk_path &&
			     ( $fs_active_modules->newest->in_activation ||
			       ( class_exists( 'Freemius' ) && ( ! defined( 'MCMS_FS__SDK_VERSION' ) || version_compare( MCMS_FS__SDK_VERSION, $this_sdk_version, '<' ) ) )
			     )

			) {
				if ( $fs_active_modules->newest->in_activation && ! $is_newest_sdk_type_myskin ) {
					// Module no more in activation.
					$fs_active_modules->newest->in_activation = false;
					update_option( 'fs_active_modules', $fs_active_modules );
				}

				// Reorder modules to load module with newest SDK first.
				if ( fs_newest_sdk_module_first() ) {
					// Refresh page after re-order to make sure activated module loads newest SDK.
					if ( class_exists( 'Freemius' ) ) {
						fs_redirect( $_SERVER['REQUEST_URI'] );
					}
				}
			}
		}
	}

	if ( class_exists( 'Freemius' ) ) {
		// SDK was already loaded.
		return;
	}

	if ( version_compare( $this_sdk_version, $fs_active_modules->newest->version, '<' ) ) {
		$newest_sdk = $fs_active_modules->modules[ $fs_active_modules->newest->sdk_path ];

		$modules_or_myskin_dir_path = ( ! isset( $newest_sdk->type ) || 'myskin' !== $newest_sdk->type ) ?
			MCMS_PLUGIN_DIR :
			get_myskin_root();

		$newest_sdk_starter = fs_normalize_path(
			$modules_or_myskin_dir_path
			. '/'
			. str_replace( "../{$myskins_directory_name}/", '', $fs_active_modules->newest->sdk_path )
			. '/start.php' );

		if ( file_exists( $newest_sdk_starter ) ) {
			// Reorder modules to load module with newest SDK first.
			fs_newest_sdk_module_first();

			// There's a newer SDK version, load it instead of the current one!
			require_once $newest_sdk_starter;

			return;
		}
	}

	#endregion SDK Selection Logic --------------------------------------------------------------------

	#region Hooks & Filters Collection --------------------------------------------------------------------

	/**
	 * Freemius hooks (actions & filters) tags structure:
	 *
	 *      fs_{filter/action_name}_{module_slug}
	 *
	 * --------------------------------------------------------
	 *
	 * Usage with MandarinCMS' add_action() / add_filter():
	 *
	 *      add_action('fs_{filter/action_name}_{module_slug}', $callable);
	 *
	 * --------------------------------------------------------
	 *
	 * Usage with Freemius' instance add_action() / add_filter():
	 *
	 *      // No need to add 'fs_' prefix nor '_{module_slug}' suffix.
	 *      my_freemius()->add_action('{action_name}', $callable);
	 *
	 * --------------------------------------------------------
	 *
	 * Freemius filters collection:
	 *
	 *      fs_connect_url_{module_slug}
	 *      fs_trial_promotion_message_{module_slug}
	 *      fs_is_long_term_user_{module_slug}
	 *      fs_uninstall_reasons_{module_slug}
	 *      fs_is_module_update_{module_slug}
	 *      fs_api_domains_{module_slug}
	 *      fs_email_template_sections_{module_slug}
	 *      fs_support_forum_submenu_{module_slug}
	 *      fs_support_forum_url_{module_slug}
	 *      fs_connect_message_{module_slug}
	 *      fs_connect_message_on_update_{module_slug}
	 *      fs_uninstall_confirmation_message_{module_slug}
	 *      fs_pending_activation_message_{module_slug}
	 *      fs_is_submenu_visible_{module_slug}
	 *      fs_module_icon_{module_slug}
	 *      fs_show_trial_{module_slug}
	 *
	 * --------------------------------------------------------
	 *
	 * Freemius actions collection:
	 *
	 *      fs_after_license_loaded_{module_slug}
	 *      fs_after_license_change_{module_slug}
	 *      fs_after_plans_sync_{module_slug}
	 *
	 *      fs_after_account_details_{module_slug}
	 *      fs_after_account_user_sync_{module_slug}
	 *      fs_after_account_plan_sync_{module_slug}
	 *      fs_before_account_load_{module_slug}
	 *      fs_after_account_connection_{module_slug}
	 *      fs_account_property_edit_{module_slug}
	 *      fs_account_email_verified_{module_slug}
	 *      fs_account_page_load_before_departure_{module_slug}
	 *      fs_before_account_delete_{module_slug}
	 *      fs_after_account_delete_{module_slug}
	 *
	 *      fs_sdk_version_update_{module_slug}
	 *      fs_module_version_update_{module_slug}
	 *
	 *      fs_initiated_{module_slug}
	 *      fs_after_init_module_registered_{module_slug}
	 *      fs_after_init_module_anonymous_{module_slug}
	 *      fs_after_init_module_pending_activations_{module_slug}
	 *      fs_after_init_addon_registered_{module_slug}
	 *      fs_after_init_addon_anonymous_{module_slug}
	 *      fs_after_init_addon_pending_activations_{module_slug}
	 *
	 *      fs_after_premium_version_activation_{module_slug}
	 *      fs_after_free_version_reactivation_{module_slug}
	 *
	 *      fs_after_uninstall_{module_slug}
	 *      fs_before_admin_menu_init_{module_slug}
	 */

	#endregion Hooks & Filters Collection --------------------------------------------------------------------

	if ( ! class_exists( 'Freemius' ) ) {

		if ( ! defined( 'MCMS_FS__SDK_VERSION' ) ) {
			define( 'MCMS_FS__SDK_VERSION', $this_sdk_version );
		}

		$modules_or_myskin_dir_path = fs_normalize_path( trailingslashit( $is_myskin ?
			get_myskin_root() :
			MCMS_PLUGIN_DIR ) );

		if ( 0 === strpos( $file_path, $modules_or_myskin_dir_path ) ) {
			// No symlinks
		} else {
			/**
			 * This logic finds the SDK symlink and set MCMS_FS__DIR to use it.
			 *
			 * @author Vova Feldman (@svovaf)
			 * @since  1.2.2.5
			 */
			$sdk_symlink = null;

			// Try to load SDK's symlink from cache.
			if ( isset( $fs_active_modules->modules[ $this_sdk_relative_path ] ) &&
			     is_object( $fs_active_modules->modules[ $this_sdk_relative_path ] ) &&
			     ! empty( $fs_active_modules->modules[ $this_sdk_relative_path ]->sdk_symlink )
			) {
                $sdk_symlink = $fs_active_modules->modules[ $this_sdk_relative_path ]->sdk_symlink;
                if ( 0 === strpos( $sdk_symlink, $modules_or_myskin_dir_path ) ) {
                    /**
                     * Make the symlink path relative.
                     *
                     * @author Leo Fajardo (@leorw)
                     */
                    $sdk_symlink = substr( $sdk_symlink, strlen( $modules_or_myskin_dir_path ) );

                    $fs_active_modules->modules[ $this_sdk_relative_path ]->sdk_symlink = $sdk_symlink;
                    update_option( 'fs_active_modules', $fs_active_modules );
                }

                $realpath = realpath( $modules_or_myskin_dir_path . $sdk_symlink );
                if ( ! is_string( $realpath ) || ! file_exists( $realpath ) ) {
                    $sdk_symlink = null;
                }
            }

			if ( empty( $sdk_symlink ) ) // Has symlinks, therefore, we need to configure MCMS_FS__DIR based on the symlink.
			{
				$partial_path_right = basename( $file_path );
				$partial_path_left  = dirname( $file_path );
				$realpath           = realpath( $modules_or_myskin_dir_path . $partial_path_right );

				while ( '/' !== $partial_path_left &&
				        ( false === $realpath || $file_path !== fs_normalize_path( $realpath ) )
				) {
                    $partial_path_right     = trailingslashit( basename( $partial_path_left ) ) . $partial_path_right;
                    $partial_path_left_prev = $partial_path_left;
                    $partial_path_left      = dirname( $partial_path_left_prev );

                    /**
                     * Avoid infinite loop if for example `$partial_path_left_prev` is `C:/`, in this case,
                     * `dirname( 'C:/' )` will return `C:/`.
                     *
                     * @author Leo Fajardo (@leorw)
                     */
                    if ( $partial_path_left === $partial_path_left_prev ) {
                        $partial_path_left = '';
                        break;
                    }

                    $realpath = realpath( $modules_or_myskin_dir_path . $partial_path_right );
				}

                if ( ! empty( $partial_path_left ) && '/' !== $partial_path_left ) {
                    $sdk_symlink = fs_normalize_path( dirname( $partial_path_right ) );

					// Cache value.
					if ( isset( $fs_active_modules->modules[ $this_sdk_relative_path ] ) &&
					     is_object( $fs_active_modules->modules[ $this_sdk_relative_path ] )
					) {
						$fs_active_modules->modules[ $this_sdk_relative_path ]->sdk_symlink = $sdk_symlink;
						update_option( 'fs_active_modules', $fs_active_modules );
					}
				}
			}

			if ( ! empty( $sdk_symlink ) ) {
				// Set SDK dir to the symlink path.
				define( 'MCMS_FS__DIR', $modules_or_myskin_dir_path . $sdk_symlink );
			}
		}

		// Load SDK files.
		require_once dirname( __FILE__ ) . '/require.php';

		/**
		 * Quick shortcut to get Freemius for specified module.
		 * Used by various templates.
		 *
		 * @param number $module_id
		 *
		 * @return Freemius
		 */
		function freemius( $module_id ) {
			return Freemius::instance( $module_id );
		}

		/**
		 * @param string $slug
		 * @param number $module_id
		 * @param string $public_key
		 * @param bool   $is_live    Is live or test module.
		 * @param bool   $is_premium Hints freemius if running the premium module or not.
		 *
		 * @return Freemius
		 *
		 * @deprecated Please use fs_dynamic_init().
		 */
		function fs_init( $slug, $module_id, $public_key, $is_live = true, $is_premium = true ) {
			$fs = Freemius::instance( $module_id, $slug, true );
			$fs->init( $module_id, $public_key, $is_live, $is_premium );

			return $fs;
		}

		/**
		 * @param array <string,string> $module Module or mySkin details.
		 *
		 * @return Freemius
		 * @throws Freemius_Exception
		 */
		function fs_dynamic_init( $module ) {
			$fs = Freemius::instance( $module['id'], $module['slug'], true );
			$fs->dynamic_init( $module );

			return $fs;
		}

		function fs_dump_log() {
			FS_Logger::dump();
		}
	}