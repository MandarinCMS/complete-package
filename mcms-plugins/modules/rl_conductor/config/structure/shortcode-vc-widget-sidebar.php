<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

return array(
	'name' => __( 'Widgetised Sidebar', 'rl_conductor' ),
	'base' => 'vc_widget_sidebar',
	'class' => 'mcmsb_widget_sidebar_widget',
	'icon' => 'icon-mcmsb-layout_sidebar',
	'category' => __( 'Structure', 'rl_conductor' ),
	'description' => __( 'MandarinCMS widgetised sidebar', 'rl_conductor' ),
	'params' => array(
		array(
			'type' => 'textfield',
			'heading' => __( 'Widget title', 'rl_conductor' ),
			'param_name' => 'title',
			'description' => __( 'Enter text used as widget title (Note: located above content element).', 'rl_conductor' ),
			'admin_label' => true,
		),
		array(
			'type' => 'widgetised_sidebars',
			'heading' => __( 'Sidebar', 'rl_conductor' ),
			'param_name' => 'sidebar_id',
			'description' => __( 'Select widget area to display.', 'rl_conductor' ),
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
