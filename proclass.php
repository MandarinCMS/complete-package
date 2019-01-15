<?php
/**
 * Used to set up and fix common variables and include
 * the MandarinCMS procedural and class library.
 *
 * Allows for some configuration in database-settings.php (see default-constants.php)
 *
 * @package MandarinCMS
 */

/**
 * Stores the location of the MandarinCMS directory of functions, classes, and core content.
 *
 * @since 1.0.0
 */
define( 'MCMSINC', 'mcms-roots' );
define( 'MCMSCOMP', 'mcms-components' );

// Include files required for initialization.
require( BASED_TREE_URI . MCMSINC . '/load.php' );
require( BASED_TREE_URI . MCMSINC . '/default-constants.php' );
require_once( BASED_TREE_URI . MCMSINC . '/module.php' );


/*
 * These can't be directly globalized in version.php. When updating,
 * we're including version.php from another installation and don't want
 * these values to be overridden if already set.
 */
global $mcms_version, $mcms_db_version, $tinymce_version, $required_php_version, $required_mysql_version, $mcms_local_package;
require( BASED_TREE_URI . MCMSINC . '/version.php' );

/**
 * If not already configured, `$blog_id` will default to 1 in a single site
 * configuration. In multisite, it will be overridden by default in ms-settings.php.
 *
 * @global int $blog_id
 * @since 2.0.0
 */
global $blog_id;

// Set initial default constants including MCMS_MEMORY_LIMIT, MCMS_MAX_MEMORY_LIMIT, MCMS_DEBUG, SCRIPT_DEBUG, MCMS_CONTENT_DIR and MCMS_CACHE.
mcms_initial_constants();

// Check for the required PHP version and for the MySQL extension or a database drop-in.
mcms_check_php_mysql_versions();

// Disable magic quotes at runtime. Magic quotes are added using mcmsdb later in proclass.php.
@ini_set( 'magic_quotes_runtime', 0 );
@ini_set( 'magic_quotes_sybase',  0 );

// MandarinCMS calculates offsets from UTC.
date_default_timezone_set( 'UTC' );

// Turn register_globals off.
mcms_unregister_GLOBALS();

// Standardize $_SERVER variables across setups.
mcms_fix_server_vars();

// Check if we have received a request due to missing favicon.ico
mcms_favicon_request();

// Check if we're in maintenance mode.
mcms_maintenance();

// Start loading timer.
timer_start();

// Check if we're in MCMS_DEBUG mode.
mcms_debug_mode();

/**
 * Filters whether to enable loading of the advanced-cache.php drop-in.
 *
 * This filter runs before it can be used by modules. It is designed for non-web
 * run-times. If false is returned, advanced-cache.php will never be loaded.
 *
 * @since 4.6.0
 *
 * @param bool $enable_advanced_cache Whether to enable loading advanced-cache.php (if present).
 *                                    Default true.
 */
if ( MCMS_CACHE && apply_filters( 'enable_loading_advanced_cache_dropin', true ) ) {
	// For an advanced caching module to use. Uses a static drop-in because you would only want one.
	MCMS_DEBUG ? include( MCMS_CONTENT_DIR . '/advanced-cache.php' ) : @include( MCMS_CONTENT_DIR . '/advanced-cache.php' );

	// Re-initialize any hooks added manually by advanced-cache.php
	if ( $mcms_filter ) {
		$mcms_filter = MCMS_Hook::build_preinitialized_hooks( $mcms_filter );
	}
}

// Define MCMS_LANG_DIR if not set.
mcms_set_lang_dir();

// Load early MandarinCMS files.
require( BASED_TREE_URI . MCMSINC . '/compat.php' );
require( BASED_TREE_URI . MCMSINC . '/class-mcms-list-util.php' );
require( BASED_TREE_URI . MCMSINC . '/functions.php' );
require( BASED_TREE_URI . MCMSINC . '/class-mcms-matchesmapregex.php' );
require( BASED_TREE_URI . MCMSINC . '/class-mcms.php' );
require( BASED_TREE_URI . MCMSINC . '/class-mcms-error.php' );
require( BASED_TREE_URI . MCMSINC . '/pomo/mo.php' );

// Include the mcmsdb class and, if present, a db.php database drop-in.
global $mcmsdb;
require_mcms_db();

// Set the database table prefix and the format specifiers for database table columns.
$GLOBALS['table_prefix'] = $table_prefix;
mcms_set_mcmsdb_vars();

