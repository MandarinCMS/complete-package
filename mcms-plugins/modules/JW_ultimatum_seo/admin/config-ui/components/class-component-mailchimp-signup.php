<?php
/**
 * @package MCMSSEO\Admin\ConfigurationUI
 */

/**
 * Represents the mailchimp signup components.
 */
class MCMSSEO_Config_Component_Mailchimp_Signup implements MCMSSEO_Config_Component {

	const META_NAME = 'mcmsseo-has-mailchimp-signup';

	/**
	 * Gets the component identifier.
	 *
	 * @return string
	 */
	public function get_identifier() {
		return 'MailchimpSignup';
	}

	/**
	 * Gets the field.
	 *
	 * @return MCMSSEO_Config_Field
	 */
	public function get_field() {
		return new MCMSSEO_Config_Field_Mailchimp_Signup();
	}

	/**
	 * Get the data for the field.
	 *
	 * @return mixed
	 */
	public function get_data() {
		$data = array(
			'hasSignup' => $this->has_mailchimp_signup(),
		);

		return $data;
	}

	/**
	 * Save data
	 *
	 * @param array $data Data containing changes.
	 *
	 * @return mixed
	 */
	public function set_data( $data ) {

		$has_saved = false;
		if ( ! empty( $data['hasSignup'] ) ) {
			// Saves the user meta.
			update_user_meta( get_current_user_id(), self::META_NAME, true );

			$has_saved = ( $data['hasSignup'] === $this->has_mailchimp_signup() );
		}

		// Collect results to return to the configurator.
		$results = array(
			'hasSignup' => $has_saved,
		);

		return $results;
	}

	/**
	 * Checks if the user has entered his email for mailchimp already.
	 *
	 * @return bool
	 */
	protected function has_mailchimp_signup() {
		$user_meta = get_user_meta( get_current_user_id(), self::META_NAME, true );

		return ( ! empty( $user_meta ) );
	}
}
