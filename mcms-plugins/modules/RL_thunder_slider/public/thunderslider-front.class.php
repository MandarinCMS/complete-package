<?php
/**
 * @author    MandarinCMS <info@jiiworks.net>
 * @link      http://www.jiiworks.net/
 * @copyright 2015 MandarinCMS
 */
 
if( !defined( 'BASED_TREE_URI') ) exit();

class ThunderSliderFront extends ThunderSliderBaseFront{
	
	/**
	 * 
	 * the constructor
	 */
	public function __construct(){
		
		parent::__construct($this);
		
		//set table names
		ThunderSliderGlobals::$table_sliders = self::$table_prefix.ThunderSliderGlobals::TABLE_SLIDERS_NAME;
		ThunderSliderGlobals::$table_slides = self::$table_prefix.ThunderSliderGlobals::TABLE_SLIDES_NAME;
		ThunderSliderGlobals::$table_static_slides = self::$table_prefix.ThunderSliderGlobals::TABLE_STATIC_SLIDES_NAME;
		ThunderSliderGlobals::$table_settings = self::$table_prefix.ThunderSliderGlobals::TABLE_SETTINGS_NAME;
		ThunderSliderGlobals::$table_css = self::$table_prefix.ThunderSliderGlobals::TABLE_CSS_NAME;
		ThunderSliderGlobals::$table_layer_anims = self::$table_prefix.ThunderSliderGlobals::TABLE_LAYER_ANIMS_NAME;
		ThunderSliderGlobals::$table_navigation = self::$table_prefix.ThunderSliderGlobals::TABLE_NAVIGATION_NAME;
		
		add_filter('punchfonts_modify_url', array('ThunderSliderFront', 'modify_punch_url'));
		
		add_action('mcms_enqueue_scripts', array($this, 'enqueue_styles'));
	}
	
	
	/**
	 * 
	 * a must function. you can not use it, but the function must stay there!
	 */		
	public static function onAddScripts(){
		global $mcms_version;
		
		$slver = apply_filters('thunderslider_remove_version', ThunderSliderGlobals::SLIDER_REVISION);
		
		$style_pre = '';
		$style_post = '';
		if($mcms_version < 3.7){
			$style_pre = '<style type="text/css">';
			$style_post = '</style>';
		}
		
		$operations = new ThunderSliderOperations();
		$arrValues = $operations->getGeneralSettingsValues();
		
		$includesGlobally = ThunderSliderFunctions::getVal($arrValues, "includes_globally","on");
		$includesFooter = ThunderSliderFunctions::getVal($arrValues, "js_to_footer","off");
		$load_all_javascript = ThunderSliderFunctions::getVal($arrValues, "load_all_javascript","off");
		$strPutIn = ThunderSliderFunctions::getVal($arrValues, "pages_for_includes");
		$isPutIn = ThunderSliderOutput::isPutIn($strPutIn,true);
		
		$do_inclusion = apply_filters('thunderslider_include_libraries', false);
		
		//put the includes only on pages with active widget or shortcode
		// if the put in match, then include them always (ignore this if)			
		if($isPutIn == false && $includesGlobally == "off" && $do_inclusion == false){
			$isWidgetActive = is_active_widget( false, false, "rev-slider-widget", true );
			$hasShortcode = ThunderSliderFunctionsMCMS::hasShortcode("thunder_slider");
			
			if($isWidgetActive == false && $hasShortcode == false)
				return(false);
		}
		
		mcms_enqueue_style('rs-module-settings', RS_PLUGIN_URL .'public/assets/css/settings.css', array(), $slver);
		
		$custom_css = ThunderSliderOperations::getStaticCss();
		$custom_css = ThunderSliderCssParser::compress_css($custom_css);
		
		if(trim($custom_css) == '') $custom_css = '#rs-demo-id {}';
		
		mcms_add_inline_style( 'rs-module-settings', $style_pre.$custom_css.$style_post );
		
		$setBase = (is_ssl()) ? "https://" : "http://";
		
		mcms_enqueue_script(array('jquery'));
		
		$waitfor = array('jquery');
		
		$enable_logs = ThunderSliderFunctions::getVal($arrValues, "enable_logs",'off');
		if($enable_logs == 'on'){
			mcms_enqueue_script('enable-logs', RS_PLUGIN_URL .'public/assets/js/jquery.myskinpunch.enablelog.js', $waitfor, $slver);
			$waitfor[] = 'enable-logs';
		}
		
		
		$ft = ($includesFooter == "on") ? true : false;
		
		mcms_enqueue_script('tp-tools', RS_PLUGIN_URL .'public/assets/js/jquery.myskinpunch.tools.min.js', $waitfor, $slver, $ft);
		mcms_enqueue_script('revmin', RS_PLUGIN_URL .'public/assets/js/jquery.myskinpunch.revolution.min.js', 'tp-tools', $slver, $ft);
		
		
		if($load_all_javascript !== 'off'){ //if on, load all libraries instead of dynamically loading them
			mcms_enqueue_script('revmin-actions', RS_PLUGIN_URL .'public/assets/js/extensions/revolution.extension.actions.min.js', 'tp-tools', $slver, $ft);
			mcms_enqueue_script('revmin-carousel', RS_PLUGIN_URL .'public/assets/js/extensions/revolution.extension.carousel.min.js', 'tp-tools', $slver, $ft);
			mcms_enqueue_script('revmin-kenburn', RS_PLUGIN_URL .'public/assets/js/extensions/revolution.extension.kenburn.min.js', 'tp-tools', $slver, $ft);
			mcms_enqueue_script('revmin-layeranimation', RS_PLUGIN_URL .'public/assets/js/extensions/revolution.extension.layeranimation.min.js', 'tp-tools', $slver, $ft);
			mcms_enqueue_script('revmin-migration', RS_PLUGIN_URL .'public/assets/js/extensions/revolution.extension.migration.min.js', 'tp-tools', $slver, $ft);
			mcms_enqueue_script('revmin-navigation', RS_PLUGIN_URL .'public/assets/js/extensions/revolution.extension.navigation.min.js', 'tp-tools', $slver, $ft);
			mcms_enqueue_script('revmin-parallax', RS_PLUGIN_URL .'public/assets/js/extensions/revolution.extension.parallax.min.js', 'tp-tools', $slver, $ft);
			mcms_enqueue_script('revmin-slideanims', RS_PLUGIN_URL .'public/assets/js/extensions/revolution.extension.slideanims.min.js', 'tp-tools', $slver, $ft);
			mcms_enqueue_script('revmin-video', RS_PLUGIN_URL .'public/assets/js/extensions/revolution.extension.video.min.js', 'tp-tools', $slver, $ft);
		}
		
		add_action('mcms_head', array('ThunderSliderFront', 'add_meta_generator'));
		add_action('mcms_head', array('ThunderSliderFront', 'add_setREVStartSize'), 99);
		add_action('admin_head', array('ThunderSliderFront', 'add_setREVStartSize'), 99);
		add_action("mcms_footer", array('ThunderSliderFront',"load_icon_fonts") );
		
		// Async JS Loading
		$js_defer = ThunderSliderBase::getVar($arrValues, 'js_defer', 'off');
		if($js_defer!='off') add_filter('clean_url', array('ThunderSliderFront', 'add_defer_forscript'), 11, 1);
		
		add_action('mcms_before_admin_bar_render', array('ThunderSliderFront', 'add_admin_menu_nodes'));
		add_action('mcms_footer', array('ThunderSliderFront', 'putAdminBarMenus'), 99);
		
	}
	
