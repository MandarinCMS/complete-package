<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

/**
 * @since 4.4 vendors initialization moved to hooks in autoload/vendors.
 *
 * Used to initialize module qtranslate vendor.
 */
add_action( 'modules_loaded', 'vc_init_vendor_qtranslatex' );
function vc_init_vendor_qtranslatex() {
	if ( defined( 'QTX_VERSION' ) ) {
		require_once vc_path_dir( 'VENDORS_DIR', 'modules/class-vc-vendor-qtranslate-x.php' );
		$vendor = new Vc_Vendor_QtranslateX();
		add_action( 'vc_after_set_mode', array(
			$vendor,
			'load',
		) );
	}
}
