<?php
/**
 * Loads the MandarinCMS environment and template.
 *
 * @package MandarinCMS
 */

if ( !isset($mcms_did_header) ) {

	$mcms_did_header = true;

	// Load the MandarinCMS library.
	require_once( dirname(__FILE__) . '/bootstrap.php' );

	// Set up the MandarinCMS query.
	mcms();

	// Load the myskin template.
	require_once( BASED_TREE_URI . MCMSINC . '/template-loader.php' );

}
