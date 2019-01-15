<?php
/**
 * @package MCMSSEO\Admin\Views
 */

if ( ! defined( 'MCMSSEO_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

if ( 'posts' == get_option( 'show_on_front' ) ) {
	echo '<div id="homepage-titles-metas">';
	echo '<h2>', esc_html__( 'Homepage', 'mandarincms-seo' ), '</h2>';
	$yform->textinput( 'title-home-mcmsseo', __( 'Title template', 'mandarincms-seo' ), 'template homepage-template' );
	$yform->textarea( 'metadesc-home-mcmsseo', __( 'Meta description template', 'mandarincms-seo' ), array( 'class' => 'template homepage-template' ) );
	if ( $options['usemetakeywords'] === true ) {
		$yform->textinput( 'metakey-home-mcmsseo', __( 'Meta keywords template', 'mandarincms-seo' ) );
	}
	echo '</div>';
}
else {
	echo '<h2>', esc_html__( 'Homepage &amp; Front page', 'mandarincms-seo' ), '</h2>';
	echo '<p>';
	printf( __( 'You can determine the title and description for the front page by %sediting the front page itself &raquo;%s', 'mandarincms-seo' ), '<a href="' . esc_url( get_edit_post_link( get_option( 'page_on_front' ) ) ) . '">', '</a>' );
	echo '</p>';
	if ( get_option( 'page_for_posts' ) > 0 ) {
		echo '<p>', sprintf( __( 'You can determine the title and description for the blog page by %sediting the blog page itself &raquo;%s', 'mandarincms-seo' ), '<a href="' . esc_url( get_edit_post_link( get_option( 'page_for_posts' ) ) ) . '">', '</a>' ), '</p>';
	}
}
