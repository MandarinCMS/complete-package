<?php
if( !defined( 'BASED_TREE_URI') ) exit();

global $thunderSliderVersion;

$wrapperClass = "";
if(ThunderSliderGlobals::$isNewVersion == false)
	$wrapperClass = " oldmcms";

$wrapperClass = apply_filters( 'rev_overview_wrapper_class_filter', $wrapperClass );

$nonce = mcms_create_nonce("thunderslider_actions");

$rsop = new ThunderSliderOperations();
$glval = $rsop->getGeneralSettingsValues();
?>

<?php
$waitstyle = '';
if(isset($_REQUEST['update_shop'])){
	$waitstyle = 'display:block';
}


$operations = new ThunderSliderOperations();
$glob_vals = $operations->getGeneralSettingsValues();
$pack_page_creation = ThunderSliderFunctions::getVal($glob_vals, "pack_page_creation", "on");
$single_page_creation = ThunderSliderFunctions::getVal($glob_vals, "single_page_creation", "off");
$tp_color_picker_presets = TPColorpicker::get_color_presets();
?>

<div id="waitaminute" style="<?php echo $waitstyle; ?>">
	<div class="waitaminute-message"><i class="eg-icon-emo-coffee"></i><br><?php _e("Please Wait...", 'thunderslider'); ?></div>
</div>


<script type="text/javascript">
	var g_revNonce = "<?php echo $nonce; ?>";
	var g_uniteDirModule = "thunderslider";
	var g_urlContent = "<?php echo str_replace(array("\n", "\r", chr(10), chr(13)), array(''), content_url())."/"; ?>";
	var g_urlAjaxShowImage = "<?php echo ThunderSliderBase::$url_ajax_showimage; ?>";
	var g_urlAjaxActions = "<?php echo ThunderSliderBase::$url_ajax_actions; ?>";
	var g_thunderslider_url = "<?php echo RS_PLUGIN_URL; ?>";
	var g_settingsObj = {};
	var rs_pack_page_creation = <?php echo ($pack_page_creation == 'on') ? 'true' : 'false'; ?>;
	var rs_single_page_creation = <?php echo ($single_page_creation == 'on') ? 'true' : 'false'; ?>;
	var tp_color_picker_presets = jQuery.parseJSON(<?php echo ThunderSliderFunctions::jsonEncodeForClientSide($tp_color_picker_presets); ?>);
	
	var global_grid_sizes = {
		'desktop': '<?php echo ThunderSliderBase::getVar($glval, 'width', 1230); ?>',
		'notebook': '<?php echo ThunderSliderBase::getVar($glval, 'width_notebook', 1230); ?>',
		'tablet': '<?php echo ThunderSliderBase::getVar($glval, 'width_tablet', 992); ?>',
		'mobile': '<?php echo ThunderSliderBase::getVar($glval, 'width_mobile', 480); ?>'
	};
	
	var RS_DEMO = <?php echo (RS_DEMO) ? 'true' : 'false'; ?>;
</script>

<div id="div_debug"></div>

<div class='unite_error_message' id="error_message" style="display:none;"></div>

<div class='unite_success_message' id="success_message" style="display:none;"></div>

<div id="viewWrapper" class="view_wrapper<?php echo $wrapperClass; ?>">
	<?php self::requireView($view); ?>
</div>

<div id="divColorPicker" style="display:none;"></div>

<?php self::requireView("system/dialog-video"); ?>

<div class="tp-module-version">
	<span style="margin-right:15px">&copy; All rights reserved, <a href="http://www.jiiworks.net" target="_blank">MandarinCMS</a>  ver. <?php echo $thunderSliderVersion; ?></span>
</div>
<?php
/*
<script type="text/javascript">
	<span class="rs-shop">SHOP</span>
	jQuery(document).ready(function(){
		jQuery('.rs-shop').click(function(){
			
		});
	});
</script>
*/
?>
<div id="rs-shop-overview">
	<?php
	$shop_data = get_option('tp-shop');
	?>
</div>

