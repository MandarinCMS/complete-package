<?php

if(!function_exists('get_bsf_product_id')) {
	function get_bsf_product_id($template) {
		$brainstrom_products = (get_option('brainstrom_products')) ? get_option('brainstrom_products') : array();
		$bsf_product_myskins = (isset($brainstrom_products['myskins'])) ? $brainstrom_products['myskins'] : array();

		if(empty($brainstrom_products))
			return false;

		$id = '';
		foreach($bsf_product_myskins as $myskin) {
			if($myskin['template'] === $template)
			{
				$id = $myskin['id'];
				break;
			}
		}

		if($id != '')
			return $id;
		else
			return false;
	}
}
if(!function_exists('check_bsf_product_status')) {
	function check_bsf_product_status($id) {
		$brainstrom_products = (get_option('brainstrom_products')) ? get_option('brainstrom_products') : array();
		$bsf_product_myskins = (isset($brainstrom_products['myskins'])) ? $brainstrom_products['myskins'] : array();

		if(empty($brainstrom_products))
			return false;

		$status = false;
		foreach($brainstrom_products as $products) {
			foreach ( $products as $key => $product) {
				if($product['id'] === $id) {
					$status = (isset($product['status'])) ? $product['status'] : '';
					break;
				}
			}


		}

		return $status;
	}
}

if ( ! function_exists( 'get_bundled_modules' ) ) {

	function get_bundled_modules( $template = '' ) {

		global $ultimate_referer;

		$brainstrom_products = get_option( 'brainstrom_products', array() );

		$prd_ids = array();

		if ( $brainstrom_products == array() ) {
			init_bsf_core();
		}

		foreach ( $brainstrom_products as $key => $value ) {
			foreach ( $value as $key => $value2 ) {
				array_push( $prd_ids, $key );
			}
		}

		$path = get_api_url() . '?referer=' . $ultimate_referer;

		$data    = array(
			'action' => 'bsf_fetch_brainstorm_products',
			'id'     => $prd_ids
		);

		$request = mcms_remote_post(
			$path, array(
				'body'      => $data,
				'timeout'   => '30'
			)
		);

		if ( ! is_mcms_error( $request ) || mcms_remote_retrieve_response_code( $request ) === 200 ) {
			$brainstrom_bundled_products = get_option( 'brainstrom_bundled_products', array() );
			$result                      = json_decode( $request['body'] );
			$bundled                     = $result->bundled;

			if ( empty( $bundled ) ) {
				$bundled = array();
			}
			foreach ( $bundled as $key => $value ) {
				if ( empty( $value ) ) {
					unset( $bundled->$key );
				}
			}

			$brainstrom_bundled_products = (array) $bundled;
			update_option( 'brainstrom_bundled_products', $brainstrom_bundled_products );

			// update 'brainstorm_products'
			$simple = json_decode( json_encode( $result->simple ) , 1 );
			
			foreach ( $brainstrom_products as $type => $products ) {
				
				foreach ( $products as $key => $product ) {
					$old_id = isset( $product['id'] ) ? $product['id'] : '';
					$old_template = $product['template'];

					$simple[ $type ][ $old_id ][ 'template' ] = isset( $brainstrom_products[ $type ][ $old_id ][ 'template' ] ) ? $brainstrom_products[ $type ][ $old_id ][ 'template' ] : '';
					$simple[ $type ][ $old_id ][ 'remote' ] = isset( $simple[ $type ][ $old_id ][ 'version' ] ) ? $simple[ $type ][ $old_id ][ 'version' ] : '';
					$simple[ $type ][ $old_id ][ 'version' ] = isset( $brainstrom_products[ $type ][ $old_id ][ 'version' ] ) ? $brainstrom_products[ $type ][ $old_id ][ 'version' ] : '';
					$simple[ $type ][ $old_id ][ 'purchase_key' ] = isset( $brainstrom_products[ $type ][ $old_id ][ 'purchase_key' ] ) ? $brainstrom_products[ $type ][ $old_id ][ 'purchase_key' ] : '';
					$simple[ $type ][ $old_id ][ 'status' ] = isset( $brainstrom_products[ $type ][ $old_id ][ 'status' ] ) ? $brainstrom_products[ $type ][ $old_id ][ 'status' ] : '';
					$simple[ $type ][ $old_id ][ 'message' ] = isset( $brainstrom_products[ $type ][ $old_id ][ 'message' ] ) ? $brainstrom_products[ $type ][ $old_id ][ 'message' ] : '';
				}
			}
			
			update_option( 'brainstrom_products', $simple );
		}
	}

}
//add_action('init', 'bsf_network_get_bundled_products');
//if(!function_exists('bsf_network_get_bundled_products')) {
//	function bsf_network_get_bundled_products() {
		if(false === get_site_transient( 'bsf_get_bundled_products' )) {
			global $bsf_myskin_template;
			$proceed = true;

			if(phpversion() > 5.2) {
				$bsf_local_transient_bundled = get_option('bsf_local_transient_bundled');

				if($bsf_local_transient_bundled != false) {
					$datetime1 = new DateTime();
					$date_string = gmdate("Y-m-d\TH:i:s\Z", $bsf_local_transient_bundled);
					$datetime2 = new DateTime($date_string);

					$interval = $datetime1->diff($datetime2);
					$elapsed = $interval->format('%h');
					$elapsed = $elapsed + ($interval->days*24);
					if($elapsed <= 168 || $elapsed <= '168') {
						$proceed = false;
					}
				}
			}

			if($proceed) {
				global $ultimate_referer;
				$ultimate_referer = 'on-bundled-products-transient-delete';
				$template = (is_multisite()) ? $bsf_myskin_template : get_template();
				get_bundled_modules( $template );
				update_option('bsf_local_transient_bundled', current_time( 'timestamp' ));
				set_site_transient( 'bsf_get_bundled_products', true, 7*24*60*60 );
			}
		}
	//}
