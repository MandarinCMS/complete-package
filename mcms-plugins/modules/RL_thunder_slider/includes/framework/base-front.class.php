<?php
/**
 * @author    MandarinCMS <info@jiiworks.net>
 * @link      http://www.jiiworks.net/
 * @copyright 2015 MandarinCMS
 */

if( !defined( 'BASED_TREE_URI') ) exit();

class ThunderSliderBaseFront extends ThunderSliderBase {		
	
	const ACTION_ENQUEUE_SCRIPTS = "mcms_enqueue_scripts";
	
	/**
	 * 
	 * main constructor		 
	 */
	public function __construct($t){
		
		parent::__construct($t);
		
		add_action('mcms_enqueue_scripts', array('ThunderSliderFront', 'onAddScripts'));
	}	
	
}

/**
 * old classname extends new one (old classnames will be obsolete soon)
 * @since: 5.0
 **/
class UniteBaseFrontClassRev extends ThunderSliderBaseFront {}
?>