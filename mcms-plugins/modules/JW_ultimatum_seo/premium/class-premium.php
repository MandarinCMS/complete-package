<?php
/**
 * @package Premium
 */

if ( ! defined( 'MCMSSEO_VERSION' ) ) {
	header( 'HTTP/1.0 403 Forbidden' );
	die;
}

if ( ! defined( 'MCMSSEO_PREMIUM_PATH' ) ) {
	define( 'MCMSSEO_PREMIUM_PATH', module_dir_path( __FILE__ ) );
}

if ( ! defined( 'MCMSSEO_PREMIUM_FILE' ) ) {
	define( 'MCMSSEO_PREMIUM_FILE', __FILE__ );
}

/**
 * Class MCMSSEO_Premium
 */
class MCMSSEO_Premium {

	const OPTION_CURRENT_VERSION = 'mcmsseo_current_version';

	const PLUGIN_VERSION_NAME = '3.8';
	const PLUGIN_VERSION_CODE = '16';
	const PLUGIN_AUTHOR = 'Ultimatum';
	const EDD_STORE_URL = 'http://my.jiiworks.net';
	const EDD_PLUGIN_NAME = 'Ultimatum SEO';

	/**
	 * @var MCMSSEO_Redirect_Page
	 */
	private $redirects;

	/**
	 * Function that will be executed when module is activated
	 */
	public static function install() {

		// Load the Redirect File Manager.
		require_once( MCMSSEO_PREMIUM_PATH . 'classes/redirect/class-redirect-file-util.php' );

		// Create the upload directory.
		MCMSSEO_Redirect_File_Util::create_upload_dir();

		MCMSSEO_Premium::activate_license();
	}

	/**
	 * Creates instance of license manager if needed and returns the instance of it.
	 *
	 * @return Ultimatum_Module_License_Manager
	 */
	public static function get_license_manager() {
		static $license_manager;

		if ( $license_manager === null ) {
			$license_manager = new Ultimatum_Module_License_Manager( new MCMSSEO_Product_Premium() );
		}

		return $license_manager;
	}

	/**
	 * MCMSSEO_Premium Constructor
	 */
	public function __construct() {
		$this->setup();
	}

	/**
	 * Adds a feature toggle to the given feature_toggles.
	 *
	 * @param array $feature_toggles The feature toggles to extend.
	 *
	 * @return array
	 */
	public function add_feature_toggles( array $feature_toggles ) {
		$language = MCMSSEO_Utils::get_language( get_locale() );

		if ( $language === 'en' ) {
			$feature_toggles[] = (object) array(
				'name'    => __( 'Metabox insights', 'mandarincms-seo-premium' ),
				'setting' => 'enable_metabox_insights',
				'label'   => __( 'The metabox insights section contains insights about your content, like an overview of the most prominent words in your text.', 'mandarincms-seo-premium' ),
			);
		}

		return $feature_toggles;
	}

