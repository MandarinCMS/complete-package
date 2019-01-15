<?php

/***** Fetch MySkin Data & Options *****/

$jmd_magandanow_new_data = mcms_get_myskin('jmd-magandanow-new');
$jmd_magandanow_new_version = $jmd_magandanow_new_data['Version'];
$jmd_magandanow_new_options = get_option('jmd_magandanow_new_options');

/***** Check if Active MySkin is Official MySkin / Child MySkin by MH MySkins *****/

function jmd_magandanow_new_official_myskin() {
	$active_myskin = mcms_get_myskin();
	$active_myskin_author = $active_myskin['Author'];
	if ($active_myskin_author === '<a href="#">JMD MySkins</a>') {
		$official_myskin = true;
	} else {
		$official_myskin = false;
	}
	return $official_myskin;
}

/***** Custom Hooks *****/

function jmd_before_header() {
    do_action('jmd_before_header');
}
function jmd_after_header() {
    do_action('jmd_after_header');
}
function jmd_before_page_content() {
    do_action('jmd_before_page_content');
}
function jmd_before_post_content() {
    do_action('jmd_before_post_content');
}
function jmd_after_post_content() {
    do_action('jmd_after_post_content');
}
function jmd_post_header() {
    do_action('jmd_post_header');
}
function jmd_before_footer() {
    do_action('jmd_before_footer');
}
function jmd_after_footer() {
    do_action('jmd_after_footer');
}
function jmd_before_container_close() {
    do_action('jmd_before_container_close');
}

/***** MySkin Setup *****/

if (!function_exists('jmd_magandanow_new_setup')) {
	function jmd_magandanow_new_setup() {
		load_myskin_textdomain('jmd-magandanow-new', get_template_directory() . '/languages');
		add_filter('use_default_gallery_style', '__return_false');
		add_myskin_support('title-tag');
		add_myskin_support('automatic-feed-links');
		add_myskin_support('html5', array('comment-list', 'comment-form', 'search-form', 'gallery', 'caption'));
		add_myskin_support('post-thumbnails');
		add_myskin_support('custom-background', array('default-color' => 'f7f7f7'));
		add_myskin_support('custom-header', array('default-image' => '', 'default-text-color' => '000', 'width' => 1080, 'height' => 250, 'flex-width' => true, 'flex-height' => true));
		add_myskin_support('custom-logo', array('width' => 300, 'height' => 100, 'flex-width' => true, 'flex-height' => true));
		add_myskin_support('customize-selective-refresh-widgets');
		register_nav_menu('main_nav', esc_html__('Main Navigation', 'jmd-magandanow-new'));
		add_editor_style();
	}
}
add_action('after_setup_myskin', 'jmd_magandanow_new_setup');

/***** Add Custom Image Sizes *****/

if (!function_exists('jmd_magandanow_new_image_sizes')) {
	function jmd_magandanow_new_image_sizes() {
		add_image_size('jmd-magandanow-new-slider', 1030, 438, true);
		add_image_size('jmd-magandanow-new-content', 678, 381, true);
		add_image_size('jmd-magandanow-new-large', 678, 509, true);
		add_image_size('jmd-magandanow-new-medium', 326, 245, true);
		add_image_size('jmd-magandanow-new-small', 80, 60, true);
	}
}
add_action('after_setup_myskin', 'jmd_magandanow_new_image_sizes');

/***** Set Content Width *****/

if (!function_exists('jmd_magandanow_new_content_width')) {
	function jmd_magandanow_new_content_width() {
		global $content_width;
		if (!isset($content_width)) {
			$content_width = 678;
		}
	}
}
add_action('template_redirect', 'jmd_magandanow_new_content_width');

/***** Load CSS & JavaScript *****/

if (!function_exists('jmd_magandanow_new_scripts')) {
	function jmd_magandanow_new_scripts() {
		global $jmd_magandanow_new_version;
		mcms_enqueue_style('mh-google-fonts', 'https://fonts.googleapis.com/css?family=Open+Sans:400,400italic,700,600', array(), null);
		mcms_enqueue_style('jmd-magandanow-new', get_stylesheet_uri(), false, $jmd_magandanow_new_version);
		mcms_enqueue_style('mh-font-awesome', get_template_directory_uri() . '/includes/font-awesome.min.css', array(), null);
		mcms_enqueue_script('mh-scripts', get_template_directory_uri() . '/js/scripts.js', array('jquery'), $jmd_magandanow_new_version);
		if (is_singular() && comments_open() && get_option('thread_comments') == 1) {
			mcms_enqueue_script('comment-reply');
		}
	}
}
add_action('mcms_enqueue_scripts', 'jmd_magandanow_new_scripts');

