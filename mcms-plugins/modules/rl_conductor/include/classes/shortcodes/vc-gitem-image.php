<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

require_once vc_path_dir( 'SHORTCODES_DIR', 'vc-custom-heading.php' );

class MCMSBakeryShortCode_VC_Gitem_Image extends MCMSBakeryShortCode_VC_Gitem_Post_Data {
	/**
	 * Get data_source attribute value
	 *
	 * @param array $atts - list of shortcode attributes
	 *
	 * @return string
	 */
	public function getDataSource( array $atts ) {
		return 'post_image';
	}
}
