<?php
/**
 * Get option and check the key exists in it.
 *
 * @since 1.0.0
 * * * * * * * * * * * * * * * */


 /**
 * @var gardenlogin_array get_option
 * @since 1.0.0
 */
$gardenlogin_array  = (array) get_option( 'gardenlogin_customization' );
$gardenlogin_preset = get_option( 'customize_presets_settings', 'default1' );

function gardenlogin_get_option_key( $gardenlogin_key, $gardenlogin_array ) {

	if ( array_key_exists( $gardenlogin_key, $gardenlogin_array ) ) {

		return $gardenlogin_array[ $gardenlogin_key ];
	}
}

function gardenlogin_bg_option( $gardenlogin_key, $gardenlogin_array ) {

	if ( array_key_exists( $gardenlogin_key, $gardenlogin_array ) ) {

		return $gardenlogin_array[ $gardenlogin_key ];
	} else {
    return true;
  }
}

function gardenlogin_check_px( $value ) {

  if ( strpos( $value, "px" ) ) {
    return $value;
  } else {
		if ( ! empty( $value ) ) {
			return $value . 'px';
		}
  }
}

function gardenlogin_check_percentage( $value ) {

  if ( strpos( $value, "%" ) ) {
    return $value;
  } else {
		if ( ! empty( $value ) ) {
			return $value . '%';
		}
  }
}

/**
 * [if for login page background]
 * @since 1.1.0
 * @version 1.1.2
 * @return string
 */
$gardenlogin_custom_background  = gardenlogin_get_option_key( 'setting_background', $gardenlogin_array );
$gardenlogin_gallery_background = gardenlogin_get_option_key( 'gallery_background', $gardenlogin_array );
if ( ! empty ( $gardenlogin_custom_background ) ) { // Use Custom Background
	$gardenlogin_background_img = $gardenlogin_custom_background;
} else if ( ! empty ( $gardenlogin_gallery_background ) ) { // Background from Gallery Control.
	if ( LOGINPRESS_DIR_URL . 'img/gallery/img-1.jpg' == $gardenlogin_gallery_background ) { // If user select 1st image from gallery control then show template's default image.
		$gardenlogin_background_img = '';
	} else { // Use selected image from gallery control.
		$gardenlogin_background_img = $gardenlogin_gallery_background;
	}
} else { // exceptional case (use default image).
	$gardenlogin_background_img = '';
}

/**
 * Add !important with property's value. To avoid overriding from myskin.
 * @return string
 * @since 1.1.2
 */
function gardenlogin_important() {

	$important = '';
	if ( ! is_customize_preview() ) { // Avoid !important in customizer previewer.
		$important = ' !important';
	}
	return $important;
}

