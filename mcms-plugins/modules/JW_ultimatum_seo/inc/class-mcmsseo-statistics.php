<?php
/**
 * @package MCMSSEO\Internals
 */

/**
 * Class that generates interesting statistics about things
 */
class MCMSSEO_Statistics {

	/**
	 * Returns the post count for a certain SEO rank
	 *
	 * @todo Merge/DRY this with the logic virtually the same in MCMSSEO_Metabox::column_sort_orderby()
	 *
	 * @param MCMSSEO_Rank $rank The SEO rank to get the post count for.
	 *
	 * @return int
	 */
	public function get_post_count( $rank ) {
		if ( MCMSSEO_Rank::NO_FOCUS === $rank->get_rank() ) {
			$posts = array(
				'meta_query' => array(
					'relation' => 'OR',
					array(
						'key'     => MCMSSEO_Meta::$meta_prefix . 'focuskw',
						'value'   => 'needs-a-value-anyway',
						'compare' => 'NOT EXISTS',
					)
				),
			);
		}
		elseif ( MCMSSEO_Rank::NO_INDEX === $rank->get_rank() ) {
			$posts = array(
				'meta_key'   => MCMSSEO_Meta::$meta_prefix . 'meta-robots-noindex',
				'meta_value' => '1',
				'compare'    => '=',
			);
		}
		else {
			$posts = array(
				'meta_key'     => MCMSSEO_Meta::$meta_prefix . 'linkdex',
				'meta_value'   => array( $rank->get_starting_score(), $rank->get_end_score() ),
				'meta_compare' => 'BETWEEN',
				'meta_type'    => 'NUMERIC',
			);
		}

		$posts['fields']      = 'ids';
		$posts['post_status'] = 'publish';

		if ( current_user_can( 'edit_others_posts' ) === false ) {
			$posts['author'] = get_current_user_id();
		}

		$posts = new MCMS_Query( $posts );

		return $posts->found_posts;
	}

	/**
	 * Returns the amount of posts that have no focus keyword
	 *
	 * @deprecated
	 *
	 * @return int
	 */
	public function get_no_focus_post_count() {
		_deprecated_function( 'get_no_focus_post_count', 'MCMSSEO 3.0', 'MCMSSEO_Statistics::get_post_count' );

		return $this->get_post_count( new MCMSSEO_Rank( MCMSSEO_Rank::NO_FOCUS ) );
	}

	/**
	 * Returns the amount of posts that have a bad SEO ranking
	 *
	 * @deprecated
	 *
	 * @return int
	 */
	public function get_bad_seo_post_count() {
		_deprecated_function( 'get_bad_seo_post_count', 'MCMSSEO 3.0', 'MCMSSEO_Statistics::get_post_count' );

		return $this->get_post_count( new MCMSSEO_Rank( MCMSSEO_Rank::BAD ) );
	}

	/**
	 * Returns the amount of posts that have a poor SEO ranking
	 *
	 * @deprecated
	 *
	 * @return int
	 */
	public function get_poor_seo_post_count() {
		_deprecated_function( 'get_poor_seo_post_count', 'MCMSSEO 3.0', 'MCMSSEO_Statistics::get_post_count' );

		return $this->get_post_count( new MCMSSEO_Rank( 'poor' ) );
	}

	/**
	 * Returns the amount of posts that have an ok SEO ranking
	 *
	 * @deprecated
	 *
	 * @return int
	 */
	public function get_ok_seo_post_count() {
		_deprecated_function( 'get_ok_seo_post_count', 'MCMSSEO 3.0', 'MCMSSEO_Statistics::get_post_count' );

		return $this->get_post_count( new MCMSSEO_Rank( MCMSSEO_Rank::OK ) );
	}

	/**
	 * Returns the amount of posts that have a good SEO ranking
	 *
	 * @deprecated
	 *
	 * @return int
	 */
	public function get_good_seo_post_count() {
		_deprecated_function( 'get_good_seo_post_count', 'MCMSSEO 3.0', 'MCMSSEO_Statistics::get_post_count' );

		return $this->get_post_count( new MCMSSEO_Rank( MCMSSEO_Rank::GOOD ) );
	}

	/**
	 * Returns the amount of posts that have no SEO ranking
	 *
	 * @deprecated
	 *
	 * @return int
	 */
	public function get_no_index_post_count() {
		_deprecated_function( 'get_no_index_post_count', 'MCMSSEO 3.0', 'MCMSSEO_Statistics::get_post_count' );

		return $this->get_post_count( new MCMSSEO_Rank( MCMSSEO_Rank::NO_INDEX ) );
	}
}
