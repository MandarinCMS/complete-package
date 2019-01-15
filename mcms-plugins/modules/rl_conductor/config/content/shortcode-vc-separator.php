<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

return array(
	'name' => __( 'Separator', 'rl_conductor' ),
	'base' => 'vc_separator',
	'icon' => 'icon-mcmsb-ui-separator',
	'show_settings_on_create' => true,
	'category' => __( 'Content', 'rl_conductor' ),
	'description' => __( 'Horizontal separator line', 'rl_conductor' ),
	'params' => array(
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
			'type' => 'dropdown',
			'heading' => __( 'Alignment', 'rl_conductor' ),
			'param_name' => 'align',
			'value' => array(
				__( 'Center', 'rl_conductor' ) => 'align_center',
				__( 'Left', 'rl_conductor' ) => 'align_left',
				__( 'Right', 'rl_conductor' ) => 'align_right',
			),
			'description' => __( 'Select separator alignment.', 'rl_conductor' ),
		),
		array(
			'type' => 'colorpicker',
			'heading' => __( 'Custom Border Color', 'rl_conductor' ),
			'param_name' => 'accent_color',
			'description' => __( 'Select border color for your element.', 'rl_conductor' ),
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
			'description' => __( 'Select separator width (percentage).', 'rl_conductor' ),
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
			'type' => 'css_editor',
			'heading' => __( 'CSS box', 'rl_conductor' ),
			'param_name' => 'css',
			'group' => __( 'Design Options', 'rl_conductor' ),
		),
	),
);
