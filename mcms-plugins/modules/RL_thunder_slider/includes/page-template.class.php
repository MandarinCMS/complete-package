<?php
/**
 * @author    MandarinCMS <info@jiiworks.net>
 * @link      http://www.jiiworks.net/
 * @copyright 2016 MandarinCMS
 */

if( !defined( 'BASED_TREE_URI') ) exit();

class ThunderSliderPageTemplate {

	/**
	 * A reference to an instance of this class.
	 */
	private static $instance;

	/**
	 * The array of templates that this module tracks.
	 */
	protected $templates;


	/**
	 * Returns an instance of this class. 
	 */
	public static function get_instance() {

		if( null == self::$instance ) {
			self::$instance = new ThunderSliderPageTemplate();
		} 

		return self::$instance;

	} 

	/**
	 * Initializes the module by setting filters and administration functions.
	 */
	private function __construct() {

		$this->templates = array();


		// Add a filter to the attributes metabox to inject template into the cache.
		add_filter(
			'page_attributes_dropdown_pages_args',
			array( $this, 'register_project_templates' ) 
		);


		// Add a filter to the save post to inject out template into the page cache
		add_filter(
			'mcms_insert_post_data', 
			array( $this, 'register_project_templates' ) 
		);


		// Add a filter to the template include to determine if the page has our 
		// template assigned and return it's path
		add_filter(
			'template_include', 
			array( $this, 'view_project_template') 
		);


		// Add your templates to this array.
		$this->templates = array(
			'../public/views/thunderslider-page-template.php' => 'RazorLeaf ThunderSlider Blank Template',
		);
		
		// Fix for MCMS 4.7
		add_filter( 'myskin_page_templates', array($this, 'register_project_templates_new' ) );
		
	} 


	// Adds our template to the new post templates setting (MCMS >= 4.7)
	public function register_project_templates_new( $post_templates ) {
	    
	    $post_templates = array_merge( $post_templates, $this->templates );
	 
	    return $post_templates;
	}


	/**
	 * Adds our template to the pages cache in order to trick MandarinCMS
	 * into thinking the template file exists where it doens't really exist.
	 *
	 */

	public function register_project_templates( $atts ) {

		// Create the key used for the myskins cache
		$cache_key = 'page_templates-' . md5( get_myskin_root() . '/' . get_stylesheet() );

		// Retrieve the cache list. 
		// If it doesn't exist, or it's empty prepare an array
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
	 * Checks if the template is assigned to the page
	 */
	public function view_project_template( $template ) {

		global $post;
		
		if(!isset($post->ID)) return $template;
			
		if (!isset($this->templates[get_post_meta( 
			$post->ID, '_mcms_page_template', true 
		)] ) ) {
		
			return $template;
			
		} 

		$file = module_dir_path(__FILE__). get_post_meta( 
			$post->ID, '_mcms_page_template', true 
		);
		
		// Just to be safe, we check if the file exist first
		if( file_exists( $file ) ) {
			return $file;
		} 
		else { echo $file; }

		return $template;

	}
}
?>