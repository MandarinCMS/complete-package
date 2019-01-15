<?php
    /**
     * @package     Freemius
     * @copyright   Copyright (c) 2015, Freemius, Inc.
     * @license     https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License Version 3
     * @since       1.0.4
     */

    if ( ! defined( 'BASED_TREE_URI' ) ) {
        exit;
    }

    if ( ! defined( 'MCMS_FS__SLUG' ) ) {
        define( 'MCMS_FS__SLUG', 'freemius' );
    }
    if ( ! defined( 'MCMS_FS__DEV_MODE' ) ) {
        define( 'MCMS_FS__DEV_MODE', false );
    }

    #--------------------------------------------------------------------------------
    #region API Connectivity Issues Simulation
    #--------------------------------------------------------------------------------

    if ( ! defined( 'MCMS_FS__SIMULATE_NO_API_CONNECTIVITY' ) ) {
        define( 'MCMS_FS__SIMULATE_NO_API_CONNECTIVITY', false );
    }
    if ( ! defined( 'MCMS_FS__SIMULATE_NO_CURL' ) ) {
        define( 'MCMS_FS__SIMULATE_NO_CURL', false );
    }
    if ( ! defined( 'MCMS_FS__SIMULATE_NO_API_CONNECTIVITY_CLOUDFLARE' ) ) {
        define( 'MCMS_FS__SIMULATE_NO_API_CONNECTIVITY_CLOUDFLARE', false );
    }
    if ( ! defined( 'MCMS_FS__SIMULATE_NO_API_CONNECTIVITY_SQUID_ACL' ) ) {
        define( 'MCMS_FS__SIMULATE_NO_API_CONNECTIVITY_SQUID_ACL', false );
    }
    if ( MCMS_FS__SIMULATE_NO_CURL ) {
        define( 'FS_SDK__SIMULATE_NO_CURL', true );
    }
    if ( MCMS_FS__SIMULATE_NO_API_CONNECTIVITY_CLOUDFLARE ) {
        define( 'FS_SDK__SIMULATE_NO_API_CONNECTIVITY_CLOUDFLARE', true );
    }
    if ( MCMS_FS__SIMULATE_NO_API_CONNECTIVITY_SQUID_ACL ) {
        define( 'FS_SDK__SIMULATE_NO_API_CONNECTIVITY_SQUID_ACL', true );
    }

    #endregion

    if ( ! defined( 'MCMS_FS__SIMULATE_FREEMIUS_OFF' ) ) {
        define( 'MCMS_FS__SIMULATE_FREEMIUS_OFF', false );
    }

    if ( ! defined( 'MCMS_FS__PING_API_ON_IP_OR_HOST_CHANGES' ) ) {
        /**
         * @since  1.1.7.3
         * @author Vova Feldman (@svovaf)
         *
         * I'm not sure if shared servers periodically change IP, or the subdomain of the
         * admin dashboard. Also, I've seen sites that have strange loop of switching
         * between domains on a daily basis. Therefore, to eliminate the risk of
         * multiple unwanted connectivity test pings, temporary ignore domain or
         * server IP changes.
         */
        define( 'MCMS_FS__PING_API_ON_IP_OR_HOST_CHANGES', false );
    }

    /**
     * If your dev environment supports custom public network IP setup
     * like VVV, please update MCMS_FS__LOCALHOST_IP with your public IP
     * and uncomment it during dev.
     */
    if ( ! defined( 'MCMS_FS__LOCALHOST_IP' ) ) {
        // VVV default public network IP.
        define( 'MCMS_FS__VVV_DEFAULT_PUBLIC_IP', '192.168.50.4' );

//		define( 'MCMS_FS__LOCALHOST_IP', MCMS_FS__VVV_DEFAULT_PUBLIC_IP );
    }

    /**
     * If true and running with secret key, the opt-in process
     * will skip the email activation process which is invoked
     * when the email of the context user already exist in Freemius
     * database (as a security precaution, to prevent sharing user
     * secret with unauthorized entity).
     *
     * IMPORTANT:
     *      AS A SECURITY PRECAUTION, WE VALIDATE THE TIMESTAMP OF THE OPT-IN REQUEST.
     *      THEREFORE, MAKE SURE THAT WHEN USING THIS PARAMETER,YOUR TESTING ENVIRONMENT'S
     *      CLOCK IS SYNCED.
     */
    if ( ! defined( 'MCMS_FS__SKIP_EMAIL_ACTIVATION' ) ) {
        define( 'MCMS_FS__SKIP_EMAIL_ACTIVATION', false );
    }


    #--------------------------------------------------------------------------------
    #region Directories
    #--------------------------------------------------------------------------------

    if ( ! defined( 'MCMS_FS__DIR' ) ) {
        define( 'MCMS_FS__DIR', dirname( __FILE__ ) );
    }
    if ( ! defined( 'MCMS_FS__DIR_INCLUDES' ) ) {
        define( 'MCMS_FS__DIR_INCLUDES', MCMS_FS__DIR . '/includes' );
    }
    if ( ! defined( 'MCMS_FS__DIR_TEMPLATES' ) ) {
        define( 'MCMS_FS__DIR_TEMPLATES', MCMS_FS__DIR . '/templates' );
    }
    if ( ! defined( 'MCMS_FS__DIR_ASSETS' ) ) {
        define( 'MCMS_FS__DIR_ASSETS', MCMS_FS__DIR . '/assets' );
    }
    if ( ! defined( 'MCMS_FS__DIR_CSS' ) ) {
        define( 'MCMS_FS__DIR_CSS', MCMS_FS__DIR_ASSETS . '/css' );
    }
    if ( ! defined( 'MCMS_FS__DIR_JS' ) ) {
        define( 'MCMS_FS__DIR_JS', MCMS_FS__DIR_ASSETS . '/js' );
    }
    if ( ! defined( 'MCMS_FS__DIR_IMG' ) ) {
        define( 'MCMS_FS__DIR_IMG', MCMS_FS__DIR_ASSETS . '/img' );
    }
    if ( ! defined( 'MCMS_FS__DIR_SDK' ) ) {
        define( 'MCMS_FS__DIR_SDK', MCMS_FS__DIR_INCLUDES . '/sdk' );
    }

    #endregion

    /**
     * Domain / URL / Address
     */
    define( 'MCMS_FS__ROOT_DOMAIN_PRODUCTION', 'freemius.com' );
    define( 'MCMS_FS__DOMAIN_PRODUCTION', 'mcms.freemius.com' );
    define( 'MCMS_FS__ADDRESS_PRODUCTION', 'https://' . MCMS_FS__DOMAIN_PRODUCTION );

    if ( ! defined( 'MCMS_FS__DOMAIN_LOCALHOST' ) ) {
        define( 'MCMS_FS__DOMAIN_LOCALHOST', 'mcms.freemius' );
    }
    if ( ! defined( 'MCMS_FS__ADDRESS_LOCALHOST' ) ) {
        define( 'MCMS_FS__ADDRESS_LOCALHOST', 'http://' . MCMS_FS__DOMAIN_LOCALHOST . ':8080' );
    }

    if ( ! defined( 'MCMS_FS__TESTING_DOMAIN' ) ) {
        define( 'MCMS_FS__TESTING_DOMAIN', 'fsmcms' );
    }

    #--------------------------------------------------------------------------------
    #region HTTP
    #--------------------------------------------------------------------------------

    if ( ! defined( 'MCMS_FS__IS_HTTP_REQUEST' ) ) {
        define( 'MCMS_FS__IS_HTTP_REQUEST', isset( $_SERVER['HTTP_HOST'] ) );
    }

    if ( ! defined( 'MCMS_FS__IS_HTTPS' ) ) {
        define( 'MCMS_FS__IS_HTTPS', ( MCMS_FS__IS_HTTP_REQUEST &&
                                     // Checks if CloudFlare's HTTPS (Flexible SSL support).
                                     isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) &&
                                     'https' === strtolower( $_SERVER['HTTP_X_FORWARDED_PROTO'] )
                                   ) ||
                                   // Check if HTTPS request.
                                   ( isset( $_SERVER['HTTPS'] ) && 'on' == $_SERVER['HTTPS'] ) ||
                                   ( isset( $_SERVER['SERVER_PORT'] ) && 443 == $_SERVER['SERVER_PORT'] )
        );
    }

    if ( ! defined( 'MCMS_FS__IS_POST_REQUEST' ) ) {
        define( 'MCMS_FS__IS_POST_REQUEST', ( MCMS_FS__IS_HTTP_REQUEST &&
                                            strtoupper( $_SERVER['REQUEST_METHOD'] ) == 'POST' ) );
    }

    if ( ! defined( 'MCMS_FS__REMOTE_ADDR' ) ) {
        define( 'MCMS_FS__REMOTE_ADDR', fs_get_ip() );
    }

    if ( ! defined( 'MCMS_FS__IS_LOCALHOST' ) ) {
        if ( defined( 'MCMS_FS__LOCALHOST_IP' ) ) {
            define( 'MCMS_FS__IS_LOCALHOST', ( MCMS_FS__LOCALHOST_IP === MCMS_FS__REMOTE_ADDR ) );
        } else {
            define( 'MCMS_FS__IS_LOCALHOST', MCMS_FS__IS_HTTP_REQUEST &&
                                           is_string( MCMS_FS__REMOTE_ADDR ) &&
                                           ( substr( MCMS_FS__REMOTE_ADDR, 0, 4 ) === '127.' ||
                                             MCMS_FS__REMOTE_ADDR === '::1' )
            );
        }
    }

    if ( ! defined( 'MCMS_FS__IS_LOCALHOST_FOR_SERVER' ) ) {
        define( 'MCMS_FS__IS_LOCALHOST_FOR_SERVER', ( ! MCMS_FS__IS_HTTP_REQUEST ||
                                                    false !== strpos( $_SERVER['HTTP_HOST'], 'localhost' ) ) );
    }

    #endregion

    if ( ! defined( 'MCMS_FS__IS_PRODUCTION_MODE' ) ) {
        // By default, run with Freemius production servers.
        define( 'MCMS_FS__IS_PRODUCTION_MODE', true );
    }

    if ( ! defined( 'MCMS_FS__ADDRESS' ) ) {
        define( 'MCMS_FS__ADDRESS', ( MCMS_FS__IS_PRODUCTION_MODE ? MCMS_FS__ADDRESS_PRODUCTION : MCMS_FS__ADDRESS_LOCALHOST ) );
    }


    #--------------------------------------------------------------------------------
    #region API
    #--------------------------------------------------------------------------------

    if ( ! defined( 'MCMS_FS__API_ADDRESS_LOCALHOST' ) ) {
        define( 'MCMS_FS__API_ADDRESS_LOCALHOST', 'http://api.freemius:8080' );
    }
    if ( ! defined( 'MCMS_FS__API_SANDBOX_ADDRESS_LOCALHOST' ) ) {
        define( 'MCMS_FS__API_SANDBOX_ADDRESS_LOCALHOST', 'http://sandbox-api.freemius:8080' );
    }

    // Set API address for local testing.
    if ( ! MCMS_FS__IS_PRODUCTION_MODE ) {
        if ( ! defined( 'FS_API__ADDRESS' ) ) {
            define( 'FS_API__ADDRESS', MCMS_FS__API_ADDRESS_LOCALHOST );
        }
        if ( ! defined( 'FS_API__SANDBOX_ADDRESS' ) ) {
            define( 'FS_API__SANDBOX_ADDRESS', MCMS_FS__API_SANDBOX_ADDRESS_LOCALHOST );
        }
    }

    #endregion

    #--------------------------------------------------------------------------------
    #region Checkout
    #--------------------------------------------------------------------------------

    if ( ! defined( 'FS_CHECKOUT__ADDRESS_PRODUCTION' ) ) {
        define( 'FS_CHECKOUT__ADDRESS_PRODUCTION', 'https://checkout.freemius.com' );
    }

    if ( ! defined( 'FS_CHECKOUT__ADDRESS_LOCALHOST' ) ) {
        define( 'FS_CHECKOUT__ADDRESS_LOCALHOST', 'http://checkout.freemius-local.com:8080' );
    }

    if ( ! defined( 'FS_CHECKOUT__ADDRESS' ) ) {
        define( 'FS_CHECKOUT__ADDRESS', ( MCMS_FS__IS_PRODUCTION_MODE ? FS_CHECKOUT__ADDRESS_PRODUCTION : FS_CHECKOUT__ADDRESS_LOCALHOST ) );
    }

    #endregion

    define( 'MCMS_FS___OPTION_PREFIX', 'fs' . ( MCMS_FS__IS_PRODUCTION_MODE ? '' : '_dbg' ) . '_' );

    if ( ! defined( 'MCMS_FS__ACCOUNTS_OPTION_NAME' ) ) {
        define( 'MCMS_FS__ACCOUNTS_OPTION_NAME', MCMS_FS___OPTION_PREFIX . 'accounts' );
    }
    if ( ! defined( 'MCMS_FS__API_CACHE_OPTION_NAME' ) ) {
        define( 'MCMS_FS__API_CACHE_OPTION_NAME', MCMS_FS___OPTION_PREFIX . 'api_cache' );
    }
    if ( ! defined( 'MCMS_FS__GDPR_OPTION_NAME' ) ) {
        define( 'MCMS_FS__GDPR_OPTION_NAME', MCMS_FS___OPTION_PREFIX . 'gdpr' );
    }
    define( 'MCMS_FS__OPTIONS_OPTION_NAME', MCMS_FS___OPTION_PREFIX . 'options' );

    /**
     * Module types
     *
     * @since 1.2.2
     */
    define( 'MCMS_FS__MODULE_TYPE_PLUGIN', 'module' );
    define( 'MCMS_FS__MODULE_TYPE_THEME', 'myskin' );

    /**
     * Billing Frequencies
     */
    define( 'MCMS_FS__PERIOD_ANNUALLY', 'annual' );
    define( 'MCMS_FS__PERIOD_MONTHLY', 'monthly' );
    define( 'MCMS_FS__PERIOD_LIFETIME', 'lifetime' );

    /**
     * Plans
     */
    define( 'MCMS_FS__PLAN_DEFAULT_PAID', false );
    define( 'MCMS_FS__PLAN_FREE', 'free' );
    define( 'MCMS_FS__PLAN_TRIAL', 'trial' );

    /**
     * Times in seconds
     */
    if ( ! defined( 'MCMS_FS__TIME_5_MIN_IN_SEC' ) ) {
        define( 'MCMS_FS__TIME_5_MIN_IN_SEC', 300 );
    }
    if ( ! defined( 'MCMS_FS__TIME_10_MIN_IN_SEC' ) ) {
        define( 'MCMS_FS__TIME_10_MIN_IN_SEC', 600 );
    }
