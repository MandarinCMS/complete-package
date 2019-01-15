<?php
/**
 * @author    MandarinCMS <info@jiiworks.net>
 * @link      http://www.jiiworks.net/
 * @copyright 2015 MandarinCMS
 */

if( !defined( 'BASED_TREE_URI') ) exit();

class ThunderSliderBase {
	
	protected static $mcmsdb;
	protected static $table_prefix;
	protected static $t;
	
	protected static $url_ajax;
	protected static $url_ajax_showimage;
	protected static $path_views;
	protected static $path_templates;
	protected static $is_multisite;
	public static $url_ajax_actions;
	
	/**
	 * 
	 * the constructor
	 */
	public function __construct($t){
		global $mcmsdb;
		
		self::$is_multisite = ThunderSliderFunctionsMCMS::isMultisite();
		
		self::$mcmsdb = $mcmsdb;
		self::$table_prefix = self::$mcmsdb->base_prefix;
		if(self::$is_multisite){
			$blogID = ThunderSliderFunctionsMCMS::getBlogID();
			if($blogID != 1){
				self::$table_prefix .= $blogID."_";
			}
		}
		
		self::$t = $t;
		
		self::$url_ajax = admin_url("admin-ajax.php");
		self::$url_ajax_actions = self::$url_ajax . "?action=thunderslider_ajax_action";
		self::$url_ajax_showimage = self::$url_ajax . "?action=thunderslider_show_image";
		
		self::$path_views = RS_PLUGIN_PATH."admin/views/";
		self::$path_templates = self::$path_views."/templates/";
		
		load_module_textdomain('thunderslider',false,'thunderslider/languages/');
		
		//update globals oldversion flag
		ThunderSliderGlobals::$isNewVersion = false;
		$version = get_bloginfo("version");
		$version = (double)$version;
		if($version >= 3.5)
			ThunderSliderGlobals::$isNewVersion = true;
		
	}
	
	
	/**
	 * 
	 * add some mandarincms action
	 */
	protected static function addAction($action,$eventFunction){
		
		add_action( $action, array(self::$t, $eventFunction) );			
	}
	
	
	/**
	 * 
	 * get image url to be shown via thumb making script.
	 */
	public static function getImageUrl($filepath, $width=null,$height=null,$exact=false,$effect=null,$effect_param=null){
		
		$urlImage = self::getUrlThumb(self::$url_ajax_showimage, $filepath,$width ,$height ,$exact ,$effect ,$effect_param);
		
		return($urlImage);
	}
	
	/**
	 * get thumb url
	 * @since: 5.0
	 * @moved from image_view.class.php
	 */
	public static function getUrlThumb($urlBase, $filename,$width=null,$height=null,$exact=false,$effect=null,$effect_param=null){			
		
		$filename = urlencode($filename);
		
		$url = $urlBase."&img=$filename";
		if(!empty($width))
			$url .= "&w=".$width;
		if(!empty($height))
			$url .= "&h=".$height;
			
		if($exact == true){
			$url .= "&t=".self::TYPE_EXACT;
		}
		
		if(!empty($effect)){
			$url .= "&e=".$effect;
			if(!empty($effect_param))
				$url .= "&ea1=".$effect_param;
		}
		
		return($url);
	}
	
	
	/**
	 * 
	 * on show image ajax event. outputs image with parameters 
	 */
	public static function onShowImage(){
	
		$pathImages = ThunderSliderFunctionsMCMS::getPathContent();
		$urlImages = ThunderSliderFunctionsMCMS::getUrlContent();
		
		try{
			$imageID = intval(ThunderSliderFunctions::getGetVar("img"));
			
			$img = mcms_get_attachment_image_src( $imageID, 'thumb' );
			
			if(empty($img)) exit;
			
			self::outputImage($img[0]);
			
		}catch (Exception $e){
			header("status: 500");
			echo __('Image not Found', 'thunderslider');
			exit();
		}
	}
	
	/**
	 * show Image to client
	 * @since: 5.0
	 * @moved from image_view.class.php
	 */
	private static function outputImage($filepath){
		
		$info = ThunderSliderFunctions::getPathInfo($filepath);
		$ext = $info["extension"];
		
		$ext = strtolower($ext);
		if($ext == "jpg")
			$ext = "jpeg";
		
		$numExpires = 31536000;	//one year
		$strExpires = @date('D, d M Y H:i:s',time()+$numExpires);
		
		$contents = file_get_contents($filepath);
		$filesize = strlen($contents);
		header("Expires: $strExpires GMT");
		header("Cache-Control: public");
		header("Content-Type: image/$ext");
		header("Content-Length: $filesize");
		
		echo $contents;
		exit();
	}
	
