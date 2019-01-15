<?php
/**
 * @package MCMSSEO\Admin\Options\Tabs
 */

/**
 * Class MCMSSEO_Option_Tabs_Formatter
 */
class MCMSSEO_Option_Tabs_Formatter {

	/**
	 * @param MCMSSEO_Option_Tabs $option_tabs Option Tabs to get base from.
	 * @param MCMSSEO_Option_Tab  $tab         Tab to get name from.
	 *
	 * @return string
	 */
	public function get_tab_view( MCMSSEO_Option_Tabs $option_tabs, MCMSSEO_Option_Tab $tab ) {
		return MCMSSEO_PATH . 'admin/views/tabs/' . $option_tabs->get_base() . '/' . $tab->get_name() . '.php';
	}

	/**
	 * @param MCMSSEO_Option_Tabs $option_tabs Option Tabs to get tabs from.
	 * @param Ultimatum_Form        $yform       Ultimatum Form which is being used in the views.
	 * @param array             $options     Options which are being used in the views.
	 */
	public function run( MCMSSEO_Option_Tabs $option_tabs, Ultimatum_Form $yform, $options = array() ) {

		echo '<h2 class="nav-tab-wrapper" id="mcmsseo-tabs">';
		foreach ( $option_tabs->get_tabs() as $tab ) {
			printf( '<a class="nav-tab" id="%1$s-tab" href="#top#%1$s">%2$s</a>', $tab->get_name(), $tab->get_label() );
		}
		echo '</h2>';

		foreach ( $option_tabs->get_tabs() as $tab ) {
			// Prepare the help center for each tab.
			$help_center = new MCMSSEO_Help_Center( $option_tabs->get_base(), $tab );

			$identifier = $tab->get_name();
			printf( '<div id="%s" class="mcmsseotab">', $identifier );

			// Output the help center.
			$help_center->output_help_center();

			// Output the settings view for all tabs.
			$tab_view = $this->get_tab_view( $option_tabs, $tab );
			if ( is_file( $tab_view ) ) {
				require_once $tab_view;
			}

			echo '</div>';
		}
	}
}