// Start the MandarinCMS object cache, or an external object cache if the drop-in is present.
mcms_start_object_cache();

// Attach the default filters.
require( BASED_TREE_URI . MCMSINC . '/default-filters.php' );

// Initialize multisite if enabled.
if ( is_multisite() ) {
	require( BASED_TREE_URI . MCMSINC . '/class-mcms-site-query.php' );
	require( BASED_TREE_URI . MCMSINC . '/class-mcms-network-query.php' );
	require( BASED_TREE_URI . MCMSINC . '/ms-blogs.php' );
	require( BASED_TREE_URI . MCMSINC . '/ms-settings.php' );
} elseif ( ! defined( 'MULTISITE' ) ) {
	define( 'MULTISITE', false );
}

register_shutdown_function( 'shutdown_action_hook' );

// Stop most of MandarinCMS from being loaded if we just want the basics.
if ( SHORTINIT )
	return false;

// Load the L10n library.
require_once( BASED_TREE_URI . MCMSINC . '/l10n.php' );
require_once( BASED_TREE_URI . MCMSINC . '/class-mcms-locale.php' );
require_once( BASED_TREE_URI . MCMSINC . '/class-mcms-locale-switcher.php' );

// Run the installer if MandarinCMS is not installed.
mcms_not_installed();

