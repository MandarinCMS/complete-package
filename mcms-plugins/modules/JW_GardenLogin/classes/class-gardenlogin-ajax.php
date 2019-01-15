<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
  // Exit if accessed directly.
  exit;
}

/**
* Handling all the AJAX calls in GardenLogin.
*
* @since 1.0.19
* @class GardenLogin_AJAX
*/

if ( ! class_exists( 'GardenLogin_AJAX' ) ) :

  class GardenLogin_AJAX {

    /* * * * * * * * * *
    * Class constructor
    * * * * * * * * * */
    public function __construct() {

      $this::init();
    }
    public static function init() {

      $ajax_calls = array(
        'export'     => false,
        'import'     => false,
        'help'       => false,
        'deactivate' => false,
        'optout_yes' => false,
      );

      foreach ( $ajax_calls as $ajax_call => $no_priv ) {
        // code...
        add_action( 'mcms_ajax_gardenlogin_' . $ajax_call, array( __CLASS__, $ajax_call ) );

        if ( $no_priv ) {
          add_action( 'mcms_ajax_nopriv_gardenlogin_' . $ajax_call, array( __CLASS__, $ajax_call ) );
        }
      }
    }

    /**
    * [Import GardenLogin Settings]
    * @return [array] [update settings meta]
    * @since 1.0.19
    */
    public function import() {

      $lg_imp_tmp_name =  $_FILES['file']['tmp_name'];
      $lg_file_content = file_get_contents( $lg_imp_tmp_name );
      $gardenlogin_json = json_decode( $lg_file_content, true );

      if ( json_last_error() == JSON_ERROR_NONE ) {

        foreach ( $gardenlogin_json as $object => $array ) {

          // Check for GardenLogin customizer images.
          if ( 'gardenlogin_customization' == $object ) {

            update_option( $object, $array );

            foreach ( $array as $key => $value ) {

              // Array of gardenlogin customizer images.
              $imagesCheck = array( 'setting_logo', 'setting_background', 'setting_form_background', 'forget_form_background' );

              /**
              * [if json fetched data has array of $imagesCheck]
              * @var [array]
              */
              if ( in_array( $key, $imagesCheck ) ) {

                global $mcmsdb;
                // Count the $value of that $key from {$mcmsdb->posts}.
                $query = "SELECT COUNT(*) FROM {$mcmsdb->posts} WHERE guid='$value'";
                $count = $mcmsdb->get_var($query);

                if ( $count < 1 && ! empty( $value ) ) {
                  $file = array();
                  $file['name'] = basename( $value );
                  $file['tmp_name'] = download_url( $value ); // Downloads a url to a local temporary file.

                  if ( is_mcms_error( $file['tmp_name'] ) ) {
                    @unlink( $file['tmp_name'] );
                    // return new MCMS_Error( 'lpimgurl', 'Could not download image from remote source' );
                  } else {
                    $id  = media_handle_sideload( $file, 0 ); // Handles a sideloaded file.
                    $src = mcms_get_attachment_url( $id ); // Returns a full URI for an attachment file.
                    $gardenlogin_options = get_option( 'gardenlogin_customization' ); // Get option that was updated previously.

                    // Change the options array properly.
                    $gardenlogin_options["$key"] = $src;

                    // Update entire array again for save the attachment w.r.t $key.
                    update_option( $object, $gardenlogin_options );
                  }
                } // media_upload.
              } // images chaeck.
            } // inner foreach.
          } // gardenlogin_customization check.

          if ( 'gardenlogin_setting' == $object ) {

            $gardenlogin_options = get_option( 'gardenlogin_setting' );
            // Check $gardenlogin_options is exists.
            if ( isset( $gardenlogin_options ) && ! empty( $gardenlogin_options ) ) {

              foreach ( $array as $key => $value ) {

                // Array of gardenlogin Settings that import.
                $setting_array = array( 'session_expiration', 'login_with_email' );

                if ( in_array( $key, $setting_array ) ) {

                  // Change the options array properly.
                  $gardenlogin_options["$key"] = $value;
                  // Update array w.r.t $key exists.
                  update_option( $object, $gardenlogin_options );
                }
              } // inner foreach.
            } else {

              update_option( $object, $array );
            }
          } // gardenlogin_setting check.
        } // endforeach.
      } else {
        echo "error";
      }
      mcms_die();
    }

    /**
    * [Export GardenLogin Settings]
    * @return [string] [return settings in json formate]
    * @since 1.0.19
    */
    public function export(){

      $gardenlogin_db            = array();
      $gardenlogin_setting_opt   = array();
      $gardenlogin_customization = get_option( 'gardenlogin_customization' );
      $gardenlogin_setting       = get_option( 'gardenlogin_setting' );
      $gardenlogin_setting_fetch = array( 'session_expiration', 'login_with_email' );

      if ( $gardenlogin_customization ) {

        $gardenlogin_db['gardenlogin_customization'] = $gardenlogin_customization;
      }
      if ( $gardenlogin_setting ) {

        foreach ( $gardenlogin_setting as $key => $value) {
          if ( in_array( $key, $gardenlogin_setting_fetch ) ) {
            $gardenlogin_setting_opt[$key] = $value;
          }
        }
        $gardenlogin_db['gardenlogin_setting'] = $gardenlogin_setting_opt;
      }
      $gardenlogin_db = json_encode( $gardenlogin_db );

      echo $gardenlogin_db;

      mcms_die();
    }

    /**
    * [Download file from help information tab]
    * @return [string] [description]
    * @since 1.0.19
    */
    public function help() {

      include LOGINPRESS_DIR_PATH . 'classes/class-gardenlogin-log.php';

      echo GardenLogin_Log_Info::get_sysinfo();

      mcms_die();
    }

    /**
     * [deactivate get response from user on deactivating module]
     * @return [string] [response]
     * @since   1.0.15
     * @version 1.0.23
     */
    public function deactivate() {

      $email         = get_option( 'admin_email' );
      $_reason       = sanitize_text_field( mcms_unslash( $_POST['reason'] ) );
      $reason_detail = sanitize_text_field( mcms_unslash( $_POST['reason_detail'] ) );
      $reason        = '';

      if ( $_reason == '1' ) {
        $reason = 'I only needed the module for a short period';
      } elseif ( $_reason == '2' ) {
        $reason = 'I found a better module';
      } elseif ( $_reason == '3' ) {
        $reason = 'The module broke my site';
      } elseif ( $_reason == '4' ) {
        $reason = 'The module suddenly stopped working';
      } elseif ( $_reason == '5' ) {
        $reason = 'I no longer need the module';
      } elseif ( $_reason == '6' ) {
        $reason = 'It\'s a temporary deactivation. I\'m just debugging an issue.';
      } elseif ( $_reason == '7' ) {
        $reason = 'Other';
      }
      $fields = array(
        'email' 		        => $email,
        'website' 			    => get_site_url(),
        'action'            => 'Deactivate',
        'reason'            => $reason,
        'reason_detail'     => $reason_detail,
        'blog_language'     => get_bloginfo( 'language' ),
        'mandarincms_version' => get_bloginfo( 'version' ),
        'php_version'       => PHP_VERSION,
        'module_version'    => LOGINPRESS_VERSION,
        'module_name' 			=> 'GardenLogin Free',
      );

      $response = mcms_remote_post( LOGINPRESS_FEEDBACK_SERVER, array(
        'method'      => 'POST',
        'timeout'     => 5,
        'httpversion' => '1.0',
        'blocking'    => false,
        'headers'     => array(),
        'body'        => $fields,
      ) );

      mcms_die();
    }

    /**
     * Opt-out
     * @since  1.0.15
     */
    function optout_yes() {
      update_option( '_gardenlogin_optin', 'no' );
      mcms_die();
    }
  }

endif;
new GardenLogin_AJAX();
?>