<div id="rs-preview-wrapper" style="display: none;">
	<div id="rs-preview-wrapper-inner">
		<div id="rs-preview-info">
			<div class="rs-preview-toolbar">
				<a class="rs-close-preview"><i class="eg-icon-cancel"></i></a>
			</div>
			
			<div data-type="desktop" class="rs-preview-device_selector_prev rs-preview-ds-desktop selected"></div>									
			<div data-type="notebook" class="rs-preview-device_selector_prev rs-preview-ds-notebook"></div>					
			<div data-type="tablet" class="rs-preview-device_selector_prev rs-preview-ds-tablet"></div>					
			<div data-type="mobile" class="rs-preview-device_selector_prev rs-preview-ds-mobile"></div>
			
		</div>
		<div class="rs-frame-preview-wrapper">
			<iframe id="rs-frame-preview" name="rs-frame-preview"></iframe>
		</div>
	</div>
</div>
<form id="rs-preview-form" name="rs-preview-form" action="<?php echo ThunderSliderBase::$url_ajax_actions; ?>" target="rs-frame-preview" method="post">
	<input type="hidden" id="rs-client-action" name="client_action" value="">
	<input type="hidden" id="rs-nonce" name="rs-nonce" value="<?php echo $nonce; ?>">
	
	<!-- SPECIFIC FOR SLIDE PREVIEW -->
	<input type="hidden" name="data" value="" id="preview-slide-data">
	
	<!-- SPECIFIC FOR SLIDER PREVIEW -->
	<input type="hidden" id="preview_sliderid" name="sliderid" value="">
	<input type="hidden" id="preview_slider_markup" name="only_markup" value="">
</form>


<div id="dialog_preview_sliders" class="dialog_preview_sliders" title="Preview Slider" style="display:none;">
	<iframe id="frame_preview_slider" name="frame_preview_slider" style="width: 100%;"></iframe>
</div>


