<?php
/*******************************************************************************
 * Copyright (c) 2017, MCMS BaloonUp Maker
 ******************************************************************************/

class PUM_Site_Assets {

	/**
	 * @var
	 */
	public static $cache_url;

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
	 * @var array
	 */
	public static $enqueued_scripts = array();

	/**
	 * @var array
	 */
	public static $enqueued_styles = array();

	/**
	 * @var bool
	 */
	public static $scripts_registered = false;

	/**
	 * @var bool
	 */
	public static $styles_registered = false;

	/**
	 * @var bool Use minified libraries if SCRIPT_DEBUG is turned off.
	 */
	public static $debug;

	/**
	 * Initialize
	 */
	public static function init() {
		self::$cache_url = PUM_Helpers::upload_dir_url( 'pum' );
		self::$debug     = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG;
		self::$suffix    = self::$debug ? '' : '.min';
		self::$js_url    = BaloonUp_Maker::$URL . 'assets/js/';
		self::$css_url   = BaloonUp_Maker::$URL . 'assets/css/';

		// Register assets early.
		add_action( 'mcms_enqueue_scripts', array( __CLASS__, 'register_styles' ) );
		add_action( 'mcms_enqueue_scripts', array( __CLASS__, 'register_scripts' ) );

		// Localize after baloonups rendered in PUM_Site_BaloonUps
		add_action( 'mcms_footer', array( __CLASS__, 'late_localize_scripts' ), 19 );

		// Checks preloaded baloonups in the head for which assets to enqueue.
		add_action( 'pum_preload_baloonup', array( __CLASS__, 'enqueue_baloonup_assets' ) );
		add_filter( 'mcms_enqueue_scripts', array( __CLASS__, 'enqueue_page_assets' ) );

		add_action( 'mcms_enqueue_scripts', array( __CLASS__, 'fix_broken_extension_scripts' ), 100 );

		// Allow forcing assets to load.
		add_action( 'mcms_head', array( __CLASS__, 'check_force_script_loading' ) );
	}

	public static function fix_broken_extension_scripts() {
		if ( mcms_script_is( 'pum_aweber_integration_js' ) && class_exists( 'PUM_Aweber_Integration' ) && defined( 'PUM_AWEBER_INTEGRATION_VER' ) && version_compare( PUM_AWEBER_INTEGRATION_VER, '1.1.0', '<' ) ) {
			mcms_dequeue_script( 'pum_aweber_integration_js' );
			mcms_dequeue_style( 'pum_aweber_integration_css' );
			mcms_dequeue_script( 'pum_newsletter_script' );
			mcms_dequeue_style( 'pum-newsletter-styles' );

			mcms_enqueue_style( 'pum-newsletter-styles', PUM_AWEBER_INTEGRATION_URL . '/includes/pum-newsletters/newsletter-styles' . self::$suffix . '.css' );
			mcms_enqueue_script( 'pum_newsletter_script', PUM_AWEBER_INTEGRATION_URL . '/includes/pum-newsletters/newsletter-scripts' . self::$suffix . '.js', array(
				'jquery',
				'baloonup-maker-site',
			), false, true );

		}

		$mc_ver_test = in_array( true, array(
			class_exists( 'PUM_MailChimp_Integration' ) && defined( 'PUM_MAILCHIMP_INTEGRATION_VER' ) && PUM_MAILCHIMP_INTEGRATION_VER,
			class_exists( 'PUM_MCI' ) && version_compare( PUM_MCI::$VER, '1.3.0', '<' ),
		) );

		if ( mcms_script_is( 'pum_aweber_integration_js' ) && $mc_ver_test ) {
			mcms_dequeue_script( 'pum_mailchimp_integration_admin_js' );
			mcms_dequeue_style( 'pum_mailchimp_integration_admin_css' );
			mcms_dequeue_script( 'pum-mci' );
			mcms_dequeue_style( 'pum-mci' );
			mcms_dequeue_script( 'pum-newsletter-site' );
			mcms_dequeue_style( 'pum-newsletter-site' );

			mcms_enqueue_style( 'pum-newsletter-site', PUM_NEWSLETTER_URL . 'assets/css/pum-newsletter-site' . self::$suffix . '.css', null, PUM_NEWSLETTER_VERSION );
			mcms_enqueue_script( 'pum-newsletter-site', PUM_NEWSLETTER_URL . 'assets/js/pum-newsletter-site' . self::$suffix . '.js', array( 'jquery' ), PUM_NEWSLETTER_VERSION, true );
			mcms_localize_script( 'pum-newsletter-site', 'pum_sub_vars', array(
				'ajaxurl'          => admin_url( 'admin-ajax.php' ),
				'message_position' => 'top',
			) );
		}

	}


