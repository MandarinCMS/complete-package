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
 * @see PUM_Abstract_Upgrade_BaloonUps
 */
class PUM_Upgrade_v1_7_BaloonUps extends PUM_Abstract_Upgrade_BaloonUps {

	/**
	 * Batch process ID.
	 *
	 * @var    string
	 */
	public $batch_id = 'core-v1_7-baloonups';

	/**
	 * Process needed upgrades on each baloonup.
	 *
	 * @param int $baloonup_id
	 */
	public function process_baloonup( $baloonup_id = 0 ) {

		$baloonup = pum_get_baloonup( $baloonup_id );

		/**
		 * If the baloonup is already updated, return early.
		 */
		if ( $baloonup->data_version < 3 ) {

			/**
			 * Processes the baloonups data through a migration routine.
			 *
			 * $baloonup is passed by reference.
			 */
			pum_baloonup_migration_2( $baloonup );

			/**
			 * Update the baloonups data version.
			 */
			$baloonup->update_meta( 'data_version', 3 );
		}
	}

}
