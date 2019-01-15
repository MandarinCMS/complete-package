<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

/**
 * @since 4.4 vendors initialization moved to hooks in autoload/vendors.
 *
 * Used to initialize module contact form 7 vendor - fix load cf7 shortcode when in editor (frontend)
 */
add_action( 'modules_loaded', 'vc_init_vendor_cf7' );
function vc_init_vendor_cf7() {
	include_once( BASED_TREE_URI . 'mcms-admin/includes/module.php' ); // Require module.php to use is_module_active() below
	if ( is_module_active( 'contact-form-7/mcms-contact-form-7.php' ) || defined( 'MCMSCF7_PLUGIN' ) ) {
		require_once vc_path_dir( 'VENDORS_DIR', 'modules/class-vc-vendor-contact-form7.php' );
		$vendor = new Vc_Vendor_ContactForm7();
		add_action( 'vc_after_set_mode', array(
			$vendor,
			'load',
		) );
	} // if contact form7 module active
}