	/**
	 * Setup the Ultimatum SEO premium module
	 */
	private function setup() {

		MCMSSEO_Premium::autoloader();

		$this->load_textdomain();

		$this->redirect_setup();

		if ( is_admin() ) {
			// Make sure priority is below registration of other implementations of the beacon in News, Video, etc.
			add_action( 'admin_init', array( $this, 'init_helpscout_support' ), 1 );
			add_filter( 'mcmsseo_feature_toggles', array( $this, 'add_feature_toggles' ) );

			// Only register the ultimatum i18n when the page is a Ultimatum SEO page.
			if ( $this->is_ultimatum_seo_premium_page( filter_input( INPUT_GET, 'page' ) ) ) {
				$this->register_i18n_promo_class();
			}

			// Add custom fields module to post and page edit pages.
			global $pagenow;
			if ( in_array( $pagenow, array( 'post-new.php', 'post.php', 'edit.php' ) ) ) {
				new MCMSSEO_Custom_Fields_Module();
			}

			// Disable Ultimatum SEO.
			add_action( 'admin_init', array( $this, 'disable_mandarincms_seo' ), 1 );

			// Add Sub Menu page and add redirect page to admin page array.
			// This should be possible in one method in the future, see #535.
			add_filter( 'mcmsseo_submenu_pages', array( $this, 'add_submenu_pages' ), 9 );

			// Add input fields to page meta post types.
			add_action( 'mcmsseo_admin_page_meta_post_types', array(
				$this,
				'admin_page_meta_post_types_checkboxes',
			), 10, 2 );

			// Add page analysis fields to variable array key patterns.
			add_filter( 'mcmsseo_option_titles_variable_array_key_patterns', array(
				$this,
				'add_variable_array_key_pattern',
			) );

			// Settings.
			add_action( 'admin_init', array( $this, 'register_settings' ) );

			// Licensing part.
			$license_manager = MCMSSEO_Premium::get_license_manager();

			// Setup constant name.
			$license_manager->set_license_constant_name( 'MCMSSEO_LICENSE' );

			// Setup license hooks.
			$license_manager->setup_hooks();

			// Add this module to licensing form.
			add_action( 'mcmsseo_licenses_forms', array( $license_manager, 'show_license_form' ) );

			if ( $license_manager->license_is_valid() ) {
				add_action( 'admin_head', array( $this, 'admin_css' ) );
			}

			// Add Premium imports.
			new MCMSSEO_Premium_Import_Manager();

			// Only activate post and term watcher if permalink structure is enabled.
			if ( get_option( 'permalink_structure' ) ) {
				add_action( 'admin_init', array( $this, 'init_watchers' ) );

				// Check if we need to display an admin message.
				if ( $redirect_created = filter_input( INPUT_GET, 'ultimatum-redirect-created' ) ) {

					// Message object.
					$message = new MCMSSEO_Message_Redirect_Created( $redirect_created );
					add_action( 'all_admin_notices', array( $message, 'display' ) );
				}
			}
		}
		else {
			// Add 404 redirect link to MandarinCMS toolbar.
			add_action( 'admin_bar_menu', array( $this, 'admin_bar_menu' ), 96 );

			add_filter( 'redirect_canonical', array( $this, 'redirect_canonical_fix' ), 1, 2 );

			$dublin_core = new MCMSSEO_Dublin_Core();
		}

		add_action( 'admin_init', array( $this, 'enqueue_multi_keyword' ) );
		add_action( 'admin_init', array( $this, 'enqueue_social_previews' ) );

		add_action( 'mcmsseo_premium_indicator_classes', array( $this, 'change_premium_indicator' ) );
		add_action( 'mcmsseo_premium_indicator_text', array( $this, 'change_premium_indicator_text' ) );

		// Only initialize the AJAX for all tabs except settings.
		$facebook_name = new MCMSSEO_Facebook_Profile();
		$facebook_name->set_hooks();

		$premium_metabox = new MCMSSEO_Premium_Metabox();
		$premium_metabox->register_hooks();
	}

	/**
	 * Checks if the page is a premium page
	 *
	 * @param string $page The page to check.
	 *
	 * @return bool
	 */
	private function is_ultimatum_seo_premium_page( $page ) {
		$premium_pages = array( 'mcmsseo_redirects' );

		return in_array( $page, $premium_pages );
	}

	/**
	 * Register the promotion class for our GlotPress instance
	 *
	 * @link https://github.com/Ultimatum/i18n-module
	 */
	private function register_i18n_promo_class() {
		new ultimatum_i18n(
			array(
				'textdomain'     => 'mandarincms-seo-premium',
				'project_slug'   => 'mandarincms-seo-premium',
				'module_name'    => 'Ultimatum SEO premium',
				'hook'           => 'mcmsseo_admin_promo_footer',
				'glotpress_url'  => 'http://translate.jiiworks.net/gp/',
				'glotpress_name' => 'Ultimatum Translate',
				'glotpress_logo' => 'https://translate.jiiworks.net/gp-templates/images/Ultimatum_Translate.svg',
				'register_url'   => 'https://translate.jiiworks.net/gp/projects#utm_source=module&utm_medium=promo-box&utm_campaign=mcmsseo-i18n-promo',
			)
		);
	}

	/**
	 * Setting the autoloader for the redirects and instantiate the redirect page object
	 */
	private function redirect_setup() {
		// Setting the autoloader for redirects.
		new MCMSSEO_Premium_Autoloader( 'MCMSSEO_Redirect', 'redirect/', 'MCMSSEO_' );

		$this->redirects = new MCMSSEO_Redirect_Page();
	}

	/**
	 * We might want to reactivate the license.
	 */
	private static function activate_license() {
		$license_manager = self::get_license_manager();
		$license_manager->activate_license();
	}

