<?php
/**
 * @package MCMSSEO\Admin
 */

if ( ! defined( 'MCMSSEO_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

$robots_file    = get_home_path() . 'robots.txt';
$ht_access_file = get_home_path() . '.htaccess';

if ( isset( $_POST['create_robots'] ) ) {
	if ( ! current_user_can( 'edit_files' ) ) {
		die( __( 'You cannot create a robots.txt file.', 'mandarincms-seo' ) );
	}

	check_admin_referer( 'mcmsseo_create_robots' );

	ob_start();
	error_reporting( 0 );
	do_robots();
	$robots_content = ob_get_clean();

	$f = fopen( $robots_file, 'x' );
	fwrite( $f, $robots_content );
}

if ( isset( $_POST['submitrobots'] ) ) {
	if ( ! current_user_can( 'edit_files' ) ) {
		die( __( 'You cannot edit the robots.txt file.', 'mandarincms-seo' ) );
	}

	check_admin_referer( 'mcmsseo-robotstxt' );

	if ( file_exists( $robots_file ) ) {
		$robotsnew = stripslashes( $_POST['robotsnew'] );
		if ( is_writable( $robots_file ) ) {
			$f = fopen( $robots_file, 'w+' );
			fwrite( $f, $robotsnew );
			fclose( $f );
			$msg = __( 'Updated Robots.txt', 'mandarincms-seo' );
		}
	}
}

if ( isset( $_POST['submithtaccess'] ) ) {
	if ( ! current_user_can( 'edit_files' ) ) {
		die( __( 'You cannot edit the .htaccess file.', 'mandarincms-seo' ) );
	}

	check_admin_referer( 'mcmsseo-htaccess' );

	if ( file_exists( $ht_access_file ) ) {
		$ht_access_new = stripslashes( $_POST['htaccessnew'] );
		if ( is_writeable( $ht_access_file ) ) {
			$f = fopen( $ht_access_file, 'w+' );
			fwrite( $f, $ht_access_new );
			fclose( $f );
		}
	}
}

if ( isset( $msg ) && ! empty( $msg ) ) {
	echo '<div id="message" class="updated fade"><p>', esc_html( $msg ), '</p></div>';
}

if ( is_multisite() ) {
	$action_url = network_admin_url( 'admin.php?page=mcmsseo_files' );
}
else {
	$action_url = admin_url( 'admin.php?page=mcmsseo_tools&tool=file-editor' );
}

echo '<br><br>';
$helpcenter_tab = new MCMSSEO_Option_Tab( 'bulk-editor', 'Bulk editor',
	array( 'video_url' => 'https://jiiworks.net/screencast-tools-file-editor' ) );

$helpcenter = new MCMSSEO_Help_Center( 'bulk-editor', $helpcenter_tab );
$helpcenter->output_help_center();

echo '<h2>', __( 'Robots.txt', 'mandarincms-seo' ), '</h2>';


if ( ! file_exists( $robots_file ) ) {
	if ( is_writable( get_home_path() ) ) {
		echo '<form action="', esc_url( $action_url ), '" method="post" id="robotstxtcreateform">';
		mcms_nonce_field( 'mcmsseo_create_robots', '_mcmsnonce', true, true );
		echo '<p>', __( 'You don\'t have a robots.txt file, create one here:', 'mandarincms-seo' ), '</p>';
		echo '<input type="submit" class="button" name="create_robots" value="', __( 'Create robots.txt file', 'mandarincms-seo' ), '">';
		echo '</form>';
	}
	else {
		echo '<p>', __( 'If you had a robots.txt file and it was editable, you could edit it from here.', 'mandarincms-seo' ), '</p>';
	}
}
else {
	$f = fopen( $robots_file, 'r' );

	$content = '';
	if ( filesize( $robots_file ) > 0 ) {
		$content = fread( $f, filesize( $robots_file ) );
	}
	$robots_txt_content = esc_textarea( $content );

	if ( ! is_writable( $robots_file ) ) {
		echo '<p><em>', __( 'If your robots.txt were writable, you could edit it from here.', 'mandarincms-seo' ), '</em></p>';
		echo '<textarea class="large-text code" disabled="disabled" rows="15" name="robotsnew">', $robots_txt_content, '</textarea><br/>';
	}
	else {
		echo '<form action="', esc_url( $action_url ), '" method="post" id="robotstxtform">';
		mcms_nonce_field( 'mcmsseo-robotstxt', '_mcmsnonce', true, true );
		echo '<p><label for="robotsnew" class="ultimatum-inline-label">', __( 'Edit the content of your robots.txt:', 'mandarincms-seo' ), '</label></p>';
		echo '<textarea class="large-text code" rows="15" name="robotsnew" id="robotsnew">', $robots_txt_content, '</textarea><br/>';
		echo '<div class="submit"><input class="button" type="submit" name="submitrobots" value="', __( 'Save changes to Robots.txt', 'mandarincms-seo' ), '" /></div>';
		echo '</form>';
	}
}
if ( ( isset( $_SERVER['SERVER_SOFTWARE'] ) && stristr( $_SERVER['SERVER_SOFTWARE'], 'nginx' ) === false ) ) {

	echo '<h2>', __( '.htaccess file', 'mandarincms-seo' ), '</h2>';

	if ( file_exists( $ht_access_file ) ) {
		$f = fopen( $ht_access_file, 'r' );

		$contentht = '';
		if ( filesize( $ht_access_file ) > 0 ) {
			$contentht = fread( $f, filesize( $ht_access_file ) );
		}
		$contentht = esc_textarea( $contentht );

		if ( ! is_writable( $ht_access_file ) ) {
			echo '<p><em>', __( 'If your .htaccess were writable, you could edit it from here.', 'mandarincms-seo' ), '</em></p>';
			echo '<textarea class="large-text code" disabled="disabled" rows="15" name="robotsnew">', $contentht, '</textarea><br/>';
		}
		else {
			echo '<form action="', esc_url( $action_url ), '" method="post" id="htaccessform">';
			mcms_nonce_field( 'mcmsseo-htaccess', '_mcmsnonce', true, true );
			echo '<p><label for="htaccessnew" class="ultimatum-inline-label">', __( 'Edit the content of your .htaccess:', 'mandarincms-seo' ), '</label></p>';
			echo '<textarea class="large-text code" rows="15" name="htaccessnew" id="htaccessnew">', $contentht, '</textarea><br/>';
			echo '<div class="submit"><input class="button" type="submit" name="submithtaccess" value="', __( 'Save changes to .htaccess', 'mandarincms-seo' ), '" /></div>';
			echo '</form>';
		}
	}
	else {
		echo '<p>', __( 'If you had a .htaccess file and it was editable, you could edit it from here.', 'mandarincms-seo' ), '</p>';
	}
}