<div id="rs-premium-benefits-dialog" style="display: none;">
	<div class="rs-premium-benefits-dialogtitles" id="rs-wrong-purchase-code-title">
		<span class="oppps-icon"></span>
		<span class="benefits-title-right">
			<span class="rs-premium-benefits-dialogtitle"><?php _e('Ooops... Wrong Purchase Code!', 'thunderslider'); ?></span>
			<span class="rs-premium-benefits-dialogsubtitle"><?php _e('Maybe just a typo? (Click <a target="_blank" href="https://jiiworks.net/direct-customer-benefits/#productactivation">here</a> to find out how to locate your RazorLeaf ThunderSlider purchase code.)', 'thunderslider'); ?></span>
		</span>
	</div>
	<div style="display:none" class="rs-premium-benefits-dialogtitles" id="rs-module-cachewarning-title">
		<span class="oppps-icon"></span>
		<span class="benefits-title-right">
			<span class="rs-premium-benefits-dialogtitle"><?php _e('Make sure to clear all caches!', 'thunderslider'); ?></span>
			<span class="rs-premium-benefits-dialogsubtitle"><?php _e('Here are the most common cache issues explained.', 'thunderslider'); ?></span>
		</span>
	</div>
	<div style="display:none" class="rs-premium-benefits-dialogtitles" id="rs-module-update-feedback-title">
		<span class="oppps-icon-red"></span>
		<span class="benefits-title-right">
			<span class="rs-premium-benefits-dialogtitle"><?php _e('Module Activation Required'); ?></span>
			<span class="rs-premium-benefits-dialogsubtitle"><?php _e('In order to download the <a target="_blank" href="http://jiiworks.net/item/slider-revolution-responsive-mandarincms-module/2751380#item-description__update-history">latest update</a> instantly', 'thunderslider'); ?></span>
		</span>
	</div>
	<div style="display:none" class="rs-premium-benefits-dialogtitles" id="rs-module-object-library-feedback-title">
		<span class="oppps-icon"></span>
		<span class="benefits-title-right">
			<span class="rs-premium-benefits-dialogtitle"><?php _e('Module Activation Required'); ?></span>
			<span class="rs-premium-benefits-dialogsubtitle"><?php _e('In order to download from the <a target="_blank" href="https://jiiworks.net/object-library/">Object Library</a> instantly', 'thunderslider'); ?></span>
		</span>
	</div>
	<div style="display:none" class="rs-premium-benefits-dialogtitles" id="rs-module-download-template-feedback-title">
		<span class="oppps-icon"></span>
		<span class="benefits-title-right">
			<span class="rs-premium-benefits-dialogtitle"><?php _e('Module Activation Required'); ?></span>
			<span class="rs-premium-benefits-dialogsubtitle"><?php _e('In order to gain instant access to the entire <a target="_blank" href="https://jiiworks.net/examples/">Template Library</a>', 'thunderslider'); ?></span>
		</span>
	</div>

	<div style="display:none" class="rs-premium-benefits-dialogtitles" id="rs-library-license-info-dialogtitle">
		<span class="oppps-icon"></span>
		<span class="benefits-title-right">
			<span class="rs-premium-benefits-dialogtitle"><?php _e('Object Library License Information'); ?></span>
			<span class="rs-premium-benefits-dialogsubtitle"><?php _e('What you need to know for using RazorLeaf ThunderSlider elements in your projects', 'thunderslider'); ?></span>
		</span>
	</div>

	<div id="basic_premium_benefits_block">
		<div class="rs-premium-benefits-block rspb-withborder">
			<h3><i class="big_present"></i><?php _e('If you purchased a myskin that bundled RazorLeaf ThunderSlider', 'thunderslider'); ?></h3>
			<ul>
				<li><?php _e('No activation needed to use to use / create sliders with RazorLeaf ThunderSlider', 'thunderslider'); ?></li>
				<li><?php _e('Update manually through your myskin', 'thunderslider'); ?></li>
				<li><?php _e('Access our <a target="_blank" class="rspb_darklink" href="https://www.jiiworks.net/support-center/#support">FAQ database</a> and <a target="_blank" class="rspb_darklink" href="https://www.youtube.com/playlist?list=PLSCdqDWVMJPPDcH_57CNZvLckoB8cimJF">video tutorials</a> for help', 'thunderslider'); ?></li>
			</ul>
		</div>
		<div class="rs-premium-benefits-block">
			<h3><i class="big_diamond"></i><?php _e('Activate RazorLeaf ThunderSlider for', 'thunderslider'); ?> <span class="instant_access"></span> <?php _e('to ...', 'thunderslider'); ?></h3>
			<ul>
				<li><?php _e('<a target="_blank" href="https://jiiworks.net/direct-customer-benefits/#liveupdates">Update</a> to the latest version directly from your dashboard', 'thunderslider'); ?></li>
				<li><?php _e('<a target="_blank" href="https://jiiworks.net/submit/">Support</a> MandarinCMS ticket desk', 'thunderslider'); ?></li>
				<li><?php _e('<a target="_blank" href="https://jiiworks.net/direct-customer-benefits/#addons">Add-Ons</a> for RazorLeaf ThunderSlider', 'thunderslider'); ?></li>
				<li><?php _e('<a target="_blank" href="https://jiiworks.net/examples/">Library</a> with tons of free premium slider templates', 'thunderslider'); ?></li>
				<li><?php _e('<a target="_blank" href="https://jiiworks.net/direct-customer-benefits/#objectlibrary">Object Library</a> with tons of images ready to be used', 'thunderslider'); ?></li>
			</ul>
		</div>
		<a target="_blank" class="get_purchase_code" href="http://jiiworks.net/item/slider-revolution-responsive-mandarincms-module/2751380?ref=myskinpunch&license=regular&open_purchase_for_item_id=2751380&purchasable=source"><?php _e('GET A PURCHASE CODE', 'thunderslider'); ?></a>
	</div>

	<!-- WARNING BLOCK -->
	<div id="cachewarning_block" style="display:none">
		<div class="rs-premium-benefits-block">
			<h3><i class="big_light"></i><?php _e('Click on the following to learn how to fix issues', 'thunderslider'); ?></h3>
			<ul>
				<li><a target="_blank" href="https://www.jiiworks.net/faq/updating-make-sure-clear-caches#hosting"><?php _e('Hosting Cache', 'thunderslider'); ?></a></li>
				<li><a target="_blank" href="https://www.jiiworks.net/faq/updating-make-sure-clear-caches#modules"><?php _e('Caching Modules', 'thunderslider'); ?></a></li>
				<li><a target="_blank" href="https://www.jiiworks.net/faq/updating-make-sure-clear-caches#cdn"><?php _e('CDNs (Content Delivery Networks)', 'thunderslider'); ?></a></li>
				<li><a target="_blank" href="https://www.jiiworks.net/faq/updating-make-sure-clear-caches#browser"><?php _e('Browser Cache', 'thunderslider'); ?></a></li>
			</ul>
		</div>
		<div class="rs-premium-benefits-block" style="border:none;">
			<h3><i class="big_diamond"></i><?php _e('Cache Modules Installed on your Server', 'thunderslider'); ?></h3>
			<ul>				
				<?php
					$cache_modules = $rsop->get_installed_cache_modules();
					if(!empty($cache_modules)){
						foreach($cache_modules as $name => $url){
							?>
							<li><a href="<?php echo esc_url($url); ?>" target="_blank"><?php echo esc_html($name); ?></a></li><?php
						}
					}
					?>
			</ul>
		</div>
		
	</div>


	<div id="basic_objectlibrary_license_block">
		<div id="license_object_library_type_list_new">						
			<span data-id="svg_license_content" class="license_obj_library_cats_filter">SVG</span>
			<span data-id="ico_license_content" class="license_obj_library_cats_filter">ICON</span>							
			<span data-id="png_license_content" class="license_obj_library_cats_filter selected">PNG</span>
			<span data-id="jpg_license_content" class="license_obj_library_cats_filter">JPG</span>
		</div>
		
		<div class="license_deep_content" id="svg_license_content" style="display:none">
			<h3><i class="pe-7s-folder"></i><?php _e('Terms of Using SVG Objects from the Object Library', 'thunderslider'); ?></h3>
			<ul>
				<li><?php _e('Usage only allowed within RazorLeaf ThunderSlider Module', 'thunderslider'); ?></li>
				<li><?php _e('A variety of sizes and densities can be also downloaded from the <br> <a target="_blank" class="rspb_darklink" href="https://github.com/google/material-design-icons">git repository</a>, making it even easier for developers to customize, share,<br> and re-use outside of RazorLeaf ThunderSlider.', 'thunderslider'); ?></li>
				<li><?php _e('Licenses via Apache License. Read More at <a target="_blank" class="rspb_darklink" href="https://github.com/google/material-design-icons/blob/master/LICENSE">Google Material Design Icons</a>', 'thunderslider'); ?></li>
				
			</ul>
		</div>

		<div class="license_deep_content" id="ico_license_content" style="display:none">
			<h3><i class="pe-7s-folder"></i><?php _e('Terms of Using ICON Objects from the Object Library', 'thunderslider'); ?></h3>
			<ul>
				<li><?php _e('Please check the listed license files for details about how you can use the "FontAwesome" and "Stroke 7 Icon" font sets for commercial projects, open source projects, or really just about whatever you want.', 'thunderslider'); ?></li>
				<li><?php _e('Please respect all other icon fonts licenses for fonts not included directly into RazorLeaf ThunderSlider.', 'thunderslider'); ?></li>

			</ul>
			<h3><i class="fa-icon-list-alt"></i><?php _e('Further License Informations', 'thunderslider'); ?></h3>
			<ul>
				<li><strong>Font Awesome 4.6.3</strong> by @davegandy - http://fontawesome.io - @fontawesome<br>
				 License - <a target="_blank" href="http://fontawesome.io/license">http://fontawesome.io/license</a> (Font: SIL OFL 1.1, CSS: MIT License)</li>				 
				<li><strong>Stroke 7 Icon Font Set</strong> by www.pixeden.com<br>
				Get your Freebie Iconset at <a href="http://www.pixeden.com/icon-fonts/stroke-7-icon-font-set" target="_blank">http://www.pixeden.com/icon-fonts/stroke-7-icon-font-set</a></li>
			</ul>
		</div>

		<div class="license_deep_content" id="png_license_content">
			<h3><i class="pe-7s-folder"></i><?php _e('Terms of Using PNG Objects from the Object Library', 'thunderslider'); ?></h3>
			<ul>
				<li><?php _e('Usage only allowed within RazorLeaf ThunderSlider Module', 'thunderslider'); ?></li>
				<li><?php _e('Licenses via extended license and cooperation with<br>author <a target="_blank" class="rspb_darklink" href="https://creativemarket.com/Qeaql">Qeaql</a>', 'thunderslider'); ?></li>
				<li><?php _e('If you need .psd files for objects, you can purchase it from the original<br>author <a target="_blank" class="rspb_darklink" href="https://creativemarket.com/Qeaql/126175-Scene-creator-Top-view">here</a>', 'thunderslider'); ?></li>
			</ul>
		</div>

		<div class="license_deep_content" id="jpg_license_content" style="display:none">
			<h3><i class="pe-7s-folder"></i><?php _e('Terms of Using JPG Objects from the Object Library', 'thunderslider'); ?></h3>
			<ul>
				<li><?php _e('The pictures are free for personal and even for commercial use.', 'thunderslider'); ?></li>
				<li><?php _e('You can modify, copy and distribute the photos. All without asking for permission or setting a link to the source. So, attribution is not required.', 'thunderslider'); ?></li>
				<li><?php _e('The only restriction is that identifiable people may not appear in a bad light or in a way that they may find offensive, unless they give their consent.', 'thunderslider'); ?></li>
				<li><?php _e('The CC0 license was released by the non-profit organization Creative Commons (CC). Get more information about Creative Commons images and the license on the official license page.', 'thunderslider'); ?></li>				
				<li><?php _e('<a target="_blank" class="rspb_darklink" href="http://allthefreestock.com/">All The Freestock</a> under the License <a target="_blank" class="rspb_darklink" href="https://creativecommons.org/publicdomain/zero/1.0/">CC0 Creative Commons</a>', 'thunderslider'); ?></li>
			</ul>
		</div>
	</div>
