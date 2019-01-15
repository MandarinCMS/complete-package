<?php
/**
 * @package MCMSSEO\Admin
 */

/**
 * Generates the HTML for a metabox tab.
 */
interface MCMSSEO_Metabox_Tab {

	/**
	 * Returns the html for the tab link.
	 *
	 * @return string
	 */
	public function link();

	/**
	 * Returns the html for the tab content.
	 *
	 * @return string
	 */
	public function content();
}
