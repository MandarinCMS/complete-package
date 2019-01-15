<?php

if( !defined( 'BASED_TREE_URI') ) exit();

$orders = false;
//order=asc&ot=name&type=reg
if(isset($_GET['ot']) && isset($_GET['order']) && isset($_GET['type'])){
	$order = array();
	switch($_GET['ot']){
		case 'alias':
			$order['alias'] = ($_GET['order'] == 'asc') ? 'ASC' : 'DESC';
		break;
		case 'favorite':
			$order['favorite'] = ($_GET['order'] == 'asc') ? 'ASC' : 'DESC';
		break;
		case 'name':
		default:
			$order['title'] = ($_GET['order'] == 'asc') ? 'ASC' : 'DESC';
		break;
	}
	
	$orders = $order;
}


$slider = new ThunderSlider();
$operations = new ThunderSliderOperations();
$arrSliders = $slider->getArrSliders($orders);
$glob_vals = $operations->getGeneralSettingsValues();

$addNewLink = self::getViewUrl(ThunderSliderAdmin::VIEW_SLIDER);


$fav = get_option('rev_fav_slider', array());
if($orders == false){ //sort the favs to top
	if(!empty($fav) && !empty($arrSliders)){
		$fav_sort = array();
		foreach($arrSliders as $skey => $sort_slider){
			if(in_array($sort_slider->getID(), $fav)){
				$fav_sort[] = $arrSliders[$skey];
				unset($arrSliders[$skey]);
			}
		}
		if(!empty($fav_sort)){
			//revert order of favs
			krsort($fav_sort);
			foreach($fav_sort as $fav_arr){
				array_unshift($arrSliders, $fav_arr);
			}
		}
	}
}

global $thunderSliderAsTheme;

$exampleID = '"slider1"';
if(!empty($arrSliders))
	$exampleID = '"'.$arrSliders[0]->getAlias().'"';

$latest_version = get_option('thunderslider-latest-version', ThunderSliderGlobals::SLIDER_REVISION);
$stable_version = get_option('thunderslider-stable-version', '4.1');

?>

