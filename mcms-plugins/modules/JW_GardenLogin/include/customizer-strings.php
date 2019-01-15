<?php
/**
 * Customizer strings for the logo control.
 * @since 1.1.3
 */
$logo_range_control = array( 'customize_logo_width', 'customize_logo_height', 'customize_logo_padding' );
$logo_range_default = array( '84', '84', '0' );
$logo_range_label = array( __( 'Logo Width:', 'gardenlogin' ), __( 'Logo Height:', 'gardenlogin' ), __( 'Space Bottom:', 'gardenlogin' ) );
$logo_range_attrs = array(
  array( 'min' => 0, 'max' => 500, 'step' => 1, 'suffix' => 'px' ),
  array( 'min' => 0, 'max' => 500, 'step' => 1, 'suffix' => 'px' ),
  array( 'min' => 0, 'max' => 100, 'step' => 1, 'suffix' => 'px' )
);
$logo_range_unit    = array( 'px', 'px', 'px' );

/**
 * Customizer strings for the grouping control.
 * @since 1.1.3
 */
$group_control  = array( 'login_input_group', 'login_label_group', 'login_form_group', 'footer_form_group', 'footer_back_group', 'footer_group' );
$group_label    = array(
  __( 'Input Fields:', 'gardenlogin'),
  __( 'Input Field Labels:', 'gardenlogin'),
  __( 'Login Form:', 'gardenlogin'),
  __( 'Lost Your Password Text', 'gardenlogin' ),
  __( 'Back To Site Text', 'gardenlogin' ),
  __( 'GardenLogin Footer Text', 'gardenlogin' ) );
$group_info     = array(
  __( 'This section helps you to easily Customize the login form input field elements.', 'gardenlogin' ),
  __( 'This section helps you to easily Customize the login form input field labels.', 'gardenlogin' ),
  __( 'This section helps you to easily Customize the login form elements whether they are form lables, fields or backgrounds.', 'gardenlogin' ),
  __( ' Customize the "Lost your password" and "Register" text section under the form.', 'gardenlogin' ),
  __( 'Customize the "Back to" text section under the form.', 'gardenlogin' ),
  __( 'Customize the copyright note and branding sections at the footer of login page.', 'gardenlogin' )  );
/** ------------------Grouping Control-------------------- */

/**
 * [ Customizer strings for the section login form. ]
 * @since 1.1.3
 */
$form_range_control = array( 'customize_form_width', 'customize_form_height', 'customize_form_radius', 'customize_form_shadow', 'customize_form_opacity', 'textfield_width', 'textfield_radius', 'textfield_shadow', 'textfield_shadow_opacity', 'customize_form_label', 'remember_me_font_size' );
$form_range_default = array( '350', '200', '0', '0', '0', '100', '0', '0', '80', '14', '13' );
$form_range_label   = array(
  __( 'Form Width:', 'gardenlogin' ),
  __( 'Form Minimum Height:', 'gardenlogin' ),
  __( 'Form Radius:', 'gardenlogin' ),
  __( 'Form Shadow:', 'gardenlogin' ),
  __( 'Form Shadow Opacity:', 'gardenlogin' ),
  __( 'Input Text Field Width:', 'gardenlogin' ),
  __( 'Input Text Field Radius:', 'gardenlogin' ),
  __( 'Input Text Field Shadow:', 'gardenlogin' ),
  __( 'Input Text Field Shadow Opacity:', 'gardenlogin' ),
  __( 'Input Field Label Font Size:', 'gardenlogin' ),
  __( 'Remember Me Font Size:', 'gardenlogin' ) );
