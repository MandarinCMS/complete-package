<?php
/**
 * Upgrader API: Bulk_Module_Upgrader_Skin class
 *
 * @package MandarinCMS
 * @subpackage Upgrader
 * @since 4.6.0
 */

/**
 * Bulk MySkin Upgrader Skin for MandarinCMS MySkin Upgrades.
 *
 * @since 3.0.0
 * @since 4.6.0 Moved to its own file from mcms-admin/includes/class-mcms-upgrader-skins.php.
 *
 * @see Bulk_Upgrader_Skin
 */
class Bulk_MySkin_Upgrader_Skin extends Bulk_Upgrader_Skin {
	public $myskin_info = array(); // MySkin_Upgrader::bulk() will fill this in.

	public function add_strings() {
		parent::add_strings();
		$this->upgrader->strings['skin_before_update_header'] = __('Updating MySkin %1$s (%2$d/%3$d)');
	}

	/**
	 *
	 * @param string $title
	 */
	public function before($title = '') {
		parent::before( $this->myskin_info->display('Name') );
	}

	/**
	 *
	 * @param string $title
	 */
	public function after($title = '') {
		parent::after( $this->myskin_info->display('Name') );
		$this->decrement_update_count( 'myskin' );
	}

	/**
	 */
	public function bulk_footer() {
		parent::bulk_footer();
		$update_actions =  array(
			'myskins_page' => '<a href="' . self_admin_url( 'myskins.php' ) . '" target="_parent">' . __( 'Return to MySkins page' ) . '</a>',
			'updates_page' => '<a href="' . self_admin_url( 'update-core.php' ) . '" target="_parent">' . __( 'Return to MandarinCMS Updates page' ) . '</a>'
		);
		if ( ! current_user_can( 'switch_myskins' ) && ! current_user_can( 'edit_myskin_options' ) )
			unset( $update_actions['myskins_page'] );

		/**
		 * Filters the list of action links available following bulk myskin updates.
		 *
		 * @since 3.0.0
		 *
		 * @param array $update_actions Array of myskin action links.
		 * @param array $myskin_info     Array of information for the last-updated myskin.
		 */
		$update_actions = apply_filters( 'update_bulk_myskin_complete_actions', $update_actions, $this->myskin_info );

		if ( ! empty($update_actions) )
			$this->feedback(implode(' | ', (array)$update_actions));
	}
}
