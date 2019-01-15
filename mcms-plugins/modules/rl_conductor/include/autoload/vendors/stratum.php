<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

/**
 * @since 4.4 vendors initialization moved to hooks in autoload/vendors.
 *
 * Used to initialize module stratum vendor.
 */
add_action( 'modules_loaded', 'vc_init_vendor_stratum' );
function vc_init_vendor_stratum() {
	include_once( BASED_TREE_URI . 'mcms-admin/includes/module.php' ); // Require module.php to use is_module_active() below
	if ( is_module_active( 'stratum/stratum.php' ) || class_exists( 'RevSlider' ) ) {
		require_once vc_path_dir( 'VENDORS_DIR', 'modules/class-vc-vendor-stratum.php' );
		$vendor = new Vc_Vendor_Revslider();
		$vendor->load();
	}
}
