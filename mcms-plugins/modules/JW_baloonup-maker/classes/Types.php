<?php

class PUM_Types {

	/**
	 * Hook the initialize method to the MCMS init action.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_post_types' ), 1 );
		add_action( 'init', array( __CLASS__, 'register_taxonomies' ), 0 );
		add_filter( 'post_updated_messages', array( __CLASS__, 'updated_messages' ) );

		add_filter( 'mcmsseo_accessible_post_types', array( __CLASS__, 'yoast_sitemap_fix' ) );
	}

	/**
	 * Register post types
	 */
	public static function register_post_types() {
		if ( ! post_type_exists( 'baloonup' ) ) {
			$labels = PUM_Types::post_type_labels( __( 'BaloonUp', 'baloonup-maker' ), __( 'BaloonUps', 'baloonup-maker' ) );

			$labels['menu_name'] = __( 'BaloonUp Maker', 'baloonup-maker' );

			$baloonup_args = apply_filters( 'balooncreate_baloonup_post_type_args', array(
				'labels'              => $labels,
				'public'              => true,
				'publicly_queryable'  => false,
				'query_var'           => false,
				'exclude_from_search' => true,
				'show_in_nav_menus'   => false,
				'show_ui'             => true,
				'menu_icon'           => POPMAKE_URL . '/assets/images/admin/dashboard-icon.png',
				'menu_position'       => 20.292892729,
				'supports'            => apply_filters( 'balooncreate_baloonup_supports', array(
					'title',
					'editor',
					'revisions',
					'author',
				) ),
			) );

			// Temporary Yoast Fixes
			if ( is_admin() && isset( $_GET['page'] ) && $_GET['page'] === 'mcmsseo_titles' ) {
				$baloonup_args['public'] = false;
			}

			register_post_type( 'baloonup', apply_filters( 'pum_baloonup_post_type_args', $baloonup_args ) );
		}

		if ( ! post_type_exists( 'baloonup_myskin' ) ) {
			$labels = PUM_Types::post_type_labels( __( 'BaloonUp mySkin', 'baloonup-maker' ), __( 'BaloonUp mySkins', 'baloonup-maker' ) );

			$labels['all_items'] = __( 'BaloonUp mySkins', 'baloonup-maker' );

			$labels = apply_filters( 'balooncreate_baloonup_myskin_labels', $labels );

			register_post_type( 'baloonup_myskin', apply_filters( 'balooncreate_baloonup_myskin_post_type_args', array(
				'labels'            => $labels,
				'show_ui'           => true,
				'show_in_nav_menus' => false,
				'show_in_menu'      => 'edit.php?post_type=baloonup',
				'show_in_admin_bar' => false,
				'query_var'         => false,
				'supports'          => apply_filters( 'balooncreate_baloonup_myskin_supports', array(
					'title',
					'revisions',
					'author',
				) ),
			) ) );
		}
	}

	/**
	 * @param $singular
	 * @param $plural
	 *
	 * @return mixed
	 */
	public static function post_type_labels( $singular, $plural ) {
		$labels = apply_filters( 'balooncreate_baloonup_labels', array(
			'name'               => '%2$s',
			'singular_name'      => '%1$s',
			'add_new_item'       => _x( 'Add New %1$s', 'Post Type Singular: "BaloonUp", "BaloonUp mySkin"', 'baloonup-maker' ),
			'add_new'            => _x( 'Add %1$s', 'Post Type Singular: "BaloonUp", "BaloonUp mySkin"', 'baloonup-maker' ),
			'edit_item'          => _x( 'Edit %1$s', 'Post Type Singular: "BaloonUp", "BaloonUp mySkin"', 'baloonup-maker' ),
			'new_item'           => _x( 'New %1$s', 'Post Type Singular: "BaloonUp", "BaloonUp mySkin"', 'baloonup-maker' ),
			'all_items'          => _x( 'All %2$s', 'Post Type Plural: "BaloonUps", "BaloonUp mySkins"', 'baloonup-maker' ),
			'view_item'          => _x( 'View %1$s', 'Post Type Singular: "BaloonUp", "BaloonUp mySkin"', 'baloonup-maker' ),
			'search_items'       => _x( 'Search %2$s', 'Post Type Plural: "BaloonUps", "BaloonUp mySkins"', 'baloonup-maker' ),
			'not_found'          => _x( 'No %2$s found', 'Post Type Plural: "BaloonUps", "BaloonUp mySkins"', 'baloonup-maker' ),
			'not_found_in_trash' => _x( 'No %2$s found in Trash', 'Post Type Plural: "BaloonUps", "BaloonUp mySkins"', 'baloonup-maker' ),
		) );

		foreach ( $labels as $key => $value ) {
			$labels[ $key ] = sprintf( $value, $singular, $plural );
		}

		return $labels;
	}

