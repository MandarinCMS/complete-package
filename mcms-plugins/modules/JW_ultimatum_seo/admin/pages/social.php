<?php
/**
 * @package MCMSSEO\Admin
 */

if ( ! defined( 'MCMSSEO_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

$yform = Ultimatum_Form::get_instance();
$yform->admin_header( true, 'mcmsseo_social' );

$tabs = new MCMSSEO_Option_Tabs( 'social' );
$tabs->add_tab( new MCMSSEO_Option_Tab( 'accounts', __( 'Accounts', 'mandarincms-seo' ), array( 'video_url' => 'https://jiiworks.net/screencast-social-accounts' ) ) );
$tabs->add_tab( new MCMSSEO_Option_Tab( 'facebook', __( 'Facebook', 'mandarincms-seo' ), array( 'video_url' => 'https://jiiworks.net/screencast-social-facebook' ) ) );
$tabs->add_tab( new MCMSSEO_Option_Tab( 'twitterbox', __( 'Twitter', 'mandarincms-seo' ), array( 'video_url' => 'https://jiiworks.net/screencast-social-twitter' ) ) );
$tabs->add_tab( new MCMSSEO_Option_Tab( 'pinterest', __( 'Pinterest', 'mandarincms-seo' ), array( 'video_url' => 'https://jiiworks.net/screencast-social-pinterest' ) ) );
$tabs->add_tab( new MCMSSEO_Option_Tab( 'google', __( 'Google+', 'mandarincms-seo' ), array( 'video_url' => 'https://jiiworks.net/screencast-social-google' ) ) );
$tabs->display( $yform );

$yform->admin_footer();