	/**
	 * Checks the current page content for the newsletter shortcode.
	 */
	public static function enqueue_page_assets() {
		global $post;

		if ( ! empty( $post ) && has_shortcode( $post->post_content, 'pum_sub_form' ) ) {
			mcms_enqueue_script( 'baloonup-maker-site' );
			mcms_enqueue_style( 'baloonup-maker-site' );
		}
	}

	/**
	 * @param int $baloonup_id
	 */
	public static function enqueue_baloonup_assets( $baloonup_id = 0 ) {
		/**
		 * TODO Replace this with a pum_get_baloonup function after new BaloonUp model is in place.
		 *
		 * $baloonup = pum_get_baloonup( $baloonup_id );
		 *
		 * if ( ! pum_is_baloonup( $baloonup ) ) {
		 *        return;
		 * }
		 */

		$baloonup = new PUM_BaloonUp( $baloonup_id );

		mcms_enqueue_script( 'baloonup-maker-site' );
		mcms_enqueue_style( 'baloonup-maker-site' );

		if ( $baloonup->mobile_disabled() || $baloonup->tablet_disabled() ) {
			mcms_enqueue_script( 'mobile-detect' );
		}

		/**
		 * TODO Implement this in core $baloonup model & advanced targeting conditions.
		 *
		 * if ( $baloonup->has_condition( array(
		 *    'device_is_mobile',
		 *    'device_is_phone',
		 *    'device_is_tablet',
		 *    'device_is_brand',
		 * ) ) ) {
		 *    self::enqueue_script( 'mobile-detect' );
		 * }
		 */
	}

	/**
	 * Register JS.
	 */
	public static function register_scripts() {
		self::$scripts_registered = true;

		mcms_register_script( 'mobile-detect', self::$js_url . 'mobile-detect' . self::$suffix . '.js', null, '1.3.3', true );
		mcms_register_script( 'iframe-resizer', self::$js_url . 'iframeResizer' . self::$suffix . '.js', array( 'jquery' ) );

		if ( PUM_AssetCache::enabled() ) {
			$cached = get_option( 'pum-has-cached-js' );

			if ( ! $cached || self::$debug ) {
				PUM_AssetCache::cache_js();
				$cached = get_option( 'pum-has-cached-js' );
			}

			// check for multisite
			global $blog_id;
			$is_multisite = ( is_multisite() ) ? '-' . $blog_id : '';

			mcms_register_script( 'baloonup-maker-site', self::$cache_url . '/pum-site-scripts' . $is_multisite . '.js?defer&generated=' . $cached, array(
				'jquery',
				'jquery-ui-core',
				'jquery-ui-position',
			), BaloonUp_Maker::$VER, true );
		} else {
			mcms_register_script( 'baloonup-maker-site', self::$js_url . 'site' . self::$suffix . '.js?defer', array(
				'jquery',
				'jquery-ui-core',
				'jquery-ui-position',
			), BaloonUp_Maker::$VER, true );
		}


		if ( balooncreate_get_option( 'enable_easy_modal_compatibility_mode', false ) ) {
			mcms_register_script( 'baloonup-maker-easy-modal-importer-site', self::$js_url . 'baloonup-maker-easy-modal-importer-site' . self::$suffix . '?defer', array( 'baloonup-maker-site' ), POPMAKE_VERSION, true );
		}

		self::localize_scripts();
	}

