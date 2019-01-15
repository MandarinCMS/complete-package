<?php
/**
 * Upgrader API: MySkin_Upgrader_Skin class
 *
 * @package MandarinCMS
 * @subpackage Upgrader
 * @since 4.6.0
 */

/**
 * MySkin Upgrader Skin for MandarinCMS MySkin Upgrades.
 *
 * @since 2.8.0
 * @since 4.6.0 Moved to its own file from mcms-admin/includes/class-mcms-upgrader-skins.php.
 *
 * @see MCMS_Upgrader_Skin
 */
class MySkin_Upgrader_Skin extends MCMS_Upgrader_Skin {
	public $myskin = '';

	/**
	 *
	 * @param array $args
	 */
	public function __construct($args = array()) {
		$defaults = array( 'url' => '', 'myskin' => '', 'nonce' => '', 'title' => __('Update MySkin') );
		$args = mcms_parse_args($args, $defaults);

		$this->myskin = $args['myskin'];

		parent::__construct($args);
	}

	/**
	 */
	public function after() {
		$this->decrement_update_count( 'myskin' );

		$update_actions = array();
		if ( ! empty( $this->upgrader->result['destination_name'] ) && $myskin_info = $this->upgrader->myskin_info() ) {
			$name       = $myskin_info->display('Name');
			$stylesheet = $this->upgrader->result['destination_name'];
			$template   = $myskin_info->get_template();

			$activate_link = add_query_arg( array(
				'action'     => 'activate',
				'template'   => urlencode( $template ),
				'stylesheet' => urlencode( $stylesheet ),
			), admin_url('myskins.php') );
			$activate_link = mcms_nonce_url( $activate_link, 'switch-myskin_' . $stylesheet );

			$customize_url = add_query_arg(
				array(
					'myskin' => urlencode( $stylesheet ),
					'return' => urlencode( admin_url( 'myskins.php' ) ),
				),
				admin_url( 'customize.php' )
			);
			if ( get_stylesheet() == $stylesheet ) {
				if ( current_user_can( 'edit_myskin_options' ) && current_user_can( 'customize' ) ) {
					$update_actions['preview']  = '<a href="' . esc_url( $customize_url ) . '" class="hide-if-no-customize load-customize"><span aria-hidden="true">' . __( 'Customize' ) . '</span><span class="screen-reader-text">' . sprintf( __( 'Customize &#8220;%s&#8221;' ), $name ) . '</span></a>';
				}
			} elseif ( current_user_can( 'switch_myskins' ) ) {
				if ( current_user_can( 'edit_myskin_options' ) && current_user_can( 'customize' ) ) {
					$update_actions['preview'] = '<a href="' . esc_url( $customize_url ) . '" class="hide-if-no-customize load-customize"><span aria-hidden="true">' . __( 'Live Preview' ) . '</span><span class="screen-reader-text">' . sprintf( __( 'Live Preview &#8220;%s&#8221;' ), $name ) . '</span></a>';
				}
				$update_actions['activate'] = '<a href="' . esc_url( $activate_link ) . '" class="activatelink"><span aria-hidden="true">' . __( 'Activate' ) . '</span><span class="screen-reader-text">' . sprintf( __( 'Activate &#8220;%s&#8221;' ), $name ) . '</span></a>';
			}

			if ( ! $this->result || is_mcms_error( $this->result ) || is_network_admin() )
				unset( $update_actions['preview'], $update_actions['activate'] );
		}

		$update_actions['myskins_page'] = '<a href="' . self_admin_url( 'myskins.php' ) . '" target="_parent">' . __( 'Return to MySkins page' ) . '</a>';

		/**
		 * Filters the list of action links available following a single myskin update.
		 *
		 * @since 2.8.0
		 *
		 * @param array  $update_actions Array of myskin action links.
		 * @param string $myskin          MySkin directory name.
		 */
		$update_actions = apply_filters( 'update_myskin_complete_actions', $update_actions, $this->myskin );

		if ( ! empty($update_actions) )
			$this->feedback(implode(' | ', (array)$update_actions));
	}
}
