<?php
/**
 * Google Web Font Integrations.
 */

// Exit if accessed directly
if ( ! defined( 'BASED_TREE_URI' ) ) {
	exit;
}

function balooncreate_get_google_webfonts_list( $key = 'AIzaSyA1Q0uFOhEh3zv_Pk31FqlACArFquyBeQU', $sort = 'alpha' ) {
	if ( $font_list = get_site_transient( 'balooncreate-google-fonts-list' ) ) {
		return $font_list;
	}

	$google_api_url = 'https://www.googleapis.com/webfonts/v1/webfonts?key=' . $key . '&sort=' . $sort;

	$response = mcms_remote_retrieve_body( mcms_remote_get( $google_api_url, array( 'sslverify' => false ) ) );

	if ( ! is_mcms_error( $response ) ) {
		$data = json_decode( $response, true );
	}

	if ( ! empty( $data['errors'] ) || empty( $data['items'] ) ) {
		$data = balooncreate_default_google_webfont_list();
	}

	$items     = $data['items'];
	$font_list = array();

	if ( count( $items ) ) {
		foreach ( $items as $item ) {
			$font_list[ $item['family'] ] = $item;
		}
	}

	set_site_transient( 'balooncreate-google-fonts-list', $font_list, 4 * WEEK_IN_SECONDS );

	return $font_list;
}

function balooncreate_default_google_webfont_list() {
	$json_data = file_get_contents( POPMAKE_DIR . 'includes/google-fonts.json' );

	return json_decode( $json_data, true );
}

add_filter( 'balooncreate_font_family_options', 'balooncreate_google_font_font_family_options', 20 );
function balooncreate_google_font_font_family_options( $options ) {
	$font_list = balooncreate_get_google_webfonts_list();

	if ( empty( $font_list ) ) {
		return $options;
	}

	$options = array_merge( $options, array(
		// option => value
		__( 'Google Web Fonts&#10549;', 'baloonup-maker' ) => '',
	) );
	foreach ( $font_list as $font_family => $font ) {
		$options[ $font_family ] = $font_family;
	}

	return $options;
}
