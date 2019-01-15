<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

return array(
	'name' => __( 'Old Tab', 'rl_conductor' ),
	'base' => 'vc_tab',
	'allowed_container_element' => 'vc_row',
	'is_container' => true,
	'content_element' => false,
	'deprecated' => '4.6',
	'params' => array(
		array(
			'type' => 'textfield',
			'heading' => __( 'Title', 'rl_conductor' ),
			'param_name' => 'title',
			'description' => __( 'Enter title of tab.', 'rl_conductor' ),
		),
		array(
			'type' => 'tab_id',
			'heading' => __( 'Tab ID', 'rl_conductor' ),
			'param_name' => 'tab_id',
		),
	),
	'js_view' => 'VcTabView',
);
