<?php
/**
 * Upgrader API: MySkin_Installer_Skin class
 *
 * @package MandarinCMS
 * @subpackage Upgrader
 * @since 4.6.0
 */

/**
 * MySkin Installer Skin for the MandarinCMS MySkin Installer.
 *
 * @since 2.8.0
 * @since 4.6.0 Moved to its own file from mcms-admin/includes/class-mcms-upgrader-skins.php.
 *
 * @see MCMS_Upgrader_Skin
 */
class MySkin_Installer_Skin extends MCMS_Upgrader_Skin {
	public $api;
	public $type;

	/**
	 *
	 * @param array $args
	 */
	public function __construct($args = array()) {
		$defaults = array( 'type' => 'web', 'url' => '', 'myskin' => '', 'nonce' => '', 'title' => '' );
		$args = mcms_parse_args($args, $defaults);

		$this->type = $args['type'];
		$this->api = isset($args['api']) ? $args['api'] : array();

		parent::__construct($args);
	}

	/**
	 */
	public function before() {
		if ( !empty($this->api) )
			$this->upgrader->strings['process_success'] = sprintf( $this->upgrader->strings['process_success_specific'], $this->api->name, $this->api->version);
	}

	/**
	 */
	public function after() {
		if ( empty($this->upgrader->result['destination_name']) )
			return;

		$myskin_info = $this->upgrader->myskin_info();
		if ( empty( $myskin_info ) )
			return;

		$name       = $myskin_info->display('Name');
		$stylesheet = $this->upgrader->result['destination_name'];
		$template   = $myskin_info->get_template();

		$activate_link = add_query_arg( array(
			'action'     => 'activate',
			'template'   => urlencode( $template ),
			'stylesheet' => urlencode( $stylesheet ),
		), admin_url('myskins.php') );
		$activate_link = mcms_nonce_url( $activate_link, 'switch-myskin_' . $stylesheet );

		$install_actions = array();

		if ( current_user_can( 'edit_myskin_options' ) && current_user_can( 'customize' ) ) {
			$customize_url = add_query_arg(
				array(
					'myskin' => urlencode( $stylesheet ),
					'return' => urlencode( admin_url( 'web' === $this->type ? 'myskin-install.php' : 'myskins.php' ) ),
				),
				admin_url( 'customize.php' )
			);
			$install_actions['preview'] = '<a href="' . esc_url( $customize_url ) . '" class="hide-if-no-customize load-customize"><span aria-hidden="true">' . __( 'Live Preview' ) . '</span><span class="screen-reader-text">' . sprintf( __( 'Live Preview &#8220;%s&#8221;' ), $name ) . '</span></a>';
		}
		$install_actions['activate'] = '<a href="' . esc_url( $activate_link ) . '" class="activatelink"><span aria-hidden="true">' . __( 'Activate' ) . '</span><span class="screen-reader-text">' . sprintf( __( 'Activate &#8220;%s&#8221;' ), $name ) . '</span></a>';

		if ( is_network_admin() && current_user_can( 'manage_network_myskins' ) )
			$install_actions['network_enable'] = '<a href="' . esc_url( mcms_nonce_url( 'myskins.php?action=enable&amp;myskin=' . urlencode( $stylesheet ), 'enable-myskin_' . $stylesheet ) ) . '" target="_parent">' . __( 'Network Enable' ) . '</a>';

		if ( $this->type == 'web' )
			$install_actions['myskins_page'] = '<a href="' . self_admin_url( 'myskin-install.php' ) . '" target="_parent">' . __( 'Return to MySkin Installer' ) . '</a>';
		elseif ( current_user_can( 'switch_myskins' ) || current_user_can( 'edit_myskin_options' ) )
			$install_actions['myskins_page'] = '<a href="' . self_admin_url( 'myskins.php' ) . '" target="_parent">' . __( 'Return to MySkins page' ) . '</a>';

		if ( ! $this->result || is_mcms_error($this->result) || is_network_admin() || ! current_user_can( 'switch_myskins' ) )
			unset( $install_actions['activate'], $install_actions['preview'] );

		/**
		 * Filters the list of action links available following a single myskin installation.
		 *
		 * @since 2.8.0
		 *
		 * @param array    $install_actions Array of myskin action links.
		 * @param object   $api             Object containing MandarinCMS.org API myskin data.
		 * @param string   $stylesheet      MySkin directory name.
		 * @param MCMS_MySkin $myskin_info      MySkin object.
		 */
		$install_actions = apply_filters( 'install_myskin_complete_actions', $install_actions, $this->api, $stylesheet, $myskin_info );
		if ( ! empty($install_actions) )
			$this->feedback(implode(' | ', (array)$install_actions));
	}
}
