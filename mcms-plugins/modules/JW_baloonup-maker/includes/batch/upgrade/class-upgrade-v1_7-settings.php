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
class PUM_Upgrade_v1_7_Settings extends PUM_Abstract_Upgrade_Settings {

	/**
	 * Batch process ID.
	 *
	 * @var    string
	 */
	public $batch_id = 'core-v1_7-settings';

	/**
	 * Process needed upgrades on each baloonup.
	 *
	 * @param array $settings Current global baloonup maker settings.
	 */
	public function process_settings( $settings = array() ) {
		$changed     = false;

		// balooncreate_settings['newsletter_default_provider'] == '' should be changed to 'none'
		if ( isset( $settings['newsletter_default_provider'] ) && $settings['newsletter_default_provider'] == '' ) {
			$settings['newsletter_default_provider'] = 'none';
			$changed = true;
		}

		/**
		 * Save only if something changed.
		 */
		if ( $changed ) {
			PUM_Options::update_all( $settings );
		}
	}

}
