<?php
/**
 * BaloonUp mySkin Functions
 *
 * @package        POPMAKE
 * @subpackage  Functions
 * @copyright   Copyright (c) 2014, Daniel Iser
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 1.0
 */

// Exit if accessed directly
if ( ! defined( 'BASED_TREE_URI' ) ) {
	exit;
}


function balooncreate_get_default_baloonup_myskin() {
	static $default_myskin = null;

	if ( $default_myskin === null ) {
		$default_myskin = get_option( 'balooncreate_default_myskin' );
	}

	if ( ! $default_myskin ) {
		if ( ! function_exists( 'balooncreate_install_default_myskin' ) ) {
			include_once POPMAKE_DIR . 'includes/install.php';
		}
		balooncreate_install_default_myskin();
		$default_myskin = get_option( 'balooncreate_default_myskin' );
		pum_force_myskin_css_refresh();
	}

	return $default_myskin;
}


function balooncreate_get_all_baloonup_myskins() {
	static $myskins;

	if ( ! $myskins ) {
		$query = new MCMS_Query( array(
			'post_type'              => 'baloonup_myskin',
			'post_status'            => 'publish',
			'posts_per_page'         => - 1,
			// Performance Optimization.
			'update_post_term_cache' => false,
			'no_found_rows'          => true,
		) );

		$myskins = $query->posts;
	}

	return $myskins;
}

function balooncreate_get_baloonup_myskin_meta( $group, $baloonup_myskin_id = null, $key = null, $default = null ) {
	if ( ! $baloonup_myskin_id ) {
		$baloonup_myskin_id = get_the_ID();
	}

	$values = get_post_meta( $baloonup_myskin_id, "baloonup_myskin_{$group}", true );

	if ( ! $values ) {
		$defaults = apply_filters( "balooncreate_baloonup_myskin_{$group}_defaults", array() );
		$values = array_merge( $defaults, balooncreate_get_baloonup_myskin_meta_group( $group, $baloonup_myskin_id ) );
	} else {
		$values = array_merge( balooncreate_get_baloonup_myskin_meta_group( $group, $baloonup_myskin_id ), $values );
	}

	if ( $key ) {

		// Check for dot notation key value.
		$test  = uniqid();
		$value = balooncreate_resolve( $values, $key, $test );
		if ( $value == $test ) {

			$key = str_replace( '.', '_', $key );

			if ( ! isset( $values[ $key ] ) ) {
				$value = $default;
			} else {
				$value = $values[ $key ];
			}

		}

		return apply_filters( "balooncreate_get_baloonup_myskin_{$group}_$key", $value, $baloonup_myskin_id );
	} else {
		return apply_filters( "balooncreate_get_baloonup_myskin_{$group}", $values, $baloonup_myskin_id );
	}
}

/**
 * Returns the meta group of a myskin or value if key is set.
 *
 * @since 1.0
 *
 * @param int $baloonup_myskin_id ID number of the baloonup to retrieve a overlay meta for
 *
 * @return mixed array|string of the baloonup overlay meta
 */
function balooncreate_get_baloonup_myskin_meta_group( $group, $baloonup_myskin_id = null, $key = null, $default = null ) {
	if ( ! $baloonup_myskin_id ) {
		$baloonup_myskin_id = get_the_ID();
	}

	$post_meta    = get_post_custom( $baloonup_myskin_id );

	if ( ! is_array( $post_meta ) ) {
		$post_meta = array();
	}

	$default_check_key = 'baloonup_myskin_defaults_set';
	if ( ! in_array( $group, array( 'overlay', 'close', 'display', 'targeting_condition' ) ) ) {
		$default_check_key = "baloonup_{$group}_defaults_set";
	}

	$group_values = array_key_exists( $default_check_key, $post_meta ) ? array() : apply_filters( "balooncreate_baloonup_myskin_{$group}_defaults", array() );
	foreach ( $post_meta as $meta_key => $value ) {
		if ( strpos( $meta_key, "baloonup_myskin_{$group}_" ) !== false ) {
			$new_key = str_replace( "baloonup_myskin_{$group}_", '', $meta_key );
			if ( count( $value ) == 1 ) {
				$group_values[ $new_key ] = $value[0];
			} else {
				$group_values[ $new_key ] = $value;
			}
		}
	}
	if ( $key ) {
		$key = str_replace( '.', '_', $key );
		if ( ! isset( $group_values[ $key ] ) ) {
			$value = $default;
		} else {
			$value = $group_values[ $key ];
		}

		return apply_filters( "balooncreate_get_baloonup_myskin_{$group}_$key", $value, $baloonup_myskin_id );
	} else {
		return apply_filters( "balooncreate_get_baloonup_myskin_{$group}", $group_values, $baloonup_myskin_id );
	}
}