$gardenlogin_logo_img 						= gardenlogin_get_option_key( 'setting_logo', $gardenlogin_array );
$gardenlogin_logo_display				= gardenlogin_get_option_key( 'setting_logo_display', $gardenlogin_array );
$gardenlogin_get_logo_width 			= gardenlogin_get_option_key( 'customize_logo_width', $gardenlogin_array );
$gardenlogin_logo_width          = gardenlogin_check_px( $gardenlogin_get_logo_width );
$gardenlogin_get_logo_height 		= gardenlogin_get_option_key( 'customize_logo_height', $gardenlogin_array );
$gardenlogin_logo_height         = gardenlogin_check_px( $gardenlogin_get_logo_height );
$gardenlogin_get_logo_padding 		= gardenlogin_get_option_key( 'customize_logo_padding', $gardenlogin_array );
$gardenlogin_logo_padding        = gardenlogin_check_px( $gardenlogin_get_logo_padding );
$gardenlogin_btn_bg 							= gardenlogin_get_option_key( 'custom_button_color', $gardenlogin_array );
$gardenlogin_btn_border 					= gardenlogin_get_option_key( 'button_border_color', $gardenlogin_array );
$gardenlogin_btn_shadow 					= gardenlogin_get_option_key( 'custom_button_shadow', $gardenlogin_array );
$gardenlogin_btn_color 					= gardenlogin_get_option_key( 'button_text_color', $gardenlogin_array );
$gardenlogin_btn_hover_bg 				= gardenlogin_get_option_key( 'button_hover_color', $gardenlogin_array );
$gardenlogin_btn_hover_border 	  = gardenlogin_get_option_key( 'button_hover_border', $gardenlogin_array );
// $gardenlogin_background_img			= gardenlogin_get_option_key( 'setting_background', $gardenlogin_array );
$gardenlogin_background_color		= gardenlogin_get_option_key( 'setting_background_color', $gardenlogin_array );
$gardenlogin_background_repeat	  = gardenlogin_get_option_key( 'background_repeat_radio', $gardenlogin_array );
$gardenlogin_background_postion	= gardenlogin_get_option_key( 'background_position', $gardenlogin_array );
$gardenlogin_background_image_size = gardenlogin_get_option_key( 'background_image_size', $gardenlogin_array );
$gardenlogin_form_background_img = gardenlogin_get_option_key( 'setting_form_background', $gardenlogin_array );
$gardenlogin_form_display_bg 		= gardenlogin_get_option_key( 'setting_form_display_bg', $gardenlogin_array );
$gardenlogin_form_background_clr = gardenlogin_get_option_key( 'form_background_color', $gardenlogin_array );
$gardenlogin_forget_form_bg_img  = gardenlogin_get_option_key( 'forget_form_background', $gardenlogin_array );
$gardenlogin_forget_form_bg_clr  = gardenlogin_get_option_key( 'forget_form_background_color', $gardenlogin_array );
$gardenlogin_form_width 			 	  = gardenlogin_get_option_key( 'customize_form_width', $gardenlogin_array );
$gardenlogin_get_form_height 		= gardenlogin_get_option_key( 'customize_form_height', $gardenlogin_array );
$gardenlogin_form_height         = gardenlogin_check_px( $gardenlogin_get_form_height );
$gardenlogin_form_padding 			  = gardenlogin_get_option_key( 'customize_form_padding', $gardenlogin_array );
$gardenlogin_form_border 			 	= gardenlogin_get_option_key( 'customize_form_border', $gardenlogin_array );
$gardenlogin_form_field_width 	  = gardenlogin_get_option_key( 'textfield_width', $gardenlogin_array );
$gardenlogin_form_field_margin 	= gardenlogin_get_option_key( 'textfield_margin', $gardenlogin_array );
$gardenlogin_form_field_bg 			= gardenlogin_get_option_key( 'textfield_background_color', $gardenlogin_array );
$gardenlogin_form_field_color 	  = gardenlogin_get_option_key( 'textfield_color', $gardenlogin_array );
$gardenlogin_form_field_label 	  = gardenlogin_get_option_key( 'textfield_label_color', $gardenlogin_array );
$gardenlogin_form_remeber_label  = gardenlogin_get_option_key( 'remember_me_label_size', $gardenlogin_array );
$gardenlogin_welcome_bg_color		= gardenlogin_get_option_key( 'message_background_color', $gardenlogin_array );
$gardenlogin_welcome_bg_border   = gardenlogin_get_option_key( 'message_background_border', $gardenlogin_array );
$gardenlogin_footer_display			= gardenlogin_get_option_key( 'footer_display_text', $gardenlogin_array );
$gardenlogin_footer_decoration   = gardenlogin_get_option_key( 'login_footer_text_decoration', $gardenlogin_array );
$gardenlogin_footer_text_color   = gardenlogin_get_option_key( 'login_footer_color', $gardenlogin_array );
$gardenlogin_footer_text_hover   = gardenlogin_get_option_key( 'login_footer_color_hover', $gardenlogin_array );
$gardenlogin_get_footer_font_size= gardenlogin_get_option_key( 'login_footer_font_size', $gardenlogin_array );
$gardenlogin_footer_font_size    = gardenlogin_check_px( $gardenlogin_get_footer_font_size );
$gardenlogin_remember_me_font_size= gardenlogin_get_option_key( 'remember_me_font_size', $gardenlogin_array );
$gardenlogin_form_label_font_size= gardenlogin_get_option_key( 'customize_form_label', $gardenlogin_array );
$gardenlogin_login_button_top		= gardenlogin_get_option_key( 'login_button_top', $gardenlogin_array );
$gardenlogin_login_button_bottom	= gardenlogin_get_option_key( 'login_button_bottom', $gardenlogin_array );
$gardenlogin_login_button_radius	= gardenlogin_get_option_key( 'login_button_radius', $gardenlogin_array );
$gardenlogin_login_button_shadow	= gardenlogin_get_option_key( 'login_button_shadow', $gardenlogin_array );
$gardenlogin_login_button_shadow_opacity	= gardenlogin_get_option_key( 'login_button_shadow_opacity', $gardenlogin_array );
$gardenlogin_login_button_width	= gardenlogin_get_option_key( 'login_button_size', $gardenlogin_array );
$gardenlogin_login_form_radius 	= gardenlogin_get_option_key( 'customize_form_radius', $gardenlogin_array );
$gardenlogin_login_form_shadow	= gardenlogin_get_option_key( 'customize_form_shadow', $gardenlogin_array );
$gardenlogin_login_form_inset	= gardenlogin_get_option_key( 'textfield_inset_shadow', $gardenlogin_array );
$gardenlogin_login_form_opacity	= gardenlogin_get_option_key( 'customize_form_opacity', $gardenlogin_array );
$gardenlogin_login_textfield_radius= gardenlogin_get_option_key( 'textfield_radius', $gardenlogin_array );
$gardenlogin_login_button_text_size= gardenlogin_get_option_key( 'login_button_text_size', $gardenlogin_array );
$gardenlogin_textfield_shadow	= gardenlogin_get_option_key( 'textfield_shadow', $gardenlogin_array );
$gardenlogin_textfield_shadow_opacity= gardenlogin_get_option_key( 'textfield_shadow_opacity', $gardenlogin_array );
$gardenlogin_footer_bg_color 		= gardenlogin_get_option_key( 'login_footer_bg_color', $gardenlogin_array );
$gardenlogin_footer_links_font_size = gardenlogin_get_option_key( 'login_footer_links_text_size', $gardenlogin_array );
$gardenlogin_footer_links_hover_size = gardenlogin_get_option_key( 'login_footer_links_hover_size', $gardenlogin_array );
$gardenlogin_header_text_color   = gardenlogin_get_option_key( 'login_head_color', $gardenlogin_array );
$gardenlogin_header_text_hover   = gardenlogin_get_option_key( 'login_head_color_hover', $gardenlogin_array );
$gardenlogin_header_font_size 	  = gardenlogin_get_option_key( 'login_head_font_size', $gardenlogin_array );
$gardenlogin_header_bg_color 		= gardenlogin_get_option_key( 'login_head_bg_color', $gardenlogin_array );
$gardenlogin_back_display			 	= gardenlogin_get_option_key( 'back_display_text', $gardenlogin_array );
$gardenlogin_back_decoration  	  = gardenlogin_get_option_key( 'login_back_text_decoration', $gardenlogin_array );
$gardenlogin_back_text_color  	  = gardenlogin_get_option_key( 'login_back_color', $gardenlogin_array );
$gardenlogin_back_text_hover  	  = gardenlogin_get_option_key( 'login_back_color_hover', $gardenlogin_array );
$gardenlogin_get_back_font_size 	= gardenlogin_get_option_key( 'login_back_font_size', $gardenlogin_array );
$gardenlogin_back_font_size      = gardenlogin_check_px( $gardenlogin_get_back_font_size );
$gardenlogin_back_bg_color 			= gardenlogin_get_option_key( 'login_back_bg_color', $gardenlogin_array );
$gardenlogin_footer_link_color	  = gardenlogin_get_option_key( 'login_footer_text_color', $gardenlogin_array );
$gardenlogin_footer_link_hover	  = gardenlogin_get_option_key( 'login_footer_text_hover', $gardenlogin_array );
$gardenlogin_footer_link_bg_clr	= gardenlogin_get_option_key( 'login_footer_backgroung_hover', $gardenlogin_array );
$gardenlogin_custom_css 			 	  = gardenlogin_get_option_key( 'gardenlogin_custom_css', $gardenlogin_array );
$gardenlogin_custom_js 				 	= gardenlogin_get_option_key( 'gardenlogin_custom_js', $gardenlogin_array );

