<?php
/**
 * Upgrader API: Bulk_Module_Upgrader_Skin class
 *
 * @package MandarinCMS
 * @subpackage Upgrader
 * @since 4.6.0
 */

/**
 * Bulk Module Upgrader Skin for MandarinCMS Module Upgrades.
 *
 * @since 3.0.0
 * @since 4.6.0 Moved to its own file from mcms-admin/includes/class-mcms-upgrader-skins.php.
 *
 * @see Bulk_Upgrader_Skin
 */
class Bulk_Module_Upgrader_Skin extends Bulk_Upgrader_Skin {
	public $module_info = array(); // Module_Upgrader::bulk() will fill this in.

	public function add_strings() {
		parent::add_strings();
		$this->upgrader->strings['skin_before_update_header'] = __('Updating Module %1$s (%2$d/%3$d)');
	}

	/**
	 *
	 * @param string $title
	 */
	public function before($title = '') {
		parent::before($this->module_info['Title']);
	}

	/**
	 *
	 * @param string $title
	 */
	public function after($title = '') {
		parent::after($this->module_info['Title']);
		$this->decrement_update_count( 'module' );
	}

	/**
	 */
	public function bulk_footer() {
		parent::bulk_footer();
		$update_actions =  array(
			'modules_page' => '<a href="' . self_admin_url( 'modules.php' ) . '" target="_parent">' . __( 'Return to Modules page' ) . '</a>',
			'updates_page' => '<a href="' . self_admin_url( 'update-core.php' ) . '" target="_parent">' . __( 'Return to MandarinCMS Updates page' ) . '</a>'
		);
		if ( ! current_user_can( 'activate_modules' ) )
			unset( $update_actions['modules_page'] );

		/**
		 * Filters the list of action links available following bulk module updates.
		 *
		 * @since 3.0.0
		 *
		 * @param array $update_actions Array of module action links.
		 * @param array $module_info    Array of information for the last-updated module.
		 */
		$update_actions = apply_filters( 'update_bulk_modules_complete_actions', $update_actions, $this->module_info );

		if ( ! empty($update_actions) )
			$this->feedback(implode(' | ', (array)$update_actions));
	}
}
