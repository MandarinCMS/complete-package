<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

/**
 * @since 4.4 vendors initialization moved to hooks in autoload/vendors.
 *
 * Used to initialize advanced custom fields vendor.
 */
add_action( 'acf/init', 'vc_init_vendor_acf' ); // pro version
add_action( 'acf/register_fields', 'vc_init_vendor_acf' ); // free version
add_action( 'modules_loaded', 'vc_init_vendor_acf' );
add_action( 'after_setup_myskin', 'vc_init_vendor_acf' ); // for myskins
function vc_init_vendor_acf() {
	if ( did_action( 'vc-vendor-acf-load' ) ) {
		return;
	}
	include_once( BASED_TREE_URI . 'mcms-admin/includes/module.php' ); // Require module.php to use is_module_active() below
	if ( class_exists( 'acf' ) || is_module_active( 'advanced-custom-fields/acf.php' ) || is_module_active( 'advanced-custom-fields-pro/acf.php' ) ) {
		require_once vc_path_dir( 'VENDORS_DIR',
			'modules/class-vc-vendor-advanced-custom-fields.php' );
		$vendor = new Vc_Vendor_AdvancedCustomFields();
		add_action( 'vc_after_set_mode',
			array(
				$vendor,
				'load',
			) );
	}
}
