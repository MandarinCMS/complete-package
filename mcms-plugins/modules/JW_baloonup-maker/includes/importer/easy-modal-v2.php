<?php
/**
 *  Function
 *
 * @package  POPMAKE_EMODAL
 * @subpackage  Functions/Import
 * @copyright   Copyright (c) 2014, Daniel Iser
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since   1.0
 */

// Exit if accessed directly
if ( ! defined( 'BASED_TREE_URI' ) ) {
	exit;
}

/**
 * Import
 *
 * Runs on module install by setting up the post types, custom taxonomies,
 * flushing rewrite rules also creates the module and populates the settings
 * fields for those module pages. After successful install, the user is
 * redirected to the POPMAKE_EMODAL Welcome screen.
 *
 * @since 1.0
 * @global $mcmsdb
 * @global $balooncreate_options
 * @global $mcms_version
 * @return void
 */
function balooncreate_emodal_v2_import() {
	global $mcmsdb, $balooncreate_options, $mcms_version, $balooncreate_tools_page;

	require_once POPMAKE_DIR . 'includes/importer/easy-modal-v2/functions.php';

	if ( ! class_exists( 'EModal_Model' ) ) {
		require_once POPMAKE_DIR . '/includes/importer/easy-modal-v2/model.php';
	}
	if ( ! class_exists( 'EModal_Model_Modal' ) ) {
		require_once POPMAKE_DIR . '/includes/importer/easy-modal-v2/model/modal.php';
	}
	if ( ! class_exists( 'EModal_Model_mySkin' ) ) {
		require_once POPMAKE_DIR . '/includes/importer/easy-modal-v2/model/myskin.php';
	}
	if ( ! class_exists( 'EModal_Model_mySkin_Meta' ) ) {
		require_once POPMAKE_DIR . '/includes/importer/easy-modal-v2/model/myskin/meta.php';
	}
	if ( ! class_exists( 'EModal_Model_Modal_Meta' ) ) {
		require_once POPMAKE_DIR . '/includes/importer/easy-modal-v2/model/modal/meta.php';
	}


	$myskins       = get_all_modal_myskins( '1 = 1' );
	$myskin_id_map = array();
	foreach ( $myskins as $mySkin ) {
		$myskin = $mySkin->as_array();
		$meta  = $myskin['meta'];

		$myskin_meta = apply_filters( 'balooncreate_emodal_import_myskin_meta', array(
			'baloonup_myskin_defaults_set'                   => true,
			'baloonup_myskin_overlay_background_color'       => $meta['overlay']['background']['color'],
			'baloonup_myskin_overlay_background_opacity'     => $meta['overlay']['background']['opacity'],
			'baloonup_myskin_container_padding'              => $meta['container']['padding'],
			'baloonup_myskin_container_background_color'     => $meta['container']['background']['color'],
			'baloonup_myskin_container_background_opacity'   => $meta['container']['background']['opacity'],
			'baloonup_myskin_container_border_radius'        => $meta['container']['border']['radius'],
			'baloonup_myskin_container_border_style'         => $meta['container']['border']['style'],
			'baloonup_myskin_container_border_color'         => $meta['container']['border']['color'],
			'baloonup_myskin_container_border_width'         => $meta['container']['border']['width'],
			'baloonup_myskin_container_boxshadow_inset'      => $meta['container']['boxshadow']['inset'],
			'baloonup_myskin_container_boxshadow_horizontal' => $meta['container']['boxshadow']['horizontal'],
			'baloonup_myskin_container_boxshadow_vertical'   => $meta['container']['boxshadow']['vertical'],
			'baloonup_myskin_container_boxshadow_blur'       => $meta['container']['boxshadow']['blur'],
			'baloonup_myskin_container_boxshadow_spread'     => $meta['container']['boxshadow']['spread'],
			'baloonup_myskin_container_boxshadow_color'      => $meta['container']['boxshadow']['color'],
			'baloonup_myskin_container_boxshadow_opacity'    => $meta['container']['boxshadow']['opacity'],
			'baloonup_myskin_title_font_color'               => $meta['title']['font']['color'],
			'baloonup_myskin_title_line_height'              => $meta['title']['font']['size'],
			'baloonup_myskin_title_font_size'                => $meta['title']['font']['size'],
			'baloonup_myskin_title_font_family'              => $meta['title']['font']['family'],
			'baloonup_myskin_title_font_weight'              => $meta['title']['font']['weight'],
			'baloonup_myskin_title_font_style'               => $meta['title']['font']['style'],
			'baloonup_myskin_title_text_align'               => $meta['title']['text']['align'],
			'baloonup_myskin_title_textshadow_horizontal'    => $meta['title']['textshadow']['horizontal'],
			'baloonup_myskin_title_textshadow_vertical'      => $meta['title']['textshadow']['vertical'],
			'baloonup_myskin_title_textshadow_blur'          => $meta['title']['textshadow']['blur'],
			'baloonup_myskin_title_textshadow_color'         => $meta['title']['textshadow']['color'],
			'baloonup_myskin_title_textshadow_opacity'       => $meta['title']['textshadow']['opacity'],
			'baloonup_myskin_content_font_color'             => $meta['content']['font']['color'],
			'baloonup_myskin_content_font_family'            => $meta['content']['font']['family'],
			'baloonup_myskin_content_font_weight'            => $meta['content']['font']['weight'],
			'baloonup_myskin_content_font_style'             => $meta['content']['font']['style'],
			'baloonup_myskin_close_text'                     => $meta['close']['text'],
			'baloonup_myskin_close_padding'                  => $meta['close']['padding'],
			'baloonup_myskin_close_location'                 => $meta['close']['location'],
			'baloonup_myskin_close_position_top'             => $meta['close']['position']['top'],
			'baloonup_myskin_close_position_left'            => $meta['close']['position']['left'],
			'baloonup_myskin_close_position_bottom'          => $meta['close']['position']['bottom'],
			'baloonup_myskin_close_position_right'           => $meta['close']['position']['right'],
			'baloonup_myskin_close_line_height'              => $meta['close']['font']['size'],
			'baloonup_myskin_close_font_color'               => $meta['close']['font']['color'],
			'baloonup_myskin_close_font_size'                => $meta['close']['font']['size'],
			'baloonup_myskin_close_font_family'              => $meta['close']['font']['family'],
			'baloonup_myskin_close_font_weight'              => $meta['close']['font']['weight'],
			'baloonup_myskin_close_font_style'               => $meta['close']['font']['style'],
			'baloonup_myskin_close_background_color'         => $meta['close']['background']['color'],
			'baloonup_myskin_close_background_opacity'       => $meta['close']['background']['opacity'],
			'baloonup_myskin_close_border_radius'            => $meta['close']['border']['radius'],
			'baloonup_myskin_close_border_style'             => $meta['close']['border']['style'],
			'baloonup_myskin_close_border_color'             => $meta['close']['border']['color'],
			'baloonup_myskin_close_border_width'             => $meta['close']['border']['width'],
			'baloonup_myskin_close_boxshadow_inset'          => $meta['close']['boxshadow']['inset'],
			'baloonup_myskin_close_boxshadow_horizontal'     => $meta['close']['boxshadow']['horizontal'],
			'baloonup_myskin_close_boxshadow_vertical'       => $meta['close']['boxshadow']['vertical'],
			'baloonup_myskin_close_boxshadow_blur'           => $meta['close']['boxshadow']['blur'],
			'baloonup_myskin_close_boxshadow_spread'         => $meta['close']['boxshadow']['spread'],
			'baloonup_myskin_close_boxshadow_color'          => $meta['close']['boxshadow']['color'],
			'baloonup_myskin_close_boxshadow_opacity'        => $meta['close']['boxshadow']['opacity'],
			'baloonup_myskin_close_textshadow_horizontal'    => $meta['close']['textshadow']['horizontal'],
			'baloonup_myskin_close_textshadow_vertical'      => $meta['close']['textshadow']['vertical'],
			'baloonup_myskin_close_textshadow_blur'          => $meta['close']['textshadow']['blur'],
			'baloonup_myskin_close_textshadow_color'         => $meta['close']['textshadow']['color'],
			'baloonup_myskin_close_textshadow_opacity'       => $meta['close']['textshadow']['opacity'],
		), $mySkin );

		$new_myskin_id = mcms_insert_post(
			array(
				'post_title'     => $myskin['name'],
				'post_status'    => $myskin['is_trash'] ? 'trash' : 'publish',
				'post_author'    => get_current_user_id(),
				'post_type'      => 'baloonup_myskin',
				'comment_status' => 'closed'
			)
		);
		foreach ( $myskin_meta as $meta_key => $meta_value ) {
			update_post_meta( $new_myskin_id, $meta_key, $meta_value );
		}
		update_post_meta( $new_myskin_id, 'baloonup_myskin_old_easy_modal_id', $myskin['id'] );

		$myskin_id_map[ $myskin['id'] ] = $new_myskin_id;
	}

	if ( count( $myskins ) == 1 ) {
		update_post_meta( $new_myskin_id, 'baloonup_myskin_defaults_set', true );
		update_option( 'balooncreate_default_myskin', $new_myskin_id );
	}

	$modals = get_all_modals( '1 = 1' );

	//echo '<pre>'; var_export(balooncreate_baloonup_meta_fields()); echo '</pre>';

	foreach ( $modals as $Modal ) {
		$modal = $Modal->as_array();
		$meta  = $modal['meta'];

		$modal_meta = apply_filters( 'balooncreate_emodal_import_modal_meta', array(
			'baloonup_old_easy_modal_id'                 => $modal['id'],
			'baloonup_defaults_set'                      => true,
			'baloonup_myskin'                             => isset( $myskin_id_map[ $myskin['id'] ] ) ? $myskin_id_map[ $myskin['id'] ] : null,
			'baloonup_title'                             => $modal['title'],
			'baloonup_display_scrollable_content'        => null,
			'baloonup_display_overlay_disabled'          => $meta['display']['overlay_disabled'],
			'baloonup_display_size'                      => $meta['display']['size'],
			'baloonup_display_responsive_min_width'      => '',
			'baloonup_display_responsive_min_width_unit' => 'px',
			'baloonup_display_responsive_max_width'      => '',
			'baloonup_display_responsive_max_width_unit' => 'px',
			'baloonup_display_custom_width'              => $meta['display']['custom_width'],
			'baloonup_display_custom_width_unit'         => $meta['display']['custom_width_unit'],
			'baloonup_display_custom_height'             => $meta['display']['custom_height'],
			'baloonup_display_custom_height_unit'        => $meta['display']['custom_height_unit'],
			'baloonup_display_custom_height_auto'        => $meta['display']['custom_height_auto'],
			'baloonup_display_location'                  => $meta['display']['location'],
			'baloonup_display_position_top'              => $meta['display']['position']['top'],
			'baloonup_display_position_left'             => $meta['display']['position']['left'],
			'baloonup_display_position_bottom'           => $meta['display']['position']['bottom'],
			'baloonup_display_position_right'            => $meta['display']['position']['right'],
			'baloonup_display_position_fixed'            => $meta['display']['position']['fixed'],
			'baloonup_display_animation_type'            => $meta['display']['animation']['type'],
			'baloonup_display_animation_speed'           => $meta['display']['animation']['speed'],
			'baloonup_display_animation_origin'          => $meta['display']['animation']['origin'],
			'baloonup_close_overlay_click'               => $meta['close']['overlay_click'],
			'baloonup_close_esc_press'                   => $meta['close']['esc_press'],
			'baloonup_close_f4_press'                    => null,
		), $Modal );

		if ( $modal['is_sitewide'] == 1 ) {
			$modal_meta['baloonup_targeting_condition_on_entire_site'] = true;
		}

		$new_modal_id = mcms_insert_post(
			array(
				'post_title'     => $modal['name'],
				'post_status'    => $modal['is_trash'] ? 'trash' : 'publish',
				'post_content'   => $modal['content'],
				'post_author'    => get_current_user_id(),
				'post_type'      => 'baloonup',
				'comment_status' => 'closed'
			)
		);
		foreach ( $modal_meta as $meta_key => $meta_value ) {
			update_post_meta( $new_modal_id, $meta_key, $meta_value );
		}

	}
}


