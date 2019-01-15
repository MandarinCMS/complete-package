<?php
/**
 * @package MCMSSEO\Admin\Views
 */

if ( ! defined( 'MCMSSEO_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

if ( ! current_myskin_supports( 'title-tag' ) ) {
	$yform->light_switch( 'forcerewritetitle', __( 'Force rewrite titles', 'mandarincms-seo' ) );
	echo '<p class="description">', sprintf( __( '%1$s has auto-detected whether it needs to force rewrite the titles for your pages, if you think it\'s wrong and you know what you\'re doing, you can change the setting here.', 'mandarincms-seo' ), 'Ultimatum SEO' ) . '</p>';
}

echo '<h2>' . esc_html__( 'Title Separator', 'mandarincms-seo' ) . '</h2>';

$legend      = __( 'Title separator symbol', 'mandarincms-seo' );
$legend_attr = array( 'class' => 'radiogroup screen-reader-text' );
$yform->radio( 'separator', MCMSSEO_Option_Titles::get_instance()->get_separator_options(), $legend, $legend_attr );
echo '<p class="description">', __( 'Choose the symbol to use as your title separator. This will display, for instance, between your post title and site name.', 'mandarincms-seo' ), ' ', __( 'Symbols are shown in the size they\'ll appear in the search results.', 'mandarincms-seo' ), '</p>';

echo '<h2>' . __( 'Enabled analysis', 'mandarincms-seo' ) . '</h2>';

$yform->light_switch( 'content-analysis-active', __( 'Readability analysis', 'mandarincms-seo' ) );
echo '<p class="description">', __( 'Removes the readability tab from the metabox and disables all readability-related suggestions.', 'mandarincms-seo' ) . '</p>';

$yform->light_switch( 'keyword-analysis-active', __( 'Keyword analysis', 'mandarincms-seo' ) );
echo '<p class="description">', __( 'Removes the keyword tab from the metabox and disables all keyword-related suggestions.', 'mandarincms-seo' ) . '</p>';
