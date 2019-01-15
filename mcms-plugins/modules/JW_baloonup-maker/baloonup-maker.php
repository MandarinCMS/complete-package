<?php
/**
 * Module Name: BaloonUp Maker
 * Description: Easily create & style baloonups with any content. mySkin editor to quickly style your baloonups. Add forms, social media boxes, videos & more.
 * Version: 1.7.29
 * Text Domain: baloonup-maker
 *
 * @package     POPMAKE
 * @category    Core
 * @author      Daniel Iser
 * @copyright   Copyright (c) 2016, Wizard Internet Solutions
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'BASED_TREE_URI' ) ) {
	exit;
}


/**
 * Class Autoloader
 *
 * @param $class
 */
function pum_autoloader( $class ) {

	if ( strncmp( 'PUM_Newsletter_', $class, strlen( 'PUM_Newsletter_' ) ) === 0 && class_exists( 'PUM_MCI' ) && ! empty( PUM_MCI::$VER ) && version_compare( PUM_MCI::$VER, '1.3.0', '<' ) ) {
		return;
	}

	$pum_autoloaders = apply_filters( 'pum_autoloaders', array(
		array(
			'prefix' => 'PUM_',
			'dir'    => dirname( __FILE__ ) . '/classes/',
		),
	) );

	foreach ( $pum_autoloaders as $autoloader ) {
		$autoloader = mcms_parse_args( $autoloader, array(
			'prefix'  => 'PUM_',
			'dir'     => dirname( __FILE__ ) . '/classes/',
			'search'  => '_',
			'replace' => '/',
		) );

		// project-specific namespace prefix
		$prefix = $autoloader['prefix'];

		// does the class use the namespace prefix?
		$len = strlen( $prefix );
		if ( strncmp( $prefix, $class, $len ) !== 0 ) {
			// no, move to the next registered autoloader
			continue;
		}

		// get the relative class name
		$relative_class = substr( $class, $len );

		// replace the namespace prefix with the base directory, replace namespace
		// separators with directory separators in the relative class name, append
		// with .php
		$file = $autoloader['dir'] . str_replace( $autoloader['search'], $autoloader['replace'], $relative_class ) . '.php';

		// if the file exists, require it
		if ( file_exists( $file ) ) {
			require_once $file;
		}
	}
}

if ( ! function_exists( 'spl_autoload_register' ) ) {
	include 'includes/compat.php';
}

spl_autoload_register( 'pum_autoloader' ); // Register autoloader

/**
 * Main BaloonUp_Maker Class
 *
 * @since 1.0
 */
class BaloonUp_Maker {

	/**
	 * @var string Module Name
	 */
	public static $NAME = 'BaloonUp Maker';

	/**
	 * @var string Module Version
	 */
	public static $VER = '1.7.29';

	/**
	 * @var int DB Version
	 */
	public static $DB_VER = 8;

	/**
	 * @var string License API URL
	 */
	public static $API_URL = 'https://mcmsbaloonupmaker.com';

	/**
	 * @var string
	 */
	public static $MIN_PHP_VER = '5.2.17';

	/**
	 * @var string
	 */
	public static $MIN_MCMS_VER = '3.6';

	/**
	 * @var string Module URL
	 */
	public static $URL;

	/**
	 * @var string Module Directory
	 */
	public static $DIR;

	/**
	 * @var string Module FILE
	 */
	public static $FILE;

	/**
	 * Used to test if debug_mode is enabled.
	 *
	 * @var bool
	 */
	public static $DEBUG_MODE = false;

	/**
	 * @var BaloonUp_Maker The one true BaloonUp_Maker
	 */
	private static $instance;

	/**
	 * Main instance
	 *
	 * @return BaloonUp_Maker
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof BaloonUp_Maker ) ) {
			self::$instance = new BaloonUp_Maker;
			self::$instance->setup_constants();
			self::$instance->includes();
			self::$instance->load_textdomain();
			self::$instance->init();
		}

		return self::$instance;
	}

	/**
	 * Setup module constants
	 */
	private function setup_constants() {

		self::$DIR  = module_dir_path( __FILE__ );
		self::$URL  = modules_url( '/', __FILE__ );
		self::$FILE = __FILE__;

		if ( isset( $_GET['pum_debug'] ) || PUM_Options::get( 'debug_mode', false ) ) {
			self::$DEBUG_MODE = true;
		}

		if ( ! defined( 'POPMAKE' ) ) {
			define( 'POPMAKE', self::$FILE );
		}

		if ( ! defined( 'POPMAKE_NAME' ) ) {
			define( 'POPMAKE_NAME', self::$NAME );
		}

		if ( ! defined( 'POPMAKE_SLUG' ) ) {
			define( 'POPMAKE_SLUG', trim( dirname( module_basename( __FILE__ ) ), '/' ) );
		}

		if ( ! defined( 'POPMAKE_DIR' ) ) {
			define( 'POPMAKE_DIR', self::$DIR );
		}

		if ( ! defined( 'POPMAKE_URL' ) ) {
			define( 'POPMAKE_URL', self::$URL );
		}

		if ( ! defined( 'POPMAKE_NONCE' ) ) {
			define( 'POPMAKE_NONCE', 'balooncreate_nonce' );
		}

		if ( ! defined( 'POPMAKE_VERSION' ) ) {
			define( 'POPMAKE_VERSION', self::$VER );
		}

		if ( ! defined( 'POPMAKE_DB_VERSION' ) ) {
			define( 'POPMAKE_DB_VERSION', self::$DB_VER );
		}

		if ( ! defined( 'POPMAKE_API_URL' ) ) {
			define( 'POPMAKE_API_URL', self::$API_URL );
		}
	}

