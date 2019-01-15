<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

/**
 * @since 4.4 vendors initialization moved to hooks in autoload/vendors.
 *
 * Used to initialize module yoast vendor.
 */
// 16 is required to be called after MCMSSEO_Admin_Init constructor. @since 4.9
add_action( 'modules_loaded', 'vc_init_vendor_yoast', 16 );
// add_action( 'modules_loaded', 'vc_init_vendor_yoast_reset_page_now', 16 );
function vc_init_vendor_yoast() {
	include_once( BASED_TREE_URI . 'mcms-admin/includes/module.php' ); // Require module.php to use is_module_active() below
	if ( is_module_active( 'mandarincms-seo/mcms-seo.php' ) || class_exists( 'MCMSSEO_Metabox' ) ) {
		require_once vc_path_dir( 'VENDORS_DIR', 'modules/class-vc-vendor-yoast_seo.php' );
		$vendor = new Vc_Vendor_YoastSeo();
		if ( defined( 'MCMSSEO_VERSION' ) && version_compare( MCMSSEO_VERSION, '3.0.0' ) === - 1 ) {
			add_action( 'vc_after_set_mode', array(
				$vendor,
				'load',
			) );
		} elseif ( is_admin() && 'vc_inline' === vc_action() ) {
			// $GLOBALS['pagenow'] = 'post.php?vc_action=vc_inline';
			$vendor->frontendEditorBuild();
		}
	}
}
/*function vc_init_vendor_yoast_reset_page_now() {
	$GLOBALS['pagenow'] = 'post.php';

}*/
