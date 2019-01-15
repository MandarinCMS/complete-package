<?php
/**
 * Cron
 *
 * @package     POPMAKE
 * @subpackage  Classes/Cron
 * @copyright   Copyright (c) 2015, Daniel Iser
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3.0
 */

// Exit if accessed directly
if ( ! defined( 'BASED_TREE_URI' ) ) {
	exit;
}

/**
 * Popmake_Cron Class
 *
 * This class handles scheduled events
 *
 * @since 1.3.0
 */
class Popmake_Cron {
	/**
	 * Get things going
	 *
	 * @since 1.3.0
	 * @see Popmake_Cron::weekly_events()
	 */
	public function __construct() {
		add_filter( 'cron_schedules', array( $this, 'add_schedules' ) );
		add_action( 'mcms', array( $this, 'schedule_Events' ) );
	}

	/**
	 * Registers new cron schedules
	 *
	 * @since 1.3.0
	 *
	 * @param array $schedules
	 *
	 * @return array
	 */
	public function add_schedules( $schedules = array() ) {
		// Adds once weekly to the existing schedules.
		$schedules['weekly'] = array(
			'interval' => 604800,
			'display'  => __( 'Once Weekly', 'baloonup-maker' )
		);

		return $schedules;
	}

	/**
	 * Schedules our events
	 *
	 * @access public
	 * @since 1.3.0
	 * @return void
	 */
	public function schedule_Events() {
		$this->weekly_events();
		$this->daily_events();
	}

	/**
	 * Schedule weekly events
	 *
	 * @access private
	 * @since 1.3.0
	 * @return void
	 */
	private function weekly_events() {
		if ( ! mcms_next_scheduled( 'balooncreate_weekly_scheduled_events' ) ) {
			mcms_schedule_event( current_time( 'timestamp' ), 'weekly', 'balooncreate_weekly_scheduled_events' );
		}
	}

	/**
	 * Schedule daily events
	 *
	 * @access private
	 * @since 1.3.0
	 * @return void
	 */
	private function daily_events() {
		if ( ! mcms_next_scheduled( 'balooncreate_daily_scheduled_events' ) ) {
			mcms_schedule_event( current_time( 'timestamp' ), 'daily', 'balooncreate_daily_scheduled_events' );
		}
	}

}

$balooncreate_cron = new Popmake_Cron;
