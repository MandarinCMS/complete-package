<?php
// Exit if accessed directly

/*******************************************************************************
 * Copyright (c) 2017, MCMS BaloonUp Maker
 ******************************************************************************/

if ( ! defined( 'BASED_TREE_URI' ) ) {
	exit;
}

/**
 * @see balooncreate_baloonup_meta_box_save
 *
 * @param $post_id
 * @param $post
 */
function pum_deprecated_save_baloonup_action( $post_id, $post ) {
	if ( has_action( 'balooncreate_save_baloonup' ) ) {
		PUM_Logging::instance()->log_deprecated_notice( 'balooncreate_save_baloonup', '1.4', 'pum_save_baloonup' );
		/**
		 * Calls old save action.
		 *
		 * @deprecated 1.4
		 *
		 * @param int   $post_id $post Post ID.
		 * @param array $post    Sanitized $_POST variable.
		 */
		do_action( 'balooncreate_save_baloonup', $post_id, $post );
	}
}
add_action( 'pum_save_baloonup', 'pum_deprecated_save_baloonup_action', 10, 2 );


/**
 * Applies the deprecated balooncreate_baloonup_is_loadable filter.
 *
 * @see PUM_BaloonUp->is_loadable()
 *
 * @param $loadable
 * @param $baloonup_id
 *
 * @return bool $loadable
 */
function pum_deprecated_baloonup_is_loadable_filter( $loadable, $baloonup_id ) {
	if ( has_filter( 'balooncreate_baloonup_is_loadable' ) ) {
		PUM_Logging::instance()->log_deprecated_notice( 'balooncreate_baloonup_is_loadable', '1.4', 'pum_baloonup_is_loadable' );
		/**
		 * Calls old filter.
		 *
		 * @deprecated 1.4
		 *
		 * @param bool  $loadable True if baloonup should load.
		 * @param array $baloonup_id Post ID.
		 */
		return apply_filters( 'balooncreate_baloonup_is_loadable', $loadable, $baloonup_id, array(), false );
	}

	return $loadable;
}
add_filter( 'pum_baloonup_is_loadable', 'pum_deprecated_baloonup_is_loadable_filter', 10, 2 );
