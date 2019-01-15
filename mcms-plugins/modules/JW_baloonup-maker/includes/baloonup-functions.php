<?php
/*******************************************************************************
 * Copyright (c) 2017, MCMS BaloonUp Maker
 ******************************************************************************/

// Exit if accessed directly
if ( ! defined( 'BASED_TREE_URI' ) ) {
	exit;
}

/**
 * Return the baloonup id.
 *
 * @param int $baloonup_id
 *
 * @return int
 */
function pum_get_baloonup_id( $baloonup_id = 0 ) {
	if ( ! empty( $baloonup_id ) && is_numeric( $baloonup_id ) ) {
		$_baloonup_id = $baloonup_id;
	} elseif ( is_object( PUM_Site_BaloonUps::$current ) && is_numeric( PUM_Site_BaloonUps::$current->ID ) ) {
		$_baloonup_id = PUM_Site_BaloonUps::$current->ID;
	} else {
		$_baloonup_id = 0;
	}

	return (int) apply_filters( 'pum_get_baloonup_id', (int) $_baloonup_id, $baloonup_id );
}

/**
 * Get a baloonup model instance.
 *
 * @since 1.7.0
 *
 * @param int $baloonup_id
 * @param bool $force Clears cached instance and refreshes.
 *
 * @return PUM_Model_BaloonUp|false
 */
function pum_get_baloonup( $baloonup_id = 0, $force = false ) {
	return PUM_Model_BaloonUp::instance( pum_get_baloonup_id( $baloonup_id ), $force );
}

/**
 * Checks if the $baloonup is valid.
 *
 * @param mixed|PUM_Model_BaloonUp $baloonup
 *
 * @return bool
 */
function pum_is_baloonup( $baloonup ) {
	return is_object( $baloonup ) && is_numeric( $baloonup->ID ) && $baloonup->is_valid();
}

#region Deprecated & Soon to Be Deprecated Functions

/**
 * @param $baloonup_id
 *
 * @return array|null|MCMS_Post
 */
function balooncreate_get_baloonup( $baloonup_id ) {
	if ( ! $baloonup_id ) {
		$baloonup_id = balooncreate_get_the_baloonup_ID();
	}

	return get_post( $baloonup_id );
}

/**
 * @return int
 */
function balooncreate_get_the_baloonup_ID() {
	global $baloonup;

	return $baloonup ? $baloonup->ID : 0;
}

/**
 *
 */
function balooncreate_the_baloonup_ID() {
	echo balooncreate_get_the_baloonup_ID();
}

/**
 * @return int
 */
function get_the_baloonup_ID() {
	return balooncreate_get_the_baloonup_ID();
}

/**
 * @deprecated 1.4 Use the PUM_BaloonUp class instead.
 *
 * @param int $baloonup_id
 *
 * @return mixed|void
 */
function balooncreate_get_the_baloonup_myskin( $baloonup_id = null ) {
	if ( ! $baloonup_id ) {
		$baloonup_id = balooncreate_get_the_baloonup_ID();
	}
	$myskin = get_post_meta( $baloonup_id, 'baloonup_myskin', true );
	if ( empty( $myskin ) ) {
		$myskin = balooncreate_get_default_baloonup_myskin();
	}

	return apply_filters( 'balooncreate_get_the_baloonup_myskin', $myskin, $baloonup_id );
}

/**
 * @deprecated 1.4 Use pum_baloonup_myskin_id instead.
 * @param int $baloonup_id
 */
function balooncreate_the_baloonup_myskin( $baloonup_id = null ) {
	echo balooncreate_get_the_baloonup_myskin( $baloonup_id );
}

/**
 * @deprecated 1.4 Use the PUM_BaloonUp class instead.
 *
 * @param int $baloonup_id
 *
 * @return string
 */
function balooncreate_get_the_baloonup_classes( $baloonup_id = null ) {
	if ( ! $baloonup_id ) {
		$baloonup_id = balooncreate_get_the_baloonup_ID();
	}
	$myskin_id = balooncreate_get_the_baloonup_myskin( $baloonup_id );

	return implode( ' ', apply_filters( 'balooncreate_get_the_baloonup_classes', array(
		'balooncreate',
		'myskin-' . $myskin_id
	), $baloonup_id ) );
}

/**
 * @deprecated 1.4 Use pum_baloonup_classes instead.
 * @param int $baloonup_id
 */
