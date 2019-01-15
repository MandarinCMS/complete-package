<?php 
/**
 * Mydog Lite functions and definitions
 *
 * @package Pizza Lite
 */
 global $content_width;
 if ( ! isset( $content_width ) )
	$content_width = 640; /* pixels */ 
/**
 * Set the content width based on the myskin's design and stylesheet.
 */
if ( ! function_exists( 'pizza_lite_setup' ) ) : 
/**
 * Sets up myskin defaults and registers support for various MandarinCMS features.
 *
 * Note that this function is hooked into the after_setup_myskin hook, which runs
 * before the init hook. The init hook is too late for some features, such as indicating
 * support post thumbnails.
 */
function pizza_lite_setup() {
	load_myskin_textdomain( 'pizza-lite', get_template_directory() . '/languages' );
	add_myskin_support( 'automatic-feed-links' );
	add_myskin_support('woocommerce');
	add_myskin_support( 'post-thumbnails' );
	add_myskin_support( 'title-tag' );
	add_post_type_support( 'page', 'excerpt' );
	add_myskin_support( 'wc-product-gallery-zoom' );
	add_myskin_support( 'wc-product-gallery-lightbox' );
	add_myskin_support( 'wc-product-gallery-slider' );
	add_myskin_support( 'custom-logo', array(
		'height'      => 50,
		'width'       => 250,
		'flex-height' => true,
	) );	
	register_nav_menus( array(
		'primary' => esc_html__( 'Primary Menu', 'pizza-lite' ),		
	) );
	add_myskin_support( 'custom-background', array(
		'default-color' => 'ffffff'
	) );
	add_editor_style( 'editor-style.css' );
} 
endif; // pizza_lite_setup
add_action( 'after_setup_myskin', 'pizza_lite_setup' );
function pizza_lite_widgets_init() { 	
	register_sidebar( array(
		'name'          => esc_html__( 'Blog Sidebar', 'pizza-lite' ),
		'description'   => esc_html__( 'Appears on blog page sidebar', 'pizza-lite' ),
		'id'            => 'sidebar-1',
		'before_widget' => '',		
		'before_title'  => '<h3 class="widget-title titleborder"><span>',
		'after_title'   => '</span></h3><aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
	) );
}
add_action( 'widgets_init', 'pizza_lite_widgets_init' );
function pizza_lite_font_url(){
		$font_url = '';		
		/* Translators: If there are any character that are not
		* supported by Roboto Condensed, trsnalate this to off, do not
		* translate into your own language.
		*/
		$robotocondensed = _x('on','Roboto Condensed:on or off','pizza-lite');		
		/* Translators: If there has any character that are not supported 
		*  by Scada, translate this to off, do not translate
		*  into your own language.
		*/
		$scada = _x('on','Scada:on or off','pizza-lite');	
		$lato = _x('on','Lato:on or off','pizza-lite');	
		$roboto = _x('on','Roboto:on or off','pizza-lite');
		$greatvibes = _x('on','Great Vibes:on or off','pizza-lite');
		$opensans = _x('on','Open Sans:on or off','pizza-lite');
		$assistant = _x('on','Assistant:on or off','pizza-lite');
		$pattaya = _x('on','Pattaya:on or off','pizza-lite');
		
		
		if('off' !== $robotocondensed ){
			$font_family = array();
			if('off' !== $robotocondensed){
				$font_family[] = 'Roboto Condensed:300,400,600,700,800,900';
			}
			if('off' !== $lato){
				$font_family[] = 'Lato:100,100i,300,300i,400,400i,700,700i,900,900i';
			}
			if('off' !== $roboto){
				$font_family[] = 'Roboto:100,100i,300,300i,400,400i,500,500i,700,700i,900,900i';
			}
			if('off' !== $greatvibes){
				$font_family[] = 'Great Vibes:400';
			}	
			if('off' !== $opensans){
				$font_family[] = 'Open Sans:300,300i,400,400i,600,600i,700,700i,800,800i';
			}	
			if('off' !== $assistant){
				$font_family[] = 'Assistant:200,300,400,600,700,800';
			}
			if('off' !== $pattaya){
				$font_family[] = 'Pattaya:400';
			}			
						
			$query_args = array(
				'family'	=> rawurlencode(implode('|',$font_family)),
			);
			$font_url = add_query_arg($query_args,'//fonts.googleapis.com/css');
		}
	return $font_url;
	}