$gardenlogin_display_bg 	        = gardenlogin_bg_option( 'gardenlogin_display_bg', $gardenlogin_array );
$gardenlogin_myskin_tem           = get_option( 'customize_presets_settings', 'default1' );

/**
 * gardenlogin_box_shadow
 * @param  integer $shadow         [Shadow Value]
 * @param  integer $opacity        [Opacity Value]
 * @param  integer $default_shadow [description]
 * @return string                  [box-border value]
 * @since 1.1.3
 */
$gardenlogin_inset = $gardenlogin_login_form_inset ? true : false; //var_dump($gardenlogin_inset);
function gardenlogin_box_shadow( $shadow, $opacity, $default_shadow = 0, $inset = false ){

	$gardenlogin_shadow = ! empty( $shadow ) ? $shadow : $default_shadow;
	$gardenlogin_opacity = ! empty( $opacity ) ? $opacity : 80;
	$inset = $inset ? ' inset' : '';
	$opacity_convertion = $gardenlogin_opacity / 100;
	$gardenlogin_rgba = 'rgba( 0,0,0,' . $opacity_convertion .' )';

	return '0 0 ' . $gardenlogin_shadow . 'px ' . $gardenlogin_rgba . $inset . ';';
}
// ob_start();
?>
<style type="text/css">
*{
	box-sizing: border-box;
}
#login::after{
  <?php if ( ( $gardenlogin_myskin_tem == 'default6' || $gardenlogin_myskin_tem == 'default10' ) && ! empty( $gardenlogin_background_img ) && $gardenlogin_display_bg ) : ?>
	background-image: url(<?php echo $gardenlogin_background_img; ?>);

  <?php elseif (  ( $gardenlogin_myskin_tem == 'default6' || $gardenlogin_myskin_tem == 'default10' ) &&  isset( $gardenlogin_display_bg ) && ! $gardenlogin_display_bg ) : ?>
	background-image: url();
	<?php endif; ?>
  <?php if( in_array( $gardenlogin_myskin_tem, array( 'default6', 'default10' ) ) ) : ?>
    <?php if ( ! empty( $gardenlogin_background_color ) ) : ?>
  	background-color: <?php echo $gardenlogin_background_color; ?>;
  	<?php endif; ?>
    <?php if ( ! empty( $gardenlogin_background_repeat ) ) : ?>
  	background-repeat: <?php echo $gardenlogin_background_repeat; ?>;
  	<?php endif; ?>
  	<?php if ( ! empty( $gardenlogin_background_postion ) ) : ?>
  	background-position: <?php echo $gardenlogin_background_postion; ?>;
  	<?php endif; ?>
  	<?php if ( ! empty( $gardenlogin_background_image_size ) ) : ?>
  	background-size: <?php echo $gardenlogin_background_image_size; ?>;
  	<?php endif; ?>
	<?php endif; ?>
}

