<?php
/**
 * @package MCMSSEO\Admin|Ajax
 */

/**
 * Class MCMSSEO_Recalculate_Scores
 *
 * This class handles the SEO score recalculation for all posts with a filled focus keyword
 */
class MCMSSEO_Recalculate_Scores_Ajax {

	/**
	 * Initialize the AJAX hooks
	 */
	public function __construct() {
		add_action( 'mcms_ajax_mcmsseo_recalculate_scores', array( $this, 'recalculate_scores' ) );
		add_action( 'mcms_ajax_mcmsseo_update_score', array( $this, 'save_score' ) );
		add_action( 'mcms_ajax_mcmsseo_recalculate_total', array( $this, 'get_total' ) );
	}

	/**
	 * Get the totals for the posts and the terms.
	 */
	public function get_total() {
		check_ajax_referer( 'mcmsseo_recalculate', 'nonce' );

		mcms_die(
			mcms_json_encode(
				array(
					'posts' => $this->calculate_posts(),
					'terms' => $this->calculate_terms(),
				)
			)
		);
	}

	/**
	 * Start recalculation
	 */
	public function recalculate_scores() {
		check_ajax_referer( 'mcmsseo_recalculate', 'nonce' );

		$fetch_object = $this->get_fetch_object();
		if ( ! empty( $fetch_object ) ) {
			$paged    = filter_input( INPUT_POST, 'paged', FILTER_VALIDATE_INT );
			$response = $fetch_object->get_items_to_recalculate( $paged );

			if ( ! empty( $response ) ) {
				mcms_die( mcms_json_encode( $response ) );
			}
		}

		mcms_die( '' );
	}

	/**
	 * Saves the new linkdex score for given post
	 */
	public function save_score() {
		check_ajax_referer( 'mcmsseo_recalculate', 'nonce' );

		$fetch_object = $this->get_fetch_object();
		if ( ! empty( $fetch_object ) ) {
			$scores = filter_input( INPUT_POST, 'scores', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
			$fetch_object->save_scores( $scores );
		}

		mcms_die();
	}

	/**
	 * Returns the needed object for recalculating scores.
	 *
	 * @return MCMSSEO_Recalculate_Posts|MCMSSEO_Recalculate_Terms
	 */
	private function get_fetch_object() {
		switch ( filter_input( INPUT_POST, 'type' ) ) {
			case 'post':
				return new MCMSSEO_Recalculate_Posts();
			case 'term':
				return new MCMSSEO_Recalculate_Terms();
		}

		return null;
	}

	/**
	 * Gets the total number of posts
	 *
	 * @return int
	 */
	private function calculate_posts() {
		$count_posts_query = new MCMS_Query(
			array(
				'post_type'      => 'any',
				'meta_key'       => '_ultimatum_mcmsseo_focuskw',
				'posts_per_page' => 1,
				'fields'         => 'ids',
			)
		);

		return $count_posts_query->found_posts;
	}

	/**
	 * Get the total number of terms
	 *
	 * @return int
	 */
	private function calculate_terms() {
		$total = 0;
		foreach ( get_taxonomies( array(), 'objects' ) as $taxonomy ) {
			$total += mcms_count_terms( $taxonomy->name, array( 'hide_empty' => false ) );
		}

		return $total;
	}
}
