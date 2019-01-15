<?php
/**
 * @package MCMSSEO\Main
 */

if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

/**
 * @internal Nobody should be able to overrule the real version number as this can cause serious issues
 * with the options, so no if ( ! defined() )
 */
define( 'MCMSSEO_VERSION', '3.8' );

if ( ! defined( 'MCMSSEO_PATH' ) ) {
	define( 'MCMSSEO_PATH', module_dir_path( MCMSSEO_FILE ) );
}

if ( ! defined( 'MCMSSEO_BASENAME' ) ) {
	define( 'MCMSSEO_BASENAME', module_basename( MCMSSEO_FILE ) );
}

/* ***************************** CLASS AUTOLOADING *************************** */

/**
 * Auto load our class files
 *
 * @param string $class Class name.
 *
 * @return void
 */
function mcmsseo_auto_load( $class ) {
	static $classes = null;

	if ( $classes === null ) {
		$classes = array(
			'mcms_list_table'   => BASED_TREE_URI . 'mcms-admin/includes/class-mcms-list-table.php',
			'walker_category' => BASED_TREE_URI . 'mcms-includes/category-template.php',
			'pclzip'          => BASED_TREE_URI . 'mcms-admin/includes/class-pclzip.php',
		);
	}

	$cn = strtolower( $class );

	if ( ! class_exists( $class ) && isset( $classes[ $cn ] ) ) {
		require_once( $classes[ $cn ] );
	}
}

if ( file_exists( MCMSSEO_PATH . '/vendor/autoload_52.php' ) ) {
	require MCMSSEO_PATH . '/vendor/autoload_52.php';
}
elseif ( ! class_exists( 'MCMSSEO_Options' ) ) { // Still checking since might be site-level autoload R.
	add_action( 'admin_init', 'ultimatum_mcmsseo_missing_autoload', 1 );

	return;
}

if ( function_exists( 'spl_autoload_register' ) ) {
	spl_autoload_register( 'mcmsseo_auto_load' );
}

/* ********************* DEFINES DEPENDING ON AUTOLOADED CODE ********************* */

/**
 * Defaults to production, for safety
 */
if ( ! defined( 'YOAST_ENVIRONMENT' ) ) {
	define( 'YOAST_ENVIRONMENT', 'production' );
}

/**
 * Only use minified assets when we are in a production environment
 */
if ( ! defined( 'MCMSSEO_CSSJS_SUFFIX' ) ) {
	define( 'MCMSSEO_CSSJS_SUFFIX', ( 'development' !== YOAST_ENVIRONMENT ) ? '.min' : '' );
}

/* ***************************** PLUGIN (DE-)ACTIVATION *************************** */

/**
 * Run single site / network-wide activation of the module.
 *
 * @param bool $networkwide Whether the module is being activated network-wide.
 */
function mcmsseo_activate( $networkwide = false ) {
	if ( ! is_multisite() || ! $networkwide ) {
		_mcmsseo_activate();
	}
	else {
		/* Multi-site network activation - activate the module for all blogs */
		mcmsseo_network_activate_deactivate( true );
	}
}

/**
 * Run single site / network-wide de-activation of the module.
 *
 * @param bool $networkwide Whether the module is being de-activated network-wide.
 */
function mcmsseo_deactivate( $networkwide = false ) {
	if ( ! is_multisite() || ! $networkwide ) {
		_mcmsseo_deactivate();
	}
	else {
		/* Multi-site network activation - de-activate the module for all blogs */
		mcmsseo_network_activate_deactivate( false );
	}
}

/**
 * Run network-wide (de-)activation of the module
 *
 * @param bool $activate True for module activation, false for de-activation.
 */
function mcmsseo_network_activate_deactivate( $activate = true ) {
	global $mcmsdb;

	$network_blogs = $mcmsdb->get_col( $mcmsdb->prepare( "SELECT blog_id FROM $mcmsdb->blogs WHERE site_id = %d", $mcmsdb->siteid ) );

	if ( is_array( $network_blogs ) && $network_blogs !== array() ) {
		foreach ( $network_blogs as $blog_id ) {
			switch_to_blog( $blog_id );

			if ( $activate === true ) {
				_mcmsseo_activate();
			}
			else {
				_mcmsseo_deactivate();
			}

			restore_current_blog();
		}
	}
}

