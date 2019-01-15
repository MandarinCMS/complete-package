<?php
/**
 * @package MCMSSEO\Premium\Classes
 */

/**
 * Class MCMSSEO_Redirect_Presenter
 */
class MCMSSEO_Redirect_Presenter {

	/**
	 * Function that outputs the redirect page
	 *
	 * @param string $tab_to_display The tab that will be shown.
	 */
	public function display( $tab_to_display ) {
		$tab_presenter = $this->get_tab_presenter( $tab_to_display );
		$redirect_tabs = $this->navigation_tabs( $tab_to_display );

		include( MCMSSEO_PATH . 'premium/classes/redirect/views/redirects.php' );
	}

	/**
	 * Returns a tab presenter.
	 *
	 * @param string $tab_to_display The tab that will be shown.
	 *
	 * @return null|MCMSSEO_Redirect_Tab_Presenter
	 */
	private function get_tab_presenter( $tab_to_display ) {
		$tab_presenter = null;
		switch ( $tab_to_display ) {
			case 'plain' :
			case 'regex' :
				$redirect_manager = new MCMSSEO_Redirect_Manager( $tab_to_display );
				$tab_presenter    = new MCMSSEO_Redirect_Table_Presenter( $tab_to_display, $this->get_view_vars() );
				$tab_presenter->set_table( $redirect_manager->get_redirects() );
				break;
			case 'settings' :
				$tab_presenter = new MCMSSEO_Redirect_Settings_Presenter( $tab_to_display, $this->get_view_vars() );
				break;
		}

		return $tab_presenter;
	}

	/**
	 * Returning the anchors html for the tabs
	 *
	 * @param string $active_tab The tab that will be active.
	 *
	 * @return array
	 */
	private function navigation_tabs( $active_tab ) {
		return array(
			'tabs' => array(
				'plain'    => __( 'Redirects', 'mandarincms-seo-premium' ),
				'regex'    => __( 'Regex Redirects', 'mandarincms-seo-premium' ),
				'settings' => __( 'Settings', 'mandarincms-seo-premium' ),
			),
			'current_tab' => $active_tab,
			'page_url' => admin_url( 'admin.php?page=mcmsseo_redirects&tab=' ),
		);
	}

	/**
	 * Getting the variables for the view
	 *
	 * @return array
	 */
	private function get_view_vars() {
		return array(
			'nonce' => mcms_create_nonce( 'mcmsseo-redirects-ajax-security' ),
		);
	}
}
