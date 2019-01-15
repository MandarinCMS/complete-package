<?php
/**
 * @package    MCMSSEO
 * @subpackage Internal
 */

/**
 * Class containing method for MCMSSEO Features.
 */
class MCMSSEO_Features {

	/**
	 * Checks if the premium constant exists to make sure if module is the premium one.
	 *
	 * @return bool
	 */
	public function is_premium() {
		return ( defined( 'MCMSSEO_PREMIUM_FILE' ) );
	}

	/**
	 * Checks if using the free version of the module.
	 *
	 * @return bool
	 */
	public function is_free() {
		return ! $this->is_premium();
	}
}
