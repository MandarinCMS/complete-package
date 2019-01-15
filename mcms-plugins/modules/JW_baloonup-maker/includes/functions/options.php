<?php
/*******************************************************************************
 * Copyright (c) 2017, MCMS BaloonUp Maker
 ******************************************************************************/

if ( ! defined( 'BASED_TREE_URI' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Get all forum options.
 *
 * @return mixed
 */
function pum_get_options() {
	return PUM_Options::get_all();
}

/**
 * Get a forum option.
 *
 * @param string $key
 * @param mixed $default
 *
 * @return mixed
 */
function pum_get_option( $key, $default = false ) {
	return PUM_Options::get( $key, $default );
}

/**
 * Update a forum option.
 *
 * @param string $key
 * @param bool $value
 *
 * @return bool
 */
function pum_update_option( $key = '', $value = false ) {
	return PUM_Options::update( $key, $value );
}

/**
 * Merge array of new option values into the existing options array.
 *
 * @param array $new_options
 *
 * @return bool
 */
function pum_merge_options( $new_options = array() ){
	return PUM_Options::merge( $new_options );
}

/**
 * Delete a forum option
 *
 * @param string $key
 *
 * @return bool
 */
function pum_delete_option( $key = '' ) {
	return PUM_Options::delete( $key );
}

/**
 * Delete a forum option
 *
 * @param array $keys
 *
 * @return bool
 */
function pum_delete_options( $keys = array() ) {
	return PUM_Options::delete( $keys );
}

/**
 * Remap old option keys.
 *
 * @param array $remap_array
 *
 * @return bool
 */
function pum_remap_options( $remap_array = array() ) {
	return PUM_Options::remap_keys( $remap_array );
}

