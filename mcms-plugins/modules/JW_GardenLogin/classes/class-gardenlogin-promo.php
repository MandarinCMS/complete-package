<?php
/**
 * Create controller for promotion.
 * @var [array]
 */
include LOGINPRESS_ROOT_PATH .'classes/control-promo.php';

$mcms_customize->add_section( 'lpcustomize_google_font', array(
  'title'				    => __( 'Google Fonts', 'gardenlogin-pro' ),
  // 'description'	    => __( 'Select Google Font', 'gardenlogin-pro' ),
  'priority'			  => 49,
  'panel'				    => 'gardenlogin_panel',
  ) );

$mcms_customize->add_setting( 'gardenlogin_customization[google_font]', array(
  'default'         => '',
  'type'						=> 'option',
  'capability'			=> 'manage_options',
  'transport'       => 'postMessage'
) );

$mcms_customize->add_control( new GardenLogin_Promo( $mcms_customize, 'gardenlogin_customization[google_font]',
array(
  'section'         => 'lpcustomize_google_font',
  'thumbnail'       => modules_url( 'img/promo/font_promo.png', LOGINPRESS_ROOT_FILE ),
  'promo_text'      => __( 'Unlock Premium Feature', 'gardenlogin' ),
  'link'            => 'https://jiiworks.net/mandarincms/modules/gardenlogin-pro/?utm_source=gardenlogin-lite&amp;utm_medium=fonts&amp;utm_campaign=pro-upgrade'
) ) );

$mcms_customize->add_section( 'customize_recaptcha', array(
  'title'				    => __( 'reCAPTCHA', 'gardenlogin-pro' ),
  // 'description'	   => __( 'reCAPTCHA Setting', 'gardenlogin-pro' ),
  'priority'			  => 24,
  'panel'				    => 'gardenlogin_panel',
) );

$mcms_customize->add_setting( "gardenlogin_customization[recaptcha_error_message]", array(
  'type'						=> 'option',
  'capability'			=> 'manage_options',
  'transport'       => 'postMessage'
) );

$mcms_customize->add_control( new GardenLogin_Promo( $mcms_customize, 'gardenlogin_customization[recaptcha_error_message]',
array(
  'section'         => 'customize_recaptcha',
  'thumbnail'       => modules_url( 'img/promo/recaptcha_option_promo.png', LOGINPRESS_ROOT_FILE ),
  'promo_text'      => __( 'Unlock Premium Feature', 'gardenlogin' ),
  'link'            => 'https://jiiworks.net/mandarincms/modules/gardenlogin-pro/?utm_source=gardenlogin-lite&amp;utm_medium=recaptcha&amp;utm_campaign=pro-upgrade'
) ) );

// $mcms_customize->add_setting( "gardenlogin_customization[reset_hint_text]", array(
//   'type'						=> 'option',
//   'capability'			=> 'manage_options',
//   'transport'       => 'postMessage'
// ) );

// $mcms_customize->add_control( new GardenLogin_Promo( $mcms_customize, 'gardenlogin_customization[reset_hint_text]',
// array(
//   'section'         => 'section_welcome',
//   'thumbnail'       => modules_url( 'img/promo/hint_promo.png', LOGINPRESS_ROOT_FILE ),
//   'promo_text'      => __( 'Unlock Premium Feature', 'gardenlogin' ),
//   'priority'        => 32,
//   'link'            => 'https://jiiworks.net/mandarincms/modules/gardenlogin-pro/?utm_source=gardenlogin-lite&amp;utm_medium=hint&amp;utm_campaign=pro-upgrade'
// ) ) );
  ?>