	/**
	 * 
	 * get POST var
	 */
	protected static function getPostVar($key,$defaultValue = ""){
		$val = self::getVar($_POST, $key, $defaultValue);
		return($val);			
	}
	
	/**
	 * 
	 * get GET var
	 */
	protected static function getGetVar($key,$defaultValue = ""){
		$val = self::getVar($_GET, $key, $defaultValue);
		return($val);
	}
	
	
	/**
	 * 
	 * get post or get variable
	 */
	protected static function getPostGetVar($key,$defaultValue = ""){
		
		if(array_key_exists($key, $_POST))
			$val = self::getVar($_POST, $key, $defaultValue);
		else
			$val = self::getVar($_GET, $key, $defaultValue);				
		
		return($val);							
	}
	
	
	/**
	 * 
	 * get some var from array
	 */
	public static function getVar($arr,$key,$defaultValue = ""){
		$val = $defaultValue;
		if(isset($arr[$key])) $val = $arr[$key];
		return($val);
	}
	
	
	/**
	* Get all images sizes + custom added sizes
	*/
	public static function get_all_image_sizes($type = 'gallery'){
		$custom_sizes = array();
		
		switch($type){
			case 'flickr':
				$custom_sizes = array(
					'original' => __('Original', 'thunderslider'),
					'large' => __('Large', 'thunderslider'),
					'large-square' => __('Large Square', 'thunderslider'),
					'medium' => __('Medium', 'thunderslider'),
					'medium-800' => __('Medium 800', 'thunderslider'),
					'medium-640' => __('Medium 640', 'thunderslider'),
					'small' => __('Small', 'thunderslider'),
					'small-320' => __('Small 320', 'thunderslider'),
					'thumbnail'=> __('Thumbnail', 'thunderslider'),
					'square' => __('Square', 'thunderslider')
				);
			break;
			case 'instagram':
				$custom_sizes = array(
					'standard_resolution' => __('Standard Resolution', 'thunderslider'),
					'thumbnail' => __('Thumbnail', 'thunderslider'),
					'low_resolution' => __('Low Resolution', 'thunderslider')
				);
			break;
			case 'twitter':
				$custom_sizes = array(
					'large' => __('Standard Resolution', 'thunderslider')
				);
			break;
			case 'facebook':
				$custom_sizes = array(
					'full' => __('Original Size', 'thunderslider'),
					'thumbnail' => __('Thumbnail', 'thunderslider')
				);
			break;
			case 'youtube':
				$custom_sizes = array(
					'default' => __('Default', 'thunderslider'),
					'medium' => __('Medium', 'thunderslider'),
					'high' => __('High', 'thunderslider'),
					'standard' => __('Standard', 'thunderslider'),
					'maxres' => __('Max. Res.', 'thunderslider')
				);
			break;
			case 'vimeo':
				$custom_sizes = array(
					'thumbnail_small' => __('Small', 'thunderslider'),
					'thumbnail_medium' => __('Medium', 'thunderslider'),
					'thumbnail_large' => __('Large', 'thunderslider'),
				);
			break;
			case 'gallery':
			default:
				$added_image_sizes = get_intermediate_image_sizes();
				if(!empty($added_image_sizes) && is_array($added_image_sizes)){
					foreach($added_image_sizes as $key => $img_size_handle){
						$custom_sizes[$img_size_handle] = ucwords(str_replace('_', ' ', $img_size_handle));
					}
				}
				$img_orig_sources = array(
					'full' => __('Original Size', 'thunderslider'),
					'thumbnail' => __('Thumbnail', 'thunderslider'),
					'medium' => __('Medium', 'thunderslider'),
					'large' => __('Large', 'thunderslider')
				);
				$custom_sizes = array_merge($img_orig_sources, $custom_sizes);
			break;
		}
		
		return $custom_sizes;
	}
	
	
	/**
	 * retrieve the image id from the given image url
	 */
	public static function get_image_id_by_url($image_url) {
		global $mcmsdb;
		
		$attachment_id = 0;
		
		if(function_exists('attachment_url_to_postid')){
			$attachment_id = attachment_url_to_postid($image_url); //0 if failed
		}
		if ( 0 == $attachment_id ){ //try to get it old school way
			//for MCMS < 4.0.0
			$attachment_id = false;

			// If there is no url, return.
			if ( '' == $image_url )
				return;

			// Get the upload directory paths
			$upload_dir_paths = mcms_upload_dir();

			// Make sure the upload path base directory exists in the attachment URL, to verify that we're working with a media library image
			if ( false !== strpos( $image_url, $upload_dir_paths['baseurl'] ) ) {

				// If this is the URL of an auto-generated thumbnail, get the URL of the original image
				$image_url = preg_replace( '/-\d+x\d+(?=\.(jpg|jpeg|png|gif)$)/i', '', $image_url );

				// Remove the upload path base directory from the attachment URL
				$image_url = str_replace( $upload_dir_paths['baseurl'] . '/', '', $image_url );

				// Finally, run a custom database query to get the attachment ID from the modified attachment URL
				$attachment_id = $mcmsdb->get_var( $mcmsdb->prepare( "SELECT mcmsosts.ID FROM $mcmsdb->posts mcmsosts, $mcmsdb->postmeta mcmsostmeta WHERE mcmsosts.ID = mcmsostmeta.post_id AND mcmsostmeta.meta_key = '_mcms_attached_file' AND mcmsostmeta.meta_value = '%s' AND mcmsosts.post_type = 'attachment'", $image_url ) );

			}
		}
		
		return $attachment_id;
	}
	