/**
 * Returns the overlay meta of a myskin.
 *
 * @since 1.0
 *
 * @param int $baloonup_myskin_id ID number of the baloonup to retrieve a overlay meta for
 *
 * @return mixed array|string of the baloonup overlay meta
 */
function balooncreate_get_baloonup_myskin_overlay( $baloonup_myskin_id = null, $key = null, $default = null ) {
	return balooncreate_get_baloonup_myskin_meta( 'overlay', $baloonup_myskin_id, $key, $default );
}


/**
 * Returns the container meta of a myskin.
 *
 * @since 1.0
 *
 * @param int $baloonup_myskin_id ID number of the baloonup to retrieve a container meta for
 *
 * @return mixed array|string of the baloonup container meta
 */
function balooncreate_get_baloonup_myskin_container( $baloonup_myskin_id = null, $key = null, $default = null ) {
	return balooncreate_get_baloonup_myskin_meta( 'container', $baloonup_myskin_id, $key, $default );
}


/**
 * Returns the title meta of a myskin.
 *
 * @since 1.0
 *
 * @param int $baloonup_myskin_id ID number of the baloonup to retrieve a title meta for
 *
 * @return mixed array|string of the baloonup title meta
 */
function balooncreate_get_baloonup_myskin_title( $baloonup_myskin_id = null, $key = null, $default = null ) {
	return balooncreate_get_baloonup_myskin_meta( 'title', $baloonup_myskin_id, $key, $default );
}


/**
 * Returns the content meta of a myskin.
 *
 * @since 1.0
 *
 * @param int $baloonup_myskin_id ID number of the baloonup to retrieve a content meta for
 *
 * @return mixed array|string of the baloonup content meta
 */
function balooncreate_get_baloonup_myskin_content( $baloonup_myskin_id = null, $key = null, $default = null ) {
	return balooncreate_get_baloonup_myskin_meta( 'content', $baloonup_myskin_id, $key, $default );
}


/**
 * Returns the close meta of a myskin.
 *
 * @since 1.0
 *
 * @param int $baloonup_myskin_id ID number of the baloonup to retrieve a close meta for
 *
 * @return mixed array|string of the baloonup close meta
 */
function balooncreate_get_baloonup_myskin_close( $baloonup_myskin_id = null, $key = null, $default = null ) {
	return balooncreate_get_baloonup_myskin_meta( 'close', $baloonup_myskin_id, $key, $default );
}


function balooncreate_get_baloonup_myskin_data_attr( $baloonup_myskin_id = 0 ) {
	$data_attr = array(
		'overlay'   => balooncreate_get_baloonup_myskin_overlay( $baloonup_myskin_id ),
		'container' => balooncreate_get_baloonup_myskin_container( $baloonup_myskin_id ),
		'title'     => balooncreate_get_baloonup_myskin_title( $baloonup_myskin_id ),
		'content'   => balooncreate_get_baloonup_myskin_content( $baloonup_myskin_id ),
		'close'     => balooncreate_get_baloonup_myskin_close( $baloonup_myskin_id ),
	);

	return apply_filters( 'balooncreate_get_baloonup_myskin_data_attr', $data_attr, $baloonup_myskin_id );
}


function balooncreate_get_baloonup_myskin_default_meta() {
	$default_meta = array();
	$defaults     = balooncreate_get_baloonup_myskin_data_attr( 0 );
	foreach ( $defaults as $group => $fields ) {
		$prefix = 'baloonup_myskin_' . $group . '_';
		foreach ( $fields as $field => $value ) {
			$default_meta[ $prefix . $field ] = $value;
		}
	}

	return $default_meta;
}

function balooncreate_get_baloonup_myskins_data() {
	$myskins = balooncreate_get_all_baloonup_myskins();

	$balooncreate_myskins = array();

	foreach ( $myskins as $myskin ) {
		$balooncreate_myskins[ $myskin->ID ] = balooncreate_get_baloonup_myskin_data_attr( $myskin->ID );
	}

	mcms_reset_postdata();

	return apply_filters( 'balooncreate_get_baloonup_myskins_data', $balooncreate_myskins );
}