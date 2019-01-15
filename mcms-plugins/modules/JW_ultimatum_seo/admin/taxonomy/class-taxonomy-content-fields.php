<?php
/**
 * @package MCMSSEO\Admin
 */

/**
 * This class parses all the values for the general tab in the Ultimatum SEO settings metabox
 */
class MCMSSEO_Taxonomy_Content_Fields extends MCMSSEO_Taxonomy_Fields {

	/**
	 * Returns array with the fields for the general tab
	 *
	 * @return array
	 */
	public function get() {
		$fields = array(
			'snippet' => $this->get_field_config(
				__( 'Snippet editor', 'mandarincms-seo' ),
				'',
				'snippetpreview',
				array(
					'help-button' => __( 'Snippet Editor Help', 'mandarincms-seo' ),
					'help'        => sprintf( __( 'This is a rendering of what this post might look like in Google\'s search results. %sLearn more about the Snippet Preview%s.', 'mandarincms-seo' ), '<a target="_blank" href="https://jiiworks.net/snippet-preview">', '</a>' ),
				)
			),
			'focuskw' => $this->get_field_config(
				__( 'Focus keyword', 'mandarincms-seo' ),
				'',
				'focuskeyword',
				array(
					'help-button' => __( 'Focus Keyword Help', 'mandarincms-seo' ),
					'help'        => sprintf( __( 'Pick the main keyword or keyphrase that this post/page is about. %sLearn more about the Focus Keyword%s.', 'mandarincms-seo' ), '<a target="_blank" href="https://jiiworks.net/focus-keyword">', '</a>' ),
				)
			),
			'analysis' => $this->get_field_config(
				__( 'Analysis', 'mandarincms-seo' ),
				'',
				'pageanalysis',
				array(
					'help-button' => __( 'Content Analysis Help', 'mandarincms-seo' ),
					'help'        => sprintf( __( 'This is the content analysis, a collection of content checks that analyze the content of your page. %sLearn more about the Content Analysis Tool%s.', 'mandarincms-seo' ), '<a target="_blank" href="https://jiiworks.net/content-analysis">', '</a>' ),
				)
			),
			'title' => $this->get_field_config(
				'',
				'',
				'hidden',
				''
			),
			'desc' => $this->get_field_config(
				'',
				'',
				'hidden',
				''
			),
			'linkdex' => $this->get_field_config(
				'',
				'',
				'hidden',
				''
			),
			'content_score' => $this->get_field_config(
				'',
				'',
				'hidden',
				''
			),
		);

		return $this->filter_hidden_fields( $fields );
	}
}