// Load most of MandarinCMS.
require( BASED_TREE_URI . MCMSINC . '/class-mcms-walker.php' );
require( BASED_TREE_URI . MCMSINC . '/class-mcms-ajax-response.php' );
require( BASED_TREE_URI . MCMSINC . '/formatting.php' );
require( BASED_TREE_URI . MCMSINC . '/capabilities.php' );
require( BASED_TREE_URI . MCMSINC . '/class-mcms-roles.php' );
require( BASED_TREE_URI . MCMSINC . '/class-mcms-role.php' );
require( BASED_TREE_URI . MCMSINC . '/class-mcms-user.php' );
require( BASED_TREE_URI . MCMSINC . '/class-mcms-query.php' );
require( BASED_TREE_URI . MCMSINC . '/query.php' );
require( BASED_TREE_URI . MCMSINC . '/date.php' );
require( BASED_TREE_URI . MCMSINC . '/myskin.php' );
require( BASED_TREE_URI . MCMSINC . '/class-mcms-myskin.php' );
require( BASED_TREE_URI . MCMSINC . '/template.php' );
require( BASED_TREE_URI . MCMSINC . '/user.php' );
require( BASED_TREE_URI . MCMSINC . '/class-mcms-user-query.php' );
require( BASED_TREE_URI . MCMSINC . '/class-mcms-session-tokens.php' );
require( BASED_TREE_URI . MCMSINC . '/class-mcms-user-meta-session-tokens.php' );
require( BASED_TREE_URI . MCMSINC . '/meta.php' );
require( BASED_TREE_URI . MCMSINC . '/class-mcms-meta-query.php' );
require( BASED_TREE_URI . MCMSINC . '/class-mcms-metadata-lazyloader.php' );
require( BASED_TREE_URI . MCMSINC . '/general-template.php' );
require( BASED_TREE_URI . MCMSINC . '/link-template.php' );
require( BASED_TREE_URI . MCMSINC . '/author-template.php' );
require( BASED_TREE_URI . MCMSINC . '/post.php' );
require( BASED_TREE_URI . MCMSINC . '/class-walker-page.php' );
require( BASED_TREE_URI . MCMSINC . '/class-walker-page-dropdown.php' );
require( BASED_TREE_URI . MCMSINC . '/class-mcms-post-type.php' );
require( BASED_TREE_URI . MCMSINC . '/class-mcms-post.php' );
require( BASED_TREE_URI . MCMSINC . '/post-template.php' );
require( BASED_TREE_URI . MCMSINC . '/revision.php' );
require( BASED_TREE_URI . MCMSINC . '/post-formats.php' );
require( BASED_TREE_URI . MCMSINC . '/post-thumbnail-template.php' );
require( BASED_TREE_URI . MCMSINC . '/category.php' );
require( BASED_TREE_URI . MCMSINC . '/class-walker-category.php' );
require( BASED_TREE_URI . MCMSINC . '/class-walker-category-dropdown.php' );
require( BASED_TREE_URI . MCMSINC . '/category-template.php' );
require( BASED_TREE_URI . MCMSINC . '/comment.php' );
require( BASED_TREE_URI . MCMSINC . '/class-mcms-comment.php' );
require( BASED_TREE_URI . MCMSINC . '/class-mcms-comment-query.php' );
require( BASED_TREE_URI . MCMSINC . '/class-walker-comment.php' );
require( BASED_TREE_URI . MCMSINC . '/comment-template.php' );
require( BASED_TREE_URI . MCMSINC . '/rewrite.php' );
require( BASED_TREE_URI . MCMSINC . '/class-mcms-rewrite.php' );
require( BASED_TREE_URI . MCMSINC . '/feed.php' );
require( BASED_TREE_URI . MCMSINC . '/bookmark.php' );
require( BASED_TREE_URI . MCMSINC . '/bookmark-template.php' );
require( BASED_TREE_URI . MCMSINC . '/kses.php' );
require( BASED_TREE_URI . MCMSINC . '/cron.php' );
require( BASED_TREE_URI . MCMSINC . '/deprecated.php' );
require( BASED_TREE_URI . MCMSINC . '/script-loader.php' );
require( BASED_TREE_URI . MCMSINC . '/taxonomy.php' );
require( BASED_TREE_URI . MCMSINC . '/class-mcms-taxonomy.php' );
require( BASED_TREE_URI . MCMSINC . '/class-mcms-term.php' );
require( BASED_TREE_URI . MCMSINC . '/class-mcms-term-query.php' );
require( BASED_TREE_URI . MCMSINC . '/class-mcms-tax-query.php' );
require( BASED_TREE_URI . MCMSINC . '/update.php' );
require( BASED_TREE_URI . MCMSINC . '/canonical.php' );
require( BASED_TREE_URI . MCMSINC . '/shortcodes.php' );
require( BASED_TREE_URI . MCMSINC . '/embed.php' );
require( BASED_TREE_URI . MCMSINC . '/class-mcms-embed.php' );
require( BASED_TREE_URI . MCMSINC . '/class-oembed.php' );
require( BASED_TREE_URI . MCMSINC . '/class-mcms-oembed-controller.php' );
require( BASED_TREE_URI . MCMSINC . '/media.php' );
require( BASED_TREE_URI . MCMSINC . '/http.php' );
require( BASED_TREE_URI . MCMSINC . '/class-http.php' );
require( BASED_TREE_URI . MCMSINC . '/class-mcms-http-streams.php' );
require( BASED_TREE_URI . MCMSINC . '/class-mcms-http-curl.php' );
require( BASED_TREE_URI . MCMSINC . '/class-mcms-http-proxy.php' );
require( BASED_TREE_URI . MCMSINC . '/class-mcms-http-cookie.php' );
require( BASED_TREE_URI . MCMSINC . '/class-mcms-http-encoding.php' );
require( BASED_TREE_URI . MCMSINC . '/class-mcms-http-response.php' );
require( BASED_TREE_URI . MCMSINC . '/class-mcms-http-requests-response.php' );
require( BASED_TREE_URI . MCMSINC . '/class-mcms-http-requests-hooks.php' );
require( BASED_TREE_URI . MCMSINC . '/widgets.php' );
require( BASED_TREE_URI . MCMSINC . '/class-mcms-widget.php' );
require( BASED_TREE_URI . MCMSINC . '/class-mcms-widget-factory.php' );
require( BASED_TREE_URI . MCMSINC . '/nav-menu.php' );
require( BASED_TREE_URI . MCMSINC . '/nav-menu-template.php' );
require( BASED_TREE_URI . MCMSINC . '/admin-bar.php' );
require( BASED_TREE_URI . MCMSINC . '/rest-api.php' );
require( BASED_TREE_URI . MCMSINC . '/rest-api/class-mcms-rest-server.php' );
require( BASED_TREE_URI . MCMSINC . '/rest-api/class-mcms-rest-response.php' );
require( BASED_TREE_URI . MCMSINC . '/rest-api/class-mcms-rest-request.php' );
require( BASED_TREE_URI . MCMSINC . '/rest-api/endpoints/class-mcms-rest-controller.php' );
require( BASED_TREE_URI . MCMSINC . '/rest-api/endpoints/class-mcms-rest-posts-controller.php' );
require( BASED_TREE_URI . MCMSINC . '/rest-api/endpoints/class-mcms-rest-attachments-controller.php' );
require( BASED_TREE_URI . MCMSINC . '/rest-api/endpoints/class-mcms-rest-post-types-controller.php' );
require( BASED_TREE_URI . MCMSINC . '/rest-api/endpoints/class-mcms-rest-post-statuses-controller.php' );
require( BASED_TREE_URI . MCMSINC . '/rest-api/endpoints/class-mcms-rest-revisions-controller.php' );
require( BASED_TREE_URI . MCMSINC . '/rest-api/endpoints/class-mcms-rest-taxonomies-controller.php' );
require( BASED_TREE_URI . MCMSINC . '/rest-api/endpoints/class-mcms-rest-terms-controller.php' );
require( BASED_TREE_URI . MCMSINC . '/rest-api/endpoints/class-mcms-rest-users-controller.php' );
require( BASED_TREE_URI . MCMSINC . '/rest-api/endpoints/class-mcms-rest-comments-controller.php' );
require( BASED_TREE_URI . MCMSINC . '/rest-api/endpoints/class-mcms-rest-settings-controller.php' );
require( BASED_TREE_URI . MCMSINC . '/rest-api/fields/class-mcms-rest-meta-fields.php' );
require( BASED_TREE_URI . MCMSINC . '/rest-api/fields/class-mcms-rest-comment-meta-fields.php' );
require( BASED_TREE_URI . MCMSINC . '/rest-api/fields/class-mcms-rest-post-meta-fields.php' );
require( BASED_TREE_URI . MCMSINC . '/rest-api/fields/class-mcms-rest-term-meta-fields.php' );
require( BASED_TREE_URI . MCMSINC . '/rest-api/fields/class-mcms-rest-user-meta-fields.php' );