	/**
	 * add admin menu points in ToolBar Top
	 * @since: 5.0.5
	 */
	public static function putAdminBarMenus () {
		if(!is_super_admin() || !is_admin_bar_showing()) return;
		
		?>
		<script>	
			jQuery(document).ready(function() {			
				
				if (jQuery('#mcms-admin-bar-thunderslider-default').length>0 && jQuery('.thunder_slider_wrapper').length>0) {
					var aliases = new Array();
					jQuery('.thunder_slider_wrapper').each(function() {
						aliases.push(jQuery(this).data('alias'));
					});								
					if(aliases.length>0)
						jQuery('#mcms-admin-bar-thunderslider-default li').each(function() {
							var li = jQuery(this),
								t = jQuery.trim(li.find('.ab-item .rs-label').data('alias')); //text()
								
							if (jQuery.inArray(t,aliases)!=-1) {
							} else {
								li.remove();
							}
						});
				} else {
					jQuery('#mcms-admin-bar-thunderslider').remove();
				}
			});
		</script>
		<?php 	
	}
	
	/**
	 * add admin nodes
	 * @since: 5.0.5
	 */
	public static function add_admin_menu_nodes(){
		if(!is_super_admin() || !is_admin_bar_showing()) return;
		
		self::_add_node('<span class="rs-label">RazorLeaf ThunderSlider</span>', false, admin_url('admin.php?page=thunderslider'), array('class' => 'thunderslider-menu' ), 'thunderslider'); //<span class="mcms-menu-image dashicons-before dashicons-update"></span>
		
		//add all nodes of all Slider
		$sl = new ThunderSliderSlider();
		$sliders = $sl->getAllSliderForAdminMenu();
		
		if(!empty($sliders)){
			foreach($sliders as $id => $slider){
				self::_add_node('<span class="rs-label" data-alias="'.esc_attr($slider['alias']).'">'.esc_html($slider['title']).'</span>', 'thunderslider', admin_url('admin.php?page=thunderslider&view=slide&id=new&slider='.intval($id)), array('class' => 'thunderslider-sub-menu' ), esc_attr($slider['alias'])); //<span class="mcms-menu-image dashicons-before dashicons-update"></span>
			}
		}
		
	}
	
	
	/**
	 * add admin node
	 * @since: 5.0.5
	 */
	public static function _add_node($title, $parent = false, $href = '', $custom_meta = array(), $id = ''){
		global $mcms_admin_bar;
		
		if(!is_super_admin() || !is_admin_bar_showing()) return;
		
		if($id == '') $id = strtolower(str_replace(' ', '-', $title));

		// links from the current host will open in the current window
		$meta = strpos( $href, site_url() ) !== false ? array() : array( 'target' => '_blank' ); // external links open in new tab/window
		$meta = array_merge( $meta, $custom_meta );

		$mcms_admin_bar->add_node(array(
			'parent' => $parent,
			'id'     => $id,
			'title'  => $title,
			'href'   => $href,
			'meta'   => $meta,
		));
	}
	
	
	/**
	 *
	 * create db tables
	 */
	public static function createDBTables(){
		if(get_option('thunderslider_change_database', false) || get_option('rs_tables_created', false) === false){
			self::createTable(ThunderSliderGlobals::TABLE_SLIDERS_NAME);
			self::createTable(ThunderSliderGlobals::TABLE_SLIDES_NAME);
			self::createTable(ThunderSliderGlobals::TABLE_STATIC_SLIDES_NAME);
			self::createTable(ThunderSliderGlobals::TABLE_CSS_NAME);
			self::createTable(ThunderSliderGlobals::TABLE_LAYER_ANIMS_NAME);
			self::createTable(ThunderSliderGlobals::TABLE_NAVIGATION_NAME);
		}
		update_option('rs_tables_created', true);
		update_option('thunderslider_change_database', false);
		
		self::updateTables();
	}
	
