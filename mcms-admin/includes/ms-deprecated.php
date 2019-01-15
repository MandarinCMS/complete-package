<?php
/**
 * Multisite: Deprecated admin functions from past versions and MandarinCMS MU
 *
 * These functions should not be used and will be removed in a later version.
 * It is suggested to use for the alternatives instead when available.
 *
 * @package MandarinCMS
 * @subpackage Deprecated
 * @since 3.0.0
 */

/**
 * Outputs the MCMSMU menu.
 *
 * @deprecated 3.0.0
 */
function mcmsmu_menu() {
	_deprecated_function(__FUNCTION__, '3.0.0' );
	// Deprecated. See #11763.
}

/**
 * Determines if the available space defined by the admin has been exceeded by the user.
 *
 * @deprecated 3.0.0 Use is_upload_space_available()
 * @see is_upload_space_available()
 */
function mcmsmu_checkAvailableSpace() {
	_deprecated_function(__FUNCTION__, '3.0.0', 'is_upload_space_available()' );

	if ( !is_upload_space_available() )
		mcms_die( __('Sorry, you must delete files before you can upload any more.') );
}

/**
 * MCMSMU options.
 *
 * @deprecated 3.0.0
 */
function mu_options( $options ) {
	_deprecated_function(__FUNCTION__, '3.0.0' );
	return $options;
}

/**
 * Deprecated functionality for activating a network-only module.
 *
 * @deprecated 3.0.0 Use activate_module()
 * @see activate_module()
 */
function activate_sitewide_module() {
	_deprecated_function(__FUNCTION__, '3.0.0', 'activate_module()' );
	return false;
}

/**
 * Deprecated functionality for deactivating a network-only module.
 *
 * @deprecated 3.0.0 Use deactivate_module()
 * @see deactivate_module()
 */
function deactivate_sitewide_module( $module = false ) {
	_deprecated_function(__FUNCTION__, '3.0.0', 'deactivate_module()' );
}

/**
 * Deprecated functionality for determining if the current module is network-only.
 *
 * @deprecated 3.0.0 Use is_network_only_module()
 * @see is_network_only_module()
 */
function is_mcmsmu_sitewide_module( $file ) {
	_deprecated_function(__FUNCTION__, '3.0.0', 'is_network_only_module()' );
	return is_network_only_module( $file );
}

/**
 * Deprecated functionality for getting myskins network-enabled myskins.
 *
 * @deprecated 3.4.0 Use MCMS_MySkin::get_allowed_on_network()
 * @see MCMS_MySkin::get_allowed_on_network()
 */
function get_site_allowed_myskins() {
	_deprecated_function( __FUNCTION__, '3.4.0', 'MCMS_MySkin::get_allowed_on_network()' );
	return array_map( 'intval', MCMS_MySkin::get_allowed_on_network() );
}

/**
 * Deprecated functionality for getting myskins allowed on a specific site.
 *
 * @deprecated 3.4.0 Use MCMS_MySkin::get_allowed_on_site()
 * @see MCMS_MySkin::get_allowed_on_site()
 */
function mcmsmu_get_blog_allowedmyskins( $blog_id = 0 ) {
	_deprecated_function( __FUNCTION__, '3.4.0', 'MCMS_MySkin::get_allowed_on_site()' );
	return array_map( 'intval', MCMS_MySkin::get_allowed_on_site( $blog_id ) );
}

/**
 * Deprecated functionality for determining whether a file is deprecated.
 *
 * @deprecated 3.5.0
 */
function ms_deprecated_blogs_file() {}
