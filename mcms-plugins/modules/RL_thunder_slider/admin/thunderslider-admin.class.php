<?php
/**
 * @author    MandarinCMS <info@jiiworks.net>
 * @link      http://www.jiiworks.net/
 * @copyright 2015 MandarinCMS
 */

if( !defined( 'BASED_TREE_URI') ) exit();

class ThunderSliderAdmin extends ThunderSliderBaseAdmin{

	const VIEW_SLIDER = "slider";
	const VIEW_SLIDER_TEMPLATE = "slider_template"; //obsolete
	const VIEW_SLIDERS = "sliders";
	const VIEW_SLIDES = "slides";
	const VIEW_SLIDE = "slide";
	
	/**
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

		ThunderSliderGlobals::$filepath_backup = RS_PLUGIN_PATH.'backup/';
		ThunderSliderGlobals::$filepath_captions = RS_PLUGIN_PATH.'public/assets/css/captions.css';
		ThunderSliderGlobals::$urlCaptionsCSS = RS_PLUGIN_URL.'public/assets/css/captions.php';
		ThunderSliderGlobals::$filepath_dynamic_captions = RS_PLUGIN_PATH.'public/assets/css/dynamic-captions.css';
		ThunderSliderGlobals::$filepath_captions_original = RS_PLUGIN_PATH.'public/assets/css/captions-original.css';
		
		$mcms_upload_dir = mcms_upload_dir();
		$mcms_upload_dir = $mcms_upload_dir['basedir'].'/';
		ThunderSliderGlobals::$uploadsUrlExportZip = $mcms_upload_dir.'export.zip';

		$this->init();
	}


	/**
	 * init all actions
	 */
	private function init(){
		global $thunderSliderAsTheme;
		global $pagenow;
		
		$template = new ThunderSliderTemplate();
		$operations = new ThunderSliderOperations();
		$obj_library = new ThunderSliderObjectLibrary();
		$general_settings = $operations->getGeneralSettingsValues();
		
		$role = ThunderSliderBase::getVar($general_settings, 'role', 'admin');
		$force_activation_box = ThunderSliderBase::getVar($general_settings, 'force_activation_box', 'off');
		
		if($force_activation_box == 'on'){ //force the notifications and more
			$thunderSliderAsTheme = false;
		}
		
		self::setMenuRole($role);

		self::addMenuPage('RazorLeaf ThunderSlider', "adminPages");
		
		self::addSubMenuPage(__('Navigation Editor', 'thunderslider'), 'display_module_submenu_page_navigation', 'thunderslider_navigation');
		self::addSubMenuPage(__('Global Settings', 'thunderslider'), 'display_module_submenu_page_global_settings', 'thunderslider_global_settings');
		

		$this->addSliderMetaBox();

		//ajax response to save slider options.
		self::addActionAjax("ajax_action", "onAjaxAction");
		
		$upgrade = new ThunderSliderUpdate( GlobalsThunderSlider::SLIDER_REVISION );
		
		$temp_active = get_option('thunderslider-temp-active', 'false');
		
		if($temp_active == 'true'){ //check once an hour
			$temp_force = (isset($_GET['checktempactivate'])) ? true : false;
			$upgrade->add_temp_active_check($temp_force);
		}
		
		//add common scripts there
		$validated = get_option('thunderslider-valid', 'false');
		$notice = get_option('thunderslider-valid-notice', 'true');
		$latestv = ThunderSliderGlobals::SLIDER_REVISION;
		$stablev = get_option('thunderslider-stable-version', '0');
		
		if(!$thunderSliderAsTheme || version_compare($latestv, $stablev, '<')){
			if($validated === 'false' && $notice === 'true'){
				add_action('admin_notices', array($this, 'addActivateNotification'));
			}

			if(isset($_GET['checkforupdates']) && $_GET['checkforupdates'] == 'true')
				$upgrade->_retrieve_version_info(true);
			
			if($validated === 'true' || version_compare($latestv, $stablev, '<')) {
				$upgrade->add_update_checks();
			}
		}
		
		
		if(isset($_REQUEST['update_shop'])){
			$template->_get_template_list(true);
		}else{
			$template->_get_template_list();
		}
		
		if(isset($_REQUEST['update_object_library'])){
			$obj_library->_get_list(true);
		}else{
			$obj_library->_get_list();
		}
		
		$upgrade->_retrieve_version_info();
		add_action('admin_notices', array($this, 'add_notices'));
		
		add_action('admin_enqueue_scripts', array('ThunderSliderAdmin', 'enqueue_styles'));
		
		add_action('admin_enqueue_scripts', array('ThunderSliderAdmin', 'enqueue_all_admin_scripts'));
		
		add_action('mcms_ajax_thunderslider_ajax_call_front', array('ThunderSliderAdmin', 'onFrontAjaxAction'));
		add_action('mcms_ajax_nopriv_thunderslider_ajax_call_front', array('ThunderSliderAdmin', 'onFrontAjaxAction')); //for not logged in users
		
		add_action( 'admin_head', array('ThunderSliderAdmin', 'include_custom_css' ));
		
		if(isset($pagenow) && $pagenow == 'modules.php'){
			add_action('admin_notices', array('ThunderSliderAdmin', 'add_modules_page_notices'));
		}
		
		// Add-on Admin
		$addon_admin = new Rev_addon_Admin( 'rev_addon', ThunderSliderGlobals::SLIDER_REVISION );
		add_action( 'admin_enqueue_scripts', array( $addon_admin, 'enqueue_styles') );
		add_action( 'admin_enqueue_scripts', array( $addon_admin, 'enqueue_scripts') );
		add_action( 'admin_menu', array( $addon_admin, 'add_module_admin_menu'), 11 );
		// Add-on Admin Button Ajax Actions
		add_action( 'mcms_ajax_activate_module', array( $addon_admin, 'activate_module') );
		//add_action( 'mcms_ajax_nopriv_activate_module', array( $addon_admin, 'activate_module') );
		add_action( 'mcms_ajax_deactivate_module', array( $addon_admin, 'deactivate_module'));
		//add_action( 'mcms_ajax_nopriv_deactivate_module', array( $addon_admin, 'deactivate_module') );
		add_action( 'mcms_ajax_install_module', array( $addon_admin, 'install_module'));
		//add_action( 'mcms_ajax_nopriv_install_module', array( $addon_admin, 'install_module') );
		
		//add_filter('module_action_links', array('ThunderSliderAdmin', 'module_action_links' ), 10, 2);
	}
	
	
	public static function add_modules_page_notices(){
		$modules = get_modules();
        
        foreach($modules as $module_id => $module){
            
            $slug = dirname($module_id);
            if(empty($slug)) continue;
			if($slug !== 'thunderslider') continue;
            
			//check version, latest updates and if registered or not
			$validated = get_option('thunderslider-valid', 'false');
			$latestv = get_option('thunderslider-latest-version', ThunderSliderGlobals::SLIDER_REVISION);
			
			if($validated != 'false'){ //activate for updates and support
				remove_action( "after_module_row_" . $module_id, array('ThunderSliderAdmin', 'show_purchase_notice'), 10, 3);
			}
			
			if(version_compare($latestv, $module['Version'], '>')){
				remove_action( "after_module_row_" . $module_id, array('ThunderSliderAdmin', 'show_update_notice'), 10, 3);
			}
		}   
	}
	
	
	public static function show_purchase_notice(){
		#$mcms_list_table = _get_list_table('MCMS_Modules_List_Table');
        ?>
        <?php 
	}
	
	
	public static function show_update_notice(){
		$mcms_list_table = _get_list_table('MCMS_Modules_List_Table');
        ?>
       
        <?php 
	}
	
	
	public static function module_action_links($links, $file){
		if ($file == module_basename(RS_PLUGIN_FILE_PATH)){
			$rs_enabled = get_option('thunderslider-valid', 'false');
			
			if($rs_enabled == 'true'){
				krsort($links);
				end($links);
				$key = key($links);
				$links[$key] .= '';
			}
		}
		
		return $links;
	}
	
	
	public static function enqueue_styles(){
		mcms_enqueue_style('rs-open-sans', '//fonts.googleapis.com/css?family=Open+Sans:400,300,700,600,800');
		mcms_enqueue_style('thunderslider-global-styles', RS_PLUGIN_URL . 'admin/assets/css/global.css', array(), GlobalsThunderSlider::SLIDER_REVISION );
	}

	
	public static function include_custom_css(){
		
		$type = (isset($_GET['view'])) ? $_GET['view'] : '';
		$page = (isset($_GET['page'])) ? $_GET['page'] : '';
		
		if($page !== 'slider' && $page !== 'thunderslider_navigation') return false; //showbiz fix
		
		$sliderID = '';
		
		switch($type){
			case 'slider':
				
				$sliderID = (isset($_GET['id'])) ? $_GET['id'] : '';
			break;
			case 'slide':
				$slideID = (isset($_GET['id'])) ? $_GET['id'] : '';
				if($slideID == 'new') break;
				
				$slide = new RevSlide();
				$slide->initByID($slideID);
				$sliderID = $slide->getSliderID();
			break;
			default:
				if(isset($_GET['slider'])){
					$sliderID = $_GET['slider'];
				}
			break;
		}

		$arrFieldsParams = array();

		if(!empty($sliderID)){
			$slider = new ThunderSlider();
			$slider->initByID($sliderID);
			$settingsFields = $slider->getSettingsFields();
			$arrFieldsMain = $settingsFields['main'];
			$arrFieldsParams = $settingsFields['params'];			
			$custom_css = @stripslashes($arrFieldsParams['custom_css']);
			$custom_css = ThunderSliderCssParser::compress_css($custom_css);
			echo '<style>'.$custom_css.'</style>';
		}
	}
	
	
	public static function enqueue_all_admin_scripts() {
		mcms_localize_script('unite_admin', 'rev_lang', self::get_javascript_multilanguage()); //Load multilanguage for JavaScript
		
		mcms_enqueue_style(array('mcms-color-picker'));
		mcms_enqueue_script(array('mcms-color-picker'));

		//enqueue TP-COLOR 
		mcms_enqueue_style('tp-color-picker-css', modules_url('../public/assets/css/tp-color-picker.css', __FILE__ ), array(), ThunderSliderGlobals::SLIDER_REVISION);
		mcms_enqueue_script('tp-color-picker-js', modules_url('../public/assets/js/tp-color-picker.min.js', __FILE__ ), array('jquery'), ThunderSliderGlobals::SLIDER_REVISION);
		
		
		//enqueue in all pages / posts in backend
		$screen = get_current_screen();
		
		$post_types = get_post_types( '', 'names' ); 
		foreach($post_types as $post_type) {
			if($post_type == $screen->id){
				mcms_enqueue_script('thunderslider-tinymce-shortcode-script', RS_PLUGIN_URL . 'admin/assets/js/tinymce-shortcode-script.js', array('jquery'), ThunderSliderGlobals::SLIDER_REVISION );
			}
		}
	}
	

