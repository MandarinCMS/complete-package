<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

/**
 * @since 4.4 vendors initialization moved to hooks in autoload/vendors.
 */
// Remove scripts from the RazorLeaf Conductor while in the Customizer = Temp Fix
// Actually we need to check if this is really needed in 4.4 uncomment if you have customizer issues
// But this actually will break any VC js in Customizer preview.
// removed by fixing vcTabsBevahiour in rl_conductor_front.js
/*
if ( ! function_exists( 'vc_mcmsex_remove_vc_scripts' ) ) {
	function vc_mcmsex_remove_vc_scripts() {
		if ( is_customize_preview() ) {
			mcms_deregister_script( 'mcmsb_composer_front_js' );
			mcms_dequeue_script( 'mcmsb_composer_front_js' );
		}
	}
}*/
//add_action( 'mcms_enqueue_scripts', 'vc_mcmsex_remove_vc_scripts' );