	/**
	 * Include required files
	 */
	private function includes() {

		require_once self::$DIR . 'includes/compat.php';

		// Initialize global options
		PUM_Options::init();

		/** @deprecated 1.7.0 */
		require_once self::$DIR . 'includes/admin/settings/register-settings.php';

		/** General Functions */
		require_once self::$DIR . 'includes/functions/cache.php';
		require_once self::$DIR . 'includes/functions/options.php';
		require_once self::$DIR . 'includes/functions/upgrades.php';
		require_once self::$DIR . 'includes/functions/developers.php';
		require_once self::$DIR . 'includes/migrations.php';

		// TODO Find another place for these admin functions so this can be put in its correct place.
		require_once self::$DIR . 'includes/admin/admin-pages.php';

		require_once self::$DIR . 'includes/actions.php';
		require_once self::$DIR . 'includes/class-balooncreate-cron.php';
		require_once self::$DIR . 'includes/defaults.php';
		require_once self::$DIR . 'includes/google-fonts.php';
		require_once self::$DIR . 'includes/general-functions.php';
		require_once self::$DIR . 'includes/extensions-functions.php';
		require_once self::$DIR . 'includes/input-options.php';
		require_once self::$DIR . 'includes/myskin-functions.php';
		require_once self::$DIR . 'includes/misc-functions.php';
		require_once self::$DIR . 'includes/css-functions.php';
		require_once self::$DIR . 'includes/ajax-calls.php';

		require_once self::$DIR . 'includes/importer/easy-modal-v2.php';
		require_once self::$DIR . 'includes/integrations/google-fonts.php';

		require_once self::$DIR . 'includes/templates.php';
		require_once self::$DIR . 'includes/load-baloonups.php';
		require_once self::$DIR . 'includes/license-handler.php';

		// Phasing Out
		require_once self::$DIR . 'includes/class-balooncreate-fields.php';
		require_once self::$DIR . 'includes/class-balooncreate-baloonup-fields.php';
		require_once self::$DIR . 'includes/class-balooncreate-baloonup-myskin-fields.php';
		require_once self::$DIR . 'includes/baloonup-functions.php';


		/**
		 * v1.4 Additions
		 */
		require_once self::$DIR . 'includes/class-pum.php';
		require_once self::$DIR . 'includes/class-pum-baloonup-query.php';
		require_once self::$DIR . 'includes/class-pum-fields.php';
		require_once self::$DIR . 'includes/class-pum-form.php';

		// Functions
		require_once self::$DIR . 'includes/pum-baloonup-functions.php';
		require_once self::$DIR . 'includes/pum-template-functions.php';
		require_once self::$DIR . 'includes/pum-general-functions.php';
		require_once self::$DIR . 'includes/pum-misc-functions.php';
		require_once self::$DIR . 'includes/pum-template-hooks.php';

		// Modules
		require_once self::$DIR . 'includes/modules/menus.php';
		require_once self::$DIR . 'includes/modules/admin-bar.php';
		require_once self::$DIR . 'includes/modules/reviews.php';

		// Upgrades
		if ( is_admin() ) {
			//require_once self::$DIR . 'includes/admin/class-pum-admin-upgrades.php';
		}

		// Deprecated Code
		require_once self::$DIR . 'includes/pum-deprecated.php';
		require_once self::$DIR . 'includes/pum-deprecated-v1.4.php';
		require_once self::$DIR . 'includes/pum-deprecated-v1.7.php';

		if ( is_admin() ) {
			require_once self::$DIR . 'includes/admin/admin-setup.php';
			require_once self::$DIR . 'includes/admin/admin-functions.php';
			require_once self::$DIR . 'includes/admin/myskins/metabox.php';
			require_once self::$DIR . 'includes/admin/myskins/metabox-close-fields.php';
			require_once self::$DIR . 'includes/admin/myskins/metabox-container-fields.php';
			require_once self::$DIR . 'includes/admin/myskins/metabox-content-fields.php';
			require_once self::$DIR . 'includes/admin/myskins/metabox-overlay-fields.php';
			require_once self::$DIR . 'includes/admin/myskins/metabox-title-fields.php';
			require_once self::$DIR . 'includes/admin/myskins/metabox-preview.php';
			require_once self::$DIR . 'includes/admin/extensions/extensions-page.php';
			require_once self::$DIR . 'includes/admin/pages/support.php';
			require_once self::$DIR . 'includes/admin/metabox-support.php';
		}

		require_once self::$DIR . 'includes/integrations/class-pum-woocommerce-integration.php';
		require_once self::$DIR . 'includes/integrations/class-pum-buddypress-integration.php';

		// Ninja Forms Integration
		require_once self::$DIR . 'includes/integrations/class-pum-ninja-forms.php';
		// CF7 Forms Integration
		require_once self::$DIR . 'includes/integrations/class-pum-cf7.php';
		// Gravity Forms Integration
		require_once self::$DIR . 'includes/integrations/class-pum-gravity-forms.php';
		// MCMSML Integration
		require_once self::$DIR . 'includes/integrations/class-pum-mcmsml.php';

		require_once self::$DIR . 'includes/pum-install-functions.php';
		require_once self::$DIR . 'includes/install.php';
	}