<div class='wrap'>


	<div class="clear_both"></div>

	<div class="title_line nobgnopd" style="height:auto; min-height:50px">
		<div class="view_title">
			<?php _e("RazorLeaf ThunderSliders", 'thunderslider'); ?>			
		</div>
		<div class="slider-sortandfilter">
				<span class="slider-listviews slider-lg-views" data-type="rs-listview"><i class="eg-icon-align-justify"></i></span>
				<span class="slider-gridviews slider-lg-views active" data-type="rs-gridview"><i class="eg-icon-th"></i></span>
				<span class="slider-sort-drop"><?php _e("Sort By:",'thunderslider'); ?></span>
				<select id="sort-sliders" name="sort-sliders" style="max-width: 105px;" class="withlabel">
					<option value="id" selected="selected"><?php _e("By ID",'thunderslider'); ?></option>
					<option value="name"><?php _e("By Name",'thunderslider'); ?></option>
					<option value="type"><?php _e("By Type",'thunderslider'); ?></option>
					<option value="favorit"><?php _e("By Favorit",'thunderslider'); ?></option>
				</select>
				
				<span class="slider-filter-drop"><?php _e("Filter By:",'thunderslider'); ?></span>
				
				<select id="filter-sliders" name="filter-sliders" style="max-width: 105px;" class="withlabel">
					<option value="all" selected="selected"><?php _e("All",'thunderslider'); ?></option>
					<option value="posts"><?php _e("Posts",'thunderslider'); ?></option>
					<option value="gallery"><?php _e("Gallery",'thunderslider'); ?></option>
					<option value="vimeo"><?php _e("Vimeo",'thunderslider'); ?></option>
					<option value="youtube"><?php _e("YouTube",'thunderslider'); ?></option>
					<option value="twitter"><?php _e("Twitter",'thunderslider'); ?></option>
					<option value="facebook"><?php _e("Facebook",'thunderslider'); ?></option>
					<option value="instagram"><?php _e("Instagram",'thunderslider'); ?></option>
					<option value="flickr"><?php _e("Flickr",'thunderslider'); ?></option>
				</select>
		</div>
		<div style="width:100%;height:1px;float:none;clear:both"></div>
	</div>

	<?php
	$no_sliders = false;
	if(empty($arrSliders)){
		?>
		<span style="display:block;margin-top:15px;margin-bottom:15px;">
		<?php  _e("No Sliders Found",'thunderslider'); ?>
		</span>
		<?php
		$no_sliders = true;
	}

	require self::getPathTemplate('sliders-list');

	?>
	<!--
	THE INFO ABOUT EMBEDING OF THE SLIDER
	-->
	<div class="rs-dialog-embed-slider" title="<?php _e("Embed Slider",'thunderslider'); ?>" style="display: none;">
		<div class="revyellow" style="background: none repeat scroll 0% 0% #F1C40F; left:0px;top:55px;position:absolute;height:205px;padding:20px 10px;"><i style="color:#fff;font-size:25px" class="revicon-arrows-ccw"></i></div>
		<div style="margin:5px 0px; padding-left: 55px;">
			<div style="font-size:14px;margin-bottom:10px;"><strong><?php _e("Standard Embeding",'thunderslider'); ?></strong></div>
			<?php _e("For the",'thunderslider'); ?> <b><?php _e("pages or posts editor",'thunderslider'); ?></b> <?php _e("insert the shortcode:",'thunderslider'); ?> <code class="rs-example-alias-1"></code>
			<div style="width:100%;height:10px"></div>
			<?php _e("From the",'thunderslider'); ?> <b><?php _e("widgets panel",'thunderslider'); ?></b> <?php _e("drag the \"RazorLeaf ThunderSlider\" widget to the desired sidebar",'thunderslider'); ?>
			<div style="width:100%;height:25px"></div>
			<div id="advanced-emeding" style="font-size:14px;margin-bottom:10px;"><strong><?php _e("Advanced Embeding",'thunderslider'); ?></strong><i class="eg-icon-plus"></i></div>
			<div id="advanced-accord" style="display:none; line-height:25px">
				<?php _e("From the",'thunderslider'); ?> <b><?php _e("myskin html",'thunderslider'); ?></b> <?php _e("use",'thunderslider'); ?>: <code>&lt?php putThunderSlider( '<span class="rs-example-alias">alias</span>' ); ?&gt</code><br>
				<span><?php _e("To add the slider only to homepage use",'thunderslider'); ?>: <code>&lt?php putThunderSlider('<span class="rs-example-alias"><?php echo $exampleID; ?></span>', 'homepage'); ?&gt</code></span></br>
				<span><?php _e("To add the slider on specific pages or posts use",'thunderslider'); ?>: <code>&lt?php putThunderSlider('<span class="rs-example-alias"><?php echo $exampleID; ?></span>', '2,10'); ?&gt</code></span></br>
			</div>
			
		</div>
	</div>
	<script>
		jQuery('#advanced-emeding').click(function() {
			jQuery('#advanced-accord').toggle(200);
		});
	</script>


	<div style="width:100%;height:40px"></div>
	 
</div>

<!-- Import slider dialog -->
<div id="dialog_import_slider" title="<?php _e("Import Slider",'thunderslider'); ?>" class="dialog_import_slider" style="display:none">
	<form action="<?php echo ThunderSliderBase::$url_ajax; ?>" enctype="multipart/form-data" method="post" id="form-import-slider-local">
		<br>
		<input type="hidden" name="action" value="thunderslider_ajax_action">
		<input type="hidden" name="client_action" value="import_slider_slidersview">
		<input type="hidden" name="nonce" value="<?php echo mcms_create_nonce("thunderslider_actions"); ?>">
		<?php _e("Choose the import file",'thunderslider'); ?>:
		<br>
		<input type="file" size="60" name="import_file" class="input_import_slider">
		<br><br>
		<span style="font-weight: 700;"><?php _e("Note: styles templates will be updated if they exist!",'thunderslider'); ?></span><br><br>
		<table>
			<tr>
				<td><?php _e("Custom Animations:",'thunderslider'); ?></td>
				<td><input type="radio" name="update_animations" value="true" checked="checked"> <?php _e("Overwrite",'thunderslider'); ?></td>
				<td><input type="radio" name="update_animations" value="false"> <?php _e("Append",'thunderslider'); ?></td>
			</tr>
			<tr>
				<td><?php _e("Custom Navigations:",'thunderslider'); ?></td>
				<td><input type="radio" name="update_navigations" value="true" checked="checked"> <?php _e("Overwrite",'thunderslider'); ?></td>
				<td><input type="radio" name="update_navigations" value="false"> <?php _e("Append",'thunderslider'); ?></td>
			</tr>
			<!--tr>
				<td><?php _e("Static Styles:",'thunderslider'); ?></td>
				<td><input type="radio" name="update_static_captions" value="true"> <?php _e("Overwrite",'thunderslider'); ?></td>
				<td><input type="radio" name="update_static_captions" value="false"> <?php _e("Append",'thunderslider'); ?></td>
				<td><input type="radio" name="update_static_captions" value="none" checked="checked"> <?php _e("Ignore",'thunderslider'); ?></td>
			</tr-->
			<?php
			$single_page_creation = ThunderSliderFunctions::getVal($glob_vals, "single_page_creation", "off");
			?>
			<tr style="<?php echo ($single_page_creation == 'on') ? '' : 'display: none;'; ?>">
				<td><?php _e('Create Blank Page:','thunderslider'); ?></td>
				<td><input type="radio" name="page-creation" value="true"> <?php _e('Yes', 'thunderslider'); ?></td>
				<td><input type="radio" name="page-creation" value="false" checked="checked"> <?php _e('No', 'thunderslider'); ?></td>
			</tr>
		</table>
		<br>
		<input type="submit" class="button-primary revblue tp-be-button rev-import-slider-button" style="display: none;" value="<?php _e("Import Slider",'thunderslider'); ?>">
	</form>