	/**
	 * Include wanted submenu page
	 */
	public function display_module_submenu_page_navigation() {
		self::display_module_submenu('navigation-editor');
	}
	

	/**
	 * Include wanted submenu page
	 */
	public function display_module_submenu_page_global_settings() {
		self::display_module_submenu('global-settings');
	}
	

	/**
	 * Include wanted submenu page
	 */
	public function display_module_submenu_page_google_fonts() {
		self::display_module_submenu('myskinpunch-google-fonts');
	}

	
	public static function display_module_submenu($subMenu){

		parent::adminPages();

		self::setMasterView('master-view');
		self::requireView($subMenu);
	}
	
	
	/**
	 * Create Multilanguage for JavaScript
	 */
	protected static function get_javascript_multilanguage(){
		$lang = array(
			'wrong_alias' => __('-- wrong alias -- ', 'thunderslider'),
			'nav_bullet_arrows_to_none' => __('Navigation Bullets and Arrows are now set to none.', 'thunderslider'),
			'create_template' => __('Create Template', 'thunderslider'),
			'really_want_to_delete' => __('Do you really want to delete', 'thunderslider'),
			'sure_to_replace_urls' => __('Are you sure to replace the urls?', 'thunderslider'),
			'set_settings_on_all_slider' => __('Set selected settings on all Slides of this Slider? (This will be saved immediately)', 'thunderslider'),
			'select_slide_img' => __('Select Slide Image', 'thunderslider'),
			'select_layer_img' => __('Select Layer Image', 'thunderslider'),
			'select_slide_video' => __('Select Slide Video', 'thunderslider'),
			'show_slide_opt' => __('Show Slide Options', 'thunderslider'),
			'hide_slide_opt' => __('Hide Slide Options', 'thunderslider'),
			'close' => __('Close', 'thunderslider'),
			'really_update_global_styles' => __('Really update global styles?', 'thunderslider'),
			'really_clear_global_styles' => __('This will remove all Global Styles, continue?', 'thunderslider'),
			'global_styles_editor' => __('Global Styles Editor', 'thunderslider'),
			'select_image' => __('Select Image', 'thunderslider'),
			'video_not_found' => __('No Thumbnail Image Set on Video / Video Not Found / No Valid Video ID', 'thunderslider'),
			'handle_at_least_three_chars' => __('Handle has to be at least three character long', 'thunderslider'),
			'really_change_font_sett' => __('Really change font settings?', 'thunderslider'),
			'really_delete_font' => __('Really delete font?', 'thunderslider'),
			'class_exist_overwrite' => __('Class already exists, overwrite?', 'thunderslider'),
			'class_must_be_valid' => __('Class must be a valid CSS class name', 'thunderslider'),
			'really_overwrite_class' => __('Really overwrite Class?', 'thunderslider'),
			'relly_delete_class' => __('Really delete Class', 'thunderslider'),
			'class_this_cant_be_undone' => __('? This can\'t be undone!', 'thunderslider'),
			'this_class_does_not_exist' => __('This class does not exist.', 'thunderslider'),
			'making_changes_will_probably_overwrite_advanced' => __('Making changes to these settings will probably overwrite advanced settings. Continue?', 'thunderslider'),
			'select_static_layer_image' => __('Select Static Layer Image', 'thunderslider'),
			'select_layer_image' => __('Select Layer Image', 'thunderslider'),
			'really_want_to_delete_all_layer' => __('Do you really want to delete all the layers?', 'thunderslider'),
			'layer_animation_editor' => __('Layer Animation Editor', 'thunderslider'),
			'animation_exists_overwrite' => __('Animation already exists, overwrite?', 'thunderslider'),
			'really_overwrite_animation' => __('Really overwrite animation?', 'thunderslider'),
			'default_animations_cant_delete' => __('Default animations can\'t be deleted', 'thunderslider'),
			'must_be_greater_than_start_time' => __('Must be greater than start time', 'thunderslider'),
			'sel_layer_not_set' => __('Selected layer not set', 'thunderslider'),
			'edit_layer_start' => __('Edit Layer Start', 'thunderslider'),
			'edit_layer_end' => __('Edit Layer End', 'thunderslider'),
			'default_animations_cant_rename' => __('Default Animations can\'t be renamed', 'thunderslider'),
			'anim_name_already_exists' => __('Animationname already existing', 'thunderslider'),
			'css_name_already_exists' => __('CSS classname already existing', 'thunderslider'),
			'css_orig_name_does_not_exists' => __('Original CSS classname not found', 'thunderslider'),
			'enter_correct_class_name' => __('Enter a correct class name', 'thunderslider'),
			'class_not_found' => __('Class not found in database', 'thunderslider'),
			'css_name_does_not_exists' => __('CSS classname not found', 'thunderslider'),
			'delete_this_caption' => __('Delete this caption? This may affect other Slider', 'thunderslider'),
			'this_will_change_the_class' => __('This will update the Class with the current set Style settings, this may affect other Sliders. Proceed?', 'thunderslider'),
			'unsaved_changes_will_not_be_added' => __('Template will have the state of the last save, proceed?', 'thunderslider'),
			'please_enter_a_slide_title' => __('Please enter a Slide title', 'thunderslider'),
			'please_wait_a_moment' => __('Please Wait a Moment', 'thunderslider'),
			'copy_move' => __('Copy / Move', 'thunderslider'),
			'preset_loaded' => __('Preset Loaded', 'thunderslider'),
			'add_bulk_slides' => __('Add Bulk Slides', 'thunderslider'),
			'select_image' => __('Select Image', 'thunderslider'),
			'arrows' => __('Arrows', 'thunderslider'),
			'bullets' => __('Bullets', 'thunderslider'),
			'thumbnails' => __('Thumbnails', 'thunderslider'),
			'tabs' => __('Tabs', 'thunderslider'),
			'delete_navigation' => __('Delete this Navigation?', 'thunderslider'),
			'could_not_update_nav_name' => __('Navigation name could not be updated', 'thunderslider'),
			'name_too_short_sanitize_3' => __('Name too short, at least 3 letters between a-zA-z needed', 'thunderslider'),
			'nav_name_already_exists' => __('Navigation name already exists, please choose a different name', 'thunderslider'),
			'remove_nav_element' => __('Remove current element from Navigation?', 'thunderslider'),
			'create_this_nav_element' => __('This navigation element does not exist, create one?', 'thunderslider'),
			'overwrite_animation' => __('Overwrite current animation?', 'thunderslider'),
			'cant_modify_default_anims' => __('Default animations can\'t be changed', 'thunderslider'),
			'anim_with_handle_exists' => __('Animation already existing with given handle, please choose a different name.', 'thunderslider'),
			'really_delete_anim' => __('Really delete animation:', 'thunderslider'),
			'this_will_reset_navigation' => __('This will reset the navigation, continue?', 'thunderslider'),
			'preset_name_already_exists' => __('Preset name already exists, please choose a different name', 'thunderslider'),
			'delete_preset' => __('Really delete this preset?', 'thunderslider'),
			'update_preset' => __('This will update the preset with the current settings. Proceed?', 'thunderslider'),
			'maybe_wrong_yt_id' => __('No Thumbnail Image Set on Video / Video Not Found / No Valid Video ID', 'thunderslider'),
			'preset_not_found' => __('Preset not found', 'thunderslider'),
			'cover_image_needs_to_be_set' => __('Cover Image need to be set for videos', 'thunderslider'),
			'remove_this_action' => __('Really remove this action?', 'thunderslider'),
			'layer_action_by' => __('Layer is triggered by ', 'thunderslider'),
			'due_to_action' => __(' due to action: ', 'thunderslider'),
			'layer' => __('layer:', 'thunderslider'),
			'start_layer_in' => __('Start Layer "in" animation', 'thunderslider'),
			'start_layer_out' => __('Start Layer "out" animation', 'thunderslider'),
			'start_video' => __('Start Media', 'thunderslider'),
			'stop_video' => __('Stop Media', 'thunderslider'),
			'mute_video' => __('Mute Media', 'thunderslider'),
			'unmute_video' => __('Unmute Media', 'thunderslider'),
			'toggle_layer_anim' => __('Toggle Layer Animation', 'thunderslider'),
			'toggle_video' => __('Toggle Media', 'thunderslider'),
			'toggle_mute_video' => __('Toggle Mute Media', 'thunderslider'),
			'toggle_global_mute_video' => __('Toggle Mute All Media', 'thunderslider'),
			'last_slide' => __('Last Slide', 'thunderslider'),
			'simulate_click' => __('Simulate Click', 'thunderslider'),
			'togglefullscreen' => __('Toggle FullScreen', 'thunderslider'),
			'gofullscreen' => __('Go FullScreen', 'thunderslider'),
			'exitfullscreen' => __('Exit FullScreen', 'thunderslider'),
			'toggle_class' => __('Toogle Class', 'thunderslider'),
			'copy_styles_to_hover_from_idle' => __('Copy hover styles to idle?', 'thunderslider'),
			'copy_styles_to_idle_from_hover' => __('Copy idle styles to hover?', 'thunderslider'),
			'select_at_least_one_device_type' => __('Please select at least one device type', 'thunderslider'),
			'please_select_first_an_existing_style' => __('Please select an existing Style Template', 'thunderslider'),
			'cant_remove_last_transition' => __('Can not remove last transition!', 'thunderslider'),
			'name_is_default_animations_cant_be_changed' => __('Given animation name is a default animation. These can not be changed.', 'thunderslider'),
			'override_animation' => __('Animation exists, override existing animation?', 'thunderslider'),
			'this_feature_only_if_activated' => __('This feature is only available if you activate RazorLeaf ThunderSlider for this installation', 'thunderslider'),
			'unsaved_data_will_be_lost_proceed' => __('Unsaved data will be lost, proceed?', 'thunderslider'),
			'delete_user_slide' => __('This will delete this Slide Template, proceed?', 'thunderslider'),
			'is_loading' => __('is Loading...', 'thunderslider'),
			'google_fonts_loaded' => __('Google Fonts Loaded', 'thunderslider'),
			'delete_layer' => __('Delete Layer?', 'thunderslider'),
			'this_template_requires_version' => __('This template requires at least version', 'thunderslider'),
			'of_slider_revolution' => __('of RazorLeaf ThunderSlider to work.', 'thunderslider'),
			'slider_revolution_shortcode_creator' => __('RazorLeaf ThunderSlider Shortcode Creator', 'thunderslider'),
			'slider_informations_are_missing' => __('Slider informations are missing!', 'thunderslider'),
			'shortcode_generator' => __('Shortcode Generator', 'thunderslider'),
			'please_add_at_least_one_layer' => __('Please add at least one Layer.', 'thunderslider'),
			'choose_image' => __('Choose Image', 'thunderslider'),
			'shortcode_parsing_successfull' => __('Shortcode parsing successfull. Items can be found in step 3', 'thunderslider'),
			'shortcode_could_not_be_correctly_parsed' => __('Shortcode could not be parsed.', 'thunderslider'),
			'background_video' => __('Background Video', 'thunderslider'),
			'active_video' => __('Video in Active Slide', 'thunderslider'),
			'empty_data_retrieved_for_slider' => __('Data could not be fetched for selected Slider', 'thunderslider'),
			'import_selected_layer' => __('Import Selected Layer?', 'thunderslider'),
			'import_all_layer_from_actions' => __('Layer Imported! The Layer has actions which include other Layers. Import all connected layers?', 'thunderslider'),
            'not_available_in_demo' => __('Not available in Demo Mode', 'thunderslider'),
            'leave_not_saved' => __('By leaving now, all changes since the last saving will be lost. Really leave now?', 'thunderslider'),
            'static_layers' => __('--- Static Layers ---', 'thunderslider'),
            'objects_only_available_if_activated' => __('Only available if module is activated', 'thunderslider'),
            'download_install_takes_longer' => __('Download/Install takes longer than usual, please wait', 'thunderslider'),
            'download_failed_check_server' => __('<div class="import_failure">Download/Install seems to have failed.</div><br>Please check your server <span class="import_failure">download speed</span> and  if the server can programatically connect to <span class="import_failure">http://templates.jiiworks.net</span><br><br>', 'thunderslider'),
            'aborting_import' => __('<b>Aborting Import...</b>', 'thunderslider'),
            'create_draft' => __('Creating Draft Page...', 'thunderslider'),
            'draft_created' => __('Draft Page created. Popup will open', 'thunderslider'),
            'draft_not_created' => __('Draft Page could not be created.', 'thunderslider'),
            'slider_import_success_reload' => __('Slider import successful', 'thunderslider'),
            'save_changes' => __('Save Changes?', 'thunderslider')
		);

		return $lang;
	}

	
	public function addActivateNotification(){
		$nonce = mcms_create_nonce("thunderslider_actions");
		?>
		 
		<?php
	}
	
	
	/**
	 * add notices from MandarinCMS
	 * @since: 4.6.8
	 */
	public function add_notices(){ //removed notices
	}
	
	
	/**
	 *
	 * add wildcards metabox variables to posts
	 */
	private function addSliderMetaBox($postTypes = null){ //null = all, post = only posts
		try{
			self::addMetaBox("RazorLeaf ThunderSlider Options",'',array("ThunderSliderAdmin","customPostFieldsOutput"),$postTypes);
		}catch(Exception $e){}
	}