#login{
  <?php if ( $gardenlogin_myskin_tem == 'default17' && ! empty( $gardenlogin_background_img ) && $gardenlogin_display_bg ) : ?>
	background-image: url(<?php echo $gardenlogin_background_img; ?>);
  <?php elseif ( $gardenlogin_myskin_tem == 'default17' &&  isset( $gardenlogin_display_bg ) && ! $gardenlogin_display_bg ) : ?>
	background-image: url();
	<?php endif; ?>

  <?php if( $gardenlogin_myskin_tem == 'default17' ) : ?>
    <?php if ( ! empty( $gardenlogin_background_color ) ) : ?>
  	background-color: <?php echo $gardenlogin_background_color; ?>;
  	<?php endif; ?>
    <?php if ( ! empty( $gardenlogin_background_repeat ) ) : ?>
  	background-repeat: <?php echo $gardenlogin_background_repeat; ?>;
  	<?php endif; ?>
  	<?php if ( ! empty( $gardenlogin_background_postion ) ) : ?>
  	background-position: <?php echo $gardenlogin_background_postion; ?>;
  	<?php endif; ?>
  	<?php if ( ! empty( $gardenlogin_background_image_size ) ) : ?>
  	background-size: <?php echo $gardenlogin_background_image_size; ?>;
    <?php endif; ?>
	<?php endif; ?>
	<?php if ( ! empty( $gardenlogin_form_display_bg ) && true == $gardenlogin_form_display_bg ) : ?>
	background: transparent;
	<?php endif; ?>
	<?php if ( ! empty( $gardenlogin_form_background_clr ) ) : ?>
	background-color: <?php echo $gardenlogin_form_background_clr; ?>;
	<?php endif; ?>
	<?php if ( ! empty( $gardenlogin_login_form_radius ) ) : ?>
	border-radius: <?php echo $gardenlogin_login_form_radius . 'px'; ?>;
	<?php endif; ?>
	<?php if ( ! empty( $gardenlogin_login_form_shadow ) && ! empty( $gardenlogin_login_form_opacity ) ) : ?>
	box-shadow: <?php echo gardenlogin_box_shadow( $gardenlogin_login_form_shadow, $gardenlogin_login_form_opacity ); ?>
	<?php endif; ?>
}
body.login:after{
  <?php if ( $gardenlogin_myskin_tem == 'default8' && ! empty( $gardenlogin_background_img ) && $gardenlogin_display_bg ) : ?>
	background-image: url(<?php echo $gardenlogin_background_img; ?>);
  <?php elseif ( $gardenlogin_myskin_tem == 'default8' &&  isset( $gardenlogin_display_bg ) && ! $gardenlogin_display_bg ) : ?>
	background-image: url();
	<?php endif; ?>

  <?php if( $gardenlogin_myskin_tem == 'default8' ) : ?>
    <?php if ( ! empty( $gardenlogin_background_color ) ) : ?>
  	background-color: <?php echo $gardenlogin_background_color; ?>;
  	<?php endif; ?>
    <?php if ( ! empty( $gardenlogin_background_repeat ) ) : ?>
  	background-repeat: <?php echo $gardenlogin_background_repeat; ?>;
  	<?php endif; ?>
  	<?php if ( ! empty( $gardenlogin_background_postion ) ) : ?>
  	background-position: <?php echo $gardenlogin_background_postion; ?>;
  	<?php endif; ?>
  	<?php if ( ! empty( $gardenlogin_background_image_size ) ) : ?>
  	background-size: <?php echo $gardenlogin_background_image_size; ?>;
    <?php endif; ?>
	<?php endif; ?>
}
body.login {

  <?php if ( in_array( $gardenlogin_myskin_tem, array( 'default6', 'default8', 'default10', 'default17' ) ) && ! empty( $gardenlogin_background_img ) && $gardenlogin_display_bg ) : ?>
	background-image: url();
  <?php elseif ( in_array( $gardenlogin_myskin_tem, array( 'default6', 'default8', 'default10', 'default17' ) ) &&  isset( $gardenlogin_display_bg ) && ! $gardenlogin_display_bg ) : ?>
	background-image: url();
	<?php endif; ?>

	<?php if ( ! in_array( $gardenlogin_myskin_tem, array( 'default6', 'default8', 'default10', 'default17' ) )  && ! empty( $gardenlogin_background_img ) && $gardenlogin_display_bg ) : ?>
	background-image: url(<?php echo $gardenlogin_background_img; ?>);
  <?php elseif ( ! in_array( $gardenlogin_myskin_tem, array( 'default6', 'default8', 'default10', 'default17' ) ) && isset( $gardenlogin_display_bg ) && ! $gardenlogin_display_bg ) : ?>
	background-image: url();
	<?php endif; ?>

	<?php if ( ! empty( $gardenlogin_background_color ) ) : ?>
	background-color: <?php echo $gardenlogin_background_color; ?>;
	<?php endif; ?>
	<?php if ( ! empty( $gardenlogin_background_repeat ) ) : ?>
	background-repeat: <?php echo $gardenlogin_background_repeat; ?>;
	<?php endif; ?>
	<?php if ( ! empty( $gardenlogin_background_postion ) ) : ?>
	background-position: <?php echo $gardenlogin_background_postion; ?>;
	<?php endif; ?>
	<?php if ( ! empty( $gardenlogin_background_image_size ) ) : ?>
	background-size: <?php echo $gardenlogin_background_image_size; ?>;
	<?php endif; ?>
  position: relative;
}
.login h1{
	<?php if ( ! empty( $gardenlogin_logo_display ) && true == $gardenlogin_logo_display ) : ?>
	display: none <?php echo gardenlogin_important(); ?>;
	<?php endif; ?>
}
.interim-login.login h1 a{
  <?php if ( ! empty( $gardenlogin_logo_width ) ) : ?>
  width: <?php echo $gardenlogin_logo_width; ?>;
  <?php else : ?>
	width: 84px;
	<?php endif; ?>
}