</div>

<div id="dialog_duplicate_slider" class="dialog_duplicate_layer" title="<?php _e('Duplicate', 'thunderslider'); ?>" style="display:none;">
	<div style="margin-top:14px">
		<span style="margin-right:15px"><?php _e('Title:', 'thunderslider'); ?></span><input id="rs-duplicate-animation" type="text" name="rs-duplicate-animation" value="" />
	</div>
</div>

<div id="dialog_duplicate_slider_package" class="dialog_duplicate_layer" title="<?php _e('Duplicate', 'thunderslider'); ?>" style="display:none;">
	<div style="margin-top:14px">
		<span style="margin-right:15px"><?php _e('Prefix:', 'thunderslider'); ?></span><input id="rs-duplicate-prefix" type="text" name="rs-duplicate-prefix" value="" />
	</div>
</div>

<script type="text/javascript">
	jQuery(document).ready(function(){
		ThunderSliderAdmin.initSlidersListView();
		ThunderSliderAdmin.initNewsletterRoutine();
		
		jQuery('#benefitsbutton').hover(function() {
			jQuery('#benefitscontent').slideDown(200);
		}, function() {
			jQuery('#benefitscontent').slideUp(200);
		});
		
		jQuery('#why-subscribe').hover(function() {
			jQuery('#why-subscribe-wrapper').slideDown(200);
		}, function() {
			jQuery('#why-subscribe-wrapper').slideUp(200);				
		});
		
		jQuery('#tp-validation-box').click(function() {
			jQuery(this).css({cursor:"default"});
			if (jQuery('#rs-validation-wrapper').css('display')=="none") {
				jQuery('#tp-before-validation').hide();
				jQuery('#rs-validation-wrapper').slideDown(200);
			}
		});

		jQuery('body').on('click','.rs-dash-more-info',function() {
			var btn = jQuery(this),
				p = btn.closest('.rs-dash-widget-inner'),
				tmb = btn.data('takemeback'),
				btxt = '';

			btxt = btxt + '<div class="rs-dash-widget-warning-panel">';
			btxt = btxt + '	<i class="eg-icon-cancel rs-dash-widget-mcms-cancel"></i>';
			btxt = btxt + '	<div class="rs-dash-strong-content">'+ btn.data("title")+'</div>';				
			btxt = btxt + '	<div class="rs-dash-content-space"></div>';
			btxt = btxt + '	<div>'+btn.data("content")+'</div>';
		
			if (tmb!=="false" && tmb!==false) {
				btxt = btxt + '	<div class="rs-dash-content-space"></div>';
				btxt = btxt + '	<span class="rs-dash-invers-button-gray rs-dash-close-panel">Thanks! Take me back</span>';
			}
			btxt = btxt + '</div>';

			p.append(btxt);
			var panel = p.find('.rs-dash-widget-warning-panel');

			punchgs.TweenLite.fromTo(panel,0.3,{y:-10,autoAlpha:0},{autoAlpha:1,y:0,ease:punchgs.Power3.easeInOut});
			panel.find('.rs-dash-widget-mcms-cancel, .rs-dash-close-panel').click(function() {
				punchgs.TweenLite.to(panel,0.3,{y:-10,autoAlpha:0,ease:punchgs.Power3.easeInOut});
				setTimeout(function() {
					panel.remove();
				},300)
			})
		});
	});
</script>
<?php
require self::getPathTemplate('template-slider-selector');
?>

<div style="visibility: none;" id="register-wrong-purchase-code"></div>