	/**
	 * Localize scripts if enqueued.
	 */
	public static function localize_scripts() {
		$site_home_path = parse_url( home_url() );
		$site_home_path = isset( $site_home_path['path'] ) ? $site_home_path['path'] : '/';

		mcms_localize_script( 'baloonup-maker-site', 'pum_vars', apply_filters( 'pum_vars', array(
			'version'                => BaloonUp_Maker::$VER,
			'ajaxurl'                => admin_url( 'admin-ajax.php' ),
			'restapi'                => function_exists( 'rest_url' ) ? esc_url_raw( rest_url( 'pum/v1' ) ) : false,
			'rest_nonce'             => is_user_logged_in() ? mcms_create_nonce( 'mcms_rest' ) : null,
			'default_myskin'          => (string) balooncreate_get_default_baloonup_myskin(),
			'debug_mode'             => BaloonUp_Maker::debug_mode(),
			'disable_tracking'       => balooncreate_get_option( 'disable_baloonup_open_tracking' ),
			'home_url'               => trailingslashit( $site_home_path ),
			'message_position'       => 'top',
			'core_sub_forms_enabled' => ! PUM_Newsletters::$disabled,
			'baloonups'                 => array(),
		) ) );

		// TODO Remove all trace usages of these in JS so they can be removed.
		// @deprecated 1.4 Use pum_vars instead.
		mcms_localize_script( 'baloonup-maker-site', 'ajaxurl', admin_url( 'admin-ajax.php' ) );

		mcms_localize_script( 'baloonup-maker-site', 'pum_debug_vars', apply_filters( 'pum_debug_vars', array(
			'debug_mode_enabled'    => __( 'BaloonUp Maker', 'baloonup-maker' ) . ': ' . __( 'Debug Mode Enabled', 'baloonup-maker' ),
			'debug_started_at'      => __( 'Debug started at:', 'baloonup-maker' ),
			'debug_more_info'       => sprintf( __( 'For more information on how to use this information visit %s', 'baloonup-maker' ), 'https://docs.mcmsbaloonupmaker.com/?utm_medium=js-debug-info&utm_campaign=ContextualHelp&utm_source=browser-console&utm_content=more-info' ),
			'global_info'           => __( 'Global Information', 'baloonup-maker' ),
			'localized_vars'        => __( 'Localized variables', 'baloonup-maker' ),
			'baloonups_initializing'   => __( 'BaloonUps Initializing', 'baloonup-maker' ),
			'baloonups_initialized'    => __( 'BaloonUps Initialized', 'baloonup-maker' ),
			'single_baloonup_label'    => __( 'BaloonUp: #', 'baloonup-maker' ),
			'myskin_id'              => __( 'mySkin ID: ', 'baloonup-maker' ),
			'label_method_call'     => __( 'Method Call:', 'baloonup-maker' ),
			'label_method_args'     => __( 'Method Arguments:', 'baloonup-maker' ),
			'label_baloonup_settings'  => __( 'Settings', 'baloonup-maker' ),
			'label_triggers'        => __( 'Triggers', 'baloonup-maker' ),
			'label_cookies'         => __( 'Cookies', 'baloonup-maker' ),
			'label_delay'           => __( 'Delay:', 'baloonup-maker' ),
			'label_conditions'      => __( 'Conditions', 'baloonup-maker' ),
			'label_cookie'          => __( 'Cookie:', 'baloonup-maker' ),
			'label_settings'        => __( 'Settings:', 'baloonup-maker' ),
			'label_selector'        => __( 'Selector:', 'baloonup-maker' ),
			'label_mobile_disabled' => __( 'Mobile Disabled:', 'baloonup-maker' ),
			'label_tablet_disabled' => __( 'Tablet Disabled:', 'baloonup-maker' ),
			'label_event'           => __( 'Event: %s', 'baloonup-maker' ),
			'triggers'              => PUM_Triggers::instance()->dropdown_list(),
			'cookies'               => PUM_Cookies::instance()->dropdown_list(),
		) ) );

		/* Here for backward compatibility. */
		mcms_localize_script( 'baloonup-maker-site', 'pum_sub_vars', array(
			'ajaxurl'          => admin_url( 'admin-ajax.php' ),
			'message_position' => 'top',
		) );
	}

