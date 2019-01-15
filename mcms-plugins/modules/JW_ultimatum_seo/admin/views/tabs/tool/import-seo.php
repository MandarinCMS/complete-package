<?php
/**
 * @package MCMSSEO\Admin\Views
 */

if ( ! defined( 'MCMSSEO_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

?>
<p><?php _e( 'No doubt you\'ve used an SEO module before if this site isn\'t new. Let\'s make it easy on you, you can import the data below. If you want, you can import first, check if it was imported correctly, and then import &amp; delete. No duplicate data will be imported.', 'mandarincms-seo' ); ?></p>

<p><?php printf( __( 'If you\'ve used another SEO module, try the %sSEO Data Transporter%s module to move your data into this module, it rocks!', 'mandarincms-seo' ), '<a href="https://mandarincms.com/modules/seo-data-transporter/">', '</a>' ); ?></p>

<form
	action="<?php echo esc_attr( admin_url( 'admin.php?page=mcmsseo_tools&tool=import-export#top#import-seo' ) ); ?>"
	method="post" accept-charset="<?php echo esc_attr( get_bloginfo( 'charset' ) ); ?>">
	<?php
	mcms_nonce_field( 'mcmsseo-import', '_mcmsnonce', true, true );
	$yform->checkbox( 'importheadspace', __( 'Import from HeadSpace2?', 'mandarincms-seo' ) );
	$yform->checkbox( 'importaioseo', __( 'Import from All-in-One SEO?', 'mandarincms-seo' ) );
	$yform->checkbox( 'importwoo', __( 'Import from WooMySkins SEO framework?', 'mandarincms-seo' ) );
	$yform->checkbox( 'importmcmsseo', __( 'Import from mcmsSEO', 'mandarincms-seo' ) );

	do_action( 'mcmsseo_import_other_modules' );
	?>
	<br/>
	<?php
	$yform->checkbox( 'deleteolddata', __( 'Delete the old data after import? (recommended)', 'mandarincms-seo' ) );
	?>
	<br/>
	<input type="submit" class="button button-primary" name="import"
	       value="<?php _e( 'Import', 'mandarincms-seo' ); ?>"/>
</form>
