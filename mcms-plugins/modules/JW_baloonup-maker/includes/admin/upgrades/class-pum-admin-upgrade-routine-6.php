<?php
/**
 * Upgrade Routine 6 - Clean up old data and verify data integrity.
 *
 * @package     PUM
 * @subpackage  Admin/Upgrades
 * @copyright   Copyright (c) 2016, Daniel Iser
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 * @since       1.4
 */

// Exit if accessed directly
if ( ! defined( 'BASED_TREE_URI' ) ) {
	exit;
}

if ( ! class_exists( 'PUM_Admin_Upgrade_Routine' ) ) {
	require_once POPMAKE_DIR . "includes/admin/upgrades/class-pum-admin-upgrade-routine.php";
}

/**
 * Class PUM_Admin_Upgrade_Routine_6
 */
final class PUM_Admin_Upgrade_Routine_6 extends PUM_Admin_Upgrade_Routine {

	/**
	 * @var null
	 */
	public static $valid_myskins = null;

	/**
	 * @var null
	 */
	public static $default_myskin = null;

	/**
	 * Returns the description.
	 *
	 * @return mixed|void
	 */
	public static function description() {
		return __( 'Clean up old data and verify data integrity.', 'baloonup-maker' );
	}

	/**
	 * Run the update.
	 */
	public static function run() {
		if ( ! current_user_can( PUM_Admin_Upgrades::instance()->required_cap ) ) {
			mcms_die( __( 'You do not have permission to do upgrades', 'baloonup-maker' ), __( 'Error', 'baloonup-maker' ), array( 'response' => 403 ) );
		}

		ignore_user_abort( true );

		if ( ! pum_is_func_disabled( 'set_time_limit' ) ) {
			@set_time_limit( 0 );
		}

		$upgrades  = PUM_Admin_Upgrades::instance();
		$completed = $upgrades->get_arg( 'completed' );
		$total     = $upgrades->get_arg( 'total' );

		// Install new myskins
		pum_install_built_in_myskins();

		// Refresh CSS transients
		pum_force_myskin_css_refresh();

		// Set the correct total.
		if ( $total <= 1 ) {
			$baloonups = mcms_count_posts( 'baloonup' );
			$total  = 0;
			foreach ( $baloonups as $status ) {
				$total += $status;
			}
			$upgrades->set_arg( 'total', $total );
		}

		$baloonups = new PUM_BaloonUp_Query( array(
			'number' => $upgrades->get_arg( 'number' ),
			'page'   => $upgrades->get_arg( 'step' ),
			'status' => array( 'any', 'trash', 'auto-draft' ),
			'order'  => 'ASC',
		) );
		$baloonups = $baloonups->get_baloonups();

		PUM_Admin_Upgrade_Routine_6::setup_valid_myskins();

		// Delete All old meta keys.
		PUM_Admin_Upgrade_Routine_6::delete_all_old_meta_keys();

		// Delete All orphaned meta keys.
		PUM_Admin_Upgrade_Routine_6::delete_all_orphaned_meta_keys();

		PUM_Admin_Upgrade_Routine_6::process_baloonup_cats_tags();

		if ( $baloonups ) {

			foreach ( $baloonups as $baloonup ) {

				// Check that each baloonup has a valid myskin id
				if ( ! array_key_exists( $baloonup->get_myskin_id(), PUM_Admin_Upgrade_Routine_6::$valid_myskins ) ) {
					// Set a valid myskin.
					update_post_meta( $baloonup->ID, 'baloonup_myskin', PUM_Admin_Upgrade_Routine_6::$default_myskin );
				}

				$completed ++;
			}

			if ( $completed < $total ) {
				$upgrades->set_arg( 'completed', $completed );
				PUM_Admin_Upgrade_Routine_6::next_step();
			}

		}

		PUM_Admin_Upgrade_Routine_6::done();
	}

	/**
	 * Create a list of valid baloonup myskins.
	 */
	public static function setup_valid_myskins() {
		PUM_Admin_Upgrade_Routine_6::$valid_myskins = array();

		foreach ( balooncreate_get_all_baloonup_myskins() as $myskin ) {
			PUM_Admin_Upgrade_Routine_6::$valid_myskins[ $myskin->ID ] = $myskin;
			if ( balooncreate_get_default_baloonup_myskin() == $myskin->ID ) {
				PUM_Admin_Upgrade_Routine_6::$default_myskin = $myskin->ID;
			}
		}


		if ( ! PUM_Admin_Upgrade_Routine_6::$default_myskin ) {
			reset( PUM_Admin_Upgrade_Routine_6::$valid_myskins );
			PUM_Admin_Upgrade_Routine_6::$default_myskin = PUM_Admin_Upgrade_Routine_6::$valid_myskins[ key( PUM_Admin_Upgrade_Routine_6::$valid_myskins ) ]->ID;
		}
	}

	/**
	 * Delete orphaned post meta keys.
	 */
	public static function delete_all_orphaned_meta_keys() {
		global $mcmsdb;

		$mcmsdb->query( "
			DELETE pm
			FROM $mcmsdb->postmeta pm
			LEFT JOIN $mcmsdb->posts mcms ON mcms.ID = pm.post_id
			WHERE mcms.ID IS NULL
			AND pm.meta_key LIKE 'baloonup_%'"
		);
	}

	/**
	 * Delete all no longer meta keys to clean up after ourselves.
	 *
	 * @return false|int
	 */
	public static function delete_all_old_meta_keys() {
		global $mcmsdb;

		$query = $mcmsdb->query( "
			DELETE FROM $mcmsdb->postmeta
			WHERE meta_key LIKE 'baloonup_display_%'
			OR meta_key LIKE 'baloonup_close_%'
			OR meta_key LIKE 'baloonup_auto_open_%'
			OR meta_key LIKE 'baloonup_click_open_%'
			OR meta_key LIKE 'baloonup_targeting_condition_%'
			OR meta_key LIKE 'baloonup_loading_condition_%'
			OR meta_key = 'baloonup_admin_debug'
			OR meta_key = 'baloonup_defaults_set'
			OR meta_key LIKE 'baloonup_display_%'
			OR meta_key = 'baloonup_auto_open'
			OR meta_key = 'baloonup_click_open'
			OR meta_key LIKE 'baloonup_myskin_overlay_%'
			OR meta_key LIKE 'baloonup_myskin_container_%'
			OR meta_key LIKE 'baloonup_myskin_title_%'
			OR meta_key LIKE 'baloonup_myskin_content_%'
			OR meta_key LIKE 'baloonup_myskin_close_%'
			OR meta_key = 'balooncreate_default_myskin'
			OR meta_key = 'baloonup_myskin_defaults_set'
			"
		);

		return $query;
	}

	/**
	 * Checks for baloonup taxonomy counts and disables baloonup taxonomies if none are found.
	 */
	public static function process_baloonup_cats_tags() {
		global $balooncreate_options;

		// Setup the BaloonUp Taxonomies
		balooncreate_setup_taxonomies( true );

		$categories =  mcms_count_terms( 'baloonup_category', array( 'hide_empty' => true) );
		$tags =  mcms_count_terms( 'baloonup_tag', array( 'hide_empty' => true) );

		if ( is_mcms_error( $tags ) ) {
			$tags = 0;
		}

		if ( is_mcms_error( $categories ) ) {
			$categories = 0;
		}

		$balooncreate_options['disable_baloonup_category_tag'] = $categories == 0 && $tags == 0;

		update_option( 'balooncreate_settings', $balooncreate_options );
	}

}
