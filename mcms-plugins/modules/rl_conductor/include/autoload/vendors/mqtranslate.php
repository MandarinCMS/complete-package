<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

/**
 * @since 4.4 vendors initialization moved to hooks in autoload/vendors.
 *
 * Used to initialize module mqtranslate vendor
 */
add_action( 'modules_loaded', 'vc_init_vendor_mqtranslate' );
function vc_init_vendor_mqtranslate() {
	include_once( BASED_TREE_URI . 'mcms-admin/includes/module.php' ); // Require module.php to use is_module_active() below
	if ( is_module_active( 'mqtranslate/mqtranslate.php' ) || function_exists( 'mqtranslate_activation_check' ) ) {
		require_once vc_path_dir( 'VENDORS_DIR', 'modules/class-vc-vendor-mqtranslate.php' );
		$vendor = new Vc_Vendor_Mqtranslate();
		add_action( 'vc_after_set_mode', array(
			$vendor,
			'load',
		) );
	}
}
