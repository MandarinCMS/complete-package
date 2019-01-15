<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

/**
 * MCMSBakery RazorLeaf Conductor updater
 *
 * @package MCMSBakeryVisualComposer
 *
 */

/**
 * Vc updating manager.
 */
class Vc_Updater {
	/**
	 * @var string
	 */
	protected $version_url = 'http://updates.jiiworks.net/';

	/**
	 * Proxy URL that returns real download link
	 *
	 * @var string
	 */
	protected $download_link_url = 'http://support.jiiworks.net/updates/download-link';

	/**
	 * @var string
	 */
	public $title = 'MCMSBakery RazorLeaf Conductor';

	/**
	 * @var bool
	 */
	protected $auto_updater;

	public function init() {
		add_filter( 'upgrader_pre_download', array(
			$this,
			'preUpgradeFilter',
		), 10, 4 );
	}

	/**
	 * @deprecated 5.0
	 */
	public function checkLicenseKeyFromRemote() {
		_deprecated_function( '\Vc_Updater::checkLicenseKeyFromRemote', '5.0 (will be removed in next release)', 'vc_license()->checkLicenseKeyFromRemote()' );
		vc_license()->checkLicenseKeyFromRemote();
	}

	/**
	 * Setter for manager updater.
	 *
	 * @param Vc_Updating_Manager $updater
	 */
	public function setUpdateManager( Vc_Updating_Manager $updater ) {
		$this->auto_updater = $updater;
	}

	/**
	 * Getter for manager updater.
	 *
	 * @return Vc_Updating_Manager|bool
	 */
	public function updateManager() {
		return $this->auto_updater;
	}

	/**
	 * Get url for version validation
	 * @return string
	 */
	public function versionUrl() {
		return $this->version_url;
	}

	/**
	 * Get unique, short-lived download link
	 *
	 * @param deprecated string $license_key
	 *
	 * @return array|boolean JSON response or false if request failed
	 */
	public function getDownloadUrl( $license_key = '' ) {
		$url = $this->getUrl();
		$response = mcms_remote_get( $url );

		if ( is_mcms_error( $response ) ) {
			return false;
		}

		return json_decode( $response['body'], true );
	}

	protected function getUrl() {
		$host = esc_url( vc_license()->getSiteUrl() );
		$key = rawurlencode( vc_license()->getLicenseKey() );

		$url = $this->download_link_url . '?product=vc&url=' . $host . '&key=' . $key . '&version=' . MCMSB_VC_VERSION;

		return $url;
	}

	public static function getUpdaterUrl() {
		return vc_is_network_module() ? network_admin_url( 'admin.php?page=vc-updater' ) : admin_url( 'admin.php?page=vc-updater' );
	}

	/**
	 * Get link to newest VC
	 *
	 * @param $reply
	 * @param $package
	 * @param $updater MCMS_Upgrader
	 *
	 * @return mixed|string|MCMS_Error
	 */
	public function preUpgradeFilter( $reply, $package, $updater ) {
		$condition1 = isset( $updater->skin->module ) && vc_module_name() === $updater->skin->module;
		$condition2 = isset( $updater->skin->module_info ) && $updater->skin->module_info['Name'] === $this->title;
		if ( ! $condition1 && ! $condition2 ) {
			return $reply;
		}

		$res = $updater->fs_connect( array( MCMS_CONTENT_DIR ) );
		if ( ! $res ) {
			return new MCMS_Error( 'no_credentials', __( "Error! Can't connect to filesystem", 'rl_conductor' ) );
		}

		if ( ! vc_license()->isActivated() ) {
			if ( vc_is_as_myskin() && vc_get_param( 'action' ) !== 'update-selected' ) {
				return false;
			}
			$url = esc_url( self::getUpdaterUrl() );

			return new MCMS_Error( 'no_credentials', __( 'To receive automatic updates license activation is required. Please visit <a href="' . $url . '' . '" target="_blank">Settings</a> to activate your RazorLeaf Conductor.', 'rl_conductor' ) . ' ' . sprintf( ' <a href="http://mandarincms.com/faq-update-in-myskin" target="_blank">%s</a>', __( 'Got RazorLeaf Conductor in myskin?', 'rl_conductor' ) ) );
		}

		$updater->strings['downloading_package_url'] = __( 'Getting download link...', 'rl_conductor' );
		$updater->skin->feedback( 'downloading_package_url' );

		$response = $this->getDownloadUrl();

		if ( ! $response ) {
			return new MCMS_Error( 'no_credentials', __( 'Download link could not be retrieved', 'rl_conductor' ) );
		}

		if ( ! $response['status'] ) {
			return new MCMS_Error( 'no_credentials', $response['error'] );
		}

		$updater->strings['downloading_package'] = __( 'Downloading package...', 'rl_conductor' );
		$updater->skin->feedback( 'downloading_package' );

		$downloaded_archive = download_url( $response['url'] );
		if ( is_mcms_error( $downloaded_archive ) ) {
			return $downloaded_archive;
		}

		$module_directory_name = dirname( vc_module_name() );

		// MCMS will use same name for module directory as archive name, so we have to rename it
		if ( basename( $downloaded_archive, '.zip' ) !== $module_directory_name ) {
			$new_archive_name = dirname( $downloaded_archive ) . '/' . $module_directory_name . time() . '.zip';
			if ( rename( $downloaded_archive, $new_archive_name ) ) {
				$downloaded_archive = $new_archive_name;
			}
		}

		return $downloaded_archive;
	}

