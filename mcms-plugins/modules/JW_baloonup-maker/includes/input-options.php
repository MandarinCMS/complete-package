<?php
/**
 * Selectbox options,and other array based data sets used for options.
 */

// Exit if accessed directly
if ( ! defined( 'BASED_TREE_URI' ) ) {
	exit;
}

add_filter( 'balooncreate_size_unit_options', 'balooncreate_core_size_unit_options', 10 );
function balooncreate_core_size_unit_options( $options ) {
	return array_merge( $options, array(
		// option => value
		'px'  => 'px',
		'%'   => '%',
		'em'  => 'em',
		'rem' => 'rem',
	) );
}

add_filter( 'balooncreate_border_style_options', 'balooncreate_core_border_style_options', 10 );
function balooncreate_core_border_style_options( $options ) {
	return array_merge( $options, array(
		// option => value
		__( 'None', 'baloonup-maker' )   => 'none',
		__( 'Solid', 'baloonup-maker' )  => 'solid',
		__( 'Dotted', 'baloonup-maker' ) => 'dotted',
		__( 'Dashed', 'baloonup-maker' ) => 'dashed',
		__( 'Double', 'baloonup-maker' ) => 'double',
		__( 'Groove', 'baloonup-maker' ) => 'groove',
		__( 'Inset', 'baloonup-maker' )  => 'inset',
		__( 'Outset', 'baloonup-maker' ) => 'outset',
		__( 'Ridge', 'baloonup-maker' )  => 'ridge',
	) );
}


add_filter( 'balooncreate_font_family_options', 'balooncreate_core_font_family_options', 10 );
function balooncreate_core_font_family_options( $options ) {
	return array_merge( $options, array(
		// option => value
		__( 'Use Your mySkins', 'baloonup-maker' ) => 'inherit',
		'Sans-Serif'                           => 'Sans-Serif',
		'Tahoma'                               => 'Tahoma',
		'Georgia'                              => 'Georgia',
		'Comic Sans MS'                        => 'Comic Sans MS',
		'Arial'                                => 'Arial',
		'Lucida Grande'                        => 'Lucida Grande',
		'Times New Roman'                      => 'Times New Roman',
	) );
}


add_filter( 'balooncreate_font_weight_options', 'balooncreate_core_font_weight_options', 10 );
function balooncreate_core_font_weight_options( $options ) {
	return array_merge( $options, array(
		__( 'Normal', 'baloonup-maker' ) => '',
		'100 '                        => '100',
		'200 '                        => '200',
		'300 '                        => '300',
		'400 '                        => '400',
		'500 '                        => '500',
		'600 '                        => '600',
		'700 '                        => '700',
		'800 '                        => '800',
		'900 '                        => '900',
	) );
}


add_filter( 'balooncreate_font_style_options', 'balooncreate_core_font_style_options', 10 );
function balooncreate_core_font_style_options( $options ) {
	return array_merge( $options, array(
		__( 'Normal', 'baloonup-maker' ) => '',
		__( 'Italic', 'baloonup-maker' ) => 'italic',
	) );
}


add_filter( 'balooncreate_text_align_options', 'balooncreate_core_text_align_options', 10 );
function balooncreate_core_text_align_options( $options ) {
	return array_merge( $options, array(
		// option => value
		__( 'Left', 'baloonup-maker' )   => 'left',
		__( 'Center', 'baloonup-maker' ) => 'center',
		__( 'Right', 'baloonup-maker' )  => 'right',
	) );
}

add_filter( 'balooncreate_baloonup_display_size_options', 'balooncreate_baloonup_display_size_options_responsive', 10 );
function balooncreate_baloonup_display_size_options_responsive( $options ) {
	return array_merge( $options, array(
		// option => value
		__( 'Responsive Sizes&#10549;', 'baloonup-maker' )     => '',
		__( 'Nano - 10%', 'baloonup-maker' )                   => 'nano',
		__( 'Micro - 20%', 'baloonup-maker' )                  => 'micro',
		__( 'Tiny - 30%', 'baloonup-maker' )                   => 'tiny',
		__( 'Small - 40%', 'baloonup-maker' )                  => 'small',
		__( 'Medium - 60%', 'baloonup-maker' )                 => 'medium',
		__( 'Normal - 70%', 'baloonup-maker' )                 => 'normal',
		__( 'Large - 80%', 'baloonup-maker' )                  => 'large',
		__( 'X Large - 95%', 'baloonup-maker' )                => 'xlarge',
		__( 'Non Responsive Sizes&#10549;', 'baloonup-maker' ) => '',
		__( 'Auto', 'baloonup-maker' )                         => 'auto',
		__( 'Custom', 'baloonup-maker' )                       => 'custom',
	) );
}