function balooncreate_emodal_init() {
	global $balooncreate_options;
	if ( isset( $balooncreate_options['enable_easy_modal_compatibility_mode'] ) ) {
		if ( ! shortcode_exists( 'modal' ) ) {
			add_shortcode( 'modal', 'balooncreate_emodal_shortcode_modal' );
		}
		add_filter( 'balooncreate_get_the_baloonup_data_attr', 'balooncreate_emodal_get_the_baloonup_data_attr', 10, 2 );
		add_filter( 'balooncreate_shortcode_baloonup_default_atts', 'balooncreate_emodal_shortcode_baloonup_default_atts', 10, 2 );
		add_filter( 'balooncreate_shortcode_data_attr', 'balooncreate_emodal_shortcode_data_attr', 10, 2 );

		add_filter( 'pum_baloonup_is_loadable', 'balooncreate_emodal_baloonup_is_loadable', 20, 2 );
	}
}

add_action( 'init', 'balooncreate_emodal_init' );


function balooncreate_emodal_baloonup_is_loadable( $return, $baloonup_id ) {
	global $post;
	if ( empty( $post ) || ! isset( $post->ID ) ) {
		return $return;
	}
	$easy_modal_id = get_post_meta( $baloonup_id, 'baloonup_old_easy_modal_id', true );
	$post_modals   = get_post_meta( $post->ID, 'easy-modal_post_modals', true );
	if ( ! $easy_modal_id || empty( $post_modals ) || ! in_array( $easy_modal_id, $post_modals ) ) {
		return $return;
	}

	return true;
}

