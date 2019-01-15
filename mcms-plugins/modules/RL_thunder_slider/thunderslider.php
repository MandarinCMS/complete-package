<?php
/*
Module Name: RazorLeaf Thunder Slider
Description: RazorLeaf Thunder Slider responsive and interactive slider
Version: 5.4.6.4
*/

// If this file is called directly, abort.
if ( ! defined( 'MCMSINC' ) ) {
	die;
}

if(class_exists('ThunderSliderFront')) {
	die('ERROR: It looks like you have more than one instance of RazorLeaf ThunderSlider installed. Please remove additional instances for this module to work again.');
}

$thunderSliderVersion	= '5.4.6.4';
$thunderSliderAsTheme	= false;
$thunderslider_screens	= array();
$thunderslider_fonts	= array();
$rs_module_url		= str_replace('index.php','',modules_url( 'index.php', __FILE__ ));
if(strpos($rs_module_url, 'http') === false) {
	$site_url		= get_site_url();
	$rs_module_url	= (substr($site_url, -1) === '/') ? substr($site_url, 0, -1). $rs_module_url : $site_url. $rs_module_url;
}
$rs_module_url		= str_replace(array(chr(10), chr(13)), '', $rs_module_url);

define('RS_PLUGIN_PATH',		module_dir_path(__FILE__));
define('RS_PLUGIN_FILE_PATH',	__FILE__);
define('RS_PLUGIN_URL',			$rs_module_url);
define('RS_PLUGIN_SLUG',		apply_filters('set_thunderslider_slug', 'thunderslider'));
define('RS_DEMO',				false);

if(isset($_GET['thunderSliderAsTheme'])){
	if($_GET['thunderSliderAsTheme'] == 'true'){
		update_option('thunderSliderAsTheme', 'true');
	}else{
		update_option('thunderSliderAsTheme', 'false');
	}
}

//set the ThunderSlider Module as a Theme. This hides the activation notice and the activation area in the Slider Overview
function set_thunderslider_as_myskin(){
	global $thunderSliderAsTheme;
	
	if(defined('REV_SLIDER_AS_THEME')){
		if(REV_SLIDER_AS_THEME == true)
			$thunderSliderAsTheme = true;
	}else{
		if(get_option('thunderSliderAsTheme', 'true') == 'true')
			$thunderSliderAsTheme = true;
	}
}

//include frameword files
require_once(RS_PLUGIN_PATH . 'includes/framework/include-framework.php');

//include bases
require_once($folderIncludes . 'base.class.php');
require_once($folderIncludes . 'elements-base.class.php');
require_once($folderIncludes . 'base-admin.class.php');
require_once($folderIncludes . 'base-front.class.php');

//include product files
require_once(RS_PLUGIN_PATH . 'includes/globals.class.php');
require_once(RS_PLUGIN_PATH . 'includes/operations.class.php');
require_once(RS_PLUGIN_PATH . 'includes/slider.class.php');
require_once(RS_PLUGIN_PATH . 'includes/output.class.php');
require_once(RS_PLUGIN_PATH . 'includes/slide.class.php');
require_once(RS_PLUGIN_PATH . 'includes/widget.class.php');
require_once(RS_PLUGIN_PATH . 'includes/navigation.class.php');
require_once(RS_PLUGIN_PATH . 'includes/object-library.class.php');
require_once(RS_PLUGIN_PATH . 'includes/template.class.php');
require_once(RS_PLUGIN_PATH . 'includes/external-sources.class.php');
require_once(RS_PLUGIN_PATH . 'includes/page-template.class.php');

require_once(RS_PLUGIN_PATH . 'includes/tinybox.class.php');
require_once(RS_PLUGIN_PATH . 'includes/extension.class.php');
require_once(RS_PLUGIN_PATH . 'public/thunderslider-front.class.php');

