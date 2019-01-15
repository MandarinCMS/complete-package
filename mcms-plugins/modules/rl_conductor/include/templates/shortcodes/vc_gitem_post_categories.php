<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

/**
 * Shortcode attributes
 * @var $atts
 * Shortcode class
 * @var $this MCMSBakeryShortCode_VC_Gitem_Post_Categories
 */

$atts = vc_map_get_attributes( $this->getShortcode(), $atts );

?>
{{ post_categories:<?php echo http_build_query( array(
	'atts' => $atts,
) ); ?> }}
