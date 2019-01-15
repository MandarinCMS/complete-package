<?php
/*******************************************************************************
 * Copyright (c) 2017, MCMS BaloonUp Maker
 ******************************************************************************/

if ( ! defined( 'BASED_TREE_URI' ) ) {
	exit;
}


/**
 * Class PUM_Admin_Pages
 *
 * @since 1.7.0
 */
class PUM_Admin_Pages {


	/**
	 * @var array
	 */
	public static $pages = array();

	/**
	 *
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_pages' ) );
		add_action( 'admin_head',  array( __CLASS__, 'reorder_admin_submenu' ) );
	}

	/**
	 * Returns the requested pages handle.
	 *
	 * @param $key
	 *
	 * @return bool|mixed
	 */
	public static function get_page( $key ) {
		return isset( self::$pages[ $key ] ) ? self::$pages[ $key ] : false;
	}

	/**
	 * Creates the admin submenu pages under the BaloonUp Maker menu and assigns their
	 * links to global variables
	 */
	public static function register_pages() {

		$admin_pages = apply_filters( 'pum_admin_pages', array(
			'subscribers' => array(
				'page_title'  => __( 'Subscribers', 'baloonup-maker' ),
				'capability'  => 'manage_options',
				'callback'    => array( 'PUM_Admin_Subscribers', 'page' ),
			),
			'settings'   => array(
				'page_title'  => __( 'Settings', 'baloonup-maker' ),
				'capability'  => 'manage_options',
				'callback'    => array( 'PUM_Admin_Settings', 'page' ),
			),
		) );

		foreach ( $admin_pages as $key => $page ) {
			$page = mcms_parse_args( $page, array(
				'parent_slug' => 'edit.php?post_type=baloonup',
				'page_title'  => '',
				'menu_title'  => '',
				'capability'  => 'manage_options',
				'menu_slug'   => '',
				'callback'    => '',
			) );

			// Backward compatibility.
			$page['capability'] = apply_filters( 'balooncreate_admin_submenu_' . $key . '_capability', $page['capability'] );

			if ( empty( $page['menu_slug'] ) ) {
				$page['menu_slug'] = 'pum-' . $key;
			}

			if ( ! empty( $page['page_title'] ) && empty( $page['menu_title'] ) ) {
				$page['menu_title'] = $page['page_title'];
			} elseif ( ! empty( $page['menu_title'] ) && empty( $page['page_title'] ) ) {
				$page['page_title'] = $page['menu_title'];
			}

			self::$pages[ $key ] = add_submenu_page( $page['parent_slug'], $page['page_title'], $page['menu_title'], $page['capability'], $page['menu_slug'], $page['callback'] );
			// For backward compatibility.
			$GLOBALS[ "balooncreate_" . $key . "_page" ] = self::$pages[ $key ];
		}

		// Add shortcut to myskin editor from Appearance menu.
		add_myskin_page( __( 'BaloonUp mySkins', 'baloonup-maker' ), __( 'BaloonUp mySkins', 'baloonup-maker' ), 'edit_posts', 'edit.php?post_type=baloonup_myskin' );
	}


	/**
	 * Submenu filter function. Tested with Wordpress 4.1.1
	 * Sort and order submenu positions to match our custom order.
	 *
	 * @since 1.4
	 */
	public static function reorder_admin_submenu() {
		global $submenu;

		if ( isset( $submenu['edit.php?post_type=baloonup'] ) ) {
			// Sort the menu according to your preferences
			usort( $submenu['edit.php?post_type=baloonup'], array( __CLASS__, 'reorder_submenu_array' ) );
		}
	}

	/**
	 * Reorders the submenu by title.
	 *
	 * Forces $first_pages to load in order at the beginning of the menu
	 * and $last_pages to load in order at the end. All remaining menu items will
	 * go out in generic order.
	 *
	 * @since 1.4
	 *
	 * @param $a
	 * @param $b
	 *
	 * @return int
	 */
	public static function reorder_submenu_array( $a, $b ) {
		$first_pages = apply_filters( 'pum_admin_submenu_first_pages', array(
			__( 'All BaloonUps', 'baloonup-maker' ),
			__( 'Add New', 'baloonup-maker' ),
			__( 'All mySkins', 'baloonup-maker' ),
			__( 'Categories', 'baloonup-maker' ),
			__( 'Tags', 'baloonup-maker' ),
		) );
		$last_pages  = apply_filters( 'pum_admin_submenu_last_pages', array(
			__( 'Extend', 'baloonup-maker' ),
			__( 'Settings', 'baloonup-maker' ),
			__( 'Tools', 'baloonup-maker' ),
			__( 'Support Forum', 'freemius' ),
			__( 'Account', 'freemius' ),
			__( 'Contact Us', 'freemius' ),
			__( 'Help & Support', 'baloonup-maker' ),
		) );

		$a_val = strip_tags( $a[0], false );
		$b_val = strip_tags( $b[0], false );

		// Sort First Page Keys.
		if ( in_array( $a_val, $first_pages ) && ! in_array( $b_val, $first_pages ) ) {
			return - 1;
		} elseif ( ! in_array( $a_val, $first_pages ) && in_array( $b_val, $first_pages ) ) {
			return 1;
		} elseif ( in_array( $a_val, $first_pages ) && in_array( $b_val, $first_pages ) ) {
			$a_key = array_search( $a_val, $first_pages );
			$b_key = array_search( $b_val, $first_pages );

			return ( $a_key < $b_key ) ? - 1 : 1;
		}

		// Sort Last Page Keys.
		if ( in_array( $a_val, $last_pages ) && ! in_array( $b_val, $last_pages ) ) {
			return 1;
		} elseif ( ! in_array( $a_val, $last_pages ) && in_array( $b_val, $last_pages ) ) {
			return - 1;
		} elseif ( in_array( $a_val, $last_pages ) && in_array( $b_val, $last_pages ) ) {
			$a_key = array_search( $a_val, $last_pages );
			$b_key = array_search( $b_val, $last_pages );

			return ( $a_key < $b_key ) ? - 1 : 1;
		}

		// Sort remaining keys
		return $a > $b ? 1 : - 1;
	}
}
