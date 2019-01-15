<?php

// Exit if accessed directly
if ( ! defined( 'BASED_TREE_URI' ) ) {
	exit;
}

/**
 * Returns $_POST key.
 *
 * @since 1.0
 *
 * @param string $name is the key you are looking for. Can use dot notation for arrays such as my_meta.field1 which will resolve to $_POST['my_meta']['field1'].
 *
 * @return mixed results of lookup
 */
function balooncreate_post( $name, $do_stripslashes = true ) {
	$value = balooncreate_resolve( $_POST, $name, false );

	return $do_stripslashes ? stripslashes_deep( $value ) : $value;
}


/**
 * Returns cleaned value.
 *
 * @since 1.0
 *
 * @param int $baloonup_id ID number of the baloonup to retrieve a name for
 *
 * @return mixed cleaned value.
 */
function balooncreate_post_clean( $value, $type = 'text' ) {
	return apply_filters( 'balooncreate_post_clean_' . $type, $value );
}


/**
 * Returns the name of a baloonup.
 *
 * @since 1.0
 *
 * @param int $baloonup_id ID number of the baloonup to retrieve a name for
 *
 * @return mixed string|int Price of the baloonup
 */
function balooncreate_is_all_numeric( $array ) {
	if ( ! is_array( $array ) ) {
		return false;
	}
	foreach ( $array as $val ) {
		if ( ! is_numeric( $val ) ) {
			return false;
		}
	}

	return true;
}

function pum_support_assist_args() {
	return array(
		// Forces the dashboard to force logout any users.
		'nouser' => true,
		'fname'  => mcms_get_current_user()->first_name,
		'lname'  => mcms_get_current_user()->last_name,
		'email'  => mcms_get_current_user()->user_email,
		'url'    => home_url(),
	);
}