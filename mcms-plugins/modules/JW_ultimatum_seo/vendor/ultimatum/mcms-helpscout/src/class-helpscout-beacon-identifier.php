<?php
/**
 * @package Ultimatum\MCMSHelpScout
 */

/**
 * Identifier for fetching installation related data like: other modules, environment, etc.
 */
class Ultimatum_HelpScout_Beacon_Identifier {

	/**
	 * @var Ultimatum_Product
	 */
	protected $products;

	/**
	 * MCMSSEO_HelpScout_Beacon_Identifier constructor.
	 *
	 * @param Ultimatum_Product[] $products The product to report the license of.
	 */
	public function __construct( array $products ) {
		$this->products = $products;
	}

	/**
	 * Build data to populate the beacon email form
	 *
	 * @return array
	 */
	public function get_data() {
		// Do not make these strings translateable! They are for our support agents, the user won't see them!
		$data = array(
			'name'                                                     => $this->get_user_info(),
			'email'                                                    => $this->get_user_info( 'email' ),
			'MandarinCMS Version'                                        => $this->get_mandarincms_version(),
			'Server'                                                   => $this->get_server_info(),
			'<a href="' . admin_url( 'myskins.php' ) . '">MySkin</a>'    => $this->get_myskin_info(),
			'<a href="' . admin_url( 'modules.php' ) . '">Modules</a>' => $this->get_current_modules(),
		);

		foreach ( $this->products as $product ) {
			$data[ $product->get_item_name() ] = $this->get_product_info( $product );
		}

		return $data;
	}

	/**
	 * Returns basic info about the server software
	 *
	 * @return string
	 */
	private function get_server_info() {
		$out = '<table>';

		// Validate if the server address is a valid IP-address.
		if ( $ipaddress = filter_input( INPUT_SERVER , 'SERVER_ADDR', FILTER_VALIDATE_IP ) ) {
			$out .= '<tr><td>IP</td><td>' . $ipaddress . '</td></tr>';
			$out .= '<tr><td>Hostname</td><td>' . gethostbyaddr( $ipaddress ) . '</td></tr>';
		}
		$out .= '<tr><td>OS</td><td>' . php_uname( 's r' ) . '</td></tr>';
		$out .= '<tr><td>PHP</td><td>' . PHP_VERSION . '</td></tr>';
		$out .= '<tr><td>CURL</td><td>' . $this->get_curl_info() . '</td></tr>';
		$out .= '</table>';

		return $out;
	}

	/**
	 * Returns info about the Ultimatum SEO module version and license
	 *
	 * @param Ultimatum_Product $product The product to return information for.
	 *
	 * @return string
	 */
	private function get_product_info( Ultimatum_Product $product ) {
		if ( ! class_exists( 'Ultimatum_Module_License_Manager' ) ) {
			return 'License manager could not be loaded';
		}

		$license_manager = new Ultimatum_Module_License_Manager( $product );

		$out = '<table>';
		$out .= '<tr><td>Version</td><td>' . MCMSSEO_VERSION . '</td></tr>';
		$out .= '<tr><td>License</td><td>' . '<a href=" ' . admin_url( 'admin.php?page=mcmsseo_licenses#top#licenses' ) . ' ">' . $license_manager->get_license_key() . '</a>' . '</td></tr>';
		$out .= '<tr><td>Status</td><td>' . $license_manager->get_license_status() . '</td></tr>';
		$out .= '</table>';

		return $out;
	}

	/**
	 * Returns info about the current user
	 *
	 * @param string $what What to retrieve, defaults to name.
	 *
	 * @return string
	 */
	private function get_user_info( $what = 'name' ) {
		$current_user = mcms_get_current_user();

		switch ( $what ) {
			case 'email':
				$out = $current_user->user_email;
				break;
			case 'name':
			default:
				$out = $current_user->user_firstname . ' ' . $current_user->user_lastname;
				break;
		}

		return $out;
	}

	/**
	 * Returns the MandarinCMS version + a suffix if current MCMS is multi site
	 *
	 * @return string
	 */
	private function get_mandarincms_version() {
		global $mcms_version;
		$msg = $mcms_version;
		if ( is_multisite() ) {
			$msg .= ' MULTI-SITE';
		}

		return $msg;
	}

	/**
	 * Returns the curl version, if curl is found
	 *
	 * @return string
	 */
	private function get_curl_info() {
		if ( function_exists( 'curl_version' ) ) {
			$curl = curl_version();
			$msg  = $curl['version'];
			if ( ! $curl['features'] && CURL_VERSION_SSL ) {
				$msg .= ' - NO SSL SUPPORT';
			}
		}
		else {
			$msg = 'No CURL installed';
		}

		return $msg;
	}

	/**
	 * Returns a formatted HTML string for the current myskin
	 *
	 * @return string
	 */
	private function get_myskin_info() {
		$myskin = mcms_get_myskin();

		$msg = '<a href="' . $myskin->display( 'MySkinURI' ) . '">' . $myskin->display( 'Name' ) . '</a> v' . $myskin->display( 'Version' ) . ' by ' . $myskin->display( 'Author' );

		if ( is_child_myskin() ) {
			$msg .= '<br />' . 'Child myskin of: ' . $myskin->display( 'Template' );
		}

		return $msg;
	}

	/**
	 * Returns a formatted HTML list of all active modules
	 *
	 * @return string
	 */
	private function get_current_modules() {
		$updates_avail = get_site_transient( 'update_modules' );

		$msg = '';
		foreach ( mcms_get_active_and_valid_modules() as $module ) {
			$module_data = get_module_data( $module );

			$module_file = str_replace( trailingslashit( MCMS_PLUGIN_DIR ), '', $module );

			if ( isset( $updates_avail->response[ $module_file ] ) ) {
				$msg .= '<i class="icon-close1"></i> ';
			}
			$msg .= '<a href="' . $module_data['ModuleURI'] . '">' . $module_data['Name'] . '</a> v' . $module_data['Version'] . '<br/>';
		}

		return $msg;
	}

}
