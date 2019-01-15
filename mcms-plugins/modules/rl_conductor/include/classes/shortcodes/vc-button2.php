<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

/**
 * MCMSBakery RazorLeaf Conductor shortcodes
 *
 * @package MCMSBakeryVisualComposer
 *
 */
class MCMSBakeryShortCode_VC_Button2 extends MCMSBakeryShortCode {
	protected function outputTitle( $title ) {
		$icon = $this->settings( 'icon' );

		return '<h4 class="mcmsb_element_title"><span class="vc_general vc_element-icon' . ( ! empty( $icon ) ? ' ' . $icon : '' ) . '"></span></h4>';
	}
}
