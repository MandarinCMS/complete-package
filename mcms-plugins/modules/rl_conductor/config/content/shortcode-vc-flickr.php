<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

return array(
	'base' => 'vc_flickr',
	'name' => __( 'Flickr Widget', 'rl_conductor' ),
	'icon' => 'icon-mcmsb-flickr',
	'category' => __( 'Content', 'rl_conductor' ),
	'description' => __( 'Image feed from Flickr account', 'rl_conductor' ),
	'params' => array(
		array(
			'type' => 'textfield',
			'heading' => __( 'Widget title', 'rl_conductor' ),
			'param_name' => 'title',
			'description' => __( 'Enter text used as widget title (Note: located above content element).', 'rl_conductor' ),
		),
		array(
			'type' => 'textfield',
			'heading' => __( 'Flickr ID', 'rl_conductor' ),
			'param_name' => 'flickr_id',
			'value' => '95572727@N00',
			'admin_label' => true,
			'description' => sprintf( __( 'To find your flickID visit %s.', 'rl_conductor' ), '<a href="http://idgettr.com/" target="_blank">idGettr</a>' ),
		),
		array(
			'type' => 'dropdown',
			'heading' => __( 'Number of photos', 'rl_conductor' ),
			'param_name' => 'count',
			'value' => array(
				9,
				8,
				7,
				6,
				5,
				4,
				3,
				2,
				1,
			),
			'description' => __( 'Select number of photos to display.', 'rl_conductor' ),
		),
		array(
			'type' => 'dropdown',
			'heading' => __( 'Type', 'rl_conductor' ),
			'param_name' => 'type',
			'value' => array(
				__( 'User', 'rl_conductor' ) => 'user',
				__( 'Group', 'rl_conductor' ) => 'group',
			),
			'description' => __( 'Select photo stream type.', 'rl_conductor' ),
		),
		array(
			'type' => 'dropdown',
			'heading' => __( 'Display order', 'rl_conductor' ),
			'param_name' => 'display',
			'value' => array(
				__( 'Latest first', 'rl_conductor' ) => 'latest',
				__( 'Random', 'rl_conductor' ) => 'random',
			),
			'description' => __( 'Select photo display order.', 'rl_conductor' ),
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
