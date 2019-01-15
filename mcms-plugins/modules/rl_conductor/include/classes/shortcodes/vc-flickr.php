<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

class MCMSBakeryShortCode_VC_flickr extends MCMSBakeryShortCode {
	protected function contentInline( $atts, $content = null ) {

		/**
		 * Shortcode attributes
		 * @var $atts
		 * @var $el_class
		 * @var $title
		 * @var $flickr_id
		 * @var $count
		 * @var $type
		 * @var $display
		 * @var $css
		 * @var $css_animation
		 * Shortcode class
		 * @var $this MCMSBakeryShortCode_VC_flickr
		 */
		$el_class = $title = $flickr_id = $css = $css_animation = $count = $type = $display = '';
		$output = '';
		$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
		extract( $atts );

		$css = isset( $atts['css'] ) ? $atts['css'] : '';
		$el_class = isset( $atts['el_class'] ) ? $atts['el_class'] : '';

		$class_to_filter = 'mcmsb_flickr_widget mcmsb_content_element';
		$class_to_filter .= vc_shortcode_custom_css_class( $css, ' ' ) . $this->getExtraClass( $el_class ) . $this->getCSSAnimation( $css_animation );
		$css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, $class_to_filter, $this->settings['base'], $atts );

		$output = '
			<div class="' . $css_class . '">
				<div class="mcmsb_wrapper">
					' . mcmsb_widget_title( array(
				'title' => $title,
				'extraclass' => 'mcmsb_flickr_heading',
			) ) . '
					<div class="vc_flickr-inline-placeholder" data-link="http://www.flickr.com/badge_code_v2.gne?count=' . $count . '&amp;display=' . $display . '&amp;size=s&amp;layout=x&amp;source=' . $type . '&amp;' . $type . '=' . $flickr_id . '"></div>
					<p class="flickr_stream_wrap"><a class="mcmsb_follow_btn mcmsb_flickr_stream" href="http://www.flickr.com/photos/' . $flickr_id . '">' . __( 'View stream on flickr', 'rl_conductor' ) . '</a></p>
				</div>
			</div>
		';

		return $output;
	}
}
