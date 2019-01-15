<?php

/***** Register Widgets *****/

function jmd_magandanow_new_register_widgets() {
	register_widget('jmd_custom_posts_widget');
	register_widget('jmd_slider_hp_widget');
	register_widget('jmd_magandanow_new_tabbed');
	register_widget('jmd_magandanow_new_posts_large');
	register_widget('jmd_magandanow_new_posts_focus');
	register_widget('jmd_magandanow_new_posts_stacked');
}
add_action('widgets_init', 'jmd_magandanow_new_register_widgets');

/***** Include Widgets *****/

require_once('widgets/mh-custom-posts.php');
require_once('widgets/mh-slider.php');
require_once('widgets/mh-tabbed.php');
require_once('widgets/mh-posts-large.php');
require_once('widgets/mh-posts-focus.php');
require_once('widgets/mh-posts-stacked.php');

?>