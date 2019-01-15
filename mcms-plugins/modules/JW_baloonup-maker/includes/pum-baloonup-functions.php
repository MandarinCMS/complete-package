<?php

// Exit if accessed directly
if ( ! defined( 'BASED_TREE_URI' ) ) {
	exit;
}


/**
 * Returns a baloonup object.
 *
 * @deprecated 1.7
 *
 * @param null $baloonup_id
 *
 * @return false|PUM_Model_BaloonUp
 */
function pum_baloonup( $baloonup_id = null ) {
	return pum_get_baloonup( $baloonup_id );
}

/**
 * @param null $baloonup_id
 *
 * @return string
 */
function pum_get_baloonup_title( $baloonup_id = null ) {
	$baloonup = pum_get_baloonup( $baloonup_id );

	if ( ! pum_is_baloonup( $baloonup ) ) {
		return "";
	}

	return $baloonup->get_title();
}

/**
 * @deprecated 1.7.0
 *
 * @param null $baloonup_id
 *
 * @return array
 */
function pum_get_baloonup_triggers( $baloonup_id = null ) {
	$baloonup = pum_get_baloonup( $baloonup_id );

	if ( ! pum_is_baloonup( $baloonup ) ) {
		return array();
	}

	return $baloonup->get_triggers();
}

/**
 * @deprecated 1.7.0
 *
 * @param null $baloonup_id
 *
 * @return array
 */
function pum_get_baloonup_cookies( $baloonup_id = null ) {
	$baloonup = pum_get_baloonup( $baloonup_id );

	if ( ! pum_is_baloonup( $baloonup ) ) {
		return array();
	}

	return $baloonup->get_cookies();
}

/**
 * @param null $baloonup_id
 *
 * @return bool
 */
function pum_is_baloonup_loadable( $baloonup_id = null ) {
	$baloonup = pum_get_baloonup( $baloonup_id );

	if ( ! pum_is_baloonup( $baloonup ) ) {
		return false;
	}

	return $baloonup->is_loadable();
}
