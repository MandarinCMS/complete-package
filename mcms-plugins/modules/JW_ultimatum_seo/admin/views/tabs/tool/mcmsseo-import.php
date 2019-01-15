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
<p><?php _e( 'Import settings by locating <em>settings.zip</em> and clicking "Import settings"', 'mandarincms-seo' ); ?></p>

<form
	action="<?php echo esc_attr( admin_url( 'admin.php?page=mcmsseo_tools&tool=import-export#top#mcmsseo-import' ) ); ?>"
	method="post" enctype="multipart/form-data"
	accept-charset="<?php echo esc_attr( get_bloginfo( 'charset' ) ); ?>">
	<?php mcms_nonce_field( 'mcmsseo-import-file', '_mcmsnonce', true, true ); ?>
	<label class="screen-reader-text" for="settings-import-file"><?php _e( 'Choose your settings.zip file', 'mandarincms-seo' ); ?></label>
	<input type="file" name="settings_import_file" id="settings-import-file"
	       accept="application/x-zip,application/x-zip-compressed,application/zip"/>
	<input type="hidden" name="action" value="mcms_handle_upload"/><br/>
	<br/>
	<input type="submit" class="button button-primary" value="<?php _e( 'Import settings', 'mandarincms-seo' ); ?>"/>
</form>
