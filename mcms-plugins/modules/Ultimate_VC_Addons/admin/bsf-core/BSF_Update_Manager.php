<?php

// delete these transients/options for debugging
// set_site_transient( 'update_modules', null );
// set_site_transient( 'update_myskins', null );
// delete_option( 'brainstrom_products' );

/**
 *
 */
if ( ! class_exists( 'BSF_Update_Manager' ) ) {

	class BSF_Update_Manager {

		public function __construct() {

			// update data to MandarinCMS's transient
			add_filter( 'pre_set_site_transient_update_modules', array(
				$this,
				'brainstorm_update_modules_transient'
			) );
			add_filter( 'pre_set_site_transient_update_myskins', array( $this, 'brainstorm_update_myskins_transient' ) );

			// display changelog in update details
			add_filter( 'modules_api', array( $this, 'bsf_get_module_information' ), 10, 3 );

			// display correct error messages
			add_action( 'load-modules.php', array( $this, 'bsf_update_display_license_link' ) );
			add_filter( 'upgrader_pre_download', array( $this, 'bsf_change_no_package_message' ), 20, 3 );
		}

		public function brainstorm_update_modules_transient( $_transient_data ) {

			global $pagenow;

			if ( ! is_object( $_transient_data ) ) {
				$_transient_data = new stdClass;
			}

			$update_data = $this->bsf_update_transient_data( 'modules' );
			
			foreach ( $update_data as $key => $product ) {

				if ( isset( $product['template'] ) && $product['template'] != '' ) {
					$template = $product['template'];
				} elseif ( isset( $product['init'] ) && $product['init'] != '' ) {
					$template = $product['init'];
				}

				if ( isset( $_transient_data->response[ $template ] ) ) {
					continue;
				}

				$module              = new stdClass();
				$module->id          = isset( $product['id'] ) ? $product['id'] : '';
				$module->slug        = $this->bsf_get_module_slug( $template );
				$module->module      = isset( $template ) ? $template : '';

				if ( $this->use_beta_version( $module->id ) ) {
					$module->new_version = isset( $product['version_beta'] ) ? $product['version_beta'] : '';
				} else {
					$module->new_version = isset( $product['remote'] ) ? $product['remote'] : '';
				}

				$module->url         = isset( $product['purchase_url'] ) ? $product['purchase_url'] : '';

				if ( BSF_License_Manager::bsf_is_active_license( $product['id'] ) == 'registered' ) {
					$module->package = $this->bsf_get_package_uri( $product['id'] );
				} else {
					$module->package = '';
					$bundled         = self::bsf_is_product_bundled( $module->id );
					if ( ! empty( $bundled ) ) {
						$parent_id              = $bundled[0];
						$parent_name            = brainstrom_product_name( $parent_id );
						$module->upgrade_notice = "This module is came bundled with the " . $parent_name . ". For receiving updates, you need to register license of " . $parent_name . ".";
					} else {
						$module->upgrade_notice = 'Please activate your license to receive automatic updates.';
					}
				}

				$module->tested = isset( $product['tested'] ) ? $product['tested'] : '';

				$_transient_data->last_checked          = time();
				$_transient_data->response[ $template ] = $module;
			}

			return $_transient_data;
		}

		public function brainstorm_update_myskins_transient( $_transient_data ) {

			global $pagenow;

			if ( ! is_object( $_transient_data ) ) {
				$_transient_data = new stdClass;
			}

			if ( 'myskins.php' != $pagenow && 'update-core.php' !== $pagenow ) {
				return $_transient_data;
			}

			$update_data = $this->bsf_update_transient_data( 'myskins' );

			foreach ( $update_data as $key => $product ) {

				if ( isset( $product['template'] ) && $product['template'] != '' ) {
					$template = $product['template'];
				}

				$myskins                = array();
				$myskins['myskin']       = isset( $template ) ? $template : '';
				
				if ( $this->use_beta_version( $product['id'] ) ) {
					$myskins['new_version'] = isset( $product['version_beta'] ) ? $product['version_beta'] : '';
				} else {
					$myskins['new_version'] = isset( $product['remote'] ) ? $product['remote'] : '';	
				}
				
				$myskins['url']         = isset( $product['purchase_url'] ) ? $product['purchase_url'] : '';
				if ( BSF_License_Manager::bsf_is_active_license( $product['id'] ) == 'registered' ) {
					$myskins['package'] = $this->bsf_get_package_uri( $product['id'] );
				} else {
					$myskins['package']        = '';
					$myskins['upgrade_notice'] = 'Please activate your license to receive automatic updates.';
				}
				$_transient_data->last_checked          = time();
				$_transient_data->response[ $template ] = $myskins;
			}

			return $_transient_data;
		}

		/**
		 * Updates information on the "View version x.x details" page with custom data.
		 *
		 * @uses api_request()
		 *
		 * @param mixed $_data
		 * @param string $_action
		 * @param object $_args
		 *
		 * @return object $_data
		 */
		public function bsf_get_module_information( $_data, $_action = '', $_args = null ) {


			if ( $_action != 'module_information' ) {

				return $_data;

			}

			$brainstrom_products = apply_filters( 'bsf_get_module_information', get_option( 'brainstrom_products', array() ) );

			$modules      = isset( $brainstrom_products['modules'] ) ? $brainstrom_products['modules'] : array();
			$myskins       = isset( $brainstrom_products['myskins'] ) ? $brainstrom_products['myskins'] : array();
			$all_products = $modules + $myskins;

			foreach ( $all_products as $key => $product ) {

				$product_slug = isset( $product['slug'] ) ? $product['slug'] : '';

				if ( $product_slug == $_args->slug ) {

					$id = isset( $product['id'] ) ? $product['id'] : '';

					$info              = new stdClass();

					if ( $this->use_beta_version( $id ) ) {
						$info->new_version = isset( $product['version_beta'] ) ? $product['version_beta'] : '';
					} else {
						$info->new_version = isset( $product['remote'] ) ? $product['remote'] : '';	
					}
					
					$product_name      = isset( $product['name'] ) ? $product['name'] : '';
					$info->name        = apply_filters( "bsf_product_name_{$id}", $product_name );
					$info->slug        = $product_slug;
					$info->version     = isset( $product['version'] ) ? $product['version'] : '';
					$info->author      = 'Brainstorm Force';
					$info->url         = isset( $product['changelog_url'] ) ? $product['changelog_url'] : '';
					$info->homepage    = isset( $product['purchase_url'] ) ? $product['purchase_url'] : '';

					if ( BSF_License_Manager::bsf_is_active_license( $id ) == true ) {
						$package_url         = $this->bsf_get_package_uri( $id );
						$info->package       = $package_url;
						$info->download_link = $package_url;
					}

					$info->sections                = array();
					$info->sections['description'] = isset( $product['description'] ) ? $product['description'] : '';
					$info->sections['changelog']   = 'Thank you for using ' . $info->name . '. </br></br>To make your experience using ' . $info->name . ' better we release updates regularly, you can view the full changelog <a href="' . $info->url . '">here</a>';

					$_data = $info;
				}
			}

			return $_data;
		}

		// helpers

		public static function bsf_is_product_bundled( $bsf_product, $search_by = 'id' ) {
			$brainstrom_bundled_products = get_option( 'brainstrom_bundled_products', array() );
			$product_parent              = array();

			foreach ( $brainstrom_bundled_products as $parent => $products ) {

				foreach ( $products as $key => $product ) {

					if ( $search_by == 'init' ) {

						if ( $product->init == $bsf_product ) {
							$product_parent[] = $parent;
						}
					} elseif ( $search_by == 'id' ) {

						if ( $product->id == $bsf_product ) {
							$product_parent[] = $parent;
						}
					} elseif ( $search_by == 'name' ) {

						if ( strcasecmp( $product->name, $bsf_product ) == 0 ) {
							$product_parent[] = $parent;
						}
					}
				}
			}

			$product_parent = apply_filters( "bsf_is_product_bundled", array_unique( $product_parent ), $bsf_product, $search_by );			

			return $product_parent;
		}

		public function bsf_get_package_uri( $product_id ) {

			// use the cached url for 2 hours.
			if ( ! $this->time_since_last_versioncheck( 2 ) ) {
				$product       = get_brainstorm_product( $product_id );
				$status        = BSF_License_Manager::bsf_is_active_license( $product_id );

				if ( $this->use_beta_version( $product_id ) ) {
					$download_file = isset( $product['download_url_beta'] ) ? $product['download_url_beta'] : '';
				} else {
					$download_file = isset( $product['download_url'] ) ? $product['download_url'] : '';
				}

				if ( $download_file !== '' ) {

					if ( $status == false ) {
						return '';
					}

					$timezone = date_default_timezone_get();
					$hash     = 'file=' . $download_file . '&hashtime=' . strtotime( date( 'd-m-Y h:i:s a' ) ) . '&timezone=' . $timezone;

					$get_path      = 'http://downloads.jiiworks.com/';
					$download_path = rtrim( $get_path, '/' ) . '/download.php?' . $hash . '&base=ignore';

					return $download_path;
				}
			}

			$product       				 = get_brainstorm_product( $product_id );
			$status        				 = BSF_License_Manager::bsf_is_active_license( $product_id );
			$brainstrom_products         = get_option( 'brainstrom_products', array() );
			$brainstrom_bundled_products = get_option( 'brainstrom_bundled_products', array() );
			$modules      				 = isset( $brainstrom_products['modules'] ) ? $brainstrom_products['modules'] : array();
			$myskins       				 = isset( $brainstrom_products['myskins'] ) ? $brainstrom_products['myskins'] : array();
			$all_products 				 = $modules + $myskins;
			$path         				 = get_api_url() . '?referer=package-' . $product_id;
			$is_bundled   				 = self::bsf_is_product_bundled( $product_id );
			$purchase_key 				 = isset( $all_products[ $product_id ]['purchase_key'] ) ? $all_products[ $product_id ]['purchase_key'] : null;
			$bundled      				 = false;

			if ( ! empty( $is_bundled ) ) {
				$bundled = true;
			}

			$data = array(
				'action'       => 'bsf_product_update_request',
				'id'           => $product_id,
				'username'     => '', // username is being depracated in new Graupi
				'purchase_key' => $purchase_key,
				'site_url'     => get_site_url(),
				'bundled'      => $bundled
			);

			$request = mcms_remote_post(
				$path, array(
					'body'      => $data,
					'timeout'   => '30'
				)
			);

			if ( ! is_mcms_error( $request ) || mcms_remote_retrieve_response_code( $request ) === 200 ) {

				$result = json_decode( mcms_remote_retrieve_body( $request ) );

				if ( isset( $result->error ) && ! $result->error ) {
					
					if ( $this->use_beta_version( $product_id ) ) {
						$download_path = $result->update_data->download_url_beta;
					} else {
						$download_path = $result->update_data->download_url;
					}

					$timezone      = date_default_timezone_get();
					$hash          = 'file=' . $download_path . '&hashtime=' . strtotime( date( 'd-m-Y h:i:s a' ) ) . '&timezone=' . $timezone;

					$get_path      = 'http://downloads.jiiworks.com/';
					$download_path = rtrim( $get_path, '/' ) . '/download.php?' . $hash . '&base=ignore';

					return $download_path;
				}
			}
		}

		public function bsf_update_transient_data( $product_type ) {

			$this->_maybe_force_check_bsf_product_updates();

			$all_products    = array();
			$update_required = array();

			if ( $product_type == 'modules' ) {
				$all_products = brainstorm_get_all_products( false, true, false );
			}

			if ( $product_type == 'myskins' ) {
				$all_products = brainstorm_get_all_products( true, false, true );
			}

			foreach ( $all_products as $key => $product ) {

				$product_id = isset( $product['id'] ) ? $product['id'] : '';

				$constant = strtoupper( str_replace( '-', '_', $product_id ) );
				$constant = 'BSF_' . $constant . '_CHECK_UPDATES';

				if ( defined( $constant ) && ( constant( $constant ) === 'false' || constant( $constant ) === false ) ) {
					continue;
				}

				$remote       = isset( $product['remote'] ) ? $product['remote'] : '';
				$local        = isset( $product['version'] ) ? $product['version'] : '';
				$version_beta = isset( $product['version_beta'] ) ? $product['version_beta'] : $remote;

				if ( $this->use_beta_version( $product_id ) ) {
					$remote = $version_beta;
				}

				if ( version_compare( $remote, $local, '>' ) ) {
					array_push( $update_required, $product );
				}
			}
			
			return $update_required;
		}

		public function _maybe_force_check_bsf_product_updates () {

			if ( $this->time_since_last_versioncheck( 2 ) ) {
				global $ultimate_referer;
				$ultimate_referer = 'on-transient-delete-2-hours';
				bsf_check_product_update();
				update_option( 'bsf_local_transient', (string) current_time( 'timestamp' ) );
				set_transient( 'bsf_check_product_updates', true, 2 * 24 * 60 * 60 );
			}

		}

		public function time_since_last_versioncheck( $hours_completed ) {

			$seconds = $hours_completed * 3600;
			$status  = false;

			$bsf_local_transient = (int) get_option( 'bsf_local_transient', false );
			
			if ( $bsf_local_transient != false ) {

				// Find seconds passed since the last timestamp update (i.e. last request made)
				$elapsed_seconds = (int) current_time( 'timestamp' ) - $bsf_local_transient;

				// IF time is more than the required seconds allow a new HTTP request.
				if ( $elapsed_seconds > $seconds ) {
					$status = true;
				}

			} else {

				// If timestamp is not yet set - allow the HTTP request.
				$status = true;
			}

			return $status;
		}

		public function use_beta_version( $product_id ) {

			$product = get_brainstorm_product( $product_id );
			$stable  = isset( $product['remote'] ) ? $product['remote'] : '';
			$beta    = isset( $product['version_beta'] ) ? $product['version_beta'] : '';

			// If beta version is not set, return
			if ( $beta == '' ) {
				return false;
			}

			if ( version_compare( $stable, $beta, '<' ) &&
			     self::bsf_allow_beta_updates( $product_id )
			) {

				return true;
			}

			return false;
		}

		public function beta_version_normalized( $beta ) {
			$beta_explode = explode( '-', $beta );

			$version = $beta_explode[0] . '.' . str_replace( 'beta', '', $beta_explode[1] );

			return $version;
		}

		public static function bsf_allow_beta_updates( $product_id ) {
			return apply_filters( "bsf_allow_beta_updates_{$product_id}", false );
		}

		public function bsf_get_module_slug( $template ) {
			$slug = explode( '/', $template );

			if ( isset( $slug[0] ) ) {
				return $slug[0];
			}

			return '';
		}

		public function bsf_update_display_license_link() {

			$brainstorm_all_products = $this->brainstorm_all_products();

			foreach ( $brainstorm_all_products as $key => $product ) {

				if( isset( $product['id'] ) ) {
					$id = $product['id'];

					if ( BSF_License_Manager::bsf_is_active_license( $id ) == false ) {

						if ( isset( $product['template'] ) && $product['template'] != '' ) {
							$template = $product['template'];
						} elseif ( isset( $product['init'] ) && $product['init'] != '' ) {
							$template = $product['init'];
						}

						add_action( "in_module_update_message-$template", array(
							$this,
							'bsf_add_registration_message'
						), 9, 2 );
					}
				}
			}
		}

		public function brainstorm_all_products() {

			$brainstrom_products         = get_option( 'brainstrom_products', array() );
			$brainstrom_products_modules = isset( $brainstrom_products['modules'] ) ? $brainstrom_products['modules'] : array();
			$brainstrom_products_myskins  = isset( $brainstrom_products['myskins'] ) ? $brainstrom_products['myskins'] : array();
			$brainstrom_bundled_products = get_option( 'brainstrom_bundled_products', array() );

			$bundled = array();

			foreach ( $brainstrom_bundled_products as $parent => $children ) {

				foreach ( $children as $key => $product ) {
					$bundled[ $product->id ] = (array) $product;
				}

			}

			// array of all the products
			$all_products = $brainstrom_products_modules + $brainstrom_products_myskins + $bundled;

			return $all_products;
		}

		public function bsf_add_registration_message( $module_data, $response ) {

			$module_init = isset( $module_data['module'] ) ? $module_data['module'] : '';

			if ( '' !== $module_init ) {
				$product_id        = brainstrom_product_id_by_init( $module_init );
				$bundled           = self::bsf_is_product_bundled( $module_init, 'init' );
				$registration_page = bsf_registration_page_url( '', $product_id );
			} else {
				$module_name 		= isset( $module_data['name'] ) ? $module_data['name'] : '';
				$product_id  		= brainstrom_product_id_by_name( $module_name );
				$bundled           	= self::bsf_is_product_bundled( $module_name, 'name' );
				$registration_page 	= bsf_registration_page_url( '', $product_id );
			}

			if ( ! empty( $bundled ) ) {
				$parent_id   = $bundled[0];
				$parent_name = apply_filters( "bsf_product_name_{$parent_id}", brainstrom_product_name( $parent_id ) );
				printf( __( ' <br>This module is came bundled with the <i>%1$s</i>. For receiving updates, you need to register license of <i>%2$s</i> <a href="%3$s">here</a>.' ), $parent_name, $parent_name, $registration_page );
			} else {
				printf( __( ' <i>Click <a href="%1$s">here</a> to activate your license.</i>' ), $registration_page );
			}

		}

		public function bsf_change_no_package_message( $reply, $package, $current ) {

			// Read atts into separate veriables so that easy to reference below.
			$strings = $current->strings;

			if ( isset( $current->skin->module_info ) ) {
				$module_info = $current->skin->module_info;

				$module_name = $module_info['Name'];
				$product_id  = brainstrom_product_id_by_name( $module_name );
				$module_name = apply_filters( "bsf_product_name_{$product_id}", $module_name );
				$is_bundled  = self::bsf_is_product_bundled( $module_name, 'name' );

				if ( empty( $is_bundled ) ) {
					if ( strcasecmp( $module_info['Author'], "Brainstorm Force" ) !== 0 ) {

						// This is not our product, let's leave.
						return $reply;
					}
				} else {
					$is_bundled  = isset( $is_bundled[0] ) ? $is_bundled[0] : $module_name;
					$module_name = apply_filters( "bsf_product_name_{$is_bundled}", brainstrom_product_name( $is_bundled ) );
				}

				$strings['downloading_package'] = 'Downloading the package...';

				if ( $module_info['Author'] == 'Brainstorm Force' ) {
					$strings['no_package'] = sprintf(
						__( 'Click <a target="_blank" href="%1s">here</a> to activate license of <i>%2s</i> to receive automatic updates.' ),
						bsf_registration_page_url( '', $product_id ),
						$module_name
					);
				} elseif ( $is_bundled !== '' ) {
					$strings['no_package'] = sprintf(
						__( 'This module is came bundled with the <i>%1s</i>. For receiving updates, you need to register license of <i>%2s</i> <a target="_blank" href="%3s">here</a>.' ),
						$module_name,
						$module_name,
						bsf_registration_page_url( '', $product_id )
					);
				}

			} elseif ( isset( $current->skin->myskin_info ) ) {
				$myskin_info   = $current->skin->myskin_info;
				$myskin_author = $myskin_info->get( 'Author' );
				$myskin_name   = $myskin_info->get( 'Name' );
				$product_id   = brainstrom_product_id_by_name( $myskin_name );

				if ( $myskin_author == 'Brainstorm Force' ) {
					$strings['downloading_package'] = 'Downloading the package...';
					$strings['no_package']          = sprintf(
						__( 'Click <a target="_blank" href="%1s">here</a> to activate license of <i>%2s</i> to receive automatic updates.' ),
						bsf_registration_page_url( '', $product_id ),
						$myskin_name
					);
				}
			}

			// restore the strings back to MCMS_Upgrader
			$current->strings = $strings;

			// We are not changing teh return parameter.
			return $reply;
		}

	} // class BSF_Update_Manager

	new BSF_Update_Manager();
}