//	define( 'MCMS_FS__TIME_15_MIN_IN_SEC', 900 );
    if ( ! defined( 'MCMS_FS__TIME_12_HOURS_IN_SEC' ) ) {
        define( 'MCMS_FS__TIME_12_HOURS_IN_SEC', 43200 );
    }
    if ( ! defined( 'MCMS_FS__TIME_24_HOURS_IN_SEC' ) ) {
        define( 'MCMS_FS__TIME_24_HOURS_IN_SEC', MCMS_FS__TIME_12_HOURS_IN_SEC * 2 );
    }
    if ( ! defined( 'MCMS_FS__TIME_WEEK_IN_SEC' ) ) {
        define( 'MCMS_FS__TIME_WEEK_IN_SEC', 7 * MCMS_FS__TIME_24_HOURS_IN_SEC );
    }

    #--------------------------------------------------------------------------------
    #region Debugging
    #--------------------------------------------------------------------------------

    if ( ! defined( 'MCMS_FS__DEBUG_SDK' ) ) {
        $debug_mode = get_option( 'fs_debug_mode', null );

        if ( $debug_mode === null ) {
            $debug_mode = false;
            add_option( 'fs_debug_mode', $debug_mode );
        }

        define( 'MCMS_FS__DEBUG_SDK', is_numeric( $debug_mode ) ? ( 0 < $debug_mode ) : MCMS_FS__DEV_MODE );
    }

    if ( ! defined( 'MCMS_FS__ECHO_DEBUG_SDK' ) ) {
        define( 'MCMS_FS__ECHO_DEBUG_SDK', MCMS_FS__DEV_MODE && ! empty( $_GET['fs_dbg_echo'] ) );
    }
    if ( ! defined( 'MCMS_FS__LOG_DATETIME_FORMAT' ) ) {
        define( 'MCMS_FS__LOG_DATETIME_FORMAT', 'Y-m-d H:i:s' );
    }
    if ( ! defined( 'FS_API__LOGGER_ON' ) ) {
        define( 'FS_API__LOGGER_ON', MCMS_FS__DEBUG_SDK );
    }

    if ( MCMS_FS__ECHO_DEBUG_SDK ) {
        error_reporting( E_ALL );
    }

    #endregion

    if ( ! defined( 'MCMS_FS__SCRIPT_START_TIME' ) ) {
        define( 'MCMS_FS__SCRIPT_START_TIME', time() );
    }
    if ( ! defined( 'MCMS_FS__DEFAULT_PRIORITY' ) ) {
        define( 'MCMS_FS__DEFAULT_PRIORITY', 10 );
    }
    if ( ! defined( 'MCMS_FS__LOWEST_PRIORITY' ) ) {
        define( 'MCMS_FS__LOWEST_PRIORITY', 999999999 );
    }

    #--------------------------------------------------------------------------------
    #region Multisite Network
    #--------------------------------------------------------------------------------

    /**
     * Do not use this define directly, it will have the wrong value
     * during module uninstall/deletion when the inclusion of the module
     * is triggered due to registration with register_uninstall_hook().
     *
     * Instead, use fs_is_network_admin().
     *
     * @author Vova Feldman (@svovaf)
     */
    if ( ! defined( 'MCMS_FS__IS_NETWORK_ADMIN' ) ) {
        define( 'MCMS_FS__IS_NETWORK_ADMIN',
            is_network_admin() ||
            ( is_multisite() &&
              ( ( defined( 'DOING_AJAX' ) && DOING_AJAX &&
                  ( isset( $_REQUEST['_fs_network_admin'] ) /*||
                    ( ! empty( $_REQUEST['action'] ) && 'delete-module' === $_REQUEST['action'] )*/ )
                ) ||
                // Module uninstall.
                defined( 'MCMS_UNINSTALL_PLUGIN' ) )
            )
        );
    }

    /**
     * Do not use this define directly, it will have the wrong value
     * during module uninstall/deletion when the inclusion of the module
     * is triggered due to registration with register_uninstall_hook().
     *
     * Instead, use fs_is_blog_admin().
     *
     * @author Vova Feldman (@svovaf)
     */
    if ( ! defined( 'MCMS_FS__IS_BLOG_ADMIN' ) ) {
        define( 'MCMS_FS__IS_BLOG_ADMIN', is_blog_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX && isset( $_REQUEST['_fs_blog_admin'] ) ) );
    }

    if ( ! defined( 'MCMS_FS__SHOW_NETWORK_EVEN_WHEN_DELEGATED' ) ) {
        // Set to true to show network level settings even if delegated to site admins.
        define( 'MCMS_FS__SHOW_NETWORK_EVEN_WHEN_DELEGATED', false );
    }

    #endregion

    if ( ! defined( 'MCMS_FS__DEMO_MODE' ) ) {
        define( 'MCMS_FS__DEMO_MODE', false );
    }