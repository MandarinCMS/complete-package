<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

class MCMSBakeryShortCode_VC_Message extends MCMSBakeryShortCode {

	public static function convertAttributesToMessageBox2( $atts ) {
		if ( isset( $atts['style'] ) ) {
			if ( '3d' === $atts['style'] ) {
				$atts['message_box_style'] = '3d';
				$atts['style'] = 'rounded';
			} elseif ( 'outlined' === $atts['style'] ) {
				$atts['message_box_style'] = 'outline';
				$atts['style'] = 'rounded';
			} elseif ( 'square_outlined' === $atts['style'] ) {
				$atts['message_box_style'] = 'outline';
				$atts['style'] = 'square';
			}
		}

		return $atts;
	}

	public function outputTitle( $title ) {
		return '';
	}
}
