<?php

class Ultimatum_Api_Google {

	/**
	 * This class will be loaded when someone calls the API library with the Google analytics module
	 */
	public function __construct() {
		spl_autoload_register( array( $this, 'autoload_api_google_files' ) );
	}

	/**
	 * Autoload the API Google class
	 *
	 * @param string $class_name - The class that should be loaded
	 */
	private function autoload_api_google_files( $class_name ) {
		$path        = dirname( __FILE__ );
		$class_name  = strtolower( $class_name );
		$oauth_files = array(
			// Main requires
			'ultimatum_google_client'          => 'google/Google_Client',
			'ultimatum_api_google_client'      => 'class-api-google-client',

			// Requires in classes
			'ultimatum_google_auth'            => 'google/auth/Google_Auth',
			'ultimatum_google_assertion'       => 'google/auth/Google_AssertionCredentials',
			'ultimatum_google_signer'          => 'google/auth/Google_Signer',
			'ultimatum_google_p12signer'       => 'google/auth/Google_P12Signer',
			'ultimatum_google_authnone'        => 'google/auth/Google_AuthNone',
			'ultimatum_google_oauth2'          => 'google/auth/Google_OAuth2',
			'ultimatum_google_verifier'        => 'google/auth/Google_Verifier',
			'ultimatum_google_loginticket'     => 'google/auth/Google_LoginTicket',
			'ultimatum_google_pemverifier'     => 'google/auth/Google_PemVerifier',
			'ultimatum_google_model'           => 'google/service/Google_Model',
			'ultimatum_google_service'         => 'google/service/Google_Service',
			'ultimatum_google_serviceresource' => 'google/service/Google_ServiceResource',
			'ultimatum_google_utils'           => 'google/service/Google_Utils',
			'ultimatum_google_batchrequest'    => 'google/service/Google_BatchRequest',
			'ultimatum_google_mediafileupload' => 'google/service/Google_MediaFileUpload',
			'ultimatum_google_uritemplate'     => 'google/external/URITemplateParser',
			'ultimatum_google_cache'           => 'google/cache/Google_Cache',

			// Requests
			'ultimatum_google_cacheparser'     => 'google/io/Google_CacheParser',
			'ultimatum_google_io'              => 'google/io/Google_IO',
			'ultimatum_google_httprequest'     => 'google/io/Google_HttpRequest',
			'ultimatum_google_rest'            => 'google/io/Google_REST',

			// Wordpress
			'ultimatum_google_mcmsio'            => 'google/io/Google_MCMSIO',
			'ultimatum_google_mcmscache'         => 'google/cache/Google_MCMSCache',
		);

		if ( ! empty( $oauth_files[$class_name] ) ) {
			if ( file_exists( $path . '/' . $oauth_files[$class_name] . '.php' ) ) {
				require_once( $path . '/' . $oauth_files[$class_name] . '.php' );
			}

		}

	}

}