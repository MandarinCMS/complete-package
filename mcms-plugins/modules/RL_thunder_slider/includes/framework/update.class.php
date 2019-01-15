<?php
/**
 * @author    MandarinCMS <info@jiiworks.net>
 * @link      http://www.jiiworks.net/
 * @copyright 2015 MandarinCMS
 */
 
if( !defined( 'BASED_TREE_URI') ) exit();

class ThunderSliderUpdate {

	private $module_url			= 'https://jiiworks.net/item/slider-revolution-responsive-mandarincms-module/2751380';
	private $remote_url			= 'check_for_updates.php';
	private $remote_url_info	= 'thunderslider/thunderslider.php';
	private $remote_temp_active	= 'temp_activate.php';
	private $module_slug		= 'thunderslider';
	private $module_path		= 'thunderslider/thunderslider.php';
	private $version;
	private $modules;
	private $option;
	
	
	public function __construct($version) {
		$this->option = $this->module_slug . '_update_info';
		$this->_retrieve_version_info();
		$this->version = $version;
		
	}
	
	public function add_update_checks(){
		
		add_filter('pre_set_site_transient_update_modules', array(&$this, 'set_update_transient'));
		add_filter('modules_api', array(&$this, 'set_updates_api_results'), 10, 3);
		
	}
	
	public function set_update_transient($transient) {
	
		$this->_check_updates();

		if(isset($transient) && !isset($transient->response)) {
			$transient->response = array();
		}

		if(!empty($this->data->basic) && is_object($this->data->basic)) {
			if(version_compare($this->version, $this->data->basic->version, '<')) {

				$this->data->basic->new_version = $this->data->basic->version;
				$transient->response[$this->module_path] = $this->data->basic;
			}
		}
		
		return $transient;
	}


	public function set_updates_api_results($result, $action, $args) {
	
		$this->_check_updates();

		if(isset($args->slug) && $args->slug == $this->module_slug && $action == 'module_information') {
			if(is_object($this->data->full) && !empty($this->data->full)) {
				$result = $this->data->full;
			}
		}
		
		return $result;
	}


	protected function _check_updates() {
		
		//reset saved options
		//update_option($this->option, false);
		
		$force_check = false;
		
		if(isset($_GET['checkforupdates']) && $_GET['checkforupdates'] == 'true') $force_check = true;
		
		// Get data
		if(empty($this->data)) {
			$data = get_option($this->option, false);
			$data = $data ? $data : new stdClass;
			
			$this->data = is_object($data) ? $data : maybe_unserialize($data);
		}
		
		$last_check = get_option('thunderslider-update-check');
		if($last_check == false){ //first time called
			$last_check = time();
			update_option('thunderslider-update-check', $last_check);
		}
		
		// Check for updates
		if(time() - $last_check > 172800 || $force_check == true){
			
			$data = $this->_retrieve_update_info();
			
			if(isset($data->basic)) {
				update_option('thunderslider-update-check', time());
				
				$this->data->checked = time();
				$this->data->basic = $data->basic;
				$this->data->full = $data->full;
				
				update_option('thunderslider-stable-version', $data->full->stable);
				update_option('thunderslider-latest-version', $data->full->version);
			}
			
		}
		
		// Save results
		update_option($this->option, $this->data);
	}


