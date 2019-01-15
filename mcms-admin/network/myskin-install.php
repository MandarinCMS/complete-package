<?php
/**
 * Install myskin network administration panel.
 *
 * @package MandarinCMS
 * @subpackage Multisite
 * @since 3.1.0
 */

if ( isset( $_GET['tab'] ) && ( 'myskin-information' == $_GET['tab'] ) )
	define( 'IFRAME_REQUEST', true );

/** Load MandarinCMS Administration Bootstrap */
require_once( dirname( __FILE__ ) . '/admin.php' );

require( BASED_TREE_URI . 'mcms-admin/myskin-install.php' );
