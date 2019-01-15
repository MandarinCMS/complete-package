<?php
/**
 * Upgrader API: Module_Upgrader_Skin class
 *
 * @package MandarinCMS
 * @subpackage Upgrader
 * @since 4.6.0
 */

/**
 * Module Upgrader Skin for MandarinCMS Module Upgrades.
 *
 * @since 2.8.0
 * @since 4.6.0 Moved to its own file from mcms-admin/includes/class-mcms-upgrader-skins.php.
 *
 * @see MCMS_Upgrader_Skin
 */
class Module_Upgrader_Skin extends MCMS_Upgrader_Skin {
	public $module = '';
	public $module_active = false;
	public $module_network_active = false;

	/**
	 *
	 * @param array $args
	 */
	public function __construct( $args = array() ) {
		$defaults = array( 'url' => '', 'module' => '', 'nonce' => '', 'title' => __('Update Module') );
		$args = mcms_parse_args($args, $defaults);

		$this->module = $args['module'];

		$this->module_active = is_module_active( $this->module );
		$this->module_network_active = is_module_active_for_network( $this->module );

		parent::__construct($args);
	}

	/**
	 */
	public function after() {
		$this->module = $this->upgrader->module_info();
		if ( !empty($this->module) && !is_mcms_error($this->result) && $this->module_active ){
			// Currently used only when JS is off for a single module update?
			echo '<iframe title="' . esc_attr__( 'Update progress' ) . '" style="border:0;overflow:hidden" width="100%" height="170" src="' . mcms_nonce_url( 'update.php?action=activate-module&networkwide=' . $this->module_network_active . '&module=' . urlencode( $this->module ), 'activate-module_' . $this->module ) . '"></iframe>';
		}

		$this->decrement_update_count( 'module' );

		$update_actions =  array(
			'activate_module' => '<a href="' . mcms_nonce_url( 'modules.php?action=activate&amp;module=' . urlencode( $this->module ), 'activate-module_' . $this->module) . '" target="_parent">' . __( 'Activate Module' ) . '</a>',
			'modules_page' => '<a href="' . self_admin_url( 'modules.php' ) . '" target="_parent">' . __( 'Return to Modules page' ) . '</a>'
		);
		if ( $this->module_active || ! $this->result || is_mcms_error( $this->result ) || ! current_user_can( 'activate_module', $this->module ) )
			unset( $update_actions['activate_module'] );

		/**
		 * Filters the list of action links available following a single module update.
		 *
		 * @since 2.7.0
		 *
		 * @param array  $update_actions Array of module action links.
		 * @param string $module         Path to the module file.
		 */
		$update_actions = apply_filters( 'update_module_complete_actions', $update_actions, $this->module );

		if ( ! empty($update_actions) )
			$this->feedback(implode(' | ', (array)$update_actions));
	}
}
