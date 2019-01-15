<?php

/***** Declare WooCommerce Compatibility *****/

add_myskin_support('woocommerce');
add_myskin_support('wc-product-gallery-zoom');
add_myskin_support('wc-product-gallery-lightbox');
add_myskin_support('wc-product-gallery-slider');

/***** Custom WooCommerce Markup *****/

remove_action('woocommerce_sidebar', 'woocommerce_get_sidebar', 10);

function jmd_magandanow_new_wrapper_start() {
	echo '<div class="mh-wrapper mh-clearfix">' . "\n";
		echo '<div id="main-content" class="mh-content entry-content" role="main" itemprop="mainContentOfPage">' . "\n";
}
remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
add_action('woocommerce_before_main_content', 'jmd_magandanow_new_wrapper_start', 10);

function jmd_magandanow_new_wrapper_end() {
		echo '</div>' . "\n";
		get_sidebar();
  	echo '</div>' . "\n";
}
remove_action('woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);
add_action('woocommerce_after_main_content', 'jmd_magandanow_new_wrapper_end', 10);

/***** Load Custom WooCommerce CSS *****/

function jmd_magandanow_new_woocommerce_css() {
    mcms_register_style('mh-woocommerce', get_template_directory_uri() . '/woocommerce/woocommerce.css');
    mcms_enqueue_style('mh-woocommerce');
}
add_action('mcms_enqueue_scripts', 'jmd_magandanow_new_woocommerce_css');

?>