<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

/**
 * Shortcode attributes
 * @var $atts
 * @var $color
 * @var $icon
 * @var $size
 * @var $target
 * @var $href
 * @var $title
 * @var $call_text
 * @var $position
 * @var $el_class
 * @var $css_animation
 * Shortcode class
 * @var $this MCMSBakeryShortCode_VC_Cta_button
 */
$color = $icon = $size = $target = $href = $target = $call_text = $position = $el_class = $css_animation = '';
$output = '';
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

$el_class = $this->getExtraClass( $el_class );

if ( 'same' === $target || '_self' === $target ) {
	$target = '';
}
if ( '' !== $target ) {
	$target = ' target="' . esc_attr( $target ) . '"';
}

$icon = ( '' !== $icon && 'none' !== $icon ) ? ' ' . $icon : '';
$i_icon = ( '' !== $icon ) ? ' <i class="icon"> </i>' : '';

$color = ( '' !== $color ) ? ' mcmsb_' . $color : '';
$size = ( '' !== $size && 'mcmsb_regularsize' !== $size ) ? ' mcmsb_' . $size : ' ' . $size;

$a_class = '';
if ( '' !== $el_class ) {
	$tmp_class = explode( ' ', $el_class );
	if ( in_array( 'prettyphoto', $tmp_class ) ) {
		mcms_enqueue_script( 'prettyphoto' );
		mcms_enqueue_style( 'prettyphoto' );
		$a_class .= ' prettyphoto';
		$el_class = str_ireplace( 'prettyphoto', '', $el_class );
	}
}

if ( '' !== $href ) {
	$button = '<span class="mcmsb_button ' . esc_attr( $color . $size . $icon ) . '">' . $title . $i_icon . '</span>';
	$button = '<a class="mcmsb_button_a' . esc_attr( $a_class ) . '" href="' . $href . '"' . $target . '>' . $button . '</a>';
} else {
	$button = '';
	$el_class .= ' cta_no_button';
}
$css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, 'mcmsb_call_to_action mcmsb_content_element vc_clearfix ' . $position . $el_class, $this->settings['base'], $atts );
$css_class .= $this->getCSSAnimation( $css_animation );

$output .= '<div class="' . esc_attr( $css_class ) . '">';
if ( 'cta_align_bottom' !== $position ) {
	$output .= $button;
}
$output .= apply_filters( 'mcmsb_cta_text', '<h2 class="mcmsb_call_text">' . $call_text . '</h2>', array( 'content' => $call_text ) );
if ( 'cta_align_bottom' === $position ) {
	$output .= $button;
}
$output .= '</div>';

echo $output;