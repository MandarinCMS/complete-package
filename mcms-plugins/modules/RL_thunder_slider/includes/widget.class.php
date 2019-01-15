<?php
/**
 * @author    MandarinCMS <info@jiiworks.net>
 * @link      http://www.jiiworks.net/
 * @copyright 2015 MandarinCMS
 */
 
if( !defined( 'BASED_TREE_URI') ) exit();

class ThunderSliderWidget extends MCMS_Widget {
	
    public function __construct(){
    	
        // widget actual processes
     	$widget_ops = array('classname' => 'widget_thunderslider', 'description' => __('Displays a revolution slider on the page','thunderslider') );
        parent::__construct('rev-slider-widget', __('RazorLeaf ThunderSlider','thunderslider'), $widget_ops);
    }
 
    /**
     * 
     * the form
     */
    public function form($instance) {
		try {
            $slider = new ThunderSlider();
            $arrSliders = $slider->getArrSlidersShort();
        }catch(Exception $e){}            
          
		if(empty($arrSliders)){
			echo __("No sliders found, Please create a slider",'thunderslider');
		}else{
			
			$field = "thunder_slider";
			$fieldPages = "thunder_slider_pages";
			$fieldCheck = "thunder_slider_homepage";
			$fieldTitle = "thunder_slider_title";
			
	    	$sliderID = ThunderSliderFunctions::getVal($instance, $field);
	    	$homepage = ThunderSliderFunctions::getVal($instance, $fieldCheck);
	    	$pagesValue = ThunderSliderFunctions::getVal($instance, $fieldPages);
	    	$title = ThunderSliderFunctions::getVal($instance, $fieldTitle);
	    	
			$fieldID = $this->get_field_id( $field );
			$fieldName = $this->get_field_name( $field );
			
			$select = ThunderSliderFunctions::getHTMLSelect($arrSliders,$sliderID,'name="'.$fieldName.'" id="'.$fieldID.'"',true);
			
			$fieldID_check = $this->get_field_id( $fieldCheck );
			$fieldName_check = $this->get_field_name( $fieldCheck );
			$checked = "";
			if($homepage == "on")
				$checked = "checked='checked'";

			$fieldPages_ID = $this->get_field_id( $fieldPages );
			$fieldPages_Name = $this->get_field_name( $fieldPages );
			
			$fieldTitle_ID = $this->get_field_id( $fieldTitle );
			$fieldTitle_Name = $this->get_field_name( $fieldTitle );
			
			?>
			<label for="<?php echo $fieldTitle_ID?>"><?php _e("Title",'thunderslider')?>:</label>
			<input type="text" name="<?php echo $fieldTitle_Name?>" id="<?php echo $fieldTitle_ID?>" value="<?php echo $title?>" class="widefat">
			
			<br><br>
			
			<?php _e("Choose Slider",'thunderslider')?>: <?php echo $select?>
			<div style="padding-top:10px;"></div>
			
			<label for="<?php echo $fieldID_check?>"><?php _e("Home Page Only",'thunderslider')?>:</label>
			<input type="checkbox" name="<?php echo $fieldName_check?>" id="<?php echo $fieldID_check?>" <?php echo $checked?> >
			<br><br>
			<label for="<?php echo $fieldPages_ID?>"><?php _e("Pages: (example: 2,10)",'thunderslider')?></label>
			<input type="text" name="<?php echo $fieldPages_Name?>" id="<?php echo $fieldPages_ID?>" value="<?php echo $pagesValue?>">
			
			<div style="padding-top:10px;"></div>
			<?php
		}	//else
    }
 
    /**
     * 
     * update
     */
    public function update($new_instance, $old_instance) {
    	
        return($new_instance);
    }

    
    /**
     * 
     * widget output
     */
    public function widget($args, $instance) {
    	
		$sliderID = ThunderSliderFunctions::getVal($instance, "thunder_slider");
		$title = ThunderSliderFunctions::getVal($instance, "thunder_slider_title");
		
		$homepageCheck = ThunderSliderFunctions::getVal($instance, "thunder_slider_homepage");
		$homepage = "";
		if($homepageCheck == "on")
			$homepage = "homepage";
		
		$pages = ThunderSliderFunctions::getVal($instance, "thunder_slider_pages");
		if(!empty($pages)){
			if(!empty($homepage))
				$homepage .= ",";
			$homepage .= $pages;
		}
				
		if(empty($sliderID))
			return(false);
		
		$slider = new ThunderSliderSlider();
		$slider->initByID($sliderID);
		$disable_on_mobile = $slider->getParam("disable_on_mobile","off");
		if($disable_on_mobile == 'on'){
			$mobile = (strstr($_SERVER['HTTP_USER_AGENT'],'Android') || strstr($_SERVER['HTTP_USER_AGENT'],'webOS') || strstr($_SERVER['HTTP_USER_AGENT'],'iPhone') ||strstr($_SERVER['HTTP_USER_AGENT'],'iPod') || strstr($_SERVER['HTTP_USER_AGENT'],'iPad') || strstr($_SERVER['HTTP_USER_AGENT'],'Windows Phone') || mcms_is_mobile()) ? true : false;
			if($mobile) return false;
		}
		
		
		//widget output
		$beforeWidget = ThunderSliderFunctions::getVal($args, "before_widget");
		$afterWidget = ThunderSliderFunctions::getVal($args, "after_widget");
		$beforeTitle = ThunderSliderFunctions::getVal($args, "before_title");
		$afterTitle = ThunderSliderFunctions::getVal($args, "after_title");
		
		echo $beforeWidget;
		
		if(!empty($title))
			echo $beforeTitle.$title.$afterTitle;
		
		ThunderSliderOutput::putSlider($sliderID,$homepage);

		add_action('mcms_head', array($this,'writeCSS'));
	    
		echo $afterWidget;						
    }

    public function writeCSS(){
    }
 
}

/**
 * old classname extends new one (old classnames will be obsolete soon)
 * @since: 5.0
 **/
class ThunderSlider_Widget extends ThunderSliderWidget {}
?>