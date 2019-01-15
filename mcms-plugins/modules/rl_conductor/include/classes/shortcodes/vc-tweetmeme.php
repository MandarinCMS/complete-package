<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

class MCMSBakeryShortCode_VC_TweetMeMe extends MCMSBakeryShortCode {
	protected function contentInline( $atts, $content = null ) {
		/**
		 * Shortcode attributes
		 * @var $atts
		 * Shortcode class
		 * @var $this MCMSBakeryShortCode_VC_TweetMeMe
		 */
		$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
		$css = isset( $atts['css'] ) ? $atts['css'] : '';
		$el_class = isset( $atts['el_class'] ) ? $atts['el_class'] : '';

		$class_to_filter = 'mcmsb_googleplus vc_social-placeholder mcmsb_content_element';
		$class_to_filter .= vc_shortcode_custom_css_class( $css, ' ' ) . $this->getExtraClass( $el_class ) . $this->getCSSAnimation( $atts['css_animation'] );
		$css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, $class_to_filter, $this->settings['base'], $atts );

		return '<div class="' . esc_attr( $css_class ) . '"></div>';
	}
}
