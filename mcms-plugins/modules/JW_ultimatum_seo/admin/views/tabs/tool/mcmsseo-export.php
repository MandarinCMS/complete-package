<?php
/**
 * @package MCMSSEO\Admin\Views
 */

if ( ! defined( 'MCMSSEO_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

/* translators: %1$s expands to Ultimatum SEO */
$submit_button_value = sprintf( __( 'Export your %1$s settings', 'mandarincms-seo' ), 'Ultimatum SEO' );

?><p><?php
	/* translators: %1$s expands to Ultimatum SEO */
	printf( __( 'Export your %1$s settings here, to import them again later or to import them on another site.', 'mandarincms-seo' ), 'Ultimatum SEO' );
	?></p>
<form
	action="<?php echo esc_attr( admin_url( 'admin.php?page=mcmsseo_tools&tool=import-export#top#mcmsseo-export' ) ); ?>"
	method="post"
	accept-charset="<?php echo esc_attr( get_bloginfo( 'charset' ) ); ?>">
	<?php $yform->checkbox( 'include_taxonomy_meta', __( 'Include Taxonomy Metadata', 'mandarincms-seo' ) ); ?><br />
	<?php mcms_nonce_field( MCMSSEO_Export::NONCE_ACTION, MCMSSEO_Export::NONCE_NAME );  ?>
	<button type="submit" class="button button-primary" id="export-button"><?php echo esc_html( $submit_button_value ); ?></button>
</form>
