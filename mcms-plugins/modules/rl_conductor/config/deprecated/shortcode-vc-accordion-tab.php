<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

return array(
	'name' => __( 'Old Section', 'rl_conductor' ),
	'base' => 'vc_accordion_tab',
	'allowed_container_element' => 'vc_row',
	'is_container' => true,
	'deprecated' => '4.6',
	'content_element' => false,
	'params' => array(
		array(
			'type' => 'textfield',
			'heading' => __( 'Title', 'rl_conductor' ),
			'param_name' => 'title',
			'value' => __( 'Section', 'rl_conductor' ),
			'description' => __( 'Enter accordion section title.', 'rl_conductor' ),
		),
		array(
			'type' => 'el_id',
			'heading' => __( 'Section ID', 'rl_conductor' ),
			'param_name' => 'el_id',
			'description' => sprintf( __( 'Enter optional row ID. Make sure it is unique, and it is valid as w3c specification: %s (Must not have spaces)', 'rl_conductor' ), '<a target="_blank" href="http://www.w3schools.com/tags/att_global_id.asp">' . __( 'link', 'rl_conductor' ) . '</a>' ),
		),
	),
	'js_view' => 'VcAccordionTabView',
);
