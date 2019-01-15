<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

/**
 * @var $tag - shortcode tag;
 */
return array(
	'name' => __( 'Column', 'rl_conductor' ),
	'icon' => 'icon-mcmsb-row',
	'is_container' => true,
	'content_element' => false,
	'description' => __( 'Place content elements inside the column', 'rl_conductor' ),
	'params' => array(
		vc_map_add_css_animation( false ),
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
		array(
			'type' => 'dropdown',
			'heading' => __( 'Width', 'rl_conductor' ),
			'param_name' => 'width',
			'value' => array(
				__( '1 column - 1/12', 'rl_conductor' ) => '1/12',
				__( '2 columns - 1/6', 'rl_conductor' ) => '1/6',
				__( '3 columns - 1/4', 'rl_conductor' ) => '1/4',
				__( '4 columns - 1/3', 'rl_conductor' ) => '1/3',
				__( '5 columns - 5/12', 'rl_conductor' ) => '5/12',
				__( '6 columns - 1/2', 'rl_conductor' ) => '1/2',
				__( '7 columns - 7/12', 'rl_conductor' ) => '7/12',
				__( '8 columns - 2/3', 'rl_conductor' ) => '2/3',
				__( '9 columns - 3/4', 'rl_conductor' ) => '3/4',
				__( '10 columns - 5/6', 'rl_conductor' ) => '5/6',
				__( '11 columns - 11/12', 'rl_conductor' ) => '11/12',
				__( '12 columns - 1/1', 'rl_conductor' ) => '1/1',
			),
			'group' => __( 'Responsive Options', 'rl_conductor' ),
			'description' => __( 'Select column width.', 'rl_conductor' ),
			'std' => '1/1',
		),
		array(
			'type' => 'column_offset',
			'heading' => __( 'Responsiveness', 'rl_conductor' ),
			'param_name' => 'offset',
			'group' => __( 'Responsive Options', 'rl_conductor' ),
			'description' => __( 'Adjust column for different screen sizes. Control width, offset and visibility settings.', 'rl_conductor' ),
		),
	),
	'js_view' => 'VcColumnView',
);
