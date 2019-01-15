<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

$colors_arr = vc_colors_arr();
$size_arr = vc_size_arr();
$icons_arr = vc_icons_arr();
return array(
	'name' => __( 'Old Button', 'rl_conductor' ) . ' 1',
	'base' => 'vc_button',
	'icon' => 'icon-mcmsb-ui-button',
	'category' => __( 'Content', 'rl_conductor' ),
	'deprecated' => '4.5',
	'content_element' => false,
	'description' => __( 'Eye catching button', 'rl_conductor' ),
	'params' => array(
		array(
			'type' => 'textfield',
			'heading' => __( 'Text', 'rl_conductor' ),
			'holder' => 'button',
			'class' => 'mcmsb_button',
			'param_name' => 'title',
			'value' => __( 'Text on the button', 'rl_conductor' ),
			'description' => __( 'Enter text on the button.', 'rl_conductor' ),
		),
		array(
			'type' => 'href',
			'heading' => __( 'URL (Link)', 'rl_conductor' ),
			'param_name' => 'href',
			'description' => __( 'Enter button link.', 'rl_conductor' ),
		),
		array(
			'type' => 'dropdown',
			'heading' => __( 'Target', 'rl_conductor' ),
			'param_name' => 'target',
			'value' => vc_target_param_list(),
			'dependency' => array(
				'element' => 'href',
				'not_empty' => true,
				'callback' => 'vc_button_param_target_callback',
			),
		),
		array(
			'type' => 'dropdown',
			'heading' => __( 'Color', 'rl_conductor' ),
			'param_name' => 'color',
			'value' => $colors_arr,
			'description' => __( 'Select button color.', 'rl_conductor' ),
			'param_holder_class' => 'vc_colored-dropdown',
		),
		array(
			'type' => 'dropdown',
			'heading' => __( 'Icon', 'rl_conductor' ),
			'param_name' => 'icon',
			'value' => $icons_arr,
			'description' => __( 'Select icon to display on button.', 'rl_conductor' ),
		),
		array(
			'type' => 'dropdown',
			'heading' => __( 'Size', 'rl_conductor' ),
			'param_name' => 'size',
			'value' => $size_arr,
			'description' => __( 'Select button size.', 'rl_conductor' ),
		),
		array(
			'type' => 'textfield',
			'heading' => __( 'Extra class name', 'rl_conductor' ),
			'param_name' => 'el_class',
			'description' => __( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'rl_conductor' ),
		),
	),
	'js_view' => 'VcButtonView',
);
