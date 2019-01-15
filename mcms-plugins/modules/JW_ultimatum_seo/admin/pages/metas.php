<?php
/**
 * @package MCMSSEO\Admin
 */

if ( ! defined( 'MCMSSEO_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

add_filter( 'mcmsseo_help_center_items', 'ultimatum_add_meta_options_help_center_tabs' );

$options = MCMSSEO_Options::get_options( array( 'mcmsseo_titles', 'mcmsseo_permalinks', 'mcmsseo_internallinks' ) );

$yform = Ultimatum_Form::get_instance();
$yform->admin_header( true, 'mcmsseo_titles' );

$tabs = new MCMSSEO_Option_Tabs( 'metas' );
$tabs->add_tab( new MCMSSEO_Option_Tab( 'general', __( 'General', 'mandarincms-seo' ), array( 'video_url' => 'https://jiiworks.net/screencast-metas' ) ) );
$tabs->add_tab( new MCMSSEO_Option_Tab( 'home', __( 'Homepage', 'mandarincms-seo' ), array( 'video_url' => 'https://jiiworks.net/screencast-metas-homepage' ) ) );
$tabs->add_tab( new MCMSSEO_Option_Tab( 'post-types', __( 'Post Types', 'mandarincms-seo' ), array( 'video_url' => 'https://jiiworks.net/screencast-metas-post-types' ) ) );
$tabs->add_tab( new MCMSSEO_Option_Tab( 'taxonomies', __( 'Taxonomies', 'mandarincms-seo' ), array( 'video_url' => 'https://jiiworks.net/screencast-metas-taxonomies' ) ) );
$tabs->add_tab( new MCMSSEO_Option_Tab( 'archives', __( 'Archives', 'mandarincms-seo' ), array( 'video_url' => 'https://jiiworks.net/screencast-metas-archives' ) ) );
$tabs->add_tab( new MCMSSEO_Option_Tab( 'other', __( 'Other', 'mandarincms-seo' ), array( 'video_url' => 'https://jiiworks.net/screencast-metas-other' ) ) );
$tabs->display( $yform, $options );

$yform->admin_footer();

/**
 * Add help tabs
 *
 * @param array $tabs Current help center tabs.
 *
 * @return array
 */
function ultimatum_add_meta_options_help_center_tabs( $tabs ) {
	$tabs[] = new MCMSSEO_Help_Center_Item(
		'basic-help',
		__( 'Template explanation', 'mandarincms-seo' ),
		array(
			'content' => '<p>' . sprintf( __( 'The title &amp; metas settings for %1$s are made up of variables that are replaced by specific values from the page when the page is displayed. The tabs on the left explain the available variables.', 'mandarincms-seo' ), 'Ultimatum SEO' ) . '</p>' . '<p>' . __( 'Note that not all variables can be used in every template.', 'mandarincms-seo' ) . '</p>',
		)
	);

	$tabs[] = new MCMSSEO_Help_Center_Item(
		'title-vars',
		__( 'Basic Variables', 'mandarincms-seo' ),
		array(
			'content' => '<h2>' . __( 'Basic Variables', 'mandarincms-seo' ) . '</h2>' . MCMSSEO_Replace_Vars::get_basic_help_texts(),
		)
	);

	$tabs[] = new MCMSSEO_Help_Center_Item(
		'title-vars-advanced',
		__( 'Advanced Variables', 'mandarincms-seo' ),
		array(
			'content' => '<h2>' . __( 'Advanced Variables', 'mandarincms-seo' ) . '</h2>' . MCMSSEO_Replace_Vars::get_advanced_help_texts(),
		)
	);

	return $tabs;
}
