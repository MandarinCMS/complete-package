<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

/**
 * @since 4.4 vendors initialization moved to hooks in autoload/vendors.
 *
 * Used to initialize module layerslider vendor.
 */
add_action( 'modules_loaded', 'vc_init_vendor_layerslider' );
function vc_init_vendor_layerslider() {
	include_once( BASED_TREE_URI . 'mcms-admin/includes/module.php' ); // Require module.php to use is_module_active() below
	if ( is_module_active( 'LayerSlider/layerslider.php' ) || class_exists( 'LS_Sliders' ) || defined( 'LS_ROOT_PATH' ) ) {
		require_once vc_path_dir( 'VENDORS_DIR', 'modules/class-vc-vendor-layerslider.php' );
		$vendor = new Vc_Vendor_Layerslider();
		$vendor->load();
	}
}