/**
 * Runs on activation of the module.
 */
function _mcmsseo_activate() {
	require_once( MCMSSEO_PATH . 'inc/mcmsseo-functions.php' );
	require_once( MCMSSEO_PATH . 'inc/class-mcmsseo-installation.php' );

	mcmsseo_load_textdomain(); // Make sure we have our translations available for the defaults.

	new MCMSSEO_Installation();

	MCMSSEO_Options::get_instance();
	if ( ! is_multisite() ) {
		MCMSSEO_Options::initialize();
	}
	else {
		MCMSSEO_Options::maybe_set_multisite_defaults( true );
	}
	MCMSSEO_Options::ensure_options_exist();

	if ( is_multisite() && ms_is_switched() ) {
		delete_option( 'rewrite_rules' );
	}
	else {
		$mcmsseo_rewrite = new MCMSSEO_Rewrite();
		$mcmsseo_rewrite->schedule_flush();
	}

	mcmsseo_add_capabilities();

	// Clear cache so the changes are obvious.
	MCMSSEO_Utils::clear_cache();

	do_action( 'mcmsseo_activate' );
}

/**
 * On deactivation, flush the rewrite rules so XML sitemaps stop working.
 */
function _mcmsseo_deactivate() {
	require_once( MCMSSEO_PATH . 'inc/mcmsseo-functions.php' );

	if ( is_multisite() && ms_is_switched() ) {
		delete_option( 'rewrite_rules' );
	}
	else {
		add_action( 'shutdown', 'flush_rewrite_rules' );
	}

	mcmsseo_remove_capabilities();

	// Clear cache so the changes are obvious.
	MCMSSEO_Utils::clear_cache();

	do_action( 'mcmsseo_deactivate' );
}

/**
 * Run mcmsseo activation routine on creation / activation of a multisite blog if MCMSSEO is activated
 * network-wide.
 *
 * Will only be called by multisite actions.
 *
 * @internal Unfortunately will fail if the module is in the must-use directory
 * @see      https://core.trac.mandarincms.com/ticket/24205
 *
 * @param int $blog_id Blog ID.
 */
function mcmsseo_on_activate_blog( $blog_id ) {
	if ( ! function_exists( 'is_module_active_for_network' ) ) {
		require_once( BASED_TREE_URI . '/mcms-admin/includes/module.php' );
	}

	if ( is_module_active_for_network( module_basename( MCMSSEO_FILE ) ) ) {
		switch_to_blog( $blog_id );
		mcmsseo_activate( false );
		restore_current_blog();
	}
}


/* ***************************** PLUGIN LOADING *************************** */

/**
 * Load translations
 */
function mcmsseo_load_textdomain() {
	$mcmsseo_path = str_replace( '\\', '/', MCMSSEO_PATH );
	$mu_path    = str_replace( '\\', '/', MCMSMU_PLUGIN_DIR );

	if ( false !== stripos( $mcmsseo_path, $mu_path ) ) {
		load_mumodule_textdomain( 'mandarincms-seo', dirname( MCMSSEO_BASENAME ) . '/languages/' );
	}
	else {
		load_module_textdomain( 'mandarincms-seo', false, dirname( MCMSSEO_BASENAME ) . '/languages/' );
	}
}

add_action( 'modules_loaded', 'mcmsseo_load_textdomain' );


/**
 * On modules_loaded: load the minimum amount of essential files for this module
 */
