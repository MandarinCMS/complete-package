<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

return array(
	'name' => __( 'Round Chart', 'rl_conductor' ),
	'base' => 'vc_round_chart',
	'class' => '',
	'icon' => 'icon-mcmsb-vc-round-chart',
	'category' => __( 'Content', 'rl_conductor' ),
	'description' => __( 'Pie and Doughnat charts', 'rl_conductor' ),
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
				__( 'Pie', 'rl_conductor' ) => 'pie',
				__( 'Doughnut', 'rl_conductor' ) => 'doughnut',
			),
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
			'type' => 'dropdown',
			'heading' => __( 'Gap', 'rl_conductor' ),
			'param_name' => 'stroke_width',
			'value' => array(
				0 => 0,
				1 => 1,
				2 => 2,
				5 => 5,
			),
			'description' => __( 'Select gap size.', 'rl_conductor' ),
			'std' => 2,
		),
		array(
			'type' => 'dropdown',
			'heading' => __( 'Outline color', 'rl_conductor' ),
			'param_name' => 'stroke_color',
			'value' => getVcShared( 'colors-dashed' ) + array( __( 'Custom', 'rl_conductor' ) => 'custom' ),
			'description' => __( 'Select outline color.', 'rl_conductor' ),
			'param_holder_class' => 'vc_colored-dropdown',
			'std' => 'white',
			'dependency' => array(
				'element' => 'stroke_width',
				'value_not_equal_to' => '0',
			),
		),
		array(
			'type' => 'colorpicker',
			'heading' => __( 'Custom outline color', 'rl_conductor' ),
			'param_name' => 'custom_stroke_color',
			'description' => __( 'Select custom outline color.', 'rl_conductor' ),
			'dependency' => array(
				'element' => 'stroke_color',
				'value' => array( 'custom' ),
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
			'type' => 'param_group',
			'heading' => __( 'Values', 'rl_conductor' ),
			'param_name' => 'values',
			'value' => urlencode( json_encode( array(
				array(
					'title' => __( 'One', 'rl_conductor' ),
					'value' => '60',
					'color' => 'blue',
				),
				array(
					'title' => __( 'Two', 'rl_conductor' ),
					'value' => '40',
					'color' => 'pink',
				),
			) ) ),
			'params' => array(
				array(
					'type' => 'textfield',
					'heading' => __( 'Title', 'rl_conductor' ),
					'param_name' => 'title',
					'description' => __( 'Enter title for chart area.', 'rl_conductor' ),
					'admin_label' => true,
				),
				array(
					'type' => 'textfield',
					'heading' => __( 'Value', 'rl_conductor' ),
					'param_name' => 'value',
					'description' => __( 'Enter value for area.', 'rl_conductor' ),
				),
				array(
					'type' => 'dropdown',
					'heading' => __( 'Color', 'rl_conductor' ),
					'param_name' => 'color',
					'value' => getVcShared( 'colors-dashed' ),
					'description' => __( 'Select area color.', 'rl_conductor' ),
					'param_holder_class' => 'vc_colored-dropdown',
				),
				array(
					'type' => 'colorpicker',
					'heading' => __( 'Custom color', 'rl_conductor' ),
					'param_name' => 'custom_color',
					'description' => __( 'Select custom area color.', 'rl_conductor' ),
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
