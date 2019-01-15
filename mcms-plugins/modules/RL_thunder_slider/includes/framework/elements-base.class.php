<?php
/**
 * @author    MandarinCMS <info@jiiworks.net>
 * @link      http://www.jiiworks.net/
 * @copyright 2015 MandarinCMS
 */
 
if( !defined( 'BASED_TREE_URI') ) exit();

class ThunderSliderElementsBase {
	
	protected $db;
	
	public function __construct(){
		
		$this->db = new ThunderSliderDB();
	}
	
}

/**
 * old classname extends new one (old classnames will be obsolete soon)
 * @since: 5.0
 **/
class UniteElementsBaseRev extends ThunderSliderElementsBase {}
?>