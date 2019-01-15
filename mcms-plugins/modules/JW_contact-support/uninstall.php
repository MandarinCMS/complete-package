<?php

if ( ! defined( 'MCMS_UNINSTALL_MODULE' ) ) {
	exit();
}

function mcmscf7_delete_module() {
	global $mcmsdb;

	delete_option( 'mcmscf7' );

	$posts = get_posts(
		array(
			'numberposts' => -1,
			'post_type' => 'mcmscf7_contact_form',
			'post_status' => 'any',
		)
	);

	foreach ( $posts as $post ) {
		mcms_delete_post( $post->ID, true );
	}

	$mcmsdb->query( sprintf( "DROP TABLE IF EXISTS %s",
		$mcmsdb->prefix . 'contact_form_7' ) );
}

mcmscf7_delete_module();
