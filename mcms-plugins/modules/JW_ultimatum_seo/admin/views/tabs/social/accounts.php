<?php
/**
 * @package MCMSSEO\Admin\Views
 */

if ( ! defined( 'MCMSSEO_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

echo '<h2>' . esc_html__( 'Your social profiles', 'mandarincms-seo' ) . '</h2>';

echo '<p>';
_e( 'To let search engines know which social profiles are associated to this site, enter them below:', 'mandarincms-seo' );
echo '</p>';

$yform->textinput( 'facebook_site', __( 'Facebook Page URL', 'mandarincms-seo' ) );
$yform->textinput( 'twitter_site', __( 'Twitter Username', 'mandarincms-seo' ) );
$yform->textinput( 'instagram_url', __( 'Instagram URL', 'mandarincms-seo' ) );
$yform->textinput( 'linkedin_url', __( 'LinkedIn URL', 'mandarincms-seo' ) );
$yform->textinput( 'myspace_url', __( 'MySpace URL', 'mandarincms-seo' ) );
$yform->textinput( 'pinterest_url', __( 'Pinterest URL', 'mandarincms-seo' ) );
$yform->textinput( 'youtube_url', __( 'YouTube URL', 'mandarincms-seo' ) );
$yform->textinput( 'google_plus_url', __( 'Google+ URL', 'mandarincms-seo' ) );

do_action( 'mcmsseo_admin_other_section' );
