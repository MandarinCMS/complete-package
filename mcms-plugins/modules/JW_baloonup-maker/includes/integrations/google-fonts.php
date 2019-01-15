<?php

// Exit if accessed directly
if ( ! defined( 'BASED_TREE_URI' ) ) {
	exit;
}

function balooncreate_get_baloonup_myskin_google_fonts( $baloonup_myskin_id ) {

	$fonts_found = array();
	$myskin = balooncreate_get_baloonup_myskin_data_attr( $baloonup_myskin_id );

	$google_fonts = balooncreate_get_google_webfonts_list();

	if ( ! empty( $myskin['title']['font_family'] ) && is_string( $myskin['title']['font_family'] ) && array_key_exists( $myskin['title']['font_family'], $google_fonts ) ) {
		$variant = $myskin['title']['font_weight'] != 'normal' ? $myskin['title']['font_weight'] : '';
		if ( $myskin['title']['font_style'] == 'italic' ) {
			$variant .= 'italic';
		}
		$fonts_found[ $myskin['title']['font_family'] ][ $variant ] = $variant;
	}
	if ( ! empty( $myskin['content']['font_family'] ) && is_string( $myskin['content']['font_family'] ) && array_key_exists( $myskin['content']['font_family'], $google_fonts ) ) {
		$variant = $myskin['content']['font_weight'] != 'normal' ? $myskin['content']['font_weight'] : '';
		if ( $myskin['content']['font_style'] == 'italic' ) {
			$variant .= 'italic';
		}
		$fonts_found[ $myskin['content']['font_family'] ][ $variant ] = $variant;
	}
	if ( ! empty( $myskin['close']['font_family'] ) && is_string( $myskin['close']['font_family'] ) && array_key_exists( $myskin['close']['font_family'], $google_fonts ) ) {
		$variant = $myskin['close']['font_weight'] != 'normal' ? $myskin['close']['font_weight'] : '';
		if ( $myskin['close']['font_style'] == 'italic' ) {
			$variant .= 'italic';
		}
		$fonts_found[ $myskin['close']['font_family'] ][ $variant ] = $variant;
	}

	return $fonts_found;
}

