<?php
/**
 * @package MCMSSEO\Admin
 */

/**
 * Class MCMSSEO_Premium_popup
 */
class MCMSSEO_Premium_Popup {
	/**
	 * An unique identifier for the popup
	 *
	 * @var string
	 */
	private $identifier = '';

	/**
	 * The heading level of the title of the popup.
	 *
	 * @var String
	 */
	private $heading_level = '';

	/**
	 * The title of the popup
	 *
	 * @var String
	 */
	private $title = '';

	/**
	 * The content of the popup
	 *
	 * @var String
	 */
	private $content = '';

	/**
	 * Wpseo_Premium_Popup constructor.
	 *
	 * @param String $identifier    An unique identifier for the popup.
	 * @param String $heading_level The heading level for the title of the popup.
	 * @param String $title         The title of the popup.
	 * @param String $content       The content of the popup.
	 */
	public function __construct( $identifier, $heading_level, $title, $content ) {
		$this->identifier    = $identifier;
		$this->heading_level = $heading_level;
		$this->title         = $title;
		$this->content       = $content;
	}

	/**
	 * Returns the premium popup as an HTML string.
	 *
	 * @param bool $popup Show this message as a popup show it straight away.
	 *
	 * @return string
	 */
	public function get_premium_message( $popup = true ) {
		// Don't show in Premium.
		if ( defined( 'MCMSSEO_PREMIUM_FILE' ) ) {
			return '';
		}

		$assets_uri = trailingslashit( module_dir_url( MCMSSEO_FILE ) );
		$premium_uri = 'https://jiiworks.net/mandarincms/modules/seo-premium/#utm_source=mandarincms-seo-metabox&amp;utm_medium=popup&amp;utm_campaign=help-center-contact-support';

		/* translators: %s expands to Ultimatum SEO */
		$cta_text = sprintf( __( 'Buy %s', 'mandarincms-seo' ), 'Ultimatum SEO' );
		$classes = '';
		if ( $popup ) {
			$classes = ' hidden';
		}

		$popup = <<<EO_POPUP
<div id="mcmsseo-{$this->identifier}-popup" class="mcmsseo-premium-popup$classes">
	<img class="alignright mcmsseo-premium-popup-icon" src="{$assets_uri}images/Ultimatum_SEO_Icon.svg" width="150" height="150" alt="Ultimatum SEO"/>
	<{$this->heading_level} id="mcmsseo-contact-support-popup-title" class="mcmsseo-premium-popup-title">{$this->title}</{$this->heading_level}>
	<p>{$this->content}</p>
	<a id="mcmsseo-{$this->identifier}-popup-button" class="button button-primary" href="{$premium_uri}">{$cta_text}</a>
</div>
EO_POPUP;

		return $popup;
	}
}