function mcmsseo_init() {

	require_once( MCMSSEO_PATH . 'inc/mcmsseo-functions.php' );
	require_once( MCMSSEO_PATH . 'inc/mcmsseo-functions-deprecated.php' );

	// Make sure our option and meta value validation routines and default values are always registered and available.
	MCMSSEO_Options::get_instance();
	MCMSSEO_Meta::init();

	$options = MCMSSEO_Options::get_options( array( 'mcmsseo', 'mcmsseo_permalinks', 'mcmsseo_xml' ) );
	if ( version_compare( $options['version'], MCMSSEO_VERSION, '<' ) ) {
		new MCMSSEO_Upgrade();
		// Get a cleaned up version of the $options.
		$options = MCMSSEO_Options::get_options( array( 'mcmsseo', 'mcmsseo_permalinks', 'mcmsseo_xml' ) );
	}

	if ( $options['stripcategorybase'] === true ) {
		$GLOBALS['mcmsseo_rewrite'] = new MCMSSEO_Rewrite;
	}

	if ( $options['enablexmlsitemap'] === true ) {
		$GLOBALS['mcmsseo_sitemaps'] = new MCMSSEO_Sitemaps;
	}

	if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
		require_once( MCMSSEO_PATH . 'inc/mcmsseo-non-ajax-functions.php' );
	}

	// Init it here because the filter must be present on the frontend as well or it won't work in the customizer.
	new MCMSSEO_Customizer();
}

/**
 * Loads the rest api endpoints.
 */
function mcmsseo_init_rest_api() {
	// We can't do anything when requirements are not met.
	if ( MCMSSEO_Utils::is_api_available() ) {
		// Boot up REST API endpoints.
		$configuration_service = new MCMSSEO_Configuration_Service();
		$configuration_service->set_default_providers();
		$configuration_service->register_hooks();
	}
}

/**
 * Used to load the required files on the modules_loaded hook, instead of immediately.
 */
function mcmsseo_frontend_init() {
	add_action( 'init', 'initialize_mcmsseo_front' );

	$options = MCMSSEO_Options::get_option( 'mcmsseo_internallinks' );
	if ( $options['breadcrumbs-enable'] === true ) {
		/**
		 * If breadcrumbs are active (which they supposedly are if the users has enabled this settings,
		 * there's no reason to have bbPress breadcrumbs as well.
		 *
		 * @internal The class itself is only loaded when the template tag is encountered via
		 * the template tag function in the mcmsseo-functions.php file
		 */
		add_filter( 'bbp_get_breadcrumb', '__return_false' );
	}

	add_action( 'template_redirect', 'mcmsseo_frontend_head_init', 999 );
}

/**
 * Instantiate the different social classes on the frontend
 */
function mcmsseo_frontend_head_init() {
	$options = MCMSSEO_Options::get_option( 'mcmsseo_social' );
	if ( $options['twitter'] === true ) {
		add_action( 'mcmsseo_head', array( 'MCMSSEO_Twitter', 'get_instance' ), 40 );
	}

	if ( $options['opengraph'] === true ) {
		$GLOBALS['mcmsseo_og'] = new MCMSSEO_OpenGraph;
	}

}

/**
 * Used to load the required files on the modules_loaded hook, instead of immediately.
 */
function mcmsseo_admin_init() {
	new MCMSSEO_Admin_Init();
}


/* ***************************** BOOTSTRAP / HOOK INTO MCMS *************************** */
$spl_autoload_exists = function_exists( 'spl_autoload_register' );
$filter_exists       = function_exists( 'filter_input' );

if ( ! $spl_autoload_exists ) {
	add_action( 'admin_init', 'ultimatum_mcmsseo_missing_spl', 1 );
}

if ( ! $filter_exists ) {
	add_action( 'admin_init', 'ultimatum_mcmsseo_missing_filter', 1 );
}

if ( ! function_exists( 'mcms_installing' ) ) {
	/**
	 * We need to define mcms_installing in MandarinCMS versions older than 4.4
	 *
	 * @return bool
	 */
	function mcms_installing() {
		return defined( 'MCMS_INSTALLING' );
	}
}

if ( ! mcms_installing() && ( $spl_autoload_exists && $filter_exists ) ) {
	add_action( 'modules_loaded', 'mcmsseo_init', 14 );
	add_action( 'init', 'mcmsseo_init_rest_api' );

	if ( is_admin() ) {

		new Ultimatum_Alerts();

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			require_once( MCMSSEO_PATH . 'admin/ajax.php' );

			// Module conflict ajax hooks.
			new Ultimatum_Module_Conflict_Ajax();

			if ( filter_input( INPUT_POST, 'action' ) === 'inline-save' ) {
				add_action( 'modules_loaded', 'mcmsseo_admin_init', 15 );
			}
		}
		else {
			add_action( 'modules_loaded', 'mcmsseo_admin_init', 15 );
		}
	}
	else {
		add_action( 'modules_loaded', 'mcmsseo_frontend_init', 15 );
	}

	add_action( 'modules_loaded', 'load_ultimatum_notifications' );
}

