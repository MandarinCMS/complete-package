<?php
if( !defined( 'BASED_TREE_URI') ) exit();

?>
<!-- THE TEMPLATE AREA -->
<div id="template_area">
	<div id="template_header_part">
		<h2><span class="revlogo-mini" style="margin-right:15px;"></span><?php _e('Slider Template Library', 'thunderslider'); ?></h2>
		
		<div id="close-template"></div>
		
		<div class="revolution-template-switcher">	
			<span style="display:table-cell;vertical-align:top">			
				<span class="template_filter_button selected" data-type="temp_all"><?php _e('All Slides', 'thunderslider'); ?></span>
				<span class="template_filter_button" data-type="temp_slider"><?php _e('Slider', 'thunderslider'); ?></span>
				<span class="template_filter_button" data-type="temp_carousel"><?php _e('Carousel', 'thunderslider'); ?></span>			
				<span class="template_filter_button" data-type="temp_hero"><?php _e('Hero', 'thunderslider'); ?></span>
				<span class="template_filter_button" data-type="template_free"><?php _e('Revolution Defaults', 'thunderslider'); ?></span>
				<span class="template_filter_button" data-type="template_premium"><?php _e('Premium Slider', 'thunderslider'); ?></span>	
				<span class="template_filter_button template_local_filter" data-type="temp_existing"><?php _e('Local Slides', 'thunderslider'); ?></span>			
				<span class="template_filter_button template_local_filter" data-type="temp_custom"><?php _e('User Templates', 'thunderslider'); ?></span>			
			</span>
			<span style="display:table-cell;vertical-align:top;text-align:right">
				<span class="rs-reload-shop"><i class="eg-icon-arrows-ccw"></i><?php _e('Update Library', 'thunderslider'); ?></span>
			</span>
			
		</div>
		<div class="revolution-template-subtitle"><?php _e('Add Single Slide', 'thunderslider'); ?></div>
	</div>
	
	<!-- THE REVOLUTION BASE TEMPLATES -->
	<div class="revolution-basic-templates revolution-template-groups" style="padding-top:20px">
		<!-- Template Slides will be added here -->
	</div>
	
	
	<!-- THE REVOLUTION CUSTOMER TEMPLATES -->
	<div class="revolution-customer-templates revolution-template-groups">
		
	</div>


	<!-- THE ALL SLIDES GROUP -->
	<div class="revolution-all-slides-templates revolution-template-groups">
		
	</div>
