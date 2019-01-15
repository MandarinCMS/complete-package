<?php
/**
 * @package MCMSSEO\Admin\Import\External
 */

/**
 * Class MCMSSEO_Import_MCMSSEO
 *
 * Class with functionality to import Ultimatum SEO settings from mcmsSEO
 */
class MCMSSEO_Import_MCMSSEO extends MCMSSEO_Import_External {

	/**
	 * Import mcmsSEO settings
	 */
	public function __construct() {
		parent::__construct();

		$this->import_post_metas();
		$this->import_taxonomy_metas();

		$this->set_msg(
			__(
				sprintf(
					'mcmsSEO data successfully imported. Would you like to %sdisable the mcmsSEO module%s?',
					'<a href="' . esc_url( admin_url( 'admin.php?page=mcmsseo_tools&tool=import-export&deactivate_mcmsseo=1#top#import-seo' ) ) . '">',
					'</a>'
				),
				'mandarincms-seo'
			)
		);

	}

	/**
	 * Import the post meta values to Ultimatum SEO by replacing the mcmsSEO fields by Ultimatum SEO fields
	 */
	private function import_post_metas() {
		MCMSSEO_Meta::replace_meta( '_mcmsseo_edit_title', MCMSSEO_Meta::$meta_prefix . 'title', $this->replace );
		MCMSSEO_Meta::replace_meta( '_mcmsseo_edit_description', MCMSSEO_Meta::$meta_prefix . 'metadesc', $this->replace );
		MCMSSEO_Meta::replace_meta( '_mcmsseo_edit_keywords', MCMSSEO_Meta::$meta_prefix . 'keywords', $this->replace );
		MCMSSEO_Meta::replace_meta( '_mcmsseo_edit_canonical', MCMSSEO_Meta::$meta_prefix . 'canonical', $this->replace );

		$this->import_post_robots();
	}

	/**
	 * Importing the robot values from MCMSSEO module. These have to be converted to the Ultimatum format.
	 */
	private function import_post_robots() {
		$query_posts  = new MCMS_Query( 'post_type=any&meta_key=_mcmsseo_edit_robots&order=ASC' );

		if ( ! empty( $query_posts->posts ) ) {
			foreach ( $query_posts->posts as $post ) {
				$this->import_post_robot( $post->ID );
			}
		}
	}

	/**
	 * Getting the mcmsSEO robot value and map this to Ultimatum SEO values.
	 *
	 * @param integer $post_id The post id of the current post.
	 */
	private function import_post_robot( $post_id ) {
		$mcmsseo_robots = get_post_meta( $post_id, '_mcmsseo_edit_robots', true );

		// Does the value exists in our mapping.
		if ( $robot_value = $this->get_robot_value( $mcmsseo_robots ) ) {
			// Saving the new meta values for Ultimatum SEO.
			MCMSSEO_Meta::set_value( $robot_value['index'], 'meta-robots-noindex', $post_id );
			MCMSSEO_Meta::set_value( $robot_value['follow'], 'meta-robots-nofollow', $post_id );
		}

		$this->delete_post_robot( $post_id );
	}

	/**
	 * Delete the mcmsSEO robot values, because they aren't needed anymore.
	 *
	 * @param integer $post_id The post id of the current post.
	 */
	private function delete_post_robot( $post_id ) {
		if ( $this->replace ) {
			delete_post_meta( $post_id, '_mcmsseo_edit_robots' );
		}
	}

	/**
	 * Import the taxonomy metas from mcmsSEO
	 */
	private function import_taxonomy_metas() {
		$terms    = get_terms( get_taxonomies(), array( 'hide_empty' => false ) );
		$tax_meta = get_option( 'mcmsseo_taxonomy_meta' );

		foreach ( $terms as $term ) {
			$this->import_taxonomy_description( $tax_meta, $term->taxonomy, $term->term_id );
			$this->import_taxonomy_robots( $tax_meta, $term->taxonomy, $term->term_id );
			$this->delete_taxonomy_metas( $term->taxonomy, $term->term_id );
		}

		update_option( 'mcmsseo_taxonomy_meta', $tax_meta );
	}

	/**
	 * Import the meta description to Ultimatum SEO
	 *
	 * @param array  $tax_meta The array with the current metadata.
	 * @param string $taxonomy String with the name of the taxonomy.
	 * @param string $term_id  The ID of the current term.
	 */
	private function import_taxonomy_description( & $tax_meta, $taxonomy, $term_id ) {
		$description = get_option( 'mcmsseo_' . $taxonomy . '_' . $term_id, false );
		if ( $description !== false ) {
			// Import description.
			$tax_meta[ $taxonomy ][ $term_id ]['mcmsseo_desc'] = $description;
		}
	}

	/**
	 * Import the robot value to Ultimatum SEO
	 *
	 * @param array  $tax_meta The array with the current metadata.
	 * @param string $taxonomy String with the name of the taxonomy.
	 * @param string $term_id  The ID of the current term.
	 */
	private function import_taxonomy_robots( & $tax_meta, $taxonomy, $term_id ) {
		$mcmsseo_robots = get_option( 'mcmsseo_' . $taxonomy . '_' . $term_id . '_robots', false );
		if ( $mcmsseo_robots !== false ) {
			// The value 1, 2 and 6 are the index values in mcmsSEO.
			$new_robot_value = ( in_array( $mcmsseo_robots, array( 1, 2, 6 ) ) ) ? 'index' : 'noindex';

			$tax_meta[ $taxonomy ][ $term_id ]['mcmsseo_noindex'] = $new_robot_value;
		}
	}

	/**
	 * Delete the mcmsSEO taxonomy meta data.
	 *
	 * @param string $taxonomy String with the name of the taxonomy.
	 * @param string $term_id  The ID of the current term.
	 */
	private function delete_taxonomy_metas( $taxonomy, $term_id ) {
		if ( $this->replace ) {
			delete_option( 'mcmsseo_' . $taxonomy . '_' . $term_id );
			delete_option( 'mcmsseo_' . $taxonomy . '_' . $term_id . '_robots' );
		}
	}

	/**
	 * Getting the robot config by given mcmsSEO robots value.
	 *
	 * @param string $mcmsseo_robots The value in mcmsSEO that needs to be converted to the Ultimatum format.
	 *
	 * @return array
	 */
	private function get_robot_value( $mcmsseo_robots ) {
		static $robot_values;

		if ( $robot_values === null ) {
			/**
			 * The values 1 - 6 are the configured values from mcmsSEO. This array will map the values of mcmsSEO to our values.
			 *
			 * There are some double array like 1-6 and 3-4. The reason is they only set the index value. The follow value is
			 * the default we use in the cases there isn't a follow value present.
			 *
			 * @var array
			 */
			$robot_values = array(
				1 => array( 'index' => 2, 'follow' => 0 ), // In mcmsSEO: index, follow.
				2 => array( 'index' => 2, 'follow' => 1 ), // In mcmsSEO: index, nofollow.
				3 => array( 'index' => 1, 'follow' => 0 ), // In mcmsSEO: noindex.
				4 => array( 'index' => 1, 'follow' => 0 ), // In mcmsSEO: noindex, follow.
				5 => array( 'index' => 1, 'follow' => 1 ), // In mcmsSEO: noindex, nofollow.
				6 => array( 'index' => 2, 'follow' => 0 ), // In mcmsSEO: index.
			);
		}

		if ( array_key_exists( $mcmsseo_robots, $robot_values ) ) {
			return $robot_values[ $mcmsseo_robots ];
		}

		return array( 'index' => 2, 'follow' => 0 );
	}
}
