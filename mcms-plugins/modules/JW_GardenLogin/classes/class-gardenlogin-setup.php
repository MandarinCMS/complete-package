<?php
/**
 * GardenLogin Settings
 *
 * @since 1.0.9
 */
if ( ! class_exists( 'GardenLogin_Settings' ) ):

class GardenLogin_Settings {

  private $settings_api;

  function __construct() {

    include_once( LOGINPRESS_ROOT_PATH . '/classes/class-gardenlogin-settings-api.php' );
    $this->settings_api = new GardenLogin_Settings_API;

    add_action( 'admin_init', array( $this, 'gardenlogin_setting_init' ) );
    add_action( 'admin_menu', array( $this, 'gardenlogin_setting_menu' ) );
  }

  function gardenlogin_setting_init() {

    //set the settings.
    $this->settings_api->set_sections( $this->get_settings_sections() );
    $this->settings_api->set_fields( $this->get_settings_fields() );

    //initialize settings.
    $this->settings_api->admin_init();

    //reset settings.
    $this->load_default_settings();
  }

  function load_default_settings() {

    $_gardenlogin_Setting = get_option( 'gardenlogin_setting' );
    if ( isset( $_gardenlogin_Setting['reset_settings'] ) && 'on' == $_gardenlogin_Setting['reset_settings'] ) {

       $gardenlogin_last_reset = array( 'last_reset_on' => date('Y-m-d') );
       update_option( 'gardenlogin_customization', $gardenlogin_last_reset );
       update_option( 'customize_presets_settings', 'default1' );
       $_gardenlogin_Setting['reset_settings'] = 'off';
       update_option( 'gardenlogin_setting', $_gardenlogin_Setting );
       add_action( 'admin_notices', array( $this, 'settings_reset_message' ) );
    }
  }

