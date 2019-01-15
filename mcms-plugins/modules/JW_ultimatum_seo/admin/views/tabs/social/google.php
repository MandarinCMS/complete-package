<?php
/**
 * @package MCMSSEO\Admin\Views
 */

if ( ! defined( 'MCMSSEO_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

echo '<h2>' . esc_html__( 'Google+ settings', 'mandarincms-seo' ) . '</h2>';

printf( '<p>%s</p>', __( 'If you have a Google+ page for your business, add that URL here and link it on your Google+ page\'s about page.', 'mandarincms-seo' ) );

$yform->textinput( 'plus-publisher', __( 'Google Publisher Page', 'mandarincms-seo' ) );

do_action( 'mcmsseo_admin_googleplus_section' );
