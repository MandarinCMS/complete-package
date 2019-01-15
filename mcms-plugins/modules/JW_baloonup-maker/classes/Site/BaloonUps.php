<?php
/*******************************************************************************
 * Copyright (c) 2017, MCMS BaloonUp Maker
 ******************************************************************************/

if ( ! defined( 'BASED_TREE_URI' ) ) {
	exit;
}

/**
 * Class Post_Types
 */
class PUM_Site_BaloonUps {

	/**
	 * @var PUM_BaloonUp|null
	 */
	public static $current;

	/**
	 * @var MCMS_Query|null
	 */
	public static $loaded;

	/**
	 * @var array
	 */
	public static $loaded_ids = array();

	/**
	 * Hook the initialize method to the MCMS init action.
	 */
	public static function init() {

		// Preload the $loaded query.
		add_action( 'init', array( __CLASS__, 'get_loaded_baloonups' ) );

		// TODO determine if the late priority is needed.
		add_action( 'mcms_enqueue_scripts', array( __CLASS__, 'load_baloonups' ), 11 );

		add_action( 'mcms_footer', array( __CLASS__, 'render_baloonups' ) );
	}

	/**
	 * Returns the current baloonup.
	 *
	 * @param bool|object|null $new_baloonup
	 *
	 * @return null|PUM_BaloonUp
	 */
	public static function current_baloonup( $new_baloonup = false ) {
		global $baloonup;

		if ( $new_baloonup !== false ) {
			self::$current = $new_baloonup;
			$baloonup         = $new_baloonup;
		}

		return self::$current;
	}

	/**
	 * Gets the loaded baloonup query.
	 *
	 * @return null|MCMS_Query
	 */
	public static function get_loaded_baloonups() {
		if ( ! self::$loaded instanceof MCMS_Query ) {
			self::$loaded        = new MCMS_Query();
			self::$loaded->posts = array();
		}

		return self::$loaded;
	}

	/**
	 * Preload baloonups in the head and determine if they will be rendered or not.
	 *
	 * @uses `pum_preload_baloonup` filter
	 * @uses `balooncreate_preload_baloonup` filter
	 */
	public static function load_baloonups() {
		if ( is_admin() ) {
			return;
		}

		// TODO Replace this with PUM_BaloonUp::query when available.
		$query = PUM_BaloonUps::get_all();

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) : $query->next_post();

				// Set this baloonup as the global $current.
				self::current_baloonup( $query->post );

				// If the baloonup is loadable (passes conditions) load it.
				if ( pum_is_baloonup_loadable( $query->post->ID ) ) {
					self::preload_baloonup( $query->post );
				}

			endwhile;

			// Clear the global $current.
			self::current_baloonup( null );
		}
	}

	/**
	 * @param $baloonup PUM_Model_BaloonUp
	 */
	public static function preload_baloonup( $baloonup ) {
		// Add to the $loaded_ids list.
		self::$loaded_ids[] = $baloonup->ID;

		// Add to the $loaded query.
		self::$loaded->posts[] = $baloonup;
		self::$loaded->post_count ++;

		// Preprocess the content for shortcodes that need to enqueue their own assets.

		PUM_Helpers::do_shortcode( $baloonup->post_content );

		# TODO cache this content for later in case of double rendering causing breakage.
		# TODO Use this content during rendering as well.

		// Fire off preload action.
		do_action( 'pum_preload_baloonup', $baloonup->ID );
		// Deprecated filter.
		do_action( 'balooncreate_preload_baloonup', $baloonup->ID );
	}

	public static function load_baloonup( $id ) {
		if ( did_action( 'mcms_head' ) && ! in_array( $id, self::$loaded_ids ) ) {
			$args1 = array(
				'post_type' => 'baloonup',
				'p'         => $id,
			);
			$query = new MCMS_Query( $args1 );
			if ( $query->have_posts() ) {
				while ( $query->have_posts() ) : $query->next_post();
					self::current_baloonup( $query->post );
					self::preload_baloonup( $query->post );
				endwhile;
				self::current_baloonup( null );
			}
		}

		return;
	}


	/**
	 * Render the baloonups in the footer.
	 */
	public static function render_baloonups() {
		$loaded = self::get_loaded_baloonups();

		if ( $loaded->have_posts() ) {
			while ( $loaded->have_posts() ) : $loaded->next_post();
				self::current_baloonup( $loaded->post );
				balooncreate_get_template_part( 'baloonup' );
			endwhile;
			self::current_baloonup( null );
		}
	}

}

