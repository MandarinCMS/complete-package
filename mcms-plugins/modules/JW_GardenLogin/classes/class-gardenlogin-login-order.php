<?php

/**
* GardenLogin_Login_Order.
*
* @description Enable user to login using their username and/or email address.
* @since 1.0.18
*/

if ( ! class_exists( 'GardenLogin_Login_Order' ) ) :

	class GardenLogin_Login_Order {

		/**
	  * Variable that Check for GardenLogin Key.
	  * @access public
	  * @var string
	  */
	  public $gardenlogin_key;

		/* * * * * * * * * *
    * Class constructor
    * * * * * * * * * */
    public function __construct() {

			$this->gardenlogin_key = get_option( 'gardenlogin_customization' );
      $this->_hooks();
    }

		public function _hooks(){

			$mcms_version = get_bloginfo( 'version' );
			$gardenlogin_setting = get_option( 'gardenlogin_setting' );
			$login_order = isset(	$gardenlogin_setting['login_order'] ) ? $gardenlogin_setting['login_order'] : '';

			remove_filter( 'authenticate', 	'mcms_authenticate_username_password', 20, 3 );
			add_filter( 'authenticate', array( $this, 'gardenlogin_login_order' ), 20, 3 );

			if ( 'username' == $login_order && '4.5.0' < $mcms_version ) {
		 		// For MCMS 4.5.0 remove email authentication.
				remove_filter( 'authenticate', 'mcms_authenticate_email_password', 20 );
			}
		}

		/**
		* If an email address is entered in the username field, then look up the matching username and authenticate as per normal, using that.
		*
		* @param string $user
		* @param string $username
		* @param string $password
		* @since 1.0.18
		* @version 1.0.22
		* @return Results of autheticating via mcms_authenticate_username_password(), using the username found when looking up via email.
		*/
		function gardenlogin_login_order( $user, $username, $password ) {

			if ( $user instanceof MCMS_User ) {
				return $user;
			}

			// Is username or password field empty?
			if ( empty( $username ) || empty( $password ) ) {

				if ( is_mcms_error( $user ) )
					return $user;

				$error = new MCMS_Error();

				$empty_username	= isset( $this->gardenlogin_key['empty_username'] ) && ! empty( $this->gardenlogin_key['empty_username'] ) ? $this->gardenlogin_key['empty_username'] : sprintf( __( '%1$sError:%2$s The username field is empty.', 'gardenlogin' ), '<strong>', '</strong>' );

	      $empty_password	= isset( $this->gardenlogin_key['empty_password'] ) && ! empty( $this->gardenlogin_key['empty_password'] ) ? $this->gardenlogin_key['empty_password'] : sprintf( __( '%1$sError:%2$s The password field is empty.', 'gardenlogin' ), '<strong>', '</strong>' );

				if ( empty( $username ) )
					$error->add( 'empty_username', $empty_username );

				if ( empty( $password ) )
					$error->add( 'empty_password', $empty_password );

				return $error;
			} // close empty_username || empty_password.

			$gardenlogin_setting = get_option( 'gardenlogin_setting' );
			$login_order = isset(	$gardenlogin_setting['login_order'] ) ? $gardenlogin_setting['login_order'] : '';

			// Is login order is set to be 'email'.
			if ( 'email' == $login_order ) {

				if ( ! empty( $username ) && ! is_email( $username ) ) {

					$error = new MCMS_Error();

					$force_email_login= isset( $this->gardenlogin_key['force_email_login'] ) && ! empty( $this->gardenlogin_key['force_email_login'] ) ? $this->gardenlogin_key['force_email_login'] : sprintf( __( '%1$sError:%2$s Invalid Email Address', 'gardenlogin' ), '<strong>', '</strong>' );

					$error->add( 'gardenlogin_use_email', $force_email_login );

					return $error;
				}

				if ( ! empty( $username ) && is_email( $username ) ) {

					$username = str_replace( '&', '&amp;', stripslashes( $username ) );
					$user = get_user_by( 'email', $username );

					if ( isset( $user, $user->user_login, $user->user_status ) && 0 === intval( $user->user_status ) )
					$username = $user->user_login;
					return mcms_authenticate_username_password( null, $username, $password );
				}
			} // login order 'email'.

			// Is login order is set to be 'username'.
			if ( 'username' == $login_order ) {
				$user = get_user_by('login', $username);

				$invalid_usrname = array_key_exists( 'incorrect_username', $this->gardenlogin_key ) && ! empty( $this->gardenlogin_key['incorrect_username'] ) ? $this->gardenlogin_key['incorrect_username'] : sprintf( __( '%1$sError:%2$s Invalid Username.', 'gardenlogin' ), '<strong>', '</strong>' );

				if ( ! $user ) {
					return new MCMS_Error( 'invalid_username', $invalid_usrname );
				}

				if ( ! empty( $username ) || ! empty( $password ) ) {

					$username = str_replace( '&', '&amp;', stripslashes( $username ) );
					$user = get_user_by( 'login', $username );

					if ( isset( $user, $user->user_login, $user->user_status ) && 0 === intval( $user->user_status ) )
					$username = $user->user_login;
					if ( ! empty( $username ) && is_email( $username ) ) {
						return mcms_authenticate_username_password( null, "", "" );
					} else {
						return mcms_authenticate_username_password( null, $username, $password );
					}

				}
			} // login order 'username'.

		}

	} // End Of Class.
endif;
