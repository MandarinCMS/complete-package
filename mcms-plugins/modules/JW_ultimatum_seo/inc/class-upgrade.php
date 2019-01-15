<?php
/**
 * @package    MCMSSEO
 * @subpackage Internal
 */

/**
 * This code handles the option upgrades
 */
class MCMSSEO_Upgrade {

	/**
	 * Holds the Ultimatum SEO options
	 *
	 * @var array
	 */
	private $options = array();

	/**
	 * Class constructor
	 */
	public function __construct() {
		$this->options = MCMSSEO_Options::get_option( 'mcmsseo' );

		MCMSSEO_Options::maybe_set_multisite_defaults( false );

		if ( version_compare( $this->options['version'], '1.5.0', '<' ) ) {
			$this->upgrade_15( $this->options['version'] );
		}

		if ( version_compare( $this->options['version'], '2.0', '<' ) ) {
			$this->upgrade_20();
		}

		if ( version_compare( $this->options['version'], '2.1', '<' ) ) {
			$this->upgrade_21();
		}

		if ( version_compare( $this->options['version'], '2.2', '<' ) ) {
			$this->upgrade_22();
		}

		if ( version_compare( $this->options['version'], '2.3', '<' ) ) {
			$this->upgrade_23();
		}

		if ( version_compare( $this->options['version'], '3.0', '<' ) ) {
			$this->upgrade_30();
		}

		if ( version_compare( $this->options['version'], '3.3', '<' ) ) {
			$this->upgrade_33();
		}

		if ( version_compare( $this->options['version'], '3.6', '<' ) ) {
			$this->upgrade_36();
		}

		// Since 3.7.
		$upsell_notice = new MCMSSEO_Product_Upsell_Notice();
		$upsell_notice->set_upgrade_notice();

		/**
		 * Filter: 'mcmsseo_run_upgrade' - Runs the upgrade hook which are dependent on Ultimatum SEO
		 *
		 * @deprecated Since 3.1
		 *
		 * @api        string - The current version of Ultimatum SEO
		 */
		do_action( 'mcmsseo_run_upgrade', $this->options['version'] );

		$this->finish_up();
	}

	/**
	 * Run the Ultimatum SEO 1.5 upgrade routine
	 *
	 * @param string $version Current module version.
	 */
	private function upgrade_15( $version ) {
		// Clean up options and meta.
		MCMSSEO_Options::clean_up( null, $version );
		MCMSSEO_Meta::clean_up();

		// Add new capabilities on upgrade.
		mcmsseo_add_capabilities();
	}

	/**
	 * Moves options that moved position in MCMSSEO 2.0
	 */
	private function upgrade_20() {
		/**
		 * Clean up stray mcmsseo_ms options from the options table, option should only exist in the sitemeta table.
		 * This could have been caused in many version of Ultimatum SEO, so deleting it for everything below 2.0
		 */
		delete_option( 'mcmsseo_ms' );

		$this->move_pinterest_option();
	}

	/**
	 * Detects if taxonomy terms were split and updates the corresponding taxonomy meta's accordingly.
	 */
	private function upgrade_21() {
		$taxonomies = get_option( 'mcmsseo_taxonomy_meta', array() );

		if ( ! empty( $taxonomies ) ) {
			foreach ( $taxonomies as $taxonomy => $tax_metas ) {
				foreach ( $tax_metas as $term_id => $tax_meta ) {
					if ( function_exists( 'mcms_get_split_term' ) && $new_term_id = mcms_get_split_term( $term_id, $taxonomy ) ) {
						$taxonomies[ $taxonomy ][ $new_term_id ] = $taxonomies[ $taxonomy ][ $term_id ];
						unset( $taxonomies[ $taxonomy ][ $term_id ] );
					}
				}
			}

			update_option( 'mcmsseo_taxonomy_meta', $taxonomies );
		}
	}

