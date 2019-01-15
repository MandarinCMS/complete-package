<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

return array(
	'name' => __( 'Google+ Button', 'rl_conductor' ),
	'base' => 'vc_googleplus',
	'icon' => 'icon-mcmsb-application-plus',
	'category' => __( 'Social', 'rl_conductor' ),
	'description' => __( 'Recommend on Google', 'rl_conductor' ),
	'params' => array(
		array(
			'type' => 'dropdown',
			'heading' => __( 'Button size', 'rl_conductor' ),
			'param_name' => 'type',
			'admin_label' => true,
			'value' => array(
				__( 'Standard', 'rl_conductor' ) => 'standard',
				__( 'Small', 'rl_conductor' ) => 'small',
				__( 'Medium', 'rl_conductor' ) => 'medium',
				__( 'Tall', 'rl_conductor' ) => 'tall',
			),
			'description' => __( 'Select button size.', 'rl_conductor' ),
		),
		array(
			'type' => 'dropdown',
			'heading' => __( 'Annotation', 'rl_conductor' ),
			'param_name' => 'annotation',
			'admin_label' => true,
			'value' => array(
				__( 'Bubble', 'rl_conductor' ) => 'bubble',
				__( 'Inline', 'rl_conductor' ) => 'inline',
				__( 'None', 'rl_conductor' ) => 'none',
			),
			'std' => 'bubble',
			'description' => __( 'Select type of annotation.', 'rl_conductor' ),
		),
		array(
			'type' => 'textfield',
			'heading' => __( 'Width', 'rl_conductor' ),
			'param_name' => 'widget_width',
			'dependency' => array(
				'element' => 'annotation',
				'value' => array( 'inline' ),
			),
			'description' => __( 'Minimum width of 120px to display. If annotation is set to "inline", this parameter sets the width in pixels to use for button and its inline annotation. Default width is 450px.', 'rl_conductor' ),
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
