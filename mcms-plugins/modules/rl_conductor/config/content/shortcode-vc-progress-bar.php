<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

return array(
	'name' => __( 'Progress Bar', 'rl_conductor' ),
	'base' => 'vc_progress_bar',
	'icon' => 'icon-mcmsb-graph',
	'category' => __( 'Content', 'rl_conductor' ),
	'description' => __( 'Animated progress bar', 'rl_conductor' ),
	'params' => array(
		array(
			'type' => 'textfield',
			'heading' => __( 'Widget title', 'rl_conductor' ),
			'param_name' => 'title',
			'description' => __( 'Enter text used as widget title (Note: located above content element).', 'rl_conductor' ),
		),
		array(
			'type' => 'param_group',
			'heading' => __( 'Values', 'rl_conductor' ),
			'param_name' => 'values',
			'description' => __( 'Enter values for graph - value, title and color.', 'rl_conductor' ),
			'value' => urlencode( json_encode( array(
				array(
					'label' => __( 'Development', 'rl_conductor' ),
					'value' => '90',
				),
				array(
					'label' => __( 'Design', 'rl_conductor' ),
					'value' => '80',
				),
				array(
					'label' => __( 'Marketing', 'rl_conductor' ),
					'value' => '70',
				),
			) ) ),
			'params' => array(
				array(
					'type' => 'textfield',
					'heading' => __( 'Label', 'rl_conductor' ),
					'param_name' => 'label',
					'description' => __( 'Enter text used as title of bar.', 'rl_conductor' ),
					'admin_label' => true,
				),
				array(
					'type' => 'textfield',
					'heading' => __( 'Value', 'rl_conductor' ),
					'param_name' => 'value',
					'description' => __( 'Enter value of bar.', 'rl_conductor' ),
					'admin_label' => true,
				),
				array(
					'type' => 'dropdown',
					'heading' => __( 'Color', 'rl_conductor' ),
					'param_name' => 'color',
					'value' => array(
							__( 'Default', 'rl_conductor' ) => '',
						) + array(
							__( 'Classic Grey', 'rl_conductor' ) => 'bar_grey',
							__( 'Classic Blue', 'rl_conductor' ) => 'bar_blue',
							__( 'Classic Turquoise', 'rl_conductor' ) => 'bar_turquoise',
							__( 'Classic Green', 'rl_conductor' ) => 'bar_green',
							__( 'Classic Orange', 'rl_conductor' ) => 'bar_orange',
							__( 'Classic Red', 'rl_conductor' ) => 'bar_red',
							__( 'Classic Black', 'rl_conductor' ) => 'bar_black',
						) + getVcShared( 'colors-dashed' ) + array(
							__( 'Custom Color', 'rl_conductor' ) => 'custom',
						),
					'description' => __( 'Select single bar background color.', 'rl_conductor' ),
					'admin_label' => true,
					'param_holder_class' => 'vc_colored-dropdown',
				),
				array(
					'type' => 'colorpicker',
					'heading' => __( 'Custom color', 'rl_conductor' ),
					'param_name' => 'customcolor',
					'description' => __( 'Select custom single bar background color.', 'rl_conductor' ),
					'dependency' => array(
						'element' => 'color',
						'value' => array( 'custom' ),
					),
				),
				array(
					'type' => 'colorpicker',
					'heading' => __( 'Custom text color', 'rl_conductor' ),
					'param_name' => 'customtxtcolor',
					'description' => __( 'Select custom single bar text color.', 'rl_conductor' ),
					'dependency' => array(
						'element' => 'color',
						'value' => array( 'custom' ),
					),
				),
			),
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
			'param_name' => 'bgcolor',
			'value' => array(
					__( 'Classic Grey', 'rl_conductor' ) => 'bar_grey',
					__( 'Classic Blue', 'rl_conductor' ) => 'bar_blue',
					__( 'Classic Turquoise', 'rl_conductor' ) => 'bar_turquoise',
					__( 'Classic Green', 'rl_conductor' ) => 'bar_green',
					__( 'Classic Orange', 'rl_conductor' ) => 'bar_orange',
					__( 'Classic Red', 'rl_conductor' ) => 'bar_red',
					__( 'Classic Black', 'rl_conductor' ) => 'bar_black',
				) + getVcShared( 'colors-dashed' ) + array(
					__( 'Custom Color', 'rl_conductor' ) => 'custom',
				),
			'description' => __( 'Select bar background color.', 'rl_conductor' ),
			'admin_label' => true,
			'param_holder_class' => 'vc_colored-dropdown',
		),
		array(
			'type' => 'colorpicker',
			'heading' => __( 'Bar custom background color', 'rl_conductor' ),
			'param_name' => 'custombgcolor',
			'description' => __( 'Select custom background color for bars.', 'rl_conductor' ),
			'dependency' => array(
				'element' => 'bgcolor',
				'value' => array( 'custom' ),
			),
		),
		array(
			'type' => 'colorpicker',
			'heading' => __( 'Bar custom text color', 'rl_conductor' ),
			'param_name' => 'customtxtcolor',
			'description' => __( 'Select custom text color for bars.', 'rl_conductor' ),
			'dependency' => array(
				'element' => 'bgcolor',
				'value' => array( 'custom' ),
			),
		),
		array(
			'type' => 'checkbox',
			'heading' => __( 'Options', 'rl_conductor' ),
			'param_name' => 'options',
			'value' => array(
				__( 'Add stripes', 'rl_conductor' ) => 'striped',
				__( 'Add animation (Note: visible only with striped bar).', 'rl_conductor' ) => 'animated',
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