// Activation and deactivation hook.
register_activation_hook( MCMSSEO_FILE, 'mcmsseo_activate' );
register_deactivation_hook( MCMSSEO_FILE, 'mcmsseo_deactivate' );
add_action( 'mcmsmu_new_blog', 'mcmsseo_on_activate_blog' );
add_action( 'activate_blog', 'mcmsseo_on_activate_blog' );

// Loading OnPage integration.
new MCMSSEO_OnPage();


/**
 * Wraps for notifications center class.
 */
function load_ultimatum_notifications() {
	// Init Ultimatum_Notification_Center class.
	Ultimatum_Notification_Center::get();
}


/**
 * Throw an error if the PHP SPL extension is disabled (prevent white screens) and self-deactivate module
 *
 * @since 1.5.4
 *
 * @return void
 */
function ultimatum_mcmsseo_missing_spl() {
	if ( is_admin() ) {
		add_action( 'admin_notices', 'ultimatum_mcmsseo_missing_spl_notice' );

		ultimatum_mcmsseo_self_deactivate();
	}
}

/**
 * Returns the notice in case of missing spl extension
 */
function ultimatum_mcmsseo_missing_spl_notice() {
	$message = esc_html__( 'The Standard PHP Library (SPL) extension seem to be unavailable. Please ask your web host to enable it.', 'mandarincms-seo' );
	ultimatum_mcmsseo_activation_failed_notice( $message );
}

/**
 * Throw an error if the Composer autoload is missing and self-deactivate module
 *
 * @return void
 */
function ultimatum_mcmsseo_missing_autoload() {
	if ( is_admin() ) {
		add_action( 'admin_notices', 'ultimatum_mcmsseo_missing_autoload_notice' );

		ultimatum_mcmsseo_self_deactivate();
	}
}

/**
 * Returns the notice in case of missing Composer autoload
 */
function ultimatum_mcmsseo_missing_autoload_notice() {
	/* translators: %1$s expands to Ultimatum SEO, %2$s / %3$s: links to the installation manual in the Readme for the Ultimatum SEO code repository on GitHub */
	$message = esc_html__( 'The %1$s module installation is incomplete. Please refer to %2$sinstallation instructions%3$s.', 'mandarincms-seo' );
	$message = sprintf( $message, 'Ultimatum SEO', '<a href="https://github.com/Ultimatum/mandarincms-seo#installation">', '</a>' );
	ultimatum_mcmsseo_activation_failed_notice( $message );
}

/**
 * Throw an error if the filter extension is disabled (prevent white screens) and self-deactivate module
 *
 * @since 2.0
 *
 * @return void
 */
function ultimatum_mcmsseo_missing_filter() {
	if ( is_admin() ) {
		add_action( 'admin_notices', 'ultimatum_mcmsseo_missing_filter_notice' );

		ultimatum_mcmsseo_self_deactivate();
	}
}

/**
 * Returns the notice in case of missing filter extension
 */
function ultimatum_mcmsseo_missing_filter_notice() {
	$message = esc_html__( 'The filter extension seem to be unavailable. Please ask your web host to enable it.', 'mandarincms-seo' );
	ultimatum_mcmsseo_activation_failed_notice( $message );
}

/**
 * Echo's the Activation failed notice with any given message.
 *
 * @param string $message Message string.
 */
function ultimatum_mcmsseo_activation_failed_notice( $message ) {
	echo '<div class="error"><p>' . __( 'Activation failed:', 'mandarincms-seo' ) . ' ' . $message . '</p></div>';
}

/**
 * The method will deactivate the module, but only once, done by the static $is_deactivated
 */
function ultimatum_mcmsseo_self_deactivate() {
	static $is_deactivated;

	if ( $is_deactivated === null ) {
		$is_deactivated = true;
		deactivate_modules( module_basename( MCMSSEO_FILE ) );
		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}
	}
}
