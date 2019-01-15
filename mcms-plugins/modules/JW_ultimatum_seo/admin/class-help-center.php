<?php
/**
 * @package MCMSSEO\Admin
 */

/**
 * Class MCMSSEO_Help_Center
 */
class MCMSSEO_Help_Center {
	/**
	 * @var array Help_Center_Item list of helpcenter tabs to be shown in the helpcenter
	 */
	private $help_center_items = array();

	/**
	 * @var String $group_name
	 */
	private $group_name;

	/**
	 * @var MCMSSEO_Option_Tab $tab
	 */
	private $tab;

	/**
	 * MCMSSEO_Help_Center constructor.
	 *
	 * @param String           $group_name The name of the group of the tab the helpcenter is on.
	 * @param MCMSSEO_Option_Tab $tab        The name of the tab the helpcenter is on.
	 */
	public function __construct( $group_name, $tab ) {
		$this->group_name = $group_name;
		$this->tab        = $tab;

		$this->add_video_tutorial_item();
		$this->add_kb_search_item();
		$this->add_contact_support_item();
	}

	/**
	 * Add the knowledge base search help center item to the help center.
	 */
	private function add_kb_search_item() {
		$kb_help_center_item = new MCMSSEO_Help_Center_Item(
			'knowledge-base',
			__( 'Knowledge base', 'mandarincms-seo' ),
			array(
				'content'        => '<div class="mcmsseo-kb-search"></div>',
				'view_arguments' => array( 'identifier' => $this->tab->get_name() ),
			),
			'dashicons-search'
		);
		array_push( $this->help_center_items, $kb_help_center_item );
	}

	/**
	 * Add the contact support help center item to the help center.
	 */
	private function add_contact_support_item() {
		$popup_title = sprintf( __( 'Email support is a %s feature', 'mandarincms-seo' ), 'Ultimatum SEO' );
		/* translators: %1$s: expands to 'Ultimatum SEO', %2$s: links to Ultimatum SEO module page. */
		$popup_content = sprintf( __( 'To be able to contact our support team, you need %1$s. You can buy the module, including one year of support, updates and upgrades, on %2$s.', 'mandarincms-seo' ),
			'<a href="https://jiiworks.net/mandarincms/modules/seo-premium/#utm_source=mandarincms-seo-metabox&utm_medium=popup&utm_campaign=multiple-keywords">Ultimatum SEO</a>',
			'jiiworks.net' );

		$premium_popup                    = new MCMSSEO_Premium_Popup( 'contact-support', 'h3', $popup_title, $popup_content );
		$contact_support_help_center_item = new MCMSSEO_Help_Center_Item(
			'contact-support',
			__( 'Email support', 'mandarincms-seo' ),
			array( 'content' => $premium_popup->get_premium_message( false ) ),
			'dashicons-email-alt'
		);

		array_push( $this->help_center_items, $contact_support_help_center_item );
	}

	/**
	 * Add the video tutorial help center item to the help center.
	 */
	private function add_video_tutorial_item() {
		array_push( $this->help_center_items, $this->get_video_help_center_item() );
	}

	/**
	 * @return mixed (MCMSSEO_Help_Center_Item | null) Returns a help center item containing a video.
	 */
	private function get_video_help_center_item() {
		$url = $this->tab->get_video_url();
		if ( empty( $url ) ) {
			return null;
		}

		return new MCMSSEO_Help_Center_Item(
			'video',
			__( 'Video tutorial', 'mandarincms-seo' ),
			array(
				'view'           => 'partial-help-center-video',
				'view_arguments' => array(
					'tab_video_url' => $url,
				),
			),
			'dashicons-video-alt3'
		);
	}

	/**
	 * Outputs the help center.
	 */
	public function output_help_center() {
		$help_center_items = apply_filters( 'mcmsseo_help_center_items', $this->help_center_items );
		$help_center_items = array_filter( $help_center_items, array( $this, 'is_a_help_center_item' ) );
		if ( empty( $help_center_items ) ) {
			return;
		}

		$id = sprintf( 'tab-help-center-%s-%s', $this->group_name, $this->tab->get_name() );
		?>
	 
		<?php
	}

	/**
	 * Checks if the param is a help center item.
	 *
	 * @param object $item The object compare.
	 *
	 * @return bool
	 */
	private function is_a_help_center_item( $item ) {
		return ( $item instanceof MCMSSEO_Help_Center_Item );
	}

	/**
	 * Pass text variables to js for the help center JS module.
	 *
	 * %s is replaced with <code>%s</code> and replaced again in the javascript with the actual variable.
	 *
	 * @return  array Translated text strings for the help center.
	 */
	public static function get_translated_texts() {
		return array(
			/* translators: %s: '%%term_title%%' variable used in titles and meta's template that's not compatible with the given template */
			'variable_warning' => sprintf( __( 'Warning: the variable %s cannot be used in this template. See the help center for more info.', 'mandarincms-seo' ), '<code>%s</code>' ),
			'locale' => get_locale(),
			/* translators: %d: number of knowledge base search results found. */
			'kb_found_results' => __( 'Number of search results: %d', 'mandarincms-seo' ),
			'kb_no_results' => __( 'No results found.', 'mandarincms-seo' ),
			'kb_heading' => __( 'Search the Ultimatum knowledge base', 'mandarincms-seo' ),
			'kb_search_button_text' => __( 'Search', 'mandarincms-seo' ),
			'kb_search_results_heading' => __( 'Search results', 'mandarincms-seo' ),
			'kb_error_message' => __( 'Something went wrong. Please try again later.', 'mandarincms-seo' ),
			'kb_loading_placeholder' => __( 'Loading...', 'mandarincms-seo' ),
			'kb_search' => __( 'search', 'mandarincms-seo' ),
			'kb_back' => __( 'Back', 'mandarincms-seo' ),
			'kb_back_label' => __( 'Back to search results' , 'mandarincms-seo' ),
			'kb_open' => __( 'Open', 'mandarincms-seo' ),
			'kb_open_label' => __( 'Open the knowledge base article in a new window or read it in the iframe below' , 'mandarincms-seo' ),
			'kb_iframe_title' => __( 'Knowledge base article', 'mandarincms-seo' ),
		);
	}
}
