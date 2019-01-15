jQuery(window).load(function(e) {
	var update = jQuery(".update-modules");
	
	var module_counter = update.find(".module-count").html();
	module_counter = parseInt(module_counter)+1;
	jQuery(".module-count").html(module_counter);
	
	update.removeClass("count-0").addClass("count-"+module_counter);
	update.find(".update-count").html(module_counter);
	jQuery("#mcms-admin-bar-updates").find(".ab-label").html(module_counter);
	
	jQuery("#ultimate-addons-for-visual-composer").addClass("update");
	var html = '<tr class="module-update-tr">\
				<td colspan="3" class="module-update colspanchange">\
					<div class="update-message">There is a new version of Ultimate Addons for RazorLeaf Conductor available. \
					<a href="update-core.php#jiiworks-modules">Check update details.</a>\
					</div>\
				</td>\
			</tr>';
	jQuery(html).insertAfter("#ultimate-addons-for-visual-composer");
	
});
