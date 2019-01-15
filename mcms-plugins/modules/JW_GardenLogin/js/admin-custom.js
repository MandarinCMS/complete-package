(function($) {
  'use strict';

  $(function() {
    // Code to fire when the DOM is ready.
    $('.mcmsbrigade-video-link').on('click', function(e) {
      e.preventDefault();
      var target = $(this).data('video-id');
      $('#' + target).fadeIn();
    });
    $('.mcmsbrigade-close-popup').on('click', function(e) {
      $(this).parent().parent().fadeOut();
      $('.mcmsbrigade-video-wrapper iframe').attr('src',
        'https://www.youtube.com/embed/GMAwsHomJlE');
    });

    // $("#mcmsb-gardenlogin_setting\\[enable_repatcha_promo\\]").on('click', function() {
    //
    //   var promotion = $('#mcmsb-gardenlogin_setting\\[enable_repatcha_promo\\]');
    //   if ( promotion.is(":checked") ) {
    //     $('tr.recapthca-promo-img').show();
    //   } else {
    //     $('tr.recapthca-promo-img').hide();
    //   }
    // }); // on click promo checkbox.

    // Remove Disabled attribute from Import Button.
    $( '#loginPressImport' ).on( 'change', function( event ) {

      event.preventDefault();
      var gardenloginFileImp = $( '#loginPressImport' ).val();
      var gardenloginFileExt = gardenloginFileImp.substr( gardenloginFileImp.lastIndexOf('.') + 1 );

      $( '.gardenlogin-import' ).attr( "disabled", "disabled" );

      if ( 'json' == gardenloginFileExt ) {
        $(".import_setting .wrong-import").html("");
        $( '.gardenlogin-import' ).removeAttr( "disabled" );
      } else {
        $(".import_setting .wrong-import").html("Choose GardenLogin settings file only.");
      }
    });

    $("#mcmsb-gardenlogin_setting\\[enable_privacy_policy\\]").on('click', function() {

      var privacy_editor = $('#mcmsb-gardenlogin_setting\\[enable_privacy_policy\\]');
      if ( privacy_editor.is(":checked") ) {
        $('tr.privacy_policy').show();
      } else {
        $('tr.privacy_policy').hide();
      }
    }); // on click promo checkbox.

    $(window).on('load', function() {

      $('<tr class="recapthca-promo-img"><th class="recapthca-promo" colspan="2"><img src="' + gardenlogin_script.module_url + '/gardenlogin/img/promo/recaptcha_promo.png"><a class="recapthca-promo-link" href="https://jiiworks.net/mandarincms/modules/gardenlogin-pro/?utm_source=gardenlogin-lite&amp;utm_medium=recaptcha-settings&amp;utm_campaign=pro-upgrade" target="_blank"><span>Unlock Premium Feature</span></a></th></tr>').insertAfter( $(".enable_repatcha_promo").closest('tr') );

      var promotion = $('#mcmsb-gardenlogin_setting\\[enable_repatcha_promo\\]');
      if ( promotion.is(":checked") ) {
        $('tr.recapthca-promo-img').show();
      }

      var privacy_editor = $('#mcmsb-gardenlogin_setting\\[enable_privacy_policy\\]');
      if ( privacy_editor.is(":checked") ) {
        $('tr.privacy_policy').show();
      }

    }); // Window on load.

    $( '.gardenlogin-log-file' ).on( 'click', function( event ) {

      event.preventDefault();

      $.ajax({

        url: ajaxurl,
        type: 'POST',
        data: {
          action : 'gardenlogin_help',
        },
        beforeSend: function() {
          $(".log-file-sniper").show();
        },
        success: function( response ) {

          $(".log-file-sniper").hide();
          $(".log-file-text").show();

          if ( ! window.navigator.msSaveOrOpenBlob ) { // If msSaveOrOpenBlob() is supported, then so is msSaveBlob().
            $("<a />", {
              "download" : "gardenlogin-log.txt",
              "href" : "data:text/plain;charset=utf-8," + encodeURIComponent( response ),
            }).appendTo( "body" )
            .click(function() {
               $(this).remove()
            })[0].click()
          } else {
            var blobObject = new Blob( [response] );
            window.navigator.msSaveBlob( blobObject, 'gardenlogin-log.txt' );
          }

          setTimeout(function() {
            $(".log-file-text").fadeOut()
          }, 3000 );
        }
      });

    });

    $('.gardenlogin-export').on('click',  function(event) {

      event.preventDefault();

      var dateObj = new Date();
      var month   = dateObj.getUTCMonth() + 1; //months from 1-12
      var day     = dateObj.getUTCDate();
      var year    = dateObj.getUTCFullYear();
      var newdate = year + "-" + month + "-" + day;

      $.ajax({

        url: ajaxurl,
        type: 'POST',
        data: {
          action : 'gardenlogin_export',
        },
        beforeSend: function() {
          $(".export_setting .export-sniper").show();
        },
        success: function( response ) {

          $(".export_setting .export-sniper").hide();
          $(".export_setting .export-text").show();

          if ( ! window.navigator.msSaveOrOpenBlob ) { // If msSaveOrOpenBlob() is supported, then so is msSaveBlob().
            $("<a />", {
              "download" : "gardenlogin-export-"+newdate+".json",
              "href" : "data:application/json;charset=utf-8," + encodeURIComponent( response ),
            }).appendTo( "body" )
            .click(function() {
               $(this).remove()
            })[0].click()
          } else {
            var blobObject = new Blob( [response] );
            window.navigator.msSaveBlob( blobObject, "gardenlogin-export-"+newdate+".json" );
          }

          setTimeout(function() {
            $(".export_setting .export-text").fadeOut()
          }, 3000 );
        }
      });
    });

    $('.gardenlogin-import').on( 'click', function(event) {
      event.preventDefault();

      var file    = $('#loginPressImport');
      var fileObj = new FormData();
      var content = file[0].files[0];

      fileObj.append( 'file', content );
      fileObj.append( 'action', 'gardenlogin_import' );

      $.ajax({

        processData: false,
        contentType: false,
        url: ajaxurl,
        type: 'POST',
        data: fileObj, // file and action append into variable fileObj.
        beforeSend: function() {
          $(".import_setting .import-sniper").show();
          $(".import_setting .wrong-import").html("");
          $( '.gardenlogin-import' ).attr( "disabled", "disabled" );
        },
        success: function(response) {

          $(".import_setting .import-sniper").hide();
          // $(".import_setting .import-text").fadeIn();
          if ( 'error' == response ) {
            $(".import_setting .wrong-import").html("JSON File is not Valid.");
          } else {
            $(".import_setting .import-text").show();
            setTimeout(function() {
              $(".import_setting .import-text").fadeOut();
              // $(".import_setting .wrong-import").html("");
              file.val('');
            }, 3000 );
          }

        }
      }); //!ajax.
    });

  });
})(jQuery); // This invokes the function above and allows us to use '$' in place of 'jQuery' in our code.
