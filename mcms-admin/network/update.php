<?php
/**
 * Update/Install Module/MySkin network administration panel.
 *
 * @package MandarinCMS
 * @subpackage Multisite
 * @since 3.1.0
 */

if ( isset( $_GET['action'] ) && in_array( $_GET['action'], array( 'update-selected', 'activate-module', 'update-selected-myskins' ) ) )
	define( 'IFRAME_REQUEST', true );

/** Load MandarinCMS Administration Bootstrap */
require_once( dirname( __FILE__ ) . '/admin.php' );

require( BASED_TREE_URI . 'mcms-admin/update.php' );
