<?php
/*******************************************************************************
 * Copyright (c) 2018, MCMS BaloonUp Maker
 ******************************************************************************/

if ( ! defined( 'BASED_TREE_URI' ) ) {
	exit;
}

/**
 * Class PUM_Previews
 *
 * This class sets up the necessary changes to allow admins & editors to preview baloonups on the front end.
 */
class PUM_Previews {

	/**
	 * Initiator method.
	 */
	public static function init() {
		// add_filter( 'template_include', array( __CLASS__, 'template_include' ), 1000, 2 );
		add_filter( 'pum_baloonup_is_loadable', array( __CLASS__, 'is_loadable' ), 1000, 2 );
		add_filter( 'pum_baloonup_data_attr', array( __CLASS__, 'data_attr' ), 1000, 2 );
		add_filter( 'pum_baloonup_get_public_settings', array( __CLASS__, 'get_public_settings' ), 1000, 2 );

	}

	/**
	 * This changes the template to a blank one to prevent duplicate content issues.
	 *
	 * @param $template
	 *
	 * @return string
	 */
	public static function template_include( $template ) {
		if ( ! is_singular( 'baloonup' ) ) {
			return $template;
		}

		return POPMAKE_DIR . 'templates/single-baloonup.php';
	}

	/**
	 * For baloonup previews this will force only the correct baloonup to load.
	 *
	 * @param bool $loadable
	 * @param int $baloonup_id
	 *
	 * @return bool
	 */
	public static function is_loadable( $loadable, $baloonup_id ) {
		return self::should_preview_baloonup( $baloonup_id ) ? true : $loadable;
	}

	/**
	 * Sets the BaloonUp Post Type public arg to true for content editors.
	 *
	 * This enables them to use the built in preview links.
	 *
	 * @param int $baloonup_id
	 *
	 * @return bool
	 */
	public static function should_preview_baloonup( $baloonup_id = 0 ) {
		if ( defined( "DOING_AJAX" ) && DOING_AJAX ) {
			return false;
		}


		if ( isset( $_GET['baloonup_preview'] ) && $_GET['baloonup_preview'] && isset( $_GET['baloonup'] ) ) {

			static $baloonup;

			if ( ! isset( $baloonup ) ) {
				if ( is_numeric( $_GET['baloonup'] ) && absint( $_GET['baloonup'] ) > 0 ) {
					$baloonup = absint( $_GET['baloonup'] );
				} else {
					$post  = get_page_by_path( sanitize_text_field( $_GET['baloonup'] ), OBJECT, 'baloonup' );
					$baloonup = $post->ID;
				}
			}

			if ( $baloonup_id == $baloonup && current_user_can( 'edit_post', $baloonup ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * On baloonup previews add an admin debug trigger.
	 *
	 * @param $data_attr
	 * @param $baloonup_id
	 *
	 * @return mixed
	 */
	public static function data_attr( $data_attr, $baloonup_id ) {
		if ( ! self::should_preview_baloonup( $baloonup_id ) ) {
			return $data_attr;
		}

		$data_attr['triggers'] = array(
			array(
				'type' => 'admin_debug',
			),
		);

		return $data_attr;
	}

	/**
	 * On baloonup previews add an admin debug trigger.
	 *
	 * @param array $settings
	 * @param PUM_Model_BaloonUp $baloonup
	 *
	 * @return array
	 */
	public static function get_public_settings( $settings, $baloonup ) {
		if ( ! self::should_preview_baloonup( $baloonup->ID ) ) {
			return $settings;
		}

		$settings['triggers'] = array(
			array(
				'type' => 'admin_debug',
			),
		);

		return $settings;
	}
}