	/**
	 * get all the svg url sets used in RazorLeaf ThunderSlider
	 * @since: 5.1.7
	 **/
	public static function get_svg_sets_url(){
		$svg_sets = array();
		
		$path = RS_PLUGIN_PATH . 'public/assets/assets/svg/';
		$url = RS_PLUGIN_URL . 'public/assets/assets/svg/';
		
		if(!file_exists($path.'action/ic_3d_rotation_24px.svg')){ //the path needs to be changed to the uploads folder then
			$upload_dir = mcms_upload_dir();
			$path = $upload_dir['basedir'].'/thunderslider/assets/svg/';
			$url = $upload_dir['baseurl'].'/thunderslider/assets/svg/';
		}
		
		$svg_sets['Actions'] = array('path' => $path.'action/', 'url' => $url.'action/');
		$svg_sets['Alerts'] = array('path' => $path.'alert/', 'url' => $url.'alert/');
		$svg_sets['AV'] = array('path' => $path.'av/', 'url' => $url.'av/');
		$svg_sets['Communication'] = array('path' => $path.'communication/', 'url' => $url.'communication/');
		$svg_sets['Content'] = array('path' => $path.'content/', 'url' => $url.'content/');
		$svg_sets['Device'] = array('path' => $path.'device/', 'url' => $url.'device/');
		$svg_sets['Editor'] = array('path' => $path.'editor/', 'url' => $url.'editor/');
		$svg_sets['File'] = array('path' => $path.'file/', 'url' => $url.'file/');
		$svg_sets['Hardware'] = array('path' => $path.'hardware/', 'url' => $url.'hardware/');
		$svg_sets['Images'] = array('path' => $path.'image/', 'url' => $url.'image/');
		$svg_sets['Maps'] = array('path' => $path.'maps/', 'url' => $url.'maps/');
		$svg_sets['Navigation'] = array('path' => $path.'navigation/', 'url' => $url.'navigation/');
		$svg_sets['Notifications'] = array('path' => $path.'notification/', 'url' => $url.'notification/');
		$svg_sets['Places'] = array('path' => $path.'places/', 'url' => $url.'places/');
		$svg_sets['Social'] = array('path' => $path.'social/', 'url' => $url.'social/');
		$svg_sets['Toggle'] = array('path' => $path.'toggle/', 'url' => $url.'toggle/');
		
		
		$svg_sets = apply_filters('thunderslider_get_svg_sets', $svg_sets);
		
		return $svg_sets;
	}
	
