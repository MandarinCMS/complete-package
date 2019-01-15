<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

/**
 * Shortcode attributes
 * @var $atts
 * @var $title
 * @var $interval
 * @var $el_class
 * @var $content - shortcode content
 * Shortcode class
 * @var $this MCMSBakeryShortCode_VC_Tabs
 */
$title = $interval = $el_class = '';
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

mcms_enqueue_script( 'jquery-ui-tabs' );

$el_class = $this->getExtraClass( $el_class );

$element = 'mcmsb_tabs';
if ( 'vc_tour' === $this->shortcode ) {
	$element = 'mcmsb_tour';
}

// Extract tab titles
preg_match_all( '/vc_tab([^\]]+)/i', $content, $matches, PREG_OFFSET_CAPTURE );
$tab_titles = array();
/**
 * vc_tabs
 *
 */
if ( isset( $matches[1] ) ) {
	$tab_titles = $matches[1];
}
$tabs_nav = '';
$tabs_nav .= '<ul class="mcmsb_tabs_nav ui-tabs-nav vc_clearfix">';
foreach ( $tab_titles as $tab ) {
	$tab_atts = shortcode_parse_atts( $tab[0] );
	if ( isset( $tab_atts['title'] ) ) {
		$tabs_nav .= '<li><a href="#tab-' . ( isset( $tab_atts['tab_id'] ) ? $tab_atts['tab_id'] : sanitize_title( $tab_atts['title'] ) ) . '">' . $tab_atts['title'] . '</a></li>';
	}
}
$tabs_nav .= '</ul>';

$css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, trim( $element . ' mcmsb_content_element ' . $el_class ), $this->settings['base'], $atts );

if ( 'vc_tour' === $this->shortcode ) {
	$next_prev_nav = '<div class="mcmsb_tour_next_prev_nav vc_clearfix"> <span class="mcmsb_prev_slide"><a href="#prev" title="' . __( 'Previous tab', 'rl_conductor' ) . '">' . __( 'Previous tab', 'rl_conductor' ) . '</a></span> <span class="mcmsb_next_slide"><a href="#next" title="' . __( 'Next tab', 'rl_conductor' ) . '">' . __( 'Next tab', 'rl_conductor' ) . '</a></span></div>';
} else {
	$next_prev_nav = '';
}

$output = '
	<div class="' . $css_class . '" data-interval="' . $interval . '">
		<div class="mcmsb_wrapper mcmsb_tour_tabs_wrapper ui-tabs vc_clearfix">
			' . mcmsb_widget_title( array( 'title' => $title, 'extraclass' => $element . '_heading' ) )
	. $tabs_nav
	. mcmsb_js_remove_mcmsautop( $content )
	. $next_prev_nav . '
		</div>
	</div>
';

echo $output;
