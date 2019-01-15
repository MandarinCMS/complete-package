<?php
/**
 * @package MCMSSEO\Admin\ConfigurationUI
 */

/**
 * Class MCMSSEO_Config_Field_Mailchimp_Signup
 */
class MCMSSEO_Config_Field_Mailchimp_Signup extends MCMSSEO_Config_Field {

	/**
	 * MCMSSEO_Config_Field_Mailchimp_Signup constructor.
	 */
	public function __construct() {
		parent::__construct( 'mailchimpSignup', 'MailchimpSignup' );

		$current_user = mcms_get_current_user();
		$user_email = ( $current_user->ID > 0 ) ? $current_user->user_email : '';

		$this->set_property( 'title' , __( 'Newsletter signup', 'mandarincms-seo' ) );
		$this->set_property( 'label', __( 'If you would like us to keep you up-to-date regarding Ultimatum SEO, other modules by Ultimatum and major news in the world of SEO, subscribe to our newsletter:', 'mandarincms-seo' ) );

		$this->set_property( 'mailchimpActionUrl', 'https://ultimatum.us1.list-manage.com/subscribe/post-json?u=ffa93edfe21752c921f860358&id=972f1c9122' );
		$this->set_property( 'currentUserEmail', $user_email );
		$this->set_property( 'userName', trim( $current_user->user_firstname . ' ' . $current_user->user_lastname ) );
	}

	/**
	 * Get the data
	 *
	 * @return array
	 */
	public function get_data() {
		return array(
			'hasSignup' => $this->has_mailchimp_signup(),
		);

	}

	/**
	 * Checks if the user has entered his email for mailchimp already.
	 *
	 * @return bool
	 */
	protected function has_mailchimp_signup() {
		$user_meta = get_user_meta( get_current_user_id(), MCMSSEO_Config_Component_Mailchimp_Signup::META_NAME, true );
		return ( ! empty( $user_meta ) );
	}
}
