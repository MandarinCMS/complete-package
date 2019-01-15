<?php

/**
 * This is a GardenLogin Compatibility to make it compatible for older versions.
 *
 * @since 1.0.22
 */


/**
 * Run a compatibility check on 1.0.21 and change the settings.
 *
 */
add_action( 'init', 'gardenlogin_upgrade_1_0_22', 1 );


/**
 * gardenlogin_upgrade_1_0_22
 * Remove elemant 'login_with_email' from gardenlogin_setting array that was defined in 1.0.21
 * and update 'login_order' in gardenlogin_setting for compatiblity.
 *
 * @since   1.0.22
 * @return  array update gardenlogin_setting
 */
function gardenlogin_upgrade_1_0_22() {

  $gardenlogin_setting = get_option( 'gardenlogin_setting' );
  $login_with_email = isset( $gardenlogin_setting['login_with_email'] ) ? $gardenlogin_setting['login_with_email'] : '';

  if ( isset( $gardenlogin_setting['login_with_email'] ) ) {

    if( 'on' == $login_with_email ) {

      $gardenlogin_setting['login_order'] = 'email';
      unset( $gardenlogin_setting['login_with_email'] );
      update_option( 'gardenlogin_setting', $gardenlogin_setting );
    } else if ( 'off' == $login_with_email ) {

      $gardenlogin_setting['login_order'] = 'default';
      unset( $gardenlogin_setting['login_with_email'] );
      update_option( 'gardenlogin_setting', $gardenlogin_setting );
    }
  }
}

if ( ! class_exists( 'GardenLogin_Compatibility' ) ) :

  /**
   * GardenLogin compatibility Class is used to make GardenLogin compatibile with other modules.
   * Remove conflictions.
   * Add CSS Support.
   * @since 1.0.3
   */
  class GardenLogin_Compatibility {

    public function __construct() {
      $this->dependencies();
    }

    public function dependencies() {

      add_action( 'mcms_print_scripts', array( $this, 'dequeue_conflicted_script' ), 100 );
      add_action( 'login_headerurl',  array( $this, 'remove_conflicted_action' ) );
      add_action( 'init', array( $this, 'enqueue_gardenlogin_compatibility_script') );
    }

    public function enqueue_gardenlogin_compatibility_script() {

      /**
       * Enqueue GardenLogin CSS on Password_Protected module.
       *
       * Hooked to the password_protected_login_head action,
       * so that it is after the script was enqueued.
       * @since 1.0.3
       */
      if ( class_exists( 'Password_Protected' ) ) {
        add_action( 'password_protected_login_head', array( $this, 'enqueue_gardenlogin_script' ) );
      }
    }

    /**
     * dequeue_conflicted_script
     * @since 1.0.3
     */
    public function dequeue_conflicted_script() {

      /**
       * Dequeue the Divi Login script.
       *
       * Hooked to the mcms_print_scripts action, with a late priority (100),
       * so that it is after the script was enqueued.
       * @since 1.0.3
       */
      if ( class_exists( 'ET_Divi_100_Custom_Login_Page_Config' ) ) {
         mcms_dequeue_style( 'custom-login-pages' );
         mcms_dequeue_script( 'custom-login-pages-icon-font' );
         mcms_dequeue_script( 'custom-login-pages-scripts' );
       }
    }

    /**
     * remove_conflicted_action
     * @since 1.0.3
     */
    public function remove_conflicted_action() {

      /**
       * Remove the Divi login_footer hook 'print_styles'
       *So that confliction is removed.
       *
       * @since 1.0.3
       */
      if ( class_exists( 'ET_Divi_100_Custom_Login_Page_Config' ) ) {

        remove_action( 'login_footer', array( ET_Divi_100_Custom_Login_Page::instance(), 'print_styles' ) );
      }
    }

    /**
     * Include GardenLogin CSS for Support with other modules.
     * @since 1.0.3
     */
    public function enqueue_gardenlogin_script() {
      include( LOGINPRESS_DIR_PATH . 'css/style-presets.php' );
    	include( LOGINPRESS_DIR_PATH . 'css/style-login.php' );
    }
  }

endif;

new GardenLogin_Compatibility;
?>