	/**
	 * get all the svg files for given sets used in RazorLeaf ThunderSlider
	 * @since: 5.1.7
	 **/
	public static function get_svg_sets_full(){
		
		$svg_sets = self::get_svg_sets_url();
		
		$svg = array();
		
		if(!empty($svg_sets)){
			foreach($svg_sets as $handle => $values){
				$svg[$handle] = array();
				
				if($dir = opendir($values['path'])) {
					while(false !== ($file = readdir($dir))){
						if ($file != "." && $file != "..") {
							$filetype = pathinfo($file);
							
							if(isset($filetype['extension']) && $filetype['extension'] == 'svg'){
								$svg[$handle][$file] = $values['url'].$file;
							}
						}
					}
				}
			}
		}
		
		$svg = apply_filters('thunderslider_get_svg_sets_full', $svg);
		
		return $svg;
	}
	
	
	/**
	 * get all the icon sets used in RazorLeaf ThunderSlider
	 * @since: 5.0
	 **/
	public static function get_icon_sets(){
		$icon_sets = array();
		
		$icon_sets = apply_filters('thunderslider_mod_icon_sets', $icon_sets);
		
		return $icon_sets;
	}
	
	
	/**
	 * add default icon sets of RazorLeaf ThunderSlider
	 * @since: 5.0
	 **/
	public static function set_icon_sets($icon_sets){
		
		$icon_sets[] = 'fa-icon-';
		$icon_sets[] = 'pe-7s-';
		
		return $icon_sets;
	}
	
	
	/**
	 * translates removed settings from Slider Settings from version <= 4.x to 5.0
	 * @since: 5.0
	 **/
	public static function translate_settings_to_v5($settings){
		
		if(isset($settings['navigaion_type'])){
			switch($settings['navigaion_type']){
				case 'none': // all is off, so leave the defaults
				break;
				case 'bullet':
					$settings['enable_bullets'] = 'on';
					$settings['enable_thumbnails'] = 'off';
					$settings['enable_tabs'] = 'off';
					
				break;
				case 'thumb':
					$settings['enable_bullets'] = 'off';
					$settings['enable_thumbnails'] = 'on';
					$settings['enable_tabs'] = 'off';
				break;
			}
			unset($settings['navigaion_type']);
		}
		
		if(isset($settings['navigation_arrows'])){
			$settings['enable_arrows'] = ($settings['navigation_arrows'] == 'solo' || $settings['navigation_arrows'] == 'nexttobullets') ? 'on' : 'off';
			unset($settings['navigation_arrows']);
		}
		
		if(isset($settings['navigation_style'])){
			$settings['navigation_arrow_style'] = $settings['navigation_style'];
			$settings['navigation_bullets_style'] = $settings['navigation_style'];
			unset($settings['navigation_style']);
		}
		
		if(isset($settings['navigaion_always_on'])){
			$settings['arrows_always_on'] = $settings['navigaion_always_on'];
			$settings['bullets_always_on'] = $settings['navigaion_always_on'];
			$settings['thumbs_always_on'] = $settings['navigaion_always_on'];
			unset($settings['navigaion_always_on']);
		}
		
		if(isset($settings['hide_thumbs']) && !isset($settings['hide_arrows']) && !isset($settings['hide_bullets'])){ //as hide_thumbs is still existing, we need to check if the other two were already set and only translate this if they are not set yet
			$settings['hide_arrows'] = $settings['hide_thumbs'];
			$settings['hide_bullets'] = $settings['hide_thumbs'];
		}
		
		if(isset($settings['navigaion_align_vert'])){
			$settings['bullets_align_vert'] = $settings['navigaion_align_vert'];
			$settings['thumbnails_align_vert'] = $settings['navigaion_align_vert'];
			unset($settings['navigaion_align_vert']);
		}
		
		if(isset($settings['navigaion_align_hor'])){
			$settings['bullets_align_hor'] = $settings['navigaion_align_hor'];
			$settings['thumbnails_align_hor'] = $settings['navigaion_align_hor'];
			unset($settings['navigaion_align_hor']);
		}
		
		if(isset($settings['navigaion_offset_hor'])){
			$settings['bullets_offset_hor'] = $settings['navigaion_offset_hor'];
			$settings['thumbnails_offset_hor'] = $settings['navigaion_offset_hor'];
			unset($settings['navigaion_offset_hor']);
		}
		
		if(isset($settings['navigaion_offset_hor'])){
			$settings['bullets_offset_hor'] = $settings['navigaion_offset_hor'];
			$settings['thumbnails_offset_hor'] = $settings['navigaion_offset_hor'];
			unset($settings['navigaion_offset_hor']);
		}
		
		if(isset($settings['navigaion_offset_vert'])){
			$settings['bullets_offset_vert'] = $settings['navigaion_offset_vert'];
			$settings['thumbnails_offset_vert'] = $settings['navigaion_offset_vert'];
			unset($settings['navigaion_offset_vert']);
		}
		
		if(isset($settings['show_timerbar']) && !isset($settings['enable_progressbar'])){
			if($settings['show_timerbar'] == 'hide'){
				$settings['enable_progressbar'] = 'off';
				$settings['show_timerbar'] = 'top';
			}else{
				$settings['enable_progressbar'] = 'on';
			}
		}
		
		return $settings;
	}
	
	
	/**
	 * explodes google fonts and returns the number of font weights of all fonts
	 * @since: 5.0
	 **/
	public static function get_font_weight_count($string){
		$string = explode(':', $string);

		$nums = 0;

		if(count($string) >= 2){
			$string = $string[1];
			if(strpos($string, '&') !== false){
				$string = explode('&', $string);
				$string = $string[0];
			}
			
			$nums = count(explode(',', $string));
		}
		
		return $nums;
	}
	
	
	/**
	 * strip slashes recursive
	 * @since: 5.0
	 */
	public static function stripslashes_deep($value){
		$value = is_array($value) ?
			array_map( array('ThunderSliderBase', 'stripslashes_deep'), $value) :
			stripslashes($value);

		return $value;
	}
	
	
	/**
	 * check if file is in zip
	 * @since: 5.0
	 */
	public static function check_file_in_zip($d_path, $image, $alias, &$alreadyImported, $add_path = false){
		global $mcms_filesystem;
		
		if(trim($image) !== ''){
			if(strpos($image, 'http') !== false){
			}else{
				$strip = false;
				$zimage = $mcms_filesystem->exists( $d_path.'images/'.$image );
				if(!$zimage){
					$zimage = $mcms_filesystem->exists( str_replace('//', '/', $d_path.'images/'.$image) );
					$strip = true;
				}
				
				if(!$zimage){
					//echo $image.__(' not found!<br>', 'thunderslider');
				}else{
					if(!isset($alreadyImported['images/'.$image])){
						//check if we are object folder, if yes, do not import into media library but add it to the object folder
						$uimg = ($strip == true) ? str_replace('//', '/', 'images/'.$image) : $image; //pclzip
						
						$object_library = (strpos($uimg, 'thunderslider/objects/') === 0) ? true : false;
						
						if($object_library === true){ //copy the image to the objects folder if false
							$objlib = new ThunderSliderObjectLibrary();
							$importImage = $objlib->_import_object($d_path.'images/'.$uimg);
						}else{
							$importImage = ThunderSliderFunctionsMCMS::import_media($d_path.'images/'.$uimg, $alias.'/');
						}
						
						if($importImage !== false){
							$alreadyImported['images/'.$image] = $importImage['path'];
							
							$image = $importImage['path'];
						}
					}else{
						$image = $alreadyImported['images/'.$image];
					}
				}
				if($add_path){
					$upload_dir = mcms_upload_dir();
					$cont_url = $upload_dir['baseurl'];
					$image = str_replace('uploads/uploads/', 'uploads/', $cont_url . '/' . $image);
				}
			}
		}
		
		return $image;
	}
	
	
	/**
	 * add "a" tags to links within a text
	 * @since: 5.0
	 */
	public static function add_wrap_around_url($text){
		$reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
		// Check if there is a url in the text
		if(preg_match($reg_exUrl, $text, $url)){
			// make the urls hyper links
			return preg_replace($reg_exUrl, '<a href="'.$url[0].'" rel="nofollow" target="_blank">'.$url[0].'</a>', $text);
		}else{
			// if no urls in the text just return the text
			return $text;
		}
	}
	
	
	/**
	 * prints out debug text if constant TP_DEBUG is defined and true
 	 * @since: 5.2.4
	 */
	public static function debug($value , $message, $where = "console"){
		if( defined('TP_DEBUG') && TP_DEBUG ){
			if($where=="console"){
				echo '<script>
					jQuery(document).ready(function(){
						if(window.console) {
							console.log("'.$message.'");
							console.log('.json_encode($value).');
						}
					});
				</script>
				';
			}
			else{
				var_dump($value);
			}
		}
		else {
			return false;
		}
	}

}

/**
 * old classname extends new one (old classnames will be obsolete soon)
 * @since: 5.0
 **/
class UniteBaseClassRev extends ThunderSliderBase {}
?>