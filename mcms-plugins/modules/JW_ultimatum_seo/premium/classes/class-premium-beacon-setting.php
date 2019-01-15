<?php
/**
 * @package MCMSSEO\Admin
 */

/**
 * Implements the suggestions for Ultimatum SEO
 */
class MCMSSEO_Premium_Beacon_Setting implements Ultimatum_HelpScout_Beacon_Setting {
	/**
	 * Returns a list of helpscout hashes to show the user for a certain page.
	 *
	 * @param string $page The current admin page we are on.
	 *
	 * @return array A list of suggestions for the beacon
	 */
	public function get_suggestions( $page ) {
		switch ( $page ) {
			case 'mcmsseo_dashboard':
				return array(
					'543752a7e4b01a27d3c00010',
					// See: http://kb.jiiworks.net/article/164-how-to-connect-your-website-to-google-webmaster-tools.
					'5469dc20e4b0f6394183a0a5',
					// See: http://kb.jiiworks.net/article/183-microformats-and-schema-org.
				);
			case 'mcmsseo_titles':
				return array(
					'53a820b7e4b02b018b783607',
					// See: http://kb.jiiworks.net/article/146-ultimatum-mandarincms-seo-titles-metas-template-variables.
					'537f0723e4b0fe61cc352111',
					// See: http://kb.jiiworks.net/article/107-google-shows-different-titles-for-my-site.
					'5375f937e4b0d833740d57ac',
					// See: http://kb.jiiworks.net/article/75-im-not-seeing-a-meta-description-in-my-head-section.
				);
			case 'mcmsseo_social':
				return array(
					'54cc3f0de4b034c37ea8c722',
					// See: http://kb.jiiworks.net/article/219-getting-open-graph-for-your-articles.
					'556df3e2e4b01a224b428375',
					// See: http://kb.jiiworks.net/article/254-gaining-access-to-facebook-insights.
					'53c4f5a9e4b085fad945d02d',
					// See: http://kb.jiiworks.net/article/147-setting-up-twitter-cards-in-mandarincms-seo.
				);
			case 'mcmsseo_xml':
				return array(
					'5375e852e4b03c6512282d5a',
					// See: http://kb.jiiworks.net/article/36-my-sitemap-is-blank-what-s-wrong.
					'5375f58ce4b03c6512282d98',
					// See: http://kb.jiiworks.net/article/58-xml-sitemap-error.
					'5375f9b6e4b0d833740d57bc',
					// See: http://kb.jiiworks.net/article/77-my-sitemap-index-is-giving-a-404-error-what-should-i-do.
				);
			case 'mcmsseo_advanced':
				return array(
					'55310094e4b0a2d7e23f5f13',
					// See: http://kb.jiiworks.net/article/245-implement-mandarincms-seo-breadcrumbs.
					'55ace6bfe4b03e788eda48a4',
					// See: http://kb.jiiworks.net/article/274-add-myskin-support-for-ultimatum-seo-breadcrumbs.
				);
			case 'mcmsseo_tools':
				return array(
					'5632ca35c697910ae05ef6cd',
					// See: http://kb.jiiworks.net/article/305-how-to-edit-htaccess-through-ultimatum-seo.
					'55b8ef7ae4b01fdb81eae86a',
					// See: http://kb.jiiworks.net/article/279-how-to-edit-robots-txt-through-ultimatum-seo.
					'53a0a63de4b0aa24c5341503',
					// See: http://kb.jiiworks.net/article/141-if-your-robots-txt-were-writeable-error.
				);
			case 'mcmsseo_search_console':
				return array(
					'5632d2adc697910ae05ef6da',
					// See: http://kb.jiiworks.net/article/306-how-to-connect-and-retrieve-crawl-issues.
					'53a1903fe4b0295576d0c7a0',
					// See: http://kb.jiiworks.net/article/142-what-are-regex-redirects.
					'5375f3f9e4b0d833740d5760',
					// See: http://kb.jiiworks.net/article/51-import-redirects.
				);

			case 'mcmsseo_redirects':
				return array( '5385c1c9e4b06542b1a212e2', '55c2b57ee4b01fdb81eb0de7' );
		}

		return array();
	}

	/**
	 * Returns a product for a a certain admin page.
	 *
	 * @param string $page The current admin page we are on.
	 *
	 * @return Ultimatum_Product[] A product to use for sending data to helpscout
	 */
	public function get_products( $page ) {
		switch ( $page ) {
			case 'mcmsseo_dashboard':
			case 'mcmsseo_titles':
			case 'mcmsseo_social':
			case 'mcmsseo_xml':
			case 'mcmsseo_advanced':
			case 'mcmsseo_tools':
			case 'mcmsseo_search_console':
				return array( new MCMSSEO_Product_Premium() );
		}

		return array();
	}


	/**
	 * Returns a list of config values for a a certain admin page.
	 *
	 * @param string $page The current admin page we are on.
	 *
	 * @return array A list with configuration for the beacon
	 */
	public function get_config( $page ) {
		return array( 'modal' => true );
	}
}
