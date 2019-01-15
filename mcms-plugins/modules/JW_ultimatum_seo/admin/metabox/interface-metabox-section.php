<?php
/**
 * @package MCMSSEO\Admin
 */

/**
 * Generates and displays the HTML for a metabox section.
 */
interface MCMSSEO_Metabox_Section {

	/**
	 * Outputs the section link.
	 */
	public function display_link();

	/**
	 * Outputs the section content.
	 */
	public function display_content();
}
