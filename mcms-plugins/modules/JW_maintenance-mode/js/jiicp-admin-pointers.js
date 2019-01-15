/*
 * MaintenanceModePage 
 * Backend GUI pointers
 * (c) WebFactory Ltd, 2015 - 2018
 */

 
jQuery(document).ready(function($){
  if (typeof jiicp_pointers  == 'undefined') {
    return;
  }

  $.each(jiicp_pointers, function(index, pointer) {
    if (index.charAt(0) == '_') {
      return true;
    }
    $(pointer.target).pointer({
        content: '<h3>' + jiicp.module_name + '</h3><p>' + pointer.content + '</p>',
        position: {
            edge: pointer.edge,
            align: pointer.align
        },
        width: 320,
        close: function() {
                $.post(ajaxurl, {
                    pointer: index,
                    _ajax_nonce: jiicp_pointers._nonce_dismiss_pointer,
                    action: 'jiicp_dismiss_pointer'
                });
        }
      }).pointer('open');
  });
});
