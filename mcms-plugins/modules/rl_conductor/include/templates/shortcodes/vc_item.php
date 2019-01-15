<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

/**
 * Shortcode attributes
 * @var $atts
 * @var $el_class
 * Shortcode class
 * @var $this MCMSBakeryShortCode
 */
$el_class = '';
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

$css = $this->getExtraClass( $el_class );

echo '<div class="vc_items' . esc_attr( $css ) . '">' . __( 'Item', 'rl_conductor' ) . '</div>';