	/**
	 * Loads the module language files
	 */
	public function load_textdomain() {
		// Set filter for module's languages directory
		$balooncreate_lang_dir = dirname( module_basename( POPMAKE ) ) . '/languages/';
		$balooncreate_lang_dir = apply_filters( 'balooncreate_languages_directory', $balooncreate_lang_dir );

		// Traditional MandarinCMS module locale filter
		$locale = apply_filters( 'module_locale', get_locale(), 'baloonup-maker' );
		$mofile = sprintf( '%1$s-%2$s.mo', 'baloonup-maker', $locale );

		// Setup paths to current locale file
		$mofile_local  = $balooncreate_lang_dir . $mofile;
		$mofile_global = MCMS_LANG_DIR . '/baloonup-maker/' . $mofile;

		if ( file_exists( $mofile_global ) ) {
			// Look in global /mcms-content/languages/baloonup-maker folder
			load_textdomain( 'baloonup-maker', $mofile_global );
		} elseif ( file_exists( $mofile_local ) ) {
			// Look in local /mcms-content/modules/baloonup-maker/languages/ folder
			load_textdomain( 'baloonup-maker', $mofile_local );
		} else {
			// Load the default language files
			load_module_textdomain( 'baloonup-maker', false, $balooncreate_lang_dir );
		}
	}

	public function init() {
		PUM_Types::init();
		PUM_AssetCache::init();
		PUM_Site::init();
		PUM_Admin::init();
		PUM_Upgrades::instance();
		PUM_Newsletters::init();
		PUM_Previews::init();
		PUM_Integrations::init();
		PUM_Privacy::init();

		PUM_Shortcode_BaloonUp::init();
		PUM_Shortcode_BaloonUpTrigger::init();
		PUM_Shortcode_BaloonUpClose::init();
	}

	/**
	 * Returns true when debug mode is enabled.
	 *
	 * @return bool
	 */
	public static function debug_mode() {
		return true === self::$DEBUG_MODE;
	}

}

/**
 * Initialize the module.
 */
BaloonUp_Maker::instance();

/**
 * Initiate Freemius
 */
PUM_Freemius::instance();

/**
 * The code that runs during module activation.
 * This action is documented in classes/Activator.php
 */
register_activation_hook( __FILE__, array( 'PUM_Activator', 'activate' ) );

/**
 * The code that runs during module deactivation.
 * This action is documented in classes/Deactivator.php
 */
register_deactivation_hook( __FILE__, array( 'PUM_Deactivator', 'deactivate' ) );

/**
 * @deprecated 1.7.0
 */
function balooncreate_initialize() {
	// Disable Unlimited mySkins extension if active.
	remove_action( 'balooncreate_initialize', 'balooncreate_ut_initialize' );

	// Initialize old PUM extensions
	do_action( 'pum_initialize' );
	do_action( 'balooncreate_initialize' );
}

add_action( 'modules_loaded', 'balooncreate_initialize' );

/**
 * The main function responsible for returning the one true BaloonUp_Maker
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $balooncreate = PopMake(); ?>
 *
 * @since      1.0
 * @deprecated 1.7.0
 *
 * @return object The one true BaloonUp_Maker Instance
 */

function PopMake() {
	return BaloonUp_Maker::instance();
}
