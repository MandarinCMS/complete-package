<?php
/**
 * @package MCMSSEO\Admin
 */

/**
 * @todo - [JRF => whomever] check for other sitemap modules which may conflict ?
 * @todo - [JRF => whomever] check for existance of .xls rewrite rule in .htaccess from
 * google-sitemaps-module/generator and remove as it will cause errors for our sitemaps
 * (or inform the user and disallow enabling of sitemaps )
 * @todo - [JRF => whomever] check if anything along these lines is already being done
 */


if ( ! defined( 'MCMSSEO_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

$yform = Ultimatum_Form::get_instance();
$yform->admin_header( true, 'mcmsseo_xml' );

$options = get_option( 'mcmsseo_xml' );

echo '<br/>';
$yform->light_switch( 'enablexmlsitemap', __( 'XML sitemap functionality', 'mandarincms-seo' ) );

$tabs = new MCMSSEO_Option_Tabs( 'sitemaps' );
$tabs->add_tab( new MCMSSEO_Option_Tab( 'general', __( 'General', 'mandarincms-seo' ), array( 'video_url' => 'https://jiiworks.net/screencast-sitemaps' ) ) );

$title_options = MCMSSEO_Options::get_option( 'mcmsseo_titles' );

if ( empty( $title_options['disable-author'] ) ) {
	$tabs->add_tab( new MCMSSEO_Option_Tab( 'user-sitemap', __( 'User sitemap', 'mandarincms-seo' ), array( 'video_url' => 'https://jiiworks.net/screencast-sitemaps-user-sitemap' ) ) );
}

$tabs->add_tab( new MCMSSEO_Option_Tab( 'post-types', __( 'Post Types', 'mandarincms-seo' ), array( 'video_url' => 'https://jiiworks.net/screencast-sitemaps-post-types' ) ) );
$tabs->add_tab( new MCMSSEO_Option_Tab( 'exclude-post', __( 'Excluded Posts', 'mandarincms-seo' ), array( 'video_url' => 'https://jiiworks.net/screencast-sitemaps-exclude-post' ) ) );
$tabs->add_tab( new MCMSSEO_Option_Tab( 'taxonomies', __( 'Taxonomies', 'mandarincms-seo' ), array( 'video_url' => 'https://jiiworks.net/screencast-sitemaps-taxonomies' ) ) );

echo '<div id="sitemapinfo">';
$tabs->display( $yform, $options );
echo '</div>';


/**
 * Fires at the end of XML Sitemaps configuration form.
 */
do_action( 'mcmsseo_xmlsitemaps_config' );

$yform->admin_footer();
