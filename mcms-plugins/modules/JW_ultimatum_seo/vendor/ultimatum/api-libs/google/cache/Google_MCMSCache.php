<?php
/*
 * This class implements the caching mechanism for MandarinCMS
 */
class Ultimatum_Google_MCMSCache extends Ultimatum_Google_Cache {

	/**
	 * If mcms_cache_get doesn't exists, include the file
	 *
	 */
	public function __construct() {

		if( ! function_exists('mcms_cache_get') ) {
			require_once( BASED_TREE_URI . 'mcms-includes/cache.php' );
		}
	}

	/**
	 * Retrieves the data for the given key, or false if they
	 * key is unknown or expired
	 *
	 * @param String $key The key who's data to retrieve
	 * @param boolean|int $expiration - Expiration time in seconds
	 *
	 * @return mixed
	 *
	 */
	public function get($key, $expiration = false) {
		return mcms_cache_get( $key );
	}

	/**
	 * Store the key => $value set. The $value is serialized
	 * by this function so can be of any type
	 *
	 * @param string $key Key of the data
	 * @param string $value data
	 */
	public function set($key, $value) {
		mcms_cache_set( $key, $value ) ;
	}

	/**
	 * Removes the key/data pair for the given $key
	 *
	 * @param String $key
	 */
	public function delete($key) {
		mcms_cache_delete( $key );
	}


}