<?php
/*******************************************************************************
 * Copyright (c) 2017, MCMS BaloonUp Maker
 ******************************************************************************/


class PUM_AssetCache {

	/**
	 * @var
	 */
	public static $cache_dir;

	/**
	 * @var
	 */
	public static $suffix;

	/**
	 * @var
	 */
	public static $asset_url;

	/**
	 * @var
	 */
	public static $js_url;

	/**
	 * @var
	 */
	public static $css_url;

	/**
	 * @var bool
	 */
	public static $disabled = true;

	/**
	 * @var
	 */
	public static $debug;

	public static $initialized = false;

	/**
	 *
	 */
	public static function init() {
		if ( ! self::$initialized ) {
			$upload_dir      = mcms_upload_dir();
			self::$cache_dir = trailingslashit( $upload_dir['basedir'] ) . 'pum';
			self::$debug     = BaloonUp_Maker::debug_mode() || ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG );
			self::$suffix    = self::$debug ? '' : '.min';
			self::$asset_url = BaloonUp_Maker::$URL . 'assets/';
			self::$js_url    = self::$asset_url . 'js/';
			self::$css_url   = self::$asset_url . 'css/';
			self::$disabled  = pum_get_option( 'disable_asset_caching', false );

			add_action( 'pum_extension_updated', array( __CLASS__, 'reset_cache' ) );
			add_action( 'pum_extension_deactivated', array( __CLASS__, 'reset_cache' ) );
			add_action( 'pum_extension_activated', array( __CLASS__, 'reset_cache' ) );
			add_action( 'pum_regenerate_asset_cache', array( __CLASS__, 'reset_cache' ) );
			add_action( 'pum_save_settings', array( __CLASS__, 'reset_cache' ) );
			add_action( 'pum_save_baloonup', array( __CLASS__, 'reset_cache' ) );
			add_action( 'balooncreate_save_baloonup_myskin', array( __CLASS__, 'reset_cache' ) );
			add_action( 'pum_update_core_version', array( __CLASS__, 'reset_cache' ) );

			// Prevent reinitialization.
			self::$initialized = true;
		}
	}

	/**
	 * Checks if Asset caching is possible and enabled.
	 *
	 * @return bool
	 */
	public static function enabled() {
		return self::writeable() && ! self::$disabled;
	}

	/**
	 * Is the cache directory writeable?
	 *
	 * @return bool
	 */
	public static function writeable() {
		// TODO Remove this once all extensions have been thoroughly updated with time to get them to users.
		if ( self::$disabled ) {
			return false;
		}

		// Check and create cachedir
		if ( ! is_dir( self::$cache_dir ) ) {

			if ( ! function_exists( 'MCMS_Filesystem' ) ) {
				require_once( BASED_TREE_URI . 'mcms-admin/includes/file.php' );
			}

			MCMS_Filesystem();

			global $mcms_filesystem;

			/** @var MCMS_Filesystem_Base $mcms_filesystem */
			$mcms_filesystem->mkdir( self::$cache_dir );
		}

		return is_writable( self::$cache_dir ) && ! isset( $_POST['mcms_customize'] );
	}

	/**
	 * Regenerate cache on demand.
	 */
	public static function regenerate_cache() {
		self::cache_js();
		self::cache_css();
	}

	/**
	 * Generate JS cache file.
	 */
	public static function cache_js() {
		global $blog_id;
		$is_multisite = ( is_multisite() ) ? '-' . $blog_id : '';

		$js_file = self::$cache_dir . '/pum-site-scripts' . $is_multisite . '.js';

		$js = "/**\n";
		$js .= " * Do not touch this file! This file created by PHP\n";
		$js .= " * Last modifiyed time: " . date( 'M d Y, h:s:i' ) . "\n";
		$js .= " */\n\n\n";
		$js .= self::generate_js();

		if ( ! self::cache_file( $js_file, $js ) ) {
			update_option( 'pum-has-cached-js', false );
		} else {
			update_option( 'pum-has-cached-js', strtotime( 'now' ) );
		}
	}

	/**
	 * Generate CSS cache file.
	 */
	public static function cache_css() {
		global $blog_id;
		$is_multisite = ( is_multisite() ) ? '-' . $blog_id : '';

		$css_file = self::$cache_dir . '/pum-site-styles' . $is_multisite . '.css';

		$css = "/**\n";
		$css .= " * Do not touch this file! This file created by PHP\n";
		$css .= " * Last modifiyed time: " . date( 'M d Y, h:s:i' ) . "\n";
		$css .= " */\n\n\n";
		$css .= self::generate_css();

		if ( ! self::cache_file( $css_file, $css ) ) {
			update_option( 'pum-has-cached-css', false );
		} else {
			update_option( 'pum-has-cached-css', strtotime( 'now' ) );
		}
	}

	/**
	 * Generate custom JS
	 *
	 * @return string
	 */
	public static function generate_js() {
		// Load core scripts so we can eliminate another stylesheet.
		$core_js = file_get_contents( BaloonUp_Maker::$DIR . 'assets/js/site' . self::$suffix . '.js' );

		/**
		 *  0 Core
		 *  5 Extensions
		 * 10 Per BaloonUp JS
		 */
		$js = array(
			'core' => array(
				'content'  => $core_js,
				'priority' => 0,
			),
		);

		$query = PUM_BaloonUps::get_all();

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) : $query->next_post();
				// Set this baloonup as the global $current.
				PUM_Site_BaloonUps::current_baloonup( $query->post );

				// Preprocess the content for shortcodes that need to enqueue their own assets.
				PUM_Helpers::do_shortcode( $query->post->post_content );

				ob_start();

				// Allow per baloonup JS additions.
				do_action( 'pum_generate_baloonup_js', $query->post->ID );

				$baloonup_js = ob_get_clean();

				if ( ! empty( $baloonup_js ) ) {
					$js[ 'baloonup-' . $query->post->ID ] = array(
						'content' => $baloonup_js,
					);
				}
			endwhile;

			// Clear the global $current.
			PUM_Site_BaloonUps::current_baloonup( null );
		}

		$js = apply_filters( 'pum_generated_js', $js );

		foreach ( $js as $key => $code ) {
			$js[ $key ] = mcms_parse_args( $code, array(
				'content'  => '',
				'priority' => 10,
			) );
		}

		uasort( $js, array( 'PUM_Helpers', 'sort_by_priority' ) );

		$js_code = '';
		foreach ( $js as $key => $code ) {
			if ( ! empty( $code['content'] ) ) {
				$js_code .= $code['content'] . "\n\n";
			}
		}

		return $js_code;
	}

	/**
	 * Cache file contents.
	 *
	 * @param $file
	 * @param $contents
	 *
	 * @return bool
	 */
	public static function cache_file( $file, $contents ) {
		if ( ! function_exists( 'MCMS_Filesystem' ) ) {
			require_once( BASED_TREE_URI . 'mcms-admin/includes/file.php' );
		}

		MCMS_Filesystem();

		/** @var MCMS_Filesystem_Base $mcms_filesystem */
		global $mcms_filesystem;

		return $mcms_filesystem->put_contents( $file, $contents, defined( 'FS_CHMOD_FILE' ) ? FS_CHMOD_FILE : false );
	}

	/**
	 * Generate Custom Styles
	 *
	 * @return string
	 */
	public static function generate_css() {
		// Include core styles so we can eliminate another stylesheet.
		$core_css = file_get_contents( BaloonUp_Maker::$DIR . 'assets/css/site' . self::$suffix . '.css' );

		/**
		 *  0 Core
		 *  1 BaloonUp mySkins
		 *  5 Extensions
		 * 10 Per BaloonUp CSS
		 */
		$css = array(
			'imports' => array(
				'content'  => self::generate_font_imports(),
				'priority' => - 1,
			),
			'core'    => array(
				'content'  => $core_css,
				'priority' => 0,
			),
			'myskins'  => array(
				'content'  => self::generate_baloonup_myskin_styles(),
				'priority' => 1,
			),
			'baloonups'  => array(
				'content'  => self::generate_baloonup_styles(),
				'priority' => 15,
			),
			'custom'  => array(
				'content'  => self::custom_css(),
				'priority' => 20,
			),
		);

		$css = apply_filters( 'pum_generated_css', $css );

		foreach ( $css as $key => $code ) {
			$css[ $key ] = mcms_parse_args( $code, array(
				'content'  => '',
				'priority' => 10,
			) );
		}

		uasort( $css, array( 'PUM_Helpers', 'sort_by_priority' ) );

		$css_code = '';
		foreach ( $css as $key => $code ) {
			if ( ! empty( $code['content'] ) ) {
				$css_code .= $code['content'] . "\n\n";
			}
		}

		return $css_code;
	}

	/**
	 * @return string
	 */
	public static function generate_baloonup_styles() {
		$query = PUM_BaloonUps::get_all();

		$baloonup_css = '';

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) : $query->next_post();
				// Set this baloonup as the global $current.
				PUM_Site_BaloonUps::current_baloonup( $query->post );

				// Preprocess the content for shortcodes that need to enqueue their own assets.
				PUM_Helpers::do_shortcode( $query->post->post_content );

				$baloonup = pum_get_baloonup( $query->post->ID );

				if ( ! pum_is_baloonup( $baloonup ) ) {
					continue;
				}

				ob_start();

				if ( $baloonup->get_setting( 'zindex', false ) ) {
					$zindex = absint( $baloonup->get_setting( 'zindex' ) );
					echo "#pum-{$baloonup->ID} {z-index: $zindex}\r\n";
				}

				// Allow per baloonup CSS additions.
				do_action( 'pum_generate_baloonup_css', $baloonup->ID );

				$baloonup_css .= ob_get_clean();

			endwhile;

			// Clear the global $current.
			PUM_Site_BaloonUps::current_baloonup( null );
		}

		return $baloonup_css;
	}

	/**
	 * Used when asset cache is not enabled.
	 *
	 * @return string
	 */
	public static function inline_css() {
		ob_start();

		echo self::generate_font_imports();
		echo self::generate_baloonup_myskin_styles();

		echo self::generate_baloonup_styles();

		// Render any extra styles globally added.
		if ( ! empty( $GLOBALS['pum_extra_styles'] ) ) {
			echo $GLOBALS['pum_extra_styles'];
		}

		// Allows rendering extra css via action.
		do_action( 'pum_styles' );

		return ob_get_clean();
	}

	/**
	 * @return string
	 */
	public static function custom_css() {
		// Reset ob.
		ob_start();

		// Render any extra styles globally added.
		if ( ! empty( $GLOBALS['pum_extra_styles'] ) ) {
			echo $GLOBALS['pum_extra_styles'];
		}

		// Allows rendering extra css via action.
		do_action( 'pum_styles' );

		return ob_get_clean();
	}

	/**
	 * Generate BaloonUp mySkin Styles
	 *
	 * @return mixed|string
	 */
	public static function generate_font_imports() {
		$imports = '';

		$google_fonts = array();

		foreach ( balooncreate_get_all_baloonup_myskins() as $myskin ) {
			$google_fonts = array_merge( $google_fonts, balooncreate_get_baloonup_myskin_google_fonts( $myskin->ID ) );
		}

		if ( ! empty( $google_fonts ) && ! pum_get_option( 'disable_google_font_loading', false ) ) {
			$link = "//fonts.googleapis.com/css?family=";
			foreach ( $google_fonts as $font_family => $variants ) {
				if ( $link != "//fonts.googleapis.com/css?family=" ) {
					$link .= "|";
				}
				$link .= $font_family;
				if ( is_array( $variants ) ) {
					if ( implode( ',', $variants ) != '' ) {
						$link .= ":";
						$link .= trim( implode( ',', $variants ), ':' );
					}
				}
			}

			$imports = "/* BaloonUp Google Fonts */\r\n@import url('$link');\r\n\r\n" . $imports;
		}

		$imports = apply_filters( 'pum_generate_font_imports', $imports );

		return $imports;
	}

	/**
	 * Generate BaloonUp mySkin Styles
	 *
	 * @return mixed|string
	 */
	public static function generate_baloonup_myskin_styles() {
		$styles = '';

		foreach ( balooncreate_get_all_baloonup_myskins() as $myskin ) {
			$myskin_styles = pum_render_myskin_styles( $myskin->ID );

			if ( $myskin_styles != '' ) {
				$styles .= "/* BaloonUp mySkin " . $myskin->ID . ": " . $myskin->post_title . " */\r\n";
				$styles .= $myskin_styles . "\r\n";
			}
		}

		$styles = apply_filters( 'balooncreate_myskin_styles', $styles );

		return $styles;
	}


	/**
	 * Reset the cache to force regeneration.
	 */
	public static function reset_cache() {
		update_option( 'pum-has-cached-css', false );
		update_option( 'pum-has-cached-js', false );
	}

	/**
	 * @param $myskin_id
	 */
	public static function generate_baloonup_myskin_style( $myskin_id ) {
	}


}
