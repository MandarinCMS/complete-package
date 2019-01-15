<?php
/**
 * @author    MandarinCMS <info@jiiworks.net>
 * @link      http://www.jiiworks.net/
 * @copyright 2017 MandarinCMS
 */
 
if( !defined( 'BASED_TREE_URI') ) exit();

class ThunderSliderLoadBalancer {
	
	public $servers = array();
	
	/**
	 * set the server list on construct
	 **/
	public function __construct(){
		$this->servers = get_option('thunderslider_servers', array());
		$this->servers = (empty($this->servers)) ? array('myskinpunch.tools') : $this->servers; //, 'myskinpunch-ext-a.tools'
	}
	
	/**
	 * get the url depending on the purpose, here with key, you can switch do a different server
	 **/
	public function get_url($purpose, $key = 0){
		$url = 'https://';
		
		$use_url = (!isset($this->servers[$key])) ? reset($this->servers) : $this->servers[$key];
		
		//$use_url = 'myskinpunch.tools';
		switch($purpose){
			case 'updates':
				$url .= 'updates.';
				break;
			case 'templates':
				$url .= 'templates.';
				break;
			case 'library':
				$url .= 'library.';
				break;
			default:
				return false;
		}
		
		$url .= $use_url;
		
		return $url;
	}
	
	/**
	 * refresh the server list to be used, will be done once in a month
	 **/
	public function refresh_server_list($force = false){
		global $mcms_version;
		
		$last_check = get_option('thunderslider_server_refresh', false);
		
		if($force === true || $last_check === false || time() - $last_check > 60 * 60 * 24 * 14){
			//$url = $this->get_url('updates');
			$url		= 'https://updates.myskinpunch.tools';
			$count		= 0;
			/*$response	= false;
			do{*/
				$request	= mcms_remote_post($url.'/get_server_list.php', array(
					'user-agent' => 'MandarinCMS/'.$mcms_version.'; '.get_bloginfo('url'),
					'body' => array(
						'item' => urlencode(RS_PLUGIN_SLUG),
						'version' => urlencode(ThunderSliderGlobals::SLIDER_REVISION)
					),
					'timeout' => 45
				));
				if(!is_mcms_error($request)){
					if($response = maybe_unserialize($request['body'])){
						$list = json_decode($response, true);
						update_option('thunderslider_servers', $list);
					}
				}/*else{
					$url = $this->get_url('updates');
				}
				$count++;
			}while($response === false && $count < 5);*/
			
			update_option('thunderslider_server_refresh', time());
		}
	}
	
	/**
	 * move the server list, to take the next server as the one currently seems unavailable
	 **/
	public function move_server_list(){
		
		$servers = $this->servers;
		
		$a = array_shift($servers);
		$servers[] = $a;
		
		$this->servers = $servers;
		update_option('thunderslider_servers', $servers);
	}
}

?>