try{
	$rs_rsl	= (isset($_GET['rs_refresh_server'])) ? true : false;
	$rslb	= new ThunderSliderLoadBalancer();
	$GLOBALS['rslb'] = $rslb;
	$rslb->refresh_server_list($rs_rsl);
	
	//register the revolution slider widget
	ThunderSliderFunctionsMCMS::registerWidget('ThunderSliderWidget');

	//add shortcode
	function thunder_slider_shortcode($args, $mid_content = null){
		
        extract(shortcode_atts(array('alias' => ''), $args, 'thunder_slider'));
		extract(shortcode_atts(array('settings' => ''), $args, 'thunder_slider'));
		extract(shortcode_atts(array('order' => ''), $args, 'thunder_slider'));
		
		if($settings !== '') $settings = json_decode(str_replace(array('({', '})', "'"), array('[', ']', '"'), $settings) ,true);
		if($order !== '') $order = explode(',', $order);
		
        $sliderAlias = ($alias != '') ? $alias : ThunderSliderFunctions::getVal($args,0);
		
		$gal_ids = ThunderSliderFunctionsMCMS::check_for_shortcodes($mid_content); //check for example on gallery shortcode and do stuff
		
		ob_start();
		if(!empty($gal_ids)){ //add a gallery based slider
			$slider = ThunderSliderOutput::putSlider($sliderAlias, '', $gal_ids);
		}else{
			$slider = ThunderSliderOutput::putSlider($sliderAlias, '', array(), $settings, $order);
		}
		$content = ob_get_contents();
		ob_clean();
		ob_end_clean();
		
		if(!empty($slider)){
			// Do not output Slider if we are on mobile
			$disable_on_mobile = $slider->getParam("disable_on_mobile","off");
			if($disable_on_mobile == 'on'){
				$mobile = (strstr($_SERVER['HTTP_USER_AGENT'],'Android') || strstr($_SERVER['HTTP_USER_AGENT'],'webOS') || strstr($_SERVER['HTTP_USER_AGENT'],'iPhone') ||strstr($_SERVER['HTTP_USER_AGENT'],'iPod') || strstr($_SERVER['HTTP_USER_AGENT'],'iPad') || strstr($_SERVER['HTTP_USER_AGENT'],'Windows Phone') || mcms_is_mobile()) ? true : false;
				if($mobile) return false;
			}
			
			$show_alternate = $slider->getParam("show_alternative_type","off");
			
			if($show_alternate == 'mobile' || $show_alternate == 'mobile-ie8'){
				if(strstr($_SERVER['HTTP_USER_AGENT'],'Android') || strstr($_SERVER['HTTP_USER_AGENT'],'webOS') || strstr($_SERVER['HTTP_USER_AGENT'],'iPhone') ||strstr($_SERVER['HTTP_USER_AGENT'],'iPod') || strstr($_SERVER['HTTP_USER_AGENT'],'iPad') || strstr($_SERVER['HTTP_USER_AGENT'],'Windows Phone') || mcms_is_mobile()){
					$show_alternate_image = $slider->getParam("show_alternate_image","");
					return '<img class="tp-slider-alternative-image" src="'.$show_alternate_image.'" data-no-retina>';
				}
			}
		
			//handle slider output types
			$outputType = $slider->getParam("output_type","");
			switch($outputType){
				case "compress":
					$content = str_replace("\n", "", $content);
					$content = str_replace("\r", "", $content);
					return($content);
				break;
				case "echo":
					echo $content; //bypass the filters
				break;
				default:
					return($content);
				break;
			}
		}else
			return($content); //normal output

	}

	add_shortcode( 'thunder_slider', 'thunder_slider_shortcode' );
	
	/**
	 * Call Extensions
	 */
	$revext = new ThunderSliderExtension();
	
	add_action('modules_loaded', array( 'ThunderSliderTinyBox', 'visual_composer_include' )); //VC functionality
	add_action('modules_loaded', array( 'ThunderSliderPageTemplate', 'get_instance' ));
	
	if(is_admin()){ //load admin part
	
		require_once(RS_PLUGIN_PATH . 'includes/framework/update.class.php');
		require_once(RS_PLUGIN_PATH . 'includes/framework/newsletter.class.php');
		require_once(RS_PLUGIN_PATH . 'admin/thunderslider-admin.class.php');

		$productAdmin = new ThunderSliderAdmin(RS_PLUGIN_FILE_PATH);
		
		//add tiny box dropdown menu
		add_action('admin_head', array('ThunderSliderTinyBox', 'add_tinymce_editor'));
		
		
	}else{ //load front part

		/**
		 *
		 * put rev slider on the page.
		 * the data can be slider ID or slider alias.
		 */
		function putThunderSlider($data,$putIn = ""){
			$operations = new ThunderSliderOperations();
			$arrValues = $operations->getGeneralSettingsValues();
			$includesGlobally = ThunderSliderFunctions::getVal($arrValues, "includes_globally","on");
			$strPutIn = ThunderSliderFunctions::getVal($arrValues, "pages_for_includes");
			$isPutIn = ThunderSliderOutput::isPutIn($strPutIn,true);
			if($isPutIn == false && $includesGlobally == "off"){
				$output = new ThunderSliderOutput();
				$option1Name = __("Include ThunderSlider libraries globally (all pages/posts)", 'thunderslider');
				$option2Name = __("Pages to include ThunderSlider libraries", 'thunderslider');
				$output->putErrorMessage(__("If you want to use the PHP function \"putThunderSlider\" in your code please make sure to check \" ",'thunderslider').$option1Name.__(" \" in the backend's \"General Settings\" (top right panel). <br> <br> Or add the current page to the \"",'thunderslider').$option2Name.__("\" option box.", 'thunderslider'));
				return(false);
			}
			
			
			ob_start();
			$slider = ThunderSliderOutput::putSlider($data,$putIn);
			$content = ob_get_contents();
			ob_clean();
			ob_end_clean();
			
			if(is_object($slider)){
				$disable_on_mobile = @$slider->getParam("disable_on_mobile","off"); // Do not output Slider if we are on mobile
				if($disable_on_mobile == 'on'){
					$mobile = (strstr($_SERVER['HTTP_USER_AGENT'],'Android') || strstr($_SERVER['HTTP_USER_AGENT'],'webOS') || strstr($_SERVER['HTTP_USER_AGENT'],'iPhone') ||strstr($_SERVER['HTTP_USER_AGENT'],'iPod') || strstr($_SERVER['HTTP_USER_AGENT'],'Windows Phone') || strstr($_SERVER['HTTP_USER_AGENT'],'iPad') || mcms_is_mobile()) ? true : false;
					if($mobile) return false;
				}
			}
			
			echo $content;
		}


		/**
		 *
		 * put rev slider on the page.
		 * the data can be slider ID or slider alias.
		 */
		function checkThunderSliderExists($alias){
            $rev = new ThunderSlider();
            return $rev->isAliasExists($alias);
		}

		$productFront = new ThunderSliderFront(RS_PLUGIN_FILE_PATH);
	}
	
	add_action('modules_loaded', array( 'ThunderSliderFront', 'createDBTables' )); //add update checks
	add_action('modules_loaded', array( 'ThunderSliderModuleUpdate', 'do_update_checks' )); //add update checks
	
}catch(Exception $e){
	$message = $e->getMessage();
	$trace = $e->getTraceAsString();
	echo _e("RazorLeaf ThunderSlider Error:",'thunderslider')." <b>".$message."</b>";
}

?>