<?php
$output = $title = $tab_id = $font_icons_position= $icon_type= $icon= $icon_color= $icon_hover_color=
$icon_size= $icon_background_color=$icon_margin_bottom=$icon_margin_left='';

extract(shortcode_atts($this->predefined_atts, $atts));

//mcms_enqueue_script('jquery_ui_tabs_rotate');
//mcms_enqueue_script('imd_ui_tabs_rotate');

global $tabarr;
  
   $tabarr[]=array(
   	'title'=>$title,
   	'tab_id'=>$tab_id,
   	'font_icons_position'=>$font_icons_position,
   	'icon_type'=>$icon_type,
   	'icon'=>$icon,
   	'icon_color'=>$icon_color,
   	'icon_hover_color'=>$icon_hover_color,
   	'icon_size'=>$icon_size,
    'content'=>$content,
    'icon_margin'=>$icon_margin,
   	);


if( current_user_can('editor') || current_user_can('administrator') ) { 

$admn="Empty tab. Edit page to add content here.";
}
else{
   $admn="";
}
  $tabcont=mcmsb_js_remove_mcmsautop($content);

$css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, 'mcmsb_tab ui-tabs-panel mcmsb_ui-tabs-hide vc_clearfix ult_back', $this->settings['base'], $atts );
$output .= "\n\t\t\t" . '<div  class="ult_tabitemname"  >';
$output .= ($content=='' || $content==' ') ? __($admn, "js_composer") : "\n\t\t\t\t" . mcmsb_js_remove_mcmsautop($content);
$output .= "\n\t\t\t" . '</div> ' ;
 //do_shortcode($content);
return $output;