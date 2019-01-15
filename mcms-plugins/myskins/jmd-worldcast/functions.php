<?php
/**
 * JMD World-casts functions and definitions
 *
 * @link https://developer.mandarincms.org/myskins/basics/myskin-functions/
 *
 * @package JMD_MandarinCMS
 */

if ( ! function_exists( 'jmd_worldcasts_setup' ) ) :
	/**
	 * Sets up myskin defaults and registers support for various MandarinCMS features.
	 *
	 * Note that this function is hooked into the after_setup_myskin hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support for post thumbnails.
	 */
	function jmd_worldcasts_setup() {
		/*
		 * Make myskin available for translation.
		 * Translations can be filed in the /languages/ directory.
		 * If you're building a myskin based on JMD Worldcasts, use a find and replace
		 * to change 'jmd-worldcast' to the name of your myskin in all the template files.
		 */
		load_myskin_textdomain( 'jmd-worldcast', get_template_directory() . '/languages' );

		// Add default posts and comments RSS feed links to head.
		add_myskin_support( 'automatic-feed-links' );

		/*
		 * Let MandarinCMS manage the document title.
		 * By adding myskin support, we declare that this myskin does not use a
		 * hard-coded <title> tag in the document head, and expect MandarinCMS to
		 * provide it for us.
		 */
		add_myskin_support( 'title-tag' );

		/*
		 * Enable support for Post Thumbnails on posts and pages.
		 *
		 * @link https://developer.mandarincms.org/myskins/functionality/featured-images-post-thumbnails/
		 */
		add_myskin_support( 'post-thumbnails' );
		add_image_size('jmd-worldcast-blog-post', 730, 485, true);

		add_myskin_support('post-formats', array('video'));

		// This myskin uses mcms_nav_menu() in one location.
		register_nav_menus( array(
			'menu-1' => esc_html__( 'Header Menu', 'jmd-worldcast' ),
		) );

		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
		add_myskin_support( 'html5', array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
		) );

		// Set up the MandarinCMS core custom background feature.
		add_myskin_support( 'custom-background', apply_filters( 'jmd_worldcasts_custom_background_args', array(
			'default-color' => 'ffffff',
			'default-image' => '',
		) ) );

		// Add myskin support for selective refresh for widgets.
		add_myskin_support( 'customize-selective-refresh-widgets' );

		/**
		 * Add support for core custom logo.
		 *
		 * @link https://codex.mandarincms.org/Theme_Logo
		 */
		add_myskin_support( 'custom-logo', array(
				'height'      => 128,
				'width'       => 274,
				'flex-height' => true,
				'flex-width'  => true,
		) );
	}
endif;
add_action( 'after_setup_myskin', 'jmd_worldcasts_setup' );

/**
 * Set the content width in pixels, based on the myskin's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function jmd_worldcasts_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'jmd_worldcasts_content_width', 640 );
}
add_action( 'after_setup_myskin', 'jmd_worldcasts_content_width', 0 );

/**
 * Register widget area.
 *
 * @link https://developer.mandarincms.org/myskins/functionality/sidebars/#registering-a-sidebar
 */
