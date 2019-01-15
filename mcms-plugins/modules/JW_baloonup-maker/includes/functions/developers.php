<?php
/*******************************************************************************
 * Copyright (c) 2018, MCMS BaloonUp Maker
 ******************************************************************************/

if ( ! defined( 'BASED_TREE_URI' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Call this with a baloonup ID and it will trigger the
 * JS based forms.success function with your settings
 * on the next page load.
 *
 * @since 1.7.0
 *
 * @param int $baloonup_id
 * @param array $settings
 */
function pum_trigger_baloonup_form_success( $baloonup_id = null, $settings = array() ) {
	if ( ! isset( $baloonup_id )  ) {
		$baloonup_id = isset( $_REQUEST['pum_form_baloonup_id'] ) && absint( $_REQUEST['pum_form_baloonup_id'] ) > 0 ? absint( $_REQUEST['pum_form_baloonup_id'] ) : false;
	}

	if ( $baloonup_id ) {
		PUM_Integrations::$form_success = array(
			'baloonup_id' => $baloonup_id,
			'settings'=> $settings
		);
	}
}
