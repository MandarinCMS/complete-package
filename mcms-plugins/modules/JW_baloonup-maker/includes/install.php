<?php
/**
 * Install Function
 *
 * @package    POPMAKE
 * @subpackage  Functions/Install
 * @copyright   Copyright (c) 2014, Daniel Iser
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since        1.0
 */

// Exit if accessed directly
if ( ! defined( 'BASED_TREE_URI' ) ) {
	exit;
}

/**
 * Install Default mySkin
 *
 * Installs the default myskin and updates the option.
 *
 * @since 1.0
 * @return void
 */
function balooncreate_install_default_myskin() {
	$default_myskin = @mcms_insert_post(
		array(
			'post_title'     => __( 'Default mySkin', 'baloonup-maker' ),
			'post_status'    => 'publish',
			'post_author'    => 1,
			'post_type'      => 'baloonup_myskin',
			'comment_status' => 'closed',
			'meta_input' => array(
				'_pum_built_in' => 'default-myskin',
				'_pum_default_myskin' => true
			),
		)
	);
	foreach ( balooncreate_get_baloonup_myskin_default_meta() as $meta_key => $meta_value ) {
		update_post_meta( $default_myskin, $meta_key, $meta_value );
	}
	update_option( 'balooncreate_default_myskin', $default_myskin );
	pum_force_myskin_css_refresh();
}


/**
 * Post-installation
 *
 * Runs just after module installation and exposes the
 * balooncreate_after_install hook.
 *
 * @since 1.0
 * @return void
 */
function balooncreate_after_install() {

	if ( ! is_admin() ) {
		return;
	}

	// Exit if not in admin or the transient doesn't exist
	if ( false === get_transient( '_balooncreate_installed' ) ) {
		return;
	}

	// Delete the transient
	delete_transient( '_balooncreate_installed' );

	do_action( 'balooncreate_after_install' );
}
add_action( 'admin_init', 'balooncreate_after_install' );