if (!function_exists('jmd_magandanow_new_admin_scripts')) {
	function jmd_magandanow_new_admin_scripts($hook) {
		if ('appearance_page_magandanews' === $hook || 'widgets.php' === $hook) {
			mcms_enqueue_style('mh-admin', get_template_directory_uri() . '/admin/admin.css');
		}
	}
}
add_action('admin_enqueue_scripts', 'jmd_magandanow_new_admin_scripts');

/***** Register Widget Areas / Sidebars	*****/

if (!function_exists('jmd_magandanow_new_widgets_init')) {
	function jmd_magandanow_new_widgets_init() {
		register_sidebar(array('name' => esc_html__('Sidebar', 'jmd-magandanow-new'), 'id' => 'sidebar', 'description' => esc_html__('Widget area (sidebar left/right) on single posts, pages and archives.', 'jmd-magandanow-new'), 'before_widget' => '<div id="%1$s" class="mh-widget %2$s">', 'after_widget' => '</div>', 'before_title' => '<h4 class="mh-widget-title"><span class="mh-widget-title-inner">', 'after_title' => '</span></h4>'));
		register_sidebar(array('name' => sprintf(esc_html_x('Home %d - Full Width', 'widget area name', 'jmd-magandanow-new'), 1), 'id' => 'home-1', 'description' => esc_html__('Widget area on homepage.', 'jmd-magandanow-new'), 'before_widget' => '<div id="%1$s" class="mh-widget mh-home-1 mh-home-wide %2$s">', 'after_widget' => '</div>', 'before_title' => '<h4 class="mh-widget-title"><span class="mh-widget-title-inner">', 'after_title' => '</span></h4>'));
		register_sidebar(array('name' => sprintf(esc_html_x('Home %d - 2/3 Width', 'widget area name', 'jmd-magandanow-new'), 2), 'id' => 'home-2', 'description' => esc_html__('Widget area on homepage.', 'jmd-magandanow-new'), 'before_widget' => '<div id="%1$s" class="mh-widget mh-home-2 mh-widget-col-2 %2$s">', 'after_widget' => '</div>', 'before_title' => '<h4 class="mh-widget-title"><span class="mh-widget-title-inner">', 'after_title' => '</span></h4>'));
		register_sidebar(array('name' => sprintf(esc_html_x('Home %d - 1/3 Width', 'widget area name', 'jmd-magandanow-new'), 3), 'id' => 'home-3', 'description' => esc_html__('Widget area on homepage.', 'jmd-magandanow-new'), 'before_widget' => '<div id="%1$s" class="mh-widget mh-home-3 %2$s">', 'after_widget' => '</div>', 'before_title' => '<h4 class="mh-widget-title"><span class="mh-widget-title-inner">', 'after_title' => '</span></h4>'));
		register_sidebar(array('name' => sprintf(esc_html_x('Home %d - 1/3 Width', 'widget area name', 'jmd-magandanow-new'), 4), 'id' => 'home-4', 'description' => esc_html__('Widget area on homepage.', 'jmd-magandanow-new'), 'before_widget' => '<div id="%1$s" class="mh-widget mh-home-4 %2$s">', 'after_widget' => '</div>', 'before_title' => '<h4 class="mh-widget-title"><span class="mh-widget-title-inner">', 'after_title' => '</span></h4>'));
		register_sidebar(array('name' => sprintf(esc_html_x('Home %d - 2/3 Width', 'widget area name', 'jmd-magandanow-new'), 5), 'id' => 'home-5', 'description' => esc_html__('Widget area on homepage.', 'jmd-magandanow-new'), 'before_widget' => '<div id="%1$s" class="mh-widget mh-home-5 mh-widget-col-2 %2$s">', 'after_widget' => '</div>', 'before_title' => '<h4 class="mh-widget-title"><span class="mh-widget-title-inner">', 'after_title' => '</span></h4>'));
		register_sidebar(array('name' => sprintf(esc_html_x('Home %d - 1/3 Width', 'widget area name', 'jmd-magandanow-new'), 6), 'id' => 'home-6', 'description' => esc_html__('Widget area on homepage.', 'jmd-magandanow-new'), 'before_widget' => '<div id="%1$s" class="mh-widget mh-home-6 %2$s">', 'after_widget' => '</div>', 'before_title' => '<h4 class="mh-widget-title"><span class="mh-widget-title-inner">', 'after_title' => '</span></h4>'));
		register_sidebar(array('name' => sprintf(esc_html_x('Posts %d - Advertisement', 'widget area name', 'jmd-magandanow-new'), 1), 'id' => 'posts-1', 'description' => esc_html__('Widget area above single post content.', 'jmd-magandanow-new'), 'before_widget' => '<div id="%1$s" class="mh-widget mh-posts-1 %2$s">', 'after_widget' => '</div>', 'before_title' => '<h4 class="mh-widget-title"><span class="mh-widget-title-inner">', 'after_title' => '</span></h4>'));
		register_sidebar(array('name' => sprintf(esc_html_x('Posts %d - Advertisement', 'widget area name', 'jmd-magandanow-new'), 2), 'id' => 'posts-2', 'description' => esc_html__('Widget area below single post content.', 'jmd-magandanow-new'), 'before_widget' => '<div id="%1$s" class="mh-widget mh-posts-2 %2$s">', 'after_widget' => '</div>', 'before_title' => '<h4 class="mh-widget-title"><span class="mh-widget-title-inner">', 'after_title' => '</span></h4>'));
		register_sidebar(array('name' => sprintf(esc_html_x('Footer %d - 1/4 Width', 'widget area name', 'jmd-magandanow-new'), 1), 'id' => 'footer-1', 'description' => esc_html__('Widget area in footer.', 'jmd-magandanow-new'), 'before_widget' => '<div id="%1$s" class="mh-footer-widget %2$s">', 'after_widget' => '</div>', 'before_title' => '<h6 class="mh-widget-title mh-footer-widget-title"><span class="mh-widget-title-inner mh-footer-widget-title-inner">', 'after_title' => '</span></h6>'));
		register_sidebar(array('name' => sprintf(esc_html_x('Footer %d - 1/4 Width', 'widget area name', 'jmd-magandanow-new'), 2), 'id' => 'footer-2', 'description' => esc_html__('Widget area in footer.', 'jmd-magandanow-new'), 'before_widget' => '<div id="%1$s" class="mh-footer-widget %2$s">', 'after_widget' => '</div>', 'before_title' => '<h6 class="mh-widget-title mh-footer-widget-title"><span class="mh-widget-title-inner mh-footer-widget-title-inner">', 'after_title' => '</span></h6>'));
		register_sidebar(array('name' => sprintf(esc_html_x('Footer %d - 1/4 Width', 'widget area name', 'jmd-magandanow-new'), 3), 'id' => 'footer-3', 'description' => esc_html__('Widget area in footer.', 'jmd-magandanow-new'), 'before_widget' => '<div id="%1$s" class="mh-footer-widget %2$s">', 'after_widget' => '</div>', 'before_title' => '<h6 class="mh-widget-title mh-footer-widget-title"><span class="mh-widget-title-inner mh-footer-widget-title-inner">', 'after_title' => '</span></h6>'));
		register_sidebar(array('name' => sprintf(esc_html_x('Footer %d - 1/4 Width', 'widget area name', 'jmd-magandanow-new'), 4), 'id' => 'footer-4', 'description' => esc_html__('Widget area in footer.', 'jmd-magandanow-new'), 'before_widget' => '<div id="%1$s" class="mh-footer-widget %2$s">', 'after_widget' => '</div>', 'before_title' => '<h6 class="mh-widget-title mh-footer-widget-title"><span class="mh-widget-title-inner mh-footer-widget-title-inner">', 'after_title' => '</span></h6>'));
	}
}
add_action('widgets_init', 'jmd_magandanow_new_widgets_init');

/***** Include Several Functions *****/

if (is_admin()) {
	require_once('admin/admin.php');
}

require_once('includes/mh-customizer.php');
require_once('includes/mh-widgets.php');
require_once('includes/mh-custom-functions.php');
require_once('includes/mh-compatibility.php');

/***** Add Support for WooCommerce *****/

include_once(BASED_TREE_URI . 'mcms-admin/includes/module.php');

if (is_module_active('woocommerce/woocommerce.php')) {
	require_once('woocommerce/mh-custom-woocommerce.php');
}

?>