function pizza_lite_scripts() {
	mcms_enqueue_style('pizza-lite-font', pizza_lite_font_url(), array());
	mcms_enqueue_style( 'pizza-lite-basic-style', get_stylesheet_uri() );
	mcms_enqueue_style( 'pizza-lite-editor-style', get_template_directory_uri()."/editor-style.css" );
	mcms_enqueue_style( 'nivo-slider', get_template_directory_uri()."/css/nivo-slider.css" );
	mcms_enqueue_style( 'pizza-lite-main-style', get_template_directory_uri()."/css/responsive.css" );		
	mcms_enqueue_style( 'pizza-lite-base-style', get_template_directory_uri()."/css/style_base.css" );
	mcms_enqueue_script( 'jquery-nivo', get_template_directory_uri() . '/js/jquery.nivo.slider.js', array('jquery') );
	mcms_enqueue_script( 'pizza-lite-custom-js', get_template_directory_uri() . '/js/custom.js' );	
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		mcms_enqueue_script( 'comment-reply' );
	}
}

add_action( 'mcms_enqueue_scripts', 'pizza_lite_scripts' );
define('PIZZA_LITE_SKTTHEMES_URL','https://www.sktmyskins.net','pizza-lite');
define('PIZZA_LITE_SKTTHEMES_PRO_THEME_URL','https://www.sktmyskins.net/shop/pizza-ordering-mandarincms-myskin/','pizza-lite');
define('PIZZA_LITE_SKTTHEMES_FREE_THEME_URL','https://www.sktmyskins.net/shop/free-pizza-mandarincms-myskin/','pizza-lite');
define('PIZZA_LITE_SKTTHEMES_THEME_DOC','http://sktmyskinsdemo.net/documentation/pizza-documentation/','pizza-lite');
define('PIZZA_LITE_SKTTHEMES_LIVE_DEMO','https://www.sktperfectdemo.com/demos/pizzaordering/','pizza-lite');
define('PIZZA_LITE_SKTTHEMES_THEMES','https://www.sktmyskins.net/myskins/','pizza-lite');

/**
 * Custom template for about myskin.
 */
require get_template_directory() . '/inc/about-myskins.php';
/**
 * Custom template tags for this myskin.
 */
require get_template_directory() . '/inc/template-tags.php';
/**
 * Custom functions that act independently of the myskin templates.
 */
require get_template_directory() . '/inc/extras.php';
/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';
// get slug by id
function pizza_lite_get_slug_by_id($id) {
	$post_data = get_post($id, ARRAY_A);
	$slug = $post_data['post_name'];
	return $slug; 
}
if ( ! function_exists( 'pizza_lite_the_custom_logo' ) ) :
/**
 * Displays the optional custom logo.
 *
 * Does nothing if the custom logo is not available.
 *
 */
function pizza_lite_the_custom_logo() {
	if ( function_exists( 'the_custom_logo' ) ) {
		the_custom_logo();
	}
}
endif;
require_once get_template_directory() . '/customize-pro/example-1/class-customize.php';
/**
 * Add a pingback url auto-discovery header for singularly identifiable articles.
 */
function pizza_lite_pingback_header() {
	if ( is_singular() && pings_open() ) {
		printf( '<link rel="pingback" href="%s">' . "\n", esc_html(get_bloginfo( 'pingback_url' ) ));
	}
}
add_action( 'mcms_head', 'pizza_lite_pingback_header' );
add_filter( 'body_class','pizza_lite_body_class' );
function pizza_lite_body_class( $classes ) {
 	$hideslide = get_myskin_mod('hide_slides', 1);
	if (!is_home() && is_front_page()) {
		if( $hideslide == '') {
			$classes[] = 'enableslide';
		}
	}
    return $classes;
}
/**
 * Filter the except length to 20 words.
 *
 * @param int $length Excerpt length.
 * @return int (Maybe) modified excerpt length.
 */
function pizza_lite_custom_excerpt_length( $excerpt_length ) {
    return 19;
}
add_filter( 'excerpt_length', 'pizza_lite_custom_excerpt_length', 999 );
/**
 *
 * Style For About MySkin Page
 *
 */
function pizza_lite_admin_about_page_css_enqueue($hook) {
   if ( 'appearance_page_pizza_lite_guide' != $hook ) {
        return;
    }
    mcms_enqueue_style( 'pizza-lite-about-page-style', get_template_directory_uri() . '/css/pizza-lite-about-page-style.css' );
}
add_action( 'admin_enqueue_scripts', 'pizza_lite_admin_about_page_css_enqueue' );