$GLOBALS['mcms_embed'] = new MCMS_Embed();

// Load multisite-specific files.
if ( is_multisite() ) {
	require( BASED_TREE_URI . MCMSINC . '/ms-functions.php' );
	require( BASED_TREE_URI . MCMSINC . '/ms-default-filters.php' );
	require( BASED_TREE_URI . MCMSINC . '/ms-deprecated.php' );
}

// Define constants that rely on the API to obtain the default value.
// Define must-use module directory constants, which may be overridden in the sunrise.php drop-in.
mcms_module_directory_constants();

$GLOBALS['mcms_module_paths'] = array();

// Load must-use modules.
foreach ( mcms_get_mu_modules() as $mu_module ) {
	include_once( $mu_module );
}
unset( $mu_module );

// Load network activated modules.
if ( is_multisite() ) {
	foreach ( mcms_get_active_network_modules() as $network_module ) {
		mcms_register_module_realpath( $network_module );
		include_once( $network_module );
	}
	unset( $network_module );
}

/**
 * Fires once all must-use and network-activated modules have loaded.
 *
 * @since 2.8.0
 */
do_action( 'mumodules_loaded' );

if ( is_multisite() )
	ms_cookie_constants(  );

// Define constants after multisite is loaded.
mcms_cookie_constants();

// Define and enforce our SSL constants
mcms_ssl_constants();

// Create common globals.
require( BASED_TREE_URI . MCMSINC . '/vars.php' );

// Make taxonomies and posts available to modules and myskins.
// @module authors: warning: these get registered again on the init hook.
create_initial_taxonomies();
create_initial_post_types();

mcms_start_scraping_edited_file_errors();

// Register the default myskin directory root
register_myskin_directory( get_myskin_root() );

// Load active modules.
foreach ( mcms_get_active_and_valid_modules() as $module ) {
	mcms_register_module_realpath( $module );
	include_once( $module );
}
unset( $module );

// Load pluggable functions.
require( BASED_TREE_URI . MCMSINC . '/pluggable.php' );
require( BASED_TREE_URI . MCMSINC . '/pluggable-deprecated.php' );

// Set internal encoding.
mcms_set_internal_encoding();

// Run mcms_cache_postload() if object cache is enabled and the function exists.
if ( MCMS_CACHE && function_exists( 'mcms_cache_postload' ) )
	mcms_cache_postload();

/**
 * Fires once activated modules have loaded.
 *
 * Pluggable functions are also available at this point in the loading order.
 *
 * @since 1.5.0
 */
do_action( 'modules_loaded' );

// Define constants which affect functionality if not already defined.
mcms_functionality_constants();

// Add magic quotes and set up $_REQUEST ( $_GET + $_POST )
mcms_magic_quotes();

