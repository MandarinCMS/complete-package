<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

if ( 'vc_edit_form' === vc_post_param( 'action' ) && vc_verify_admin_nonce() ) {
	$link_category = array( __( 'All Links', 'rl_conductor' ) => '' );
	$link_cats = get_terms( 'link_category' );
	if ( is_array( $link_cats ) && ! empty( $link_cats ) ) {
		foreach ( $link_cats as $link_cat ) {
			if ( is_object( $link_cat ) && isset( $link_cat->name, $link_cat->term_id ) ) {
				$link_category[ $link_cat->name ] = $link_cat->term_id;
			}
		}
	}
} else {
	$link_category = array();
}

return array(
	'name' => 'MCMS ' . __( 'Links' ),
	'base' => 'vc_mcms_links',
	'icon' => 'icon-mcmsb-mcms',
	'category' => __( 'MandarinCMS Widgets', 'rl_conductor' ),
	'class' => 'mcmsb_vc_mcms_widget',
	'content_element' => (bool) get_option( 'link_manager_enabled' ),
	'weight' => - 50,
	'description' => __( 'Your blogroll', 'rl_conductor' ),
	'params' => array(
		array(
			'type' => 'dropdown',
			'heading' => __( 'Link Category', 'rl_conductor' ),
			'param_name' => 'category',
			'value' => $link_category,
			'admin_label' => true,
		),
		array(
			'type' => 'dropdown',
			'heading' => __( 'Order by', 'rl_conductor' ),
			'param_name' => 'orderby',
			'value' => array(
				__( 'Link title', 'rl_conductor' ) => 'name',
				__( 'Link rating', 'rl_conductor' ) => 'rating',
				__( 'Link ID', 'rl_conductor' ) => 'id',
				__( 'Random', 'rl_conductor' ) => 'rand',
			),
		),
		array(
			'type' => 'checkbox',
			'heading' => __( 'Options', 'rl_conductor' ),
			'param_name' => 'options',
			'value' => array(
				__( 'Show Link Image', 'rl_conductor' ) => 'images',
				__( 'Show Link Name', 'rl_conductor' ) => 'name',
				__( 'Show Link Description', 'rl_conductor' ) => 'description',
				__( 'Show Link Rating', 'rl_conductor' ) => 'rating',
			),
		),
		array(
			'type' => 'textfield',
			'heading' => __( 'Number of links to show', 'rl_conductor' ),
			'param_name' => 'limit',
			'value' => - 1,
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
