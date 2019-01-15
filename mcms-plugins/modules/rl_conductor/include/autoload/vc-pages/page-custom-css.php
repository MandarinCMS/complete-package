<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

function vc_page_settings_custom_css_load() {
	mcms_enqueue_script( 'ace-editor', vc_asset_url( 'lib/bower/ace-builds/src-min-noconflict/ace.js' ), array( 'jquery' ), MCMSB_VC_VERSION, true );
}

add_action( 'vc-settings-render-tab-vc-custom_css', 'vc_page_settings_custom_css_load' );
