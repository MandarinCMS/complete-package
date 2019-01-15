/**
 * This file handling some LIVE to the GardenLogin Customizer live preview.
 */
jQuery(document).ready(function($) {

    // Update gallery default thumbnail on load. @since 1.1.3
    var defaultThumbnails = jQuery('.customize-control-checkbox-multiple input[type="radio"]:checked').next('label').find('img').attr('src');
    $('.gardenlogin_gallery_thumbnails:first-child').find('img').attr({'src': defaultThumbnails,'title': defaultThumbnails});

  /**
   * Presets Settings
   * @param  {[type]} ) {               checkbox_values [checkbox value]
   * @return {[type]}   [description]
   * @since 1.0.9
   * @version 1.1.3
   */
  jQuery( '.customize-control-checkbox-multiple input[type="radio"]' ).on( 'change', function() {

    checkbox_values = jQuery(this)
    .parents( '.customize-control' )
    .find( 'input[type="radio"]:checked' )
    .val();

    style_values = jQuery(this)
    .parents( '.customize-control' )
    .find( 'input[type="radio"]:checked' )
    .data('style');

    var val = [];
    val.push(checkbox_values);
    val.push(style_values);
    // console.log(val);
    jQuery(this)
    .parents( '.customize-control' )
    .find( 'input[type="hidden"]' )
    .val(checkbox_values)
    .delay(500)
    .trigger( 'change' );

    // Update gallery default thumbnail on presets change. @since 1.1.3
    var defaultThumbnails = jQuery(this).next('label').find('img').attr('src');
    $('.gardenlogin_gallery_thumbnails:first-child').find('img').attr({'src': defaultThumbnails,'title': defaultThumbnails});
    // if myskin is not Company remove label controls.
    if(checkbox_values == 'default2'){
      $('#customize-control-gardenlogin_customization-textfield_label_color,#customize-control-gardenlogin_customization-customize_form_label').hide();
    }else{
      $('#customize-control-gardenlogin_customization-textfield_label_color,#customize-control-gardenlogin_customization-customize_form_label').show();
    }

  } );
} ); // jQuery( document ).ready


