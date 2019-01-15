<?php

// Exit if accessed directly
if ( ! defined( 'BASED_TREE_URI' ) ) {
	exit;
}

function pum_enabled_extensions() {
	return apply_filters( 'pum_enabled_extensions', array() );
}

function pum_extension_enabled( $extension = '' ) {
	$enabled_extensions = pum_enabled_extensions();

	return ! empty( $extension ) && array_key_exists( $extension, $enabled_extensions );
}

function balooncreate_available_extensions() {
	$json_data = file_get_contents( POPMAKE_DIR . 'includes/extension-list.json' );

	return json_decode( $json_data, true );
	/*
	if(($extensions = get_site_transient('baloonup-maker-extension-list')) === false) {

		// data to send in our API request
		$api_params = array( 
			'edd_action'	=> 'extension_list',
			'url'       => home_url()
		);
		// Call the custom API.
		$response = mcms_remote_get( add_query_arg( $api_params, POPMAKE_API_URL ), array( 'timeout' => 15, 'sslverify' => false ) );

		// make sure the response came back okay
		if ( is_mcms_error( $response ) )
			return array();

		$extensions = json_decode( mcms_remote_retrieve_body( $response ) );
		set_site_transient( 'baloonup-maker-extension-list', $extensions, 86400 );
	}
	return $extensions;
	*/
}

add_filter( 'balooncreate_existing_extension_images', 'balooncreate_core_extension_images', 10 );
function balooncreate_core_extension_images( $array ) {
	return array_merge( $array, array(
		'core-extensions-bundle',
		'aweber-integration',
		'mailchimp-integration',
		'remote-content',
		'scroll-triggered-baloonups',
		'baloonup-analytics',
		'forced-interaction',
		'age-verification-modals',
		'advanced-myskin-builder',
		'exit-intent-baloonups',
		'ajax-login-modals',
		'advanced-targeting-conditions',
		'secure-idle-user-logout',
		'terms-conditions-baloonups',
	) );
}