function jmd_worldcasts_widgets_init() {
	register_sidebar( array(
		'name'          => esc_html__( 'Sidebar Left', 'jmd-worldcast' ),
		'id'            => 'sidebar-widget',
		'description'   => esc_html__( 'Add widgets here.', 'jmd-worldcast' ),
		'before_widget' => '<div id="%1$s" class="%2$s sidebar-widget">',
		'after_widget' => '</div>',
		'before_title' => '<div class="widget-title">',
		'after_title' => '</div>',
	));

	register_sidebar( array(
		'name'          => esc_html__( 'Sidebar Right', 'jmd-worldcast' ),
		'id'            => 'sidebar-widget2',
		'description'   => esc_html__( 'Add widgets here.', 'jmd-worldcast' ),
		'before_widget' => '<div id="%1$s" class="%2$s sidebar-widget">',
		'after_widget' => '</div>',
		'before_title' => '<div class="widget-title">',
		'after_title' => '</div>',
	));

	register_sidebar( array(
		'name'          => esc_html__( 'Footer', 'jmd-worldcast' ),
		'id'            => 'footer-widget',
		'description'   => esc_html__( 'Add widgets here.', 'jmd-worldcast' ),
		'before_widget' => '<div id="%1$s" class="%2$s footer-widget">',
		'after_widget' => '</div>',
		'before_title' => '<div class="footer-widget-title">',
		'after_title' => '</div>',
	));

	register_sidebar( array(
		'name'          => esc_html__( 'Top ADS Block', 'jmd-worldcast' ),
		'id'            => 'ads-widget1',
		'description'   => esc_html__( 'Add widgets here.', 'jmd-worldcast' ),
				'before_widget' => '<div id="%1$s" class="%2$s top-ads-widget">',
				'after_widget' => '</div>',
				'before_title' => '<div class="widget-title">',
				'after_title' => '</div>',
	));

}
add_action( 'widgets_init', 'jmd_worldcasts_widgets_init' );

/**
 * Enqueue scripts and styles.
 */
function jmd_worldcasts_scripts() {

	mcms_enqueue_style( 'jmd-worldcast-myskin-google-font-open', '//fonts.googleapis.com/css?family=Oswald:400,700|Roboto:400,700', null, null );

	mcms_register_style('font-awesome', get_template_directory_uri() . '/js/lib/font-awesome/css/font-awesome.min.css', array(), '4.7.0', 'all');
	mcms_enqueue_style('font-awesome');

  mcms_register_style('swiper', get_template_directory_uri() . '/js/lib/swiper/css/swiper.min.css', array(), '4.1.0', 'all');
  mcms_enqueue_style('swiper'); // Enqueue it!

	mcms_enqueue_style( 'jmd-worldcast-style', get_stylesheet_uri() );

	mcms_register_script('swiper', get_template_directory_uri() . '/js/lib/swiper/js/swiper.js', array('jquery'), '4.1.0');
  mcms_enqueue_script('swiper'); // Enqueue it!

	mcms_register_script('jmd-worldcast-myskin-script', get_template_directory_uri() . '/js/scripts.js', array('jquery'), '1.0.0'); // Custom scripts
	mcms_enqueue_script('jmd-worldcast-myskin-script'); // Enqueue it!

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		mcms_enqueue_script( 'comment-reply' );
	}
}
add_action( 'mcms_enqueue_scripts', 'jmd_worldcasts_scripts' );

/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this myskin.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Functions which enhance the myskin by hooking into MandarinCMS.
 */
require get_template_directory() . '/inc/template-functions.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

// /**
//  * TGM_Module_Activation class.
//  */
require_once get_template_directory() . '/inc/tgm-module-activation/class-tgm-module-activation.php';

add_action( 'tgmpa_register', 'jmd_worldcasts_register_required_modules' );
function jmd_worldcasts_register_required_modules() {
	$modules = array(
		array(
			'name'      => 'Recent Posts Widget With Thumbnails',
			'slug'      => 'recent-posts-widget-with-thumbnails',
			'required'  => false,
		),
	);

	$config = array(
		'id'           => 'jmd-worldcast',                 // Unique ID for hashing notices for multiple instances of TGMPA.
		'default_path' => '',                      // Default absolute path to bundled modules.
		'menu'         => 'tgmpa-install-modules', // Menu slug.
		'has_notices'  => true,                    // Show admin notices or not.
		'dismissable'  => true,                    // If false, a user cannot dismiss the nag message.
		'dismiss_msg'  => '',                      // If 'dismissable' is false, this message will be output at top of nag.
		'is_automatic' => false,                   // Automatically activate modules after installation or not.
		'message'      => '',                      // Message to output right before the modules table.
	);

	tgmpa( $modules, $config );
}