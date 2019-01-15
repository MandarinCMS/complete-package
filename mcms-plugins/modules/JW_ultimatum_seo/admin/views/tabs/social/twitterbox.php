<?php
/**
 * @package MCMSSEO\Admin\Views
 */

if ( ! defined( 'MCMSSEO_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

echo '<h2>' . esc_html__( 'Twitter settings', 'mandarincms-seo' ) . '</h2>';

$yform->light_switch( 'twitter', __( 'Add Twitter card meta data', 'mandarincms-seo' ) );

/* translators: %s expands to <code>&lt;head&gt;</code> */
$p = sprintf( __( 'Add Twitter card meta data to your site\'s %s section.', 'mandarincms-seo' ), '<code>&lt;head&gt;</code>' );
printf( '<p>%s</p>', $p );

echo '<br />';

$yform->select( 'twitter_card_type', __( 'The default card type to use', 'mandarincms-seo' ), MCMSSEO_Option_Social::$twitter_card_types );

do_action( 'mcmsseo_admin_twitter_section' );
