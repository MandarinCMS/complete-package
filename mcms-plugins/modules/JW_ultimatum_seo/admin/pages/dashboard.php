<?php
/**
 * @package MCMSSEO\Admin
 */

if ( ! defined( 'MCMSSEO_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

if ( filter_input( INPUT_GET, 'intro' ) ) {

	update_user_meta( get_current_user_id(), 'mcmsseo_seen_about_version', MCMSSEO_VERSION );
	require MCMSSEO_PATH . 'admin/views/about.php';

	return;
}

$options = get_option( 'mcmsseo' );

if ( isset( $_GET['allow_tracking'] ) && check_admin_referer( 'mcmsseo_activate_tracking', 'nonce' ) ) {
	$options['ultimatum_tracking'] = ( $_GET['allow_tracking'] == 'yes' );
	update_option( 'mcmsseo', $options );

	if ( isset( $_SERVER['HTTP_REFERER'] ) ) {
		mcms_safe_redirect( $_SERVER['HTTP_REFERER'], 307 );
		exit;
	}
}

$yform = Ultimatum_Form::get_instance();
$yform->admin_header( true, 'mcmsseo' );

do_action( 'mcmsseo_all_admin_notices' );

if ( is_array( $options['blocking_files'] ) && count( $options['blocking_files'] ) > 0 ) {

	$xml_sitemap_options = MCMSSEO_Options::get_option( 'mcmsseo_xml' );
	if ( $xml_sitemap_options['enablexmlsitemap'] ) {

		echo '<div class="notice notice-error inline ultimatum-notice-blocking-files"><p id="blocking_files">';
		printf(
			/* translators: %1$s expands to Ultimatum SEO */
			_n( 'The following file is blocking your XML sitemaps from working properly. Either delete it (this can be done with the "Fix it" button) or disable %1$s XML sitemaps.', 'The following files are blocking your XML sitemaps from working properly. Either delete them (this can be done with the "Fix it" button) or disable %1$s XML sitemaps.', count( $options['blocking_files'] ), 'mandarincms-seo' ),
			'Ultimatum SEO'
		);
		foreach ( $options['blocking_files'] as $file ) {
			echo '<br/>', '<code>', esc_html( $file ), '</code>';
		}
		unset( $file );
		echo '<br><button type="button" data-nonce="', esc_js( mcms_create_nonce( 'mcmsseo-blocking-files' ) ), '" class="button">', __( 'Fix it', 'mandarincms-seo' ), '</button>';
		echo '</p></div>';
	}
}

$tabs = new MCMSSEO_Option_Tabs( 'dashboard' );
$tabs->add_tab( new MCMSSEO_Option_Tab( 'dashboard', __( 'Dashboard', 'mandarincms-seo' ), array( 'video_url' => 'https://jiiworks.net/screencast-notification-center' ) ) );
$tabs->add_tab( new MCMSSEO_Option_Tab( 'general', __( 'General', 'mandarincms-seo' ), array( 'video_url' => 'https://jiiworks.net/screencast-general' ) ) );
$tabs->add_tab( new MCMSSEO_Option_Tab( 'features', __( 'Features', 'mandarincms-seo' ) ) );
$knowledge_graph_label = ( 'company' === $options['company_or_person'] ) ? __( 'Company Info', 'mandarincms-seo' ) : __( 'Your Info', 'mandarincms-seo' );
$tabs->add_tab( new MCMSSEO_Option_Tab( 'knowledge-graph', __( $knowledge_graph_label, 'mandarincms-seo' ), array( 'video_url' => 'https://jiiworks.net/screencast-knowledge-graph' ) ) );
$tabs->add_tab( new MCMSSEO_Option_Tab( 'webmaster-tools', __( 'Webmaster Tools', 'mandarincms-seo' ), array( 'video_url' => 'https://jiiworks.net/screencast-general-search-console' ) ) );
$tabs->add_tab( new MCMSSEO_Option_Tab( 'security', __( 'Security', 'mandarincms-seo' ), array( 'video_url' => 'https://jiiworks.net/screencast-security' ) ) );
$tabs->display( $yform, $options );

do_action( 'mcmsseo_dashboard' );

$yform->admin_footer();
