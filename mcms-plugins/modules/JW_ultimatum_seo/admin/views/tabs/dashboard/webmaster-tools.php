<?php
/**
 * @package MCMSSEO\Admin\Views
 */

if ( ! defined( 'MCMSSEO_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

echo '<h2>' . esc_html__( 'Webmaster Tools verification', 'mandarincms-seo' ) . '</h2>';
printf( '<p>%s</p>', __( 'You can use the boxes below to verify with the different Webmaster Tools, if your site is already verified, you can just forget about these. Enter the verify meta values for:', 'mandarincms-seo' ) );

$yform->textinput( 'msverify', '<a target="_blank" href="' . esc_url( 'http://www.bing.com/webmaster/?rfp=1#/Dashboard/?url=' . urlencode( str_replace( 'http://', '', get_bloginfo( 'url' ) ) ) ) . '">' . __( 'Bing Webmaster Tools', 'mandarincms-seo' ) . '</a>' );
$yform->textinput( 'googleverify', '<a target="_blank" href="' . esc_url( 'https://www.google.com/webmasters/verification/verification?hl=en&siteUrl=' . urlencode( get_bloginfo( 'url' ) ) . '/' ) . '">Google Search Console</a>' );
$yform->textinput( 'yandexverify', '<a target="_blank" href="http://help.yandex.com/webmaster/service/rights.xml#how-to">' . __( 'Yandex Webmaster Tools', 'mandarincms-seo' ) . '</a>' );