	/**
	 *  custom output function
	 */
	public static function customPostFieldsOutput(){
		
		$meta = get_post_meta(get_the_ID(), 'slide_template', true);
		if($meta == '') $meta = 'default';
		
		$slider = new ThunderSlider();
		$arrOutput = array();
		$arrOutput["default"] = "default";

		$arrSlides = $slider->getArrSlidersWithSlidesShort(ThunderSlider::SLIDER_TYPE_TEMPLATE);
		$arrOutput = $arrOutput + $arrSlides;	//union arrays
		
		?>
		<ul class="thunderslider_settings">
			<li id="slide_template_row">
				<div title="" class="setting_text" id="slide_template_text"><?php _e('Choose Slide Template', 'thunderslider'); ?></div>
				<div class="setting_input">
					<select name="slide_template" id="slide_template">
						<?php
						foreach($arrOutput as $handle => $name){
							echo '<option '.selected($handle, $meta).' value="'.$handle.'">'.$name.'</option>';
						}
						?>
					</select>
				</div>
				<div class="clear"></div>
			</li>
		</ul>
		<?php
	}


	/**
	 * a must function. please don't remove it.
	 * process activate event - install the db (with delta).
	 */
	public static function onActivate(){
		ThunderSliderFront::createDBTables();
	}


	/**
	 * a must function. adds scripts on the page
	 * add all page scripts and styles here.
	 * pelase don't remove this function
	 * common scripts even if the module not load, use this function only if no choise.
	 */
	public static function onAddScripts(){
		global $mcms_version;
		
		$style_pre = '';
		$style_post = '';
		if($mcms_version < 3.7){
			$style_pre = '<style type="text/css">';
			$style_post = '</style>';
		}
		
		mcms_enqueue_style('edit_layers', RS_PLUGIN_URL .'admin/assets/css/edit_layers.css', array(), ThunderSliderGlobals::SLIDER_REVISION);
		
		mcms_enqueue_script('unite_layers_timeline', RS_PLUGIN_URL .'admin/assets/js/edit_layers_timeline.js', array(), ThunderSliderGlobals::SLIDER_REVISION );
		mcms_enqueue_script('unite_context_menu', RS_PLUGIN_URL .'admin/assets/js/context_menu.js', array(), ThunderSliderGlobals::SLIDER_REVISION );
		mcms_enqueue_script('unite_layers', RS_PLUGIN_URL .'admin/assets/js/edit_layers.js', array('jquery-ui-mouse'), ThunderSliderGlobals::SLIDER_REVISION );
		mcms_enqueue_script('unite_css_editor', RS_PLUGIN_URL .'admin/assets/js/css_editor.js', array(), ThunderSliderGlobals::SLIDER_REVISION );
		mcms_enqueue_script('rev_admin', RS_PLUGIN_URL .'admin/assets/js/rev_admin.js', array(), ThunderSliderGlobals::SLIDER_REVISION );
		
		mcms_enqueue_script('tp-tools', RS_PLUGIN_URL .'public/assets/js/jquery.myskinpunch.tools.min.js', array(), ThunderSliderGlobals::SLIDER_REVISION );

		//include all media upload scripts
		self::addMediaUploadIncludes();

		//add rs css:
		mcms_enqueue_style('rs-module-settings', RS_PLUGIN_URL .'public/assets/css/settings.css', array(), ThunderSliderGlobals::SLIDER_REVISION);
		
		//add icon sets
		mcms_enqueue_style('rs-icon-set-fa-icon-', RS_PLUGIN_URL .'public/assets/fonts/font-awesome/css/font-awesome.css', array(), ThunderSliderGlobals::SLIDER_REVISION);
		mcms_enqueue_style('rs-icon-set-pe-7s-', RS_PLUGIN_URL .'public/assets/fonts/pe-icon-7-stroke/css/pe-icon-7-stroke.css', array(), ThunderSliderGlobals::SLIDER_REVISION);
		
		add_filter('thunderslider_mod_icon_sets', array('ThunderSliderBase', 'set_icon_sets'));
		
		$db = new ThunderSliderDB();

		$styles = $db->fetch(ThunderSliderGlobals::$table_css);
		$styles = ThunderSliderCssParser::parseDbArrayToCss($styles, "\n");
		$styles = ThunderSliderCssParser::compress_css($styles);
		mcms_add_inline_style( 'rs-module-settings', $style_pre.$styles.$style_post );

		$custom_css = ThunderSliderOperations::getStaticCss();
		$custom_css = ThunderSliderCssParser::compress_css($custom_css);
		mcms_add_inline_style( 'rs-module-settings', $style_pre.$custom_css.$style_post );
		
	}


	/**
	 *
	 * admin main page function.
	 */
	public static function adminPages(){

		parent::adminPages();

		self::setMasterView('master-view');
		self::requireView(self::$view);
		
	}
	

