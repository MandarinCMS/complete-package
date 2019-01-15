<?php
/**
 * @package MCMSSEO\Admin\Formatter
 */

/**
 * This class forces needed methods for the metabox localization
 */
class MCMSSEO_Metabox_Formatter {

	/**
	 * @var MCMSSEO_Metabox_Formatter_Interface Object that provides formatted values.
	 */
	private $formatter;

	/**
	 * Setting the formatter property.
	 *
	 * @param MCMSSEO_Metabox_Formatter_Interface $formatter Object that provides the formatted values.
	 */
	public function __construct( MCMSSEO_Metabox_Formatter_Interface $formatter ) {
		$this->formatter = $formatter;
	}

	/**
	 * Returns the values
	 *
	 * @return array
	 */
	public function get_values() {
		$defaults = $this->get_defaults();
		$values   = $this->formatter->get_values();

		return ( $values + $defaults );
	}

	/**
	 * Returns array with all the values always needed by a scraper object
	 *
	 * @return array
	 */
	private function get_defaults() {
		$analysis_seo = new MCMSSEO_Metabox_Analysis_SEO();
		$analysis_readability = new MCMSSEO_Metabox_Analysis_Readability();

		return array(
			'search_url'        => '',
			'post_edit_url'     => '',
			'base_url'          => '',
			'contentTab'        => __( 'Readability', 'mandarincms-seo' ),
			'keywordTab'        => __( 'Keyword:', 'mandarincms-seo' ),
			'enterFocusKeyword' => __( 'Enter your focus keyword', 'mandarincms-seo' ),
			'removeKeyword'     => __( 'Remove keyword', 'mandarincms-seo' ),
			'locale'            => get_locale(),
			'translations'      => $this->get_translations(),
			'keyword_usage'     => array(),
			'title_template'    => '',
			'metadesc_template' => '',
			'contentAnalysisActive' => $analysis_readability->is_enabled() ? 1 : 0,
			'keywordAnalysisActive' => $analysis_seo->is_enabled() ? 1 : 0,

			/**
			 * Filter to determine if the markers should be enabled or not.
			 *
			 * @param bool $showMarkers Should the markers being enabled. Default = true.
			 */
			'show_markers'      => apply_filters( 'mcmsseo_enable_assessment_markers', true ),
			'publish_box'       => array(
				'labels'   => array(
					'content' => __( 'Readability', 'mandarincms-seo' ),
					'keyword' => __( 'SEO', 'mandarincms-seo' ),
				),
				'statuses' => array(
					'na'   => __( 'Not available', 'mandarincms-seo' ),
					'bad'  => __( 'Needs improvement', 'mandarincms-seo' ),
					'ok'   => __( 'OK', 'mandarincms-seo' ),
					'good' => __( 'Good', 'mandarincms-seo' ),
				),
			),
		);

	}

	/**
	 * Returns Jed compatible UltimatumSEO.js translations.
	 *
	 * @return array
	 */
	private function get_translations() {
		$file = module_dir_path( MCMSSEO_FILE ) . 'languages/mandarincms-seo-' . get_locale() . '.json';
		if ( file_exists( $file ) && $file = file_get_contents( $file ) ) {
			return json_decode( $file, true );
		}

		return array();
	}
}