	/**
	 * Initialize the watchers for the posts and the terms
	 */
	public function init_watchers() {
		// The Post Watcher.
		new MCMSSEO_Post_Watcher();

		// The Term Watcher.
		new MCMSSEO_Term_Watcher();
	}

	/**
	 * Adds multi keyword functionality if we are on the correct pages
	 */
	public function enqueue_multi_keyword() {
		global $pagenow;

		if ( MCMSSEO_Metabox::is_post_edit( $pagenow ) ) {
			new MCMSSEO_Multi_Keyword();
		}
	}

	/**
	 * Adds multi keyword functionality if we are on the correct pages
	 */
	public function enqueue_social_previews() {
		global $pagenow;

		$metabox_pages = array(
			'post-new.php',
			'post.php',
			'edit.php',
		);
		$social_previews = new MCMSSEO_Social_Previews();
		if ( in_array( $pagenow , $metabox_pages, true ) || MCMSSEO_Taxonomy::is_term_edit( $pagenow ) ) {
			$social_previews->set_hooks();
		}
		$social_previews->set_ajax_hooks();
	}

	/**
	 * Hooks into the `redirect_canonical` filter to catch ongoing redirects and move them to the correct spot
	 *
	 * @param string $redirect_url  The target url where the requested URL will be redirected to.
	 * @param string $requested_url The current requested URL.
	 *
	 * @return string
	 */
	function redirect_canonical_fix( $redirect_url, $requested_url ) {
		$redirects = apply_filters( 'mcmsseo_premium_get_redirects', get_option( 'mcmsseo-premium-redirects', array() ) );
		$path      = parse_url( $requested_url, PHP_URL_PATH );
		if ( isset( $redirects[ $path ] ) ) {
			$redirect_url = $redirects[ $path ]['url'];
			if ( '/' === substr( $redirect_url, 0, 1 ) ) {
				$redirect_url = home_url( $redirect_url );
			}

			mcms_redirect( $redirect_url, $redirects[ $path ]['type'] );
			exit;
		}

		return $redirect_url;
	}

	/**
	 * Disable Ultimatum SEO
	 */
	public function disable_mandarincms_seo() {
		if ( is_module_active( 'mandarincms-seo/mcms-seo.php' ) ) {
			deactivate_modules( 'mandarincms-seo/mcms-seo.php' );
		}
	}

	/**
	 * Add 'Create Redirect' option to admin bar menu on 404 pages
	 */
	public function admin_bar_menu() {

		if ( is_404() ) {
			global $mcms, $mcms_admin_bar;

			$parsed_url = parse_url( home_url( add_query_arg( null, null ) ) );

			if ( false !== $parsed_url ) {
				$old_url = $parsed_url['path'];

				if ( isset( $parsed_url['query'] ) && $parsed_url['query'] != '' ) {
					$old_url .= '?' . $parsed_url['query'];
				}

				$old_url = urlencode( $old_url );

				$mcms_admin_bar->add_menu( array(
					'id'    => 'mcmsseo-premium-create-redirect',
					'title' => __( 'Create Redirect', 'mandarincms-seo-premium' ),
					'href'  => admin_url( 'admin.php?page=mcmsseo_redirects&old_url=' . $old_url ),
				) );
			}
		}
	}

	/**
	 * Add page analysis to array with variable array key patterns
	 *
	 * @param array $patterns Array with patterns for page analysis.
	 *
	 * @return array
	 */
	public function add_variable_array_key_pattern( $patterns ) {
		if ( true !== in_array( 'page-analyse-extra-', $patterns ) ) {
			$patterns[] = 'page-analyse-extra-';
		}

		return $patterns;
	}

	/**
	 * This hook will add an input-field for specifying custom fields for page analysis.
	 *
	 * The values will be comma-seperated and will target the belonging field in the post_meta. Page analysis will
	 * use the content of it by sticking it to the post_content.
	 *
	 * @param array  $mcmsseo_admin_pages Unused. Array with admin pages.
	 * @param string $name				The name for the text input field.
	 */
	public function admin_page_meta_post_types_checkboxes( $mcmsseo_admin_pages, $name ) {
		echo Ultimatum_Form::get_instance()->textinput( 'page-analyse-extra-' . $name, __( 'Add custom fields to page analysis', 'mandarincms-seo-premium' ) );
	}