function balooncreate_the_baloonup_classes( $baloonup_id = null ) {
	esc_attr_e( balooncreate_get_the_baloonup_classes( $baloonup_id ) );
}


/**
 * @deprecated 1.4 Built into the PUM_BaloonUp class instead.
 *
 * @param array $classes
 * @param int   $baloonup_id
 *
 * @return array
 */
function balooncreate_add_baloonup_size_classes( $classes, $baloonup_id ) {
	$baloonup_size = balooncreate_get_baloonup_display( $baloonup_id, 'size' );
	if ( in_array( $baloonup_size, array( 'nano', 'micro', 'tiny', 'small', 'medium', 'normal', 'large', 'xlarge' ) ) ) {
		$classes[] = 'responsive';
		$classes[] = 'size-' . $baloonup_size;
	} elseif ( $baloonup_size == 'custom' ) {
		$classes[] = 'size-custom';
	}

	if ( ! balooncreate_get_baloonup_display( $baloonup_id, 'custom_height_auto' ) && balooncreate_get_baloonup_display( $baloonup_id, 'scrollable_content' ) ) {
		$classes[] = 'scrollable';
	}

	return $classes;
}

/**
 * @deprecated 1.4 Use the PUM_BaloonUp class instead.
 *
 * @param int $baloonup_id
 *
 * @return array
 */
function balooncreate_get_the_baloonup_data_attr( $baloonup_id = null ) {
	if ( ! $baloonup_id ) {
		$baloonup_id = balooncreate_get_the_baloonup_ID();
	}
	$post      = get_post( $baloonup_id );
	$data_attr = array(
		'id'       => $baloonup_id,
		'slug'     => $post->post_name,
		'myskin_id' => balooncreate_get_the_baloonup_myskin( $baloonup_id ),
		'cookies'  => pum_get_baloonup_cookies( $baloonup_id ),
		'triggers' => pum_get_baloonup_triggers( $baloonup_id ),
		'meta'     => array(
			'display'    => balooncreate_get_baloonup_display( $baloonup_id ),
			'close'      => balooncreate_get_baloonup_close( $baloonup_id ),
			'click_open' => balooncreate_get_baloonup_click_open( $baloonup_id ),
		)
	);
	if ( balooncreate_get_baloonup_auto_open( $baloonup_id, 'enabled' ) ) {
		$data_attr['meta']['auto_open'] = balooncreate_get_baloonup_auto_open( $baloonup_id );
	}
	if ( balooncreate_get_baloonup_admin_debug( $baloonup_id, 'enabled' ) ) {
		$data_attr['meta']['admin_debug'] = balooncreate_get_baloonup_admin_debug( $baloonup_id );
	}

	return apply_filters( 'balooncreate_get_the_baloonup_data_attr', $data_attr, $baloonup_id );
}

/**
 * @param $data_attr
 *
 * @return mixed
 */
function balooncreate_clean_baloonup_data_attr( $data_attr ) {

	$display = $data_attr['meta']['display'];

	if ( ! in_array( $display['size'], array(
		'nano',
		'micro',
		'tiny',
		'small',
		'medium',
		'normal',
		'large',
		'xlarge'
	) )
	) {
		unset( $display['responsive_max_width'], $display['responsive_max_width_unit'], $display['responsive_min_width'], $display['responsive_min_width_unit'] );
	} else if ( $display['size'] != 'custom' ) {
		unset( $display['custom_height'], $display['custom_height_auto'], $display['custom_height_unit'], $display['custom_width'], $display['custom_width_unit'] );
	}

	if ( empty( $display['responsive_max_width'] ) ) {
		unset( $display['responsive_max_width'], $display['responsive_max_width_unit'] );
	}
	if ( empty( $display['responsive_min_width'] ) ) {
		unset( $display['responsive_min_width'], $display['responsive_min_width_unit'] );
	}
	if ( strpos( $display['location'], 'left' ) === false ) {
		unset( $display['position_left'] );
	}
	if ( strpos( $display['location'], 'right' ) === false ) {
		unset( $display['position_right'] );
	}
	if ( strpos( $display['location'], 'top' ) === false ) {
		unset( $display['position_top'] );
	}
	if ( strpos( $display['location'], 'bottom' ) === false ) {
		unset( $display['position_bottom'] );
	}

	$data_attr['meta']['display'] = $display;

	if ( $data_attr['meta']['click_open']['extra_selectors'] == '' ) {
		unset( $data_attr['meta']['click_open']['extra_selectors'] );
	}

	if ( $data_attr['meta']['close']['text'] == '' ) {
		unset( $data_attr['meta']['close']['text'] );
	}

	if ( $data_attr['meta']['close']['button_delay'] == '' ) {
		unset( $data_attr['meta']['close']['button_delay'] );
	}

	foreach ( $data_attr['meta'] as $key => $opts ) {
		if ( empty ( $opts ) ) {
			unset( $data_attr['meta'][ $key ] );
		}
	}

	return $data_attr;
}

