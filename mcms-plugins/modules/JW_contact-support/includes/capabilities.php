<?php

add_filter( 'map_meta_cap', 'mcmscf7_map_meta_cap', 10, 4 );

function mcmscf7_map_meta_cap( $caps, $cap, $user_id, $args ) {
	$meta_caps = array(
		'mcmscf7_edit_contact_form' => MCMSCF7_ADMIN_READ_WRITE_CAPABILITY,
		'mcmscf7_edit_contact_forms' => MCMSCF7_ADMIN_READ_WRITE_CAPABILITY,
		'mcmscf7_read_contact_forms' => MCMSCF7_ADMIN_READ_CAPABILITY,
		'mcmscf7_delete_contact_form' => MCMSCF7_ADMIN_READ_WRITE_CAPABILITY,
		'mcmscf7_manage_integration' => 'manage_options',
		'mcmscf7_submit' => 'read',
	);

	$meta_caps = apply_filters( 'mcmscf7_map_meta_cap', $meta_caps );

	$caps = array_diff( $caps, array_keys( $meta_caps ) );

	if ( isset( $meta_caps[$cap] ) ) {
		$caps[] = $meta_caps[$cap];
	}

	return $caps;
}
