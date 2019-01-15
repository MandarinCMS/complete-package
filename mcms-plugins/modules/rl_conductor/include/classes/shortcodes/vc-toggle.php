<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

class MCMSBakeryShortCode_VC_Toggle extends MCMSBakeryShortCode {
	public function outputTitle( $title ) {
		return '';
	}

	public function getHeading( $atts ) {
		if ( isset( $atts['use_custom_heading'] ) && 'true' === $atts['use_custom_heading'] ) {
			$custom_heading = visual_composer()->getShortCode( 'vc_custom_heading' );

			$data = vc_map_integrate_parse_atts( $this->shortcode, 'vc_custom_heading', $atts, 'custom_' );
			$data['text'] = $atts['title'];

			return $custom_heading->render( array_filter( $data ) );
		} else {
			return '<h4>' . esc_html( $atts['title'] ) . '</h4>';
		}
	}
}
