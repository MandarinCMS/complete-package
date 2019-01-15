<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

class MCMSBakeryShortCode_VC_Text_separator extends MCMSBakeryShortCode {

	public function outputTitle( $title ) {
		return '';
	}

	public function getVcIcon( $atts ) {

		if ( empty( $atts['i_type'] ) ) {
			$atts['i_type'] = 'fontawesome';
		}
		$data = vc_map_integrate_parse_atts( $this->shortcode, 'vc_icon', $atts, 'i_' );
		if ( $data ) {
			$icon = visual_composer()->getShortCode( 'vc_icon' );
			if ( is_object( $icon ) ) {
				return $icon->render( array_filter( $data ) );
			}
		}

		return '';
	}
}