	public static function load_icon_fonts(){
		global $fa_icon_var,$pe_7s_var;
		if($fa_icon_var) echo "<link rel='stylesheet' property='stylesheet' id='rs-icon-set-fa-icon-css'  href='" . RS_PLUGIN_URL . "public/assets/fonts/font-awesome/css/font-awesome.css' type='text/css' media='all' />";
		if($pe_7s_var) echo "<link rel='stylesheet' property='stylesheet' id='rs-icon-set-pe-7s-css'  href='" . RS_PLUGIN_URL . "public/assets/fonts/pe-icon-7-stroke/css/pe-icon-7-stroke.css' type='text/css' media='all' />";
	}
	
	public static function updateTables(){
		$cur_ver = get_option('thunderslider_table_version', '1.0.0');
		if(get_option('thunderslider_change_database', false)){
			$cur_ver = '1.0.0';
		}
		
		if(version_compare($cur_ver, '1.0.6', '<')){
			require_once(BASED_TREE_URI . 'mcms-admin/includes/upgrade.php');
			
			$tableName = ThunderSliderGlobals::TABLE_SLIDERS_NAME;
			$sql = "CREATE TABLE " .self::$table_prefix.$tableName ." (
						  id int(9) NOT NULL AUTO_INCREMENT,
						  title tinytext NOT NULL,
						  alias tinytext,
						  params LONGTEXT NOT NULL,
						  settings TEXT NULL,
						  type VARCHAR(191) NOT NULL DEFAULT '',
						  UNIQUE KEY id (id)
						);";
			dbDelta($sql);
			
			$tableName = ThunderSliderGlobals::TABLE_SLIDES_NAME;
			$sql = "CREATE TABLE " .self::$table_prefix.$tableName ." (
						  id int(9) NOT NULL AUTO_INCREMENT,
						  slider_id int(9) NOT NULL,
						  slide_order int not NULL,
						  params LONGTEXT NOT NULL,
						  layers LONGTEXT NOT NULL,
						  settings TEXT NOT NULL DEFAULT '',
						  UNIQUE KEY id (id)
						);";
			dbDelta($sql);
			