	/**
	 *
	 * import slideer handle (not ajax response)
	 */
	private static function importSliderHandle($viewBack = null, $updateAnim = true, $updateStatic = true, $updateNavigation = true){

		$slider = new ThunderSlider();
		$response = $slider->importSliderFromPost($updateAnim, $updateStatic, false, false, false, $updateNavigation);
		
		$sliderID = intval($response["sliderID"]);

		if(empty($viewBack)){
			$viewBack = self::getViewUrl(self::VIEW_SLIDER,"id=".$sliderID);
			if(empty($sliderID))
				$viewBack = self::getViewUrl(self::VIEW_SLIDERS);
		}
		
		//handle error this
		if($response["success"] == false){
			$message = $response["error"];
			ThunderSliderOperations::import_failed_message($message, $viewBack);
			
		}else{	//handle success, js redirect.
			//check here to create a page or not
			if(!empty($sliderID)){
				$page_id = 0;
				$page_creation = esc_attr(ThunderSliderFunctions::getPostVariable('page-creation'));
				if($page_creation === 'true'){
					$operations = new ThunderSliderOperations();
					$page_id = $operations->create_slider_page((array)$sliderID);
				}
				if($page_id > 0){
					echo '<script>window.open("'.get_permalink($page_id).'", "_blank");</script>';
				}
			}
			
			echo "<script>
			location.href='".$viewBack."';
			</script>";
		}
		exit();
	}
	
	
	/**
	 * import slider from TP servers
	 * @since: 5.0.5
	 */
	private static function importSliderOnlineTemplateHandleNew($data, $viewBack = null, $updateAnim = true, $updateStatic = true, $single_slide = false){
		
		$return = array('error' => array(), 'success' => array(), 'open' => false, 'view' => $viewBack);
		
		$uid = esc_attr($data['uid']);
		
		$added = array();
		
		if($uid == ''){
			$return['error'][] = __("ID missing, something went wrong. Please try again!", 'thunderslider');
		}else{
			$tmp = new ThunderSliderTemplate();
			
			$package = esc_attr($data['package']);
			$package = ($package == 'true') ? true : false;
			
			//get all in the same package as the uid
			if($package === true){
				$uids = $tmp->get_package_uids($uid);
			}else{
				$uids = (array)$uid;
			}
			
			if(!empty($uids)){
				foreach($uids as $uid){
					set_time_limit(60); //reset the time limit
			
					$filepath = $tmp->_download_template($uid); //can be single or multiple, depending on $package beeing false or true
					
					//send request to TP server and download file
					if(is_array($filepath) && isset($filepath['error'])){
						$return['error'][] = $filepath['error'];
						break;
					}
					
					if($filepath !== false){
						//check if Slider Template was already imported. If yes, remove the old Slider Template as we now do an "update" (in reality we delete and insert again)
						//get all template sliders
						$tmp_slider = $tmp->getMandarinCMSTemplateSliders();
						foreach($tmp_slider as $tslider){
							if(isset($tslider['uid']) && $uid == $tslider['uid']){
								if(!isset($tslider['installed'])){ //slider is installed
									//delete template Slider!
									$mSlider = new ThunderSlider();
									$mSlider->initByID($tslider['id']);
									
									$mSlider->deleteSlider();
									//remove the update flag from the slider
									
									$tmp->remove_is_new($uid);
								}
								break;
							}
						}
						
						$slider = new ThunderSlider();
						$response = $slider->importSliderFromPost($updateAnim, $updateStatic, $filepath, $uid, $single_slide);
						
						$tmp->_delete_template($uid);
						
						if($single_slide === false){
							if(empty($viewBack)){
								$sliderID = $response["sliderID"];
								$viewBack = self::getViewUrl(self::VIEW_SLIDER,"id=".$sliderID);
								$return['view'] = $viewBack;
								if(empty($sliderID)){
									$viewBack = self::getViewUrl(self::VIEW_SLIDERS);
									$return['view'] = $viewBack;
								}
							}
						}
						
						if(isset($response["sliderID"])){
							$return['slider_id'] = $response["sliderID"];
							$added[] = $response["sliderID"];
						}
						//handle error
						if($response["success"] == false){
							$return['error'][] = $response["error"];
							break;
						}else{	//handle success, js redirect.
							$return['success'][] = __("Slider Import Success", 'thunderslider');
						}
						
					}else{
						if(is_array($filepath)){
							$return['error'][] = $filepath['error'];
						}else{
							$return['error'][] = __("Could not download from server. Please try again later!", 'thunderslider');
						}
						break;
					}
				}
				
				//check here to create a page or not
				if(!empty($added)){
					$page_creation = esc_attr($data['page-creation']);
					if($page_creation === 'true'){
						$operations = new ThunderSliderOperations();
						$page_id = $operations->create_slider_page($added);
					}
					if($page_id > 0){
						$return['open'] = get_permalink($page_id);
					}
				}
			}else{
				$return['error'][] = __("Could not download package. Please try again later!", 'thunderslider');
			}
		}
		
		return $return;
	}
	
	
	/**
	 * import slider from TP servers
	 * @since: 5.0.5
	 */
	private static function importSliderOnlineTemplateHandle($data, $viewBack = null, $updateAnim = true, $updateStatic = true, $single_slide = false){
		
		$uid = esc_attr($data['uid']);
		
		$added = array();
		
		if($uid == ''){
			$message = __("ID missing, something went wrong. Please try again!", 'thunderslider');
			ThunderSliderOperations::import_failed_message($message, $viewBack);
			exit;
		}else{
			$tmp = new ThunderSliderTemplate();
			
			$package = esc_attr($data['package']);
			$package = ($package == 'true') ? true : false;
			
			//get all in the same package as the uid
			if($package === true){
				$uids = $tmp->get_package_uids($uid);
			}else{
				$uids = (array)$uid;
			}
			
			if(!empty($uids)){
				foreach($uids as $uid){
					set_time_limit(60); //reset the time limit
			
					$filepath = $tmp->_download_template($uid); //can be single or multiple, depending on $package beeing false or true
					//var_dump($filepath);
					//exit;
					//send request to TP server and download file
					if(is_array($filepath) && isset($filepath['error'])){
						$message = $filepath['error'];
						ThunderSliderOperations::import_failed_message($message, $viewBack);
						exit;
					}
					
					if($filepath !== false){
						//check if Slider Template was already imported. If yes, remove the old Slider Template as we now do an "update" (in reality we delete and insert again)
						//get all template sliders
						$tmp_slider = $tmp->getMandarinCMSTemplateSliders();
						
						foreach($tmp_slider as $tslider){
							if(isset($tslider['uid']) && $uid == $tslider['uid']){
								if(!isset($tslider['installed'])){ //slider is installed
									//delete template Slider!
									$mSlider = new ThunderSlider();
									$mSlider->initByID($tslider['id']);
									
									$mSlider->deleteSlider();
									//remove the update flag from the slider
									
									$tmp->remove_is_new($uid);
								}
								break;
							}
						}
						
						
						$slider = new ThunderSlider();
						$response = $slider->importSliderFromPost($updateAnim, $updateStatic, $filepath, $uid, $single_slide);
						
						$tmp->_delete_template($uid);
						
						if($single_slide === false){
							if(empty($viewBack)){
								$sliderID = $response["sliderID"];
								$viewBack = self::getViewUrl(self::VIEW_SLIDER,"id=".$sliderID);
								if(empty($sliderID))
									$viewBack = self::getViewUrl(self::VIEW_SLIDERS);
							}
						}
						
						if(isset($response["sliderID"])){
							$added[] = $response["sliderID"];
						}
						//handle error
						if($response["success"] == false){
							$message = $response["error"];
							ThunderSliderOperations::import_failed_message($message, $viewBack);
						}else{	//handle success, js redirect.
						
						}
						
					}else{
						if(is_array($filepath)){
							$message = $filepath['error'];
						}else{
							$message = __("Could not download from server. Please try again later!", 'thunderslider');
						}
						ThunderSliderOperations::import_failed_message($message, $viewBack);
						exit;
					}
				}
				
				//check here to create a page or not
				if(!empty($added)){
					$page_creation = esc_attr($data['page-creation']);
					if($page_creation === 'true'){
						$operations = new ThunderSliderOperations();
						$page_id = $operations->create_slider_page($added);
					}
					if($page_id > 0){
						echo '<script>window.open("'.get_permalink($page_id).'", "_blank");</script>';
					}
				}
				
				echo "<script>location.href='".$viewBack."';</script>";
			}else{
				$message = __("Could not download package. Please try again later!", 'thunderslider');
				ThunderSliderOperations::import_failed_message($message, $viewBack);
				exit;
			}
		}
		
		exit;
	}
	
	
	/**
	 *
	 * import slider handle (not ajax response)
	 */
	private static function importSliderTemplateHandle($viewBack = null, $updateAnim = true, $updateStatic = true, $single_slide = false){
		
		$uid = esc_attr(ThunderSliderFunctions::getPostVariable('uid'));
		if($uid == ''){
			$message = __("ID missing, something went wrong. Please try again!", 'thunderslider');
			ThunderSliderOperations::import_failed_message($message, $viewBack);
			exit;
		}
		
		//check if the filename is correct
		//import to templates, then duplicate Slider
		
		$slider = new ThunderSlider();
		$response = $slider->importSliderFromPost($updateAnim, $updateStatic, false, $uid, $single_slide);
		
		if($single_slide === false){
			$sliderID = $response["sliderID"];
			if(empty($viewBack)){
				$viewBack = self::getViewUrl(self::VIEW_SLIDER,"id=".$sliderID);
				if(empty($sliderID))
					$viewBack = self::getViewUrl(self::VIEW_SLIDERS);
			}
		}

		//handle error
		if($response["success"] == false){
			$message = $response["error"];
			ThunderSliderOperations::import_failed_message($message, $viewBack);
		}else{	//handle success, js redirect.
			//check here to create a page or not
			if(isset($sliderID) && !empty($sliderID)){
				$page_creation = esc_attr(ThunderSliderFunctions::getPostVariable('page-creation'));
				if($page_creation === 'true'){
					$operations = new ThunderSliderOperations();
					$page_id = $operations->create_slider_page((array)$sliderID);
				}
				if($page_id > 0){
					echo '<script>window.open("'.get_permalink($page_id).'", "_blank");</script>';
				}
			}
			
			echo "<script>location.href='".$viewBack."';</script>";
		}
		
		exit();
	}

	/**
	 * Get url to secific view.
	 */
	public static function getFontsUrl(){

		$link = admin_url('admin.php?page=myskinpunch-google-fonts');
		return($link);
	}
	
	
	/**
	 * Toggle Favorite State of Slider
	 * @since: 5.0
	 */
	public static function toggle_favorite_by_id($id){
		$id = intval($id);
		if($id === 0) return false;
		
		global $mcmsdb;
		
		$table_name = $mcmsdb->prefix . ThunderSliderGlobals::TABLE_SLIDERS_NAME;
		
		//check if ID exists
		$slider = $mcmsdb->get_row($mcmsdb->prepare("SELECT settings FROM $table_name WHERE id = %s", $id), ARRAY_A);
		
		if(empty($slider))
			return __('Slider not found', 'thunderslider');
			
		$settings = json_decode($slider['settings'], true);
		
		if(!isset($settings['favorite']) || $settings['favorite'] == 'false' || $settings['favorite'] == false){
			$settings['favorite'] = 'true';
		}else{
			$settings['favorite'] = 'false';
		}
		
		$response = $mcmsdb->update($table_name, array('settings' => json_encode($settings)), array('id' => $id));
		
		if($response === false) return __('Slider setting could not be changed', 'thunderslider');
		
		return true;
	}

