<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

/**
 * Shortcode attributes
 * @var $atts
 * @var $el_class
 * @var $el_id
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
$el_class = $el_id = $title = $flickr_id = $css = $css_animation = $count = $type = $display = '';
$output = '';
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

$class_to_filter = 'mcmsb_flickr_widget mcmsb_content_element';
$class_to_filter .= vc_shortcode_custom_css_class( $css, ' ' ) . $this->getExtraClass( $el_class ) . $this->getCSSAnimation( $css_animation );
$css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, $class_to_filter, $this->settings['base'], $atts );
$wrapper_attributes = array();
if ( ! empty( $el_id ) ) {
	$wrapper_attributes[] = 'id="' . esc_attr( $el_id ) . '"';
}
$output = '
	<div class="' . esc_attr( $css_class ) . '" ' . implode( ' ', $wrapper_attributes ) . '>
		<div class="mcmsb_wrapper">
			' . mcmsb_widget_title( array(
		'title' => $title,
		'extraclass' => 'mcmsb_flickr_heading',
	) ) . '
			<script type="text/javascript" src="//www.flickr.com/badge_code_v2.gne?count=' . $count . '&amp;display=' . $display . '&amp;size=s&amp;layout=x&amp;source=' . $type . '&amp;' . $type . '=' . $flickr_id . '"></script>
			<p class="flickr_stream_wrap"><a class="mcmsb_follow_btn mcmsb_flickr_stream" href="//www.flickr.com/photos/' . $flickr_id . '">' . __( 'View stream on flickr', 'rl_conductor' ) . '</a></p>
		</div>
	</div>
';

echo $output;
