<?php
/**
 * @package Ultimatum\MCMS\HelpScout
 */

if ( ! function_exists( 'ultimatum_get_helpscout_beacon' ) ) {
	/**
	 * Retrieve the instance of the beacon
	 *
	 * @param string $page The current admin page.
	 * @param string $type Which type of popup we want to show.
	 *
	 * @return Ultimatum_HelpScout_Beacon
	 */
	function ultimatum_get_helpscout_beacon( $page, $type = Ultimatum_HelpScout_Beacon::BEACON_TYPE_SEARCH ) {
		static $beacon;

		if ( ! isset( $beacon ) ) {
			$beacon = new Ultimatum_HelpScout_Beacon( $page, array(), $type );
		}

		return $beacon;
	}
}