(function($) {

  /**
   * [gardenlogin_find find CSS classes in MandarinCMS customizer]
   * @param  {String} [finder='#gardenlogin-customize'] [find class in customizer]
   * @return {[type]}                                  [iframe content finder]
   * @since 1.1.0
   * @version 1.1.3
   */
  function gardenlogin_find( finder = '#gardenlogin-customize' ) {

      var customizer_finder = $('#customize-preview iframe').contents().find( finder );
      return customizer_finder;
  }
  var formbgimg = '';
  // function for change GardenLogin background-image in real time...
  function gardenlogin_background_img( setting, target ) {
    mcms.customize( setting, function( value ) {
      value.bind( function( loginPressVal ) {
        if ( loginPressVal == '' ) {
          formbgimg = '';
          gardenlogin_find( target ).css( 'background-image', 'none' );
        } else {
          formbgimg = loginPressVal;
          gardenlogin_find( target ).css( 'background-image', 'url(' + loginPressVal + ')' );
        }
      } );
    } );
  } // ! gardenlogin_background_img();

  // function for change GardenLogin CSS in real time...
  function gardenlogin_css_property( setting, target, property, em = false ) {
    // Update the login logo width in real time...
    mcms.customize( setting, function( value ) {
      value.bind( function( loginPressVal ) {

        if ( loginPressVal == '' ) {
          gardenlogin_find( target ).css( property, em );
        } else {
          gardenlogin_find( target ).css( property, loginPressVal );
        }
      } );
    } );
  } // finish gardenlogin_css_property();

  // function for change GardenLogin CSS in real time...
  function gardenlogin_new_css_property( setting, target, property, suffix ) {
    // Update the login logo width in real time...
    mcms.customize( setting, function( value ) {
      value.bind( function( loginPressVal ) {

        if ( loginPressVal == '' ) {
          gardenlogin_find( target ).css( property, '' );
        } else {
          gardenlogin_find( target ).css( property, loginPressVal + suffix );
        }
      } );
    } );
  } // finish gardenlogin_css_property();

  // Declare Variable values for button shadow and button Opacity. Since 1.1.3

  var gardenlogin_button_shadow_opacity = 'rgba(0,0,0,1)',
  gardenlogin_button_shadow = 0,
  gardenlogin_button_inset_shadow = '';
  $(window).on('load', function(){
    gardenlogin_button_shadow_opacity = 'rgba(0,0,0,'+(parseInt($('#customize-control-gardenlogin_customization-textfield_shadow_opacity').find('.gardenlogin-range-slider_val').val())/100)+')';
    gardenlogin_button_shadow = $('#customize-control-gardenlogin_customization-textfield_shadow').find('.gardenlogin-range-slider_val').val();
    if($('#customize-control-gardenlogin_customization-textfield_inset_shadow').find('.gardenlogin-radio.gardenlogin-radio-ios').is(':checked')== true){
      gardenlogin_button_inset_shadow = ' inset';
    }
    gardenlogin_find( '#loginform input[type="text"], #loginform input[type="password"]' ).css( 'box-shadow', '0 0 ' + gardenlogin_button_shadow + 'px ' + gardenlogin_button_shadow_opacity + gardenlogin_button_inset_shadow );
    // if myskin is not Company remove label controls.
    var checkbox_values = $('#customize-control-customize_presets_settings input[type=radio]:checked').val();
    if(checkbox_values == 'default2'){
      $('#customize-control-gardenlogin_customization-textfield_label_color,#customize-control-gardenlogin_customization-customize_form_label').hide();
    }else{
      $('#customize-control-gardenlogin_customization-textfield_label_color,#customize-control-gardenlogin_customization-customize_form_label').show();
    }
  });

  // function for change GardenLogin Button Shadow in real time... since 1.1.3
  function gardenlogin_shadow_property( setting, target, property, suffix ) {
    // Update the login logo width in real time...
    mcms.customize( setting, function( value ) {
      value.bind( function( loginPressVal ) {

        if ( loginPressVal == '' ) {
          gardenlogin_find( target ).css( property, '' );
        } else {
          gardenlogin_button_shadow = loginPressVal;
          gardenlogin_find( target ).css( property, '0 0 ' + loginPressVal + 'px ' + gardenlogin_button_shadow_opacity + gardenlogin_button_inset_shadow );
        }
      } );
    } );
  } // finish gardenlogin_css_property();


  // function for change GardenLogin CSS in real time...
  function gardenlogin_shadow_opacity_property( setting, target, property, suffix ) {
    // Update the login logo width in real time...
    mcms.customize( setting, function( value ) {
      value.bind( function( loginPressVal ) {

        if ( loginPressVal == '' ) {
          // gardenlogin_find( target ).css( property, '' );
        } else {
          gardenlogin_button_shadow_opacity = 'rgba(0,0,0,'+(loginPressVal/100)+')';
          gardenlogin_find( target ).css( property, '0 0 ' + gardenlogin_button_shadow + 'px ' + gardenlogin_button_shadow_opacity + gardenlogin_button_inset_shadow );
          // gardenlogin_shadow_property( 'gardenlogin_customization[login_button_shadow]', '.login input[type="submit"]', 'box-shadow', 'px' );
        }
      } );
    } );
  } // finish gardenlogin_css_property();

  mcms.customize( 'gardenlogin_customization[textfield_inset_shadow]', function( value ) {
      value.bind( function( loginPressVal ) {
        if ( loginPressVal == true ) {
          // gardenlogin_find( target ).css( property, '' );
          gardenlogin_button_inset_shadow = ' inset';

        } else {
          gardenlogin_button_inset_shadow = '';
          // gardenlogin_button_shadow_opacity = 'rgba(0,0,0,'+(loginPressVal/100)+')';
          //
          // gardenlogin_shadow_property( 'gardenlogin_customization[login_button_shadow]', '.login input[type="submit"]', 'box-shadow', 'px' );
        }
        gardenlogin_find( '#loginform input[type="text"], #loginform input[type="password"]' ).css( 'box-shadow', '0 0 ' + gardenlogin_button_shadow + 'px ' + gardenlogin_button_shadow_opacity + gardenlogin_button_inset_shadow );
      } );
    } );

  // function for change GardenLogin attribute in real time...
  function gardenlogin_attr_property( setting, target, property ) {
    mcms.customize( setting, function( value ) {
      value.bind( function( loginPressVal ) {

        if ( loginPressVal == '' ) {
          gardenlogin_find( target ).attr( property, '' );
        } else {
          gardenlogin_find( target ).attr( property, loginPressVal );
        }
      } );
    } );
  }

  // function for change GardenLogin input fields in real time...
  function gardenlogin_input_property( setting, property ) {
    mcms.customize( setting, function( value ) {
      value.bind( function( loginPressVal ) {

        if ( loginPressVal == '' ) {
          gardenlogin_find( '.login input[type="text"]' ).css( property, '' );
          gardenlogin_find( '.login input[type="password"]' ).css( property, '' );
        } else {
          gardenlogin_find( '.login input[type="text"]' ).css( property, loginPressVal );
          gardenlogin_find( '.login input[type="password"]' ).css( property, loginPressVal );
        }
      } );
    } );
  } // finish gardenlogin_input_property();

  // function for change GardenLogin input fields in real time...
  function gardenlogin_new_input_property( setting, property, suffix ) {
    mcms.customize( setting, function( value ) {
      value.bind( function( loginPressVal ) {

        if ( loginPressVal == '' ) {
          gardenlogin_find( '.login input[type="text"]' ).css( property, '' );
          gardenlogin_find( '.login input[type="password"]' ).css( property, '' );
        } else {
          gardenlogin_find( '.login input[type="text"]' ).css( property, loginPressVal + suffix);
          gardenlogin_find( '.login input[type="password"]' ).css( property, loginPressVal + suffix);
        }
      } );
    } );
  } // finish gardenlogin_input_property();

  // function for change GardenLogin error and welcome messages in real time...
  /**
   * [gardenlogin_text_message GardenLogin (Error + Welcome) Message live Control.]
   * @param  id       [Unique ID of the section. ]
   * @param  target   [CSS Property]
   * @return string   [CSS property]
   */
  function gardenlogin_text_message( id, target ) {
    mcms.customize( id, function( value ) {
      value.bind( function( loginPressVal ) {

        if ( loginPressVal == '' ) {
          gardenlogin_find( target ).html('');
          gardenlogin_find( target ).css( 'display', 'none' );
        } else {
          gardenlogin_find( target ).html( loginPressVal );
          gardenlogin_find( target ).css( 'display', 'block' );
        }
      } );
    } );
  }

  /**
   * gardenlogin_change_form_label GardenLogin (Label) Text live Control.
   * @param  id       [Unique ID of the section. ]
   * @param  target   [CSS Property]
   * @since 1.1.3
   * @return string   [CSS property]
   */
  function gardenlogin_change_form_label( id, target ) {
    mcms.customize( id, function( value ) {
      value.bind( function( loginPressVal ) {

        if ( loginPressVal == '' ) {
          gardenlogin_find( target ).html('');
        } else {
          gardenlogin_find( target ).html( loginPressVal );
        }
      } );
    } );
  }

  var change_myskin;

  /**
   * Change the GardenLogin Presets MySkin.
   * @param  {[type]} value [Customized value from user.]
   * @return {[type]}       [MySkin ID]
   */
  mcms.customize( 'customize_presets_settings', function(value) {
    value.bind( function(loginPressVal) {

      change_myskin = loginPressVal;

    });
  });


  // function for change GardenLogin CSS in real time...
  function gardenlogin_display_control(setting) {
    // Update the login logo width in real time...
    mcms.customize(setting, function(value) {
      value.bind(function( loginPressVal ) {
        // Control on footer text.
        if ( 'gardenlogin_customization[footer_display_text]' == setting && false == loginPressVal ) {

          $( '#customize-preview iframe' ).contents().find( '.login #nav' ).css( 'display', 'none' );
          $('#customize-control-gardenlogin_customization-login_footer_text').fadeOut().css( 'display', 'none' );
          $('#customize-control-gardenlogin_customization-login_footer_text_decoration').fadeOut().css( 'display', 'none' );
          $('#customize-control-gardenlogin_customization-login_footer_color').fadeOut().css( 'display', 'none' );
          $('#customize-control-gardenlogin_customization-login_footer_color_hover').fadeOut().css( 'display', 'none' );
          $('#customize-control-gardenlogin_customization-login_footer_font_size').fadeOut().css( 'display', 'none' );
          $('#customize-control-gardenlogin_customization-login_footer_bg_color').fadeOut().css( 'display', 'none' );

        } else if ('gardenlogin_customization[footer_display_text]' == setting && true == loginPressVal ) {

          $( '#customize-preview iframe' ).contents().find( '.login #nav' ).css( 'display', 'block' );
          $('#customize-control-gardenlogin_customization-login_footer_text').fadeIn().css( 'display', 'list-item' );
          $('#customize-control-gardenlogin_customization-login_footer_text_decoration').fadeIn().css( 'display', 'list-item' );
          $('#customize-control-gardenlogin_customization-login_footer_color').fadeIn().css( 'display', 'list-item' );
          $('#customize-control-gardenlogin_customization-login_footer_color_hover').fadeIn().css( 'display', 'list-item' );
          $('#customize-control-gardenlogin_customization-login_footer_font_size').fadeIn().css( 'display', 'list-item' );
          $('#customize-control-gardenlogin_customization-login_footer_bg_color').fadeIn().css( 'display', 'list-item' );

        }

        // Control on footer back link text.
        if ('gardenlogin_customization[back_display_text]' == setting && false == loginPressVal ) {

          $( '#customize-preview iframe' ).contents().find( '.login #backtoblog' ).css( 'display', 'none' );
          $('#customize-control-gardenlogin_customization-login_back_text_decoration').fadeOut().css( 'display', 'none' );
          $('#customize-control-gardenlogin_customization-login_back_color').fadeOut().css( 'display', 'none' );
          $('#customize-control-gardenlogin_customization-login_back_color_hover').fadeOut().css( 'display', 'none' );
          $('#customize-control-gardenlogin_customization-login_back_font_size').fadeOut().css( 'display', 'none' );
          $('#customize-control-gardenlogin_customization-login_back_bg_color').fadeOut().css( 'display', 'none' );

        } else if ('gardenlogin_customization[back_display_text]' == setting && true == loginPressVal ) {

          $( '#customize-preview iframe' ).contents().find( '.login #backtoblog' ).css( 'display', 'block' );
          $('#customize-control-gardenlogin_customization-login_back_text_decoration').fadeIn().css( 'display', 'list-item' );
          $('#customize-control-gardenlogin_customization-login_back_color').fadeIn().css( 'display', 'list-item' );
          $('#customize-control-gardenlogin_customization-login_back_color_hover').fadeIn().css( 'display', 'list-item' );
          $('#customize-control-gardenlogin_customization-login_back_font_size').fadeIn().css( 'display', 'list-item' );
          $('#customize-control-gardenlogin_customization-login_back_bg_color').fadeIn().css( 'display', 'list-item' );

        }
      });
    });
  }

  // function for change GardenLogin error and welcome messages in real time...
  function gardenlogin_footer_text_message( errorlog, target ) {
    mcms.customize( errorlog, function(value) {
      value.bind(function(loginPressVal) {

        if ( loginPressVal == '' ) {
          gardenlogin_find(target).html('');
          if ( errorlog == 'gardenlogin_customization[login_footer_copy_right]' ) {
            gardenlogin_find(target).css( 'display', 'none' );
          }
        } else {
          gardenlogin_find(target).html(loginPressVal);
          if ( errorlog == 'gardenlogin_customization[login_footer_copy_right]' ) {
            gardenlogin_find(target).css( 'display', 'block' );
          }
        }
      });
    });
  }

  // Update the login logo width in real time...
  mcms.customize( 'gardenlogin_customization[setting_form_display_bg]', function( value ) {
    value.bind( function( loginPressVal ) {
      var formbg;
      if($('#customize-control-gardenlogin_customization-form_background_color .mcms-color-picker').val().length>0){
        formbg = $('#customize-control-gardenlogin_customization-form_background_color .mcms-color-picker').val();
      }

      if ( loginPressVal == true ) {
        gardenlogin_find( '#login, #loginform' ).css( 'background-color', 'transparent' );
        gardenlogin_find( '#login, #loginform' ).css( 'background-image', 'none' );
        $('#customize-control-gardenlogin_customization-form_background_color').fadeOut().hide();
        $('#customize-control-gardenlogin_customization-setting_form_background').fadeOut().hide();
      } else{
        gardenlogin_find('#loginform').css('background-image', 'url('+formbgimg+')');
          gardenlogin_find( '#login, #loginform' ).css( 'background-color', formbg );

        $('#customize-control-gardenlogin_customization-form_background_color').fadeIn().show();
        $('#customize-control-gardenlogin_customization-setting_form_background').fadeIn().show();
      }
    } );
  } );

  /**
   * [gardenlogin_customizer_bg GardenLogin Customizer Background Image Control that Retrive the Image URL w.r.t myskin]
   * @param  {[string]} customizer_bg [Preset Option]
   * @return {[URL]} gardenlogin_bg   [Image URL]
   */
  function gardenlogin_customizer_bg(customizer_bg) {

    if ( 'default1' == customizer_bg ) {
      gardenlogin_bg = 'url(' + gardenlogin_script.module_url + '/gardenlogin/img/bg.jpg)';
    } else if ( 'default2' == customizer_bg ) {
      gardenlogin_bg = 'url(' + gardenlogin_script.module_url + '/gardenlogin/img/bg2.jpg)';
    } else if ( 'default3' == customizer_bg ) {
      gardenlogin_bg = 'url(' + gardenlogin_script.module_url + '/gardenlogin-pro/img/bg3.jpg)';
    } else if ( 'default4' == customizer_bg ) {
      gardenlogin_bg = 'url(' + gardenlogin_script.module_url + '/gardenlogin-pro/img/bg4.jpg)';
    } else if ( 'default5' == customizer_bg ) {
      gardenlogin_bg = 'url(' + gardenlogin_script.module_url + '/gardenlogin-pro/img/bg5.jpg)';
    } else if ( 'default6' == customizer_bg ) {
      gardenlogin_bg = 'url(' + gardenlogin_script.module_url + '/gardenlogin-pro/img/bg6.jpg)';
    } else if ( 'default7' == customizer_bg ) {
      gardenlogin_bg = 'url(' + gardenlogin_script.module_url + '/gardenlogin-pro/img/bg7.jpg)';
    } else if ( 'default8' == customizer_bg ) {
      gardenlogin_bg = 'url(' + gardenlogin_script.module_url + '/gardenlogin-pro/img/bg8.jpg)';
    } else if ( 'default9' == customizer_bg ) {
      gardenlogin_bg = 'url(' + gardenlogin_script.module_url + '/gardenlogin-pro/img/bg9.jpg)';
    } else if ( 'default10' == customizer_bg ) {
      gardenlogin_bg = 'url(' + gardenlogin_script.module_url + '/gardenlogin-pro/img/bg10.jpg)';
    } else if ( 'default11' == customizer_bg ) {
      gardenlogin_bg = 'url(' + gardenlogin_script.module_url + '/gardenlogin-pro/img/bg11.png)';
    } else if ( 'default12' == customizer_bg ) {
      gardenlogin_bg = 'url(' + gardenlogin_script.module_url + '/gardenlogin-pro/img/bg12.jpg)';
    } else if ( 'default13' == customizer_bg ) {
      gardenlogin_bg = 'url(' + gardenlogin_script.module_url + '/gardenlogin-pro/img/bg13.jpg)';
    } else if ( 'default14' == customizer_bg ) {
      gardenlogin_bg = 'url(' + gardenlogin_script.module_url + '/gardenlogin-pro/img/bg14.jpg)';
    } else if ( 'default15' == customizer_bg ) {
      gardenlogin_bg = 'url(' + gardenlogin_script.module_url + '/gardenlogin-pro/img/bg15.jpg)';
    } else if ( 'default16' == customizer_bg ) {
      gardenlogin_bg = 'url(' + gardenlogin_script.module_url + '/gardenlogin-pro/img/bg16.jpg)';
    } else if ( 'default17' == customizer_bg ) {
      gardenlogin_bg = 'url(' + gardenlogin_script.module_url + '/gardenlogin-pro/img/bg17.jpg)';
    }
  }

  // Enable / Disable GardenLogin Background.
  mcms.customize( 'gardenlogin_customization[gardenlogin_display_bg]', function(value) {
    value.bind( function(loginPressVal) {

      // Check the myskin id.
      customizer_bg = change_myskin ? change_myskin : gardenlogin_script.login_myskin;

      // Set custom style on customizer.
      if ( gardenlogin_find().length == 0 ) {
        $("<style type='text/css' id='gardenlogin-customize'></style>").appendTo(gardenlogin_find('head'));
      }

      if ( loginPressVal == false ) { // Set conditions on behalf on myskins.

        if ( 'default6' == customizer_bg ) {
          gardenlogin_find().html( "#login::after{background-image: none}" );
        } else if ( 'default8' == customizer_bg ) {
          gardenlogin_find().html( "body.login::after{background: none}" );
        } else if ( 'default10' == customizer_bg ) {
          gardenlogin_find().html( "#login::after{background-image: none}" );
        } else if ( 'default17' == customizer_bg ) {
          gardenlogin_find().html( "#login{background: none}" );
        } else {
          gardenlogin_find('body.login').css('background-image', 'none');
        }

        // Turn Off the Dependencies controls.
        $('#customize-control-gardenlogin_customization-gardenlogin_display_bg').nextAll().hide();

      } else {
        if ( localStorage.gardenlogin_bg ) {

          gardenlogin_bg_ = 'url(' + localStorage.gardenlogin_bg + ')';

          if ( 'default6' == customizer_bg ) {
            gardenlogin_find().html( "#login::after{background-image: " + gardenlogin_bg_ + "}" );
          } else if ( 'default8' == customizer_bg ) {
            gardenlogin_find().html( "body.login::after{background: " + gardenlogin_bg_ + " no-repeat 0 0; background-size: cover}" );
          } else if ( 'default10' == customizer_bg ) {
            gardenlogin_find().html( "#login::after{background-image: " + gardenlogin_bg_ + "}" );
          } else if ( 'default17' == customizer_bg ) {
            gardenlogin_find().html( "#login{background: " + gardenlogin_bg_ + " no-repeat 0 0;}" );
          } else {
            gardenlogin_find('body.login').css( 'background-image', gardenlogin_bg_ );
          }

        } else if ( gardenlogin_script.gardenlogin_bg_url == true ) {

          if ( 'default6' == customizer_bg ) {
            gardenlogin_find().html( "#login::after{background-image: " + gardenlogin_script.gardenlogin_bg_url + "}" );
          } else if ( 'default8' == customizer_bg ) {
            gardenlogin_find().html( "body.login::after{background: " + gardenlogin_script.gardenlogin_bg_url + " no-repeat 0 0; background-size: cover}" );
          } else if ( 'default10' == customizer_bg ) {
            gardenlogin_find().html( "#login::after{background-image: " + gardenlogin_script.gardenlogin_bg_url + "}" );
          } else if ( 'default17' == customizer_bg ) {
            gardenlogin_find().html( "#login{background: " + gardenlogin_script.gardenlogin_bg_url + " no-repeat 0 0;}" );
          } else {
            gardenlogin_find('body.login').css( 'background-image', 'url(' + gardenlogin_script.gardenlogin_bg_url + ')' );
          }

        } else {

          /**
           * [gardenlogin_customizer_bg Retrive the Image URL w.r.t myskin]
           * @param  {[string]} customizer_bg [Preset Option]
           * @return {[URL]} gardenlogin_bg   [Image URL]
           */
          gardenlogin_customizer_bg(customizer_bg);
          if( $('#gardenlogin-gallery .image-select:checked').length > 0 && $('#gardenlogin-gallery .image-select:checked').parent('.gardenlogin_gallery_thumbnails').index() != 0 ) {
            gardenlogin_bg = $('#gardenlogin-gallery .image-select:checked').val();
            gardenlogin_bg = 'url(' + gardenlogin_bg + ')';
          }


          if ( 'default6' == customizer_bg ) {
            gardenlogin_find().html( "#login::after{background-image: " + gardenlogin_bg + "}" );
          } else if ( 'default8' == customizer_bg ) {
            gardenlogin_find().html( "body.login::after{background: " + gardenlogin_bg + " no-repeat 0 0; background-size: cover}" );
          } else if ( 'default10' == customizer_bg ) {
            gardenlogin_find().html( "#login::after{background-image: " + gardenlogin_bg + "}" );
          } else if ( 'default17' == customizer_bg ) {
            gardenlogin_find().html( "#login{background: " + gardenlogin_bg + " no-repeat 0 0;}" );
          } else {
            gardenlogin_find('body.login').css( 'background-image', gardenlogin_bg );
          }

          // Display Gallery Control.
          $('#customize-control-gardenlogin_customization-gallery_background').fadeIn().css( 'display', 'list-item' );
          if ( $('#customize-control-gardenlogin_customization-setting_background .attachment-media-view-image').length > 0  ) {
            $('#customize-control-gardenlogin_customization-gallery_background').css( 'display', 'none' );
          }
        }

        // Turn On the Dependencies controls.
        $('#customize-control-gardenlogin_customization-gardenlogin_display_bg').nextAll().show();
        if($('#customize-control-gardenlogin_customization-setting_background .attachment-thumb').length>0){
          $('#customize-control-gardenlogin_customization-gallery_background').hide();
        }

      } // endif; conditions on behalf on myskins.
    });
  });

  // Change GardenLogin Custom Background that choosen by user.
  mcms.customize( 'gardenlogin_customization[setting_background]', function(value) {
    value.bind( function(loginPressVal) {

      customizer_bg = change_myskin ? change_myskin : gardenlogin_script.login_myskin;

      if ( gardenlogin_find().length == 0 ) {
        $("<style type='text/css' id='gardenlogin-customize'></style>").appendTo( gardenlogin_find('head') );
      }

      if ( loginPressVal == '' ) {

        if ( localStorage.gardenlogin_bg ) {
          localStorage.removeItem("gardenlogin_bg");
        }

        /**
         * [gardenlogin_customizer_bg Retrive the Image URL w.r.t myskin]
         * @param  {[string]} customizer_bg [Preset Option]
         * @return {[URL]} gardenlogin_bg   [Image URL]
         */
        gardenlogin_customizer_bg(customizer_bg);
        if( $('#gardenlogin-gallery .image-select:checked').length > 0 && $('#gardenlogin-gallery .image-select:checked').parent('.gardenlogin_gallery_thumbnails').index() != 0 ) { // when remove custom background, set selected gallery bg
            gardenlogin_bg = $('#gardenlogin-gallery .image-select:checked').val();
            gardenlogin_bg = 'url('+gardenlogin_bg+')';
          }
        if ( 'default6' == customizer_bg ) {
          gardenlogin_find().html( "#login::after{background-image: " + gardenlogin_bg + "}" );
        } else if ( 'default8' == customizer_bg ) {
          gardenlogin_find().html( "body.login::after{background: " + gardenlogin_bg + " no-repeat 0 0; background-size: cover}" );
        } else if ( 'default10' == customizer_bg ) {
          gardenlogin_find().html( "#login::after{background-image: " + gardenlogin_bg + "}" );
        } else if ( 'default17' == customizer_bg ) {
          gardenlogin_find().html( "#login{background: " + gardenlogin_bg + " no-repeat 0 0;}" );
        } else {
          gardenlogin_find('body.login').css( 'background-image', gardenlogin_bg );

        }


        // Display the Gallery Control.
        $('#customize-control-gardenlogin_customization-gallery_background').fadeIn().css( 'display', 'list-item' );

      } else {

        // if (!localStorage.gardenlogin_bg) {
          localStorage.setItem("gardenlogin_bg", loginPressVal);
        // }

        if ( 'default6' == customizer_bg ) {
          gardenlogin_find().html( "#login::after{background-image: url(" + loginPressVal + ")}" );
        } else if ( 'default8' == customizer_bg ) {
          gardenlogin_find().html( "body.login::after{background: url(" + loginPressVal + ") no-repeat 0 0; background-size: cover}" );
        } else if ( 'default10' == customizer_bg ) {
          gardenlogin_find().html( "#login::after{background-image: url(" + loginPressVal + ")}" );
        } else if ( 'default17' == customizer_bg ) {
          gardenlogin_find().html( "#login{background: url(" + loginPressVal + ") no-repeat 0 0;}" );
        } else {
          gardenlogin_find('body.login').css( 'background-image', 'url(' + loginPressVal + ')' );
        }

        // Disable the Gallery Control.
        $('#customize-control-gardenlogin_customization-gallery_background').fadeOut().css( 'display', 'none' );
      }

    });
  });

  // Change GardenLogin Background Image that choosen from Gallery.
  mcms.customize( 'gardenlogin_customization[gallery_background]', function(value) {
    value.bind( function(loginPressVal) {

      // Check the myskin id.
      customizer_bg = change_myskin ? change_myskin : gardenlogin_script.login_myskin;

      // Set custom style on customizer.
      if ( gardenlogin_find().length == 0 ) {
        $("<style type='text/css' id='gardenlogin-customize'></style>").appendTo(gardenlogin_find('head'));
      }

      if ( gardenlogin_script.module_url + '/gardenlogin/img/gallery/img-1.jpg' == loginPressVal ) {

        /**
         * [gardenlogin_customizer_bg Retrive the Image URL w.r.t myskin]
         * @param  {[string]} customizer_bg [Preset Option]
         * @return {[URL]} gardenlogin_bg   [Image URL]
         */
        gardenlogin_customizer_bg(customizer_bg);
        console.log(gardenlogin_bg);
        if ( 'default6' == customizer_bg ) {
          gardenlogin_find().html( "#login::after{background-image: " + gardenlogin_bg + "}" );
        } else if ( 'default8' == customizer_bg ) {
          gardenlogin_find().html( "body.login::after{background: " + gardenlogin_bg + " no-repeat 0 0; background-size: cover}" );
        } else if ( 'default10' == customizer_bg ) {
          gardenlogin_find().html( "#login::after{background-image: " + gardenlogin_bg + "}" );
        } else if ( 'default17' == customizer_bg ) {
          gardenlogin_find().html( "#login{background: " + gardenlogin_bg + " no-repeat 0 0;}" );
        } else {
          gardenlogin_find('body.login').css( 'background-image', gardenlogin_bg );
        }

      } else {

        if ( 'default6' == customizer_bg ) {
          gardenlogin_find().html( "#login::after{background-image: url(" + loginPressVal + ")}" );
        } else if ( 'default8' == customizer_bg ) {
          gardenlogin_find().html( "body.login::after{background: url(" + loginPressVal + ") no-repeat 0 0; background-size: cover}" );
        } else if ( 'default10' == customizer_bg ) {
          gardenlogin_find().html( "#login::after{background-image: url(" + loginPressVal + ")}" );
        } else if ( 'default17' == customizer_bg ) {
          gardenlogin_find().html( "#login{background: url(" + loginPressVal + ") no-repeat 0 0;}" );
        } else {
          gardenlogin_find('body.login').css( 'background-image', 'url(' + loginPressVal + ')' );
        }

      }
    });
  });
  // gardenlogin_background_img( 'gardenlogin_customization[]', 'body.login' );
  $('.customize-controls-close').on('click', function() {
    // localStorage.removeItem("gardenlogin_bg_check");
    // localStorage.removeItem("gardenlogin_bg");
  });
  // localStorage.removeItem("gardenlogin_bg");
  // localStorage.removeItem("gardenlogin_bg_check");
  gardenlogin_display_control( 'gardenlogin_customization[footer_display_text]' );
  gardenlogin_display_control( 'gardenlogin_customization[back_display_text]' );

  // Update the MandarinCMS login logo in real time...
  mcms.customize( 'gardenlogin_customization[setting_logo]', function(value) {
    value.bind( function(loginPressVal) {

      if ( loginPressVal == '' ) {
        gardenlogin_find('#login h1 a').css( 'background-image', 'url(' + gardenlogin_script.admin_url + '/images/mandarincms-logo.svg)' );
      } else {
        gardenlogin_find('#login h1 a').css( 'background-image', 'url(' + loginPressVal + ')' );
      }
    });
  });

  // Enable / Disabe MandarinCMS login logo in real time... since 1.1.3
  mcms.customize( 'gardenlogin_customization[setting_logo_display]', function(value) {
    value.bind( function(loginPressVal) {
      if ( loginPressVal == true ) {
        gardenlogin_find('#login h1').fadeOut();
        $('#customize-control-gardenlogin_customization-setting_logo_display').nextAll().hide();
      } else {
        gardenlogin_find('#login h1').fadeIn();
        $('#customize-control-gardenlogin_customization-setting_logo_display').nextAll().show();
      }
    });
  });

  /**
   * [gardenlogin_new_css_property Apply Live JS on MandarinCMS Login Page Logo]
   * @param  {[type]} gardenlogin_customization [Section ID]
   * @param  {[type]} login                    [Targeted CSS]
   * @param  {[type]} width                    [Property]
   * @param  {[type]} px                       [Unit]
   */
  gardenlogin_new_css_property( 'gardenlogin_customization[customize_logo_width]', '#login h1 a', 'width', 'px' );
  gardenlogin_new_css_property( 'gardenlogin_customization[customize_logo_height]', '#login h1 a', 'height', 'px' );
  gardenlogin_new_css_property( 'gardenlogin_customization[customize_logo_padding]', '#login h1 a', 'margin-bottom', 'px' );

  gardenlogin_attr_property( 'gardenlogin_customization[customize_logo_hover]', '#login h1 a', 'href' );
  gardenlogin_attr_property( 'gardenlogin_customization[customize_logo_hover_title]', '#login h1 a', 'title' );

  // Live Background color change.
    mcms.customize( 'gardenlogin_customization[setting_background_color]', function(value) {
      value.bind( function(loginPressVal) {

        customizer_bg = change_myskin ? change_myskin : gardenlogin_script.login_myskin;

        if ( gardenlogin_find('#gardenlogin-iframe-bgColor').length == 0 ) {
          $("<style type='text/css' id='gardenlogin-iframe-bgColor'></style>").appendTo(gardenlogin_find('head'));
        }

        if ( loginPressVal == '' ) {

          if ( 'default6' == customizer_bg || 'default10' == customizer_bg ) {
            gardenlogin_find('#gardenlogin-iframe-bgColor' ).html( "#login::after{background-color: #ffffff}" );
          } else if ( 'default17' == customizer_bg ) {
            gardenlogin_find('#login').css( "background-color" , "'#ffffff'" );
          } else if ( 'default8' == customizer_bg ) {
            gardenlogin_find('#gardenlogin-iframe-bgColor').html( "body.login::after{background-color: #ffffff}" );
          } else {
            gardenlogin_find('body.login').css( "background-color", "#ffffff" );
          }
        } else {

          if ( 'default6' == customizer_bg || 'default10' == customizer_bg ) {
            gardenlogin_find('#gardenlogin-iframe-bgColor').html( "#login::after{background-color: " + loginPressVal + "}" );
          } else if ( 'default17' == customizer_bg ) {
            gardenlogin_find('#login').css( "background-color" , loginPressVal );
          } else if ( 'default8' == customizer_bg ) {
            gardenlogin_find('#gardenlogin-iframe-bgColor').html( "body.login::after{background-color: " + loginPressVal + "}" );
          } else {
            gardenlogin_find('body.login').css( "background-color", loginPressVal );
          }
        }
      });
    });


  // Live Background Repeat change.
  mcms.customize( 'gardenlogin_customization[background_repeat_radio]', function(value) {
    value.bind(function(loginPressVal) {

      customizer_bg = change_myskin ? change_myskin : gardenlogin_script
        .login_myskin;

        if ( gardenlogin_find('#gardenlogin-scbg-repeat').length == 0 ) {
          $("<style type='text/css' id='gardenlogin-scbg-repeat'></style>").appendTo(gardenlogin_find('head'));
        }

      if ( loginPressVal != '' ) {

        if ( 'default6' == customizer_bg || 'default10' == customizer_bg ) {
          gardenlogin_find('#gardenlogin-scbg-repeat').html( "#login::after{background-repeat: " + loginPressVal + "}" );
        } else if ( 'default17' == customizer_bg ) {
          gardenlogin_find('#login').css( "background-repeat" , loginPressVal );
        } else if ( 'default8' == customizer_bg ) {
          gardenlogin_find('#gardenlogin-scbg-repeat').html( "body.login::after{background-repeat: " + loginPressVal + "}" );
        } else {
          gardenlogin_find('body.login').css( "background-repeat", loginPressVal );
        }

      }
    });
  });

  // Live Background Image Size Change.
  mcms.customize( 'gardenlogin_customization[background_image_size]', function(value) {
    value.bind( function(loginPressVal) {

      customizer_bg = change_myskin ? change_myskin : gardenlogin_script.login_myskin;

        if ( gardenlogin_find('#gardenlogin-scbg-size').length == 0 ) {
          $("<style type='text/css' id='gardenlogin-scbg-size'></style>").appendTo(gardenlogin_find('head'));
        }

      if ( loginPressVal != '' ) {

        if ( 'default6' == customizer_bg || 'default10' == customizer_bg ) {
          gardenlogin_find('#gardenlogin-scbg-size').html( "#login::after{background-size: " + loginPressVal + "}" );
        } else if ( 'default17' == customizer_bg ) {
          gardenlogin_find('#login').css( "background-size" , loginPressVal );
        } else if ( 'default8' == customizer_bg ) {
          gardenlogin_find('#gardenlogin-scbg-size').html( "body.login::after{background-size: " + loginPressVal + "}" );
        } else {
          gardenlogin_find('body.login').css( "background-size", loginPressVal );
        }

      }
    });
  });

  // Live Background Position Change.
  mcms.customize( 'gardenlogin_customization[background_position]', function(value) {
    value.bind( function(loginPressVal) {

      customizer_bg = change_myskin ? change_myskin : gardenlogin_script.login_myskin;

        if ( gardenlogin_find('#gardenlogin-scbg-position').length == 0 ) {
          $("<style type='text/css' id='gardenlogin-scbg-position'></style>").appendTo(gardenlogin_find('head'));
        }

      if ( loginPressVal != '' ) {

        if ( 'default6' == customizer_bg || 'default10' == customizer_bg ) {
          gardenlogin_find('#gardenlogin-scbg-position').html( "#login::after{background-position: " + loginPressVal + "}" );
        } else if ( 'default17' == customizer_bg ) {
          gardenlogin_find('#login').css( "background-position" , loginPressVal );
        } else if ( 'default8' == customizer_bg ) {
          gardenlogin_find('#gardenlogin-scbg-position').html( "body.login::after{background-position: " + loginPressVal + "}" );
        } else {
          gardenlogin_find('body.login').css( "background-position", loginPressVal );
        }

      }
    });
  });


  gardenlogin_background_img( 'gardenlogin_customization[setting_form_background]', '#loginform');

  gardenlogin_new_css_property( 'gardenlogin_customization[customize_form_width]', '#login', 'max-width', 'px' );
  gardenlogin_new_css_property( 'gardenlogin_customization[customize_form_height]', '#loginform', 'min-height', 'px' );
  gardenlogin_css_property( 'gardenlogin_customization[customize_form_padding]', '#loginform', 'padding' );
  gardenlogin_css_property( 'gardenlogin_customization[customize_form_border]', '#loginform', 'border' );

  gardenlogin_new_input_property( 'gardenlogin_customization[textfield_width]', 'width', '%' );
  gardenlogin_input_property( 'gardenlogin_customization[textfield_margin]', 'margin' );
  gardenlogin_input_property( 'gardenlogin_customization[textfield_background_color]', 'background' );
  gardenlogin_input_property( 'gardenlogin_customization[textfield_color]', 'color' );

  gardenlogin_css_property( 'gardenlogin_customization[form_background_color]', '#loginform, #login', 'background-color', '#FFF' );
  gardenlogin_css_property( 'gardenlogin_customization[textfield_label_color]', '.login label[for="user_login"], .login label[for="user_pass"]', 'color', '#777' );
  gardenlogin_css_property( 'gardenlogin_customization[remember_me_label_size]', '.login label[for="rememberme"]', 'color', '#777' );

  gardenlogin_new_css_property( 'gardenlogin_customization[textfield_radius]', '#loginform input[type="text"], #loginform input[type="password"], #registerform input[type="text"], #registerform input[type="password"], #registerform input[type="number"], #registerform input[type="email"], #lostpasswordform input[type="text"]', 'border-radius', 'px' );

  gardenlogin_shadow_property( 'gardenlogin_customization[textfield_shadow]', '#loginform input[type="text"], #loginform input[type="password"], #registerform input[type="text"], #registerform input[type="password"], #registerform input[type="number"], #registerform input[type="email"], #lostpasswordform input[type="text"]', 'box-shadow', 'px' );
  gardenlogin_shadow_opacity_property( 'gardenlogin_customization[textfield_shadow_opacity]', '#loginform input[type="text"], #loginform input[type="password"], #registerform input[type="text"], #registerform input[type="password"], #registerform input[type="number"], #registerform input[type="email"], #lostpasswordform input[type="text"]', 'box-shadow', 'px' );

  gardenlogin_background_img( 'gardenlogin_customization[forget_form_background]', '#lostpasswordform' );
  gardenlogin_css_property( 'gardenlogin_customization[forget_form_background_color]', '#lostpasswordform', 'background-color' );

  gardenlogin_new_css_property( 'gardenlogin_customization[customize_form_radius]', '#login', 'border-radius', 'px' );
  gardenlogin_shadow_property( 'gardenlogin_customization[customize_form_shadow]', '#login', 'box-shadow', 'px' );
  gardenlogin_shadow_opacity_property( 'gardenlogin_customization[customize_form_opacity]', '#login', 'box-shadow', 'px' );

  //Buttons starts.
  // Update the login form button background in real time...
  var loginPressBtnClr;
  var loginPressBtnHvr;
  mcms.customize( 'gardenlogin_customization[custom_button_color]', function(value) {
    value.bind( function(loginPressVal) {
      if ( loginPressVal == '' ) {
        loginPressBtnClr = undefined;
        gardenlogin_find('.mcms-core-ui #login  .button-primary').css( 'background', '' );
        gardenlogin_find('.mcms-core-ui #login  .button-primary').on( 'mouseover', function() {
          if ( typeof loginPressBtnHvr !== "undefined" || loginPressBtnHvr === null ) {
            $(this).css( 'background', loginPressBtnHvr );
          } else {
            $(this).css( 'background', '' );
          }
          }).on( 'mouseleave', function() {
          $(this).css( 'background', '' );
        });
      } else {
        gardenlogin_find('.mcms-core-ui #login .button-primary').css( 'background', loginPressVal );
        loginPressBtnClr = loginPressVal;

        gardenlogin_find('.mcms-core-ui #login  .button-primary').on( 'mouseover', function() {
          if ( typeof loginPressBtnHvr !== "undefined" || loginPressBtnHvr === null ) {
            $(this).css( 'background', loginPressBtnHvr );
          } else {
            $(this).css( 'background', '' );
          }
          }).on( 'mouseleave', function() {
          $(this).css( 'background', loginPressVal );
        });
      }
    });
  });

  var loginPressBtnBrdrClr;
  // Update the login form button border-color in real time...
  mcms.customize( 'gardenlogin_customization[button_border_color]', function(value) {
    value.bind( function(loginPressVal) {
      if ( loginPressVal == '' ) {
        gardenlogin_find('.mcms-core-ui #login  .button-primary').css( 'border-color', '' );
      } else {
        gardenlogin_find('.mcms-core-ui #login  .button-primary').css( 'border-color', loginPressVal );
        loginPressBtnBrdrClr = loginPressVal;
      }
    });
  });

  // Update the login form button border-color in real time...
  mcms.customize( 'gardenlogin_customization[button_hover_color]', function(value) {
    value.bind( function(loginPressVal) {
      console.log(loginPressVal);
      if ( loginPressVal == '' ) {
        loginPressBtnHvr = undefined;
        // gardenlogin_find('.mcms-core-ui #login  .button-primary').css( 'background', '' );
        gardenlogin_find('.mcms-core-ui #login  .button-primary').on( 'mouseover', function() {
            $(this).css( 'background', '' );
          }).on( 'mouseleave', function() {
          if ( typeof loginPressBtnClr !== "undefined" || loginPressBtnClr === null ) {
            $(this).css( 'background', loginPressBtnClr );
          } else {
            $(this).css( 'background', '' );
          }
        });
      } else {
        loginPressBtnHvr = loginPressVal;
        gardenlogin_find('.mcms-core-ui #login  .button-primary').on( 'mouseover', function() {
            $(this).css( 'background', loginPressVal );
          }).on( 'mouseleave', function() {
          if ( typeof loginPressBtnClr !== "undefined" || loginPressBtnClr === null ) {
            $(this).css( 'background', loginPressBtnClr );
          } else {
            $(this).css( 'background', '' );
          }
        });
      }
    });
  });

  // Update the login form button border-color in real time...
  mcms.customize( 'gardenlogin_customization[button_hover_border]', function(value) {
    value.bind( function(loginPressVal) {
      if ( loginPressVal == '' ) {
        gardenlogin_find('.mcms-core-ui #login  .button-primary').css( 'border-color', '' );
      } else {
        gardenlogin_find('.mcms-core-ui #login  .button-primary').on( 'mouseover', function() {
            $(this).css( 'border-color', loginPressVal );
          }).on( 'mouseleave', function() {
          if ( typeof loginPressBtnBrdrClr !== "undefined" || loginPressBtnBrdrClr === null ) {
            $(this).css( 'border-color', loginPressBtnBrdrClr );
          } else {
            $(this).css( 'border-color', '' );
          }
        });
      }
    });
  });

  // Update the login form button border-color in real time...
  mcms.customize( 'gardenlogin_customization[custom_button_shadow]', function(value) {
    value.bind( function(loginPressVal) {
      if ( loginPressVal == '' ) {
        gardenlogin_find('.mcms-core-ui #login .button-primary').css( 'box-shadow', '' );
      } else {
        gardenlogin_find('.mcms-core-ui #login .button-primary').css( 'box-shadow', loginPressVal );
      }
    });
  });

  // Update the login form button border-color in real time...
  mcms.customize( 'gardenlogin_customization[button_text_color]', function(value) {
    value.bind( function(loginPressVal) {
      if ( loginPressVal == '' ) {
        gardenlogin_find('.mcms-core-ui #login .button-primary').css( 'color', '' );
      } else {
        gardenlogin_find('.mcms-core-ui #login .button-primary').css( 'color', loginPressVal );
      }
    });
  });

  /**
   * MandarinCMS Login Form Label Change.
   * @since 1.1.3
   */
  gardenlogin_change_form_label( 'gardenlogin_customization[form_username_label]', '.login label[for="user_login"] span' );
  gardenlogin_change_form_label( 'gardenlogin_customization[form_password_label]', '.login label[for="user_pass"] span' );

  /**
   * MandarinCMS Login Page Footer Message.
   */
  gardenlogin_change_form_label( 'gardenlogin_customization[login_footer_text]', '.login #nav a:nth-child(3)' );

  // gardenlogin_css_property( 'gardenlogin_customization[footer_display_text]', '.login #nav', 'display' );
  gardenlogin_css_property( 'gardenlogin_customization[login_footer_text_decoration]', '.login #nav a', 'text-decoration' );

  var loginPressFtrClr;
  var loginPressFtrHvr;
  // Update the login form button border-color in real time...
  mcms.customize( 'gardenlogin_customization[login_footer_color]', function(value) {
    value.bind( function(loginPressVal) {

      if ( loginPressVal == '' ) {
        gardenlogin_find('.login #nav a, .login #nav').css( 'color', '' );
        gardenlogin_find('.login #nav a, .login #nav').on( 'mouseover', function() {
          if ( typeof loginPressFtrHvr !== "undefined" || loginPressFtrHvr === null ) {
            $(this).css( 'color', loginPressFtrHvr );
          } else {
            $(this).css( 'color', '' );
          }
        }).on( 'mouseleave', function() {
          $(this).css( 'color', '' );
        });
      } else {
        loginPressFtrClr = loginPressVal;
        gardenlogin_find('.login #nav a, .login #nav').css( 'color', loginPressVal );
        gardenlogin_find('.login #nav a, .login #nav').on( 'mouseover', function() {
          if ( typeof loginPressFtrHvr !== "undefined" || loginPressFtrHvr === null ) {
            $(this).css( 'color', loginPressFtrHvr );
          } else {
            $(this).css( 'color', '' );
          }
        }).on( 'mouseleave', function() {
          $(this).css( 'color', loginPressVal );
        });
      }
    });
  });

  // Update the login form button border-color in real time...
  mcms.customize( 'gardenlogin_customization[login_footer_color_hover]', function(value) {
    value.bind( function(loginPressVal) {

      if ( loginPressVal == '' ) {
        gardenlogin_find('.login #nav a').css( 'color', '' );
        gardenlogin_find('.login #nav a').on( 'mouseover', function() {
          $(this).css( 'color', '' );
        }).on( 'mouseleave', function() {
          if ( typeof loginPressFtrClr !== "undefined" || loginPressFtrClr === null ) {
            $(this).css( 'color', loginPressFtrClr );
          } else {
            $(this).css( 'color', '' );
          }
        });
      } else {
        loginPressFtrHvr = loginPressVal;
        gardenlogin_find('.login #nav a').on( 'mouseover', function() {
          $(this).css('color', loginPressVal);
        }).on('mouseleave', function() {
          if ( typeof loginPressFtrClr !== "undefined" || loginPressFtrClr === null ) {
            $(this).css( 'color', loginPressFtrClr );
          } else {
            $(this).css( 'color', '' );
          }
        });
      }
    });
  });

  gardenlogin_new_css_property( 'gardenlogin_customization[login_footer_font_size]', '.login #nav a', 'font-size', 'px' );
  gardenlogin_new_css_property( 'gardenlogin_customization[customize_form_label]', '.login label[for="user_login"], .login label[for="user_pass"]', 'font-size', 'px' );
  gardenlogin_new_css_property( 'gardenlogin_customization[remember_me_font_size]', '.login form .forgetmenot label', 'font-size', 'px' );
  gardenlogin_css_property( 'gardenlogin_customization[login_footer_bg_color]', '.login #nav', 'background-color', 'transparent' );

  // gardenlogin_css_property( 'gardenlogin_customization[back_display_text]', '.login #backtoblog', 'display' );
  gardenlogin_css_property( 'gardenlogin_customization[login_back_text_decoration]', '.login #backtoblog a', 'text-decoration' );

  var loginPressFtrBackClr;
  var loginPressFtrBackHvr;
  /**
   * Change GardenLogin 'Back to Blog(link)' color live.
   */
  mcms.customize( 'gardenlogin_customization[login_back_color]', function( value ) {
    value.bind(function( loginPressVal ) {

      if ( loginPressVal == '' ) {
        gardenlogin_find('.login #backtoblog a').css( 'color', '' );
        gardenlogin_find('.login #backtoblog a').on( 'mouseover', function() {
          if ( typeof loginPressFtrBackHvr !== "undefined" || loginPressFtrBackHvr === null ) {
            $(this).css( 'color', loginPressFtrBackHvr );
          } else {
            $(this).css( 'color', '' );
          }
        } )
        .on( 'mouseleave', function() {
          $(this).css( 'color', '' );
        } );
      } else {
        loginPressFtrBackClr = loginPressVal;
        gardenlogin_find('.login #backtoblog a').css( 'color', loginPressVal );
        gardenlogin_find('.login #backtoblog a').on( 'mouseover', function() {
          if ( typeof loginPressFtrBackHvr !== "undefined" || loginPressFtrBackHvr === null ) {
            $(this).css( 'color', loginPressFtrBackHvr );
          } else {
            $(this).css( 'color', '' );
          }
        } )
        .on( 'mouseleave', function() {
          $(this).css( 'color', loginPressVal );
        });
      }
    });
  });

  /**
   * Change GardenLogin 'Button' CSS. Since 1.1.3
   */
  gardenlogin_new_css_property( 'gardenlogin_customization[login_button_size]', '.login input[type="submit"]', 'width', '%' );
  gardenlogin_new_css_property( 'gardenlogin_customization[login_button_top]', '.mcms-core-ui .button-group.button-large .button, .mcms-core-ui .button.button-large', 'padding-top', 'px' );
  gardenlogin_new_css_property( 'gardenlogin_customization[login_button_bottom]', '.mcms-core-ui .button-group.button-large .button, .mcms-core-ui .button.button-large', 'padding-bottom', 'px' );
  gardenlogin_new_css_property( 'gardenlogin_customization[login_button_radius]', '.login input[type="submit"]', 'border-radius', 'px' );
  gardenlogin_shadow_property( 'gardenlogin_customization[login_button_shadow]', '.login input[type="submit"]', 'box-shadow', 'px' );
  gardenlogin_shadow_opacity_property( 'gardenlogin_customization[login_button_shadow_opacity]', '.login input[type="submit"]', 'box-shadow', 'px' );
  gardenlogin_new_css_property( 'gardenlogin_customization[login_button_text_size]', '.login input[type="submit"]', 'font-size', 'px' );

  /**
   * Change GardenLogin 'Back to Blog(link)' hover color live.
   */
  mcms.customize( 'gardenlogin_customization[login_back_color_hover]', function( value ) {
    value.bind( function( loginPressVal ) {

      if ( loginPressVal == '' ) {

        gardenlogin_find('.login #backtoblog a').css( 'color', '' );

        gardenlogin_find('.login #backtoblog a').on( 'mouseover', function() {
          $(this).css( 'color', '' );
        } )
        .on( 'mouseleave', function() {
          if ( typeof loginPressFtrBackClr !== "undefined" || loginPressFtrBackClr === null ) {
            $(this).css( 'color', loginPressFtrBackClr );
          } else {
            $(this).css( 'color', '' );
          }
        });
      } else {
        loginPressFtrBackHvr = loginPressVal;
        gardenlogin_find('.login #backtoblog a').on( 'mouseover', function() {
          $(this).css( 'color', loginPressVal );
        } )
        .on( 'mouseleave', function() {
          if ( typeof loginPressFtrBackClr !== "undefined" || loginPressFtrBackClr === null ) {
            $(this).css( 'color', loginPressFtrBackClr );
          } else {
            $(this).css( 'color', '' );
          }
        });
      }
    });
  });

  /**
   * MandarinCMS Login Page Footer Style.
   */
  gardenlogin_new_css_property( 'gardenlogin_customization[login_back_font_size]', '.login #backtoblog a', 'font-size', 'px' );
  gardenlogin_css_property( 'gardenlogin_customization[login_back_font_size]', '.login #backtoblog a', 'font-size' );
  gardenlogin_css_property( 'gardenlogin_customization[login_back_bg_color]', '.login #backtoblog', 'background-color', 'transparent' );
  gardenlogin_footer_text_message( 'gardenlogin_customization[login_footer_copy_right]', '.copyRight' );

  /**
   * MandarinCMS Login Page Error Messages.
   */
  gardenlogin_text_message( 'gardenlogin_customization[incorrect_username]', '#login_error' );
  gardenlogin_text_message( 'gardenlogin_customization[incorrect_password]', '#login_error' );
  gardenlogin_text_message( 'gardenlogin_customization[empty_username]', '#login_error' );
  gardenlogin_text_message( 'gardenlogin_customization[empty_password]', '#login_error' );
  gardenlogin_text_message( 'gardenlogin_customization[invalid_email]', '#login_error' );
  gardenlogin_text_message( 'gardenlogin_customization[empty_email]', '#login_error' );
  gardenlogin_text_message( 'gardenlogin_customization[invalidcombo_message]', '#login_error' );

  /**
   * MandarinCMS Login Page Welcome Messages.
   */
  gardenlogin_text_message( 'gardenlogin_customization[lostpwd_welcome_message]', '.login-action-lostpassword .custom-message' );
  gardenlogin_text_message( 'gardenlogin_customization[welcome_message]', '.login-action-login .custom-message' );
  gardenlogin_text_message( 'gardenlogin_customization[register_welcome_message]', '.login-action-register .custom-message' );
  gardenlogin_text_message( 'gardenlogin_customization[logout_message]', '.login .custom-message' );

  /**
   * MandarinCMS Login Page Welcome Messages Style.
   */
  gardenlogin_css_property( 'gardenlogin_customization[message_background_border]', '.login .custom-message', 'border' );
  gardenlogin_css_property( 'gardenlogin_customization[message_background_color]', '.login .custom-message', 'background-color' );

  /**
   * Enable / Disable GardenLogin Footer link.
   */
  mcms.customize( 'gardenlogin_customization[gardenlogin_show_love]', function( value ) {
    value.bind( function( loginPressVal ) {

      if ( loginPressVal == false ) {
        gardenlogin_find('.gardenlogin-show-love').fadeOut().hide();
        $('#customize-control-gardenlogin_customization-gardenlogin_show_love').nextAll().hide();
      } else {
        if(gardenlogin_find('.gardenlogin-show-love').length===0){
          $('<div class="gardenlogin-show-love">Powered by: <a href="https://mandarincms.com" target="_blank">Mandarin CMS</a></div>').insertBefore($('#customize-preview iframe').contents().find('.footer-wrapper'));
        }
        gardenlogin_find('.gardenlogin-show-love').fadeIn().show();
        $('#customize-control-gardenlogin_customization-gardenlogin_show_love').nextAll().show();
      }
    } );
  } );

  /**
   * Set position of Footer link.
   */
  mcms.customize( 'gardenlogin_customization[show_love_position]', function( value ) {
    value.bind( function( loginPressVal ) {
      if ( loginPressVal == 'left' ) {
        gardenlogin_find('.gardenlogin-show-love').addClass('love-postion');
      } else {
        gardenlogin_find('.gardenlogin-show-love').removeClass('love-postion');
      }
    } );
  } );

  /**
   * Set position of Footer link.
   */
  mcms.customize( 'gardenlogin_customization[login_copy_right_display]', function( value ) {
    value.bind( function( loginPressVal ) {
      if ( loginPressVal == true ) {
        if( gardenlogin_find('.copyRight').length == 0 ){
          gardenlogin_find('.footer-cont').html('<div class="copyRight">'+$('[id="_customize-input-gardenlogin_customization[login_footer_copy_right]"]').val()+'</div>');
        }
        $('#customize-control-gardenlogin_customization-login_footer_copy_right').show();
      } else {
        gardenlogin_find('.copyRight').remove();
        $('#customize-control-gardenlogin_customization-login_footer_copy_right').hide();
      }
    } );
  } );

  /**
   * Change GardenLogin Google reCaptcha size in real time...
   */
  mcms.customize( 'gardenlogin_customization[recaptcha_size]', function( value ) {
    value.bind( function( loginPressVal ) {

      if ( loginPressVal == '' ) {
        gardenlogin_find('.gardenlogin_recaptcha_wrapper .g-recaptcha').css( 'transform', '' );
      } else {
        gardenlogin_find('.gardenlogin_recaptcha_wrapper .g-recaptcha').css( 'transform', 'scale(' + loginPressVal + ')' );
      }
    });
  });

  $(window).on('load', function() {

    if ( $('#customize-control-gardenlogin_customization-setting_logo_display input[type="checkbox"]').is(":checked") ) {
      $('#customize-control-gardenlogin_customization-setting_logo_display').nextAll().hide();
    } else {
      $('#customize-control-gardenlogin_customization-setting_logo_display').nextAll().show();
    }

    if ( $('#customize-control-gardenlogin_customization-gardenlogin_show_love input[type="checkbox"]').is(":checked") ) {
      $('#customize-control-gardenlogin_customization-gardenlogin_show_love').nextAll().show();
    } else {
      $('#customize-control-gardenlogin_customization-gardenlogin_show_love').nextAll().hide();
    }

    if ( $('#customize-control-gardenlogin_customization-gardenlogin_display_bg input[type="checkbox"]').is(":checked") ) {
      $('#customize-control-gardenlogin_customization-gardenlogin_display_bg').nextAll().show();
      if($('#customize-control-gardenlogin_customization-setting_background .attachment-thumb').length>0){
        $('#customize-control-gardenlogin_customization-gallery_background').hide();
      }
    } else {
      $('#customize-control-gardenlogin_customization-gardenlogin_display_bg').nextAll().hide();
    }

    if ( $('#customize-control-gardenlogin_customization-setting_background .attachment-media-view-image').length > 0  ) {
      $('#customize-control-gardenlogin_customization-gallery_background').css( 'display', 'none' );
    }

    if ( $('#customize-control-gardenlogin_customization-setting_form_display_bg input[type="checkbox"]').is(":checked") ) {
      $('#customize-control-gardenlogin_customization-form_background_color').css( 'display', 'none' );
    } else {
      $('#customize-control-gardenlogin_customization-form_background_color').css( 'display', 'list-item' );
    }

    if ( $('#customize-control-gardenlogin_customization-footer_display_text input[type="checkbox"]').is(":checked") ) {

      $('#customize-control-gardenlogin_customization-login_footer_text').css( 'display', 'list-item' );
      $('#customize-control-gardenlogin_customization-login_footer_text_decoration').css( 'display', 'list-item' );
      $('#customize-control-gardenlogin_customization-login_footer_color').css( 'display', 'list-item' );
      $('#customize-control-gardenlogin_customization-login_footer_color_hover').css( 'display', 'list-item' );
      $('#customize-control-gardenlogin_customization-login_footer_font_size').css( 'display', 'list-item' );
      $('#customize-control-gardenlogin_customization-login_footer_bg_color').css( 'display', 'list-item' );
    } else {

      $('#customize-control-gardenlogin_customization-login_footer_text').css( 'display', 'none' );
      $('#customize-control-gardenlogin_customization-login_footer_text_decoration').css( 'display', 'none' );
      $('#customize-control-gardenlogin_customization-login_footer_color').css( 'display', 'none' );
      $('#customize-control-gardenlogin_customization-login_footer_color_hover').css( 'display', 'none' );
      $('#customize-control-gardenlogin_customization-login_footer_font_size').css( 'display', 'none' );
      $('#customize-control-gardenlogin_customization-login_footer_bg_color').css( 'display', 'none' );
    }

    if ( $('#customize-control-gardenlogin_customization-back_display_text input[type="checkbox"]').is(":checked") ) {

      $('#customize-control-gardenlogin_customization-login_back_text_decoration').css( 'display', 'list-item' );
      $('#customize-control-gardenlogin_customization-login_back_color').css( 'display', 'list-item' );
      $('#customize-control-gardenlogin_customization-login_back_color_hover').css( 'display', 'list-item' );
      $('#customize-control-gardenlogin_customization-login_back_font_size').css( 'display', 'list-item' );
      $('#customize-control-gardenlogin_customization-login_back_bg_color').css( 'display', 'list-item' );
    } else {

      $('#customize-control-gardenlogin_customization-login_back_text_decoration').css( 'display', 'none' );
      $('#customize-control-gardenlogin_customization-login_back_color').css( 'display', 'none' );
      $('#customize-control-gardenlogin_customization-login_back_color_hover').css( 'display', 'none' );
      $('#customize-control-gardenlogin_customization-login_back_font_size').css( 'display', 'none' );
      $('#customize-control-gardenlogin_customization-login_back_bg_color').css( 'display', 'none' );
    }

    if ( $('#customize-control-gardenlogin_customization-login_copy_right_display input[type="checkbox"]').is(":checked") ) {
      $('#customize-control-gardenlogin_customization-login_footer_copy_right').css( 'display', 'list-item' );
    } else {
      $('#customize-control-gardenlogin_customization-login_footer_copy_right').css( 'display', 'none' );
    }

    $("<style type='text/css' id='gardenlogin-customize'></style>").appendTo(gardenlogin_find('head'));
    $("<style type='text/css' id='gardenlogin-iframe-bgColor'></style>").appendTo(gardenlogin_find('head'));
    $("<style type='text/css' id='gardenlogin-scbg-position'></style>").appendTo(gardenlogin_find('head'));
    $("<style type='text/css' id='gardenlogin-scbg-size'></style>").appendTo(gardenlogin_find('head'));
    $("<style type='text/css' id='gardenlogin-scbg-repeat'></style>").appendTo(gardenlogin_find('head'));

  });

})(jQuery);