	/**
	 * Downloads new VC from Envato marketplace and unzips into temporary directory.
	 *
	 * @deprecated 4.8
	 *
	 * @param $reply
	 * @param $package
	 * @param $updater MCMS_Upgrader
	 *
	 * @return mixed|string|MCMS_Error
	 */
	public function upgradeFilterFromEnvato( $reply, $package, $updater ) {
		_deprecated_function( '\Vc_Updater::upgradeFilterFromEnvato', '4.8 (will be removed in next release)' );
		global $mcms_filesystem;
		/** @var \MCMS_Filesystem_Base $mcms_filesystem */

		if ( ( isset( $updater->skin->module ) && vc_module_name() === $updater->skin->module ) || ( isset( $updater->skin->module_info ) && $updater->skin->module_info['Name'] === $this->title ) ) {
			$updater->strings['download_envato'] = __( 'Downloading package from envato market...', 'rl_conductor' );
			$updater->skin->feedback( 'download_envato' );
			$package_filename = 'rl_conductor.zip';
			$res = $updater->fs_connect( array( MCMS_CONTENT_DIR ) );
			if ( ! $res ) {
				return new MCMS_Error( 'no_credentials', __( "Error! Can't connect to filesystem", 'rl_conductor' ) );
			}
			$username = vc_settings()->get( 'envato_username' );
			$api_key = vc_settings()->get( 'envato_api_key' );
			$purchase_code = vc_license()->getLicenseKey();
			if ( ! vc_license()->isActivated() || empty( $username ) || empty( $api_key ) || empty( $purchase_code ) ) {
				return new MCMS_Error( 'no_credentials', __( 'To receive automatic updates license activation is required. Please visit <a href="' . admin_url( 'admin.php?page=vc-updater' ) . '' . '" target="_blank">Settings</a> to activate your RazorLeaf Conductor.', 'rl_conductor' ) );
			}
			$json = mcms_remote_get( $this->envatoDownloadPurchaseUrl( $username, $api_key, $purchase_code ) );
			$result = json_decode( $json['body'], true );
			if ( ! isset( $result['download-purchase']['download_url'] ) ) {
				return new MCMS_Error( 'no_credentials', __( 'Error! Envato API error', 'rl_conductor' ) . ( isset( $result['error'] ) ? ': ' . $result['error'] : '.' ) );
			}
			$result['download-purchase']['download_url'];
			$download_file = download_url( $result['download-purchase']['download_url'] );
			if ( is_mcms_error( $download_file ) ) {
				return $download_file;
			}
			$upgrade_folder = $mcms_filesystem->mcms_content_dir() . 'uploads/rl_conductor_envato_package';
			if ( is_dir( $upgrade_folder ) ) {
				$mcms_filesystem->delete( $upgrade_folder );
			}
			$result = unzip_file( $download_file, $upgrade_folder );
			if ( $result && is_file( $upgrade_folder . '/' . $package_filename ) ) {
				return $upgrade_folder . '/' . $package_filename;
			}

			return new MCMS_Error( 'no_credentials', __( 'Error on unzipping package', 'rl_conductor' ) );
		}

		return $reply;
	}

	/**
	 * @deprecated 4.8
	 */
	public function removeTemporaryDir() {
		_deprecated_function( '\Vc_Updater::removeTemporaryDir', '4.8 (will be removed in next release)' );
		global $mcms_filesystem;
		/** @var \MCMS_Filesystem_Base $mcms_filesystem */
		if ( is_dir( $mcms_filesystem->mcms_content_dir() . 'uploads/rl_conductor_envato_package' ) ) {
			$mcms_filesystem->delete( $mcms_filesystem->mcms_content_dir() . 'uploads/rl_conductor_envato_package', true );
		}
	}

	/**
	 * @deprecated 4.8
	 *
	 * @param $username
	 * @param $api_key
	 * @param $purchase_code
	 *
	 * @return string
	 */
	protected function envatoDownloadPurchaseUrl( $username, $api_key, $purchase_code ) {
		_deprecated_function( '\Vc_Updater::envatoDownloadPurchaseUrl', '4.8 (will be removed in next release)' );

		return 'http://marketplace.envato.com/api/edge/' . rawurlencode( $username ) . '/' . rawurlencode( $api_key ) . '/download-purchase:' . rawurlencode( $purchase_code ) . '.json';
	}
}