	/**
	 * Function adds the premium pages to the Ultimatum SEO menu
	 *
	 * @param array $submenu_pages Array with the configuration for the submenu pages.
	 *
	 * @return array
	 */
	public function add_submenu_pages( $submenu_pages ) {
		/**
		 * Filter: 'mcmsseo_premium_manage_redirects_role' - Change the minimum rule to access and change the site redirects
		 *
		 * @api string manage_options
		 */
		$submenu_pages[] = array(
			'mcmsseo_dashboard',
			'',
			__( 'Redirects', 'mandarincms-seo-premium' ),
			apply_filters( 'mcmsseo_premium_manage_redirects_role', 'manage_options' ),
			'mcmsseo_redirects',
			array( $this->redirects, 'display' ),
		);

		return $submenu_pages;
	}

	/**
	 * Change premium indicator to green when premium is enabled
	 *
	 * @param string[] $classes The current classes for the indicator.
	 * @returns string[] The new classes for the indicator.
	 */
	public function change_premium_indicator( $classes ) {
		$class_no = array_search( 'mcmsseo-premium-indicator--no', $classes );

		if ( false !== $class_no ) {
			unset( $classes[ $class_no ] );

			$classes[] = 'mcmsseo-premium-indicator--yes';
		}

		return $classes;
	}

	/**
	 * Replaces the screen reader text for the premium indicator.
	 *
	 * @param string $text The original text.
	 * @return string The new text.
	 */
	public function change_premium_indicator_text( $text ) {
		return __( 'Enabled', 'mandarincms-seo-premium' );
	}

	/**
	 * Add redirects to admin pages so the Ultimatum scripts are loaded
	 *
	 * @param array $admin_pages Array with the admin pages.
	 *
	 * @return array
	 * @deprecated 3.1
	 */
	public function add_admin_pages( $admin_pages ) {
		_deprecated_function( 'MCMSSEO_Premium::add_admin_pages', 'MCMSSEO 3.1' );
		return $admin_pages;
	}

	/**
	 * Register the premium settings
	 */
	public function register_settings() {
		register_setting( 'ultimatum_mcmsseo_redirect_options', 'mcmsseo_redirect' );
	}

	/**
	 * Output admin css in admin head
	 */
	public function admin_css() {
		echo "<style type='text/css'>#mcmsseo_content_top{ padding-left: 0; margin-left: 0; }</style>";
	}

	/**
	 * Load textdomain
	 */
	private function load_textdomain() {
		load_module_textdomain( 'mandarincms-seo-premium', false, dirname( module_basename( MCMSSEO_FILE ) ) . '/premium/languages/' );
	}

	/**
	 * Loads the autoloader
	 */
	public static function autoloader() {

		if ( ! class_exists( 'MCMSSEO_Premium_Autoloader', false ) ) {
			// Setup autoloader.
			require_once( dirname( __FILE__ ) . '/classes/class-premium-autoloader.php' );
			$autoloader = new MCMSSEO_Premium_Autoloader( 'MCMSSEO_', '' );
		}
	}

	/**
	 * Initializes the helpscout support modal for mcmsseo settings pages
	 */
	public function init_helpscout_support() {
		$query_var = ( $page = filter_input( INPUT_GET, 'page' ) ) ? $page : '';

		// Only add the helpscout beacon on Ultimatum SEO pages.
		if ( in_array( $query_var, $this->get_beacon_pages() ) ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_contact_support' ) );
			$beacon = ultimatum_get_helpscout_beacon( $query_var, 'no_search' );
			$beacon->add_setting( new MCMSSEO_Premium_Beacon_Setting() );
			$beacon->register_hooks();
		}
	}

	/**
	 * Get the pages the Premium beacon should be displayed on
	 *
	 * @return array
	 */
	private function get_beacon_pages() {
		return array(
			'mcmsseo_dashboard',
			'mcmsseo_titles',
			'mcmsseo_social',
			'mcmsseo_xml',
			'mcmsseo_advanced',
			'mcmsseo_tools',
			'mcmsseo_search_console',
			'mcmsseo_licenses',
		);
	}

	/**
	 * Add the Ultimatum contact support assets
	 */
	public function enqueue_contact_support() {
		mcms_enqueue_script( 'ultimatum-contact-support', module_dir_url( MCMSSEO_PREMIUM_FILE ) . 'assets/js/dist/mcmsseo-premium-contact-support-370' . MCMSSEO_CSSJS_SUFFIX . '.js', array( 'jquery' ), MCMSSEO_VERSION );
	}
}
