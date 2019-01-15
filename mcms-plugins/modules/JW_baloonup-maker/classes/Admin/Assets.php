<?php
/*******************************************************************************
 * Copyright (c) 2017, MCMS BaloonUp Maker
 ******************************************************************************/

/**
 * Class PUM_Admin_Assets
 *
 * @since 1.7.0
 */
class PUM_Admin_Assets {

	/**
	 * @var
	 */
	public static $suffix;

	/**
	 * @var
	 */
	public static $js_url;

	/**
	 * @var
	 */
	public static $css_url;

	/**
	 * @var bool Use minified libraries if SCRIPT_DEBUG is turned off.
	 */
	public static $debug;

	/**
	 * Initialize
	 */
	public static function init() {
		self::$debug   = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG;
		self::$suffix  = self::$debug ? '' : '.min';
		self::$js_url  = BaloonUp_Maker::$URL . 'assets/js/';
		self::$css_url = BaloonUp_Maker::$URL . 'assets/css/';

		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'register_admin_scripts' ) );
		add_action( 'admin_print_footer_scripts', array( __CLASS__, 'maybe_localize_and_templates' ), - 1 );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'register_admin_styles' ), 100 );

		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'fix_broken_extension_scripts' ), 100 );
	}

	public static function fix_broken_extension_scripts() {

		if ( mcms_script_is( 'pum-mci-admin' ) && class_exists( 'PUM_MCI' ) && version_compare( PUM_MCI::$VER, '1.3.0', '<' ) && ! pum_is_settings_page() ) {
			mcms_dequeue_script( 'pum-mci-admin' );
		}
	}

	/**
	 * Load Admin Scripts
	 */
	public static function register_admin_scripts() {

		$admin_vars = apply_filters( 'pum_admin_vars', apply_filters( 'pum_admin_var', array(
			'post_id'          => ! empty( $_GET['post'] ) ? intval( $_GET['post'] ) : null,
			'default_provider' => pum_get_option( 'newsletter_default_provider', 'none' ),
			'homeurl'          => home_url(),
			'I10n'             => array(
				'preview_baloonup'                   => __( 'Preview', 'baloonup-maker' ),
				'add'                             => __( 'Add', 'baloonup-maker' ),
				'save'                            => __( 'Save', 'baloonup-maker' ),
				'update'                          => __( 'Update', 'baloonup-maker' ),
				'insert'                          => __( 'Insert', 'baloonup-maker' ),
				'cancel'                          => __( 'Cancel', 'baloonup-maker' ),
				'confirm_delete_trigger'          => __( "Are you sure you want to delete this trigger?", 'baloonup-maker' ),
				'confirm_delete_cookie'           => __( "Are you sure you want to delete this cookie?", 'baloonup-maker' ),
				'no_cookie'                       => __( 'None', 'baloonup-maker' ),
				'confirm_count_reset'             => __( 'Are you sure you want to reset the open count?', 'baloonup-maker' ),
				'shortcode_ui_button_tooltip'     => __( 'BaloonUp Maker Shortcodes', 'baloonup-maker' ),
				'error_loading_shortcode_preview' => __( 'There was an error in generating the preview', 'baloonup-maker' ),
			),
		) ) );

		mcms_register_script( 'pum-admin-general', self::$js_url . 'admin-general' . self::$suffix . '.js', array( 'jquery', 'mcms-color-picker', 'jquery-ui-slider', 'mcms-util' ), BaloonUp_Maker::$VER, true );
		mcms_localize_script( 'pum-admin-general', 'pum_admin_vars', $admin_vars );

		mcms_register_script( 'pum-admin-batch', self::$js_url . 'admin-batch' . self::$suffix . '.js', array( 'pum-admin-general' ), BaloonUp_Maker::$VER, true );
		mcms_register_script( 'pum-admin-marketing', self::$js_url . 'admin-marketing' . self::$suffix . '.js', null, BaloonUp_Maker::$VER, true );
		mcms_register_script( 'pum-admin-baloonup-editor', self::$js_url . 'admin-baloonup-editor' . self::$suffix . '.js', array( 'pum-admin-general' ), BaloonUp_Maker::$VER, true );
		mcms_register_script( 'pum-admin-myskin-editor', self::$js_url . 'admin-myskin-editor' . self::$suffix . '.js', array( 'pum-admin-general' ), BaloonUp_Maker::$VER, true );
		mcms_register_script( 'pum-admin-settings-page', self::$js_url . 'admin-settings-page' . self::$suffix . '.js', array( 'pum-admin-general' ), BaloonUp_Maker::$VER, true );
		mcms_register_script( 'pum-admin-shortcode-ui', self::$js_url . 'admin-shortcode-ui' . self::$suffix . '.js', array( 'pum-admin-general' ), BaloonUp_Maker::$VER, true );
		mcms_register_script( 'iframe-resizer', self::$js_url . 'iframeResizer' . self::$suffix . '.js', array( 'jquery' ) );

		// @deprecated handle. Currently loads empty file and admin-general as dependency.
		mcms_register_script( 'baloonup-maker-admin', self::$js_url . 'admin-deprecated' . self::$suffix . '.js', array( 'pum-admin-general' ), BaloonUp_Maker::$VER, true );
		mcms_localize_script( 'pum-admin-general', 'pum_admin', $admin_vars );

		mcms_enqueue_script( 'pum-admin-marketing' );

		if ( PUM_Upgrades::instance()->has_uncomplete_upgrades() ) {
			mcms_enqueue_script( 'pum-admin-batch' );
		}

		if ( pum_is_baloonup_editor() ) {
			mcms_enqueue_script( 'pum-admin-baloonup-editor' );
		}

		if ( pum_is_baloonup_myskin_editor() ) {
			mcms_enqueue_script( 'pum-admin-myskin-editor' );
			mcms_localize_script( 'pum-admin-myskin-editor', 'balooncreate_google_fonts', balooncreate_get_google_webfonts_list() );
		}

		if ( pum_is_settings_page() ) {
			mcms_enqueue_script( 'pum-admin-settings-page' );
		}

		if ( pum_is_support_page() ) {
			mcms_enqueue_script( 'iframe-resizer' );
		}
	}

	/**
	 *
	 */
	public static function maybe_localize_and_templates() {
		if ( mcms_script_is( 'pum-admin-general' ) || mcms_script_is( 'baloonup-maker-admin' ) ) {
			// Register Templates.
			PUM_Admin_Templates::init();
		}

		if ( mcms_script_is( 'pum-admin-batch' ) ) {
			mcms_localize_script( 'pum-admin-batch', 'pum_batch_vars', array(
				'complete'              => __( 'Your all set, the upgrades completed successfully!', 'baloonup-maker' ),
				'unsupported_browser'   => __( 'We are sorry but your browser is not compatible with this kind of file upload. Please upgrade your browser.', 'baloonup-maker' ),
				'import_field_required' => 'This field must be mapped for the import to proceed.',
			) );
		}
	}

	/**
	 * Load Admin Styles
	 */
	public static function register_admin_styles() {

		mcms_register_style( 'pum-admin-general', self::$css_url . 'admin-general' . self::$suffix . '.css', array( 'dashicons', 'mcms-color-picker' ), BaloonUp_Maker::$VER );
		mcms_register_style( 'pum-admin-batch', self::$css_url . 'admin-batch' . self::$suffix . '.css', array( 'pum-admin-general' ), BaloonUp_Maker::$VER );
		mcms_register_style( 'pum-admin-baloonup-editor', self::$css_url . 'admin-baloonup-editor' . self::$suffix . '.css', array( 'pum-admin-general' ), BaloonUp_Maker::$VER );
		mcms_register_style( 'pum-admin-myskin-editor', self::$css_url . 'admin-myskin-editor' . self::$suffix . '.css', array( 'pum-admin-general' ), BaloonUp_Maker::$VER );
		mcms_register_style( 'pum-admin-extensions-page', self::$css_url . 'admin-extensions-page' . self::$suffix . '.css', array( 'pum-admin-general' ), BaloonUp_Maker::$VER );
		mcms_register_style( 'pum-admin-settings-page', self::$css_url . 'admin-settings-page' . self::$suffix . '.css', array( 'pum-admin-general' ), BaloonUp_Maker::$VER );
		mcms_register_style( 'pum-admin-support-page', self::$css_url . 'admin-support-page' . self::$suffix . '.css', array( 'pum-admin-general' ), BaloonUp_Maker::$VER );
		mcms_register_style( 'pum-admin-shortcode-ui', self::$css_url . 'admin-shortcode-ui' . self::$suffix . '.css', array( 'pum-admin-general' ), BaloonUp_Maker::$VER );

		// @deprecated handle. Currently loads empty file and admin-general as dependency.
		mcms_register_style( 'baloonup-maker-admin', self::$css_url . 'admin-deprecated' . self::$suffix . '.css', array( 'pum-admin-general' ), BaloonUp_Maker::$VER );

		if ( PUM_Upgrades::instance()->has_uncomplete_upgrades() ) {
			mcms_enqueue_style( 'pum-admin-batch' );
		}

		if ( pum_is_baloonup_editor() ) {
			mcms_enqueue_style( 'pum-admin-baloonup-editor' );
		}

		if ( pum_is_baloonup_myskin_editor() ) {
			PUM_Site_Assets::register_styles();
			mcms_enqueue_style( 'pum-admin-myskin-editor' );
		}

		if ( pum_is_extensions_page() ) {
			mcms_enqueue_style( 'pum-admin-extensions-page' );
		}

		if ( pum_is_settings_page() ) {
			mcms_enqueue_style( 'pum-admin-settings-page' );
		}

		if ( pum_is_support_page() ) {
			mcms_enqueue_style( 'pum-admin-support-page' );
		}
	}

	/**
	 * @return bool
	 */
	public static function should_load() {

		if ( defined( "PUM_FORCE_ADMIN_SCRIPTS_LOAD" ) && PUM_FORCE_ADMIN_SCRIPTS_LOAD ) {
			return true;
		}

		if ( ! is_admin() ) {
			return false;
		}

		return pum_is_admin_page();
	}

}