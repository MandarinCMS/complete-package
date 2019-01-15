<?php
/**
* Module Name: GardenLogin - Customizing the MandarinCMS Login
* Description: GardenLogin is the best <code>mcms-login</code> Login Page Customizer module by <a href="https://jiiworks.net/">JIIWorks</a> which allows you to completely change the layout of login, register and forgot password forms.
* Version: 1.1.4
* Text Domain: gardenlogin
* Domain Path: /languages
*
* @package gardenlogin
* @category Core
* @author JIIWorks
*/


if ( ! class_exists( 'GardenLogin' ) ) :

  final class GardenLogin {

    /**
    * @var string
    */
    public $version = '1.1.4';

    /**
    * @var The single instance of the class
    * @since 1.0.0
    */
    protected static $_instance = null;

    /**
    * @var MCMS_Session session
    */
    public $session = null;

    /**
    * @var MCMS_Query $query
    */
    public $query = null;

    /**s
    * @var MCMS_Countries $countries
    */
    public $countries = null;

    /* * * * * * * * * *
    * Class constructor
    * * * * * * * * * */
    public function __construct() {

      $this->define_constants();
      $this->includes();
      $this->_hooks();

    }

    /**
    * Define GardenLogin Constants
    */
    private function define_constants() {

      $this->define( 'LOGINPRESS_PLUGIN_BASENAME', module_basename( __FILE__ ) );
      $this->define( 'LOGINPRESS_DIR_PATH', module_dir_path( __FILE__ ) );
      $this->define( 'LOGINPRESS_DIR_URL', module_dir_url( __FILE__ ) );
      $this->define( 'LOGINPRESS_ROOT_PATH',  dirname( __FILE__ ) . '/' );
      $this->define( 'LOGINPRESS_ROOT_FILE', __FILE__ );
      $this->define( 'LOGINPRESS_VERSION', $this->version );
      $this->define( 'LOGINPRESS_FEEDBACK_SERVER', 'https://jiiworks.net/' );
      }

    /**
    * Include required core files used in admin and on the frontend.
    */

    public function includes() {

      include_once( LOGINPRESS_DIR_PATH . 'include/compatibility.php' );
      include_once( LOGINPRESS_DIR_PATH . 'custom.php' );
      include_once( LOGINPRESS_DIR_PATH . 'classes/class-gardenlogin-setup.php' );
      include_once( LOGINPRESS_DIR_PATH . 'classes/class-gardenlogin-ajax.php' );
      include_once( LOGINPRESS_DIR_PATH . 'classes/class-gardenlogin-filter-module.php' );
      if ( is_multisite() ) {
  			require_once( LOGINPRESS_DIR_PATH . 'include/class-gardenlogin-myskin-template.php' );
      }

      $gardenlogin_setting = get_option( 'gardenlogin_setting' );

      $gardenlogin_privacy_policy = isset( $gardenlogin_setting['enable_privacy_policy'] ) ?  $gardenlogin_setting['enable_privacy_policy'] : 'off';
      if ( 'off' != $gardenlogin_privacy_policy ) {
        include_once( LOGINPRESS_DIR_PATH . 'include/privacy-policy.php' );
      }

			$login_with_email = isset( $gardenlogin_setting['login_order'] ) ?  $gardenlogin_setting['login_order'] : 'default';
      if ( 'default' != $login_with_email ) {
        include_once( LOGINPRESS_DIR_PATH . 'classes/class-gardenlogin-login-order.php' );
        new GardenLogin_Login_Order();
      }

      $enable_reg_pass_field = isset( $gardenlogin_setting['enable_reg_pass_field'] ) ?  $gardenlogin_setting['enable_reg_pass_field'] : 'off';
      if ( 'off' != $enable_reg_pass_field ) {
        include_once( LOGINPRESS_DIR_PATH . 'classes/class-gardenlogin-custom-password.php' );
        new GardenLogins_Custom_Password();
      }
    }

    /**
    * Hook into actions and filters
    * @since  1.0.0
    */
    private function _hooks() {

      add_action( 'admin_menu',             array( $this, 'register_options_page' ) );
      add_action( 'modules_loaded',         array( $this, 'textdomain' ) );
      add_filter( 'module_row_meta',        array( $this, '_row_meta'), 10, 2 );
      add_action( 'admin_enqueue_scripts',  array( $this, '_admin_scripts' ) );
      add_action( 'admin_footer',           array( $this, 'add_deactive_modal' ) );
			add_action( 'admin_init',             array( $this, 'gardenlogin_review_notice' ) );
      add_action( 'admin_init' ,            array( $this, 'gardenlogin_addon_notice' ) );
      add_action( 'module_action_links', 	  array( $this, 'gardenlogin_action_links' ), 10, 2 );
      add_action( 'admin_init',             array( $this, 'redirect_optin' ) );
      add_filter( 'auth_cookie_expiration', array( $this, '_change_auth_cookie_expiration' ), 10, 3 );
      //add_filter( 'modules_api',            array( $this, 'get_addon_info_' ) , 100, 3 );
      if ( is_multisite() ) {
  			add_action( 'admin_init',             array( $this, 'redirect_gardenlogin_edit_page' ) );
  			add_action( 'admin_init',             array( $this, 'check_gardenlogin_page' ) );
      }

    }

    /**
    * Redirect to Optin page.
    *
    * @since 1.0.15
    */
    function redirect_optin() {

      // delete_option( '_gardenlogin_optin' );

      if ( isset( $_POST['gardenlogin-submit-optout'] ) ) {

        update_option( '_gardenlogin_optin', 'no' );
        $this->_send_data( array(
          'action'	=>	'Skip',
        ) );

      } elseif ( isset( $_POST['gardenlogin-submit-optin'] ) ) {

        update_option( '_gardenlogin_optin', 'yes' );
        $fields = array(
          'action'	        =>	'Activate',
          'track_mailchimp' =>	'yes'
        );
        $this->_send_data( $fields );

      } elseif ( ! get_option( '_gardenlogin_optin' ) && isset( $_GET['page'] ) && ( $_GET['page'] === 'gardenlogin-settings' || $_GET['page'] === 'gardenlogin' || $_GET['page'] === 'abw' ) ) {

        mcms_redirect( admin_url('admin.php?page=gardenlogin-optin&redirect-page=' . $_GET['page'] ) );
        exit;
      } elseif ( get_option( '_gardenlogin_optin' ) && ( get_option( '_gardenlogin_optin' ) == 'yes' || get_option( '_gardenlogin_optin' ) == 'no' ) && isset( $_GET['page'] ) && $_GET['page'] === 'gardenlogin-optin' ) {

        mcms_redirect( admin_url( 'admin.php?page=gardenlogin-settings' ) );
        exit;
      }
    }


    /**
    * Main Instance
    *
    * @since 1.0.0
    * @static
    * @see loginPress_loader()
    * @return Main instance
    */
    public static function instance() {
      if ( is_null( self::$_instance ) ) {
        self::$_instance = new self();
      }
      return self::$_instance;
    }


    /**
    * Load Languages
    * @since 1.0.0
    */
    public function textdomain() {

      $module_dir =  dirname( module_basename( __FILE__ ) ) ;
      load_module_textdomain( 'gardenlogin', false, $module_dir . '/languages/' );
    }

    /**
    * Define constant if not already set
    * @param  string $name
    * @param  string|bool $value
    */
    private function define( $name, $value ) {
      if ( ! defined( $name ) ) {
        define( $name, $value );
      }
    }

    /**
    * Init JIIWorks when MandarinCMS Initialises.
    */
    public function init() {
      // Before init action
    }

    /**
		 * Create GardenLogin Page Template.
		 *
		 * @since 1.1.3
		 */
		public function check_gardenlogin_page() {

			// Retrieve the Login Designer admin page option, that was created during the activation process.
			$option = $this->get_gardenlogin_page();

      include LOGINPRESS_DIR_PATH . 'include/create-gardenlogin-page.php';
			// Retrieve the status of the page, if the option is available.
			if ( $option ) {
				$page   = get_post( $option );
				$status = $page->post_status;
			} else {
				$status = null;
			}

			// Check the status of the page. Let's fix it, if the page is missing or in the trash.
			if ( empty( $status ) || 'trash' === $status ) {
				new GardenLogin_Page_Create();
			}
		}

    /**
		 * function for redirect the GardenLogin page on editing.
		 *
		 * @since 1.1.3
		 */
		public function redirect_gardenlogin_edit_page() {
			global $pagenow;

			$page = $this->get_gardenlogin_page();

			if ( ! $page ) {
				return;
			}

			$page_url = get_permalink( $page );
			$page_id  = get_post( $page );
			$page_id  = $page->ID;

			// Generate the redirect url.
			$url = add_query_arg(
				array(
					'autofocus[section]' => 'gardenlogin_panel',
					'url'                => rawurlencode( $page_url ),
				),
				admin_url( 'customize.php' )
			);

			/* Check current admin page. */
			if ( $pagenow == 'post.php' && isset( $_GET['post'] ) && $_GET['post'] == $page_id ) {
				mcms_safe_redirect( $url );
			}
		}

    /**
    * Add new page in Apperance to customize Login Page
    */
    public function register_options_page() {

      add_submenu_page( null, __( 'Activate', 'gardenlogin' ), __( 'Activate', 'gardenlogin' ), 'manage_options', 'gardenlogin-optin', array( $this, 'render_optin' )  );

      add_myskin_page( __( 'GardenLogin', 'gardenlogin' ), __( 'GardenLogin', 'gardenlogin' ), 'manage_options', "abw", '__return_null' );
    }


    /**
     * Show Opt-in Page.
     *
     * @since 1.0.15
     */
		function render_optin() {
			include LOGINPRESS_DIR_PATH . 'include/gardenlogin-optin-form.php';
		}

    /**
    * Wrapper function to send data.
    * @param  [arrays]  $args.
    *
    * @since  1.0.15
    * @version 1.0.23
    */
 function _send_data( $args ) {

   $cuurent_user = mcms_get_current_user();
   $fields = array(
     'email' 		         => get_option( 'admin_email' ),
     'website' 			     => get_site_url(),
     'action'            => '',
     'reason'            => '',
     'reason_detail'     => '',
     'display_name'			 => $cuurent_user->display_name,
     'blog_language'     => get_bloginfo( 'language' ),
     'mandarincms_version' => get_bloginfo( 'version' ),
     'php_version'       => PHP_VERSION,
     'module_version'    => LOGINPRESS_VERSION,
     'module_name' 			 => 'GardenLogin Free',
   );

   $args = array_merge( $fields, $args );
   $response = mcms_remote_post( LOGINPRESS_FEEDBACK_SERVER, array(
     'method'      => 'POST',
     'timeout'     => 5,
     'httpversion' => '1.0',
     'blocking'    => true,
     'headers'     => array(),
     'body'        => $args,
   ) );

  //  echo '<pre>'; print_r( $args ); echo '</pre>';

 }

   /**
    * Session Expiration
    *
    * @since  1.0.18
    */
   function _change_auth_cookie_expiration( $expiration, $user_id, $remember ) {

     $gardenlogin_setting  = get_option( 'gardenlogin_setting' );
     $_expiration =  isset( $gardenlogin_setting['session_expiration'] ) ? intval( $gardenlogin_setting['session_expiration'] ) : '';

     if ( empty( $_expiration ) || '0' == $_expiration ) {
       return $expiration;
     }

      $expiration  = $_expiration * 60; // Duration of the expiration period in seconds.

     return $expiration;
   }

    /**
     * Load JS or CSS files at admin side and enqueue them
     * @param  string tell you the Page ID
     * @return void
     */
    function _admin_scripts( $hook ) {

      if( $hook == 'toplevel_page_gardenlogin-settings' || $hook == 'gardenlogin_page_gardenlogin-help' || $hook == 'gardenlogin_page_gardenlogin-import-export' || $hook == 'gardenlogin_page_gardenlogin-license' ) {

        mcms_enqueue_style( 'gardenlogin_stlye', modules_url( 'css/style.css', __FILE__ ), array(), LOGINPRESS_VERSION );
        mcms_enqueue_script( 'gardenlogin_js', modules_url( 'js/admin-custom.js', __FILE__ ), array(), LOGINPRESS_VERSION );

        // Array for localize.
        $gardenlogin_localize = array(
          'module_url' => modules_url(),
        );

        mcms_localize_script( 'gardenlogin_js', 'gardenlogin_script', $gardenlogin_localize );
      }

    }


    public function _row_meta( $links, $file ) {
      return $links;
    }

    /**
     * Add deactivate modal layout.
     */
    function add_deactive_modal() {
      global $pagenow;

      if ( 'modules.php' !== $pagenow ) {
        return;
      }

      include LOGINPRESS_DIR_PATH . 'include/deactivate_modal.php';
      include LOGINPRESS_DIR_PATH . 'include/gardenlogin-optout-form.php';
    }

  /**
   * Module activation
   *
   * @since  1.0.15
   * @version 1.0.22
   */
  static function module_activation() {

    $gardenlogin_key     = get_option( 'gardenlogin_customization' );
    $gardenlogin_setting = get_option( 'gardenlogin_setting' );

    // Create a key 'gardenlogin_customization' with empty array.
    if ( ! $gardenlogin_key ) {
      update_option( 'gardenlogin_customization', array() );
    }

    // Create a key 'gardenlogin_setting' with empty array.
    if ( ! $gardenlogin_setting ) {
      update_option( 'gardenlogin_setting', array() );
    }
  }

  static function module_uninstallation() {

    $email         = get_option( 'admin_email' );

    $fields = array(
      'email' 		        => $email,
      'website' 			    => get_site_url(),
      'action'            => 'Uninstall',
      'reason'            => '',
      'reason_detail'     => '',
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

  }

  /**
	 * Ask users to review our module on mandarincms.org
	 *
	 * @since 1.1.3
	 * @return boolean false
	 */
	public function gardenlogin_addon_notice() {

		$this->gardenlogin_addon_dismissal();

		$activation_time 	= get_site_option( 'gardenlogin_addon_active_time' );
		$addon_dismissal	= get_site_option( 'gardenlogin_addon_dismiss_1' );

		if ( 'yes' == $addon_dismissal ) return;

		if ( ! $activation_time ) :

			$activation_time = time();
			add_site_option( 'gardenlogin_addon_active_time', $activation_time );
		endif;

		// 432000 = 5 Days in seconds.
		// if ( time() - $activation_time > 432000 ) :

			add_action( 'admin_notices' , array( $this, 'gardenlogin_addon_notice_text' ) );
		// endif;

	}

  /**
	 * Ask users to review our module on mandarincms.org
	 *
	 * @since 1.0.11
	 * @return boolean false
	 * @version 1.1.3
	 */
	public function gardenlogin_review_notice() {

		$this->gardenlogin_review_dismissal();
		$this->gardenlogin_review_pending();

		$activation_time 	= get_site_option( 'gardenlogin_active_time' );
		$review_dismissal	= get_site_option( 'gardenlogin_review_dismiss' );

		if ( 'yes' == $review_dismissal ) return;

		if ( ! $activation_time ) :

			$activation_time = time();
			add_site_option( 'gardenlogin_active_time', $activation_time );
		endif;

		// 1296000 = 15 Days in seconds.
		if ( time() - $activation_time > 1296000 ) :

      mcms_enqueue_style( 'gardenlogin_review_stlye', modules_url( 'css/style-review.css', __FILE__ ), array(), LOGINPRESS_VERSION );
			add_action( 'admin_notices' , array( $this, 'gardenlogin_review_notice_message' ) );
		endif;

	}

  /**
	 *	Check and Dismiss review message.
	 *
	 *	@since 1.0.11
	 */
	private function gardenlogin_review_dismissal() {

		if ( ! is_admin() ||
			! current_user_can( 'manage_options' ) ||
			! isset( $_GET['_mcmsnonce'] ) ||
			! mcms_verify_nonce( sanitize_key( mcms_unslash( $_GET['_mcmsnonce'] ) ), 'gardenlogin-review-nonce' ) ||
			! isset( $_GET['gardenlogin_review_dismiss'] ) ) :

			return;
		endif;

		add_site_option( 'gardenlogin_review_dismiss', 'yes' );
	}

  /**
	 * Set time to current so review notice will popup after 14 days
	 *
	 * @since 1.0.11
	 */
	function gardenlogin_review_pending() {

		if ( ! is_admin() ||
			! current_user_can( 'manage_options' ) ||
			! isset( $_GET['_mcmsnonce'] ) ||
			! mcms_verify_nonce( sanitize_key( mcms_unslash( $_GET['_mcmsnonce'] ) ), 'gardenlogin-review-nonce' ) ||
			! isset( $_GET['gardenlogin_review_later'] ) ) :

			return;
		endif;

		// Reset Time to current time.
		update_site_option( 'gardenlogin_active_time', time() );
	}

  /**
	 * Review notice message
	 *
	 * @since  1.0.11
	 */
	public function gardenlogin_review_notice_message() {

		$scheme      = ( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_QUERY ) ) ? '&' : '?';
		$url         = $_SERVER['REQUEST_URI'] . $scheme . 'gardenlogin_review_dismiss=yes';
		$dismiss_url = mcms_nonce_url( $url, 'gardenlogin-review-nonce' );

		$_later_link = $_SERVER['REQUEST_URI'] . $scheme . 'gardenlogin_review_later=yes';
		$later_url   = mcms_nonce_url( $_later_link, 'gardenlogin-review-nonce' );
    ?>

		<div class="gardenlogin-review-notice">
			<div class="gardenlogin-review-thumbnail">
				<img src="<?php echo modules_url( 'img/thumbnail/gray-gardenlogin.png', __FILE__ ) ?>" alt="">
			</div>
			<div class="gardenlogin-review-text">
				<h3><?php _e( 'Leave A Review?', 'gardenlogin' ) ?></h3>
				<p><?php _e( 'We hope you\'ve enjoyed using GardenLogin! Would you consider leaving us a review on MandarinCMS.org?', 'gardenlogin' ) ?></p>
				<ul class="gardenlogin-review-ul">
          <li><a href="https://mandarincms.org/support/view/module-reviews/gardenlogin?rate=5#postform" target="_blank"><span class="dashicons dashicons-external"></span><?php _e( 'Sure! I\'d love to!', 'gardenlogin' ) ?></a></li>
          <li><a href="<?php echo $dismiss_url ?>"><span class="dashicons dashicons-smiley"></span><?php _e( 'I\'ve already left a review', 'gardenlogin' ) ?></a></li>
          <li><a href="<?php echo $later_url ?>"><span class="dashicons dashicons-calendar-alt"></span><?php _e( 'Maybe Later', 'gardenlogin' ) ?></a></li>
          <li><a href="<?php echo $dismiss_url ?>"><span class="dashicons dashicons-dismiss"></span><?php _e( 'Never show again', 'gardenlogin' ) ?></a></li></ul>
			</div>
		</div>
	<?php
	}

  /**
   * Review notice message
   *
   * @since  1.1.3
   */
  public function gardenlogin_addon_notice_text() {
  }

  /**
	 *	Check and Dismiss addon message.
	 *
	 *	@since 1.1.3
	 */
	private function gardenlogin_addon_dismissal() {

		if ( ! is_admin() ||
			! current_user_can( 'manage_options' ) ||
			! isset( $_GET['_mcmsnonce'] ) ||
			! mcms_verify_nonce( sanitize_key( mcms_unslash( $_GET['_mcmsnonce'] ) ), 'gardenlogin-addon-nonce' ) ||
			! isset( $_GET['gardenlogin_addon_dismiss_1'] ) ) :

			return;
		endif;

		add_site_option( 'gardenlogin_addon_dismiss_1', 'yes' );
	}

  /**
	 * Pull the Login Designer page from options.
	 *
	 * @access public
	 */
	public function get_gardenlogin_page() {

		$gardenlogin_settings = get_option( 'gardenlogin_setting', array() );
		$page = array_key_exists( 'gardenlogin_page', $gardenlogin_settings ) ? get_post( $gardenlogin_settings['gardenlogin_page'] ) : false;

		return $page;
	}

  /**
	 * Add a link to the settings page to the modules list
	 *
	 * @since  1.0.11
	 */
	public function gardenlogin_action_links( $links, $file ) {

		static $this_module;

		if ( empty( $this_module ) ) {

			$this_module = 'gardenlogin/gardenlogin.php';
		}

		if ( $file == $this_module ) {

			$settings_link = sprintf( esc_html__( '%1$s Settings %2$s | %3$s Customize %4$s', 'gardenlogin' ), '<a href="' . admin_url( 'admin.php?page=gardenlogin-settings' ) . '">', '</a>', '<a href="' . admin_url( 'admin.php?page=gardenlogin' ) . '">', '</a>' );


      if( 'yes' == get_option( '_gardenlogin_optin' ) ){
        $settings_link .= sprintf( esc_html__( ' | %1$s Opt Out %2$s ', 'gardenlogin'), '<a class="opt-out" href="' . admin_url( 'admin.php?page=gardenlogin-settings' ) . '">', '</a>' );
      } else {
        $settings_link .= sprintf( esc_html__( ' | %1$s Opt In %2$s ', 'gardenlogin'), '<a href="' . admin_url( 'admin.php?page=gardenlogin-optin&redirect-page=' .'gardenlogin-settings' ) . '">', '</a>' );
      }

      array_unshift( $links, $settings_link );

      if ( ! has_action( 'gardenlogin_pro_add_template' ) ) :
        $pro_link = sprintf( esc_html__( '%1$s %3$s Upgrade Pro %4$s %2$s', 'gardenlogin' ),  '<a href="https://jiiworks.net/mandarincms/modules/gardenlogin-pro/?utm_source=gardenlogin-lite&utm_medium=module-action-link&utm_campaign=pro-upgrade" target="_blank">', '</a>', '<span class="gardenlogin-dasboard-pro-link">', '</span>' );
        array_push( $links, $pro_link );
      endif;
		}

		return $links;
	}

  // function get_addon_info_( $api, $action, $args ) {

  //   if ( $action == 'module_information' && empty( $api ) && ( ! empty( $_GET['lgp'] )  ) ) {

  //     $raw_response = mcms_remote_post( 'https://jiiworks.net/gardenlogin-api/search.php', array(
  //       'body' => array(
  //         'slug' => $args->slug
  //       ) )
  //      );

  //      if ( is_mcms_error( $raw_response ) || $raw_response['response']['code'] != 200 ) {
  //        return false;
  //      }

  // 		$module = unserialize( $raw_response['body'] );

  //     $api                = new stdClass();
  //     $api->name          = $module['title'];
  //     $api->version       = $module['version'];
  //     $api->download_link = $module['download_url'];
  //     $api->tested        = '10.0';

  //   }

  //   return $api;
  // }
} // End Of Class.
endif;


/**
* Returns the main instance of MCMS to prevent the need to use globals.
*
* @since  1.0.0
* @return GardenLogin
*/
function loginPress_loader() {
  return GardenLogin::instance();
}

// Call the function
loginPress_loader();

/**
* Create the Object of Custom Login Entites and Settings.
*
* @since  1.0.0
*/
new GardenLogin_Entities();
new GardenLogin_Settings();

/**
* Create the Object of Remote Notification.
*
* @since  1.0.9
*/
if (!class_exists('TAV_Remote_Notification_Client')) {
  require( LOGINPRESS_ROOT_PATH . 'include/class-remote-notification-client.php' );
}
$notification = new TAV_Remote_Notification_Client( 125, '16765c0902705d62', 'http://jiiworks.net?post_type=notification' );

register_activation_hook( __FILE__, array( 'GardenLogin', 'module_activation' ) );
register_uninstall_hook( __FILE__, array( 'GardenLogin', 'module_uninstallation' ) );
?>