function balooncreate_emodal_get_the_baloonup_data_attr( $data_attr, $baloonup_id ) {
	$easy_modal_id = get_post_meta( $baloonup_id, 'baloonup_old_easy_modal_id', true );
	if ( ! $easy_modal_id ) {
		return $data_attr;
	}

	return array_merge( $data_attr, array(
		'old_easy_modal_id' => $easy_modal_id
	) );
}

function balooncreate_emodal_shortcode_modal( $atts, $content = null ) {
	$atts = shortcode_atts(
		apply_filters( 'emodal_shortcode_modal_default_atts', array(
			'id'               => "",
			'myskin_id'         => null,
			'title'            => null,
			'overlay_disabled' => null,
			'size'             => null,
			'width'            => null,
			'widthUnit'        => null,
			'height'           => null,
			'heightUnit'       => null,
			'location'         => null,
			'positionTop'      => null,
			'positionLeft'     => null,
			'positionBottom'   => null,
			'positionRight'    => null,
			'positionFixed'    => null,
			'animation'        => null,
			'animationSpeed'   => null,
			'animationOrigin'  => null,
			'overlayClose'     => null,
			'escClose'         => null,
			// Deprecated
			'myskin'            => null,
			'duration'         => null,
			'direction'        => null,
			'overlayEscClose'  => null,
		) ),
		apply_filters( 'emodal_shortcode_modal_atts', $atts )
	);

	$new_shortcode_atts = array(
		'id'               => $atts['id'],
		'emodal_id'        => $atts['id'],
		'myskin_id'         => $atts['myskin_id'],
		'title'            => $atts['title'],
		'overlay_disabled' => $atts['overlay_disabled'],
		'size'             => $atts['size'],
		'width'            => $atts['width'],
		'width_unit'       => $atts['widthUnit'],
		'height'           => $atts['height'],
		'height_unit'      => $atts['heightUnit'],
		'location'         => $atts['location'],
		'position_top'     => $atts['positionTop'],
		'position_left'    => $atts['positionLeft'],
		'position_bottom'  => $atts['positionBottom'],
		'position_right'   => $atts['positionRight'],
		'position_fixed'   => $atts['positionFixed'],
		'animation_type'   => $atts['animation'],
		'animation_speed'  => $atts['animationSpeed'],
		'animation_origin' => $atts['animationOrigin'],
		'overlay_click'    => $atts['overlayClose'],
		'esc_press'        => $atts['escClose']
	);

	$shortcode = '[baloonup ';

	foreach ( $new_shortcode_atts as $attr => $val ) {
		if ( $val && ! empty( $val ) ) {
			$shortcode .= $attr . '="' . $val . '" ';
		}
	}

	$shortcode .= ']' . $content . '[/baloonup]';

	return do_shortcode( $shortcode );
}


function balooncreate_emodal_shortcode_baloonup_default_atts( $default_atts = array() ) {
	return array_merge( $default_atts, array(
		'emodal_id' => null,
	) );
}


function balooncreate_emodal_shortcode_data_attr( $data, $attr ) {
	if ( ! empty( $attr['emodal_id'] ) ) {
		$data['old_easy_modal_id'] = $attr['emodal_id'];
	}

	return $data;
}


