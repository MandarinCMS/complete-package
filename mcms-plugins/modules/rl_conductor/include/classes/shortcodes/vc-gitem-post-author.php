<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

require_once vc_path_dir( 'SHORTCODES_DIR', 'vc-gitem-post-data.php' );

class MCMSBakeryShortCode_VC_Gitem_Post_Author extends MCMSBakeryShortCode_VC_Gitem_Post_Data {
	protected function getFileName() {
		return 'vc_gitem_post_author';
	}
}
