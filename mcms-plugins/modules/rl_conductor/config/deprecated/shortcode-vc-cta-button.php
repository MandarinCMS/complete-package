<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

$colors_arr = vc_colors_arr();
$icons_arr = vc_icons_arr();
$size_arr = vc_size_arr();
return array(
	'name' => __( 'Old Call to Action', 'rl_conductor' ),
	'base' => 'vc_cta_button',
	'icon' => 'icon-mcmsb-call-to-action',
	'deprecated' => '4.5',
	'content_element' => false,
	'category' => __( 'Content', 'rl_conductor' ),
	'description' => __( 'Catch visitors attention with CTA block', 'rl_conductor' ),
	'params' => array(
		array(
			'type' => 'textarea',
			'admin_label' => true,
			'heading' => __( 'Text', 'rl_conductor' ),
			'param_name' => 'call_text',
			'value' => __( 'Click edit button to change this text.', 'rl_conductor' ),
			'description' => __( 'Enter text content.', 'rl_conductor' ),
		),
		array(
			'type' => 'textfield',
			'heading' => __( 'Text on the button', 'rl_conductor' ),
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
				'callback' => 'vc_cta_button_param_target_callback',
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
			'heading' => __( 'Button icon', 'rl_conductor' ),
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
			'type' => 'dropdown',
			'heading' => __( 'Button position', 'rl_conductor' ),
			'param_name' => 'position',
			'value' => array(
				__( 'Right', 'rl_conductor' ) => 'cta_align_right',
				__( 'Left', 'rl_conductor' ) => 'cta_align_left',
				__( 'Bottom', 'rl_conductor' ) => 'cta_align_bottom',
			),
			'description' => __( 'Select button alignment.', 'rl_conductor' ),
		),
		vc_map_add_css_animation(),
		array(
			'type' => 'textfield',
			'heading' => __( 'Extra class name', 'rl_conductor' ),
			'param_name' => 'el_class',
			'description' => __( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'rl_conductor' ),
		),
	),
	'js_view' => 'VcCallToActionView',
);
