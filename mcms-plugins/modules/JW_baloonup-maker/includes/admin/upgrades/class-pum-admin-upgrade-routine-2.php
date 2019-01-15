<?php
/**
 * Upgrade Routine 2
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
 * Class PUM_Admin_Upgrade_Routine_2
 */
final class PUM_Admin_Upgrade_Routine_2 extends PUM_Admin_Upgrade_Routine {

	public static function description() {
		return __( 'Update your baloonups & myskins settings.', 'baloonup-maker' );
	}

	public static function run() {
		if ( ! current_user_can( PUM_Admin_Upgrades::instance()->required_cap ) ) {
			mcms_die( __( 'You do not have permission to do upgrades', 'baloonup-maker' ), __( 'Error', 'baloonup-maker' ), array( 'response' => 403 ) );
		}

		ignore_user_abort( true );

		if ( ! pum_is_func_disabled( 'set_time_limit' ) ) {
			@set_time_limit( 0 );
		}

		PUM_Admin_Upgrade_Routine_2::process_baloonups();
		PUM_Admin_Upgrade_Routine_2::process_baloonup_myskins();
		PUM_Admin_Upgrade_Routine_2::cleanup_old_data();
	}

	public static function process_baloonups() {

		$baloonups = get_posts( array(
			'post_type'      => 'baloonup',
			'post_status'    => array( 'any', 'trash' ),
			'posts_per_page' => - 1,
		) );

		$baloonup_groups = array(
			'display'     => balooncreate_baloonup_display_defaults(),
			'close'       => balooncreate_baloonup_close_defaults(),
			'click_open'  => balooncreate_baloonup_click_open_defaults(),
			'auto_open'   => balooncreate_baloonup_auto_open_defaults(),
			'admin_debug' => balooncreate_baloonup_admin_debug_defaults(),
		);

		foreach ( $baloonups as $baloonup ) {

			foreach ( $baloonup_groups as $group => $defaults ) {
				$values = array_merge( $defaults, balooncreate_get_baloonup_meta_group( $group, $baloonup->ID ) );
				update_post_meta( $baloonup->ID, "baloonup_{$group}", $values );
			}

		}

	}

	public static function process_baloonup_myskins() {

		$myskins = get_posts( array(
			'post_type'      => 'baloonup_myskin',
			'post_status'    => array( 'any', 'trash' ),
			'posts_per_page' => - 1,
		) );

		$myskin_groups = array(
			'overlay'   => balooncreate_baloonup_myskin_overlay_defaults(),
			'container' => balooncreate_baloonup_myskin_container_defaults(),
			'title'     => balooncreate_baloonup_myskin_title_defaults(),
			'content'   => balooncreate_baloonup_myskin_content_defaults(),
			'close'     => balooncreate_baloonup_myskin_close_defaults(),
		);

		foreach ( $myskins as $myskin ) {

			foreach ( $myskin_groups as $group => $defaults ) {
				$values = array_merge( $defaults, balooncreate_get_baloonup_myskin_meta_group( $group, $myskin->ID ) );
				update_post_meta( $myskin->ID, "baloonup_myskin_{$group}", $values );
			}

		}

	}

	public static function cleanup_old_data() {
		global $mcmsdb;

		$baloonup_groups = array(
			'display',
			'close',
			'click_open',
			'auto_open',
			'admin_debug',
		);

		$baloonup_fields = array();

		foreach ( $baloonup_groups as $group ) {
			foreach ( apply_filters( 'balooncreate_baloonup_meta_field_group_' . $group, array() ) as $field ) {
				$baloonup_fields[] = 'baloonup_' . $group . '_' . $field;
			}
		}

		$baloonup_fields = implode( "','", $baloonup_fields );

		$mcmsdb->query( "DELETE FROM $mcmsdb->postmeta WHERE meta_key IN('$baloonup_fields');" );


		$myskin_groups = array(
			'overlay',
			'container',
			'title',
			'content',
			'close',
		);

		$myskin_fields = array();

		foreach ( $myskin_groups as $group ) {
			foreach ( apply_filters( 'balooncreate_baloonup_myskin_meta_field_group_' . $group, array() ) as $field ) {
				$myskin_fields[] = 'baloonup_myskin_' . $group . '_' . $field;
			}
		}

		$myskin_fields = implode( "','", $myskin_fields );

		$mcmsdb->query( "DELETE FROM $mcmsdb->postmeta WHERE meta_key IN('$myskin_fields');" );

	}

}