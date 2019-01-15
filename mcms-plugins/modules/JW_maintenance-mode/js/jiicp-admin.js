/*
 * MaintenanceModePage
 * Main backend JS
 * (c) Web factory Ltd, 2015 - 2018
 */


jQuery(document).ready(function($) {
  var old_settings = $('#jiicp_form *').not('.skip-save').serialize();
  var ad_name = '';

  // init tabs
  $('#jiicp_tabs').tabs({
    activate: function(event, ui) {
        Cookies.set('jiicp_tabs', $('#jiicp_tabs').tabs('option', 'active'), { expires: 365 });
    },
    active: Cookies.get('jiicp_tabs')
  }).show();

  // init 2nd level of tabs
  $('.jiicp-tabs-2nd-level').each(function() {
    $(this).tabs({
      activate: function(event, ui) {
        Cookies.set($(this).attr('id'), $(this).tabs('option', 'active'), { expires: 365 });
      },
      active: Cookies.get($(this).attr('id'))
    });
  });

  // init select2
  $('#whitelisted_users').select2({ 'placeholder': jiicp.whitelisted_users_placeholder});

  // autosize textareas
  $.each($('textarea[data-autoresize]'), function() {
    var offset = this.offsetHeight - this.clientHeight;

    var resizeTextarea = function(el) {
        $(el).css('height', 'auto').css('height', el.scrollHeight + offset + 2);
    };
    $(this).on('keyup input click', function() { resizeTextarea(this); }).removeAttr('data-autoresize');
  });

  // maybe init survey dialog
  $('#features-survey-dialog').dialog({'dialogClass': 'mcms-dialog jiicp-dialog jiicp-survey-dialog',
                               'modal': 1,
                               'resizable': false,
                               'zIndex': 9999,
                               'width': 550,
                               'height': 'auto',
                               'show': 'fade',
                               'hide': 'fade',
                               'open': function(event, ui) { jiicp_fix_dialog_close(event, ui); },
                               'close': function(event, ui) { },
                               'autoOpen': jiicp.open_survey,
                               'closeOnEscape': true
                              });


  // turn questions into checkboxes
  $('.question-wrapper').on('click', function(e) {
    $('.question-wrapper').removeClass('selected');
    $(this).addClass('selected');

    e.preventDefault();
    return false;
  });


  // dismiss survey forever
  $('.dismiss-survey').on('click', function(e) {
    $('#features-survey-dialog').dialog('close');

    $.post(ajaxurl, { survey: $(this).data('survey'),
                      _ajax_nonce: jiicp.nonce_dismiss_survey,
                      action: 'jiicp_dismiss_survey'
    });

    e.preventDefault();
    return false;
  });


  // submit and hide survey
  $('.submit-survey').on('click', function(e) {
    if ($('.question-wrapper.selected').length != 1) {
      alert('Please choose the way you use JIICP.');
      return false;
    }

    answers = $('.question-wrapper.selected').data('value');
    answers += '-' + $('.question-wrapper').index($('.question-wrapper.selected'));

    $.post(ajaxurl, { survey: $(this).data('survey'),
                      answers: answers,
                      emailme: $('#features-survey-dialog #emailme:checked').val(),
                      _ajax_nonce: jiicp.nonce_submit_survey,
                      action: 'jiicp_submit_survey'
    });

    alert('Thank you for your time! We appriciate your input!');

    $('#features-survey-dialog').dialog('close');
    e.preventDefault();
    return false;
  });


  // send support message
  $('#jiicp-send-support-message').on('click', function(e) {
    e.preventDefault();
    button = $(this);

    if ($('#support_email').val().length < 5 || /\S+@\S+\.\S+/.test($('#support_email').val()) == false) {
      alert('We need your email address, don\'t you think?');
      $('#support_email').select().focus();
      return false;
    }

    if ($('#support_message').val().length < 15) {
      alert('A message that short won\'t do anybody any good.');
      $('#support_message').select().focus();
      return false;
    }

    button.addClass('loading');
    $.post(ajaxurl, { support_email: $('#support_email').val(),
                      support_message: $('#support_message').val(),
                      support_info: $('#support_info:checked').val(),
                      _ajax_nonce: jiicp.nonce_submit_support_message,
                      action: 'jiicp_submit_support_message'},
    function(data) {
      if (data.success) {
        alert('Message sent! Our agents will get back to you ASAP.');
      } else {
        alert(data.data);
      }
    }).fail(function() {
      alert('Something is not right. Please reload the page and try again');
    }).always(function() {
      button.removeClass('loading');
    });

    return false;
  });


  // fix for enter press in support email
  $('#support_email').on('keypress', function(e) {
    if (e.which == 13) {
      e.preventDefault();
      $('#jiicp-send-support-message').trigger('click');
      return false;
    }
  }); // if enter on support email


  // activate myskin via thumb and save
  $('.jiicp-thumb .activate-myskin').on('click', function(e) {
    e.preventDefault();

    myskin_id = $(this).parents('.jiicp-thumb').data('myskin-id');
    $('#myskin_id').val(myskin_id);
    $('#jiicp_tabs #submit').trigger('click');

    return false;
  });


  // init datepicker
  $('.datepicker').AnyTime_picker({ format: "%Y-%m-%d %H:%i", firstDOW: 1, earliest: new Date(), labelTitle: "Select the date &amp; time when construction mode will be disabled" } );


  // fix when opening datepicker
  $('.show-datepicker').on('click', function(e) {
    e.preventDefault();

    $(this).prevAll('input.datepicker').focus();

    return false;
  });


  $('#ga_tracking_id_toggle').on('change', function(e, is_triggered) {
    if ($(this).is(':checked')) {
      if (is_triggered) {
        $('#ga_tracking_id_wrapper').show();
      } else {
        $('#ga_tracking_id_wrapper').slideDown();
      }
    } else {
      if (is_triggered) {
        $('#ga_tracking_id_wrapper').hide();
      } else {
        $('#ga_tracking_id_wrapper').slideUp();
      }
    }
  }).triggerHandler('change', true);

  $('#end_date_toggle').on('change', function(e, is_triggered) {
    if ($(this).is(':checked')) {
      if (is_triggered) {
        $('#end_date_wrapper').show();
      } else {
        $('#end_date_wrapper').slideDown();
      }
    } else {
      if (is_triggered) {
        $('#end_date_wrapper').hide();
      } else {
        $('#end_date_wrapper').slideUp();
      }
    }
  }).triggerHandler('change', true);


  $('.settings_page_jiicp .wrap').on('click', '.reset-settings', function(e) {
    if (!confirm('Are you sure you want to reset all JIICP settings to their default values? There is NO undo.')) {
      e.preventDefault();
      return false;
    }

    return true;
  }); // reset-settings


  // warning if there are unsaved changes when previewing
  $('.settings_page_jiicp .wrap').on('click', '#jiicp_preview', function(e) {
    if ($('#jiicp_form *').not('.skip-save').serialize() != old_settings) {
      if (!confirm('There are unsaved changes that will not be visible in the preview. Please save changes first.\nContinue?')) {
        e.preventDefault();
        return false;
      }
    }

    return true;
  });


  // check if there are invalid fields
  // assume they are social icons
  $('.settings_page_jiicp .wrap').on('click', '#submit', function(e) {
    if ($('#jiicp_form input:invalid').not('.skip-save').length) {
      $('#jiicp_tabs').tabs('option', 'active', 2);
      $('#jiicp_form input:invalid').first().focus();
      alert('Please correct the errors before saving.');

      return false;
    }

    return true;
  }); // form submit


  // show all social icons
  $('.settings_page_jiicp .wrap').on('click', '#show-social-icons', function(e) {
    $(this).hide();
    $('#jiicp-social-icons tr').removeClass('hidden');

    return false;
  });


  // helper for linking anchors in different tabs
  $('.settings_page_jiicp').on('click', '.change_tab', function(e) {
    $('#jiicp_tabs').tabs('option', 'active', $(this).data('tab'));

    // get the link anchor and scroll to it
    target = this.href.split('#')[1];
    if (target) {
      $.scrollTo('#' + target, 500, {offset: {top:-50, left:0}});
    }
  });


  // upsell dialog init
  $('#upsell-dialog').dialog({'dialogClass': 'mcms-dialog jiicp-dialog jiicp-upsell-dialog',
                              'modal': 1,
                              'resizable': false,
                              'title': 'asdasd <b>asdasd</b>',
                              'zIndex': 9999,
                              'width': 900,
                              'height': 'auto',
                              'show': 'fade',
                              'hide': 'fade',
                              'open': function(event, ui) {
                                jiicp_fix_dialog_close(event, ui);
                                $(this).siblings().find('span.ui-dialog-title').html(jiicp.dialog_upsell_title);
                              },
                              'close': function(event, ui) { },
                              'autoOpen': false,
                              'closeOnEscape': true
  });
  $(window).resize(function(e){
    $('#upsell-dialog').dialog("option", "position", {my: "center", at: "center", of: window});
  });

  // zebra on pricing table, per column
  $('#jiicp-pricing-table').find('tr').each(function() {
    $(this).find('td').eq(1).addClass('hover');
  });

  $('.settings_page_jiicp').on('click change', '.open-jiicp-upsell', function(e) {
    if ($(this).is('select') && $(this).val() != '-1') {
      return true;
    }

    e.preventDefault();

    if (jiicp.is_activated) {
      $('#jiicp_tabs').tabs('option', 'active', 5);
      $.scrollTo('#license_key');
      $('#license_key').focus();

      return;
    }

    ad_name = $(this).attr('id');
    if (!ad_name) {
      ad_name = $(this).data('pro-ad');
    }
    if (!ad_name) {
      ad_name = '';
    }

    $('.promo-button').each(function(ind, el) {
      tmp = $(el).data('href-org');
      tmp = tmp.replace('pricing-table', ad_name);
      $(el).attr('href', tmp);
    });

    if ($(this).is('select')) {
      $(this).find('option').attr('selected', '');
      $(this).find('option:first').attr('selected', 'selected');
    }
    $(this).blur();

    $('#upsell-dialog').dialog('open');

    if ($(this).data('tab') == 'buy') {
      $('#tabs_upsell').tabs('option', 'active', 0);
    }
    if ($(this).data('tab') == 'features') {
      $('#tabs_upsell').tabs('option', 'active', 1);
    }

    return false;
  });

  $('#tabs_upsell').on('tabsactivate', function(event, ui) {
    $('#upsell-dialog').dialog("option", "position", {my: "center", at: "center", of: window});
  });

  $('.settings_page_jiicp').on('click', '.go-to-license-key', function(e) {
    $('#upsell-dialog').dialog('close');
    $('#jiicp_tabs').tabs('option', 'active', 5);
    $.scrollTo('#license_key');
    $('#license_key').focus();
  });

  $('#license_key').on('keypress', function(e) {
    if (e.which == 13) {
      e.preventDefault();
      $('#license-submit').trigger('click');
      return false;
    }
  });

  if (!Date.now) {
    Date.now = function() { return new Date().getTime(); }
  }

  function jiicp_update_timer() {
    out = '';
    timer = $('.jiicp-countdown');

    if (timer.length == 0) {
      clearInterval(jiicp_countdown_interval);
    }

    now = Math.round(new Date().getTime()/1000);
    timer_end = jiicp.promo_countdown;
    delta = timer_end - now;
    seconds = Math.floor( (delta) % 60 );
    minutes = Math.floor( (delta/60) % 60 );
    hours = Math.floor( (delta/(60*60)) % 24 );

    if (delta <= 0) {
      clearInterval(jiicp_countdown_interval);
    }

    if (hours) {
      out += hours + 'h ';
    }
    if (minutes || out) {
      out += minutes + 'min ';
    }
    if (seconds || out) {
      out += seconds + 'sec';
    }
    if (delta <= 0 || !out) {
      out = 'discount is no longer available';
    }

    $(timer).html(out);

    return true;
  } // jiicp_update_timer

  if (jiicp.promo_countdown) {
    jiicp_countdown_interval = setInterval(jiicp_update_timer, 1000);
  }
}); // on ready


function jiicp_fix_dialog_close(event, ui) {
  jQuery('.ui-widget-overlay').bind('click', function(){
    jQuery('#' + event.target.id).dialog('close');
  });
} // jiicp_fix_dialog_close
