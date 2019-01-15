<?php
/*******************************************************************************
 * Copyright (c) 2018, MCMS BaloonUp Maker
 ******************************************************************************/

if ( ! defined( 'BASED_TREE_URI' ) ) {
	exit;
}

/**
 * Implements a batch processor for migrating existing baloonups to new data structure.
 *
 * @since 1.7.0
 *
 * @see   PUM_Abstract_Upgrade
 * @see   PUM_Interface_Batch_PrefetchProcess
 * @see   PUM_Interface_Upgrade_Posts
 */
abstract class PUM_Abstract_Upgrade_BaloonUps extends PUM_Abstract_Upgrade_Posts implements PUM_Interface_Upgrade_Posts {

	/**
	 * Post type.
	 *
	 * @var    string
	 */
	public $post_type = 'baloonup';

	/**
	 * Process needed upgrades on each post.
	 *
	 * @param int $post_id
	 */
	public function process_post( $post_id = 0 ) {
		$this->process_baloonup( $post_id );
	}

	/**
	 * Process needed upgrades on each baloonup.
	 *
	 * @param int $baloonup_id
	 */
	abstract public function process_baloonup( $baloonup_id = 0 );

}