  function settings_reset_message() {

    $class = 'notice notice-success';
    $message = __( 'Default Settings Restored', 'gardenlogin' );

    printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message );
  }

  function gardenlogin_setting_menu() {

    add_menu_page( __( 'GardenLogin', 'gardenlogin' ), __( 'GardenLogin', 'gardenlogin' ), 'manage_options', "gardenlogin-settings", array( $this, 'module_page' ), '', 50 );

    add_submenu_page( 'gardenlogin-settings', __( 'Settings', 'gardenlogin' ), __( 'Settings', 'gardenlogin' ), 'manage_options', "gardenlogin-settings", array( $this, 'module_page' ) );

    add_submenu_page( 'gardenlogin-settings', __( 'Customizer', 'gardenlogin' ), __( 'Customizer', 'gardenlogin' ), 'manage_options', "gardenlogin", '__return_null' );

    add_submenu_page( 'gardenlogin-settings', __( 'Import/Export Settings', 'gardenlogin' ), __( 'Import / Export', 'gardenlogin' ), 'manage_options', "gardenlogin-import-export", array( $this, 'gardenlogin_import_export_page' ) );

  }

  function get_settings_sections() {

    $gardenlogin_general_tab = array(
      array(
        'id'    => 'gardenlogin_setting',
        'title' => __( 'Settings', 'gardenlogin' ),
        'desc'  => sprintf( __( 'Everything else is customizable through %1$sMandarinCMS Customizer%2$s.', 'gardenlogin' ), '<a href="' . admin_url( 'admin.php?page=gardenlogin' ) . '">', '</a>' ),
      ),
    );
    // if ( has_action( 'gardenlogin_pro_add_template' ) ) {
    //
    //   $gardenlogin_license =
    //   array(
    //     'id'    => 'gardenlogin_license',
    //     'title' => __( 'License', 'gardenlogin' ),
    //     'desc'  => __( 'License Description', 'gardenlogin' ),
    //   );
    //   array_push( $gardenlogin_general_tab , $gardenlogin_license );
    // }
    if ( has_action( 'gardenlogin_pro_add_template' ) ) {
 
      array_push( $gardenlogin_general_tab , $gardenlogin_premium );
    }

      $sections = apply_filters( 'gardenlogin_settings_tab', $gardenlogin_general_tab );
	
      return $sections;
  }

  /**
   * Returns all the settings fields
   *
   * @return array settings fields
   */
  function get_settings_fields() {

    /**
     * [$_free_fields array of free fields]
     * @var array
     */
    $_free_fields = array(
      array(
        'name'        => 'session_expiration',
        'label'       => __( 'Session Expire', 'gardenlogin' ),
        'desc'        => __( 'Set the session expiration time in minutes. e.g: 10', 'gardenlogin' ), //<br /> When you set the time, here you need to set the expiration cookies. for this, you just need to logout at least one time. After login again, it should be working fine.<br />For removing the session expiration just pass empty value in “Expiration” field and save it. Now clear the expiration cookies by logout at least one time.
        'placeholder' => __( '10', 'gardenlogin' ),
        'min'         => 0,
        // 'max'         => 100,
        'step'        => '1',
        'type'        => 'number',
        'default'     => 'Title',
        'sanitize_callback' => 'intval'
      ),
      // array(
      //   'name'  => 'enable_privacy_policy',
      //   'label' => __( 'Enable Privacy Policy', 'gardenlogin' ),
      //   'desc'  => __( 'Enable Privacy Policy checkbox on registration page.', 'gardenlogin' ),
      //   'type'  => 'checkbox'
      // ),
      // array(
      //   'name'  => 'privacy_policy',
      //   'label' => __( 'Privacy & Policy', 'gardenlogin' ),
      //   'desc'  => __( 'Right down the privacy and policy description.', 'gardenlogin' ),
      //   'type'  => 'wysiwyg',
      //   'default' => __( sprintf( __( '%1$sPrivacy Policy%2$s.', 'gardenlogin' ), '<a href="' . admin_url( 'admin.php?page=gardenlogin-settings' ) . '">', '</a>' ) )
      // ),
      array(
        'name'  => 'enable_reg_pass_field',
        'label' => __( 'Custom Password Fields', 'gardenlogin' ),
        'desc'  => __( 'Enable custom password fields on registration form.', 'gardenlogin' ),
        'type'  => 'checkbox'
      ),
      array(
        'name'    => 'login_order',
        'label'   => __( 'Login Order', 'gardenlogin' ),
        'desc'    => __( 'Enable users to login using their username and/or email address.', 'gardenlogin' ),
        'type'    => 'radio',
        'default' => 'default',
        'options' => array(
            'default' => 'Both Username Or Email Address',
            'username'  => 'Only Username',
            'email' => 'Only Email Address'
        )
      ),
      // array(
      //   'name'  => 'login_with_email',
      //   'label' => __( 'Login with Email', 'gardenlogin' ),
      //   'desc'  => __( 'Force user to login with Email Only Instead Username.', 'gardenlogin' ),
      //   'type'  => 'checkbox'
      // ),
      array(
        'name'  => 'reset_settings',
        'label' => __( 'Reset Default Settings', 'gardenlogin' ),
        'desc'  => __( 'Remove my custom settings.', 'gardenlogin' ),
        'type'  => 'checkbox'
      ),
    );

    // Hide Advertisement in version 1.1.3
    // if ( ! has_action( 'gardenlogin_pro_add_template' ) ) {
    //   array_unshift( $_free_fields , array(
    //     'name'  => 'enable_repatcha_promo',
    //     'label' => __( 'Enable reCAPTCHA', 'gardenlogin' ),
    //     'desc'  => __( 'Enable GardenLogin reCaptcha', 'gardenlogin' ),
    //     'type'  => 'checkbox'
    //   ) );
    // }

    $_settings_fields = apply_filters( 'gardenlogin_pro_settings', $_free_fields );

    $settings_fields = array( 'gardenlogin_setting' => $_settings_fields );

    $tab = apply_filters( 'gardenlogin_settings_fields', $settings_fields );

    return $tab;
  }

  function module_page() {

      echo '<div class="wrap gardenlogin-admin-setting">';
      echo '<h2 style="margin: 20px 0 20px 0;">';
      esc_html_e( 'GardenLogin - Rebranding your boring MandarinCMS Login pages', 'gardenlogin' );
      echo '</h2>';

      $this->settings_api->show_navigation();
      $this->settings_api->show_forms();

      echo '</div>';
  }

  /**
   * [gardenlogin_help_page callback function for sub-page Help]
   * @since 1.0.19
   */
  function gardenlogin_help_page(){

    include LOGINPRESS_DIR_PATH . 'classes/class-gardenlogin-log.php';

    $html = '<div class="gardenlogin-help-page">';
    $html .= '<h2>Help & Troubleshooting</h2>';
    $html .= sprintf( __( 'Free support is available on the %1$s module support forums%2$s.', 'gardenlogin' ), '<a href="https://mandarincms.org/support/module/gardenlogin" target="_blank">', '</a>' );
    $html .="<br /><br />";
    $html .= sprintf( __( 'For premium features, add-ons and priority email support, %1$s upgrade to pro%2$s.', 'gardenlogin' ), '<a href="https://jiiworks.net/mandarincms/modules/gardenlogin-pro/?utm_source=gardenlogin-lite&utm_medium=help-page&utm_campaign=pro-upgrade" target="_blank">', '</a>' );
    $html .="<br /><br />";
    $html .= 'Found a bug or have a feature request? Please submit an issue <a href="https://jiiworks.net/contact/" target="_blank">here</a>!';
    $html .= '<pre><textarea rows="25" cols="75" readonly="readonly">';
    $html .= GardenLogin_Log_Info::get_sysinfo();
    $html .= '</textarea></pre>';
    $html .= '<input type="button" class="button gardenlogin-log-file" value="' . __( 'Download Log File', 'gardenlogin' ) . '"/>';
    $html .= '<span class="log-file-sniper"><img src="'. admin_url( 'images/mcmsspin_light.gif' ) .'" /></span>';
    $html .= '<span class="log-file-text">GardenLogin Log File Downloaded Successfully!</span>';
    $html .= '</div>';
    echo $html;
  }

  /**
   * [gardenlogin_import_export_page callback function for sub-page Import / Export]
   * @since 1.0.19
   */
  function gardenlogin_import_export_page(){

    include LOGINPRESS_DIR_PATH . 'include/gardenlogin-import-export.php';
  }

  /**
   * [gardenlogin_addons_page callback function for sub-page Add-ons]
   * @since 1.0.19
   */
  function gardenlogin_addons_page() {}

  /**
   * Get all the pages
   *
   * @return array page names with key value pairs
   */
  function get_pages() {
    $pages = get_pages();
    $pages_options = array();
    if ( $pages ) {
        foreach ($pages as $page) {
            $pages_options[$page->ID] = $page->post_title;
        }
    }

    return $pages_options;
  }

}
endif;