	/**
	 * Performs upgrade functions to Ultimatum SEO 2.2
	 */
	private function upgrade_22() {
		// Unschedule our tracking.
		mcms_clear_scheduled_hook( 'ultimatum_tracking' );

		// Clear the tracking settings, the seen about setting and the ignore tour setting.
		$options = get_option( 'mcmsseo' );
		unset( $options['tracking_popup_done'], $options['ultimatum_tracking'], $options['seen_about'], $options['ignore_tour'] );
		update_option( 'mcmsseo', $options );
	}

	/**
	 * Schedules upgrade function to Ultimatum SEO 2.3
	 */
	private function upgrade_23() {
		add_action( 'mcms', array( $this, 'upgrade_23_query' ), 90 );
		add_action( 'admin_head', array( $this, 'upgrade_23_query' ), 90 );
	}

	/**
	 * Performs upgrade query to Ultimatum SEO 2.3
	 */
	public function upgrade_23_query() {
		$mcms_query = new MCMS_Query( 'post_type=any&meta_key=_ultimatum_mcmsseo_sitemap-include&meta_value=never&order=ASC' );

		if ( ! empty( $mcms_query->posts ) ) {
			$options = get_option( 'mcmsseo_xml' );

			$excluded_posts = array();
			if ( $options['excluded-posts'] !== '' ) {
				$excluded_posts = explode( ',', $options['excluded-posts'] );
			}

			foreach ( $mcms_query->posts as $post ) {
				if ( ! in_array( $post->ID, $excluded_posts ) ) {
					$excluded_posts[] = $post->ID;
				}
			}

			// Updates the meta value.
			$options['excluded-posts'] = implode( ',', $excluded_posts );

			// Update the option.
			update_option( 'mcmsseo_xml', $options );
		}

		// Remove the meta fields.
		delete_post_meta_by_key( '_ultimatum_mcmsseo_sitemap-include' );
	}

	/**
	 * Performs upgrade functions to Ultimatum SEO 3.0
	 */
	private function upgrade_30() {
		// Remove the meta fields for sitemap prio.
		delete_post_meta_by_key( '_ultimatum_mcmsseo_sitemap-prio' );
	}

	/**
	 * Performs upgrade functions to Ultimatum SEO 3.3
	 */
	private function upgrade_33() {
		// Notification dismissals have been moved to User Meta instead of global option.
		delete_option( Ultimatum_Notification_Center::STORAGE_KEY );
	}

	/**
	 * Performs upgrade functions to Ultimatum SEO 3.6
	 */
	private function upgrade_36() {
		global $mcmsdb;

		// Between 3.2 and 3.4 the sitemap options were saved with autoloading enabled.
		$mcmsdb->query( 'DELETE FROM ' . $mcmsdb->options . ' WHERE option_name LIKE "mcmsseo_sitemap_%" AND autoload = "yes"' );
	}

	/**
	 * Move the pinterest verification option from the mcmsseo option to the mcmsseo_social option
	 */
	private function move_pinterest_option() {
		$options_social = get_option( 'mcmsseo_social' );

		if ( isset( $option_mcmsseo['pinterestverify'] ) ) {
			$options_social['pinterestverify'] = $option_mcmsseo['pinterestverify'];
			unset( $option_mcmsseo['pinterestverify'] );
			update_option( 'mcmsseo_social', $options_social );
			update_option( 'mcmsseo', $option_mcmsseo );
		}
	}

	/**
	 * Runs the needed cleanup after an update, setting the DB version to latest version, flushing caches etc.
	 */
	private function finish_up() {
		$this->options = MCMSSEO_Options::get_option( 'mcmsseo' );              // Re-get to make sure we have the latest version.
		update_option( 'mcmsseo', $this->options );                           // This also ensures the DB version is equal to MCMSSEO_VERSION.

		add_action( 'shutdown', 'flush_rewrite_rules' );                    // Just flush rewrites, always, to at least make them work after an upgrade.
		MCMSSEO_Sitemaps_Cache::clear();                                 // Flush the sitemap cache.

		MCMSSEO_Options::ensure_options_exist();                              // Make sure all our options always exist - issue #1245.
	}
}