</div>
<?php
if(!isset($rs_disable_template_script)){
?>
<script>
	/*jQuery("document").ready(function() {		
		templateSelectorHandling();
	});*/

	function isElementInViemcmsort(element,sctop,wh,rtgt) {		
		var etp = parseInt(element.offset().top,0)-rtgt,
			etpp = parseInt(element.position().top,0),
			inviewport = false;						
		if ((etp>-50) && (etp<wh+50))
			inviewport =  true;						
		return inviewport;
	}
	
	function scrollTA() {
		var ta = jQuery('.revolution-template-groups'),
			st = ta.scrollTop(),			
			wh = jQuery(window).height();

		ta.find('.template_item:visible, .template_slide_item_img:visible').each(function() {

			var el = jQuery(this),
				rtgt = parseInt(el.closest('.revolution-template-groups').offset().top,0);
			if (el.data('src')!=undefined && el.data('bgadded')!=1) {
				if (jQuery('#template_area').hasClass("show"))
					if (isElementInViemcmsort(el,st,wh,rtgt)){																			
						el.css({backgroundImage:'url("'+el.data('src')+'")'});														
						el.data('bgadded',1);					
					}
			}
		});
	}


	function templateSelectorHandling() {
		// TEMPLATE ELEMENTS
		

		// TEMPLATE ELEMENTS
		jQuery('.template_filter_button').on("click",function() {
			jQuery('#template_bigoverlay').fadeOut(100);
			jQuery('.template_thumb_more:visible').fadeOut(100);
			var btn = jQuery(this),
				sch = btn.data('type');
			jQuery('.template_filter_button').removeClass("selected");
			btn.addClass("selected");
			jQuery('.template_group_wrappers').hide();
			if (sch=="temp_all") 
				jQuery('.template_group_wrappers').show();
			else
				jQuery('.'+sch).show();
			jQuery('.revolution-template-groups').scrollTop(0);
			scrollTA();
		});

		
		jQuery('.template_item, .template_slide_item_img').each(function() {
			var item = jQuery(this),
				gw = item.data('gridwidth'),
				gh = item.data('gridheight'),
				id = item.data('slideid'),
				w = 180;
				
			if (gw==undefined || gw<=0) gw = w;
			if (gh==undefined || gh<=0) gh = w;
			
			var	h = Math.round((w/gw)*gh);
			//item.css({height:h+"px"});
			
			var factor = w/gw;
			
			var htitle = item.closest('.template_group_wrappers').find('h3');
			if (!htitle.hasClass("modificated")) {
				htitle.html(htitle.html()+" ("+gw+"x"+gh+")").addClass("modificated");
			}			
		});
		
		// CLOSE SLIDE TEMPLATE
		jQuery('#close-template').click(function() {
			jQuery('#template_area').removeClass("show");
		});		

		function templateButtonClicked(btn) {			
			jQuery('.revolution-template-groups').each(function() { jQuery(this).hide();});			
			jQuery("."+btn.data("showgroup")).show();
			jQuery('.revolution-templatebutton').removeClass("selected");
			btn.addClass("selected");
			scrollTA();			
			jQuery('#template_area').perfectScrollbar();
			
			if(btn.data("showgroup") == 'revolution-basic-templates' || btn.data("showgroup") == 'revolution-premium-templates'){
				jQuery('.revolution-filters').show();
			}else{
				jQuery('.revolution-filters').hide();
			}
		};

		jQuery('body').on('click','.show_more_template_slider',function() {
			if (jQuery(this).hasClass("add_user_template_slide_item")) return true;
			jQuery('.temp_slide_single_wrapper').css({zIndex:2});
			var item = jQuery(this).closest('.temp_slide_single_wrapper');
			if (item.length>0) {	

				if (jQuery(window).width() - item.offset().left < item.width()*2.1)
					item.addClass("show_more_to_left")
				else 
					item.removeClass("show_more_to_left");

				item.find('.template_thumb_more').fadeIn(100);
				jQuery('#template_bigoverlay').fadeIn(100);
				item.css({zIndex:15});
			}
		});

		jQuery('#template_bigoverlay').on('click',function() {
			jQuery('#template_bigoverlay').fadeOut(100);
			jQuery('.template_thumb_more:visible').fadeOut(100);
		});


		// TEMPLATE TAB CHANGE 
		jQuery('body').on("click",'.revolution-templatebutton',function() {			
			templateButtonClicked(jQuery(this));
		});

		scrollTA();		

		function setTWHeight() {
			var w = jQuery(window).height(),
				wh = jQuery('#template_header_part').height();
			jQuery('.revolution-template-groups').css({height:(w-wh)+"px"});
			jQuery('.revolution-template-groups').perfectScrollbar("update");
		};

		setTWHeight();
		jQuery(window).on("resize",setTWHeight);
		jQuery('.revolution-template-groups').perfectScrollbar();

		document.addEventListener('ps-scroll-y', function (e) {
			if (jQuery(e.target).closest('.revolution-template-groups').length>0) {
				scrollTA();
				jQuery('#template_bigoverlay').css({top:jQuery('.revolution-template-groups').scrollTop()});												
			}
	    });

		
		

		
		
		jQuery(".input_import_slider").change(function(){
			if(jQuery(this).val() !== ''){
				jQuery('.rs-import-slider-button').show();
			}else{
				jQuery('.rs-import-slider-button').hide();
			}
		});
		
	};

	<?php
	if(isset($_REQUEST['update_shop'])){
		?>
		jQuery(document).ready(function(){
			var recalls_amount = 0;
			function callTemplateSlider() {   
				recalls_amount++;
				if (recalls_amount>5000) {
					jQuery('#waitaminute').hide();
				} else {
					if (jQuery('#template_area').length>0) {    
						scrollTA();
						jQuery('#template_area').addClass("show");
						jQuery('#waitaminute').hide();      
						ThunderSliderAdmin.load_slide_template_html();
					} else {
						callTemplateSlider();
					}
				}   
			}
			callTemplateSlider();  
		});
		<?php
	}
	?>
</script>

<!-- Import template slider dialog -->
<div id="dialog_import_template_slide" title="<?php _e("Import Template Slide",'thunderslider'); ?>" class="dialog_import_template_slide" style="display:none">
	<form action="<?php echo ThunderSliderBase::$url_ajax; ?>" enctype="multipart/form-data" method="post">
		<input type="hidden" name="action" value="thunderslider_ajax_action">
		<input type="hidden" name="client_action" value="import_slide_template_slidersview">
		<input type="hidden" name="nonce" value="<?php echo mcms_create_nonce("thunderslider_actions"); ?>">
		<input type="hidden" name="uid" class="rs-uid" value="">
		<input type="hidden" name="slidenum" class="rs-slide-number" value="">
		<input type="hidden" name="slider_id" class="rs-slider-id" value="">
		<input type="hidden" name="redirect_id" class="rs-slide-id" value="">
		
		<p><?php _e('Please select the corresponding zip file from the download packages import folder called', 'thunderslider'); ?>:</p>
		<p class="filetoimport"><b><span class="rs-zip-name"></span></b></p>
		<p class="import-file-wrapper"><input type="file" size="60" name="import_file" class="input_import_slider"></p>
		<span style="margin-top:45px;display:block"><input type="submit" class="rs-import-slider-button button-primary revblue tp-be-button" value="<?php _e("Import Template Slide",'thunderslider'); ?>"></span>
		<span class="tp-clearfix"></span>
		<span style="font-weight: 700;"><?php _e("Note: style templates will be updated if they exist!",'thunderslider'); ?></span><br><br>
		<table style="display: none;">
			<tr>
				<td><?php _e("Custom Animations:",'thunderslider'); ?></td>
				<td><input type="radio" name="update_animations" value="true" checked="checked"> <?php _e("overwrite",'thunderslider'); ?></td>
				<td><input type="radio" name="update_animations" value="false"> <?php _e("append",'thunderslider'); ?></td>
			</tr>
			<!--tr>
				<td><?php _e("Static Styles:",'thunderslider'); ?></td>
				<td><input type="radio" name="update_static_captions" value="true"> <?php _e("overwrite",'thunderslider'); ?></td>
				<td><input type="radio" name="update_static_captions" value="false"> <?php _e("append",'thunderslider'); ?></td>
				<td><input type="radio" name="update_static_captions" value="none" checked="checked"> <?php _e("ignore",'thunderslider'); ?></td>
			</tr-->
		</table>		
		
	</form>
</div>


<div id="dialog_import_template_slide_from" title="<?php _e("Import Template Slide",'thunderslider'); ?>" class="dialog_import_template_slide_from" style="display:none">
	<?php _e('Import Slide from local or from MandarinCMS online server?', 'thunderslider'); ?>
	<form action="<?php echo ThunderSliderBase::$url_ajax; ?>" enctype="multipart/form-data" method="post" name="rs-import-slide-template-from-server" id="rs-import-slide-template-from-server">
		<input type="hidden" name="action" value="thunderslider_ajax_action">
		<input type="hidden" name="client_action" value="import_slide_online_template_slidersview">
		<input type="hidden" name="nonce" value="<?php echo mcms_create_nonce("thunderslider_actions"); ?>">
		<input type="hidden" name="uid" class="rs-uid" value="">
		<input type="hidden" name="slidenum" class="rs-slide-number" value="">
		<input type="hidden" name="slider_id" class="rs-slider-id" value="">
		<input type="hidden" name="redirect_id" class="rs-slide-id" value="">
	</form>
</div>

<div id="dialog_import_template_slide_info" title="<?php _e('Importing Status','thunderslider'); ?>" class="dialog_import_template_slider_info" style="display:none;">
	<!-- ADD INFOS HERE ON DEMAND -->
	<div class="thunderslider_logo_rotating"><div class="thunderslidercycle"></div></div>
	<div id="install-slider-counter-wrapper"><span id="install-slider-counter"></span></div>
	<div id="nowinstalling_label"><?php _e('Now Installing','thunderslider'); ?></div>
	<div id="import_dialog_box_action"></div>
	<div id="import_dialog_box"></div>
	
</div>
<?php
}
?>