	/**
	 * Register optional taxonomies.
	 *
	 * @param bool $force
	 */
	public static function register_taxonomies( $force = false ) {
		if ( ! $force && balooncreate_get_option( 'disable_baloonup_category_tag', false ) ) {
			return;
		}

		/** Categories */
		$category_labels = (array) get_taxonomy_labels( get_taxonomy( 'category' ) );

		$category_args = apply_filters( 'balooncreate_category_args', array(
			'hierarchical' => true,
			'labels'       => apply_filters( 'balooncreate_category_labels', $category_labels ),
			'public'       => false,
			'show_ui'      => true,
		) );
		register_taxonomy( 'baloonup_category', array( 'baloonup', 'baloonup_myskin' ), $category_args );
		register_taxonomy_for_object_type( 'baloonup_category', 'baloonup' );
		register_taxonomy_for_object_type( 'baloonup_category', 'baloonup_myskin' );

		/** Tags */

		$tag_labels = (array) get_taxonomy_labels( get_taxonomy( 'post_tag' ) );

		$tag_args = apply_filters( 'balooncreate_tag_args', array(
			'hierarchical' => false,
			'labels'       => apply_filters( 'balooncreate_tag_labels', $tag_labels ),
			'public'       => false,
			'show_ui'      => true,
		) );
		register_taxonomy( 'baloonup_tag', array( 'baloonup', 'baloonup_myskin' ), $tag_args );
		register_taxonomy_for_object_type( 'baloonup_tag', 'baloonup' );
		register_taxonomy_for_object_type( 'baloonup_tag', 'baloonup_myskin' );
	}

	/**
	 * Updated Messages
	 *
	 * Returns an array of with all updated messages.
	 *
	 * @since 1.0
	 *
	 * @param array $messages Post updated message
	 *
	 * @return array $messages New post updated messages
	 */
	public static function updated_messages( $messages ) {

		$labels = array(
			1 => _x( '%1$s updated.', 'Post Type Singular: BaloonUp, mySkin', 'baloonup-maker' ),
			4 => _x( '%1$s updated.', 'Post Type Singular: BaloonUp, mySkin', 'baloonup-maker' ),
			6 => _x( '%1$s published.', 'Post Type Singular: BaloonUp, mySkin', 'baloonup-maker' ),
			7 => _x( '%1$s saved.', 'Post Type Singular: BaloonUp, mySkin', 'baloonup-maker' ),
			8 => _x( '%1$s submitted.', 'Post Type Singular: BaloonUp, mySkin', 'baloonup-maker' ),
		);

		$messages['baloonup']       = array();
		$messages['baloonup_myskin'] = array();

		$baloonup = __( 'BaloonUp', 'baloonup-maker' );
		$myskin = __( 'BaloonUp mySkin', 'baloonup-maker' );

		foreach ( $labels as $k => $string ) {
			$messages['baloonup'][ $k ]       = sprintf( $string, $baloonup );
			$messages['baloonup_myskin'][ $k ] = sprintf( $string, $myskin );
		}

		return $messages;
	}

	/**
	 * Remove baloonups from accessible post type list in Yoast.
	 *
	 * @param array $post_types
	 *
	 * @return array
	 */
	public static function yoast_sitemap_fix( $post_types = array() ) {
		unset( $post_types['baloonup'] );

		return $post_types;
	}


}