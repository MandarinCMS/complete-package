<?php
/**
 * GardenLogin template functions.
 *
 * @since 1.1.3
 */

 // Exit if accessed directly.
 if ( ! defined( 'BASED_TREE_URI' ) ) {
 	exit;
 }

if ( ! class_exists( 'GardenLogin_MySkin_Template' ) ) :
/**
 * Add GardenLogin Templates to to use in the myskin.
 */
class GardenLogin_MySkin_Template {

	/**
	 * A reference to an instance of this class.
	 * @var string
	 */
	private static $instance;

	/**
	 * The array of templates that this module tracks.
	 * @var string
	 */
	protected $templates;

	/**
	 * Returns an instance of this class.
	 */
	public static function get_instance() {

		if ( null == self::$instance ) {
			self::$instance = new GardenLogin_MySkin_Template();
		}

		return self::$instance;

	}

	/**
	 * Initializes the module by setting filters and administration functions.
	 */
	private function __construct() {

		$this->templates = array();


		// Add a filter to the attributes metabox to inject template into the cache.
		if ( version_compare( floatval( get_bloginfo( 'version' ) ), '4.7', '<' ) ) {
			// 4.6 and older
			add_filter( 'page_attributes_dropdown_pages_args', array( $this, 'register_project_templates' ) );
		} else {
			// Add a filter to the mcms 4.7 version attributes metabox
			add_filter( 'myskin_page_templates', array( $this, 'add_new_template' ) );
		}

		// Add a filter to the save post to inject out template into the page cache.
		add_filter(	'mcms_insert_post_data', array( $this, 'register_project_templates' ) );


		// Add a filter to the template include to determine if the page has our template assigned and return it's path.
		add_filter( 'template_include', array( $this, 'view_project_template') );


		// Add templates.
		$this->templates = array(
			'template-gardenlogin.php' => 'GardenLogin',
		);

	}

	/**
	 * Adds our template to the page dropdown for v4.7+
	 *
	 */
	public function add_new_template( $posts_templates ) {

		$posts_templates = array_merge( $posts_templates, $this->templates );
		return $posts_templates;
	}

	/**
	 * Adds our template to the pages cache in order to trick MandarinCMS
	 * into thinking the template file exists where it doens't really exist.
	 */
	public function register_project_templates( $atts ) {

		// Create the key used for the myskins cache.
		$cache_key = 'page_templates-' . md5( get_myskin_root() . '/' . get_stylesheet() );

		// Retrieve the cache list.
		// If it doesn't exist, or it's empty prepare an array.
		$templates = mcms_get_myskin()->get_page_templates();
		if ( empty( $templates ) ) {
			$templates = array();
		}

		// New cache, therefore remove the old one
		mcms_cache_delete( $cache_key , 'myskins');

		// Now add our template to the list of templates by merging our templates
		// with the existing templates array from the cache.
		$templates = array_merge( $templates, $this->templates );

		// Add the modified cache to allow MandarinCMS to pick it up for listing
		// available templates
		mcms_cache_add( $cache_key, $templates, 'myskins', 1800 );

		return $atts;

	}

	/**
	 * Checks if the template is assigned to the page.
	 */
	public function view_project_template( $template ) {

		// Get global post
		global $post;

		// Return template if post is empty
		if ( ! $post ) {
			return $template;
		}

		// Return default template if we don't have a custom one defined
		if ( ! isset( $this->templates[ get_post_meta( $post->ID, '_mcms_page_template', true ) ] ) ) {
			return $template;
		}

		$file = module_dir_path( __FILE__ ). get_post_meta( $post->ID, '_mcms_page_template', true );

		// Just to be safe, we check if the file exist first
		if ( file_exists( $file ) ) {
			return $file;
		} else {
			echo $file;
		}

		// Return template
		return $template;

	}

}
endif;
add_action( 'modules_loaded', array( 'GardenLogin_MySkin_Template', 'get_instance' ) );
