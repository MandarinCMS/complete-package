<?php
/*******************************************************************************
 * Copyright (c) 2018, MCMS BaloonUp Maker
 ******************************************************************************/

if ( ! defined( 'BASED_TREE_URI' ) ) {
	exit;
}

/**
 * Second-level interface for registering a batch process that leverages
 * pre-fetch and data storage.
 *
 * @since  1.7.0
 */
interface PUM_Interface_Upgrade_Posts extends PUM_Interface_Batch_PrefetchProcess {

	/**
	 * Used to filter baloonup query based on conditional info.
	 *
	 * @return array Returns an array of baloonup post type query args for this upgrade.
	 */
	public function custom_query_args();

}