//add_filter( 'balooncreate_get_the_baloonup_data_attr', 'balooncreate_clean_baloonup_data_attr' );

/**
 * @deprecated 1.4 Use pum_baloonup_data_attr instead.
 * @param int $baloonup_id
 */
function balooncreate_the_baloonup_data_attr( $baloonup_id = null ) {
	echo 'data-balooncreate="' . esc_attr( mcms_json_encode( balooncreate_get_the_baloonup_data_attr( $baloonup_id ) ) ) . '"';
}

/**
 * Returns the meta group of a baloonup or value if key is set.
 *
 * @since 1.3.0
 * @deprecated 1.4
 *
 * @param $group
 * @param int $baloonup_id ID number of the baloonup to retrieve a overlay meta for
 * @param null $key
 * @param null $default
 *
 * @return mixed array|string
 */
function balooncreate_get_baloonup_meta( $group, $baloonup_id = null, $key = null, $default = null ) {
	if ( ! $baloonup_id ) {
		$baloonup_id = balooncreate_get_the_baloonup_ID();
	}

	$values = get_post_meta( $baloonup_id, "baloonup_{$group}", true );

	if ( ! $values ) {
		$defaults = apply_filters( "balooncreate_baloonup_{$group}_defaults", array() );
		$values = array_merge( $defaults, balooncreate_get_baloonup_meta_group( $group, $baloonup_id ) );
	} else {
		$values = array_merge( balooncreate_get_baloonup_meta_group( $group, $baloonup_id ), $values );
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

		return apply_filters( "balooncreate_get_baloonup_{$group}_$key", $value, $baloonup_id );
	} else {
		return apply_filters( "balooncreate_get_baloonup_{$group}", $values, $baloonup_id );
	}
}

/**
 * Returns the meta group of a baloonup or value if key is set.
 *
 * @since 1.0
 * @deprecated 1.3.0
 *
 * @param int $baloonup_id ID number of the baloonup to retrieve a overlay meta for
 *
 * @return mixed array|string
 */