.login h1 a {
	<?php if ( ! empty( $gardenlogin_logo_img ) ) : ?>
	background-image: url( <?php echo $gardenlogin_logo_img; ?> ) <?php echo gardenlogin_important(); ?>;
	<?php endif; ?>
	<?php if ( ! empty( $gardenlogin_logo_width ) ) : ?>
	width: <?php echo $gardenlogin_logo_width . gardenlogin_important(); ?>;
	<?php endif; ?>
	<?php if ( ! empty( $gardenlogin_logo_height ) ) : ?>
	height: <?php echo $gardenlogin_logo_height . gardenlogin_important(); ?>;
	<?php endif; ?>
	<?php if ( ! empty( $gardenlogin_logo_width ) || ! empty( $gardenlogin_logo_height ) ) : ?>
	background-size: cover <?php echo gardenlogin_important(); ?>;
	<?php else: ?>
		background-size: cover;
	<?php endif; ?>

	<?php if ( ! empty( $gardenlogin_logo_padding ) ) : ?>
	margin-bottom: <?php echo $gardenlogin_logo_padding . gardenlogin_important(); ?>;
	<?php endif; ?>

}

.mcms-core-ui #login  .button-primary{
	<?php if ( ! empty( $gardenlogin_btn_bg ) ) : ?>
	background: <?php echo $gardenlogin_btn_bg; ?>;
	<?php endif; ?>
	<?php if ( ! empty( $gardenlogin_btn_border ) ) : ?>
	border-color: <?php echo $gardenlogin_btn_border; ?>;
	<?php endif; ?>
	<?php if ( ! empty( $gardenlogin_btn_shadow ) ) : ?>
	box-shadow: 0px 1px 0px <?php echo $gardenlogin_btn_shadow; ?> inset, 0px 1px 0px rgba(0, 0, 0, 0.15);
	<?php endif; ?>
	<?php if ( ! empty( $gardenlogin_btn_color ) ) : ?>
	color: <?php echo $gardenlogin_btn_color; ?>;
	<?php endif; ?>
}

