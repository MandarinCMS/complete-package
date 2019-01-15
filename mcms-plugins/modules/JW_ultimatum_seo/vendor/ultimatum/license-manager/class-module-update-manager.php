<?php

if( class_exists( 'Ultimatum_Update_Manager' ) && ! class_exists( "Ultimatum_Module_Update_Manager", false ) ) {

	class Ultimatum_Module_Update_Manager extends Ultimatum_Update_Manager {

		/**
		 * Constructor
		 *
		 * @param Ultimatum_Product $product     The Product.
		 * @param string        $license_key The License entered.
		 */
		public function __construct( Ultimatum_Product $product, $license_key ) {
			parent::__construct( $product, $license_key );

			// setup hooks
			$this->setup_hooks();
		}

		/**
		* Setup hooks
		*/
		private function setup_hooks() {

			// check for updates
			add_filter( 'pre_set_site_transient_update_modules', array( $this, 'set_updates_available_data' ) );
			
			// get correct module information (when viewing details)
			add_filter( 'modules_api', array( $this, 'modules_api_filter' ), 10, 3 );
		}

		/**
		* Check for updates and if so, add to "updates available" data
		*
		* @param object $data
		* @return object $data
		*/
		public function set_updates_available_data( $data ) {

			if ( empty( $data ) ) {
				return $data;
			}

			// send of API request to check for updates
			$remote_data = $this->get_remote_data();

			// did we get a response?
			if( $remote_data === false ) {
				return $data;
			}

			// compare local version with remote version
			if ( version_compare( $this->product->get_version(), $remote_data->new_version, '<' ) ) {

				// remote version is newer, add to data
				$data->response[ $this->product->get_file() ] = $remote_data;

			}

			return $data;
		}

		/**
		 * Gets new module version details (view version x.x.x details)
		 *
		 * @uses api_request()
		 *
		 * @param object $data
		 * @param string $action
		 * @param object $args (optional)
		 *
		 * @return object $data
		 */
		public function modules_api_filter( $data, $action = '', $args = null ) {

			// only do something if we're checking for our module
			if ( $action !== 'module_information' || ! isset( $args->slug ) || $args->slug !== $this->product->get_slug() ) {
				return $data;
			}

			$api_response = $this->get_remote_data();
			
			// did we get a response?
			if ( $api_response === false ) {
				return $data;	
			}

			// return api response
			return $api_response;
		}
	}

}