			$tableName = ThunderSliderGlobals::TABLE_STATIC_SLIDES_NAME;
			$sql = "CREATE TABLE " .self::$table_prefix.$tableName ." (
						  id int(9) NOT NULL AUTO_INCREMENT,
						  slider_id int(9) NOT NULL,
						  params LONGTEXT NOT NULL,
						  layers LONGTEXT NOT NULL,
						  settings TEXT NOT NULL,
						  UNIQUE KEY id (id)
						);";
			dbDelta($sql);
			
			$tableName = ThunderSliderGlobals::TABLE_CSS_NAME;
			$sql = "CREATE TABLE " .self::$table_prefix.$tableName ." (
						  id int(9) NOT NULL AUTO_INCREMENT,
						  handle TEXT NOT NULL,
						  settings LONGTEXT,
						  hover LONGTEXT,
						  advanced LONGTEXT,
						  params LONGTEXT NOT NULL,
						  UNIQUE KEY id (id)
						);";
			dbDelta($sql);
			
			$tableName = ThunderSliderGlobals::TABLE_LAYER_ANIMS_NAME;
			$sql = "CREATE TABLE " .self::$table_prefix.$tableName ." (
					  id int(9) NOT NULL AUTO_INCREMENT,
					  settings text NULL,
					  UNIQUE KEY id (id)
					);";
			dbDelta($sql);

			update_option('thunderslider_table_version', '1.0.6');
		}

	}
	
	
	/**
	 * create tables
	 */
	public static function createTable($tableName){
		global $mcmsdb;

		$parseCssToDb = false;

		$checkForTablesOneTime = get_option('thunderslider_checktables', '0');
		
		if($checkForTablesOneTime == '0'){
			update_option('thunderslider_checktables', '1');
			if(ThunderSliderFunctionsMCMS::isDBTableExists(self::$table_prefix.ThunderSliderGlobals::TABLE_CSS_NAME)){ //$mcmsdb->tables( 'global' )
				//check if database is empty
				$result = $mcmsdb->get_row("SELECT COUNT( DISTINCT id ) AS NumberOfEntrys FROM ".self::$table_prefix.ThunderSliderGlobals::TABLE_CSS_NAME);
				if($result->NumberOfEntrys == 0) $parseCssToDb = true;
			}
		}

		if($parseCssToDb){
			$ThunderSliderOperations = new ThunderSliderOperations();
			$ThunderSliderOperations->importCaptionsCssContentArray();
			$ThunderSliderOperations->moveOldCaptionsCss();
		}
		
		if(!get_option('thunderslider_change_database', false)){
			//if table exists - don't create it.
			$tableRealName = self::$table_prefix.$tableName;
			if(ThunderSliderFunctionsMCMS::isDBTableExists($tableRealName))
				return(false);
		}
		
		switch($tableName){
			case ThunderSliderGlobals::TABLE_SLIDERS_NAME:
			$sql = "CREATE TABLE " .self::$table_prefix.$tableName ." (
						  id int(9) NOT NULL AUTO_INCREMENT,
						  title tinytext NOT NULL,
						  alias tinytext,
						  params LONGTEXT NOT NULL,
						  settings TEXT NULL,
						  type VARCHAR(191) NOT NULL DEFAULT '',
						  UNIQUE KEY id (id)
						);";
			break;
			case ThunderSliderGlobals::TABLE_SLIDES_NAME:
				$sql = "CREATE TABLE " .self::$table_prefix.$tableName ." (
							  id int(9) NOT NULL AUTO_INCREMENT,
							  slider_id int(9) NOT NULL,
							  slide_order int not NULL,
							  params LONGTEXT NOT NULL,
							  layers LONGTEXT NOT NULL,
							  settings TEXT NOT NULL DEFAULT '',
							  UNIQUE KEY id (id)
							);";
			break;
			case ThunderSliderGlobals::TABLE_STATIC_SLIDES_NAME:
				$sql = "CREATE TABLE " .self::$table_prefix.$tableName ." (
							  id int(9) NOT NULL AUTO_INCREMENT,
							  slider_id int(9) NOT NULL,
							  params LONGTEXT NOT NULL,
							  layers LONGTEXT NOT NULL,
							  settings TEXT NOT NULL,
							  UNIQUE KEY id (id)
							);";
			break;
			case ThunderSliderGlobals::TABLE_CSS_NAME:
				$sql = "CREATE TABLE " .self::$table_prefix.$tableName ." (
							  id int(9) NOT NULL AUTO_INCREMENT,
							  handle TEXT NOT NULL,
							  settings LONGTEXT,
							  hover LONGTEXT,
							  advanced LONGTEXT,
							  params LONGTEXT NOT NULL,
							  UNIQUE KEY id (id)
							);";
				$parseCssToDb = true;
			break;
			case ThunderSliderGlobals::TABLE_LAYER_ANIMS_NAME:
				$sql = "CREATE TABLE " .self::$table_prefix.$tableName ." (
							  id int(9) NOT NULL AUTO_INCREMENT,
							  handle TEXT NOT NULL,
							  params TEXT NOT NULL,
							  settings TEXT NULL,
							  UNIQUE KEY id (id)
							);";
			break;
			case ThunderSliderGlobals::TABLE_NAVIGATION_NAME:
				$sql = "CREATE TABLE " .self::$table_prefix.$tableName ." (
							  id int(9) NOT NULL AUTO_INCREMENT,
							  name VARCHAR(191) NOT NULL,
							  handle VARCHAR(191) NOT NULL,
							  css LONGTEXT NOT NULL,
							  markup LONGTEXT NOT NULL,
							  settings LONGTEXT NULL,
							  UNIQUE KEY id (id)
							);";
			break;
			default:
				ThunderSliderFunctions::throwError("table: $tableName not found");
			break;
		}
		
		require_once(BASED_TREE_URI . 'mcms-admin/includes/upgrade.php');
		dbDelta($sql);
		
		if(!get_option('thunderslider_change_database', false)){
			if($parseCssToDb){
				$ThunderSliderOperations = new ThunderSliderOperations();
				$ThunderSliderOperations->importCaptionsCssContentArray();
				$ThunderSliderOperations->moveOldCaptionsCss();
			}
		}

	}
	
	
	
	public function enqueue_styles(){
		
	}
	
	
	/**
	 * Change FontURL to new URL (added for chinese support since google is blocked there)
	 * @since: 5.0
	 */
	public static function modify_punch_url($url){
		$operations = new ThunderSliderOperations();
		$arrValues = $operations->getGeneralSettingsValues();
		
		$set_diff_font = ThunderSliderFunctions::getVal($arrValues, "change_font_loading",'');
		if($set_diff_font !== ''){
			return $set_diff_font;
		}else{
			return $url;
		}
	}
	
	
	/**
	 * Add Meta Generator Tag in FrontEnd
	 * @since: 5.0
	 */
	public static function add_meta_generator(){
		global $thunderSliderVersion;
		
		echo apply_filters('thunderslider_meta_generator', '<meta name="generator" content="Powered by RazorLeaf ThunderSlider '.$thunderSliderVersion.' - responsive, Mobile-Friendly Slider Module for MandarinCMS with comfortable drag and drop interface." />'."\n");
	}
	
	
	/**
	 * Add Meta Generator Tag in FrontEnd
	 * @since: 5.4.3
	 */
	public static function add_setREVStartSize(){
		$script = '<script type="text/javascript">';
		$script .= 'function setREVStartSize(e){
				try{ var i=jQuery(window).width(),t=9999,r=0,n=0,l=0,f=0,s=0,h=0;					
					if(e.responsiveLevels&&(jQuery.each(e.responsiveLevels,function(e,f){f>i&&(t=r=f,l=e),i>f&&f>r&&(r=f,n=e)}),t>r&&(l=n)),f=e.gridheight[l]||e.gridheight[0]||e.gridheight,s=e.gridwidth[l]||e.gridwidth[0]||e.gridwidth,h=i/s,h=h>1?1:h,f=Math.round(h*f),"fullscreen"==e.sliderLayout){var u=(e.c.width(),jQuery(window).height());if(void 0!=e.fullScreenOffsetContainer){var c=e.fullScreenOffsetContainer.split(",");if (c) jQuery.each(c,function(e,i){u=jQuery(i).length>0?u-jQuery(i).outerHeight(!0):u}),e.fullScreenOffset.split("%").length>1&&void 0!=e.fullScreenOffset&&e.fullScreenOffset.length>0?u-=jQuery(window).height()*parseInt(e.fullScreenOffset,0)/100:void 0!=e.fullScreenOffset&&e.fullScreenOffset.length>0&&(u-=parseInt(e.fullScreenOffset,0))}f=u}else void 0!=e.minHeight&&f<e.minHeight&&(f=e.minHeight);e.c.closest(".thunder_slider_wrapper").css({height:f})					
				}catch(d){console.log("Failure at Presize of Slider:"+d)}
			};';
		$script .= '</script>'."\n";
		echo apply_filters('thunderslider_add_setREVStartSize', $script);
	}

	/**
	 *
	 * adds async loading
	 * @since: 5.0
	 */
	public static function add_defer_forscript($url){
	    if ( strpos($url, 'myskinpunch.enablelog.js' )===false && strpos($url, 'myskinpunch.revolution.min.js' )===false  && strpos($url, 'myskinpunch.tools.min.js' )===false )
	        return $url;
	    else if (is_admin())
	        return $url;
	    else
	        return $url."' defer='defer"; 
	}
	
}
	
?>