.mcms-core-ui #login  .button-primary:hover{
	<?php if ( ! empty( $gardenlogin_btn_hover_bg ) ) : ?>
	background: <?php echo $gardenlogin_btn_hover_bg; ?>;
	<?php endif; ?>
	<?php if ( ! empty( $gardenlogin_btn_hover_border ) ) : ?>
	border-color: <?php echo $gardenlogin_btn_hover_border; ?>;
	<?php endif; ?>
}
.mcms-core-ui #login .button-primary{

	box-shadow: <?php echo gardenlogin_box_shadow( $gardenlogin_login_button_shadow, $gardenlogin_login_button_shadow_opacity ); ?>
  /* box-shadow: none; */
	height: auto;
	line-height: 20px;
	padding: 13px;
	<?php if ( ! empty( $gardenlogin_login_button_top ) ) : ?>
	padding-top: <?php echo $gardenlogin_login_button_top . 'px;' ?>;
	<?php endif; ?>
	<?php if ( ! empty( $gardenlogin_login_button_bottom ) ) : ?>
	padding-bottom: <?php echo $gardenlogin_login_button_bottom . 'px;' ?>;
	<?php endif; ?>
}
#loginform {

	<?php if ( ! empty( $gardenlogin_form_display_bg ) && true == $gardenlogin_form_display_bg ) : ?>
	background: transparent;
	<?php endif; ?>
	<?php if ( ! empty( $gardenlogin_form_background_img ) ) : ?>
	background-image: url(<?php echo $gardenlogin_form_background_img; ?>);
	<?php endif; ?>
	<?php if ( ! empty( $gardenlogin_form_background_clr ) ) : ?>
	background-color: <?php echo $gardenlogin_form_background_clr; ?>;
	<?php endif; ?>
	<?php if ( ! empty( $gardenlogin_form_height ) ) : ?>
	min-height: <?php echo $gardenlogin_form_height; ?>;
	<?php endif; ?>
	<?php if ( ! empty( $gardenlogin_form_padding ) ) : ?>
	padding: <?php echo $gardenlogin_form_padding; ?>;
	<?php endif; ?>
	<?php if ( ! empty( $gardenlogin_form_border ) ) : ?>
	border: <?php echo $gardenlogin_form_border; ?>;
	<?php endif; ?>
}

#loginform input[type="text"], #loginform input[type="password"]{
<?php if ( ! empty( $gardenlogin_login_textfield_radius ) ) : ?>
border-radius: <?php echo $gardenlogin_login_textfield_radius . 'px;'; ?>;
<?php endif; ?>

box-shadow: <?php echo gardenlogin_box_shadow( $gardenlogin_textfield_shadow, $gardenlogin_textfield_shadow_opacity, '0', $gardenlogin_inset ); ?>
}

#registerform input[type="text"], #registerform input[type="password"], #registerform input[type="number"], #registerform input[type="email"] {
	<?php if ( ! empty( $gardenlogin_login_textfield_radius ) ) : ?>
	border-radius: <?php echo $gardenlogin_login_textfield_radius . 'px;'; ?>;
	<?php endif; ?>
	box-shadow: <?php echo gardenlogin_box_shadow( $gardenlogin_textfield_shadow, $gardenlogin_textfield_shadow_opacity, '0', $gardenlogin_inset ); ?>
}

#lostpasswordform input[type="text"]{
	<?php if ( ! empty( $gardenlogin_login_textfield_radius ) ) : ?>
	border-radius: <?php echo $gardenlogin_login_textfield_radius . 'px;'; ?>;
	<?php endif; ?>
	box-shadow: <?php echo gardenlogin_box_shadow( $gardenlogin_textfield_shadow, $gardenlogin_textfield_shadow_opacity, '0', $gardenlogin_inset ); ?>
}

#login {
	<?php if ( ! empty( $gardenlogin_form_width ) ) : ?>
	max-width: <?php echo gardenlogin_check_px( $gardenlogin_form_width ); ?>;
	<?php else : ?>
	<?php endif; ?>
}

.login label[for="rememberme"] {
	<?php if ( ! empty( $gardenlogin_form_remeber_label ) ) : ?>
	color: <?php echo $gardenlogin_form_remeber_label; ?>;
	<?php endif; ?>
}

.login label {
	<?php if( !empty( $gardenlogin_form_label_font_size ) && 'default2' != $gardenlogin_preset ) : ?>
	font-size: <?php echo $gardenlogin_form_label_font_size . 'px;'; ?>
	<?php endif; ?>
	<?php if ( ! empty( $gardenlogin_form_field_label ) ) : ?>
	color: <?php echo $gardenlogin_form_field_label; ?>;
	<?php endif; ?>
}