add_filter( 'balooncreate_baloonup_display_animation_type_options', 'balooncreate_core_baloonup_display_animation_type_options', 10 );
function balooncreate_core_baloonup_display_animation_type_options( $options ) {
	return array_merge( $options, array(
		// option => value
		__( 'None', 'baloonup-maker' )           => 'none',
		__( 'Slide', 'baloonup-maker' )          => 'slide',
		__( 'Fade', 'baloonup-maker' )           => 'fade',
		__( 'Fade and Slide', 'baloonup-maker' ) => 'fadeAndSlide',
		__( 'Grow', 'baloonup-maker' )           => 'grow',
		__( 'Grow and Slide', 'baloonup-maker' ) => 'growAndSlide',
	) );
}


add_filter( 'balooncreate_baloonup_display_animation_origin_options', 'balooncreate_core_baloonup_display_animation_origins_options', 10 );
function balooncreate_core_baloonup_display_animation_origins_options( $options ) {
	return array_merge( $options, array(
		// option => value
		__( 'Top', 'baloonup-maker' )           => 'top',
		__( 'Left', 'baloonup-maker' )          => 'left',
		__( 'Bottom', 'baloonup-maker' )        => 'bottom',
		__( 'Right', 'baloonup-maker' )         => 'right',
		__( 'Top Left', 'baloonup-maker' )      => 'left top',
		__( 'Top Center', 'baloonup-maker' )    => 'center top',
		__( 'Top Right', 'baloonup-maker' )     => 'right top',
		__( 'Middle Left', 'baloonup-maker' )   => 'left center',
		__( 'Middle Center', 'baloonup-maker' ) => 'center center',
		__( 'Middle Right', 'baloonup-maker' )  => 'right center',
		__( 'Bottom Left', 'baloonup-maker' )   => 'left bottom',
		__( 'Bottom Center', 'baloonup-maker' ) => 'center bottom',
		__( 'Bottom Right', 'baloonup-maker' )  => 'right bottom',
		//__( 'Mouse', 'baloonup-maker' )			=> 'mouse',
	) );
}

add_filter( 'balooncreate_baloonup_display_location_options', 'balooncreate_core_baloonup_display_location_options', 10 );
function balooncreate_core_baloonup_display_location_options( $options ) {
	return array_merge( $options, array(
		// option => value
		__( 'Top Left', 'baloonup-maker' )      => 'left top',
		__( 'Top Center', 'baloonup-maker' )    => 'center top',
		__( 'Top Right', 'baloonup-maker' )     => 'right top',
		__( 'Middle Left', 'baloonup-maker' )   => 'left center',
		__( 'Middle Center', 'baloonup-maker' ) => 'center',
		__( 'Middle Right', 'baloonup-maker' )  => 'right center',
		__( 'Bottom Left', 'baloonup-maker' )   => 'left bottom',
		__( 'Bottom Center', 'baloonup-maker' ) => 'center bottom',
		__( 'Bottom Right', 'baloonup-maker' )  => 'right bottom',
	) );
}


add_filter( 'balooncreate_myskin_close_location_options', 'balooncreate_core_myskin_close_location_options', 10 );
function balooncreate_core_myskin_close_location_options( $options ) {
	return array_merge( $options, array(
		// option => value
		__( 'Top Left', 'baloonup-maker' )     => 'topleft',
		__( 'Top Right', 'baloonup-maker' )    => 'topright',
		__( 'Bottom Left', 'baloonup-maker' )  => 'bottomleft',
		__( 'Bottom Right', 'baloonup-maker' ) => 'bottomright',
	) );
}


add_filter( 'balooncreate_cookie_trigger_options', 'balooncreate_cookie_trigger_options', 10 );
function balooncreate_cookie_trigger_options( $options ) {
	return array_merge( $options, array(
		// option => value
		__( 'Disabled', 'baloonup-maker' ) => 'disabled',
		__( 'On Open', 'baloonup-maker' )  => 'open',
		__( 'On Close', 'baloonup-maker' ) => 'close',
		__( 'Manual', 'baloonup-maker' )   => 'manual',
	) );
}
