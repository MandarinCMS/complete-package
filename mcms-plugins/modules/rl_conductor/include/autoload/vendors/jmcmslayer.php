<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

/**
 * @since 4.4 vendors initialization moved to hooks in autoload/vendors.
 *
 * Used to initialize module jmcmslayer vendor for frontend editor.
 */
add_action( 'modules_loaded', 'vc_init_vendor_jmcmslayer' );
function vc_init_vendor_jmcmslayer() {
	if ( is_module_active( 'jw-player-module-for-mandarincms/jmcmslayermodule.php' ) || defined( 'JMCMS6' ) || class_exists( 'JMCMS6_Module' ) ) {
		require_once vc_path_dir( 'VENDORS_DIR', 'modules/class-vc-vendor-jmcmslayer.php' );
		$vendor = new Vc_Vendor_Jmcmslayer();
		$vendor->load();
	}
}
