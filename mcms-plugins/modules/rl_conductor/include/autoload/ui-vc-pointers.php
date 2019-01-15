<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

global $vc_default_pointers, $vc_pointers;
$vc_default_pointers = (array) apply_filters( 'vc_pointers_list',
	array(
		'vc_grid_item',
		'vc_pointers_backend_editor',
		'vc_pointers_frontend_editor',
	)
);
if ( is_admin() ) {
	add_action( 'admin_enqueue_scripts', 'vc_pointer_load', 1000 );
}

function vc_pointer_load() {
	global $vc_pointers;
	// Don't run on MCMS < 3.3
	if ( get_bloginfo( 'version' ) < '3.3' ) {
		return;
	}

	$screen = get_current_screen();
	$screen_id = $screen->id;

	// Get pointers for this screen
	$pointers = apply_filters( 'vc-ui-pointers', array() );
	$pointers = apply_filters( 'vc_ui-pointers-' . $screen_id, $pointers );

	if ( ! $pointers || ! is_array( $pointers ) ) {
		return;
	}

	// Get dismissed pointers
	$dismissed = explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_mcms_pointers', true ) );
	$vc_pointers = array( 'pointers' => array() );

	// Check pointers and remove dismissed ones.
	foreach ( $pointers as $pointer_id => $pointer ) {

		// Sanity check
		if ( in_array( $pointer_id, $dismissed ) || empty( $pointer ) || empty( $pointer_id ) || empty( $pointer['name'] ) ) {
			continue;
		}

		$pointer['pointer_id'] = $pointer_id;

		// Add the pointer to $valid_pointers array

		$vc_pointers['pointers'][] = $pointer;
	}

	// No valid pointers? Stop here.
	if ( empty( $vc_pointers['pointers'] ) ) {
		return;
	}
	mcms_enqueue_style( 'mcms-pointer' );
	mcms_enqueue_script( 'mcms-pointer' );
	// messages
	$vc_pointers['texts'] = array(
		'finish' => __( 'Finish', 'rl_conductor' ),
		'next' => __( 'Next', 'rl_conductor' ),
		'prev' => __( 'Prev', 'rl_conductor' ),
	);

	// Add pointer options to script.
	mcms_localize_script( 'mcms-pointer', 'vcPointer', $vc_pointers );
}

/**
 * Remove Vc pointers keys to show Tour markers again.
 * @sine 4.5
 */
function vc_pointer_reset() {
	global $vc_default_pointers;
	vc_user_access()
		->checkAdminNonce()
		->validateDie()
		->mcmsAny( 'manage_options' )
		->validateDie()
		->part( 'settings' )
		->can( 'vc-general-tab' )
		->validateDie();

	$pointers = (array) apply_filters( 'vc_pointers_list', $vc_default_pointers );
	$prev_meta_value = get_user_meta( get_current_user_id(), 'dismissed_mcms_pointers', true );
	$dismissed = explode( ',', (string) $prev_meta_value );
	if ( count( $dismissed ) > 0 && count( $pointers ) ) {
		$meta_value = implode( ',', array_diff( $dismissed, $pointers ) );
		update_user_meta( get_current_user_id(), 'dismissed_mcms_pointers', $meta_value, $prev_meta_value );
	}

	mcms_send_json( array( 'success' => true ) );
}

/**
 * Reset tour guid
 * @return bool
 */
function vc_pointers_is_dismissed() {
	global $vc_default_pointers;
	$pointers = (array) apply_filters( 'vc_pointers_list', $vc_default_pointers );
	$prev_meta_value = get_user_meta( get_current_user_id(), 'dismissed_mcms_pointers', true );
	$dismissed = explode( ',', (string) $prev_meta_value );

	return count( array_diff( $dismissed, $pointers ) ) < count( $dismissed );
}

add_action( 'mcms_ajax_vc_pointer_reset', 'vc_pointer_reset' );
