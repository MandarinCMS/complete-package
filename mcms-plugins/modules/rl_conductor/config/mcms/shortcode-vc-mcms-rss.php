<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

return array(
	'name' => 'MCMS ' . __( 'RSS' ),
	'base' => 'vc_mcms_rss',
	'icon' => 'icon-mcmsb-mcms',
	'category' => __( 'MandarinCMS Widgets', 'rl_conductor' ),
	'class' => 'mcmsb_vc_mcms_widget',
	'weight' => - 50,
	'description' => __( 'Entries from any RSS or Atom feed', 'rl_conductor' ),
	'params' => array(
		array(
			'type' => 'textfield',
			'heading' => __( 'Widget title', 'rl_conductor' ),
			'param_name' => 'title',
			'description' => __( 'What text use as a widget title. Leave blank to use default widget title.', 'rl_conductor' ),
		),
		array(
			'type' => 'textfield',
			'heading' => __( 'RSS feed URL', 'rl_conductor' ),
			'param_name' => 'url',
			'description' => __( 'Enter the RSS feed URL.', 'rl_conductor' ),
			'admin_label' => true,
		),
		array(
			'type' => 'dropdown',
			'heading' => __( 'Items', 'rl_conductor' ),
			'param_name' => 'items',
			'value' => array(
				__( '10 - Default', 'rl_conductor' ) => 10,
				1,
				2,
				3,
				4,
				5,
				6,
				7,
				8,
				9,
				10,
				11,
				12,
				13,
				14,
				15,
				16,
				17,
				18,
				19,
				20,
			),
			'description' => __( 'Select how many items to display.', 'rl_conductor' ),
			'admin_label' => true,
		),
		array(
			'type' => 'checkbox',
			'heading' => __( 'Options', 'rl_conductor' ),
			'param_name' => 'options',
			'value' => array(
				__( 'Item content', 'rl_conductor' ) => 'show_summary',
				__( 'Display item author if available?', 'rl_conductor' ) => 'show_author',
				__( 'Display item date?', 'rl_conductor' ) => 'show_date',
			),
			'description' => __( 'Select display options for RSS feeds.', 'rl_conductor' ),
		),
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
	),
);
