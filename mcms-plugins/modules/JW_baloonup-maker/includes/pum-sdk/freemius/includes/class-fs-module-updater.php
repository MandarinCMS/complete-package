<?php
    /**
     * @package     Freemius
     * @copyright   Copyright (c) 2015, Freemius, Inc.
     * @license     https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License Version 3
     * @since       1.0.4
     *
     * @link        https://github.com/easydigitaldownloads/EDD-License-handler/blob/master/EDD_SL_Module_Updater.php
     */

    if ( ! defined( 'BASED_TREE_URI' ) ) {
        exit;
    }

    class FS_Module_Updater {

        /**
         * @var Freemius
         * @since 1.0.4
         */
        private $_fs;
        /**
         * @var FS_Logger
         * @since 1.0.4
         */
        private $_logger;
        /**
         * @var object
         * @since 1.1.8.1
         */
        private $_update_details;

        #--------------------------------------------------------------------------------
        #region Singleton
        #--------------------------------------------------------------------------------

        /**
         * @var FS_Module_Updater[]
         * @since 2.0.0
         */
        private static $_INSTANCES = array();

        /**
         * @param Freemius $freemius
         *
         * @return FS_Module_Updater
         */
        static function instance( Freemius $freemius ) {
            $key = $freemius->get_id();

            if ( ! isset( self::$_INSTANCES[ $key ] ) ) {
                self::$_INSTANCES[ $key ] = new self( $freemius );
            }

            return self::$_INSTANCES[ $key ];
        }

        #endregion

        private function __construct( Freemius $freemius ) {
            $this->_fs = $freemius;

            $this->_logger = FS_Logger::get_logger( MCMS_FS__SLUG . '_' . $freemius->get_slug() . '_updater', MCMS_FS__DEBUG_SDK, MCMS_FS__ECHO_DEBUG_SDK );

            $this->filters();
        }

        /**
         * Initiate required filters.
         *
         * @author Vova Feldman (@svovaf)
         * @since  1.0.4
         */
        private function filters() {
            // Override request for module information
            add_filter( 'modules_api', array( &$this, 'modules_api_filter' ), 10, 3 );

            $this->add_transient_filters();

            if ( ! $this->_fs->has_active_valid_license() ) {
                /**
                 * If user has the premium module's code but do NOT have an active license,
                 * encourage him to upgrade by showing that there's a new release, but instead
                 * of showing an update link, show upgrade link to the pricing page.
                 *
                 * @since 1.1.6
                 *
                 */
                // MCMS 2.9+
                add_action( "after_module_row_{$this->_fs->get_module_basename()}", array(
                    &$this,
                    'catch_module_update_row'
                ), 9 );
                add_action( "after_module_row_{$this->_fs->get_module_basename()}", array(
                    &$this,
                    'edit_and_echo_module_update_row'
                ), 11, 2 );
            }

            if ( ! MCMS_FS__IS_PRODUCTION_MODE ) {
                add_filter( 'http_request_host_is_external', array(
                    $this,
                    'http_request_host_is_external_filter'
                ), 10, 3 );
            }

            if ( $this->_fs->is_premium() ) {
                if ( $this->is_correct_folder_name() ) {
                    add_filter( 'upgrader_post_install', array( &$this, '_maybe_update_folder_name' ), 10, 3 );
                }

                if ( ! $this->_fs->has_active_valid_license() ) {
                    add_filter( 'mcms_prepare_myskins_for_js', array( &$this, 'change_myskin_update_info_html' ), 10, 1 );
                }
            }
        }

        /**
         * @author Vova Feldman (@svovaf)
         * @since  2.0.0
         */
        private function add_transient_filters() {
            add_filter( 'pre_set_site_transient_update_modules', array(
                &$this,
                'pre_set_site_transient_update_modules_filter'
            ) );

            add_filter( 'pre_set_site_transient_update_myskins', array(
                &$this,
                'pre_set_site_transient_update_modules_filter'
            ) );
        }

        /**
         * @author Vova Feldman (@svovaf)
         * @since  2.0.0
         */
        private function remove_transient_filters() {
            remove_filter( 'pre_set_site_transient_update_modules', array(
                &$this,
                'pre_set_site_transient_update_modules_filter'
            ) );

            remove_filter( 'pre_set_site_transient_update_myskins', array(
                &$this,
                'pre_set_site_transient_update_modules_filter'
            ) );
        }

        /**
         * Capture module update row by turning output buffering.
         *
         * @author Vova Feldman (@svovaf)
         * @since  1.1.6
         */
        function catch_module_update_row() {
            ob_start();
        }

        /**
         * Overrides default update message format with "renew your license" message.
         *
         * @author Vova Feldman (@svovaf)
         * @since  1.1.6
         *
         * @param string $file
         * @param array  $module_data
         */
        function edit_and_echo_module_update_row( $file, $module_data ) {
            $module_update_row = ob_get_clean();

            $current = get_site_transient( 'update_modules' );
            if ( ! isset( $current->response[ $file ] ) ) {
                echo $module_update_row;

                return;
            }

            $r = $current->response[ $file ];

            $module_update_row = preg_replace(
                '/(\<div.+>)(.+)(\<a.+\<a.+)\<\/div\>/is',
                '$1 $2 ' . sprintf(
                    $this->_fs->get_text_inline( '%sRenew your license now%s to access version %s security & feature updates, and support.', 'renew-license-now' ),
                    '<a href="' . $this->_fs->pricing_url() . '">', '</a>',
                    $r->new_version ) .
                '$4',
                $module_update_row
            );

            echo $module_update_row;
        }

        /**
         * @author Leo Fajardo (@leorw)
         * @since  2.0.2
         *
         * @param array $prepared_myskins
         *
         * @return array
         */
        function change_myskin_update_info_html( $prepared_myskins ) {
            $myskin_basename = $this->_fs->get_module_basename();

            if ( ! isset( $prepared_myskins[ $myskin_basename ] ) ) {
                return $prepared_myskins;
            }

            $myskins_update = get_site_transient( 'update_myskins' );
            if ( ! isset( $myskins_update->response[ $myskin_basename ] ) ||
                empty( $myskins_update->response[ $myskin_basename ]['package'] )
            ) {
                return $prepared_myskins;
            }

            $prepared_myskins[ $myskin_basename ]['update'] = preg_replace(
                '/(\<p.+>)(.+)(\<a.+\<a.+)\.(.+\<\/p\>)/is',
                '$1 $2 ' . sprintf(
                    $this->_fs->get_text_inline( '%sRenew your license now%s to access version %s security & feature updates, and support.', 'renew-license-now' ),
                    '<a href="' . $this->_fs->pricing_url() . '">', '</a>',
                    $myskins_update->response[ $myskin_basename ]['new_version'] ) .
                '$4',
                $prepared_myskins[ $myskin_basename ]['update']
            );

            // Set to false to prevent the "Update now" link for the context myskin from being shown on the "mySkins" page.
            $prepared_myskins[ $myskin_basename ]['hasPackage'] = false;

            return $prepared_myskins;
        }

        /**
         * Since MCMS version 3.6, a new security feature was added that denies access to repository with a local ip.
         * During development mode we want to be able updating module versions via our localhost repository. This
         * filter white-list all domains including "api.freemius".
         *
         * @link   http://www.emanueletessore.com/mandarincms-download-failed-valid-url-provided/
         *
         * @author Vova Feldman (@svovaf)
         * @since  1.0.4
         *
         * @param bool   $allow
         * @param string $host
         * @param string $url
         *
         * @return bool
         */
        function http_request_host_is_external_filter( $allow, $host, $url ) {
            return ( false !== strpos( $host, 'freemius' ) ) ? true : $allow;
        }

        /**
         * Check for Updates at the defined API endpoint and modify the update array.
         *
         * This function dives into the update api just when MandarinCMS creates its update array,
         * then adds a custom API call and injects the custom module data retrieved from the API.
         * It is reassembled from parts of the native MandarinCMS module update code.
         * See mcms-includes/update.php line 121 for the original mcms_update_modules() function.
         *
         * @author Vova Feldman (@svovaf)
         * @since  1.0.4
         *
         * @uses   FS_Api
         *
         * @param object $transient_data Update array build by MandarinCMS.
         *
         * @return object Modified update array with custom module data.
         */
        function pre_set_site_transient_update_modules_filter( $transient_data ) {
            $this->_logger->entrance();

            /**
             * "modules" or "myskins".
             *
             * @author Leo Fajardo (@leorw)
             * @since  1.2.2
             */
            $module_type = $this->_fs->get_module_type() . 's';

            /**
             * Ensure that we don't mix modules update info with myskins update info.
             *
             * @author Leo Fajardo (@leorw)
             * @since  1.2.2
             */
            if ( "pre_set_site_transient_update_{$module_type}" !== current_filter() ) {
                return $transient_data;
            }

            if ( empty( $transient_data ) ||
                 defined( 'MCMS_FS__UNINSTALL_MODE' )
            ) {
                return $transient_data;
            }

            if ( ! isset( $this->_update_details ) ) {
                // Get module's newest update.
                $new_version = $this->_fs->get_update(
                    false,
                    fs_request_get_bool( 'force-check' ),
                    MCMS_FS__TIME_24_HOURS_IN_SEC / 24
                );

                $this->_update_details = false;

                if ( is_object( $new_version ) ) {
                    $this->_logger->log( 'Found newer module version ' . $new_version->version );

                    /**
                     * Cache module details locally since set_site_transient( 'update_modules' )
                     * called multiple times and the non mcms.org modules are filtered after the
                     * call to .org.
                     *
                     * @since 1.1.8.1
                     */
                    $this->_update_details = $this->get_update_details( $new_version );
                }
            }

            if ( is_object( $this->_update_details ) ) {
                // Add module to transient data.
                $transient_data->response[ $this->_fs->get_module_basename() ] = $this->_fs->is_module() ?
                    $this->_update_details :
                    (array) $this->_update_details;
            }

            return $transient_data;
        }

        /**
         * Get module's required data for the updates mechanism.
         *
         * @author Vova Feldman (@svovaf)
         * @since  2.0.0
         *
         * @param \FS_Module_Tag $new_version
         *
         * @return object
         */
        function get_update_details( FS_Module_Tag $new_version ) {
            $update              = new stdClass();
            $update->slug        = $this->_fs->get_slug();
            $update->new_version = $new_version->version;
            $update->url         = MCMS_FS__ADDRESS;
            $update->package     = $new_version->url;
            $update->tested      = $new_version->tested_up_to_version;
            $update->requires    = $new_version->requires_platform_version;

            $icon = $this->_fs->get_local_icon_url();

            if ( ! empty( $icon ) ) {
                $update->icons = array(
//                    '1x'      => $icon,
//                    '2x'      => $icon,
                    'default' => $icon,
                );
            }

            $update->{$this->_fs->get_module_type()} = $this->_fs->get_module_basename();

            return $update;
        }

        /**
         * Update the updates transient with the module's update information.
         *
         * This method is required for multisite environment.
         * If a module is site activated (not network) and not on the main site,
         * the module will NOT be executed on the network level, therefore, the
         * custom updates logic will not be executed as well, so unless we force
         * the injection of the update into the updates transient, premium updates
         * will not work.
         *
         * @author Vova Feldman (@svovaf)
         * @since  2.0.0
         *
         * @param \FS_Module_Tag $new_version
         */
        function set_update_data( FS_Module_Tag $new_version ) {
            $this->_logger->entrance();

            $transient_key = "update_{$this->_fs->get_module_type()}s";

            $transient_data = get_site_transient( $transient_key );

            $transient_data = is_object( $transient_data ) ?
                $transient_data :
                new stdClass();

            // Alias.
            $basename  = $this->_fs->get_module_basename();
            $is_module = $this->_fs->is_module();

            if ( ! isset( $transient_data->response ) ||
                 ! is_array( $transient_data->response )
            ) {
                $transient_data->response = array();
            } else if ( ! empty( $transient_data->response[ $basename ] ) ) {
                $version = $is_module ?
                    ( ! empty( $transient_data->response[ $basename ]->new_version ) ?
                        $transient_data->response[ $basename ]->new_version :
                        null
                    ) : ( ! empty( $transient_data->response[ $basename ]['new_version'] ) ?
                        $transient_data->response[ $basename ]['new_version'] :
                        null
                    );

                if ( $version == $new_version->version ) {
                    // The update data is already set.
                    return;
                }
            }

            // Remove the added filters.
            $this->remove_transient_filters();

            $this->_update_details = $this->get_update_details( $new_version );

            // Set update data in transient.
            $transient_data->response[ $basename ] = $is_module ?
                $this->_update_details :
                (array) $this->_update_details;

            if ( ! isset( $transient_data->checked ) ||
                 ! is_array( $transient_data->checked )
            ) {
                $transient_data->checked = array();
            }

            // Flag the module as if it was already checked.
            $transient_data->checked[ $basename ] = $this->_fs->get_module_version();
            $transient_data->last_checked         = time();

            set_site_transient( $transient_key, $transient_data );

            $this->add_transient_filters();
        }

        /**
         * @author Leo Fajardo (@leorw)
         * @since 2.0.2
         */
        function delete_update_data() {
            $this->_logger->entrance();

            $transient_key = "update_{$this->_fs->get_module_type()}s";

            $transient_data = get_site_transient( $transient_key );

            // Alias
            $basename = $this->_fs->get_module_basename();

            if ( ! is_object( $transient_data ) ||
                ! isset( $transient_data->response ) ||
                 ! is_array( $transient_data->response ) ||
                empty( $transient_data->response[ $basename ] )
            ) {
                return;
            }

            unset( $transient_data->response[ $basename ] );

            // Remove the added filters.
            $this->remove_transient_filters();

            set_site_transient( $transient_key, $transient_data );

            $this->add_transient_filters();
        }

        /**
         * Try to fetch module's info from .org repository.
         *
         * @author Vova Feldman (@svovaf)
         * @since  1.0.5
         *
         * @param string $action
         * @param object $args
         *
         * @return bool|mixed
         */
        static function _fetch_module_info_from_repository( $action, $args ) {
            $url = $http_url = 'http://api.mandarincms.org/modules/info/1.0/';
            if ( $ssl = mcms_http_supports( array( 'ssl' ) ) ) {
                $url = set_url_scheme( $url, 'https' );
            }

            $args = array(
                'timeout' => 15,
                'body'    => array(
                    'action'  => $action,
                    'request' => serialize( $args )
                )
            );

            $request = mcms_remote_post( $url, $args );

            if ( is_mcms_error( $request ) ) {
                return false;
            }

            $res = maybe_unserialize( mcms_remote_retrieve_body( $request ) );

            if ( ! is_object( $res ) && ! is_array( $res ) ) {
                return false;
            }

            return $res;
        }

        /**
         * Updates information on the "View version x.x details" page with custom data.
         *
         * @author Vova Feldman (@svovaf)
         * @since  1.0.4
         *
         * @uses   FS_Api
         *
         * @param object $data
         * @param string $action
         * @param mixed  $args
         *
         * @return object
         */
        function modules_api_filter( $data, $action = '', $args = null ) {
            $this->_logger->entrance();

            if ( ( 'module_information' !== $action ) ||
                 ! isset( $args->slug )
            ) {
                return $data;
            }

            $addon    = false;
            $is_addon = false;

            if ( $this->_fs->get_slug() !== $args->slug ) {
                $addon = $this->_fs->get_addon_by_slug( $args->slug );

                if ( ! is_object( $addon ) ) {
                    return $data;
                }

                $is_addon = true;
            }

            $module_in_repo = false;
            if ( ! $is_addon ) {
                // Try to fetch info from .org repository.
                $data = self::_fetch_module_info_from_repository( $action, $args );

                $module_in_repo = ( false !== $data );
            }

            if ( ! $module_in_repo ) {
                $data = $args;

                // Fetch as much as possible info from local files.
                $module_local_data = $this->_fs->get_module_data();
                $data->name        = $module_local_data['Name'];
                $data->author      = $module_local_data['Author'];
                $data->sections    = array(
                    'description' => 'Upgrade ' . $module_local_data['Name'] . ' to latest.',
                );

                // @todo Store extra module info on Freemius or parse readme.txt markup.
                /*$info = $this->_fs->get_api_site_scope()->call('/information.json');

if ( !isset($info->error) ) {
    $data = $info;
}*/
            }

            // Get module's newest update.
            $new_version = $this->get_latest_download_details( $is_addon ? $addon->id : false );

            if ( ! is_object( $new_version ) || empty( $new_version->version ) ) {
                $data->version = $this->_fs->get_module_version();
            } else {
                if ( $is_addon ) {
                    $data->name    = $addon->title . ' ' . $this->_fs->get_text_inline( 'Add-On', 'addon' );
                    $data->slug    = $addon->slug;
                    $data->url     = MCMS_FS__ADDRESS;
                    $data->package = $new_version->url;
                }

                if ( ! $module_in_repo ) {
                    $data->last_updated = ! is_null( $new_version->updated ) ? $new_version->updated : $new_version->created;
                    $data->requires     = $new_version->requires_platform_version;
                    $data->tested       = $new_version->tested_up_to_version;
                }

                $data->version       = $new_version->version;
                $data->download_link = $new_version->url;
            }

            return $data;
        }

        /**
         * @author Vova Feldman (@svovaf)
         * @since  1.2.1.7
         *
         * @param number|bool $addon_id
         *
         * @return object
         */
        private function get_latest_download_details( $addon_id = false ) {
            return $this->_fs->_fetch_latest_version( $addon_id );
        }

        /**
         * Checks if a given basename has a matching folder name
         * with the current context module.
         *
         * @author Vova Feldman (@svovaf)
         * @since  1.2.1.6
         *
         * @param string $basename Current module's basename.
         *
         * @return bool
         */
        private function is_correct_folder_name( $basename = '' ) {
            if ( empty( $basename ) ) {
                $basename = $this->_fs->get_module_basename();
            }

            return ( $this->_fs->get_target_folder_name() != trim( dirname( $basename ), '/\\' ) );
        }

        /**
         * This is a special after upgrade handler for migrating modules
         * that didn't use the '-premium' suffix folder structure before
         * the migration.
         *
         * @author Vova Feldman (@svovaf)
         * @since  1.2.1.6
         *
         * @param bool  $response   Install response.
         * @param array $hook_extra Extra arguments passed to hooked filters.
         * @param array $result     Installation result data.
         *
         * @return bool
         */
        function _maybe_update_folder_name( $response, $hook_extra, $result ) {
            $basename = $this->_fs->get_module_basename();

            if ( true !== $response ||
                 empty( $hook_extra ) ||
                 empty( $hook_extra['module'] ) ||
                 $basename !== $hook_extra['module']
            ) {
                return $response;
            }

            $active_modules_basenames = get_option( 'active_modules' );

            for ( $i = 0, $len = count( $active_modules_basenames ); $i < $len; $i ++ ) {
                if ( $basename === $active_modules_basenames[ $i ] ) {
                    // Get filename including extension.
                    $filename = basename( $basename );

                    $new_basename = module_basename(
                        trailingslashit( $this->_fs->get_slug() . ( $this->_fs->is_premium() ? '-premium' : '' ) ) .
                        $filename
                    );

                    // Verify that the expected correct path exists.
                    if ( file_exists( fs_normalize_path( MCMS_PLUGIN_DIR . '/' . $new_basename ) ) ) {
                        // Override active module name.
                        $active_modules_basenames[ $i ] = $new_basename;
                        update_option( 'active_modules', $active_modules_basenames );
                    }

                    break;
                }
            }

            return $response;
        }

        #----------------------------------------------------------------------------------
        #region Auto Activation
        #----------------------------------------------------------------------------------

        /**
         * Installs and active a module when explicitly requested that from a 3rd party service.
         *
         * This logic was inspired by the TGMPA GPL licensed library by Thomas Griffin.
         *
         * @link   http://tgmmoduleactivation.com/
         *
         * @author Vova Feldman
         * @since  1.2.1.7
         *
         * @link   https://make.mandarincms.org/modules/2017/03/16/clarification-of-guideline-8-executable-code-and-installs/
         *
         * @uses   MCMS_Filesystem
         * @uses   MCMS_Error
         * @uses   MCMS_Upgrader
         * @uses   Module_Upgrader
         * @uses   Module_Installer_Skin
         * @uses   Module_Upgrader_Skin
         *
         * @param number|bool $module_id
         *
         * @return array
         */
        function install_and_activate_module( $module_id = false ) {
            if ( ! empty( $module_id ) && ! FS_Module::is_valid_id( $module_id ) ) {
                // Invalid module ID.
                return array(
                    'message' => $this->_fs->get_text_inline( 'Invalid module ID.', 'auto-install-error-invalid-id' ),
                    'code'    => 'invalid_module_id',
                );
            }

            $is_addon = false;
            if ( FS_Module::is_valid_id( $module_id ) &&
                 $module_id != $this->_fs->get_id()
            ) {
                $addon = $this->_fs->get_addon( $module_id );

                if ( ! is_object( $addon ) ) {
                    // Invalid add-on ID.
                    return array(
                        'message' => $this->_fs->get_text_inline( 'Invalid module ID.', 'auto-install-error-invalid-id' ),
                        'code'    => 'invalid_module_id',
                    );
                }

                $slug  = $addon->slug;
                $title = $addon->title . ' ' . $this->_fs->get_text_inline( 'Add-On', 'addon' );

                $is_addon = true;
            } else {
                $slug  = $this->_fs->get_slug();
                $title = $this->_fs->get_module_title() .
                         ( $this->_fs->is_addon() ? ' ' . $this->_fs->get_text_inline( 'Add-On', 'addon' ) : '' );
            }

            if ( $this->is_premium_module_active( $module_id ) ) {
                // Premium version already activated.
                return array(
                    'message' => $is_addon ?
                        $this->_fs->get_text_inline( 'Premium add-on version already installed.', 'auto-install-error-premium-addon-activated' ) :
                        $this->_fs->get_text_inline( 'Premium version already active.', 'auto-install-error-premium-activated' ),
                    'code'    => 'premium_installed',
                );
            }

            $latest_version = $this->get_latest_download_details( $module_id );
            $target_folder  = "{$slug}-premium";

            // Prep variables for Module_Installer_Skin class.
            $extra         = array();
            $extra['slug'] = $target_folder;
            $source        = $latest_version->url;
            $api           = null;

            $install_url = add_query_arg(
                array(
                    'action' => 'install-module',
                    'module' => urlencode( $slug ),
                ),
                'update.php'
            );

            if ( ! class_exists( 'Module_Upgrader', false ) ) {
                // Include required resources for the installation.
                require_once BASED_TREE_URI . 'mcms-admin/includes/class-mcms-upgrader.php';
            }

            $skin_args = array(
                'type'   => 'web',
                'title'  => sprintf( $this->_fs->get_text_inline( 'Installing module: %s', 'installing-module-x' ), $title ),
                'url'    => esc_url_raw( $install_url ),
                'nonce'  => 'install-module_' . $slug,
                'module' => '',
                'api'    => $api,
                'extra'  => $extra,
            );

//			$skin = new Automatic_Upgrader_Skin( $skin_args );
//			$skin = new Module_Installer_Skin( $skin_args );
            $skin = new MCMS_Ajax_Upgrader_Skin( $skin_args );

            // Create a new instance of Module_Upgrader.
            $upgrader = new Module_Upgrader( $skin );

            // Perform the action and install the module from the $source urldecode().
            add_filter( 'upgrader_source_selection', array( &$this, '_maybe_adjust_source_dir' ), 1, 3 );

            $install_result = $upgrader->install( $source );

            remove_filter( 'upgrader_source_selection', array( &$this, '_maybe_adjust_source_dir' ), 1 );

            if ( is_mcms_error( $install_result ) ) {
                return array(
                    'message' => $install_result->get_error_message(),
                    'code'    => $install_result->get_error_code(),
                );
            } elseif ( is_mcms_error( $skin->result ) ) {
                return array(
                    'message' => $skin->result->get_error_message(),
                    'code'    => $skin->result->get_error_code(),
                );
            } elseif ( $skin->get_errors()->get_error_code() ) {
                return array(
                    'message' => $skin->get_error_messages(),
                    'code'    => 'unknown',
                );
            } elseif ( is_null( $install_result ) ) {
                global $mcms_filesystem;

                $error_code    = 'unable_to_connect_to_filesystem';
                $error_message = $this->_fs->get_text_inline( 'Unable to connect to the filesystem. Please confirm your credentials.' );

                // Pass through the error from MCMS_Filesystem if one was raised.
                if ( $mcms_filesystem instanceof MCMS_Filesystem_Base &&
                     is_mcms_error( $mcms_filesystem->errors ) &&
                     $mcms_filesystem->errors->get_error_code()
                ) {
                    $error_message = $mcms_filesystem->errors->get_error_message();
                }

                return array(
                    'message' => $error_message,
                    'code'    => $error_code,
                );
            }

            // Grab the full path to the main module's file.
            $module_activate = $upgrader->module_info();

            // Try to activate the module.
            $activation_result = $this->try_activate_module( $module_activate );

            if ( is_mcms_error( $activation_result ) ) {
                return array(
                    'message' => $activation_result->get_error_message(),
                    'code'    => $activation_result->get_error_code(),
                );
            }

            return $skin->get_upgrade_messages();
        }

        /**
         * Tries to activate a module. If fails, returns the error.
         *
         * @author Vova Feldman
         * @since  1.2.1.7
         *
         * @param string $file_path Path within mcms-modules/ to main module file.
         *                          This determines the styling of the output messages.
         *
         * @return bool|MCMS_Error
         */
        protected function try_activate_module( $file_path ) {
            $activate = activate_module( $file_path, '', $this->_fs->is_network_active() );

            return is_mcms_error( $activate ) ?
                $activate :
                true;
        }

        /**
         * Check if a premium module version is already active.
         *
         * @author Vova Feldman
         * @since  1.2.1.7
         *
         * @param number|bool $module_id
         *
         * @return bool
         */
        private function is_premium_module_active( $module_id = false ) {
            if ( $module_id != $this->_fs->get_id() ) {
                return $this->_fs->is_addon_activated( $module_id, true );
            }

            return is_module_active( $this->_fs->premium_module_basename() );
        }

        /**
         * Adjust the module directory name if necessary.
         * Assumes module has a folder (not a single file module).
         *
         * The final destination directory of a module is based on the subdirectory name found in the
         * (un)zipped source. In some cases this subdirectory name is not the same as the expected
         * slug and the module will not be recognized as installed. This is fixed by adjusting
         * the temporary unzipped source subdirectory name to the expected module slug.
         *
         * @author Vova Feldman
         * @since  1.2.1.7
         *
         * @param string       $source        Path to upgrade/zip-file-name.tmp/subdirectory/.
         * @param string       $remote_source Path to upgrade/zip-file-name.tmp.
         * @param \MCMS_Upgrader $upgrader      Instance of the upgrader which installs the module.
         *
         * @return string|MCMS_Error
         */
        function _maybe_adjust_source_dir( $source, $remote_source, $upgrader ) {
            if ( ! is_object( $GLOBALS['mcms_filesystem'] ) ) {
                return $source;
            }

            // Figure out what the slug is supposed to be.
            $desired_slug = $upgrader->skin->options['extra']['slug'];

            $subdir_name = untrailingslashit( str_replace( trailingslashit( $remote_source ), '', $source ) );

            if ( ! empty( $subdir_name ) && $subdir_name !== $desired_slug ) {
                $from_path = untrailingslashit( $source );
                $to_path   = trailingslashit( $remote_source ) . $desired_slug;

                if ( true === $GLOBALS['mcms_filesystem']->move( $from_path, $to_path ) ) {
                    return trailingslashit( $to_path );
                } else {
                    return new MCMS_Error(
                        'rename_failed',
                        $this->_fs->get_text_inline( 'The remote module package does not contain a folder with the desired slug and renaming did not work.', 'module-package-rename-failure' ),
                        array(
                            'found'    => $subdir_name,
                            'expected' => $desired_slug
                        ) );
                }
            }

            return $source;
        }

        #endregion
    }