<?php

/*******************************************************************************
 * Copyright (c) 2017, MCMS BaloonUp Maker
 ******************************************************************************/

if ( ! defined( 'BASED_TREE_URI' ) ) {
	exit;
}

/**
 * Class Post_Types
 */
class PUM_BaloonUps {

	/**
	 * Hook the initialize method to the MCMS init action.
	 */
	public static function init() {
		add_filter( 'pum_baloonup_content', array( $GLOBALS['mcms_embed'], 'run_shortcode' ), 8 );
		add_filter( 'pum_baloonup_content', array( $GLOBALS['mcms_embed'], 'autoembed' ), 8 );
		add_filter( 'pum_baloonup_content', 'mcmstexturize', 10 );
		add_filter( 'pum_baloonup_content', 'convert_smilies', 10 );
		add_filter( 'pum_baloonup_content', 'convert_chars', 10 );
		add_filter( 'pum_baloonup_content', 'mcmsautop', 10 );
		add_filter( 'pum_baloonup_content', 'shortcode_unautop', 10 );
		add_filter( 'pum_baloonup_content', 'prepend_attachment', 10 );
		add_filter( 'pum_baloonup_content', 'force_balance_tags', 10 );
		add_filter( 'pum_baloonup_content', 'do_shortcode', 11 );
		add_filter( 'pum_baloonup_content', 'capital_P_dangit', 11 );
	}


	public static function get_all() {
		static $query;

		if ( ! isset( $query ) ) {
			$query = self::query();
		}

		return $query;
	}

	public static function query( $args = array() ) {
		$args = mcms_parse_args( $args, array(
			'post_type'      => 'baloonup',
			'posts_per_page' => - 1,
		) );

		return new MCMS_Query( $args );
	}

}