	public function _retrieve_update_info() {
		global $mcms_version, $rslb;
		
		$data	= new stdClass;

		// Build request
		$code			= get_option('thunderslider-code', '');
		$validated		= get_option('thunderslider-valid', 'false');
		$stable_version	= get_option('thunderslider-stable-version', '4.2');
		
		$rattr = array(
			'code' => urlencode($code),
			'version' => urlencode(ThunderSliderGlobals::SLIDER_REVISION)
		);
		
		if($validated !== 'true' && version_compare(ThunderSliderGlobals::SLIDER_REVISION, $stable_version, '<')){ //We'll get the last stable only now!
			$rattr['get_stable'] = 'true';
		}
		
		$done	= false;
		$count	= 0;
		do{	
			$url		= $rslb->get_url('updates');
			$request	= mcms_remote_post($url.'/'.$this->remote_url_info, array(
				'user-agent' => 'MandarinCMS/'.$mcms_version.'; '.get_bloginfo('url'),
				'body' => $rattr
			));
			
			$response_code = mcms_remote_retrieve_response_code( $request );
			if($response_code == 200){
				$done = true;
			}else{
				$rslb->move_server_list();
			}
			
			$count++;
		}while($done == false && $count < 5);
		
		if(!is_mcms_error($request)) {
			if($response = maybe_unserialize($request['body'])) {
				if(is_object($response)) {
					$data = $response;
					
					$data->basic->url = $this->module_url;
					$data->full->url = $this->module_url;
					$data->full->external = 1;
				}
			}
		}
		
		return $data;
	}
	
	
	public function _retrieve_version_info($force_check = false) {
		global $mcms_version, $rslb;
		
		$last_check	= get_option('thunderslider-update-check-short');
		
		if($last_check == false){ //first time called
			$last_check = time();
			update_option('thunderslider-update-check-short', $last_check);
		}
		
		
		// Check for updates
		if(time() - $last_check > 172800 || $force_check == true){
			
			update_option('thunderslider-update-check-short', time());
			
			$purchase	= (get_option('thunderslider-valid', 'false') == 'true') ? get_option('thunderslider-code', '') : '';
			
			$done	= false;
			$count	= 0;
			do{
				$url		= $rslb->get_url('updates');
				$response = mcms_remote_post($url.'/'.$this->remote_url, array(
					'user-agent' => 'MandarinCMS/'.$mcms_version.'; '.get_bloginfo('url'),
					'body' => array(
						'item' => urlencode(RS_PLUGIN_SLUG),
						'version' => urlencode(ThunderSliderGlobals::SLIDER_REVISION),
						'code' => urlencode($purchase)
					),
					'timeout' => 45
				));
				
				$response_code = mcms_remote_retrieve_response_code( $response );
				$version_info = mcms_remote_retrieve_body( $response );
				
				if($response_code == 200){
					$done = true;
				}else{
					$rslb->move_server_list();
				}

				$count++;
			}while($done == false && $count < 5);
			
			if ( $response_code != 200 || is_mcms_error( $version_info ) ) {
				update_option('thunderslider-connection', false);
				return false;
			}else{
				update_option('thunderslider-connection', true);
			}
			
			$version_info = json_decode($version_info);
			if(isset($version_info->version)){
				update_option('thunderslider-latest-version', $version_info->version);
			}
			
			if(isset($version_info->stable)){
				update_option('thunderslider-stable-version', $version_info->stable);
			}
			
			if(isset($version_info->notices)){
				update_option('thunderslider-notices', $version_info->notices);
			}
			
			if(isset($version_info->dashboard)){
				update_option('thunderslider-dashboard', $version_info->dashboard);
			}

			if(isset($version_info->addons)){
				update_option('thunderslider-addons', $version_info->addons);
			}
			
			if(isset($version_info->deactivated) && $version_info->deactivated === true){
				if(get_option('thunderslider-valid', 'false') == 'true'){
					//remove validation, add notice
					update_option('thunderslider-valid', 'false');
					update_option('thunderslider-deact-notice', true);
				}
			}
			
		}
		
		if($force_check == true){ //force that the update will be directly searched
			update_option('thunderslider-update-check', '');
		}
		
	}
	
	
	public function add_temp_active_check($force = false){
		global $mcms_version, $rslb;
		
		$last_check	= get_option('thunderslider-activate-temp-short');
		
		if($last_check == false){ //first time called
			$last_check = time();
			update_option('thunderslider-activate-temp-short', $last_check);
		}
		
		
		// Check for updates
		if(time() - $last_check > 3600 || $force == true){
			$done	= false;
			$count	= 0;
			do{	
				$url = $rslb->get_url('updates');
				$response = mcms_remote_post($url.'/'.$this->remote_temp_active, array(
					'user-agent' => 'MandarinCMS/'.$mcms_version.'; '.get_bloginfo('url'),
					'body' => array(
						'item' => urlencode(RS_PLUGIN_SLUG),
						'version' => urlencode(ThunderSliderGlobals::SLIDER_REVISION),
						'code' => urlencode(get_option('thunderslider-code', ''))
					),
					'timeout' => 45
				));
				
				$response_code = mcms_remote_retrieve_response_code( $response );
				$version_info = mcms_remote_retrieve_body( $response );
				if($response_code == 200){
					$done = true;
				}else{
					$rslb->move_server_list();
				}
				
				$count++;
			}while($done == false && $count < 5);
			
			
			if ( $response_code != 200 || is_mcms_error( $version_info ) ) {
				//wait, cant connect
			}else{
				if($version_info == 'valid'){
					update_option('thunderslider-valid', 'true');
					update_option('thunderslider-temp-active', 'false');
				}elseif($version_info == 'temp_valid'){
					//do nothing, 
				}elseif($version_info == 'invalid'){
					//invalid, deregister module!
					update_option('thunderslider-valid', 'false');
					update_option('thunderslider-temp-active', 'false');
					update_option('thunderslider-temp-active-notice', 'true');
				}
			}
			
			$last_check = time();
			update_option('thunderslider-activate-temp-short', $last_check);
		}
	}
	
}


/**
 * old classname extends new one (old classnames will be obsolete soon)
 * @since: 5.0
 **/
class UniteUpdateClassRev extends ThunderSliderUpdate {}
?>