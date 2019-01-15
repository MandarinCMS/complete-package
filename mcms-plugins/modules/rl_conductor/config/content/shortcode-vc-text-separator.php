<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

require_once 'vc-icon-element.php';
$icon_params = vc_icon_element_params();
/* Separator (Divider)
---------------------------------------------------------- */
$icons_params = vc_map_integrate_shortcode( $icon_params, 'i_', __( 'Icon', 'rl_conductor' ), array(
	'exclude' => array(
		'align',
		'css',
		'el_class',
		'el_id',
		'link',
		'css_animation',
	),
	// we need only type, icon_fontawesome, icon_blabla..., NOT color and etc
), array(
	'element' => 'add_icon',
	'value' => 'true',
) );

// populate integrated vc_icons params.
if ( is_array( $icons_params ) && ! empty( $icons_params ) ) {
	foreach ( $icons_params as $key => $param ) {
		if ( is_array( $param ) && ! empty( $param ) ) {
			if ( isset( $param['admin_label'] ) ) {
				// remove admin label
				unset( $icons_params[ $key ]['admin_label'] );
			}
		}
	}
}
return array(
	'name' => __( 'Separator with Text', 'rl_conductor' ),
	'base' => 'vc_text_separator',
	'icon' => 'icon-mcmsb-ui-separator-label',
	'category' => __( 'Content', 'rl_conductor' ),
	'description' => __( 'Horizontal separator line with heading', 'rl_conductor' ),
	'params' => array_merge( array(
		array(
			'type' => 'textfield',
			'heading' => __( 'Title', 'rl_conductor' ),
			'param_name' => 'title',
			'holder' => 'div',
			'value' => __( 'Title', 'rl_conductor' ),
			'description' => __( 'Add text to separator.', 'rl_conductor' ),
		),
		array(
			'type' => 'checkbox',
			'heading' => __( 'Add icon?', 'rl_conductor' ),
			'param_name' => 'add_icon',
		),
	), $icons_params, array(
		array(
			'type' => 'dropdown',
			'heading' => __( 'Title position', 'rl_conductor' ),
			'param_name' => 'title_align',
			'value' => array(
				__( 'Center', 'rl_conductor' ) => 'separator_align_center',
				__( 'Left', 'rl_conductor' ) => 'separator_align_left',
				__( 'Right', 'rl_conductor' ) => 'separator_align_right',
			),
			'description' => __( 'Select title location.', 'rl_conductor' ),
		),
		array(
			'type' => 'dropdown',
			'heading' => __( 'Separator alignment', 'rl_conductor' ),
			'param_name' => 'align',
			'value' => array(
				__( 'Center', 'rl_conductor' ) => 'align_center',
				__( 'Left', 'rl_conductor' ) => 'align_left',
				__( 'Right', 'rl_conductor' ) => 'align_right',
			),
			'description' => __( 'Select separator alignment.', 'rl_conductor' ),
		),
		array(
			'type' => 'dropdown',
			'heading' => __( 'Color', 'rl_conductor' ),
			'param_name' => 'color',
			'value' => array_merge( getVcShared( 'colors' ), array( __( 'Custom color', 'rl_conductor' ) => 'custom' ) ),
			'std' => 'grey',
			'description' => __( 'Select color of separator.', 'rl_conductor' ),
			'param_holder_class' => 'vc_colored-dropdown',
		),
		array(
			'type' => 'colorpicker',
			'heading' => __( 'Custom Color', 'rl_conductor' ),
			'param_name' => 'accent_color',
			'description' => __( 'Custom separator color for your element.', 'rl_conductor' ),
			'dependency' => array(
				'element' => 'color',
				'value' => array( 'custom' ),
			),
		),
		array(
			'type' => 'dropdown',
			'heading' => __( 'Style', 'rl_conductor' ),
			'param_name' => 'style',
			'value' => getVcShared( 'separator styles' ),
			'description' => __( 'Separator display style.', 'rl_conductor' ),
		),
		array(
			'type' => 'dropdown',
			'heading' => __( 'Border width', 'rl_conductor' ),
			'param_name' => 'border_width',
			'value' => getVcShared( 'separator border widths' ),
			'description' => __( 'Select border width (pixels).', 'rl_conductor' ),
		),
		array(
			'type' => 'dropdown',
			'heading' => __( 'Element width', 'rl_conductor' ),
			'param_name' => 'el_width',
			'value' => getVcShared( 'separator widths' ),
			'description' => __( 'Separator element width in percents.', 'rl_conductor' ),
		),
		vc_map_add_css_animation(),
		array(
			'type' => 'el_id',
			'heading' => __( 'Element ID', 'rl_conductor' ),
			'param_name' => 'el_id',
			'description' => sprintf( __( 'Enter element ID (Note: make sure it is unique and valid according to <a href="%s" target="_blank">w3c specification</a>).', 'rl_conductor' ), 'http://www.w3schools.com/tags/att_global_id.asp' ),
		),
		array(
			'type' => 'textfield',
			'heading' => __( 'Extra class name', 'rl_conductor' ),
			'param_name' => 'el_class',
			'description' => __( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'rl_conductor' ),
		),
		array(
			'type' => 'hidden',
			'param_name' => 'layout',
			'value' => 'separator_with_text',
		),
		array(
			'type' => 'css_editor',
			'heading' => __( 'CSS box', 'rl_conductor' ),
			'param_name' => 'css',
			'group' => __( 'Design Options', 'rl_conductor' ),
		),
	) ),
	'js_view' => 'VcTextSeparatorView',
);
