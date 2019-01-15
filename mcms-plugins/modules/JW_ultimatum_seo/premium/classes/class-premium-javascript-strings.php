<?php
/**
 * @package MCMSSEO\Premium\Classes
 */

/**
 * Class MCMSSEO_Premium_Javascript_Strings
 */
class MCMSSEO_Premium_Javascript_Strings {

	/**
	 * @var null
	 */
	private static $strings = null;

	/**
	 * Fill the value of self::$strings with translated strings
	 */
	private static function fill() {
		self::$strings = array(
			'error_circular'        => __( 'You can\'t redirect a URL to itself.', 'mandarincms-seo-premium' ),
			'error_old_url'         => __( 'The old URL field can\'t be empty.', 'mandarincms-seo-premium' ),
			'error_regex'           => __( 'The Regular Expression field can\'t be empty.', 'mandarincms-seo-premium' ),
			'error_new_url'         => __( 'The new URL field can\'t be empty.', 'mandarincms-seo-premium' ),
			'error_saving_redirect' => __( 'Error while saving this redirect', 'mandarincms-seo-premium' ),
			'error_new_type'        => __( 'New type can\'t be empty.', 'mandarincms-seo-premium' ),
			'unsaved_redirects'     => __( 'You have unsaved redirects, are you sure you want to leave?', 'mandarincms-seo-premium' ),

			/* translator note: %s is replaced with the URL that will be deleted */
			'enter_new_url'         => __( 'Please enter the new URL for %s', 'mandarincms-seo-premium' ),
			/* translator note: variables will be replaced with from and to URLs */
			'redirect_saved'           => __( 'Redirect created from %1$s to %2$s!', 'mandarincms-seo-premium' ),
			'redirect_saved_no_target' => __( '410 Redirect created from %1$s!', 'mandarincms-seo-premium' ),

			'redirect_added'        => array(
				'title'   => __( 'Redirect added.', 'mandarincms-seo-premium' ),
				'message' => __( 'The redirect was added successfully.', 'mandarincms-seo-premium' ),
			),
			'redirect_updated'        => array(
				'title'   => __( 'Redirect updated.', 'mandarincms-seo-premium' ),
				'message' => __( 'The redirect was updated successfully.', 'mandarincms-seo-premium' ),
			),
			'redirect_deleted'        => array(
				'title'   => __( 'Redirect deleted.', 'mandarincms-seo-premium' ),
				'message' => __( 'The redirect was deleted successfully.', 'mandarincms-seo-premium' ),
			),

			'button_ok'          => __( 'OK', 'mandarincms-seo-premium' ),
			'button_cancel'      => __( 'Cancel', 'mandarincms-seo-premium' ),
			'button_save'        => __( 'Save', 'mandarincms-seo-premium' ),
			'button_save_anyway' => __( 'Save anyway', 'mandarincms-seo-premium' ),

			'edit_redirect'     => __( 'Edit redirect', 'mandarincms-seo-premium' ),
			'editing_redirect'  => __( 'You are already editing a redirect, please finish this one first', 'mandarincms-seo-premium' ),

			'editAction'   => __( 'Edit', 'mandarincms-seo-premium' ),
			'deleteAction' => __( 'Delete', 'mandarincms-seo-premium' ),
		);
	}

	/**
	 * Returns an array with all the translated strings
	 *
	 * @return array
	 */
	public static function strings() {
		if ( self::$strings === null ) {
			self::fill();
		}

		return self::$strings;
	}
}
