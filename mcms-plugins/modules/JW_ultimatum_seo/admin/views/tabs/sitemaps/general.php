<?php
/**
 * @package MCMSSEO\Admin\Views
 */

if ( ! defined( 'MCMSSEO_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

echo '<h2>' . esc_html__( 'Your XML Sitemap', 'mandarincms-seo' ) . '</h2>';

if ( $options['enablexmlsitemap'] === true ) {
	echo '<p>';
	/* translators: %1$s opening tag of the link to the Sitemap, %2$s closing tag for the link. */
	printf(
		esc_html__( 'You can find your XML Sitemap here: %1$sXML Sitemap%2$s', 'mandarincms-seo' ),
		'<a target="_blank" href="' . esc_url( MCMSSEO_Sitemaps_Router::get_base_url( 'sitemap_index.xml' ) ) . '">',
		'</a>'
	);
	echo '<br/>';
	echo '<br/>';
	_e( 'You do <strong>not</strong> need to generate the XML sitemap, nor will it take up time to generate after publishing a post.', 'mandarincms-seo' );
	echo '</p>';
}
else {
	echo '<p>', __( 'Save your settings to activate your XML Sitemap.', 'mandarincms-seo' ), '</p>';
}

echo '<h2>' . esc_html__( 'Entries per sitemap page', 'mandarincms-seo' ) . '</h2>';
?>
	<p>
		<?php printf( __( 'Please enter the maximum number of entries per sitemap page (defaults to %s, you might want to lower this to prevent memory issues on some installs):', 'mandarincms-seo' ), MCMSSEO_Options::get_default( 'mcmsseo_xml', 'entries-per-page' ) ); ?>
	</p>

<?php
$yform->textinput( 'entries-per-page', __( 'Max entries per sitemap', 'mandarincms-seo' ) );
