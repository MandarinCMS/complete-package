<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

require_once vc_path_dir( 'SHORTCODES_DIR', 'vc-tabs.php' );

define( 'SLIDE_TITLE', __( 'Slide', 'rl_conductor' ) );

class MCMSBakeryShortCode_VC_Tour extends MCMSBakeryShortCode_VC_Tabs {
	protected $predefined_atts = array(
		'tab_id' => SLIDE_TITLE,
		'title' => '',
	);

	protected function getFileName() {
		return 'vc_tabs';
	}

	public function getTabTemplate() {
		return '<div class="mcmsb_template">' . do_shortcode( '[vc_tab title="' . SLIDE_TITLE . '" tab_id=""][/vc_tab]' ) . '</div>';
	}
}
