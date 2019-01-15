<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

return array(
	'name' => __( 'Pie Chart', 'rl_conductor' ),
	'base' => 'vc_pie',
	'class' => '',
	'icon' => 'icon-mcmsb-vc_pie',
	'category' => __( 'Content', 'rl_conductor' ),
	'description' => __( 'Animated pie chart', 'rl_conductor' ),
	'params' => array(
		array(
			'type' => 'textfield',
			'heading' => __( 'Widget title', 'rl_conductor' ),
			'param_name' => 'title',
			'description' => __( 'Enter text used as widget title (Note: located above content element).', 'rl_conductor' ),
			'admin_label' => true,
		),
		array(
			'type' => 'textfield',
			'heading' => __( 'Value', 'rl_conductor' ),
			'param_name' => 'value',
			'description' => __( 'Enter value for graph (Note: choose range from 0 to 100).', 'rl_conductor' ),
			'value' => '50',
			'admin_label' => true,
		),
		array(
			'type' => 'textfield',
			'heading' => __( 'Label value', 'rl_conductor' ),
			'param_name' => 'label_value',
			'description' => __( 'Enter label for pie chart (Note: leaving empty will set value from "Value" field).', 'rl_conductor' ),
			'value' => '',
		),
		array(
			'type' => 'textfield',
			'heading' => __( 'Units', 'rl_conductor' ),
			'param_name' => 'units',
			'description' => __( 'Enter measurement units (Example: %, px, points, etc. Note: graph value and units will be appended to graph title).', 'rl_conductor' ),
		),
		array(
			'type' => 'dropdown',
			'heading' => __( 'Color', 'rl_conductor' ),
			'param_name' => 'color',
			'value' => getVcShared( 'colors-dashed' ) + array( __( 'Custom', 'rl_conductor' ) => 'custom' ),
			'description' => __( 'Select pie chart color.', 'rl_conductor' ),
			'admin_label' => true,
			'param_holder_class' => 'vc_colored-dropdown',
			'std' => 'grey',
		),
		array(
			'type' => 'colorpicker',
			'heading' => __( 'Custom color', 'rl_conductor' ),
			'param_name' => 'custom_color',
			'description' => __( 'Select custom color.', 'rl_conductor' ),
			'dependency' => array(
				'element' => 'color',
				'value' => array( 'custom' ),
			),
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
