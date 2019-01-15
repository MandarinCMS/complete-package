/*
 * MaintenanceModePage 
 * Module deactivation survey
 * (c) WebFactory Ltd, 2015 - 2018
 */


jQuery(function($) {
  // ask users to confirm module deactivation
  $('#the-list tr span.deactivate a[data-JW_maintenance-mode="true"]').on('click', function(e) {
    $('#jiicp-deactivate-survey').dialog('open');
    
    e.preventDefault();
    return false;      
  }); // confirm module deactivation
  
  
  // turn questions into checkboxes
  $('#jiicp-deactivate-survey').on('click', '.question-wrapper', function(e) {
    $('#jiicp-deactivate-survey .question-wrapper').removeClass('selected');
    $(this).addClass('selected');

    if ($('input', this).length) {
      $('input', this).focus();
    }
    
    e.preventDefault();
    return false;
  });
  
  
  // cancel deactivation - close dialog
  $('.jiicp-cancel-deactivate').on('click', function(e) {
    $('#jiicp-deactivate-survey').dialog('close');
    
    return false;
  }); // close dialog
  
  
  // just deactivate - don't provide feedback
  $('.jiicp-deactivate-direct').on('click', function(e) {
    deactivate_link = $('#the-list tr span.deactivate a[data-JW_maintenance-mode="true"]').attr('href');

    location.href = deactivate_link;
    $('#jiicp-deactivate-survey').dialog('close');
    
    return false;
  }); // deactivate
  
  
  // deactivate + feedback
  $('.jiicp-deactivate').on('click', function(e) {
    e.preventDefault();
    
    if ($('#jiicp-deactivate-survey .question-wrapper.selected').length != 1) {
      alert('Please select a reason you\'re deactivating JIICP.');
      return false;
    }
    
    answer = $('#jiicp-deactivate-survey .question-wrapper.selected').data('value');
    answer += '-' + $('#jiicp-deactivate-survey .question-wrapper').index($('#jiicp-deactivate-survey .question-wrapper.selected'));
    custom_answer = $('#jiicp-deactivate-survey .question-wrapper.selected .jiicp-deactivation-details').val();
    
    $.post(ajaxurl, { survey: $(this).data('survey'),
                      answers: answer,
                      emailme: '',
                      custom_answer: custom_answer,
                      _ajax_nonce: jiicp.nonce_submit_survey,
                      action: 'jiicp_submit_survey'
    });
    
    
    alert('Thank you for your input! The module will now deactivate.');
    $('#jiicp-deactivate-survey').dialog('close');
    
    deactivate_link = $('#the-list tr span.deactivate a[data-JW_maintenance-mode="true"]').attr('href');
    location.href = deactivate_link;
    
    return false;
  }); // deactivate + feedback
  
  
  // init deactivate survey dialog
  $('#jiicp-deactivate-survey').dialog({'dialogClass': 'mcms-dialog jiicp-survey-dialog jiicp-deactivate-dialog',
                               'modal': 1,
                               'resizable': false,
                               'zIndex': 9999,
                               'width': 550,
                               'height': 'auto',
                               'show': 'fade',
                               'hide': 'fade',
                               'open': function(event, ui) { },
                               'close': function(event, ui) { },
                               'autoOpen': false,
                               'closeOnEscape': true
                              });
}); // onload