/**
 * Fires when comment cookies are sanitized.
 *
 * @since 2.0.11
 */
do_action( 'sanitize_comment_cookies' );

/**
 * MandarinCMS Query object
 * @global MCMS_Query $mcms_the_query
 * @since 2.0.0
 */
$GLOBALS['mcms_the_query'] = new MCMS_Query();

/**
 * Holds the reference to @see $mcms_the_query
 * Use this global for MandarinCMS queries
 * @global MCMS_Query $mcms_query
 * @since 1.5.0
 */
$GLOBALS['mcms_query'] = $GLOBALS['mcms_the_query'];

/**
 * Holds the MandarinCMS Rewrite object for creating pretty URLs
 * @global MCMS_Rewrite $mcms_rewrite
 * @since 1.5.0
 */
$GLOBALS['mcms_rewrite'] = new MCMS_Rewrite();

/**
 * MandarinCMS Object
 * @global MCMS $mcms
 * @since 2.0.0
 */
$GLOBALS['mcms'] = new MCMS();

/**
 * MandarinCMS Widget Factory Object
 * @global MCMS_Widget_Factory $mcms_widget_factory
 * @since 2.8.0
 */
$GLOBALS['mcms_widget_factory'] = new MCMS_Widget_Factory();

/**
 * MandarinCMS User Roles
 * @global MCMS_Roles $mcms_roles
 * @since 2.0.0
 */
$GLOBALS['mcms_roles'] = new MCMS_Roles();

/**
 * Fires before the myskin is loaded.
 *
 * @since 2.6.0
 */
do_action( 'setup_myskin' );

// Define the template related constants.
mcms_templating_constants(  );

// Load the default text localization domain.
load_default_textdomain();

$locale = get_locale();
$locale_file = MCMS_LANG_DIR . "/$locale.php";
if ( ( 0 === validate_file( $locale ) ) && is_readable( $locale_file ) )
	require( $locale_file );
unset( $locale_file );

/**
 * MandarinCMS Locale object for loading locale domain date and various strings.
 * @global MCMS_Locale $mcms_locale
 * @since 2.1.0
 */
$GLOBALS['mcms_locale'] = new MCMS_Locale();

/**
 *  MandarinCMS Locale Switcher object for switching locales.
 *
 * @since 4.7.0
 *
 * @global MCMS_Locale_Switcher $mcms_locale_switcher MandarinCMS locale switcher object.
 */
$GLOBALS['mcms_locale_switcher'] = new MCMS_Locale_Switcher();
$GLOBALS['mcms_locale_switcher']->init();

// Load the functions for the active myskin, for both parent and child myskin if applicable.
if ( ! mcms_installing() || 'register-activation.php' === $pagenow ) {
	if ( TEMPLATEPATH !== STYLESHEETPATH && file_exists( STYLESHEETPATH . '/functions.php' ) )
		include( STYLESHEETPATH . '/functions.php' );
	if ( file_exists( TEMPLATEPATH . '/functions.php' ) )
		include( TEMPLATEPATH . '/functions.php' );
}

/**
 * Fires after the myskin is loaded.
 *
 * @since 3.0.0
 */
do_action( 'after_setup_myskin' );

// Set up current user.
$GLOBALS['mcms']->init();

/**
 * Fires after MandarinCMS has finished loading but before any headers are sent.
 *
 * Most of MCMS is loaded at this stage, and the user is authenticated. MCMS continues
 * to load on the {@see 'init'} hook that follows (e.g. widgets), and many modules instantiate
 * themselves on it for all sorts of reasons (e.g. they need a user, a taxonomy, etc.).
 *
 * If you wish to plug an action once MCMS is loaded, use the {@see 'mcms_loaded'} hook below.
 *
 * @since 1.5.0
 */
do_action( 'init' );

// Check site status
if ( is_multisite() ) {
	if ( true !== ( $file = ms_site_check() ) ) {
		require( $file );
		die();
	}
	unset($file);
}

/**
 * This hook is fired once MCMS, all modules, and the myskin are fully loaded and instantiated.
 *
 * Ajax requests should use mcms-admin/admin-ajax.php. admin-ajax.php can handle requests for
 * users not logged in.
 *
 * @link https://dev.mandarincms.com/AJAX_in_Modules
 *
 * @since 3.0.0
 */
do_action( 'mcms_loaded' );
