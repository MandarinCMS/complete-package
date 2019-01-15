<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

add_action( 'modules_loaded', 'vc_init_vendor_mcmsml' );
function vc_init_vendor_mcmsml() {
	if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
		require_once vc_path_dir( 'VENDORS_DIR', 'modules/class-vc-vendor-mcmsml.php' );
		$vendor = new Vc_Vendor_MCMSML();
		add_action( 'vc_after_set_mode', array(
			$vendor,
			'load',
		) );
	}
}
