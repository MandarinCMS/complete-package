<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

return array(
	'name' => __( 'Line Chart', 'rl_conductor' ),
	'base' => 'vc_line_chart',
	'class' => '',
	'icon' => 'icon-mcmsb-vc-line-chart',
	'category' => __( 'Content', 'rl_conductor' ),
	'description' => __( 'Line and Bar charts', 'rl_conductor' ),
	'params' => array(
		array(
			'type' => 'textfield',
			'heading' => __( 'Widget title', 'rl_conductor' ),
			'param_name' => 'title',
			'description' => __( 'Enter text used as widget title (Note: located above content element).', 'rl_conductor' ),
			'admin_label' => true,
		),
		array(
			'type' => 'dropdown',
			'heading' => __( 'Design', 'rl_conductor' ),
			'param_name' => 'type',
			'value' => array(
				__( 'Line', 'rl_conductor' ) => 'line',
				__( 'Bar', 'rl_conductor' ) => 'bar',
			),
			'std' => 'bar',
			'description' => __( 'Select type of chart.', 'rl_conductor' ),
			'admin_label' => true,
		),
		array(
			'type' => 'dropdown',
			'heading' => __( 'Style', 'rl_conductor' ),
			'description' => __( 'Select chart color style.', 'rl_conductor' ),
			'param_name' => 'style',
			'value' => array(
				__( 'Flat', 'rl_conductor' ) => 'flat',
				__( 'Modern', 'rl_conductor' ) => 'modern',
				__( 'Custom', 'rl_conductor' ) => 'custom',
			),
			'dependency' => array(
				'callback' => 'vcChartCustomColorDependency',
			),
		),
		array(
			'type' => 'checkbox',
			'heading' => __( 'Show legend?', 'rl_conductor' ),
			'param_name' => 'legend',
			'description' => __( 'If checked, chart will have legend.', 'rl_conductor' ),
			'value' => array( __( 'Yes', 'rl_conductor' ) => 'yes' ),
			'std' => 'yes',
		),
		array(
			'type' => 'checkbox',
			'heading' => __( 'Show hover values?', 'rl_conductor' ),
			'param_name' => 'tooltips',
			'description' => __( 'If checked, chart will show values on hover.', 'rl_conductor' ),
			'value' => array( __( 'Yes', 'rl_conductor' ) => 'yes' ),
			'std' => 'yes',
		),
		array(
			'type' => 'textfield',
			'heading' => __( 'X-axis values', 'rl_conductor' ),
			'param_name' => 'x_values',
			'description' => __( 'Enter values for axis (Note: separate values with ";").', 'rl_conductor' ),
			'value' => 'JAN; FEB; MAR; APR; MAY; JUN; JUL; AUG',
		),
		array(
			'type' => 'param_group',
			'heading' => __( 'Values', 'rl_conductor' ),
			'param_name' => 'values',
			'value' => urlencode( json_encode( array(
				array(
					'title' => __( 'One', 'rl_conductor' ),
					'y_values' => '10; 15; 20; 25; 27; 25; 23; 25',
					'color' => 'blue',
				),
				array(
					'title' => __( 'Two', 'rl_conductor' ),
					'y_values' => '25; 18; 16; 17; 20; 25; 30; 35',
					'color' => 'pink',
				),
			) ) ),
			'params' => array(
				array(
					'type' => 'textfield',
					'heading' => __( 'Title', 'rl_conductor' ),
					'param_name' => 'title',
					'description' => __( 'Enter title for chart dataset.', 'rl_conductor' ),
					'admin_label' => true,
				),
				array(
					'type' => 'textfield',
					'heading' => __( 'Y-axis values', 'rl_conductor' ),
					'param_name' => 'y_values',
					'description' => __( 'Enter values for axis (Note: separate values with ";").', 'rl_conductor' ),
				),
				array(
					'type' => 'dropdown',
					'heading' => __( 'Color', 'rl_conductor' ),
					'param_name' => 'color',
					'value' => getVcShared( 'colors-dashed' ),
					'description' => __( 'Select chart color.', 'rl_conductor' ),
					'param_holder_class' => 'vc_colored-dropdown',
				),
				array(
					'type' => 'colorpicker',
					'heading' => __( 'Custom color', 'rl_conductor' ),
					'param_name' => 'custom_color',
					'description' => __( 'Select custom chart color.', 'rl_conductor' ),
				),
			),
			'callbacks' => array(
				'after_add' => 'vcChartParamAfterAddCallback',
			),
		),
		array(
			'type' => 'dropdown',
			'heading' => __( 'Animation', 'rl_conductor' ),
			'description' => __( 'Select animation style.', 'rl_conductor' ),
			'param_name' => 'animation',
			'value' => getVcShared( 'animation styles' ),
			'std' => 'easeinOutCubic',
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
