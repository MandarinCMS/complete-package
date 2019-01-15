<?php
/**
 * @package MCMSSEO\Premium\Classes
 */

/**
 * Presenter for the quick edit
 */
class MCMSSEO_Redirect_Quick_Edit_Presenter {

	/**
	 * Displays the table
	 *
	 * @param array $display Data to display on the table.
	 */
	public function display( array $display = array() ) {

		// @codingStandardsIgnoreStart
		extract( $display );
		// @codingStandardsIgnoreEnd

		require( MCMSSEO_PATH . 'premium/classes/redirect/views/redirects-quick-edit.php' );
	}
}