</div>


<script>
	
	var	show_premium_dialog = function(clicked){
			jQuery('#rs-premium-benefits-dialog').dialog({
				width:830,
				height:750,
				modal:true,
				resizable:false,
				open:function(ui) {
					var dialog = jQuery(ui.target).parent(),						
						dialogtitle = dialog.find('.ui-dialog-title');

					dialog.addClass("rs-open-premium-benefits-dialog-container");
					if (!dialogtitle.hasClass("titlechanged")) {
						dialogtitle.html("");
						dialogtitle.append(jQuery('#rs-premium-benefits-dialog .rs-premium-benefits-dialogtitles'));
						dialogtitle.addClass("titlechanged");
					}
					
					//HIDE TITLE
					jQuery('#rs-library-license-info-dialogtitle, #rs-module-object-library-feedback-title, #rs-wrong-purchase-code-title, #rs-module-update-feedback-title, #rs-module-download-template-feedback-title').hide();
					jQuery('#rs-premium-benefits-dialog').removeClass("nomainbg")
					//HIDE CONTENT
					jQuery('#basic_objectlibrary_license_block, #basic_premium_benefits_block').hide();
					
					switch (clicked) {
						case "regsiter-to-access-update-none":
							jQuery('#rs-module-update-feedback-title').show();
							jQuery('#basic_premium_benefits_block').show();
						break;
						case "regsiter-to-access-store-none":
							jQuery('#rs-module-download-template-feedback-title').show();
							jQuery('#basic_premium_benefits_block').show();
						break;
						case "register-wrong-purchase-code":
							jQuery('#rs-wrong-purchase-code-title').show();
							jQuery('#basic_premium_benefits_block').show();
						break;
						case "register-to-acess-object-library":
							jQuery('#rs-module-object-library-feedback-title').show();
							jQuery('#basic_premium_benefits_block').show();
						break;
						case "licence_obect_library":						
							jQuery('#basic_objectlibrary_license_block').show();
							jQuery('#rs-library-license-info-dialogtitle').show();
							jQuery('#rs-premium-benefits-dialog').addClass("nomainbg");
						break;
						case "cahce-warning-dialog":
							jQuery('#rs-module-cachewarning-title').show();
							jQuery('#cachewarning_block').show();
							jQuery('#rs-premium-benefits-dialog').addClass("cachbg");
							jQuery('#rs-premium-benefits-dialog').height(600)
						break;
					}
				}
			});
		}
	