.login form .input, .login input[type="text"] {
	<?php if ( ! empty( $gardenlogin_form_field_width ) ) : ?>
	width: <?php echo gardenlogin_check_percentage($gardenlogin_form_field_width); ?>;
	<?php endif; ?>
	<?php if ( ! empty( $gardenlogin_form_field_margin ) ) : ?>
	margin: <?php echo $gardenlogin_form_field_margin; ?>;
	<?php endif; ?>
	<?php if ( ! empty( $gardenlogin_form_field_bg ) ) : ?>
	background: <?php echo $gardenlogin_form_field_bg; ?>;
	<?php endif; ?>
	<?php if ( ! empty( $gardenlogin_form_field_color ) ) : ?>
	color: <?php echo $gardenlogin_form_field_color; ?>;
	<?php endif; ?>
}

#lostpasswordform {
	<?php if ( ! empty( $gardenlogin_forget_form_bg_img ) ) : ?>
	background-image: url(<?php echo $gardenlogin_forget_form_bg_img; ?>);
	<?php endif; ?>
	<?php if ( ! empty( $gardenlogin_forget_form_bg_clr ) ) : ?>
	background-color: <?php echo $gardenlogin_forget_form_bg_clr; ?>;
	<?php endif; ?>
  <?php if ( ! empty( $gardenlogin_form_padding ) ) : ?>
	padding: <?php echo $gardenlogin_form_padding; ?>;
	<?php endif; ?>
	<?php if ( ! empty( $gardenlogin_form_background_clr ) ) : ?>
	background-color: <?php echo $gardenlogin_form_background_clr; ?>;
	<?php endif; ?>
}

#registerform {
  <?php if ( ! empty( $gardenlogin_form_padding ) ) : ?>
	padding: <?php echo $gardenlogin_form_padding; ?>;
	<?php endif; ?>
	<?php if ( ! empty( $gardenlogin_form_background_clr ) ) : ?>
	background-color: <?php echo $gardenlogin_form_background_clr; ?>;
	<?php endif; ?>
}


.login .custom-message {

  <?php if ( ! empty( $gardenlogin_welcome_bg_border ) ) : ?>
  border: <?php echo $gardenlogin_welcome_bg_border; ?>;
  <?php else : ?>
  border-left: 5px solid #009432;
  <?php endif; ?>

	<?php if ( ! empty( $gardenlogin_welcome_bg_color ) ) : ?>
	background-color: <?php echo $gardenlogin_welcome_bg_color; ?>;
  <?php else : ?>
  background-color: #fff;
	<?php endif; ?>

  padding: 12px;
  margin-left: 0;
  margin-bottom: 20px;
  -webkit-box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
  box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
}

.login #nav {
	<?php if ( ! empty( $gardenlogin_footer_bg_color ) ) : ?>
	background-color: <?php echo $gardenlogin_footer_bg_color; ?>;
	<?php endif; ?>
	<?php if ( isset( $gardenlogin_footer_display ) && '1' != $gardenlogin_footer_display) : ?>
		display: none;
	<?php endif; ?>
}

.login #nav a, .login #nav{
	<?php if ( ! empty( $gardenlogin_footer_decoration ) ) : ?>
	text-decoration: <?php echo $gardenlogin_footer_decoration; ?>;
	<?php endif; ?>
	<?php if ( ! empty( $gardenlogin_footer_text_color ) ) : ?>
	color: <?php echo $gardenlogin_footer_text_color; ?>;
	<?php endif; ?>
	<?php if ( ! empty( $gardenlogin_footer_font_size ) ) : ?>
	font-size: <?php echo $gardenlogin_footer_font_size . ';'; ?>;
	<?php endif; ?>

}

.login form .forgetmenot label{
	<?php if ( ! empty( $gardenlogin_remember_me_font_size ) ) : ?>
	font-size: <?php echo $gardenlogin_remember_me_font_size . 'px;'; ?>;
	<?php endif; ?>
}

.login input[type="submit"]{
	<?php if ( ! empty( $gardenlogin_login_button_text_size ) ) : ?>
	font-size: <?php echo $gardenlogin_login_button_text_size . 'px;'; ?>;
	<?php endif; ?>
	<?php if ( ! empty( $gardenlogin_login_button_width ) ) : ?>
	width: <?php echo $gardenlogin_login_button_width . '%;'; ?>;
	<?php endif; ?>
	<?php if ( ! empty( $gardenlogin_login_button_radius ) ) : ?>
	border-radius: <?php echo $gardenlogin_login_button_radius . 'px;'; ?>;
	<?php endif; ?>
}

