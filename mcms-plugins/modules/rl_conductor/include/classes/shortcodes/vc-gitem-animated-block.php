<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

require_once vc_path_dir( 'SHORTCODES_DIR', 'vc-gitem.php' );

class MCMSBakeryShortCode_VC_Gitem_Animated_Block extends MCMSBakeryShortCode_VC_Gitem {
	protected static $animations = array();

	public function itemGrid() {
		$output = '';
		$output .= '<div class="vc_gitem-animated-block-content-controls">'
		           . '<ul class="vc_gitem-tabs vc_clearfix" data-vc-gitem-animated-block="tabs">'
		           . '</ul>'
		           . '</div>';
		$output .= ''
		           . '<div class="vc_gitem-zone-tab vc_clearfix" data-vc-gitem-animated-block="add-a"></div>'
		           . '<div class="vc_gitem-zone-tab vc_clearfix" data-vc-gitem-animated-block="add-b"></div>';

		return $output;
	}

	public function containerHtmlBlockParams( $width, $i ) {
		return 'class="vc_gitem-animated-block-content"';
	}

	public static function animations() {
		return array(
			__( 'Single block (no animation)', 'rl_conductor' ) => '',
			__( 'Double block (no animation)', 'rl_conductor' ) => 'none',
			__( 'Fade in', 'rl_conductor' ) => 'fadeIn',
			__( 'Scale in', 'rl_conductor' ) => 'scaleIn',
			__( 'Scale in with rotation', 'rl_conductor' ) => 'scaleRotateIn',
			__( 'Blur out', 'rl_conductor' ) => 'blurOut',
			__( 'Blur scale out', 'rl_conductor' ) => 'blurScaleOut',
			__( 'Slide in from left', 'rl_conductor' ) => 'slideInRight',
			__( 'Slide in from right', 'rl_conductor' ) => 'slideInLeft',
			__( 'Slide bottom', 'rl_conductor' ) => 'slideBottom',
			__( 'Slide top', 'rl_conductor' ) => 'slideTop',
			__( 'Vertical flip in with fade', 'rl_conductor' ) => 'flipFadeIn',
			__( 'Horizontal flip in with fade', 'rl_conductor' ) => 'flipHorizontalFadeIn',
			__( 'Go top', 'rl_conductor' ) => 'goTop20',
			__( 'Go bottom', 'rl_conductor' ) => 'goBottom20',
		);
	}
}
