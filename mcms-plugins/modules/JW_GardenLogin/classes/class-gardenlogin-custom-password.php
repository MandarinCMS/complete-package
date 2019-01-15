<?php
/**
* GardenLogins_Custom_Password
*
* Description: Enable Custom Password for Register User.
*
* @package GardenLogin
* @since 1.0.22
*/

if ( ! class_exists( 'GardenLogins_Custom_Password' ) ) :
  /**
  * GardenLogin Custom Passwords class
  *
  * @since 1.0.22
  */
  class GardenLogins_Custom_Password {

    /* * * * * * * * * *
    * Class constructor
    * * * * * * * * * */
    public function __construct() {

      $this->_hooks();
    }

    public function _hooks() {

      add_action( 'register_form',             array( $this, 'gardenlogin_reg_password_fields' ) );
      add_filter( 'registration_errors',       array( $this, 'gardenlogin_reg_pass_errors' ), 10, 3 );
      add_filter( 'random_password',           array( $this, 'gardenlogin_set_password' ) );
      add_action( 'register_new_user',         array( $this, 'update_default_password_nag' ) );
    }

    /**
     * Custom Password Fields on Registration Form.
     *
     * @since   1.0.22
     * @access  public
     * @return  string html.
     */
    public function gardenlogin_reg_password_fields() {
      ?>
      <p class="gardenlogin-reg-pass-wrap">
        <label for="gardenlogin-reg-pass"><?php _e( 'Password', 'gardenlogin' ); ?></label>
        <input autocomplete="off" name="gardenlogin-reg-pass" id="gardenlogin-reg-pass" class="input" size="20" value="" type="password" />
      </p>
      <p class="gardenlogin-reg-pass-2-wrap">
        <label for="gardenlogin-reg-pass-2"><?php _e( 'Confirm Password', 'gardenlogin' ); ?></label>
        <input autocomplete="off" name="gardenlogin-reg-pass-2" id="gardenlogin-reg-pass-2" class="input" size="20" value="" type="password" />
      </p>
      <?php
    }

    /**
    * Handles password field errors for registration form.
    *
    * @since 1.0.22
    * @access public
    *
    * @param Object $errors MCMS_Error
    * @param Object $sanitized_user_login user login.
    * @param Object $user_email user email.
    * @return MCMS_Error object.
    */
    public function gardenlogin_reg_pass_errors( $errors, $sanitized_user_login, $user_email ) {

      // Ensure passwords aren't empty.
      if ( empty( $_POST['gardenlogin-reg-pass'] ) || empty( $_POST['gardenlogin-reg-pass-2'] ) ) {
        $errors->add( 'empty_password', __( '<strong>ERROR</strong>: Please enter your password twice.', 'gardenlogin' ) );

      // Ensure passwords are matched.
      } elseif ( $_POST['gardenlogin-reg-pass'] != $_POST['gardenlogin-reg-pass-2'] ) {
        $errors->add( 'password_mismatch', __( '<strong>ERROR</strong>: Please enter the same password in the end password fields.', 'gardenlogin' ) );

      // Password Set? assign password to a user_pass
      } else {
        $_POST['user_pass'] = $_POST['gardenlogin-reg-pass'];
      }

      return $errors;
    }

    /**
    * Let's set the user password.
    *
    * @since 1.0.22
    * @access public
    * @param string $password Auto-generated password passed in from filter.
    * @return string Password Choose by User.
    */
    public function gardenlogin_set_password( $password ) {

      // Make sure password field isn't empty.
      if ( ! empty( $_POST['user_pass'] ) ) {
        $password = $_POST['user_pass'];
      }

      return $password;
    }

    /**
    * Sets the value of default password nag.
    *
    * @since 1.0.22
    * @access public
    * @param int $user_id.
    */
    public function update_default_password_nag( $user_id ) {

      // False => User not using MandarinCMS default password.
      update_user_meta( $user_id, 'default_password_nag', false );
    }

  } // End Of Class.

endif;
