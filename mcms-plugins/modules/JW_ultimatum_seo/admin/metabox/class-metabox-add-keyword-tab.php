<?php
/**
 * @package MCMSSEO\Admin\Metabox
 */

/**
 * Tab to add a keyword to analyze
 */
class Metabox_Add_Keyword_Tab implements MCMSSEO_Metabox_Tab {

	/**
	 * Returns a button because a link is inappropriate here
	 *
	 * @return string
	 */
	public function link() {

		// Ensure thickbox is enqueued.
		add_thickbox();

		ob_start();
		?>
		<li class="mcmsseo-tab-add-keyword">
			<button type="button" class="mcmsseo-add-keyword button">
				<span aria-hidden="true">+</span>
				<span class="screen-reader-text"><?php _e( 'Add keyword', 'mandarincms-seo' ); ?></span>
			</button>
		</li>

		<?php
		$popup_title = sprintf( __( 'Multiple focus keywords is a %s feature', 'mandarincms-seo' ), 'Ultimatum SEO' );
		/* translators: %1$s: expands to 'Ultimatum SEO', %2$s: links to Ultimatum SEO module page. */
		$popup_content       = sprintf( __( 'To be able to add and analyze multiple keywords for a post or page you need %1$s. You can buy the module, including one year of support, updates and upgrades, on %2$s.', 'mandarincms-seo' ),
			'<a href="https://jiiworks.net/mandarincms/modules/seo-premium/#utm_source=mandarincms-seo-metabox&utm_medium=popup&utm_campaign=multiple-keywords">Ultimatum SEO</a>',
			'jiiworks.net' );
		$premium_popup = new MCMSSEO_Premium_Popup( 'add-keyword', 'h1', $popup_title, $popup_content );
		echo $premium_popup->get_premium_message();

		return ob_get_clean();
	}

	/**
	 * Returns an empty string because this tab has no content
	 *
	 * @return string
	 */
	public function content() {
		return '';
	}
}