$form_range_attrs   = array(
  array( 'min' => 320, 'max' => 800, 'step' => 1, 'suffix' => 'px' ), // form width
  array( 'min' => 0, 'max' => 500, 'step' => 1, 'suffix' => 'px' ), // form height
  array( 'min' => 0, 'max' => 100, 'step' => 1, 'suffix' => 'px' ), // form radius
  array( 'min' => 0, 'max' => 30, 'step' => 1, 'suffix' => 'px' ), // form shadow
  array( 'min' => 0, 'max' => 100, 'step' => 1, 'suffix' => '%' ), // form Opacity
  array( 'min' => 0, 'max' => 100, 'step' => 1, 'suffix' => '%' ), // textfield width
  array( 'min' => 0, 'max' => 30, 'step' => 1, 'suffix' => 'px' ), // textfield radius
  array( 'min' => 0, 'max' => 30, 'step' => 1, 'suffix' => 'px' ), // textfield shadow
  array( 'min' => 0, 'max' => 100, 'step' => 1, 'suffix' => '%' ), // textfield Opacity
  array( 'min' => 9, 'max' => 30, 'step' => 1, 'suffix' => 'px' ), // testfield label
  array( 'min' => 9, 'max' => 30, 'step' => 1, 'suffix' => 'px' ) // readme label
);
$form_range_unit    = array( 'px', 'px', 'px', 'px', '%', '%', 'px', 'px', '%', 'px', 'px' );
//--------------------
$form_color_control = array( 'form_background_color', 'textfield_background_color', 'textfield_color', 'textfield_label_color', 'remember_me_label_size' );
$form_color_default = array( '#FFF', '#FFF', '#333', '#777', '#72777c' );
$form_color_label   = array(
  __( 'Form Background Color:', 'gardenlogin' ),
  __( 'Input Field Background Color:', 'gardenlogin' ),
  __( 'Input Field Text Color:', 'gardenlogin' ),
  __( 'Input Field Label Color:', 'gardenlogin' ),
  __( 'Remember me Label Color:', 'gardenlogin' ),
);
//--------------------
$form_control       = array( 'customize_form_padding', 'customize_form_border', 'textfield_margin', 'form_username_label', 'form_password_label' );
$form_default       = array( '0 24px 12px', '', '2px 6px 18px 0px', __( 'Username or Email Address', 'gardenlogin' ), __( 'Password', 'gardenlogin' ) );
$form_label         = array(
  __( 'Form Padding:', 'gardenlogin' ),
  __( 'Border (Example: 2px dotted black):', 'gardenlogin' ),
  __( 'Input Text Field Margin:', 'gardenlogin' ),
  __( 'Username Label:', 'gardenlogin' ),
  __( 'Password Label:', 'gardenlogin' ),
);
/** -----------------Sectin Login Form------------------ */

/**
 * [ Customizer strings for the section button beauty. ]
 * @since 1.1.3
 */
 $button_control = array( 'custom_button_color', 'button_border_color', 'button_hover_color', 'button_hover_border', 'custom_button_shadow', 'button_text_color' );
 $button_default = array( '#2EA2CC', '#0074A2', '#1E8CBE', '#0074A2', '#78C8E6', '#FFF' );
 $button_label = array(
   __( 'Button Color:', 'gardenlogin' ),
   __( 'Button Border Color:', 'gardenlogin' ),
   __( 'Button Color (Hover):', 'gardenlogin' ),
   __( 'Button Border (Hover):', 'gardenlogin' ),
   __( 'Button Box Shadow:', 'gardenlogin' ),
   __( 'Button Text Color:', 'gardenlogin' )
 );

$button_range_control = array( 'login_button_size', 'login_button_top', 'login_button_bottom', 'login_button_radius', 'login_button_shadow', 'login_button_shadow_opacity', 'login_button_text_size' );
$button_range_default = array( '100', '13', '13', '5', '0', '80', '15' );
$button_range_label = array( __( 'Button Size:', 'gardenlogin' ), __( 'Button Top Padding:', 'gardenlogin' ), __( 'Button Bottom Padding:', 'gardenlogin' ), __( 'Radius:', 'gardenlogin' ), __( 'Shadow:', 'gardenlogin' ), __( 'Shadow Opacity:', 'gardenlogin' ), __( 'Text Size:', 'gardenlogin' ) );
$button_range_attrs = array(
  array( 'min' => 20, 'max' => 100, 'step' => 1, 'suffix' => '%' ),
  array( 'min' => 0, 'max' => 30, 'step' => 1, 'suffix' => 'px' ),
  array( 'min' => 0, 'max' => 30, 'step' => 1, 'suffix' => 'px' ),
  array( 'min' => 0, 'max' => 50, 'step' => 1, 'suffix' => 'px' ),
  array( 'min' => 0, 'max' => 30, 'step' => 1, 'suffix' => 'px' ),
  array( 'min' => 0, 'max' => 100, 'step' => 1, 'suffix' => 'px' ),
  array( 'min' => 7, 'max' => 35, 'step' => 1, 'suffix' => 'px' ),
);
$button_range_unit = array( '%', 'px', 'px', 'px', 'px', '%', 'px' );
/** -----------------Sectin Button Beauty------------------ */

/**
 * [ Customizer strings for the group close. ]
 * @since 1.1.3
 */
$close_control = array( 'login_input_br', 'login_label_br', 'login_form_br', 'footer_form_br', 'footer_back_br', 'footer_br' );