.login #nav a:hover{
	<?php if ( ! empty( $gardenlogin_footer_text_hover ) ) : ?>
	color: <?php echo $gardenlogin_footer_text_hover; ?>;
	<?php endif; ?>
}

.login #backtoblog{
	<?php if ( ! empty( $gardenlogin_back_bg_color ) ) : ?>
	background-color: <?php echo $gardenlogin_back_bg_color; ?>;
	<?php endif; ?>
}

.login #backtoblog a{
	<?php if ( ! empty( $gardenlogin_back_decoration ) ) : ?>
	text-decoration: <?php echo $gardenlogin_back_decoration; ?>;
	<?php endif; ?>
	<?php if ( ! empty( $gardenlogin_back_text_color ) ) : ?>
	color: <?php echo $gardenlogin_back_text_color; ?>;
	<?php endif; ?>
	<?php if ( ! empty( $gardenlogin_back_font_size ) ) : ?>
	font-size: <?php echo $gardenlogin_back_font_size; ?>;
	<?php endif; ?>
	<?php if ( isset( $gardenlogin_back_display ) && '1' != $gardenlogin_back_display ) : ?>
	display: none;
	<?php endif; ?>
}

.login #backtoblog a:hover{
	<?php if ( ! empty( $gardenlogin_back_text_hover ) ) : ?>
	color: <?php echo $gardenlogin_back_text_hover; ?>;
	<?php endif; ?>
}

.loginHead {
	<?php if ( ! empty( $gardenlogin_header_bg_color ) ) : ?>
	background: <?php echo $gardenlogin_header_bg_color; ?>;
	<?php endif; ?>
}

.loginHead p a {
	<?php if ( ! empty( $gardenlogin_header_text_color ) ) : ?>
	color: <?php echo $gardenlogin_header_text_color; ?>;
	<?php endif; ?>
	<?php if ( ! empty( $gardenlogin_header_font_size ) ) : ?>
	font-size: <?php echo $gardenlogin_header_font_size; ?>;
	<?php endif; ?>
}

.loginHead p a:hover {
	<?php if ( ! empty( $gardenlogin_header_text_hover ) ) : ?>
	color: <?php echo $gardenlogin_header_text_hover; ?>;
	<?php endif; ?>
}

.loginFooter p a {
	margin: 0 5px;
	<?php if ( ! empty( $gardenlogin_footer_link_color ) ) : ?>
	color: <?php echo $gardenlogin_footer_link_color; ?>;
	<?php endif; ?>
	<?php if ( ! empty( $gardenlogin_footer_links_font_size ) ) : ?>
	font-size: <?php echo $gardenlogin_footer_links_font_size; ?>;
	<?php endif; ?>
}

.loginFooter p a:hover {
	<?php if ( ! empty( $gardenlogin_footer_link_hover ) ) : ?>
	color: <?php echo $gardenlogin_footer_link_hover; ?>;
	<?php endif; ?>
	<?php if ( ! empty( $gardenlogin_footer_links_hover_size ) ) : ?>
	font-size: <?php echo $gardenlogin_footer_links_hover_size; ?>;
	<?php endif; ?>
}

.loginInner {
	<?php if ( ! empty( $gardenlogin_footer_link_bg_clr ) ) : ?>
	background: <?php echo $gardenlogin_footer_link_bg_clr; ?>;
	<?php endif; ?>
}

<?php if ( ! empty( $gardenlogin_custom_css ) ) : ?>
<?php echo $gardenlogin_custom_css; ?>
<?php endif; ?>

.mcms-core-ui .button-primary{
text-shadow: none;
}

/*input:-webkit-autofill{
  transition: all 100000s ease-in-out 0s !important;
  transition-property: background-color, color !important;
}*/
.copyRight{
	padding: 12px 170px;
}
.gardenlogin-show-love{
  float: right;
  font-style: italic;
  padding-right: 20px;
  padding-bottom: 10px;
  position: absolute;
  bottom: 3px;
  right: 0;
  z-index: 10;
}
.gardenlogin-show-love a{
  text-decoration: none;
}
.love-postion{
	left: 0;
	padding-left: 20px;
}
@media screen and (max-width: 767px) {
		.login h1 a {
				max-width: 100%;
				background-size: contain !important;
		}
    .copyRight{
    	padding: 12px;
    }
}

</style>

<?php // $content = ob_get_clean(); ?>

<?php if ( ! empty( $gardenlogin_custom_js ) ) : ?>
<script>
<?php echo $gardenlogin_custom_js; ?>
</script>

<?php endif; ?>
