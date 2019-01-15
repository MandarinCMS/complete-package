<?php
/**
 * @package MCMSSEO\Admin\Views
 */

if ( ! defined( 'MCMSSEO_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

echo '<h2>' . esc_html__( 'Pinterest settings', 'mandarincms-seo' ) . '</h2>';

printf( '<p>%s</p>', __( 'Pinterest uses Open Graph metadata just like Facebook, so be sure to keep the Open Graph checkbox on the Facebook tab checked if you want to optimize your site for Pinterest.', 'mandarincms-seo' ) );
printf( '<p>%s</p>', __( 'If you have already confirmed your website with Pinterest, you can skip the step below.', 'mandarincms-seo' ) );

/* translators: %1$s / %2$s expands to a link to pinterest.com's help page. */
$p = sprintf( __( 'To %1$sconfirm your site with Pinterest%2$s, add the meta tag here:', 'mandarincms-seo' ), '<a target="_blank" href="https://help.pinterest.com/en/articles/confirm-your-website#meta_tag">', '</a>' );
printf( '<p>%s</p>', $p );

$yform->textinput( 'pinterestverify', __( 'Pinterest confirmation', 'mandarincms-seo' ) );

do_action( 'mcmsseo_admin_pinterest_section' );
