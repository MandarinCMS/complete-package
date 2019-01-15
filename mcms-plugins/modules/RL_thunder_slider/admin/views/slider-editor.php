<?php
if( !defined('BASED_TREE_URI') ) exit();

//get taxonomies with cats
$postTypesWithCats = ThunderSliderOperations::getPostTypesWithCatsForClient();		
$jsonTaxWithCats = ThunderSliderFunctions::jsonEncodeForClientSide($postTypesWithCats);

//check existing slider data:
$sliderID = self::getGetVar('id');

$arrFieldsParams = array();

$uslider = new ThunderSlider();

if(!empty($sliderID)){
	$slider = new ThunderSlider();
	$slider->initByID($sliderID);
	
	//get setting fields
	$settingsFields = $slider->getSettingsFields();
	$arrFieldsMain = $settingsFields['main'];
	$arrFieldsParams = $settingsFields['params'];		
	
	$linksEditSlides = self::getViewUrl(ThunderSliderAdmin::VIEW_SLIDE,'id=new&slider='.intval($sliderID));
	
	require self::getPathTemplate('edit-slider');
}else{
	require self::getPathTemplate('create-slider');		
}

?>