<?php
/**
 * @package   RazorLeaf ThunderSlider
 * @author    MandarinCMS <info@jiiworks.net>
 * @link      http://jiiworks.net/
 * @copyright 2017 MandarinCMS
 */
 
if( !defined( 'BASED_TREE_URI') ) exit();

$operations = new ThunderSliderOperations();
$arrValues = $operations->getGeneralSettingsValues();

$role = ThunderSliderBase::getVar($arrValues, 'role', 'admin');
$includes_globally = ThunderSliderBase::getVar($arrValues, 'includes_globally', 'on');
$pages_for_includes = ThunderSliderBase::getVar($arrValues, 'pages_for_includes', '');
$js_to_footer = ThunderSliderBase::getVar($arrValues, 'js_to_footer', 'off');
$js_defer = ThunderSliderBase::getVar($arrValues, 'js_defer', 'off');
$show_dev_export = ThunderSliderBase::getVar($arrValues, 'show_dev_export', 'off');
$change_font_loading = ThunderSliderBase::getVar($arrValues, 'change_font_loading', '');
$enable_logs = ThunderSliderBase::getVar($arrValues, 'enable_logs', 'off');
$load_all_javascript = ThunderSliderBase::getVar($arrValues, 'load_all_javascript', 'off');

$pack_page_creation = ThunderSliderBase::getVar($arrValues, 'pack_page_creation', 'on');
$single_page_creation = ThunderSliderBase::getVar($arrValues, 'single_page_creation', 'off');

$stage_collapse = ThunderSliderBase::getVar($arrValues, "stage_collapse",'off');

$enable_newschannel = apply_filters('thunderslider_set_notifications', 'on');
$enable_newschannel = ThunderSliderBase::getVar($arrValues, "enable_newschannel",$enable_newschannel);

$width = ThunderSliderBase::getVar($arrValues, 'width', 1240);
$width_notebook = ThunderSliderBase::getVar($arrValues, 'width_notebook', 1024);
$width_tablet = ThunderSliderBase::getVar($arrValues, 'width_tablet', 778);
$width_mobile = ThunderSliderBase::getVar($arrValues, 'width_mobile', 480);

$force_activation_box = ThunderSliderBase::getVar($arrValues, 'force_activation_box', 'off');

