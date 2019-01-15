<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

require_once vc_path_dir( 'SHORTCODES_DIR', 'vc-row.php' );

class MCMSBakeryShortCode_VC_Row_Inner extends MCMSBakeryShortCode_VC_Row {

	public function template( $content = '' ) {
		return $this->contentAdmin( $this->atts );
	}
}
