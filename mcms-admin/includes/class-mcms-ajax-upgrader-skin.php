<?php
/**
 * Upgrader API: MCMS_Ajax_Upgrader_Skin class
 *
 * @package MandarinCMS
 * @subpackage Upgrader
 * @since 4.6.0
 */

/**
 * Upgrader Skin for Ajax MandarinCMS upgrades.
 *
 * This skin is designed to be used for Ajax updates.
 *
 * @since 4.6.0
 *
 * @see Automatic_Upgrader_Skin
 */
class MCMS_Ajax_Upgrader_Skin extends Automatic_Upgrader_Skin {

	/**
	 * Holds the MCMS_Error object.
	 *
	 * @since 4.6.0
	 * @var null|MCMS_Error
	 */
	protected $errors = null;

	/**
	 * Constructor.
	 *
	 * @since 4.6.0
	 *
	 * @param array $args Options for the upgrader, see MCMS_Upgrader_Skin::__construct().
	 */
	public function __construct( $args = array() ) {
		parent::__construct( $args );

		$this->errors = new MCMS_Error();
	}

	/**
	 * Retrieves the list of errors.
	 *
	 * @since 4.6.0
	 *
	 * @return MCMS_Error Errors during an upgrade.
	 */
	public function get_errors() {
		return $this->errors;
	}

	/**
	 * Retrieves a string for error messages.
	 *
	 * @since 4.6.0
	 *
	 * @return string Error messages during an upgrade.
	 */
	public function get_error_messages() {
		$messages = array();

		foreach ( $this->errors->get_error_codes() as $error_code ) {
			if ( $this->errors->get_error_data( $error_code ) && is_string( $this->errors->get_error_data( $error_code ) ) ) {
				$messages[] = $this->errors->get_error_message( $error_code ) . ' ' . esc_html( strip_tags( $this->errors->get_error_data( $error_code ) ) );
			} else {
				$messages[] = $this->errors->get_error_message( $error_code );
			}
		}

		return implode( ', ', $messages );
	}

	/**
	 * Stores a log entry for an error.
	 *
	 * @since 4.6.0
	 *
	 * @param string|MCMS_Error $errors Errors.
	 */
	public function error( $errors ) {
		if ( is_string( $errors ) ) {
			$string = $errors;
			if ( ! empty( $this->upgrader->strings[ $string ] ) ) {
				$string = $this->upgrader->strings[ $string ];
			}

			if ( false !== strpos( $string, '%' ) ) {
				$args = func_get_args();
				$args = array_splice( $args, 1 );
				if ( ! empty( $args ) ) {
					$string = vsprintf( $string, $args );
				}
			}

			// Count existing errors to generate an unique error code.
			$errors_count = count( $this->errors->get_error_codes() );
			$this->errors->add( 'unknown_upgrade_error_' . $errors_count + 1 , $string );
		} elseif ( is_mcms_error( $errors ) ) {
			foreach ( $errors->get_error_codes() as $error_code ) {
				$this->errors->add( $error_code, $errors->get_error_message( $error_code ), $errors->get_error_data( $error_code ) );
			}
		}

		$args = func_get_args();
		call_user_func_array( array( $this, 'parent::error' ), $args );
	}

	/**
	 * Stores a log entry.
	 *
	 * @since 4.6.0
	 *
	 * @param string|array|MCMS_Error $data Log entry data.
	 */
	public function feedback( $data ) {
		if ( is_mcms_error( $data ) ) {
			foreach ( $data->get_error_codes() as $error_code ) {
				$this->errors->add( $error_code, $data->get_error_message( $error_code ), $data->get_error_data( $error_code ) );
			}
		}

		$args = func_get_args();
		call_user_func_array( array( $this, 'parent::feedback' ), $args );
	}
}
