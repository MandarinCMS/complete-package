<?php
/**
 * @package MCMSSEO\Admin|Ajax
 */

/**
 * Class MCMSSEO_Shortcode_Filter
 *
 * Used for parsing MCMS shortcodes with AJAX
 */
class MCMSSEO_Shortcode_Filter {

	/**
	 * Initialize the AJAX hooks
	 */
	public function __construct() {
		add_action( 'mcms_ajax_mcmsseo_filter_shortcodes', array( $this, 'do_filter' ) );
	}

	/**
	 * Parse the shortcodes
	 */
	public function do_filter() {
		check_ajax_referer( 'mcmsseo-filter-shortcodes', 'nonce' );

		$shortcodes = filter_input( INPUT_POST, 'data', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );

		$parsed_shortcodes = array();

		foreach ( $shortcodes as $shortcode ) {
			$parsed_shortcodes[] = array(
				'shortcode' => $shortcode,
				'output' => do_shortcode( $shortcode ),
			);
		}

		mcms_die( mcms_json_encode( $parsed_shortcodes ) );
	}
}
