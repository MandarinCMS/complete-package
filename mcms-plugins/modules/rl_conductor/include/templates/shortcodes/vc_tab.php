<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

/**
 * Shortcode attributes
 * @var $atts
 * @var $tab_id
 * @var $title
 * @var $content - shortcode content
 * Shortcode class
 * @var $this MCMSBakeryShortCode_VC_Tab
 */
$tab_id = $title = '';
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

mcms_enqueue_script( 'jquery_ui_tabs_rotate' );

$css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, 'mcmsb_tab ui-tabs-panel mcmsb_ui-tabs-hide vc_clearfix', $this->settings['base'], $atts );

$output = '
	<div id="tab-' . ( empty( $tab_id ) ? sanitize_title( $title ) : esc_attr( $tab_id ) ) . '" class="' . esc_attr( $css_class ) . '">
		' . ( ( '' === trim( $content ) ) ? __( 'Empty tab. Edit page to add content here.', 'rl_conductor' ) : mcmsb_js_remove_mcmsautop( $content ) ) . '
	</div>
';

echo $output;