	/**
	 * Localize late script vars if enqueued.
	 */
	public static function late_localize_scripts() {
		// If scripts not rendered, localize these vars. Otherwise echo them manually.
		if ( ! mcms_script_is( 'baloonup-maker-site', 'done' ) ) {
			mcms_localize_script( 'baloonup-maker-site', 'pum_baloonups', self::get_baloonup_settings() );
		} else {
			echo "<script type='text/javascript'>";
			echo 'window.pum_baloonups = ' . PUM_Utils_Array::safe_json_encode( self::get_baloonup_settings() ) . ';';
			// Backward compatibility fill.
			echo 'window.pum_vars.baloonups = window.pum_baloonups;';
			echo "</script>";
		}
	}

	/**
	 * Gets public settings for each baloonup for a global JS variable.
	 *
	 * @return array
	 */
	public static function get_baloonup_settings() {
		$loaded = PUM_Site_BaloonUps::get_loaded_baloonups();

		$settings = array();

		$current_baloonup = PUM_Site_BaloonUps::current_baloonup();

		if ( $loaded->have_posts() ) {
			while ( $loaded->have_posts() ) : $loaded->next_post();
				PUM_Site_BaloonUps::current_baloonup( $loaded->post );
				$baloonup = pum_get_baloonup( $loaded->post->ID );
				// Set the key to the CSS id of this baloonup for easy lookup.
				$settings[ 'pum-' . $baloonup->ID ] = $baloonup->get_public_settings();
			endwhile;

			PUM_Site_BaloonUps::current_baloonup( $current_baloonup );
		}

		return $settings;
	}

	/**
	 * Register CSS.
	 */
	public static function register_styles() {
		self::$styles_registered = true;

		if ( PUM_AssetCache::enabled() ) {
			$cached = get_option( 'pum-has-cached-css' );

			if ( ! $cached || self::$debug ) {
				PUM_AssetCache::cache_css();
				$cached = get_option( 'pum-has-cached-css' );
			}

			// check for multisite
			global $blog_id;
			$is_multisite = ( is_multisite() ) ? '-' . $blog_id : '';

			mcms_register_style( 'baloonup-maker-site', self::$cache_url . '/pum-site-styles' . $is_multisite . '.css?generated=' . $cached, array(), BaloonUp_Maker::$VER );
		} else {
			mcms_register_style( 'baloonup-maker-site', self::$css_url . 'site' . self::$suffix . '.css', array(), BaloonUp_Maker::$VER );
			self::inline_styles();
		}
	}

	/**
	 * Render baloonup inline styles.
	 */
	public static function inline_styles() {
		if ( ( current_action() == 'mcms_head' && balooncreate_get_option( 'disable_baloonup_myskin_styles', false ) ) || ( current_action() == 'admin_head' && ! balooncreate_is_admin_baloonup_page() ) ) {
			return;
		}

		mcms_add_inline_style( 'baloonup-maker-site', PUM_AssetCache::inline_css() );
	}

	/**
	 * Defers loading of scripts with ?defer parameter in url.
	 *
	 * @param string $url URL being cleaned
	 *
	 * @return string $url
	 */
	public static function defer_js_url( $url ) {
		if ( false === strpos( $url, '.js?defer' ) ) {
			// not our file
			return $url;
		}

		return "$url' defer='defer";
	}

	/**
	 *
	 */
	public static function check_force_script_loading() {
		global $mcms_query;
		if ( ! empty( $mcms_query->post ) && has_shortcode( $mcms_query->post->post_content, 'baloonup' ) || ( defined( "POPMAKE_FORCE_SCRIPTS" ) && POPMAKE_FORCE_SCRIPTS ) ) {
			mcms_enqueue_script( 'baloonup-maker-site' );
			mcms_enqueue_style( 'baloonup-maker-site' );
		}
	}
}
