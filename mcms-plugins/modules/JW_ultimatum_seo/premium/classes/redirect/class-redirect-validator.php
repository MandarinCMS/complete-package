<?php
/**
 * @package MCMSSEO\Premium\Classes\Redirect
 */

/**
 * The validation class.
 */
class MCMSSEO_Redirect_Validator {

	/**
	 * @var array
	 */
	protected $validation_rules = array(
		'self-redirect' => array(
			'validation_class' => 'MCMSSEO_Redirect_Self_Redirect_Validation',
			'exclude_types'  => array(),
			'exclude_format' => array( MCMSSEO_Redirect::FORMAT_REGEX ),
		),
		'uniqueness' => array(
			'validation_class' => 'MCMSSEO_Redirect_Uniqueness_Validation',
			'exclude_types'  => array(),
			'exclude_format' => array(),
		),
		'presence'     => array(
			'validation_class' => 'MCMSSEO_Redirect_Presence_Validation',
			'exclude_types'  => array(),
			'exclude_format' => array(),
		),
		'subdirectory-presence'  => array(
			'validation_class' => 'MCMSSEO_Redirect_Subdirectory_Validation',
			'exclude_types'  => array(),
			'exclude_format' => array(),
		),
		'accessible' => array(
			'validation_class' => 'MCMSSEO_Redirect_Accessible_Validation',
			'exclude_types'  => array( MCMSSEO_Redirect::DELETED, MCMSSEO_Redirect::UNAVAILABLE ),
			'exclude_format' => array(),
		),
		'endpoint'   => array(
			'validation_class' => 'MCMSSEO_Redirect_Endpoint_Validation',
			'exclude_types'  => array( MCMSSEO_Redirect::DELETED, MCMSSEO_Redirect::UNAVAILABLE ),
			'exclude_format' => array( MCMSSEO_Redirect::FORMAT_REGEX ),
		),
	);

	/**
	 * @var bool|string The validation error.
	 */
	protected $validation_error = false;

	/**
	 * Validates the old and the new URL
	 *
	 * @param MCMSSEO_Redirect $redirect		   The redirect that will be saved.
	 * @param MCMSSEO_Redirect $current_redirect Redirect that will be used for comparison.
	 *
	 * @return bool|string
	 */
	public function validate( MCMSSEO_Redirect $redirect, MCMSSEO_Redirect $current_redirect = null ) {

		$validators = $this->get_validations( $this->get_filtered_validation_rules( $this->validation_rules, $redirect ) );
		$redirects  = $this->get_redirects( $redirect->get_format() );

		$this->validation_error = '';
		foreach ( $validators as $validator ) {
			if ( ! $validator->run( $redirect, $current_redirect, $redirects ) ) {
				$this->validation_error = $validator->get_error();

				return false;
			}
		}

		return true;
	}

	/**
	 * Returns the validation error
	 *
	 * @return MCMSSEO_Validation_Result
	 */
	public function get_error() {
		return $this->validation_error;
	}

	/**
	 * Removes a rule from the validations
	 *
	 * @param array  $validations    Array with the validations.
	 * @param string $rule_to_remove The rule that will be removed.
	 */
	protected function remove_rule( & $validations, $rule_to_remove ) {
		if ( array_key_exists( $rule_to_remove, $validations ) ) {
			unset( $validations[ $rule_to_remove ] );
		}
	}

	/**
	 * Filters the validation rules.
	 *
	 * @param array          $validations Array with validation rules.
	 * @param MCMSSEO_Redirect $redirect    The redirect that will be saved.
	 *
	 * @return array
	 */
	protected function get_filtered_validation_rules( array $validations, MCMSSEO_Redirect $redirect ) {
		foreach ( $validations as $validation => $validation_rules ) {
			$exclude_format = in_array( $redirect->get_format(), $validation_rules['exclude_format'] );
			$exclude_type   = in_array( $redirect->get_type(), $validation_rules['exclude_types'] );

			if ( $exclude_format || $exclude_type ) {
				$this->remove_rule( $validations, $validation );
			}
		}

		return $validations;
	}

	/**
	 *
	 * Getting the validations based on the set validation rules.
	 *
	 * @param array $validation_rules The rules for the validations that will be run.
	 *
	 * @return MCMSSEO_Redirect_Validation[]
	 */
	protected function get_validations( $validation_rules ) {
		$validations = array();
		foreach ( $validation_rules as $validation_rule ) {
			$validations[] = new $validation_rule['validation_class']();
		}

		return $validations;
	}

	/**
	 * Fill the redirect property
	 *
	 * @param string $format The format for the redirects.
	 *
	 * @return array
	 */
	protected function get_redirects( $format ) {
		$redirect_manager = new MCMSSEO_Redirect_Manager( $format );

		// Format the redirects.
		$redirects = array();
		foreach ( $redirect_manager->get_all_redirects() as $redirect ) {
			$redirects[ $redirect->get_origin() ] = $redirect->get_target();
		}

		return $redirects;
	}
}
