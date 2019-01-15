<?php
/**
 * MandarinCMS Generic Request (POST/GET) Handler
 *
 * Intended for form submission handling in myskins and modules.
 *
 * @package MandarinCMS
 * @subpackage Administration
 */

/** We are located in MandarinCMS Administration Screens */
if ( ! defined( 'MCMS_ADMIN' ) ) {
	define( 'MCMS_ADMIN', true );
}

if ( defined('BASED_TREE_URI') )
	require_once(BASED_TREE_URI . 'bootstrap.php');
else
	require_once( dirname( dirname( __FILE__ ) ) . '/bootstrap.php' );

/** Allow for cross-domain requests (from the front end). */
send_origin_headers();

require_once(BASED_TREE_URI . 'mcms-admin/includes/admin.php');

nocache_headers();

/** This action is documented in mcms-admin/admin.php */
do_action( 'admin_init' );

$action = empty( $_REQUEST['action'] ) ? '' : $_REQUEST['action'];

if ( ! mcms_validate_auth_cookie() ) {
	if ( empty( $action ) ) {
		/**
		 * Fires on a non-authenticated admin post request where no action was supplied.
		 *
		 * @since 2.6.0
		 */
		do_action( 'admin_post_nopriv' );
	} else {
		/**
		 * Fires on a non-authenticated admin post request for the given action.
		 *
		 * The dynamic portion of the hook name, `$action`, refers to the given
		 * request action.
		 *
		 * @since 2.6.0
		 */
		do_action( "admin_post_nopriv_{$action}" );
	}
} else {
	if ( empty( $action ) ) {
		/**
		 * Fires on an authenticated admin post request where no action was supplied.
		 *
		 * @since 2.6.0
		 */
		do_action( 'admin_post' );
	} else {
		/**
		 * Fires on an authenticated admin post request for the given action.
		 *
		 * The dynamic portion of the hook name, `$action`, refers to the given
		 * request action.
		 *
		 * @since 2.6.0
		 */
		do_action( "admin_post_{$action}" );
	}
}
