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

$options = get_site_option( 'mcmsseo_ms' );

if ( isset( $_POST['mcmsseo_submit'] ) ) {
	check_admin_referer( 'mcmsseo-network-settings' );

	foreach ( array( 'access', 'defaultblog' ) as $opt ) {
		$options[ $opt ] = $_POST['mcmsseo_ms'][ $opt ];
	}
	unset( $opt );
	MCMSSEO_Options::update_site_option( 'mcmsseo_ms', $options );
	add_settings_error( 'mcmsseo_ms', 'settings_updated', __( 'Settings Updated.', 'mandarincms-seo' ), 'updated' );
}

if ( isset( $_POST['mcmsseo_restore_blog'] ) ) {
	check_admin_referer( 'mcmsseo-network-restore' );
	if ( isset( $_POST['mcmsseo_ms']['restoreblog'] ) && is_numeric( $_POST['mcmsseo_ms']['restoreblog'] ) ) {
		$restoreblog = (int) MCMSSEO_Utils::validate_int( $_POST['mcmsseo_ms']['restoreblog'] );
		$blog        = get_blog_details( $restoreblog );

		if ( $blog ) {
			MCMSSEO_Options::reset_ms_blog( $restoreblog );
			add_settings_error( 'mcmsseo_ms', 'settings_updated', sprintf( __( '%s restored to default SEO settings.', 'mandarincms-seo' ), esc_html( $blog->blogname ) ), 'updated' );
		}
		else {
			add_settings_error( 'mcmsseo_ms', 'settings_updated', sprintf( __( 'Blog %s not found.', 'mandarincms-seo' ), esc_html( $restoreblog ) ), 'error' );
		}
		unset( $restoreblog, $blog );
	}
}

/* Set up selectbox dropdowns for smaller networks (usability) */
$use_dropdown = true;
if ( get_blog_count() > 100 ) {
	$use_dropdown = false;
}
else {

	if ( function_exists( 'get_sites' ) ) { // MCMS 4.6+.
		$sites = array_map( 'get_object_vars', get_sites( array( 'deleted' => 0 ) ) );
	}
	else {
		$sites = mcms_get_sites( array( 'deleted' => 0 ) );
	}

	if ( is_array( $sites ) && $sites !== array() ) {
		$dropdown_input = array(
			'-' => __( 'None', 'mandarincms-seo' ),
		);

		foreach ( $sites as $site ) {
			$dropdown_input[ $site['blog_id'] ] = $site['blog_id'] . ': ' . $site['domain'];

			$blog_states = array();
			if ( $site['public'] === '1' ) {
				$blog_states[] = __( 'public', 'mandarincms-seo' );
			}
			if ( $site['archived'] === '1' ) {
				$blog_states[] = __( 'archived', 'mandarincms-seo' );
			}
			if ( $site['mature'] === '1' ) {
				$blog_states[] = __( 'mature', 'mandarincms-seo' );
			}
			if ( $site['spam'] === '1' ) {
				$blog_states[] = __( 'spam', 'mandarincms-seo' );
			}
			if ( $blog_states !== array() ) {
				$dropdown_input[ $site['blog_id'] ] .= ' [' . implode( ', ', $blog_states ) . ']';
			}
		}
		unset( $site, $blog_states );
	}
	else {
		$use_dropdown = false;
	}
	unset( $sites );
}

$yform->admin_header( false, 'mcmsseo_ms' );

echo '<h2>', __( 'MultiSite Settings', 'mandarincms-seo' ), '</h2>';
echo '<form method="post" accept-charset="', esc_attr( get_bloginfo( 'charset' ) ), '">';
mcms_nonce_field( 'mcmsseo-network-settings', '_mcmsnonce', true, true );

/* @internal Important: Make sure the options added to the array here are in line with the options set in the MCMSSEO_Option_MS::$allowed_access_options property */
$yform->select(
	'access',
	/* translators: %1$s expands to Ultimatum SEO */
	sprintf( __( 'Who should have access to the %1$s settings', 'mandarincms-seo' ), 'Ultimatum SEO' ),
	array(
		'admin'      => __( 'Site Admins (default)', 'mandarincms-seo' ),
		'superadmin' => __( 'Super Admins only', 'mandarincms-seo' ),
	),
	'mcmsseo_ms'
);

if ( $use_dropdown === true ) {
	$yform->select(
		'defaultblog',
		__( 'New sites in the network inherit their SEO settings from this site', 'mandarincms-seo' ),
		$dropdown_input,
		'mcmsseo_ms'
	);
	echo '<p>' . __( 'Choose the site whose settings you want to use as default for all sites that are added to your network. If you choose \'None\', the normal module defaults will be used.', 'mandarincms-seo' ) . '</p>';
}
else {
	$yform->textinput( 'defaultblog', __( 'New sites in the network inherit their SEO settings from this site', 'mandarincms-seo' ), 'mcmsseo_ms' );
	echo '<p>' . sprintf( __( 'Enter the %sSite ID%s for the site whose settings you want to use as default for all sites that are added to your network. Leave empty for none (i.e. the normal module defaults will be used).', 'mandarincms-seo' ), '<a href="' . esc_url( network_admin_url( 'sites.php' ) ) . '">', '</a>' ) . '</p>';
}
	echo '<p><strong>' . __( 'Take note:', 'mandarincms-seo' ) . '</strong> ' . __( 'Privacy sensitive (FB admins and such), myskin specific (title rewrite) and a few very site specific settings will not be imported to new blogs.', 'mandarincms-seo' ) . '</p>';


echo '<input type="submit" name="mcmsseo_submit" class="button button-primary" value="' . __( 'Save MultiSite Settings', 'mandarincms-seo' ) . '"/>';
echo '</form>';

echo '<h2>' . __( 'Restore site to default settings', 'mandarincms-seo' ) . '</h2>';
echo '<form method="post" accept-charset="' . esc_attr( get_bloginfo( 'charset' ) ) . '">';
mcms_nonce_field( 'mcmsseo-network-restore', '_mcmsnonce', true, true );
echo '<p>' . __( 'Using this form you can reset a site to the default SEO settings.', 'mandarincms-seo' ) . '</p>';

if ( $use_dropdown === true ) {
	unset( $dropdown_input['-'] );
	$yform->select(
		'restoreblog',
		__( 'Site ID', 'mandarincms-seo' ),
		$dropdown_input,
		'mcmsseo_ms'
	);
}
else {
	$yform->textinput( 'restoreblog', __( 'Blog ID', 'mandarincms-seo' ), 'mcmsseo_ms' );
}

echo '<input type="submit" name="mcmsseo_restore_blog" value="' . __( 'Restore site to defaults', 'mandarincms-seo' ) . '" class="button"/>';
echo '</form>';

$yform->admin_footer( false );
