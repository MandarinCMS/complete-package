<?php

// Exit if accessed directly
if ( ! defined( 'BASED_TREE_URI' ) ) {
	exit;
}

function balooncreate_optin_ajax_call() {
	// Check our nonce and make sure it's correct.
	check_ajax_referer( POPMAKE_NONCE, POPMAKE_NONCE );
	if ( isset( $_REQUEST['optin_dismiss'] ) ) {
		$optin = $_REQUEST['optin_name'];
		$type  = $_REQUEST['optin_type'];
		if ( $type == 'user' ) {
			update_user_meta( get_current_user_id(), '_balooncreate_dismiss_optin_' . $optin, true );
		} else {
			update_option( '_balooncreate_dismiss_optin_' . $optin, true );
		}
		$response['success'] = true;
	}
	$response['new_nonce'] = mcms_create_nonce( POPMAKE_NONCE );
	echo mcms_json_encode( $response );
	die();
}

add_action( 'mcms_ajax_balooncreate_optin', 'balooncreate_optin_ajax_call' );
add_action( 'mcms_ajax_nopriv_balooncreate_optin', 'balooncreate_optin_ajax_call' );


function balooncreate_baloonup_preview_content_ajax_call() {
	// Check our nonce and make sure it's correct.
	check_ajax_referer( POPMAKE_NONCE, POPMAKE_NONCE );
	if ( isset( $_REQUEST['baloonup_content'] ) ) {
		remove_filter( 'the_baloonup_content', 'balooncreate_baloonup_content_container', 10000 );
		$response['content'] = stripslashes( apply_filters( 'the_baloonup_content', $_REQUEST['baloonup_content'], $_REQUEST['baloonup_id'] ) );
		$response['success'] = true;
	}
	$response['new_nonce'] = mcms_create_nonce( POPMAKE_NONCE );
	header( "Expires: Mon, 26 Jul 1997 05:00:00 GMT" );
	header( "Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . "GMT" );
	header( "Cache-Control: no-cache, must-revalidate" );
	header( "Pragma: no-cache" );
	header( "Content-type: text/x-json" );
	echo mcms_json_encode( $response );
	die();
}

add_action( 'mcms_ajax_balooncreate_baloonup_preview_content', 'balooncreate_baloonup_preview_content_ajax_call' );
add_action( 'mcms_ajax_nopriv_balooncreate_baloonup_preview_content', 'balooncreate_baloonup_preview_content_ajax_call' );