	/**
	 *
	 * onAjax action handler
	 */
	public static function onAjaxAction(){
		
		$role = self::getMenuRole(); //add additional security check and allow for example import only for admin
		
		$slider = new ThunderSlider();
		$slide = new RevSlide();
		$operations = new ThunderSliderOperations();

		$action = self::getPostGetVar("client_action");
		$data = self::getPostGetVar("data");
		if($data == '') $data = array();
		$nonce = self::getPostGetVar("nonce");
		if(empty($nonce))
			$nonce = self::getPostGetVar("rs-nonce");
		
		try{
			
			if(RS_DEMO){
				switch($action){
					case 'import_slider_online_template_slidersview':
					case 'duplicate_slider':
					case 'preview_slider':
					case 'get_static_css':
					case 'get_dynamic_css':
					case 'preview_slide':
						//these are all okay in demo mode
					break;
					default:
						ThunderSliderFunctions::throwError(__('Function Not Available in Demo Mode', 'thunderslider'));
						exit;
					break;
				}
			}
			
			if(!ThunderSliderFunctionsMCMS::isAdminUser() && apply_filters('thunderslider_restrict_role', true)){
				switch($action){
					case 'change_specific_navigation':
					case 'change_navigations':
					case 'update_static_css':
					case 'add_new_preset':
					case 'update_preset':
					case 'import_slider':
					case 'import_slider_slidersview':
					case 'import_slider_template_slidersview':
					case 'import_slide_template_slidersview':
					case 'import_slider_online_template_slidersview_new':
					case 'fix_database_issues':
						ThunderSliderFunctions::throwError(__('Function Only Available for Adminstrators', 'thunderslider'));
						exit;
					break;
					default:
						$return = apply_filters('thunderslider_admin_onAjaxAction_user_restriction', true, $action, $data, $slider, $slide, $operations);
						if($return !== true){
							ThunderSliderFunctions::throwError(__('Function Only Available for Adminstrators', 'thunderslider'));
							exit;
						}
					break;
				}
			}
			
			//verify the nonce
			$isVerified = mcms_verify_nonce($nonce, "thunderslider_actions");

			if($isVerified == false){
				ThunderSliderFunctions::throwError("Wrong request");
				exit;
			}
			switch($action){
				case 'add_new_preset':
					
					if(!isset($data['settings']) || !isset($data['values'])) self::ajaxResponseError(__('Missing values to add preset', 'thunderslider'), false);
					
					$result = $operations->add_preset_setting($data);
					
					if($result === true){
						
						$presets = $operations->get_preset_settings();
						
						self::ajaxResponseSuccess(__('Preset created', 'thunderslider'), array('data' => $presets));
					}else{
						self::ajaxResponseError($result, false);
					}
					
					exit;
				break;
				case 'update_preset':
					if(!isset($data['name']) || !isset($data['values'])) self::ajaxResponseError(__('Missing values to update preset', 'thunderslider'), false);
					
					$result = $operations->update_preset_setting($data);
					
					if($result === true){
						
						$presets = $operations->get_preset_settings();
						
						self::ajaxResponseSuccess(__('Preset created', 'thunderslider'), array('data' => $presets));
					}else{
						self::ajaxResponseError($result, false);
					}
					
					exit;
				break;
				case 'remove_preset':
					if(!isset($data['name'])) self::ajaxResponseError(__('Missing values to remove preset', 'thunderslider'), false);
					
					$result = $operations->remove_preset_setting($data);
					
					if($result === true){
						
						$presets = $operations->get_preset_settings();
						
						self::ajaxResponseSuccess(__('Preset deleted', 'thunderslider'), array('data' => $presets));
					}else{
						self::ajaxResponseError($result, false);
					}
					
					exit;
				break;
				case "export_slider":
					$sliderID = self::getGetVar("sliderid");
					$dummy = self::getGetVar("dummy");
					$slider->initByID($sliderID);
					$slider->exportSlider($dummy);
				break;
				case "import_slider":
					$updateAnim = self::getPostGetVar("update_animations");
					$updateNav = self::getPostGetVar("update_navigations");
					//$updateStatic = self::getPostGetVar("update_static_captions");
					$updateStatic = 'none';
					self::importSliderHandle(null, $updateAnim, $updateStatic, $updateNav);
				break;
				case "import_slider_slidersview":
					$viewBack = self::getViewUrl(self::VIEW_SLIDERS);
					$updateAnim = self::getPostGetVar("update_animations");
					$updateNav = self::getPostGetVar("update_navigations");
					//$updateStatic = self::getPostGetVar("update_static_captions");
					$updateStatic = 'none';
					self::importSliderHandle($viewBack, $updateAnim, $updateStatic, $updateNav);
				break;
				case "import_slider_online_template_slidersview":
					$viewBack = self::getViewUrl(self::VIEW_SLIDERS);
					//ob_start();
					$data['uid'] = esc_attr(ThunderSliderFunctions::getPostVariable('uid'));
					$data['page-creation'] = esc_attr(ThunderSliderFunctions::getPostVariable('page-creation'));
					$data['package'] = esc_attr(ThunderSliderFunctions::getPostVariable('package'));
					
					self::importSliderOnlineTemplateHandle($data, $viewBack, 'true', 'none');
					/*$html = ob_get_contents();
					ob_clean();
					ob_end_clean();
					
					self::ajaxResponseData($html);*/
				break;
				case "import_slider_template_slidersview":
					$viewBack = self::getViewUrl(self::VIEW_SLIDERS);
					$updateAnim = self::getPostGetVar("update_animations");
					//$updateStatic = self::getPostGetVar("update_static_captions");
					$updateStatic = 'none';
					self::importSliderTemplateHandle($viewBack, $updateAnim, $updateStatic);
				break;
				case "import_slider_online_template_slidersview_new":
					$viewBack = self::getViewUrl(self::VIEW_SLIDERS);
					$response = self::importSliderOnlineTemplateHandleNew($data, $viewBack, 'true', 'none');
					self::ajaxResponseData($response);
				break;
				case 'create_draft_page':
					$response = array('open' => false);
					
					$page_id = $operations->create_slider_page($data['slider_ids']);
					if($page_id > 0){
						$response['open'] = get_permalink($page_id);
					}
					self::ajaxResponseData($response);
				break;
				case "import_slide_online_template_slidersview":
					$redirect_id = esc_attr(self::getPostGetVar("redirect_id"));
					$viewBack = self::getViewUrl(self::VIEW_SLIDE,"id=$redirect_id");
					$slidenum = intval(self::getPostGetVar("slidenum"));
					$sliderid = intval(self::getPostGetVar("slider_id"));
					
					$data['uid'] = esc_attr(ThunderSliderFunctions::getPostVariable('uid'));
					$data['page-creation'] = esc_attr(ThunderSliderFunctions::getPostVariable('page-creation'));
					$data['package'] = esc_attr(ThunderSliderFunctions::getPostVariable('package'));
					
					self::importSliderOnlineTemplateHandle($data, $viewBack, 'true', 'none', array('slider_id' => $sliderid, 'slide_id' => $slidenum));
				break;
				case "import_slide_template_slidersview":
					$redirect_id = esc_attr(self::getPostGetVar("redirect_id"));
					$viewBack = self::getViewUrl(self::VIEW_SLIDE,"id=$redirect_id");
					$updateAnim = self::getPostGetVar("update_animations");
					//$updateStatic = self::getPostGetVar("update_static_captions");
					$updateStatic = 'none';
					$slidenum = intval(self::getPostGetVar("slidenum"));
					$sliderid = intval(self::getPostGetVar("slider_id"));
					
					self::importSliderTemplateHandle($viewBack, $updateAnim, $updateStatic, array('slider_id' => $sliderid, 'slide_id' => $slidenum));
				break;
				case "create_slider":
					$data = $operations->modifyCustomSliderParams($data);
					$newSliderID = $slider->createSliderFromOptions($data);
					self::ajaxResponseSuccessRedirect(__("Slider created",'thunderslider'), self::getViewUrl(self::VIEW_SLIDE, 'id=new&slider='.esc_attr($newSliderID))); //redirect to slide now

				break;
				case "update_slider":
					$data = $operations->modifyCustomSliderParams($data);
					$slider->updateSliderFromOptions($data);
					self::ajaxResponseSuccess(__("Slider updated",'thunderslider'));
				break;
				case "delete_slider":
				case "delete_slider_stay":

					$isDeleted = $slider->deleteSliderFromData($data);

					if(is_array($isDeleted)){
						$isDeleted = implode(', ', $isDeleted);
						self::ajaxResponseError(__("Template can't be deleted, it is still being used by the following Sliders: ", 'thunderslider').$isDeleted);
					}else{
						if($action == 'delete_slider_stay'){
							self::ajaxResponseSuccess(__("Slider deleted",'thunderslider'));
						}else{
							self::ajaxResponseSuccessRedirect(__("Slider deleted",'thunderslider'), self::getViewUrl(self::VIEW_SLIDERS));
						}
					}
				break;
				case "duplicate_slider":

					$slider->duplicateSliderFromData($data);

					self::ajaxResponseSuccessRedirect(__("Success! Refreshing page...",'thunderslider'), self::getViewUrl(self::VIEW_SLIDERS));
				break;
				case "duplicate_slider_package":

					$ret = $slider->duplicateSliderPackageFromData($data);
					
					if($ret !== true){
						ThunderSliderFunctions::throwError($ret);
					}else{
						self::ajaxResponseSuccessRedirect(__("Success! Refreshing page...",'thunderslider'), self::getViewUrl(self::VIEW_SLIDERS));
					}
				break;
				case "add_slide":
				case "add_bulk_slide":
					$numSlides = $slider->createSlideFromData($data);
					$sliderID = $data["sliderid"];

					if($numSlides == 1){
						$responseText = __("Slide Created",'thunderslider');
					}else{
						$responseText = $numSlides . " ".__("Slides Created",'thunderslider');
					}

					$urlRedirect = self::getViewUrl(self::VIEW_SLIDE,"id=new&slider=$sliderID");
					self::ajaxResponseSuccessRedirect($responseText,$urlRedirect);

				break;
				case "add_slide_fromslideview":
					$slideID = $slider->createSlideFromData($data,true);
					$urlRedirect = self::getViewUrl(self::VIEW_SLIDE,"id=$slideID");
					$responseText = __("Slide Created, redirecting...",'thunderslider');
					self::ajaxResponseSuccessRedirect($responseText,$urlRedirect);
				break;
				case 'copy_slide_to_slider':
					$slideID = (isset($data['redirect_id'])) ? $data['redirect_id'] : -1;
					
					if($slideID === -1) ThunderSliderFunctions::throwError(__('Missing redirect ID!', 'thunderslider'));
					
					$return = $slider->copySlideToSlider($data);
					
					if($return !== true) ThunderSliderFunctions::throwError($return);
					
					$urlRedirect = self::getViewUrl(self::VIEW_SLIDE,"id=$slideID");
					$responseText = __("Slide copied to current Slider, redirecting...",'thunderslider');
					self::ajaxResponseSuccessRedirect($responseText,$urlRedirect);
				break;
				case 'update_slide':
					if(isset($data['obj_favorites'])){
						$obj_favorites = $data['obj_favorites'];
						unset($data['obj_favorites']);
						//save object favourites
						$objlib = new ThunderSliderObjectLibrary();
						$objlib->save_favorites($obj_favorites);
					}
					$slide->updateSlideFromData($data);
					self::ajaxResponseSuccess(__("Slide updated",'thunderslider'));
				break;
				case "update_static_slide":
					if(isset($data['obj_favorites'])){
						$obj_favorites = $data['obj_favorites'];
						unset($data['obj_favorites']);
						//save object favourites
						$objlib = new ThunderSliderObjectLibrary();
						$objlib->save_favorites($obj_favorites);
					}
					$slide->updateStaticSlideFromData($data);
					self::ajaxResponseSuccess(__("Static Global Layers updated",'thunderslider'));
				break;
				case "delete_slide":
				case "delete_slide_stay":
					$isPost = $slide->deleteSlideFromData($data);
					if($isPost)
						$message = __("Post deleted",'thunderslider');
					else
						$message = __("Slide deleted",'thunderslider');

					$sliderID = ThunderSliderFunctions::getVal($data, "sliderID");
					if($action == 'delete_slide_stay'){
						self::ajaxResponseSuccess($message);
					}else{
						self::ajaxResponseSuccessRedirect($message, self::getViewUrl(self::VIEW_SLIDE,"id=new&slider=$sliderID"));
					}
				break;
				case "duplicate_slide":
				case "duplicate_slide_stay":
					$return = $slider->duplicateSlideFromData($data);
					if($action == 'duplicate_slide_stay'){
						self::ajaxResponseSuccess(__("Slide duplicated",'thunderslider'), array('id' => $return[1]));
					}else{
						self::ajaxResponseSuccessRedirect(__("Slide duplicated",'thunderslider'), self::getViewUrl(self::VIEW_SLIDE,"id=new&slider=".$return[0]));
					}
				break;
				case "copy_move_slide":
				case "copy_move_slide_stay":
					$sliderID = $slider->copyMoveSlideFromData($data);
					if($action == 'copy_move_slide_stay'){
						self::ajaxResponseSuccess(__("Success!",'thunderslider'));
					}else{
						self::ajaxResponseSuccessRedirect(__("Success! Refreshing page...",'thunderslider'), self::getViewUrl(self::VIEW_SLIDE,"id=new&slider=$sliderID"));
					}
				break;
				case "add_slide_to_template":
					$template = new ThunderSliderTemplate();
					if(!isset($data['slideID']) || intval($data['slideID']) == 0){
						ThunderSliderFunctions::throwError(__('No valid Slide ID given', 'thunderslider'));
						exit;
					}
					if(!isset($data['title']) || strlen(trim($data['title'])) < 3){
						ThunderSliderFunctions::throwError(__('No valid title given', 'thunderslider'));
						exit;
					}
					if(!isset($data['settings']) || !isset($data['settings']['width']) || !isset($data['settings']['height'])){
						ThunderSliderFunctions::throwError(__('No valid title given', 'thunderslider'));
						exit;
					}
					
					$return = $template->copySlideToTemplates($data['slideID'], $data['title'], $data['settings']);
					
					if($return == false){
						ThunderSliderFunctions::throwError(__('Could not save Slide as Template', 'thunderslider'));
						exit;
					}
					
					//get HTML for template section
					ob_start();
					
					$rs_disable_template_script = true; //disable the script output of template selector file
					
					include(RS_PLUGIN_PATH.'admin/views/templates/template-selector.php');
					
					$html = ob_get_contents();
					
					ob_clean();
					ob_end_clean();
					
					self::ajaxResponseSuccess(__('Slide added to Templates', 'thunderslider'),array('HTML' => $html));
					exit;
				break;
				case "get_slider_custom_css_js":
					$slider_css = '';
					$slider_js = '';
					if(isset($data['slider_id']) && intval($data['slider_id']) > 0){
						$slider->initByID(intval($data['slider_id']));
						$slider_css = stripslashes($slider->getParam('custom_css', ''));
						$slider_js = stripslashes($slider->getParam('custom_javascript', ''));
					}
					self::ajaxResponseData(array('css' => $slider_css, 'js' => $slider_js));
				break;
				case "update_slider_custom_css_js":
					if(isset($data['slider_id']) && intval($data['slider_id']) > 0){
						$slider->initByID(intval($data['slider_id']));
						$slider->updateParam(array('custom_css' => $data['css']));
						$slider->updateParam(array('custom_javascript' => $data['js']));
					}
					self::ajaxResponseSuccess(__('Slider CSS saved', 'thunderslider'));
					exit;
				break;
				case "get_static_css":
					$contentCSS = $operations->getStaticCss();
					self::ajaxResponseData($contentCSS);
				break;
				case "get_dynamic_css":
					$contentCSS = $operations->getDynamicCss();
					self::ajaxResponseData($contentCSS);
				break;
				case "insert_captions_css":
					
					$arrCaptions = $operations->insertCaptionsContentData($data);
					
					if($arrCaptions !== false){
						$db = new ThunderSliderDB();
						$styles = $db->fetch(ThunderSliderGlobals::$table_css);
						$styles = ThunderSliderCssParser::parseDbArrayToCss($styles, "\n");
						$styles = ThunderSliderCssParser::compress_css($styles);
						$custom_css = ThunderSliderOperations::getStaticCss();
						$custom_css = ThunderSliderCssParser::compress_css($custom_css);
						
						$arrCSS = $operations->getCaptionsContentArray();
						$arrCssStyles = ThunderSliderFunctions::jsonEncodeForClientSide($arrCSS);
						$arrCssStyles = $arrCSS;
						
						self::ajaxResponseSuccess(__("CSS saved",'thunderslider'),array("arrCaptions"=>$arrCaptions,'compressed_css'=>$styles.$custom_css,'initstyles'=>$arrCssStyles));
					}
					
					ThunderSliderFunctions::throwError(__('CSS could not be saved', 'thunderslider'));
					exit();
				break;
				case "update_captions_css":
					$arrCaptions = $operations->updateCaptionsContentData($data);
					
					//now check all layers of all sliders and check if you need to change them (only if all values are default)
					
					
					if($arrCaptions !== false){
						$db = new ThunderSliderDB();
						$styles = $db->fetch(ThunderSliderGlobals::$table_css);
						$styles = ThunderSliderCssParser::parseDbArrayToCss($styles, "\n");
						$styles = ThunderSliderCssParser::compress_css($styles);
						$custom_css = ThunderSliderOperations::getStaticCss();
						$custom_css = ThunderSliderCssParser::compress_css($custom_css);
						
						$arrCSS = $operations->getCaptionsContentArray();
						$arrCssStyles = ThunderSliderFunctions::jsonEncodeForClientSide($arrCSS);
						$arrCssStyles = $arrCSS;
						
						self::ajaxResponseSuccess(__("CSS saved",'thunderslider'),array("arrCaptions"=>$arrCaptions,'compressed_css'=>$styles.$custom_css,'initstyles'=>$arrCssStyles));
					}
					
					ThunderSliderFunctions::throwError(__('CSS could not be saved', 'thunderslider'));
					exit();
				break;
				case "update_captions_advanced_css":
					
					$arrCaptions = $operations->updateAdvancedCssData($data);
					if($arrCaptions !== false){
						$db = new ThunderSliderDB();
						$styles = $db->fetch(ThunderSliderGlobals::$table_css);
						$styles = ThunderSliderCssParser::parseDbArrayToCss($styles, "\n");
						$styles = ThunderSliderCssParser::compress_css($styles);
						$custom_css = ThunderSliderOperations::getStaticCss();
						$custom_css = ThunderSliderCssParser::compress_css($custom_css);
						
						$arrCSS = $operations->getCaptionsContentArray();
						$arrCssStyles = ThunderSliderFunctions::jsonEncodeForClientSide($arrCSS);
						$arrCssStyles = $arrCSS;
						
						self::ajaxResponseSuccess(__("CSS saved",'thunderslider'),array("arrCaptions"=>$arrCaptions,'compressed_css'=>$styles.$custom_css,'initstyles'=>$arrCssStyles));
					}
					
					ThunderSliderFunctions::throwError(__('CSS could not be saved', 'thunderslider'));
					exit();
				break;
				case "rename_captions_css":
					//rename all captions in all sliders with new handle if success
					$arrCaptions = $operations->renameCaption($data);
					
					$db = new ThunderSliderDB();
					$styles = $db->fetch(ThunderSliderGlobals::$table_css);
					$styles = ThunderSliderCssParser::parseDbArrayToCss($styles, "\n");
					$styles = ThunderSliderCssParser::compress_css($styles);
					$custom_css = ThunderSliderOperations::getStaticCss();
					$custom_css = ThunderSliderCssParser::compress_css($custom_css);
					
					$arrCSS = $operations->getCaptionsContentArray();
					$arrCssStyles = ThunderSliderFunctions::jsonEncodeForClientSide($arrCSS);
					$arrCssStyles = $arrCSS;
					
					self::ajaxResponseSuccess(__("Class name renamed",'thunderslider'),array("arrCaptions"=>$arrCaptions,'compressed_css'=>$styles.$custom_css,'initstyles'=>$arrCssStyles));
				break;
				case "delete_captions_css":
					$arrCaptions = $operations->deleteCaptionsContentData($data);
					
					$db = new ThunderSliderDB();
					$styles = $db->fetch(ThunderSliderGlobals::$table_css);
					$styles = ThunderSliderCssParser::parseDbArrayToCss($styles, "\n");
					$styles = ThunderSliderCssParser::compress_css($styles);
					$custom_css = ThunderSliderOperations::getStaticCss();
					$custom_css = ThunderSliderCssParser::compress_css($custom_css);
					
					$arrCSS = $operations->getCaptionsContentArray();
					$arrCssStyles = ThunderSliderFunctions::jsonEncodeForClientSide($arrCSS);
					$arrCssStyles = $arrCSS;
					
					self::ajaxResponseSuccess(__("Style deleted!",'thunderslider'),array("arrCaptions"=>$arrCaptions,'compressed_css'=>$styles.$custom_css,'initstyles'=>$arrCssStyles));
				break;
				case "update_static_css":
					$data = ''; //do not allow to add new global CSS anymore, instead, remove all!
					$staticCss = $operations->updateStaticCss($data);
					
					$db = new ThunderSliderDB();
					$styles = $db->fetch(ThunderSliderGlobals::$table_css);
					$styles = ThunderSliderCssParser::parseDbArrayToCss($styles, "\n");
					$styles = ThunderSliderCssParser::compress_css($styles);
					$custom_css = ThunderSliderOperations::getStaticCss();
					$custom_css = ThunderSliderCssParser::compress_css($custom_css);
					
					self::ajaxResponseSuccess(__("CSS saved",'thunderslider'),array("css"=>$staticCss,'compressed_css'=>$styles.$custom_css));
				break;
				case "insert_custom_anim":
					$arrAnims = $operations->insertCustomAnim($data); //$arrCaptions =
					self::ajaxResponseSuccess(__("Animation saved",'thunderslider'), $arrAnims); //,array("arrCaptions"=>$arrCaptions)
				break;
				case "update_custom_anim":
					$arrAnims = $operations->updateCustomAnim($data);
					self::ajaxResponseSuccess(__("Animation saved",'thunderslider'), $arrAnims); //,array("arrCaptions"=>$arrCaptions)
				break;
				case "update_custom_anim_name":
					$arrAnims = $operations->updateCustomAnimName($data);
					self::ajaxResponseSuccess(__("Animation saved",'thunderslider'), $arrAnims); //,array("arrCaptions"=>$arrCaptions)
				break;
				case "delete_custom_anim":
					$arrAnims = $operations->deleteCustomAnim($data);
					self::ajaxResponseSuccess(__("Animation deleted",'thunderslider'), $arrAnims); //,array("arrCaptions"=>$arrCaptions)
				break;
				case "update_slides_order":
					$slider->updateSlidesOrderFromData($data);
					self::ajaxResponseSuccess(__("Order updated",'thunderslider'));
				break;
				case "change_slide_title":
					$slide->updateTitleByID($data);
					self::ajaxResponseSuccess(__('Title updated','thunderslider'));
				break;
				case "change_slide_image":
					$slide->updateSlideImageFromData($data);
					$sliderID = ThunderSliderFunctions::getVal($data, "slider_id");
					self::ajaxResponseSuccessRedirect(__("Slide changed",'thunderslider'), self::getViewUrl(self::VIEW_SLIDE,"id=new&slider=$sliderID"));
				break;
				case "preview_slide":
					$operations->putSlidePreviewByData($data);
					exit;
				break;
				case "preview_slider":
					$sliderID = ThunderSliderFunctions::getPostGetVariable("sliderid");
					$do_markup = ThunderSliderFunctions::getPostGetVariable("only_markup");

					if($do_markup == 'true')
						$operations->previewOutputMarkup($sliderID);
					else
						$operations->previewOutput($sliderID);
					
					exit;
				break;
				case "get_import_slides_data":
					$slides = array();
					if(!is_array($data)){
						$slider->initByID(intval($data));
						
						$full_slides = $slider->getSlides(); //static slide is missing
						
						if(!empty($full_slides)){
							foreach($full_slides as $slide_id => $mslide){
								$slides[$slide_id]['layers'] = $mslide->getLayers();
								foreach($slides[$slide_id]['layers'] as $k => $l){ //remove columns as they can not be imported
									if(isset($l['type']) && ($l['type'] == 'column' || $l['type'] == 'row' || $l['type'] == 'group')) unset($slides[$slide_id]['layers'][$k]);
								}
								$slides[$slide_id]['params'] = $mslide->getParams();
							}
						}
						
						$staticID = $slide->getStaticSlideID($slider->getID());
						if($staticID !== false){
							$msl = new ThunderSliderSlide();
							if(strpos($staticID, 'static_') === false){
								$staticID = 'static_'.$slider->getID();
							}
							$msl->initByID($staticID);
							if($msl->getID() !== ''){
								$slides[$msl->getID()]['layers'] = $msl->getLayers();
								foreach($slides[$msl->getID()]['layers'] as $k => $l){ //remove columns as they can not be imported
									if(isset($l['type']) && ($l['type'] == 'column' || $l['type'] == 'row' || $l['type'] == 'group')) unset($slides[$msl->getID()]['layers'][$k]);
								}
								$slides[$msl->getID()]['params'] = $msl->getParams();
								$slides[$msl->getID()]['params']['title'] = __('Static Slide', 'thunderslider');
							}
						}
					}
					if(!empty($slides)){
						self::ajaxResponseData(array('slides' => $slides));
					}else{
						self::ajaxResponseData('');
					}
				break;
				case "create_navigation_preset":
					$nav = new ThunderSliderNavigation();
					
					$return = $nav->add_preset($data);
					
					if($return === true){
						self::ajaxResponseSuccess(__('Navigation preset saved/updated', 'thunderslider'), array('navs' => $nav->get_all_navigations()));
					}else{
						if($return === false) $return = __('Preset could not be saved/values are the same', 'thunderslider');
						self::ajaxResponseError($return);
					}
				break;
				case "delete_navigation_preset":
					$nav = new ThunderSliderNavigation();
					
					$return = $nav->delete_preset($data);
					
					if($return){
						self::ajaxResponseSuccess(__('Navigation preset deleted', 'thunderslider'), array('navs' => $nav->get_all_navigations()));
					}else{
						if($return === false) $return = __('Preset not found', 'thunderslider');
						self::ajaxResponseError($return);
					}
				break;
				case "toggle_slide_state":
					$currentState = $slide->toggleSlideStatFromData($data);
					self::ajaxResponseData(array("state"=>$currentState));
				break;
				case "toggle_hero_slide":
					$currentHero = $slider->setHeroSlide($data);
					self::ajaxResponseSuccess(__('Slide is now the new active Hero Slide', 'thunderslider'));
				break;
				case "slide_lang_operation":
					$responseData = $slide->doSlideLangOperation($data);
					self::ajaxResponseData($responseData);
				break;
				case "update_general_settings":
					$operations->updateGeneralSettings($data);
					self::ajaxResponseSuccess(__("General settings updated",'thunderslider'));
				break;
				case "fix_database_issues":
					update_option('thunderslider_change_database', true);
					ThunderSliderFront::createDBTables();
					
					self::ajaxResponseSuccess(__('Database structure creation/update done','thunderslider'));
				break;
				case "update_posts_sortby":
					$slider->updatePostsSortbyFromData($data);
					self::ajaxResponseSuccess(__("Sortby updated",'thunderslider'));
				break;
				case "replace_image_urls":
					$slider->replaceImageUrlsFromData($data);
					self::ajaxResponseSuccess(__("All Urls replaced",'thunderslider'));
				break;
				case "reset_slide_settings":
					$slider->resetSlideSettings($data);
					self::ajaxResponseSuccess(__("Settings in all Slides changed",'thunderslider'));
				break;
				case "delete_template_slide":
				
					$slideID = (isset($data['slide_id'])) ? $data['slide_id'] : -1;
					
					if($slideID === -1) ThunderSliderFunctions::throwError(__('Missing Slide ID!', 'thunderslider'));
					
					$slide->initByID($slideID);
					$slide->deleteSlide();
					
					$responseText = __("Slide deleted",'thunderslider');
					self::ajaxResponseSuccess($responseText);
				break;
				case "activate_purchase_code":
					$result = false;
					if(!empty($data['code'])){ // && !empty($data['email'])
						$result = $operations->checkPurchaseVerification($data);
					}else{
						ThunderSliderFunctions::throwError(__('The Purchase Code and the E-Mail address need to be set!', 'thunderslider'));
						exit();
					}

					if($result === true){
						self::ajaxResponseSuccessRedirect(__("Purchase Code Successfully Activated",'thunderslider'), self::getViewUrl(self::VIEW_SLIDERS));
					}elseif($result === false){
						ThunderSliderFunctions::throwError(__('Purchase Code is invalid', 'thunderslider'));
					}else{
						if($result == 'temp'){
							self::ajaxResponseSuccessRedirect(__("Purchase Code Temporary Activated",'thunderslider'), self::getViewUrl(self::VIEW_SLIDERS));
						}
						if($result == 'exist'){
							self::ajaxResponseData(array('error'=>$result,'msg'=> __('Purchase Code already registered!', 'thunderslider')));
						}
						/*elseif($result == 'bad_email'){
							ThunderSliderFunctions::throwError(__('Please add an valid E-Mail Address', 'thunderslider'));
						}elseif($result == 'email_used'){
							ThunderSliderFunctions::throwError(__('E-Mail already in use, please choose a different E-Mail', 'thunderslider'));
						}*/
						ThunderSliderFunctions::throwError(__('Purchase Code could not be validated', 'thunderslider'));
					}
				break;
				case "deactivate_purchase_code":
					$result = $operations->doPurchaseDeactivation($data);

					if($result){
						self::ajaxResponseSuccessRedirect(__("Successfully removed validation",'thunderslider'), self::getViewUrl(self::VIEW_SLIDERS));
					}else{
						ThunderSliderFunctions::throwError(__('Could not remove Validation!', 'thunderslider'));
					}
				break;
				case 'dismiss_notice':
					update_option('thunderslider-valid-notice', 'false');
					self::ajaxResponseSuccess(__(".",'thunderslider'));
				break;
				case 'dismiss_dynamic_notice':
					if(trim($data['id']) == 'DISCARD'){
						update_option('thunderslider-deact-notice', false);
					}elseif(trim($data['id']) == 'DISCARDTEMPACT'){
						update_option('thunderslider-temp-active-notice', 'false');
					}else{
						$notices_discarded = get_option('thunderslider-notices-dc', array());
						$notices_discarded[] = esc_attr(trim($data['id']));
						update_option('thunderslider-notices-dc', $notices_discarded);
					}
					
					self::ajaxResponseSuccess(__(".",'thunderslider'));
				break;
				case 'toggle_favorite':
					if(isset($data['id']) && intval($data['id']) > 0){
						$return = self::toggle_favorite_by_id($data['id']);
						if($return === true){
							self::ajaxResponseSuccess(__('Setting Changed!', 'thunderslider'));
						}else{
							$error = $return;
						}	
					}else{
						$error = __('No ID given', 'thunderslider');
					}
					self::ajaxResponseError($error);
				break;
				case "subscribe_to_newsletter":
					if(isset($data['email']) && !empty($data['email'])){
						$return = MandarinCMS_Newsletter::subscribe($data['email']);
						
						if($return !== false){
							if(!isset($return['status']) || $return['status'] === 'error'){
								$error = (isset($return['message']) && !empty($return['message'])) ? $return['message'] : __('Invalid Email', 'thunderslider');
								self::ajaxResponseError($error);
							}else{
								self::ajaxResponseSuccess(__("Success! Please check your Emails to finish the subscription", 'thunderslider'), $return);
							}
						}else{
							self::ajaxResponseError(__('Invalid Email/Could not connect to the Newsletter server', 'thunderslider'));
						}	
					}else{
						self::ajaxResponseError(__('No Email given', 'thunderslider'));
					}
				break;
				case "unsubscribe_to_newsletter":
					if(isset($data['email']) && !empty($data['email'])){
						$return = MandarinCMS_Newsletter::unsubscribe($data['email']);
						
						if($return !== false){
							if(!isset($return['status']) || $return['status'] === 'error'){
								$error = (isset($return['message']) && !empty($return['message'])) ? $return['message'] : __('Invalid Email', 'thunderslider');
								self::ajaxResponseError($error);
							}else{
								self::ajaxResponseSuccess(__("Success! Please check your Emails to finish the process", 'thunderslider'), $return);
							}
						}else{
							self::ajaxResponseError(__('Invalid Email/Could not connect to the Newsletter server', 'thunderslider'));
						}	
					}else{
						self::ajaxResponseError(__('No Email given', 'thunderslider'));
					}
				break;
				case 'change_specific_navigation':
					$nav = new ThunderSliderNavigation();
					
					$found = false;
					$navigations = $nav->get_all_navigations();
					foreach($navigations as $navig){
						if($data['id'] == $navig['id']){
							$found = true;
							break;
						}
					}
					if($found){
						$nav->create_update_navigation($data, $data['id']);
					}else{
						$nav->create_update_navigation($data);
					}
					
					self::ajaxResponseSuccess(__('Navigation saved/updated', 'thunderslider'), array('navs' => $nav->get_all_navigations()));
					
				break;
				case 'change_navigations':
					$nav = new ThunderSliderNavigation();
					
					$nav->create_update_full_navigation($data);
					
					self::ajaxResponseSuccess(__('Navigations updated', 'thunderslider'), array('navs' => $nav->get_all_navigations()));
				break;
				case 'delete_navigation':
					$nav = new ThunderSliderNavigation();
					
					if(isset($data) && intval($data) > 0){
						$return = $nav->delete_navigation($data);
						
						if($return !== true){
							self::ajaxResponseError($return);
						}else{
							self::ajaxResponseSuccess(__('Navigation deleted', 'thunderslider'), array('navs' => $nav->get_all_navigations()));
						}
					}
					
					self::ajaxResponseError(__('Wrong ID given', 'thunderslider'));
				break;
				case "get_facebook_photosets":
					if(!empty($data['url'])){
						$facebook = new ThunderSliderFacebook();
						$return = $facebook->get_photo_set_photos_options($data['url'],$data['album'],$data['app_id'],$data['app_secret']);
						if(!empty($return)){
							self::ajaxResponseSuccess(__('Successfully fetched Facebook albums', 'thunderslider'), array('html'=>implode(' ', $return)));
						}
						else{
							$error = __('Could not fetch Facebook albums', 'thunderslider');
							self::ajaxResponseError($error);	
						}
					}
					else {
						self::ajaxResponseSuccess(__('Cleared Albums', 'thunderslider'), array('html'=>implode(' ', $return)));
					}
				break;
				case "get_flickr_photosets":
					if(!empty($data['url']) && !empty($data['key'])){
						$flickr = new ThunderSliderFlickr($data['key']);
						$user_id = $flickr->get_user_from_url($data['url']);
						$return = $flickr->get_photo_sets($user_id,$data['count'],$data['set']);
						if(!empty($return)){
							self::ajaxResponseSuccess(__('Successfully fetched flickr photosets', 'thunderslider'), array("data"=>array('html'=>implode(' ', $return))));
						}
						else{
							$error = __('Could not fetch flickr photosets', 'thunderslider');
							self::ajaxResponseError($error);
						}
					}
					else {
						if(empty($data['url']) && empty($data['key'])){
							self::ajaxResponseSuccess(__('Cleared Photosets', 'thunderslider'), array('html'=>implode(' ', $return)));
						}
						elseif(empty($data['url'])){
							$error = __('No User URL - Could not fetch flickr photosets', 'thunderslider');
							self::ajaxResponseError($error);
						}
						else{
							$error = __('No API KEY - Could not fetch flickr photosets', 'thunderslider');
							self::ajaxResponseError($error);
						}
					}
				break;
				case "get_youtube_playlists":
					if(!empty($data['id'])){
						$youtube = new ThunderSliderYoutube(trim($data['api']),trim($data['id']));
						$return = $youtube->get_playlist_options($data['playlist']);
						self::ajaxResponseSuccess(__('Successfully fetched YouTube playlists', 'thunderslider'), array("data"=>array('html'=>implode(' ', $return))));
					}
					else {
						$error = __('Could not fetch YouTube playlists', 'thunderslider');
						self::ajaxResponseError($error);
					}
				break;
				case 'rs_get_store_information': 
					global $mcms_version, $rslb;
					
					$code			= get_option('thunderslider-code', '');
					$shop_version	= ThunderSliderTemplate::SHOP_VERSION;
					
					$validated = get_option('thunderslider-valid', 'false');
					if($validated == 'false'){
						$api_key = '';
						$username = '';
						$code = '';
					}
					
					$rattr = array(
						'code' => urlencode($code),
						'product' => urlencode('thunderslider'),
						'shop_version' => urlencode($shop_version),
						'version' => urlencode(ThunderSliderGlobals::SLIDER_REVISION)
					);
					
					$done	= false;
					$count	= 0;
					do {
						$url		= $rslb->get_url('templates');
						$request	= mcms_remote_post($url.'/thunderslider/store.php', array(
							'user-agent' => 'MandarinCMS/'.$mcms_version.'; '.get_bloginfo('url'),
							'body' => $rattr
						));
						
						$response = '';
						
						if(!is_mcms_error($request)) {
							$response	= json_decode(@$request['body'], true);
							$done		= true;
						}else{
							$rslb->move_server_list();
						}
						
						$count++;
					}while($done == false && $count < 5);
					
					self::ajaxResponseData(array("data"=>$response));
				break;
				case 'load_library_object': 
					$obj_library = new ThunderSliderObjectLibrary();
					
					$thumbhandle = $data['handle'];
					$type = $data['type'];
					if($type == 'thumb'){
						$thumb = $obj_library->_get_object_thumb($thumbhandle, 'thumb');
					}elseif($type == 'orig'){
						$thumb = $obj_library->_get_object_thumb($thumbhandle, 'original');
					}
					if($thumb['error']){
						self::ajaxResponseError(__('Object could not be loaded', 'thunderslider'));
					}else{
						self::ajaxResponseData(array('url'=> $thumb['url'], 'width' => $thumb['width'], 'height' => $thumb['height']));
					}
				break;
				case 'load_template_store_sliders': 
					$tmpl = new ThunderSliderTemplate();

					$tp_template_slider = $tmpl->getMandarinCMSTemplateSliders();
					
					ob_start();
					$tmpl->create_html_sliders($tp_template_slider);
					$html = ob_get_contents();
					ob_clean();
					ob_end_clean();
					
					self::ajaxResponseData(array('html'=> $html));
					
				break;
				case 'load_template_store_slides': 
					$tmpl = new ThunderSliderTemplate();

					$templates = $tmpl->getTemplateSlides();
					$tp_template_slider = $tmpl->getMandarinCMSTemplateSliders();

					$tmp_slider = new ThunderSlider();
					$all_slider = apply_filters('thunderslider_slide_templates', $tmp_slider->getArrSliders());
					
					ob_start();
					$tmpl->create_html_slides($tp_template_slider, $all_slider, $templates);
					$html = ob_get_contents();
					ob_clean();
					ob_end_clean();
					
					self::ajaxResponseData(array('html'=> $html));
					
				break;
				case 'load_object_library': 
					$html = '';
					$obj = new ThunderSliderObjectLibrary();
					$mdata = $obj->retrieve_all_object_data();
					
					self::ajaxResponseData(array('data'=> $mdata));
				break;
				case 'slide_editor_sticky_menu':
					if(isset($data['set_sticky']) && $data['set_sticky'] == 'true'){
						update_option('thunderslider_slide_editor_sticky', 'true');
					}else{
						update_option('thunderslider_slide_editor_sticky', 'false');
					}
					self::ajaxResponseData(array());
				break;
				case 'save_color_preset':
				
					$presets = TPColorpicker::save_color_presets($data['presets']);
					self::ajaxResponseData(array('presets' => $presets));
					
				break;
				default:
					$return = apply_filters('thunderslider_admin_onAjaxAction_switch', false, $action, $data, $slider, $slide, $operations);
					if($return === false)
						self::ajaxResponseError("wrong ajax action: ".esc_attr($action));
					
					exit;
				break;
			}
			
			
			$role = self::getMenuRole(); //add additional security check and allow for example import only for admin
		}
		catch(Exception $e){

			$message = $e->getMessage();
			if($action == "preview_slide" || $action == "preview_slider"){
				echo $message;
				exit();
			}

			self::ajaxResponseError($message);
		}

		//it's an ajax action, so exit
		self::ajaxResponseError("No response output on $action action. please check with the developer.");
		exit();
	}
	
	
	/**
	 * onAjax action handler
	 */
	public static function onFrontAjaxAction(){
		$db = new ThunderSliderDB();
		$slider = new ThunderSlider();
		$slide = new RevSlide();
		$operations = new ThunderSliderOperations();
		
		$token = self::getPostVar("token", false);
		
		//verify the token
		$isVerified = mcms_verify_nonce($token, 'ThunderSlider_Front');
		
		$error = false;
		if($isVerified){
			$data = self::getPostVar('data', false);
			switch(self::getPostVar('client_action', false)){
				case 'get_slider_html':
					$id = intval(self::getPostVar('id', 0));
					if($id > 0){
						$html = '';
						ob_start();
						$slider_class = ThunderSliderOutput::putSlider($id);
						$html = ob_get_contents();
						
						//add styling
						$custom_css = ThunderSliderOperations::getStaticCss();
						$custom_css = ThunderSliderCssParser::compress_css($custom_css);
						$styles = $db->fetch(ThunderSliderGlobals::$table_css);
						$styles = ThunderSliderCssParser::parseDbArrayToCss($styles, "\n");
						$styles = ThunderSliderCssParser::compress_css($styles);
						
						$html .= '<style type="text/css">'.$custom_css.'</style>';
						$html .= '<style type="text/css">'.$styles.'</style>';
						
						ob_clean();
						ob_end_clean();
						
						$result = (!empty($slider_class) && $html !== '') ? true : false;
						
						if(!$result){
							$error = __('Slider not found', 'thunderslider');
						}else{
							
							if($html !== false){
								self::ajaxResponseData($html);
							}else{
								$error = __('Slider not found', 'thunderslider');
							}
						}
					}else{
						$error = __('No Data Received', 'thunderslider');
					}
				break;
			}
			
		}else{
			$error = true;
		}
		
		if($error !== false){
			$showError = __('Loading Error', 'thunderslider');
			if($error !== true)
				$showError = __('Loading Error: ', 'thunderslider').$error;
			
			self::ajaxResponseError($showError, false);
		}
		exit();
	}
	
}
?>