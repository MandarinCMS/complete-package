<?php
/**
 * @author    MandarinCMS <info@jiiworks.net>
 * @link      http://www.jiiworks.net/
 * @copyright 2015 MandarinCMS
 */
 
if( !defined( 'BASED_TREE_URI') ) exit();

class ThunderSliderWpml{
	
	/**
	 * 
	 * true / false if the mcmsml module exists
	 */
	public static function isWpmlExists(){
		return did_action( 'mcmsml_loaded' );
	}
	
	/**
	 * 
	 * valdiate that mcmsml exists
	 */
	private static function validateWpmlExists(){
		if(!self::isWpmlExists())
			ThunderSliderFunctions::throwError("The mcmsml module is not activated");
	}
	
	/**
	 * 
	 * get languages array
	 */
	public static function getArrLanguages($getAllCode = true){
		
		self::validateWpmlExists();
		
		$arrLangs = apply_filters( 'mcmsml_active_languages', array() );
		
		$response = array();
		
		if($getAllCode == true)
			$response["all"] = __("All Languages",'thunderslider');
		
		foreach($arrLangs as $code=>$arrLang){
			$name = $arrLang["native_name"];
			$response[$code] = $name;
		}
		
		return($response);
	}
	
	/**
	 * 
	 * get assoc array of lang codes
	 */
	public static function getArrLangCodes($getAllCode = true){
		
		$arrCodes = array();
		
		if($getAllCode == true)
			$arrCodes["all"] = "all";
			
		self::validateWpmlExists();
		
		$arrLangs = apply_filters( 'mcmsml_active_languages', array() );
		
		foreach($arrLangs as $code=>$arr){
			$arrCodes[$code] = $code;
		}
		
		return($arrCodes);
	}
	
	
	/**
	 * 
	 * check if all languages exists in the given langs array
	 */
	public static function isAllLangsInArray($arrCodes){
		$arrAllCodes = self::getArrLangCodes();
		$diff = array_diff($arrAllCodes, $arrCodes);
		return(empty($diff));
	}
	
	
	/**
	 * 
	 * get langs with flags menu list
	 * @param $props
	 */
	public static function getLangsWithFlagsHtmlList($props = "",$htmlBefore = ""){
		
		$arrLangs = self::getArrLanguages();
		
		if(!empty($props))
			$props = " ".$props;
		
		$html = "<ul".$props.">"."\n";
		$html .= $htmlBefore;
	
		foreach($arrLangs as $code=>$title){
			$urlIcon = self::getFlagUrl($code);
		
		/* NEW:
		foreach($arrLangs as $lang){
            $code = $lang['language_code'];
            $title = $lang['native_name'];
            $urlIcon = $lang['country_flag_url'];
		
		*/	
			$html .= "<li data-lang='".$code."' class='item_lang'><a data-lang='".$code."' href='javascript:void(0)'>"."\n";
			$html .= "<img src='".$urlIcon."'/> $title"."\n";				
			$html .= "</a></li>"."\n";
		}

		$html .= "</ul>";
		
		
		return($html);
	}

	
	/**
	 * get flag url
	 */
	public static function getFlagUrl($code){
		
		self::validateWpmlExists();
		
		if ( empty( $code ) || $code == "all" ) {
            $url = RS_PLUGIN_URL.'admin/assets/images/icon-all.png'; // NEW: ICL_PLUGIN_URL . '/res/img/icon16.png';
        } else {
            $active_languages = apply_filters( 'mcmsml_active_languages', array() );
            $url = isset( $active_languages[$code]['country_flag_url'] ) ? $active_languages[$code]['country_flag_url'] : null;
        }
		
		//default: show all
		if(empty($url)){
			$url = RS_PLUGIN_URL.'admin/assets/images/icon-all.png';
			// NEW: $url = ICL_PLUGIN_URL . '/res/img/icon16.png';
		}
		
		return($url);
	}
	
	
	/**
	 * 
	 * get language title by code
	 */
	public static function getLangTitle($code){

		if($code == "all")
			return(__("All Languages", 'thunderslider'));
		
		$default_language = apply_filters( 'mcmsml_default_language', null );
        return apply_filters( 'mcmsml_translated_language_name', '', $code, $default_language );
	}
	
	
	/**
	 * 
	 * get current language
	 */
	public static function getCurrentLang(){
		self::validateWpmlExists();
		
		if ( is_admin() ) {
            return apply_filters( 'mcmsml_default_language', null );
        }
        return apply_filters( 'mcmsml_current_language', null );
		
		return($lang);
	}
}

/**
 * old classname extends new one (old classnames will be obsolete soon)
 * @since: 5.0
 **/
class UniteWpmlRev extends ThunderSliderWpml {}
?>