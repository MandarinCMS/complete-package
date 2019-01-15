<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

add_action( 'vc_activation_hook', 'vc_bc_multisite_options', 9 );

function vc_bc_multisite_options( $networkWide ) {
	global $current_site;
	if ( ! is_multisite() || empty( $current_site ) || ! $networkWide || get_site_option( 'vc_bc_options_called', false ) || get_site_option( 'mcmsb_js_rl_conductor_purchase_code', false ) ) {
		return;
	}
	// Now we need to check BC with license keys
	$is_main_blog_activated = get_blog_option( (int) $current_site->id, 'mcmsb_js_rl_conductor_purchase_code' );
	if ( $is_main_blog_activated ) {
		update_site_option( 'mcmsb_js_rl_conductor_purchase_code', $is_main_blog_activated );
	}
	update_site_option( 'vc_bc_options_called', true );
}
