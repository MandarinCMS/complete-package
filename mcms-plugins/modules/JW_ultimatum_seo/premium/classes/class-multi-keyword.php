<?php
/**
 * @package MCMSSEO\Premium\Classes
 */

/**
 * Implements multi keyword int he admin.
 */
class MCMSSEO_Multi_Keyword {
	/**
	 * Constructor. Adds MandarinCMS hooks.
	 */
	public function __construct() {
		add_filter( 'mcmsseo_metabox_entries_general', array( $this, 'add_focus_keywords_input' ) );
	}

	/**
	 * Add field in which we can save multiple keywords
	 *
	 * @param array $field_defs The current fields definitions.
	 *
	 * @return array Field definitions with our added field.
	 */
	public function add_focus_keywords_input( $field_defs ) {
		if ( is_array( $field_defs ) ) {
			$field_defs['focuskeywords'] = array(
				'type' => 'hidden',
				'title' => 'focuskeywords',
			);
		}

		return $field_defs;
	}
}
