<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

/**
 * @since 4.4 vendors initialization moved to hooks in autoload/vendors.
 *
 * Used to initialize module ninja forms vendor
 */
add_action( 'modules_loaded', 'vc_init_vendor_ninja_forms' );
function vc_init_vendor_ninja_forms() {
	include_once( BASED_TREE_URI . 'mcms-admin/includes/module.php' ); // Require module.php to use is_module_active() below
	if ( is_module_active( 'ninja-forms/ninja-forms.php' ) || defined( 'NINJA_FORMS_DIR' ) || function_exists( 'ninja_forms_get_all_forms' ) ) {
		require_once vc_path_dir( 'VENDORS_DIR', 'modules/class-vc-vendor-ninja-forms.php' );
		$vendor = new Vc_Vendor_NinjaForms();
		add_action( 'vc_after_set_mode', array(
			$vendor,
			'load',
		) );
	}
}
