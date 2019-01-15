<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

/**
 * @deprecated
 * Shortcode attributes
 * @var $atts
 * @var $title
 * @var $el_id
 * @var $content - shortcode content
 * Shortcode class
 * @var $this MCMSBakeryShortCode_VC_Accordion_tab
 */
$title = $el_id = '';
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

$css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, 'mcmsb_accordion_section group', $this->settings['base'], $atts );

$output = '
	<div ' . ( isset( $el_id ) && ! empty( $el_id ) ? "id='" . esc_attr( $el_id ) . "'" : '' ) . 'class="' . esc_attr( $css_class ) . '">
		<h3 class="mcmsb_accordion_header ui-accordion-header"><a href="#' . sanitize_title( $title ) . '">' . $title . '</a></h3>
		<div class="mcmsb_accordion_content ui-accordion-content vc_clearfix">
			' . ( ( '' === trim( $content ) ) ? __( 'Empty section. Edit page to add content here.', 'rl_conductor' ) : mcmsb_js_remove_mcmsautop( $content ) ) . '
		</div>
	</div>
';

echo $output;
