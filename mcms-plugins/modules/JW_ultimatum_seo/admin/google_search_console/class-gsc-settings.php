<?php
/**
 * @package MCMSSEO\Admin|Google_Search_Console
 */

/**
 * Class MCMSSEO_GSC_Settings
 */
class MCMSSEO_GSC_Settings {

	/**
	 * Clear all data from the database
	 *
	 * @param MCMSSEO_GSC_Service $service Service class instance.
	 */
	public static function clear_data( MCMSSEO_GSC_Service $service ) {
		// Remove issue and issue counts.
		self::remove();

		// Removes the GSC options.
		self::remove_gsc_option();

		// Clear the service data.
		$service->clear_data();
	}

	/**
	 * Reloading all the issues
	 */
	public static function reload_issues() {
		// Remove issue and issue counts.
		self::remove();
	}

	/**
	 * When authorization is successful return true, otherwise false
	 *
	 * @param string                  $authorization_code Code to validate.
	 * @param Ultimatum_Api_Google_Client $client             Client object instance.
	 *
	 * @return bool
	 */
	public static function validate_authorization( $authorization_code, Ultimatum_Api_Google_Client $client ) {
		return ( $authorization_code !== '' && $client->authenticate_client( $authorization_code ) );
	}

	/**
	 * Get the GSC profile
	 *
	 * @return string
	 */
	public static function get_profile() {
		// Get option.
		$option = get_option( MCMSSEO_GSC::OPTION_MCMSSEO_GSC, array( 'profile' => '' ) );

		// Set the profile.
		$profile = '';
		if ( ! empty( $option['profile'] ) ) {
			$profile = $option['profile'];
		}

		// Return the profile.
		return trim( $profile, '/' );
	}

	/**
	 * Removes the issue counts and all the issues from the options
	 */
	private static function remove() {
		// Remove the issue counts from the options.
		self::remove_issue_counts();

		// Removing all issues from the database.
		self::remove_issues();
	}

	/**
	 * Remove the issue counts
	 */
	private static function remove_issue_counts() {
		// Remove the options which are holding the counts.
		delete_option( MCMSSEO_GSC_Count::OPTION_CI_COUNTS );
		delete_option( MCMSSEO_GSC_Count::OPTION_CI_LAST_FETCH );
	}

	/**
	 * Delete the issues and their meta data from the database
	 */
	private static function remove_issues() {
		global $mcmsdb;

		// Remove local crawl issues by running a delete query.
		$mcmsdb->query( "DELETE FROM {$mcmsdb->options} WHERE option_name LIKE 'mcmsseo-gsc-issues-%'" );
	}

	/**
	 * Removes the options for GSC
	 */
	private static function remove_gsc_option() {
		delete_option( MCMSSEO_GSC::OPTION_MCMSSEO_GSC );
	}
}
