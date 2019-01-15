<?php

/**
 *
 */
if ( ! class_exists( 'BSF_License_Manager' ) ) {

	class BSF_License_Manager {

		private static $_instance = null;

		public static function instance() {
			if ( ! isset( self::$_instance ) ) {
				self::$_instance = new self;
			}

			return self::$_instance;
		}

		public function __construct() {

			add_action( 'admin_init', array( $this, 'bsf_activate_license' ) );
			add_action( 'admin_init', array( $this, 'bsf_deactivate_license' ) );

			$this->includes();
		}

		public function includes() {
			require_once BSF_UPDATER_PATH . '/BSF_Envato_Activate.php';
		}

		public function get_support_url() {
			$support_url = get_api_site() . 'mcms-admin/admin-ajax.php';

			return $support_url;
		}

		public function bsf_deactivate_license() {

			if ( ! isset( $_POST['bsf_deactivate_license'] ) ) {
				return;
			}

			if ( ! isset( $_POST['bsf_license_manager']['license_key'] ) || $_POST['bsf_license_manager']['license_key'] == "" ) {
				return;
			}

			if ( ! isset( $_POST['bsf_license_manager']['product_id'] ) || $_POST['bsf_license_manager']['product_id'] == "" ) {
				return;
			}

			$product_id  = esc_attr( $_POST['bsf_license_manager']['product_id'] );
			$license_key = $this->bsf_get_product_info( $product_id, 'purchase_key' );

			// Check if the key is from EDD
			$is_edd = $this->is_edd( $license_key );

			// 
			$path = $this->get_support_url() . '?referer=deactivate-' . $product_id;

			// Using Brainstorm API v2
			$data = array(
				'action'       => 'bsf_deactivate_license',
				'purchase_key' => $license_key,
				'product_id'   => $product_id,
				'site_url'     => get_site_url(),
				'is_edd'       => $is_edd,
				'referer'      => 'customer'
			);

			$data     = apply_filters( 'bsf_deactivate_license_args', $data );
			$response = mcms_remote_post(
				$path, array(
					'body'      => $data,
					'timeout'   => '30'
				)
			);

			if ( ! is_mcms_error( $response ) || mcms_remote_retrieve_response_code( $response ) === 200 ) {
				$result = json_decode( mcms_remote_retrieve_body( $response ), true );

				if ( isset( $result['success'] ) && $result['success'] == true ) {
					// update license saus to the product
					$_POST['bsf_license_deactivation']['success'] = $result['success'];
					$_POST['bsf_license_deactivation']['message'] = $result['message'];
					unset( $result['success'] );
					unset( $result['message'] );

					$this->bsf_update_product_info( $product_id, $result );
				} else {
					$_POST['bsf_license_deactivation']['success'] = $result['success'];
					$_POST['bsf_license_deactivation']['message'] = $result['message'];
				}
			} else {
				$_POST['bsf_license_deactivation']['success'] = $result['success'];
				$_POST['bsf_license_deactivation']['message'] = $result['message'];
			}
		}

		public function bsf_activate_license() {

			if ( ! isset( $_POST['bsf_activate_license'] ) ) {
				return;
			}

			if ( ! isset( $_POST['bsf_license_manager']['license_key'] ) || $_POST['bsf_license_manager']['license_key'] == "" ) {
				return;
			}

			if ( ! isset( $_POST['bsf_license_manager']['product_id'] ) || $_POST['bsf_license_manager']['product_id'] == "" ) {
				return;
			}

			$license_key = esc_attr( $_POST['bsf_license_manager']['license_key'] );
			$product_id  = esc_attr( $_POST['bsf_license_manager']['product_id'] );
			$user_name   = isset( $_POST['bsf_license_manager']['user_name'] ) ? esc_attr( $_POST['bsf_license_manager']['user_name'] ) : '';
			$user_email  = isset( $_POST['bsf_license_manager']['user_email'] ) ? esc_attr( $_POST['bsf_license_manager']['user_email'] ) : '';
			$subscribe   = isset( $_POST['bsf_license_manager']['subscribe'] ) ? 'yes' : 'no';

			// update product license key
			$args = array(
				'purchase_key' => $license_key
			);

			$this->bsf_update_product_info( $product_id, $args );

			// Check if the key is from EDD
			$is_edd = $this->is_edd( $license_key );

			// Server side check if the license key is valid

			$path = $this->get_support_url() . '?referer=activate-' . $product_id;;

			// Using Brainstorm API v2
			$data = array(
				'action'       => 'bsf_activate_license',
				'purchase_key' => $license_key,
				'product_id'   => $product_id,
				'user_name'    => $user_name,
				'user_email'   => $user_email,
				'subscribe'    => $subscribe,
				'site_url'     => get_site_url(),
				'is_edd'       => $is_edd,
				'referer'      => 'customer'
			);

			$data     = apply_filters( 'bsf_activate_license_args', $data );
			$response = mcms_remote_post(
				$path, array(
					'body'      => $data,
					'timeout'   => '30'
				)
			);

			if ( ! is_mcms_error( $response ) || mcms_remote_retrieve_response_code( $response ) === 200 ) {
				$result = json_decode( mcms_remote_retrieve_body( $response ), true );
				if ( isset( $result['success'] ) && $result['success'] == true ) {
					// update license saus to the product
					$_POST['bsf_license_activation']['success'] = $result['success'];
					$_POST['bsf_license_activation']['message'] = $result['message'];
					unset( $result['success'] );

					$this->bsf_update_product_info( $product_id, $result );
				} else {
					$_POST['bsf_license_activation']['success'] = $result['success'];
					$_POST['bsf_license_activation']['message'] = $result['message'];
				}
			} else {
				$_POST['bsf_license_activation']['success'] = $result['success'];
				$_POST['bsf_license_activation']['message'] = $result['message'];
			}
		}

		public function is_edd( $license_key ) {

			// Purchase key length for EDD is 32 characters
			if ( strlen( $license_key ) === 32 ) {

				return true;
			}

			return false;
		}

		public function bsf_update_product_info( $product_id, $args ) {
			$brainstrom_products = get_option( 'brainstrom_products', array() );


			foreach ( $brainstrom_products as $type => $products ) {

				foreach ( $products as $id => $product ) {

					if ( $id == $product_id ) {
						foreach ( $args as $key => $value ) {
							$brainstrom_products[ $type ][ $id ][ $key ] = $value;
						}
					}
				}
			}

			update_option( 'brainstrom_products', $brainstrom_products );
		}

		public static function bsf_is_active_license( $product_id ) {

			$brainstrom_products = get_option( 'brainstrom_products', array() );
			$brainstorm_modules  = isset( $brainstrom_products['modules'] ) ? $brainstrom_products['modules'] : array();
			$brainstorm_myskins   = isset( $brainstrom_products['myskins'] ) ? $brainstrom_products['myskins'] : array();

			$all_products = $brainstorm_modules + $brainstorm_myskins;

			// If a product is marked as free, it is considered as active.
			$is_free 	= self::is_product_free( $product_id );
			if ( 'true' == $is_free ) {
				return true;
			}

			$is_bundled = BSF_Update_Manager::bsf_is_product_bundled( $product_id );

			// The product is not bundled
			if ( isset( $all_products[ $product_id ] ) ) {

				if ( isset( $all_products[ $product_id ]['status'] ) && $all_products[ $product_id ]['status'] == 'registered' ) {
					return true;
				}

			}
			
			if ( ! empty( $is_bundled ) ) {
				
				// The product is bundled
				foreach ( $is_bundled as $key => $value ) {

					$product_id = $value;

					if ( isset( $all_products[ $product_id ] ) ) {

						if ( isset( $all_products[ $product_id ]['status'] ) && $all_products[ $product_id ]['status'] == 'registered' ) {
							return true;
						}
					}
				}
			}

			// Return false by default
			return false;
		}

		public static function is_product_free( $product_id ) {
			$license_manager 	= BSF_License_Manager::instance();
			$is_free 			= $license_manager->bsf_get_product_info( $product_id, 'is_product_free' );

			return $is_free;
		}

		public function bsf_get_product_info( $product_id, $key ) {

			$brainstrom_products = get_option( 'brainstrom_products', array() );
			$brainstorm_modules  = isset( $brainstrom_products['modules'] ) ? $brainstrom_products['modules'] : array();
			$brainstorm_myskins   = isset( $brainstrom_products['myskins'] ) ? $brainstrom_products['myskins'] : array();

			$all_products = $brainstorm_modules + $brainstorm_myskins;

			if ( isset( $all_products[ $product_id ][ $key ] ) && $all_products[ $product_id ][ $key ] !== '' ) {
				return $all_products[ $product_id ][ $key ];
			}
		}

		public function license_activation_form( $args ) {
			$html = '';

			$product_id             = ( isset( $args['product_id'] ) && ! is_null( $args['product_id'] ) ) ? $args['product_id'] : '';

			// bail out if product id is missing.
			if ( $product_id == '' ) {
				_e( 'Product id is missing.', 'bsf' );

				return;
			}

			$form_action            = ( isset( $args['form_action'] ) && ! is_null( $args['form_action'] ) ) ? $args['form_action'] : '';
			$form_class             = ( isset( $args['form_class'] ) && ! is_null( $args['form_class'] ) ) ? $args['form_class'] : "bsf-license-form-{$product_id}";
			$submit_button_class    = ( isset( $args['submit_button_class'] ) && ! is_null( $args['submit_button_class'] ) ) ? $args['submit_button_class'] : '';
			$license_form_heading_class    = ( isset( $args['bsf_license_form_heading_class'] ) && ! is_null( $args['bsf_license_form_heading_class'] ) ) ? $args['bsf_license_form_heading_class'] : '';
			$license_active_class    = ( isset( $args['bsf_license_active_class'] ) && ! is_null( $args['bsf_license_active_class'] ) ) ? $args['bsf_license_active_class'] : '';
			$license_not_activate_message    = ( isset( $args['bsf_license_not_activate_message'] ) && ! is_null( $args['bsf_license_not_activate_message'] ) ) ? $args['bsf_license_not_activate_message'] : '';

			$size                   = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
			$button_text_activate   = ( isset( $args['button_text_activate'] ) && ! is_null( $args['button_text_activate'] ) ) ? $args['button_text_activate'] : 'Activate License';
			$button_text_deactivate = ( isset( $args['button_text_deactivate'] ) && ! is_null( $args['button_text_deactivate'] ) ) ? $args['button_text_deactivate'] : 'Deactivate License';
			$placeholder            = ( isset( $args['placeholder'] ) && ! is_null( $args['placeholder'] ) ) ? $args['placeholder'] : 'Enter your license key..';
			$placeholder_name       = ( isset( $args['placeholder_name'] ) && ! is_null( $args['placeholder_name'] ) ) ? $args['placeholder_name'] : 'Your Name..';
			$placeholder_email      = ( isset( $args['placeholder_email'] ) && ! is_null( $args['placeholder_email'] ) ) ? $args['placeholder_email'] : 'Your Email..';
			$bsf_license_allow_email = ( isset( $args['bsf_license_allow_email'] ) && ! is_null( $args['bsf_license_allow_email'] ) ) ? $args['bsf_license_allow_email'] : true;
			$license_form_title 	= ( isset( $args['license_form_title'] ) && ! is_null( $args['license_form_title'] ) ) ? $args['license_form_title'] : 'Updates & Support Registration - ';

			// Forcefully disable the subscribe options for uabb.
			// This should be disabled from uabb and removed from graupi.
			if ( 'uabb' == $product_id ) {
				$bsf_license_allow_email = false;
			}

			$purchase_url = $this->bsf_get_product_info( $product_id, 'purchase_url' );

			// License activation messages
			$current_status = $current_message = '';

			$is_active   = self::bsf_is_active_license( $product_id );
			$license_key = $this->bsf_get_product_info( $product_id, 'purchase_key' );

			if ( isset( $_POST['bsf_license_activation']['success'] ) ) {
				$current_status = esc_attr( $_POST['bsf_license_activation']['success'] );
				if ( true == $current_status ) {
					$current_status = "bsf-current-license-success-" . $product_id;
					$is_active 		= true;
				} else {
					$current_status = "bsf-current-license-error-" . $product_id;
					$is_active 		= false;
				}
			}

			if ( isset( $_POST['bsf_license_activation']['message'] ) ) {
				$current_message = mcms_kses_post( $_POST['bsf_license_activation']['message'] );
			}

			$license_status       = 'Active!';
			$license_status_class = "bsf-license-active-" . $product_id;

			$html .= '<div class="bsf-license-key-registration">';

			// License not active message
			if ( $is_active == false ) {
				$license_status       = 'Not Active!';
				$license_status_class = "bsf-license-not-active-" . $product_id;
				$not_activate = '';

				$html .= apply_filters( "bsf_license_not_activate_message_{$product_id}", $not_activate, $license_status_class, $license_not_activate_message );
			} else {
				$form_class .= " form-submited-{$product_id}";
			}

			do_action( "bsf_before_license_activation_form_{$product_id}" );

			$html .= '<form method="post" class="' . $form_class . '" action="' . $form_action . '">';

			$form_heading = '<h3 class="' . $license_status_class . ' ' . $license_form_heading_class . '">' . $license_form_title . '<span>' . $license_status . '</span></h3>';

			$html .= apply_filters( "bsf_license_form_heading_{$product_id}", $form_heading, $license_status_class, $license_status );

			if ( $current_status !== '' && $current_message !== '' ) {
				$current_message = '<span class="' . $current_status . '">' . $current_message . '</span>';
				$html .= apply_filters( "bsf_license_current_message_{$product_id}", $current_message );
			}

			if ( $is_active == true ) {

				$licnse_active_message = __( 'Your license is active.', 'bsf' );
				$licnse_active_message = apply_filters( 'bsf_license_active_message', $licnse_active_message );

				$html .= '<span class="license-form-field">';
				$html .= '<input type="text" readonly class="' . $license_active_class . ' ' . $size . '-text" id="bsf_license_manager[license_key]" name="bsf_license_manager[license_key]" value="' . esc_attr( $licnse_active_message ) . '"/>';
				$html .= '</span>';
				$html .= '<input type="hidden" class="' . $size . '-text" id="bsf_license_manager[product_id]" name="bsf_license_manager[product_id]" value="' . esc_attr( stripslashes( $product_id ) ) . '"/>';

				do_action( "bsf_before_license_activation_submit_button_{$product_id}" );

				$html .= '<input type="submit" class="button ' . $submit_button_class . '" name="bsf_deactivate_license" value="' . esc_attr__( $button_text_deactivate, 'bsf' ) . '"/>';
			} else {

				if ( $bsf_license_allow_email == true ) {

					$html .= '<span class="license-form-field">';
					$html .= '<h4>Your Name</h4>';
					$html .= '<input type="text" class="' . $size . '-text" id="bsf_license_manager[user_name]" name="bsf_license_manager[user_name]" value="" required/>';
					$html .= '</span>';

					$html .= '<span class="license-form-field">';
					$html .= '<h4>Your Email Address</h4>';
					$html .= '<input type="email" class="' . $size . '-text" id="bsf_license_manager[user_email]" name="bsf_license_manager[user_email]" value="" required/>';
					$html .= '</span>';

					$html .= '<span class="license-form-field">';
					$html .= '<h4>Your License Key</h4>';
					$html .= '<input type="text" class="' . $size . '-text" id="bsf_license_manager[license_key]" name="bsf_license_manager[license_key]" value="" autocomplete="off" required/>';
					$html .= '</span>';

					$html .= '<span class="license-form-field">';
					$html .= '<input type="checkbox" name="bsf_license_manager[subscribe]" checked="checked">It\'s OK to email me important news on occasions</label>';
					$html .= '</span>';
					//$html .= '<p class="email-note">We respect your privacy & of course you can unsubscribe at any time.</p>';

				} else {
					$html .= '<span class="license-form-field">';
					$html .= '<input type="text" placeholder="' . esc_attr( $placeholder ) . '" class="' . $size . '-text" id="bsf_license_manager[license_key]" name="bsf_license_manager[license_key]" value="" autocomplete="off"/>';
					$html .= '</span>';
				}

				$html .= '<input type="hidden" class="' . $size . '-text" id="bsf_license_manager[product_id]" name="bsf_license_manager[product_id]" value="' . esc_attr( stripslashes( $product_id ) ) . '"/>';

				do_action( "bsf_before_license_activation_submit_button_{$product_id}" );

				$html .= '<input type="submit" class="button ' . $submit_button_class . '" name="bsf_activate_license" value="' . esc_attr__( $button_text_activate, 'bsf' ) . '"/>';
				
				$get_license_message = apply_filters( "bsf_get_license_message_{$product_id}", "<p>If you don't have a license, you can <a target='_blank' href='$purchase_url'>get it here »</a></p>", $purchase_url );

				$html .= $get_license_message;
			}

			$html .= '</form>';

			do_action( "bsf_after_license_activation_form_{$product_id}" );

			$html .= '</div> <!-- envato-license-registration -->';

			if ( isset( $_GET['debug'] ) ) {
				$html .= get_bsf_systeminfo();
			}

			// Output the license activation/deactivation form
			return apply_filters( "bsf_core_license_activation_form_{$product_id}", $html, $args );
		}


	} // Class BSF_License_Manager

	new BSF_License_Manager();
}


function bsf_license_activation_form( $args ) {
	$license_manager = BSF_License_Manager::instance();

	return $license_manager->license_activation_form( $args );
}
