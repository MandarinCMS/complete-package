<?php
/**
 * Update/Install Module/MySkin administration panel.
 *
 * @package MandarinCMS
 * @subpackage Administration
 */

if ( ! defined( 'IFRAME_REQUEST' ) && isset( $_GET['action'] ) && in_array( $_GET['action'], array( 'update-selected', 'activate-module', 'update-selected-myskins' ) ) )
	define( 'IFRAME_REQUEST', true );

/** MandarinCMS Administration Bootstrap */
require_once( dirname( __FILE__ ) . '/admin.php' );

include_once( BASED_TREE_URI . 'mcms-admin/includes/class-mcms-upgrader.php' );

if ( isset($_GET['action']) ) {
	$module = isset($_REQUEST['module']) ? trim($_REQUEST['module']) : '';
	$myskin = isset($_REQUEST['myskin']) ? urldecode($_REQUEST['myskin']) : '';
	$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

	if ( 'update-selected' == $action ) {
		if ( ! current_user_can( 'update_modules' ) )
			mcms_die( __( 'Sorry, you are not allowed to update modules for this site.' ) );

		check_admin_referer( 'bulk-update-modules' );

		if ( isset( $_GET['modules'] ) )
			$modules = explode( ',', stripslashes($_GET['modules']) );
		elseif ( isset( $_POST['checked'] ) )
			$modules = (array) $_POST['checked'];
		else
			$modules = array();

		$modules = array_map('urldecode', $modules);

		$url = 'update.php?action=update-selected&amp;modules=' . urlencode(implode(',', $modules));
		$nonce = 'bulk-update-modules';

		mcms_enqueue_script( 'updates' );
		iframe_header();

		$upgrader = new Module_Upgrader( new Bulk_Module_Upgrader_Skin( compact( 'nonce', 'url' ) ) );
		$upgrader->bulk_upgrade( $modules );

		iframe_footer();

	} elseif ( 'upgrade-module' == $action ) {
		if ( ! current_user_can('update_modules') )
			mcms_die(__('Sorry, you are not allowed to update modules for this site.'));

		check_admin_referer('upgrade-module_' . $module);

		$title = __('Update Module');
		$parent_file = 'modules.php';
		$submenu_file = 'modules.php';

		mcms_enqueue_script( 'updates' );
		require_once(BASED_TREE_URI . 'mcms-admin/admin-header.php');

		$nonce = 'upgrade-module_' . $module;
		$url = 'update.php?action=upgrade-module&module=' . urlencode( $module );

		$upgrader = new Module_Upgrader( new Module_Upgrader_Skin( compact('title', 'nonce', 'url', 'module') ) );
		$upgrader->upgrade($module);

		include(BASED_TREE_URI . 'mcms-admin/admin-footer.php');

	} elseif ('activate-module' == $action ) {
		if ( ! current_user_can('update_modules') )
			mcms_die(__('Sorry, you are not allowed to update modules for this site.'));

		check_admin_referer('activate-module_' . $module);
		if ( ! isset($_GET['failure']) && ! isset($_GET['success']) ) {
			mcms_redirect( admin_url('update.php?action=activate-module&failure=true&module=' . urlencode( $module ) . '&_mcmsnonce=' . $_GET['_mcmsnonce']) );
			activate_module( $module, '', ! empty( $_GET['networkwide'] ), true );
			mcms_redirect( admin_url('update.php?action=activate-module&success=true&module=' . urlencode( $module ) . '&_mcmsnonce=' . $_GET['_mcmsnonce']) );
			die();
		}
		iframe_header( __('Module Reactivation'), true );
		if ( isset($_GET['success']) )
			echo '<p>' . __('Module reactivated successfully.') . '</p>';

		if ( isset($_GET['failure']) ){
			echo '<p>' . __('Module failed to reactivate due to a fatal error.') . '</p>';

			error_reporting( E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_ERROR | E_WARNING | E_PARSE | E_USER_ERROR | E_USER_WARNING | E_RECOVERABLE_ERROR );
			@ini_set('display_errors', true); //Ensure that Fatal errors are displayed.
			mcms_register_module_realpath( MCMS_PLUGIN_DIR . '/' . $module );
			include( MCMS_PLUGIN_DIR . '/' . $module );
		}
		iframe_footer();
	} elseif ( 'install-module' == $action ) {

		if ( ! current_user_can('install_modules') )
			mcms_die( __( 'Sorry, you are not allowed to install modules on this site.' ) );

		include_once( BASED_TREE_URI . 'mcms-admin/includes/module-install.php' ); //for modules_api..

		check_admin_referer( 'install-module_' . $module );
		$api = modules_api( 'module_information', array(
			'slug' => $module,
			'fields' => array(
				'short_description' => false,
				'sections' => false,
				'requires' => false,
				'rating' => false,
				'ratings' => false,
				'downloaded' => false,
				'last_updated' => false,
				'added' => false,
				'tags' => false,
				'compatibility' => false,
				'homepage' => false,
				'donate_link' => false,
			),
		) );

		if ( is_mcms_error( $api ) ) {
	 		mcms_die( $api );
		}

		$title = __('Module Installation');
		$parent_file = 'modules.php';
		$submenu_file = 'module-install.php';
		require_once(BASED_TREE_URI . 'mcms-admin/admin-header.php');

		$title = sprintf( __('Installing Module: %s'), $api->name . ' ' . $api->version );
		$nonce = 'install-module_' . $module;
		$url = 'update.php?action=install-module&module=' . urlencode( $module );
		if ( isset($_GET['from']) )
			$url .= '&from=' . urlencode(stripslashes($_GET['from']));

		$type = 'web'; //Install module type, From Web or an Upload.

		$upgrader = new Module_Upgrader( new Module_Installer_Skin( compact('title', 'url', 'nonce', 'module', 'api') ) );
		$upgrader->install($api->download_link);

		include(BASED_TREE_URI . 'mcms-admin/admin-footer.php');

	} elseif ( 'upload-module' == $action ) {

		if ( ! current_user_can( 'upload_modules' ) ) {
			mcms_die( __( 'Sorry, you are not allowed to install modules on this site.' ) );
		}

		check_admin_referer('module-upload');

		$file_upload = new File_Upload_Upgrader('modulezip', 'package');

		$title = __('Upload Module');
		$parent_file = 'modules.php';
		$submenu_file = 'module-install.php';
		require_once(BASED_TREE_URI . 'mcms-admin/admin-header.php');

		$title = sprintf( __('Installing Module from uploaded file: %s'), esc_html( basename( $file_upload->filename ) ) );
		$nonce = 'module-upload';
		$url = add_query_arg(array('package' => $file_upload->id), 'update.php?action=upload-module');
		$type = 'upload'; //Install module type, From Web or an Upload.

		$upgrader = new Module_Upgrader( new Module_Installer_Skin( compact('type', 'title', 'nonce', 'url') ) );
		$result = $upgrader->install( $file_upload->package );

		if ( $result || is_mcms_error($result) )
			$file_upload->cleanup();

		include(BASED_TREE_URI . 'mcms-admin/admin-footer.php');

	} elseif ( 'upgrade-myskin' == $action ) {

		if ( ! current_user_can('update_myskins') )
			mcms_die(__('Sorry, you are not allowed to update myskins for this site.'));

		check_admin_referer('upgrade-myskin_' . $myskin);

		mcms_enqueue_script( 'updates' );

		$title = __('Update MySkin');
		$parent_file = 'myskins.php';
		$submenu_file = 'myskins.php';
		require_once(BASED_TREE_URI . 'mcms-admin/admin-header.php');

		$nonce = 'upgrade-myskin_' . $myskin;
		$url = 'update.php?action=upgrade-myskin&myskin=' . urlencode( $myskin );

		$upgrader = new MySkin_Upgrader( new MySkin_Upgrader_Skin( compact('title', 'nonce', 'url', 'myskin') ) );
		$upgrader->upgrade($myskin);

		include(BASED_TREE_URI . 'mcms-admin/admin-footer.php');
	} elseif ( 'update-selected-myskins' == $action ) {
		if ( ! current_user_can( 'update_myskins' ) )
			mcms_die( __( 'Sorry, you are not allowed to update myskins for this site.' ) );

		check_admin_referer( 'bulk-update-myskins' );

		if ( isset( $_GET['myskins'] ) )
			$myskins = explode( ',', stripslashes($_GET['myskins']) );
		elseif ( isset( $_POST['checked'] ) )
			$myskins = (array) $_POST['checked'];
		else
			$myskins = array();

		$myskins = array_map('urldecode', $myskins);

		$url = 'update.php?action=update-selected-myskins&amp;myskins=' . urlencode(implode(',', $myskins));
		$nonce = 'bulk-update-myskins';

		mcms_enqueue_script( 'updates' );
		iframe_header();

		$upgrader = new MySkin_Upgrader( new Bulk_MySkin_Upgrader_Skin( compact( 'nonce', 'url' ) ) );
		$upgrader->bulk_upgrade( $myskins );

		iframe_footer();
	} elseif ( 'install-myskin' == $action ) {

		if ( ! current_user_can('install_myskins') )
			mcms_die( __( 'Sorry, you are not allowed to install myskins on this site.' ) );

		include_once( BASED_TREE_URI . 'mcms-admin/includes/class-mcms-upgrader.php' ); //for myskins_api..

		check_admin_referer( 'install-myskin_' . $myskin );
		$api = myskins_api('myskin_information', array('slug' => $myskin, 'fields' => array('sections' => false, 'tags' => false) ) ); //Save on a bit of bandwidth.

		if ( is_mcms_error( $api ) ) {
			mcms_die( $api );
		}

		$title = __('Install MySkins');
		$parent_file = 'myskins.php';
		$submenu_file = 'myskins.php';
		require_once(BASED_TREE_URI . 'mcms-admin/admin-header.php');

		$title = sprintf( __('Installing MySkin: %s'), $api->name . ' ' . $api->version );
		$nonce = 'install-myskin_' . $myskin;
		$url = 'update.php?action=install-myskin&myskin=' . urlencode( $myskin );
		$type = 'web'; //Install myskin type, From Web or an Upload.

		$upgrader = new MySkin_Upgrader( new MySkin_Installer_Skin( compact('title', 'url', 'nonce', 'module', 'api') ) );
		$upgrader->install($api->download_link);

		include(BASED_TREE_URI . 'mcms-admin/admin-footer.php');

	} elseif ( 'upload-myskin' == $action ) {

		if ( ! current_user_can( 'upload_myskins' ) ) {
			mcms_die( __( 'Sorry, you are not allowed to install myskins on this site.' ) );
		}

		check_admin_referer('myskin-upload');

		$file_upload = new File_Upload_Upgrader('myskinzip', 'package');

		$title = __('Upload MySkin');
		$parent_file = 'myskins.php';
		$submenu_file = 'myskin-install.php';

		require_once(BASED_TREE_URI . 'mcms-admin/admin-header.php');

		$title = sprintf( __('Installing MySkin from uploaded file: %s'), esc_html( basename( $file_upload->filename ) ) );
		$nonce = 'myskin-upload';
		$url = add_query_arg(array('package' => $file_upload->id), 'update.php?action=upload-myskin');
		$type = 'upload'; //Install module type, From Web or an Upload.

		$upgrader = new MySkin_Upgrader( new MySkin_Installer_Skin( compact('type', 'title', 'nonce', 'url') ) );
		$result = $upgrader->install( $file_upload->package );

		if ( $result || is_mcms_error($result) )
			$file_upload->cleanup();

		include(BASED_TREE_URI . 'mcms-admin/admin-footer.php');

	} else {
		/**
		 * Fires when a custom module or myskin update request is received.
		 *
		 * The dynamic portion of the hook name, `$action`, refers to the action
		 * provided in the request for mcms-admin/update.php. Can be used to
		 * provide custom update functionality for myskins and modules.
		 *
		 * @since 2.8.0
		 */
		do_action( "update-custom_{$action}" );
	}
}
