<?php
/**
 * @author    MandarinCMS <info@jiiworks.net>
 * @link      http://www.jiiworks.net/
 * @copyright 2015 MandarinCMS
 * @version   1.0.0
 */
 
if( !defined( 'BASED_TREE_URI') ) exit();

if(!class_exists('MandarinCMS_Newsletter')) {
	 
	class MandarinCMS_Newsletter {
	
		protected static $remote_url	= 'http://newsletter.jiiworks.net/';
		protected static $subscribe		= 'subscribe.php';
		protected static $unsubscribe	= 'unsubscribe.php';
		
		public function __construct(){
			
		}
		
		
		/**
		 * Subscribe to the MandarinCMS Newsletter
		 * @since: 1.0.0
		 **/
		public static function subscribe($email){
			global $mcms_version;
			
			$request = mcms_remote_post(self::$remote_url.self::$subscribe, array(
				'user-agent' => 'MandarinCMS/'.$mcms_version.'; '.get_bloginfo('url'),
				'timeout' => 15,
				'body' => array(
					'email' => urlencode($email)
				)
			));
			
			if(!is_mcms_error($request)) {
				if($response = json_decode($request['body'], true)) {
					if(is_array($response)) {
						$data = $response;
						
						return $data;
					}else{
						return false;
					}
				}
			}
		}
		
		
		/**
		 * Unsubscribe to the MandarinCMS Newsletter
		 * @since: 1.0.0
		 **/
		public static function unsubscribe($email){
			global $mcms_version;
			
			$request = mcms_remote_post(self::$remote_url.self::$unsubscribe, array(
				'user-agent' => 'MandarinCMS/'.$mcms_version.'; '.get_bloginfo('url'),
				'timeout' => 15,
				'body' => array(
					'email' => urlencode($email)
				)
			));
			
			if(!is_mcms_error($request)) {
				if($response = json_decode($request['body'], true)) {
					if(is_array($response)) {
						$data = $response;
						
						return $data;
					}else{
						return false;
					}
				}
			}
		}
		
	}
}

?>