?>
<div class='wrap'>
	<div class="clear_both"></div>
	<div class="title_line" style="margin-bottom:10px">
		<?php 
		$icon_general = '<div class="icon32" id="icon-options-general"></div>';
		echo apply_filters( 'rev_icon_general_filter', $icon_general ); 
		?>
	</div>

	<div class="clear_both"></div>
	
	<div id="rs-global-settings-dialog-wrap">
		<form name="form_general_settings" id="form_general_settings">
			<script type="text/javascript">
				g_settingsObj['form_general_settings'] = {};
				
				jQuery(document).ready(function(){
					ThunderSliderSettings.createModernOnOff();
					
					jQuery('.tp-moderncheckbox').each(function(){
						ThunderSliderSettings.onoffStatus(jQuery(this));
					});
				});
			</script>
			<div class="rs-global-setting">
				<div class="rs-gs-tc">
					<label><?php _e("View Module Permission:",'thunderslider'); ?></label>
				</div>
				<div class="rs-gs-tc">
					<select id="role" name="role">
						<option <?php selected($role, 'admin'); ?> value="admin"><?php _e("To Admin",'thunderslider'); ?></option>
						<option <?php selected($role, 'editor'); ?> value="editor"><?php _e("To Editor, Admin",'thunderslider'); ?></option>
						<option <?php selected($role, 'author'); ?> value="author"><?php _e("Author, Editor, Admin",'thunderslider'); ?></option>
					</select>
				</div>
				<div class="rs-gs-tc">
					<i style=""><?php _e("The role of user that can view and edit the module",'thunderslider'); ?></i>
				</div>
			</div>
			
			<div class="rs-global-setting">
				<div class="rs-gs-tc">
					<label><?php _e("Include ThunderSlider libraries globally:",'thunderslider'); ?></label>
				</div>
				<div class="rs-gs-tc">
					<input type="checkbox" id="includes_globally_1" name="includes_globally" class="tp-moderncheckbox" data-unchecked="off" <?php checked($includes_globally, "on");?>>
				</div>
				<div class="rs-gs-tc">
					<i style=""><?php _e("ON - Add CSS and JS Files to all pages. </br>Off - CSS and JS Files will be only loaded on Pages where any thunder_slider shortcode exists.",'thunderslider'); ?></i>
				</div>
			</div>

			<div class="rs-global-setting">
				<div class="rs-gs-tc">
					<label><?php _e("Pages to include ThunderSlider libraries:", 'thunderslider'); ?></label>
				</div>
				<div class="rs-gs-tc">
					<input type="text" class="regular-text" id="pages_for_includes" name="pages_for_includes" value="<?php echo $pages_for_includes; ?>">
				</div>
				<div class="rs-gs-tc">
					<i style=""><?php _e("Specify the page id's that the front end includes will be included in. Example: 2,3,5 also: homepage,3,4",'thunderslider'); ?></i>
				</div>
			</div>
			
			<div class="rs-global-setting">
				<div class="rs-gs-tc">
					<label><?php _e("Insert JavaScript Into Footer:",'thunderslider'); ?></label>
				</div>
				<div class="rs-gs-tc">
					<input type="checkbox" id="js_to_footer_1" name="js_to_footer" class="tp-moderncheckbox" data-unchecked="off" <?php checked($js_to_footer, 'on'); ?>>
				</div>
				<div class="rs-gs-tc">
					<i style=""><?php _e("Putting the js to footer (instead of the head) is good for fixing some javascript conflicts.",'thunderslider'); ?></i>
				</div>
			</div>
			
			<div class="rs-global-setting">
				<div class="rs-gs-tc">
					<label><?php _e("Defer JavaScript Loading:",'thunderslider'); ?></label>
				</div>
				<div class="rs-gs-tc">
					<input type="checkbox" id="js_defer_1" name="js_defer" class="tp-moderncheckbox" data-unchecked="off" <?php checked($js_defer, 'on'); ?>>
				</div>
				<div class="rs-gs-tc">
					<i style=""><?php _e("Defer the loading of the JavaScript libraries to maximize page loading speed.",'thunderslider'); ?></i>
				</div>
			</div>
			
			<div class="rs-global-setting">
				<div class="rs-gs-tc">
					<label><?php _e("Load all JavaScript libraries:", 'thunderslider'); ?></label>
				</div>
				<div class="rs-gs-tc">
					<input type="checkbox" id="load_all_javascript_1" name="load_all_javascript" class="tp-moderncheckbox" data-unchecked="off" <?php checked($load_all_javascript, 'on'); ?>>
				</div>
				<div class="rs-gs-tc">
					<i style=""><?php _e("Enabling this will load all JavaScript libraries of RazorLeaf ThunderSlider. Disabling this will let RazorLeaf ThunderSlider load only the libraries needed for the current Sliders on page. Enabling this option can solve CDN issues.",'thunderslider'); ?></i>
				</div>
			</div>
			
			<div class="rs-global-setting">
				<div class="rs-gs-tc">
					<label><?php _e("Enable Markup Export option:",'thunderslider'); ?></label>
				</div>
				<div class="rs-gs-tc">
					<input type="checkbox" id="show_dev_export_1" name="show_dev_export" class="tp-moderncheckbox" data-unchecked="off" <?php checked($show_dev_export, 'on'); ?>>
				</div>
				<div class="rs-gs-tc">
					<i style=""><?php _e("This will enable the option to export the Slider Markups to copy/paste it directly into websites.",'thunderslider'); ?></i>
				</div>
			</div>
			
			<div class="rs-global-setting">
				<div class="rs-gs-tc">
					<label><?php _e("Font Loading URL:",'thunderslider'); ?></label>
				</div>
				<div class="rs-gs-tc">
					<input id="change_font_loading" name="change_font_loading" type="text" class="regular-text" value="<?php echo $change_font_loading; ?>">
				</div>
				<div class="rs-gs-tc">
					<i style=""><?php _e("Insert something in it and it will be used instead of http://fonts.googleapis.com/css?family= (For example: http://fonts.useso.com/css?family= which will also work for chinese visitors)",'thunderslider'); ?></i>
				</div>
			</div>
			
			
			<div class="rs-global-setting">
				<div class="rs-gs-tc">
					<label><?php _e("Default Settings for Advanced Responsive Grid Sizes:",'thunderslider'); ?></label>
				</div>
				<div class="rs-gs-tc">
					<div>
						<?php _e('Desktop Grid Width', 'thunderslider'); ?>
						<input id="width" name="width" type="text" class="textbox-small" value="<?php echo $width; ?>">
					</div>
					<div>
						<?php _e('Notebook Grid Width', 'thunderslider'); ?>
						<input id="width_notebook" name="width_notebook" type="text" class="textbox-small" value="<?php echo $width_notebook; ?>">
					</div>
					<div>
						<?php _e('Tablet Grid Width', 'thunderslider'); ?>
						<input name="width_tablet" type="text" class="textbox-small" value="<?php echo $width_tablet; ?>">
					</div>
					<div>
						<?php _e('Mobile Grid Width', 'thunderslider'); ?>
						<input name="width_mobile" type="text" class="textbox-small" value="<?php echo $width_mobile; ?>">
					</div>
				</div>
				<div class="rs-gs-tc">
					<i style=""><?php _e("Define the default Grid Sizes for devices: Desktop, Tablet and Mobile",'thunderslider'); ?></i>
				</div>
			</div>
			
			<div class="rs-global-setting">
				<div class="rs-gs-tc">
					<label><?php _e("Slide Stage Collapse:",'thunderslider'); ?></label>
				</div>
				<div class="rs-gs-tc">
					<input type="checkbox" id="" name="stage_collapse" class="tp-moderncheckbox" data-unchecked="off" <?php checked($stage_collapse, 'on'); ?>>
				</div>
				<div class="rs-gs-tc">
					<i style=""><?php _e("Collapse left MandarinCMS Menu on Slide Stage automatically ",'thunderslider'); ?></i>
				</div>
			</div>
			
			<div class="rs-global-setting">
				<div class="rs-gs-tc">
					<label><?php _e("Enable Notifications:",'thunderslider'); ?></label>
				</div>
				<div class="rs-gs-tc">
					<input type="checkbox" id="" name="enable_newschannel" class="tp-moderncheckbox" data-unchecked="off" <?php checked($enable_newschannel, 'on'); ?>>
				</div>
				<div class="rs-gs-tc">
					<i style=""><?php _e("Enable/Disable MandarinCMS Notifications in the Admin Notice bar.",'thunderslider'); ?></i>
				</div>
			</div>
			
			<div class="rs-global-setting">
				<div class="rs-gs-tc">
					<label><?php _e("Enable Logs:",'thunderslider'); ?></label>
				</div>
				<div class="rs-gs-tc">
					<input type="checkbox" id="enable_logs_1"name="enable_logs" class="tp-moderncheckbox" data-unchecked="off" <?php checked($enable_logs, 'on'); ?>>
				</div>
				<div class="rs-gs-tc">
					<i style=""><?php _e("Enable console logs for debugging.",'thunderslider'); ?></i>
				</div>
			</div>
			
			<div class="rs-global-setting">
				<div class="rs-gs-tc">
					<label><?php _e("Enable Missing Activation Area:",'thunderslider'); ?></label>
				</div>
				<div class="rs-gs-tc">
					<input type="checkbox" id="" name="force_activation_box" class="tp-moderncheckbox" data-unchecked="off" <?php checked($force_activation_box, 'on'); ?>>
				</div>
				<div class="rs-gs-tc">
					<i style=""><?php _e("Force the Activation Area to show up if the Theme disabled it.",'thunderslider'); ?></i>
				</div>
			</div>
			
			<div class="rs-global-setting">
				<div class="rs-gs-tc">
					<label><?php _e("Enable Blank Page Creation for Slider Packages:",'thunderslider'); ?></label>
				</div>
				<div class="rs-gs-tc">
					<input type="checkbox" id="" name="pack_page_creation" class="tp-moderncheckbox" data-unchecked="off" <?php checked($pack_page_creation, 'on'); ?>>
				</div>
				<div class="rs-gs-tc">
					<i style=""><?php _e("Enable option to automatically create a Blank Page if a Slider Pack is installed.",'thunderslider'); ?></i>
				</div>
			</div>
			
			<div class="rs-global-setting">
				<div class="rs-gs-tc">
					<label><?php _e("Enable Blank Page Creation for Single Sliders:",'thunderslider'); ?></label>
				</div>
				<div class="rs-gs-tc">
					<input type="checkbox" id="" value="on" name="single_page_creation" class="tp-moderncheckbox" data-unchecked="off" <?php checked($single_page_creation, 'on'); ?>>
				</div>
				<div class="rs-gs-tc">
					<i style=""><?php _e("Enable option to automatically create a Blank Page if a Single Slider is installed.",'thunderslider'); ?></i>
				</div>
			</div>
			
			<div class="rs-global-setting">
				<div class="rs-gs-tc">
					<label><?php _e("Run RazorLeaf ThunderSlider database creation:",'thunderslider'); ?></label>
				</div>
				<div class="rs-gs-tc">
					<a id="trigger_database_creation" class="button-primary revblue" original-title="" href="javascript:void(0);"><?php _e('Go!', 'thunderslider'); ?></a>
				</div>
				<div class="rs-gs-tc">
					<i style=""><?php _e("Force creation of RazorLeaf ThunderSlider database structure to fix table issues that may occur for example at the Slider creation process.",'thunderslider'); ?></i>
				</div>
			</div>
			
		</form>
	</div>
	<p>
		<a id="button_save_general_settings" class="button-primary revgreen" original-title=""><?php _e('Save Settings', 'thunderslider'); ?></a>
	</p>
	<span id="loader_general_settings" class="loader_round mleft_10" style="display: none;"></span>

</div>