function balooncreate_get_baloonup_meta_group( $group, $baloonup_id = null, $key = null, $default = null ) {
	if ( ! $baloonup_id || $group === 'secure_logout') {
		$baloonup_id = balooncreate_get_the_baloonup_ID();
	}

	$post_meta         = get_post_custom( $baloonup_id );

	if ( ! is_array( $post_meta ) ) {
		$post_meta = array();
	}

	$default_check_key = 'baloonup_defaults_set';
	if ( ! in_array( $group, array( 'auto_open', 'close', 'display', 'targeting_condition' ) ) ) {
		$default_check_key = "baloonup_{$group}_defaults_set";
	}

	$group_values = array_key_exists( $default_check_key, $post_meta ) ? array() : apply_filters( "balooncreate_baloonup_{$group}_defaults", array() );
	foreach ( $post_meta as $meta_key => $value ) {
		if ( strpos( $meta_key, "baloonup_{$group}_" ) !== false ) {
			$new_key = str_replace( "baloonup_{$group}_", '', $meta_key );
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

		return apply_filters( "balooncreate_get_baloonup_{$group}_$key", $value, $baloonup_id );
	} else {
		return apply_filters( "balooncreate_get_baloonup_{$group}", $group_values, $baloonup_id );
	}
}

/**
 * Returns the load settings meta of a baloonup.
 *
 * @since 1.0
 * @deprecated 1.4
 *
 * @param int $baloonup_id ID number of the baloonup to retrieve a overlay meta for
 *
 * @return mixed array|string of the baloonup load settings meta
 */
function balooncreate_get_baloonup_targeting_condition( $baloonup_id = null, $key = null ) {
	return balooncreate_get_baloonup_meta_group( 'targeting_condition', $baloonup_id, $key );
}

/**
 *
 * @since 1.0
 * @deprecated 1.4
 *
 * @param      $baloonup_id
 * @param null $post_type
 *
 * @return array
 */
function balooncreate_get_baloonup_targeting_condition_includes( $baloonup_id, $post_type = null ) {
	$post_meta = get_post_custom_keys( $baloonup_id );
	$includes  = array();
	if ( ! empty( $post_meta ) ) {
		foreach ( $post_meta as $meta_key ) {
			if ( strpos( $meta_key, 'baloonup_targeting_condition_on_' ) !== false ) {
				$id = intval( substr( strrchr( $meta_key, "_" ), 1 ) );

				if ( $id > 0 ) {
					$remove = strrchr( $meta_key, strrchr( $meta_key, "_" ) );
					$name   = str_replace( 'baloonup_targeting_condition_on_', "", str_replace( $remove, "", $meta_key ) );

					$includes[ $name ][] = intval( $id );
				}
			}
		}
	}
	if ( $post_type ) {
		if ( ! isset( $includes[ $post_type ] ) || empty( $includes[ $post_type ] ) ) {
			$includes[ $post_type ] = array();
		}

		return $includes[ $post_type ];
	}

	return $includes;
}

/**
 * @param      $baloonup_id
 * @param null $post_type
 *
 * @return array
 */
function balooncreate_get_baloonup_targeting_condition_excludes( $baloonup_id, $post_type = null ) {
	$post_meta = get_post_custom_keys( $baloonup_id );
	$excludes  = array();
	if ( ! empty( $post_meta ) ) {
		foreach ( $post_meta as $meta_key ) {
			if ( strpos( $meta_key, 'baloonup_targeting_condition_exclude_on_' ) !== false ) {
				$id = intval( substr( strrchr( $meta_key, "_" ), 1 ) );

				if ( $id > 0 ) {
					$remove = strrchr( $meta_key, strrchr( $meta_key, "_" ) );
					$name   = str_replace( 'baloonup_targeting_condition_exclude_on_', "", str_replace( $remove, "", $meta_key ) );

					$excludes[ $name ][] = intval( $id );
				}
			}
		}
	}
	if ( $post_type ) {
		if ( ! isset( $excludes[ $post_type ] ) || empty( $excludes[ $post_type ] ) ) {
			$excludes[ $post_type ] = array();
		}

		return $excludes[ $post_type ];
	}

	return $excludes;
}

/**
 * Returns the title of a baloonup.
 *
 * @since 1.0
 * @deprecated 1.4 Use the PUM_BaloonUp class instead.
 *
 * @param int $baloonup_id ID number of the baloonup to retrieve a title for
 *
 * @return mixed string|int
 */
function balooncreate_get_the_baloonup_title( $baloonup_id = null ) {
	if ( ! $baloonup_id ) {
		$baloonup_id = balooncreate_get_the_baloonup_ID();
	}
	$title = get_post_meta( $baloonup_id, 'baloonup_title', true );

	return apply_filters( 'balooncreate_get_the_baloonup_title', $title, $baloonup_id );
}

/**
 * @deprecated 1.4 Use pum_baloonup_title instead.
 * @param int $baloonup_id
 */
function balooncreate_the_baloonup_title( $baloonup_id = null ) {
	echo esc_html( balooncreate_get_the_baloonup_title( $baloonup_id ) );
}

/**
 * @deprecated 1.4 Use the PUM_BaloonUp class instead.
 *
 * @param int $baloonup_id
 *
 * @return mixed|void
 */
function balooncreate_get_the_baloonup_content( $baloonup_id = null ) {
	if ( ! $baloonup_id ) {
		$baloonup_id = balooncreate_get_the_baloonup_ID();
	}
	$baloonup = balooncreate_get_baloonup( $baloonup_id );

	return apply_filters( 'the_baloonup_content', $baloonup->post_content, $baloonup_id );
}

/**
 * @deprecated 1.4 Use pum_baloonup_content instead.
 * @param int $baloonup_id
 */
function balooncreate_the_baloonup_content( $baloonup_id = null ) {
	echo balooncreate_get_the_baloonup_content( $baloonup_id );
}

/**
 * Returns the display meta of a baloonup.
 *
 * @since 1.0
 * @deprecated 1.4
 *
 * @param int $baloonup_id ID number of the baloonup to retrieve a display meta for
 *
 * @return mixed array|string of the baloonup display meta
 */
function balooncreate_get_baloonup_display( $baloonup_id = null, $key = null, $default = null ) {
	return pum_baloonup( $baloonup_id )->get_display( $key );
	//return balooncreate_get_baloonup_meta( 'display', $baloonup_id, $key, $default );
}

/**
 * Returns the close meta of a baloonup.
 *
 * @since 1.0
 * @deprecated 1.4 Use PUM_BaloonUp class instead
 *
 * @param int $baloonup_id ID number of the baloonup to retrieve a close meta for
 *
 * @return mixed array|string of the baloonup close meta
 */
function balooncreate_get_baloonup_close( $baloonup_id = null, $key = null, $default = null ) {
	return pum_baloonup( $baloonup_id )->get_close( $key );
	//return balooncreate_get_baloonup_meta( 'close', $baloonup_id, $key, $default );
}

/**
 * Returns the click_open meta of a baloonup.
 *
 * @since 1.0
 * @deprecated 1.4
 *
 * @param int $baloonup_id ID number of the baloonup to retrieve a click_open meta for
 * @param null $key
 * @param null $default
 *
 * @return mixed array|string of the baloonup click_open meta
 */
function balooncreate_get_baloonup_click_open( $baloonup_id = null, $key = null, $default = null ) {
	return balooncreate_get_baloonup_meta( 'click_open', $baloonup_id, $key, $default );
}

/**
 * Returns the auto open meta of a baloonup.
 *
 * @since 1.1.0
 * @deprecated 1.4
 *
 * @param int $baloonup_id ID number of the baloonup to retrieve a auto open meta for
 *
 * @return mixed array|string of the baloonup auto open meta
 */
function balooncreate_get_baloonup_auto_open( $baloonup_id = null, $key = null, $default = null ) {
	return balooncreate_get_baloonup_meta( 'auto_open', $baloonup_id, $key, $default );
}

/**
 * Returns the auto open meta of a baloonup.
 *
 * @since 1.1.8
 * @deprecated 1.4
 *
 * @param int $baloonup_id ID number of the baloonup to retrieve a admin debug meta for
 * @param null $key
 * @param null $default
 *
 * @return mixed array|string of the baloonup admin debug meta
 */
function balooncreate_get_baloonup_admin_debug( $baloonup_id = null, $key = null, $default = null ) {
	if ( ! current_user_can( 'edit_post', $baloonup_id ) ) {
		return null;
	}

	return balooncreate_get_baloonup_meta( 'admin_debug', $baloonup_id, $key, $default );
}

/**
 * todo replace this with customizable templates.
 *
 * @param $content
 * @param $baloonup_id
 *
 * @return string
 */
function balooncreate_baloonup_content_container( $content, $baloonup_id ) {
	$baloonup = balooncreate_get_baloonup( $baloonup_id );
	if ( $baloonup->post_type == 'baloonup' ) {
		$content = '<div class="balooncreate-content">' . $content;
		$content .= '</div>';
		if ( apply_filters( 'balooncreate_show_close_button', true, $baloonup_id ) ) {
			$content .= '<span class="balooncreate-close">' . apply_filters( 'balooncreate_baloonup_default_close_text', '&#215;', $baloonup_id ) . '</span>';
		}
	}

	return $content;
}

/**
 * @deprecated 1.4 use PUM_BaloonUp get_close_text method.
 *
 * @param $text
 * @param $baloonup_id
 *
 * @return mixed
 */
function balooncreate_baloonup_close_text( $text, $baloonup_id ) {
	$myskin_text = get_post_meta( balooncreate_get_the_baloonup_myskin( $baloonup_id ), 'baloonup_myskin_close_text', true );
	if ( $myskin_text && $myskin_text != '' ) {
		$text = $myskin_text;
	}

	$baloonup_close_text = balooncreate_get_baloonup_close( $baloonup_id, 'text' );
	if ( $baloonup_close_text && $baloonup_close_text != '' ) {
		$text = $baloonup_close_text;
	}

	return $text;
}
add_filter( 'balooncreate_baloonup_default_close_text', 'balooncreate_baloonup_close_text', 10, 2 );


/**
 * @param $baloonup_id
 *
 * @return mixed|void
 */
function balooncreate_baloonup_is_loadable( $baloonup_id ) {
	global $post, $mcms_query;

	$conditions  = balooncreate_get_baloonup_targeting_condition( $baloonup_id );
	$sitewide    = false;
	$is_loadable = false;

	if ( array_key_exists( 'on_entire_site', $conditions ) ) {
		$sitewide    = true;
		$is_loadable = true;
	}
	/**
	 * Front Page Checks
	 */
	if ( is_front_page() ) {
		if ( ! $sitewide && array_key_exists( 'on_home', $conditions ) ) {
			$is_loadable = true;
		} elseif ( $sitewide && array_key_exists( 'exclude_on_home', $conditions ) ) {
			$is_loadable = false;
		}
	}
	/**
	 * Blog Index Page Checks
	 */
	if ( is_home() ) {
		if ( ! $sitewide && array_key_exists( 'on_blog', $conditions ) ) {
			$is_loadable = true;
		} elseif ( $sitewide && array_key_exists( 'exclude_on_blog', $conditions ) ) {
			$is_loadable = false;
		}
	} /**
	 * Page Checks
	 */
	elseif ( is_page() ) {
		if ( ! $sitewide ) {
			// Load on all pages
			if ( array_key_exists( 'on_pages', $conditions ) && ! array_key_exists( 'on_specific_pages', $conditions ) ) {
				$is_loadable = true;
			} // Load on specific pages
			elseif ( array_key_exists( 'on_specific_pages', $conditions ) && array_key_exists( 'on_page_' . $post->ID, $conditions ) ) {
				$is_loadable = true;
			}
		} else {
			// Exclude on all pages.
			if ( array_key_exists( 'exclude_on_pages', $conditions ) && ! array_key_exists( 'exclude_on_specific_pages', $conditions ) ) {
				$is_loadable = false;
			} // Exclude on specific pages.
			elseif ( array_key_exists( 'exclude_on_specific_pages', $conditions ) && array_key_exists( 'exclude_on_page_' . $post->ID, $conditions ) ) {
				$is_loadable = false;
			}
		}
	} /**
	 * Post Checks
	 */
	elseif ( is_single() && $post->post_type == 'post' ) {
		if ( ! $sitewide ) {
			// Load on all posts`1
			if ( array_key_exists( 'on_posts', $conditions ) && ! array_key_exists( 'on_specific_posts', $conditions ) ) {
				$is_loadable = true;
			} // Load on specific posts
			elseif ( array_key_exists( 'on_specific_posts', $conditions ) && array_key_exists( 'on_post_' . $post->ID, $conditions ) ) {
				$is_loadable = true;
			}
		} else {
			// Exclude on all posts.
			if ( array_key_exists( 'exclude_on_posts', $conditions ) && ! array_key_exists( 'exclude_on_specific_posts', $conditions ) ) {
				$is_loadable = false;
			} // Exclude on specific posts.
			elseif ( array_key_exists( 'exclude_on_specific_posts', $conditions ) && array_key_exists( 'exclude_on_post_' . $post->ID, $conditions ) ) {
				$is_loadable = false;
			}
		}
	} /**
	 * Category Checks
	 */
	elseif ( is_category() ) {
		$category_id = $mcms_query->get_queried_object_id();
		if ( ! $sitewide ) {
			// Load on all categories
			if ( array_key_exists( 'on_categorys', $conditions ) && ! array_key_exists( 'on_specific_categorys', $conditions ) ) {
				$is_loadable = true;
			} // Load on specific categories
			elseif ( array_key_exists( 'on_specific_categorys', $conditions ) && array_key_exists( 'on_category_' . $category_id, $conditions ) ) {
				$is_loadable = true;
			}
		} else {
			// Exclude on all categories.
			if ( array_key_exists( 'exclude_on_categorys', $conditions ) && ! array_key_exists( 'exclude_on_specific_categorys', $conditions ) ) {
				$is_loadable = false;
			} // Exclude on specific categories.
			elseif ( array_key_exists( 'exclude_on_specific_categorys', $conditions ) && array_key_exists( 'exclude_on_category_' . $category_id, $conditions ) ) {
				$is_loadable = false;
			}
		}
	} /**
	 * Tag Checks
	 */
	elseif ( is_tag() ) {
		$tag_id = $mcms_query->get_queried_object_id();
		if ( ! $sitewide ) {
			// Load on all tags
			if ( array_key_exists( 'on_tags', $conditions ) && ! array_key_exists( 'on_specific_tags', $conditions ) ) {
				$is_loadable = true;
			} // Load on specific tags
			elseif ( array_key_exists( 'on_specific_tags', $conditions ) && array_key_exists( 'on_tag_' . $tag_id, $conditions ) ) {
				$is_loadable = true;
			}
		} else {
			// Exclude on all tags.
			if ( array_key_exists( 'exclude_on_tags', $conditions ) && ! array_key_exists( 'exclude_on_specific_tags', $conditions ) ) {
				$is_loadable = false;
			} // Exclude on specific tags.
			elseif ( array_key_exists( 'exclude_on_specific_tags', $conditions ) && array_key_exists( 'exclude_on_tag_' . $tag_id, $conditions ) ) {
				$is_loadable = false;
			}
		}
	} /**
	 * Custom Post Type Checks
	 * Add support for custom post types
	 */
	elseif ( is_single() && ! in_array( $post->post_type, array( 'post', 'page' ) ) ) {
		$pt = $post->post_type;

		if ( ! $sitewide ) {
			// Load on all post type items
			if ( array_key_exists( "on_{$pt}s", $conditions ) && ! array_key_exists( "on_specific_{$pt}s", $conditions ) ) {
				$is_loadable = true;
			} // Load on specific post type items
			elseif ( array_key_exists( "on_specific_{$pt}s", $conditions ) && array_key_exists( "on_{$pt}_" . $post->ID, $conditions ) ) {
				$is_loadable = true;
			}
		} else {
			// Exclude on all post type items.
			if ( array_key_exists( "exclude_on_{$pt}s", $conditions ) && ! array_key_exists( "exclude_on_specific_{$pt}s", $conditions ) ) {
				$is_loadable = false;
			} // Exclude on specific post type items.
			elseif ( array_key_exists( "exclude_on_specific_{$pt}s", $conditions ) && array_key_exists( "exclude_on_{$pt}_" . $post->ID, $conditions ) ) {
				$is_loadable = false;
			}
		}
	} /**
	 * Custom Taxonomy Checks
	 * Add support for custom taxonomies
	 */
	elseif ( is_tax() ) {
		$term_id = $mcms_query->get_queried_object_id();
		$tax     = get_query_var( 'taxonomy' );
		if ( ! $sitewide ) {
			// Load on all custom tax terms.
			if ( array_key_exists( "on_{$tax}s", $conditions ) && ! array_key_exists( "on_specific_{$tax}s", $conditions ) ) {
				$is_loadable = true;
			} // Load on specific custom tax terms.
			elseif ( array_key_exists( "on_specific_{$tax}s", $conditions ) && array_key_exists( "on_{$tax}_" . $term_id, $conditions ) ) {
				$is_loadable = true;
			}
		} else {
			// Exclude on all custom tax terms.
			if ( array_key_exists( "exclude_on_{$tax}s", $conditions ) && ! array_key_exists( "exclude_on_specific_{$tax}s", $conditions ) ) {
				$is_loadable = false;
			} // Exclude on specific custom tax terms.
			elseif ( array_key_exists( "exclude_on_specific_{$tax}s", $conditions ) && array_key_exists( "exclude_on_{$tax}_" . $term_id, $conditions ) ) {
				$is_loadable = false;
			}
		}
	}
	/**
	 * Search Checks
	 */
	if ( is_search() ) {
		if ( ! $sitewide && array_key_exists( 'on_search', $conditions ) ) {
			$is_loadable = true;
		} elseif ( $sitewide && array_key_exists( 'exclude_on_search', $conditions ) ) {
			$is_loadable = false;
		}
	}
	/**
	 * 404 Page Checks
	 */
	if ( is_404() ) {
		if ( ! $sitewide && array_key_exists( 'on_404', $conditions ) ) {
			$is_loadable = true;
		} elseif ( $sitewide && array_key_exists( 'exclude_on_404', $conditions ) ) {
			$is_loadable = false;
		}
	}

	/*
		// An Archive is a Category, Tag, Author or a Date based pages.
		elseif( is_archive() ) {
			if( array_key_exists("on_entire_site", $conditions)) {
				$is_loadable = true;
			}
		}
	*/

	return apply_filters( 'balooncreate_baloonup_is_loadable', $is_loadable, $baloonup_id, $conditions, $sitewide );
}


/**
 * @return MCMS_Query
 */
function get_all_baloonups() {
	$query = PUM_BaloonUps::get_all();

	return $query;
}


#endregion