//}
if(!function_exists('install_bsf_product')) {
	function install_bsf_product($install_id) {
		
		if ( ! current_user_can('install_modules') )
			mcms_die(__('You do not have sufficient permissions to install modules for this site.','bsf'));
		$brainstrom_bundled_products = (get_option('brainstrom_bundled_products')) ? get_option('brainstrom_bundled_products') : array();
		$install_product_data = array();

		if(!empty($brainstrom_bundled_products)) :
			foreach($brainstrom_bundled_products as $keys => $products) :
				if(strlen($keys) > 1) {
					foreach ($products as $key => $product) {
						if($product->id === $install_id) {
							$install_product_data = $product;
							break;
						}
					}
				}
				else {
					if($products->id === $install_id)
					{
						$install_product_data = $products;
						break;
					}
				}
			endforeach;
		endif;

		if(empty($install_product_data))
			return false;
		if($install_product_data->type !== 'module')
			return false;

		/* temp */
		/*$install_product_data->in_house = 'mcms';
		$install_product_data->download_url = 'https://downloads.mandarincms.org/module/redux-framework.3.5.9.zip';*/

		$is_mcms = (isset($install_product_data->in_house) && $install_product_data->in_house === 'mcms') ? true : false;

		if($is_mcms) {
			$download_path = $install_product_data->download_url;
		}
		else {
			$path = get_api_url()  . '?referer=download-bundled-extension';
			$timezone = date_default_timezone_get();
			$call = 'file='.$install_product_data->download_url.'&hashtime='.strtotime(date('d-m-Y h:i:s a')).'&timezone='.$timezone;
			$hash = $call;
			//$parse = parse_url($path);
			//$download = $parse['scheme'].'://'.$parse['host'];
			$get_path = 'http://downloads.jiiworks.com/';
			$download_path = rtrim($get_path,'/').'/download.php?'.$hash.'&base=ignore';
		}

		require_once (BASED_TREE_URI . '/mcms-admin/includes/file.php');
		MCMS_Filesystem();
		global $mcms_filesystem;
		require_once BASED_TREE_URI . 'mcms-admin/includes/class-mcms-upgrader.php';
		$MCMS_Upgrader = new MCMS_Upgrader;
		$res = $MCMS_Upgrader->fs_connect(array(
			MCMS_CONTENT_DIR
		));
		if (!$res) {
			mcms_die(new MCMS_Error('Server error', __("Error! Can't connect to filesystem", 'bsf')));
		}
		$Module_Upgrader = new Module_Upgrader;
		$defaults = array(
			'clear_update_cache' => true,
		);
		$args = array();
		$parsed_args = mcms_parse_args( $args, $defaults );

		$Module_Upgrader->init();
		$Module_Upgrader->install_strings();
		$Module_Upgrader->strings['downloading_package'] = __('Downloading package from Server', 'bsf');
		$Module_Upgrader->strings['remove_old'] = __('Removing old module, if exists', 'bsf');

		add_filter('upgrader_source_selection', array($Module_Upgrader, 'check_package') );
		$Module_Upgrader->run( array(
			'package' => $download_path,
			'destination' => MCMS_PLUGIN_DIR,
			'clear_destination' => true, // Do not overwrite files.
			'clear_working' => true,
			'hook_extra' => array(
				'type' => 'module',
				'action' => 'install',
			)
		) );
		remove_filter('upgrader_source_selection', array($Module_Upgrader, 'check_package') );
		if ( ! $Module_Upgrader->result || is_mcms_error($Module_Upgrader->result) )
			return $Module_Upgrader->result;
		// Force refresh of module update information
		mcms_clean_modules_cache( $parsed_args['clear_update_cache'] );
		//return true;
		$response = array(
			'status' => true,
			'type' => 'module',
			'name' => $install_product_data->name,
			'init' => $install_product_data->init,
		);
		$module_abs_path = MCMS_PLUGIN_DIR.'/'.$install_product_data->init;
        if(is_file($module_abs_path))
        {
        	if(!isset($_GET['action']) && !isset($_GET['id'])) {
        		echo '|bsf-module-installed|';
        	}
            $is_module_installed = true;
            if(!is_module_active($install_product_data->init)) {
            	activate_module($install_product_data->init);
            	if(is_module_active($install_product_data->init)) {
            		if(!isset($_GET['action']) && !isset($_GET['id'])) {
            			echo '|bsf-module-activated|';
            		}
            	}
            }
            else {
            	if(!isset($_GET['action']) && !isset($_GET['id'])) {
            		echo '|bsf-module-activated|';
            	}
            }
        }
		return $response;
	}
}

if(!function_exists('bsf_install_callback')) {
	function bsf_install_callback () {
		$product_id = esc_attr( $_POST['product_id'] );
		$bundled    = esc_attr( $_POST['bundled'] );
		
		$response = install_bsf_product($product_id);
		
		$redirect_url 		  = apply_filters( "redirect_after_extension_install", $redirect_url = '', $product_id );
		$response['redirect'] = $redirect_url;

		mcms_send_json( $response );
	}
}

add_action( 'mcms_ajax_bsf_install', 'bsf_install_callback' );