</script>
<?php
$cvc = get_option('rs_cache_overlay', ThunderSliderGlobals::SLIDER_REVISION);

if(version_compare(ThunderSliderGlobals::SLIDER_REVISION, $cvc, '>')){
	?>
	<div id="rs-cache-info-dialog" title="">
		
		
		<div><?php _e('Click on the following to learn how to fix issues', 'thunderslider'); ?></div>
		
		
		
	</div>
	<script type="text/javascript">
		jQuery(document).ready(function() {			
			show_premium_dialog("cahce-warning-dialog");
		});				
	</script>
	<?php 
	$cvc = ThunderSliderGlobals::SLIDER_REVISION;
}
update_option('rs_cache_overlay', $cvc);
?>

<script type="text/javascript">
    <?php
	$validated = get_option('thunderslider-valid', 'false');
	?>
	rs_module_validated = <?php echo ($validated == 'true') ? 'true' : 'false'; ?>;
	
    jQuery('body').on('click','.rs-preview-device_selector_prev', function() {
    	var btn = jQuery(this);
    	jQuery('.rs-preview-device_selector_prev.selected').removeClass("selected");    	
    	btn.addClass("selected");
    	
    	var w = parseInt(global_grid_sizes[btn.data("type")],0);
    	if (w>1450) w = 1450;
    	jQuery('#rs-preview-wrapper-inner').css({maxWidth:w+"px"});
    	
    });

    jQuery(window).resize(function() {
    	var ww = jQuery(window).width();
    	if (global_grid_sizes)
	    	jQuery.each(global_grid_sizes,function(key,val) {    		
	    		if (ww<=parseInt(val,0)) {
	    			jQuery('.rs-preview-device_selector_prev.selected').removeClass("selected");
	    			jQuery('.rs-preview-device_selector_prev[data-type="'+key+'"]').addClass("selected");
	    		}
	    	})
    })

	/* SHOW A WAIT FOR PROGRESS */
	function showWaitAMinute(obj) {
		var wm = jQuery('#waitaminute');

		// CHANGE TEXT
		if (obj.text != undefined) {
			switch (obj.text) {
				case "progress1":

				break;
				default:	
					
					wm.html('<div class="waitaminute-message"><i class="eg-icon-emo-coffee"></i><br>'+obj.text+'</div>');					
				break;	
			}
		}		
		
		
		if (obj.delay!=undefined) {
			punchgs.TweenLite.to(wm,0.3,{autoAlpha:1,ease:punchgs.Power3.easeInOut});
			punchgs.TweenLite.set(wm,{display:"block"});
			
			setTimeout(function() {
				punchgs.TweenLite.to(wm,0.3,{autoAlpha:0,ease:punchgs.Power3.easeInOut,onComplete:function() {
					punchgs.TweenLite.set(wm,{display:"block"});	
				}});  			
			},obj.delay)
		}

		// SHOW IT
		if (obj.fadeIn !== undefined) {
			punchgs.TweenLite.to(wm,obj.fadeIn/1000,{autoAlpha:1,ease:punchgs.Power3.easeInOut});
			punchgs.TweenLite.set(wm,{display:"block"});			
		}

		// HIDE IT
		if (obj.fadeOut !== undefined) {
			punchgs.TweenLite.to(wm,obj.fadeOut/1000,{autoAlpha:0,ease:punchgs.Power3.easeInOut,onComplete:function() {
				punchgs.TweenLite.set(wm,{display:"block"});	
			}});  
		}

		
	}
	
	
	jQuery(document).ready(function(){
		jQuery('#licence_obect_library, #regsiter-to-access-update-none, #regsiter-to-access-store-none, #register-wrong-purchase-code').click(function(){		
			var clicked = jQuery(this).attr('id');	
			show_premium_dialog(clicked);
		});
		
		jQuery('.license_obj_library_cats_filter').click(function() {
			var t = jQuery(this);
			jQuery('.license_obj_library_cats_filter').removeClass("selected");
			t.addClass("selected");
			jQuery('.license_deep_content').hide();
			jQuery("#"+t.data('id')).show();
		});
				
	});
	
	
</script>