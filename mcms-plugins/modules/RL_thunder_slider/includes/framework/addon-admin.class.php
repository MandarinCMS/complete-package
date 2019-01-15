<?php

/**
 * The admin-specific functionality of the module.
 *
 * @link       h
 * @since      1.0.0
 *
 * @package    Rev_addon
 * @subpackage Rev_addon/admin
 */

/**
 * The admin-specific functionality of the module.
 *
 * Defines the module name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Rev_addon
 * @subpackage Rev_addon/admin
 * @author     MandarinCMS <info@jiiworks.net>
 */
class Rev_addon_Admin {

	/**
	 * The ID of this module.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $module_name    The ID of this module.
	 */
	private $module_name;

	/**
	 * The version of this module.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this module.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $module_name       The name of this module.
	 * @param      string    $version    The version of this module.
	 */
	public function __construct( $module_name, $version ) {

		$this->module_name = $module_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Rev_addon_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Rev_addon_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		if(isset($_GET["page"]) && $_GET["page"]=="rev_addon"){
			mcms_enqueue_style('rs-module-settings', RS_PLUGIN_URL .'admin/assets/css/admin.css', array(), ThunderSliderGlobals::SLIDER_REVISION);
			mcms_enqueue_style( $this->module_name, RS_PLUGIN_URL . 'admin/assets/css/rev_addon-admin.css', array( ), $this->version);
		}
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Rev_addon_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Rev_addon_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		if(isset($_GET["page"]) && $_GET["page"]=="rev_addon"){
			mcms_enqueue_script('tp-tools', RS_PLUGIN_URL .'public/assets/js/jquery.myskinpunch.tools.min.js', array(), ThunderSliderGlobals::SLIDER_REVISION );
			mcms_enqueue_script('unite_admin', RS_PLUGIN_URL .'admin/assets/js/admin.js', array(), ThunderSliderGlobals::SLIDER_REVISION );
			mcms_enqueue_script( $this->module_name, RS_PLUGIN_URL .'admin/assets/js/rev_addon-admin.js', array( 'jquery' ), $this->version, false );
			mcms_localize_script( $this->module_name, 'thunder_slider_addon', array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'please_wait_a_moment' => __("Please Wait a Moment",'thunderslider'),
					'settings_saved' => __("Settings saved",'thunderslider')
				));
		}
	}

	/**
	 * Register the administration menu for this module into the MandarinCMS Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_module_admin_menu() {
		$this->module_screen_hook_suffix = add_submenu_page(
			'thunderslider',
			__( 'Add-Ons', 'thunderslider' ),
			__( 'Add-Ons', 'thunderslider' ),
			'manage_options',
			$this->module_name,
			array( $this, 'display_module_admin_page' )
		);
	}

	/**
	 * Render the settings page for this module.
	 *
	 * @since    1.0.0
	 */
	public function display_module_admin_page() {
		include_once( RS_PLUGIN_PATH.'admin/views/rev_addon-admin-display.php' );
	}

	/**
	 * Activates Installed Add-On/Module
	 *
	 * @since    1.0.0
	 */
	public function activate_module() {
		// Verify that the incoming request is coming with the security nonce
		if( mcms_verify_nonce( $_REQUEST['nonce'], 'ajax_thunder_slider_addon_nonce' ) ) {
			if(isset($_REQUEST['module'])){
				//update_option( "thunder_slider_addon_gal_default", sanitize_text_field($_REQUEST['default_gallery']) );
				$result = activate_module( $_REQUEST['module'] );
				if ( is_mcms_error( $result ) ) {
					// Process Error
					die('0');
				}
				die( '1' );
			}
			else{
				die( '0' );
			}
		} 
		else {
			die( '-1' );
		}
	}

	/**
	 * Deactivates Installed Add-On/Module
	 *
	 * @since    1.0.0
	 */
	public function deactivate_module() {
		// Verify that the incoming request is coming with the security nonce
		if( mcms_verify_nonce( $_REQUEST['nonce'], 'ajax_thunder_slider_addon_nonce' ) ) {
			if(isset($_REQUEST['module'])){
				//update_option( "thunder_slider_addon_gal_default", sanitize_text_field($_REQUEST['default_gallery']) );
				$result = deactivate_modules( $_REQUEST['module'] );
				if ( is_mcms_error( $result ) ) {
					// Process Error
					die('0');
				}
				die( '1' );
			}
			else{
				die( '0' );
			}
		} 
		else {
			die( '-1' );
		}
	}

	/**
	 * Install Add-On/Module
	 *
	 * @since    1.0.0
	 */
	public function install_module() {
		if( mcms_verify_nonce( $_REQUEST['nonce'], 'ajax_thunder_slider_addon_nonce' ) ) {
			if(isset($_REQUEST['module'])){
				global $mcms_version, $rslb;
				
				$module_slug	= basename($_REQUEST['module']);
				$module_result	= false;
				$module_message	= 'UNKNOWN';

				if(0 !== strpos($module_slug, 'thunderslider-')) die( '-1' );
				
				$done	= false;
				$count	= 0;
				do{	
					$url = $rslb->get_url('updates');
					$url .= '/addons/'.$module_slug.'/'.$module_slug.'.zip';

					$get = mcms_remote_post($url, array(
						'user-agent' => 'MandarinCMS/'.$mcms_version.'; '.get_bloginfo('url'),
						'body' => '',
						'timeout' => 45
					));
					
					$response_code = mcms_remote_retrieve_response_code( $get );
					if($response_code == 200){
						$done = true;
					}else{
						$rslb->move_server_list();
					}
					
					$count++;
				}while($done == false && $count < 5);
				
				if( !$get || $get["response"]["code"] != "200" ){
				  $module_message = 'FAILED TO DOWNLOAD';
				}else{
					$module_message = 'ZIP is there';
					$upload_dir = mcms_upload_dir();
					$file = $upload_dir['basedir']. '/thunderslider/templates/' . $module_slug . '.zip';
					@mkdir(dirname($file));
					$ret = @file_put_contents( $file, $get['body'] );

					MCMS_Filesystem();

					global $mcms_filesystem;

					$upload_dir = mcms_upload_dir();
					$d_path = MCMS_PLUGIN_DIR;
					$unzipfile = unzip_file( $file, $d_path);

					if( is_mcms_error($unzipfile) ){
						define('FS_METHOD', 'direct'); //lets try direct. 

						MCMS_Filesystem();  //MCMS_Filesystem() needs to be called again since now we use direct !

						//@chmod($file, 0775);
						$unzipfile = unzip_file( $file, $d_path);
						if( is_mcms_error($unzipfile) ){
							$d_path = MCMS_PLUGIN_DIR;
							$unzipfile = unzip_file( $file, $d_path);

							if( is_mcms_error($unzipfile) ){
								$f = basename($file);
								$d_path = str_replace($f, '', $file);

								$unzipfile = unzip_file( $file, $d_path);
							}
						}
					}
					@unlink($file);
					die('1');
				}
				//$result = activate_module( $module_slug.'/'.$module_slug.'.php' );
			}
			else{
				die( '0' );
			}
		} 
		else {
			die( '-1' );
		}
	}

} // END of class