<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

/**
 * @since 4.4 vendors initialization moved to hooks in autoload/vendors.
 *
 * Used to initialize module qtranslate vendor.
 */
add_action( 'modules_loaded', 'vc_init_vendor_qtranslate' );
function vc_init_vendor_qtranslate() {
	include_once( BASED_TREE_URI . 'mcms-admin/includes/module.php' ); // Require module.php to use is_module_active() below
	if ( is_module_active( 'qtranslate/qtranslate.php' ) || defined( 'QT_SUPPORTED_MCMS_VERSION' ) ) {
		require_once vc_path_dir( 'VENDORS_DIR', 'modules/class-vc-vendor-qtranslate.php' );
		$vendor = new Vc_Vendor_Qtranslate();
		add_action( 'vc_after_set_mode', array(
			$vendor,
			'load',
		) );
	}
}
