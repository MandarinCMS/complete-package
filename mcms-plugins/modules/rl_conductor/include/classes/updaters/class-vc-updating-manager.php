<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

/**
 * Manage update messages and Modules info for VC in default Wordpress modules list.
 */
class Vc_Updating_Manager {
	/**
	 * The module current version
	 *
	 * @var string
	 */
	public $current_version;

	/**
	 * The module remote update path
	 *
	 * @var string
	 */
	public $update_path;

	/**
	 * Module Slug (module_directory/module_file.php)
	 *
	 * @var string
	 */
	public $module_slug;

	/**
	 * Module name (module_file)
	 *
	 * @var string
	 */
	public $slug;
	/**
	 * Link to download VC.
	 * @var string
	 */
	protected $url = 'http://bit.ly/vcomposer';

	/**
	 * Initialize a new instance of the MandarinCMS Auto-Update class
	 *
	 * @param string $current_version
	 * @param string $update_path
	 * @param string $module_slug
	 */
	function __construct( $current_version, $update_path, $module_slug ) {
		// Set the class public variables
		$this->current_version = $current_version;
		$this->update_path = $update_path;
		$this->module_slug = $module_slug;
		$t = explode( '/', $module_slug );
		$this->slug = str_replace( '.php', '', $t[1] );

		// define the alternative API for updating checking
		add_filter( 'pre_set_site_transient_update_modules', array(
			$this,
			'check_update',
		) );

		// Define the alternative response for information checking
		add_filter( 'modules_api', array(
			$this,
			'check_info',
		), 10, 3 );

		add_action( 'in_module_update_message-' . vc_module_name(), array(
			$this,
			'addUpgradeMessageLink',
		) );
	}

	/**
	 * Add our self-hosted autoupdate module to the filter transient
	 *
	 * @param $transient
	 *
	 * @return object $ transient
	 */
	public function check_update( $transient ) {
		// Extra check for 3rd modules
		if ( isset( $transient->response[ $this->module_slug ] ) ) {
			return $transient;
		}
		// Get the remote version
		$remote_version = $this->getRemote_version();

		// If a newer version is available, add the update
		if ( version_compare( $this->current_version, $remote_version, '<' ) ) {
			$obj = new stdClass();
			$obj->slug = $this->slug;
			$obj->new_version = $remote_version;
			$obj->url = '';
			$obj->package = vc_license()->isActivated();
			$obj->name = vc_updater()->title;
			$transient->response[ $this->module_slug ] = $obj;
		}

		return $transient;
	}

	/**
	 * Add our self-hosted description to the filter
	 *
	 * @param bool $false
	 * @param array $action
	 * @param object $arg
	 *
	 * @return bool|object
	 */
	public function check_info( $false, $action, $arg ) {
		if ( isset( $arg->slug ) && $arg->slug === $this->slug ) {
			$information = $this->getRemote_information();
			$array_pattern = array(
				'/^([\*\s])*(\d\d\.\d\d\.\d\d\d\d[^\n]*)/m',
				'/^\n+|^[\t\s]*\n+/m',
				'/\n/',
			);
			$array_replace = array(
				'<h4>$2</h4>',
				'</div><div>',
				'</div><div>',
			);
			$information->name = vc_updater()->title;
			$information->sections = (array) $information->sections;
			$information->sections['changelog'] = '<div>' . preg_replace( $array_pattern, $array_replace, $information->sections['changelog'] ) . '</div>';

			return $information;
		}

		return $false;
	}

	/**
	 * Return the remote version
	 *
	 * @return string $remote_version
	 */
	public function getRemote_version() {
		$request = mcms_remote_get( $this->update_path );
		if ( ! is_mcms_error( $request ) || mcms_remote_retrieve_response_code( $request ) === 200 ) {
			return $request['body'];
		}

		return false;
	}

	/**
	 * Get information about the remote version
	 *
	 * @return bool|object
	 */
	public function getRemote_information() {
		$request = mcms_remote_get( $this->update_path.'information.json' );
		if ( ! is_mcms_error( $request ) || mcms_remote_retrieve_response_code( $request ) === 200 ) {
			return json_decode( $request['body'] );
		}

		return false;
	}

	/**
	 * Shows message on Wp modules page with a link for updating from envato.
	 */
	public function addUpgradeMessageLink() {
		$is_activated = vc_license()->isActivated();
		if ( ! $is_activated ) {
			$url = esc_url( vc_updater()->getUpdaterUrl() );
			$redirect = sprintf( '<a href="%s" target="_blank">%s</a>', $url, __( 'settings', 'rl_conductor' ) );

			echo sprintf( ' ' . __( 'To receive automatic updates license activation is required. Please visit %s to activate your RazorLeaf Conductor.', 'rl_conductor' ), $redirect ) . sprintf( ' <a href="http://mandarincms.com/faq-update-in-myskin" target="_blank">%s</a>', __( 'Got RazorLeaf Conductor in myskin?', 'rl_conductor' ) );
		}
	}
}
