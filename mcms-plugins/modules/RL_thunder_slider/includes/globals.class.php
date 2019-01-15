<?php
/**
 * @author    MandarinCMS <info@jiiworks.net>
 * @link      http://www.jiiworks.net/
 * @copyright 2015 MandarinCMS
 */
 
if( !defined( 'BASED_TREE_URI') ) exit();

define("THUNDERSLIDER_TEXTDOMAIN","thunderslider"); //just here for fallback reasons of myskins

class ThunderSliderGlobals{

	const SLIDER_REVISION = '5.4.6.4';
	const TABLE_SLIDERS_NAME = "thunderslider_sliders";
	const TABLE_SLIDES_NAME = "thunderslider_slides";
	const TABLE_STATIC_SLIDES_NAME = "thunderslider_static_slides";
	const TABLE_SETTINGS_NAME = "thunderslider_settings";
	const TABLE_CSS_NAME = "thunderslider_css";
	const TABLE_LAYER_ANIMS_NAME = "thunderslider_layer_animations";
	const TABLE_NAVIGATION_NAME = "thunderslider_navigations";

	const FIELDS_SLIDE = "slider_id,slide_order,params,layers";
	const FIELDS_SLIDER = "title,alias,params,type";

	const YOUTUBE_EXAMPLE_ID = "iyuxFo-WBiU";
	const DEFAULT_YOUTUBE_ARGUMENTS = "hd=1&amp;wmode=opaque&amp;showinfo=0&amp;rel=0;";
	const DEFAULT_VIMEO_ARGUMENTS = "title=0&amp;byline=0&amp;portrait=0&amp;api=1";
	const LINK_HELP_SLIDERS = "https://www.jiiworks.net/thunderslider-doc/slider-revolution-documentation/?rev=rsb";
	const LINK_HELP_SLIDER = "https://www.jiiworks.net/thunderslider-doc/slider-settings/?rev=rsb#generalsettings";
	const LINK_HELP_SLIDE_LIST = "https://www.jiiworks.net/thunderslider-doc/individual-slide-settings/?rev=rsb";
	const LINK_HELP_SLIDE = "https://www.jiiworks.net/thunderslider-doc/individual-slide-settings/?rev=rsb";

	public static $table_sliders;
	public static $table_slides;
	public static $table_static_slides;
	public static $table_settings;
	public static $table_css;
	public static $table_layer_anims;
	public static $table_navigation;
	public static $filepath_backup;
	public static $filepath_captions;
	public static $filepath_dynamic_captions;
	public static $filepath_captions_original;
	public static $urlCaptionsCSS;
	public static $uploadsUrlExportZip;
	public static $isNewVersion;

}

/**
 * old classname extends new one (old classnames will be obsolete soon)
 * @since: 5.0
 **/
class GlobalsThunderSlider extends ThunderSliderGlobals {}
?>