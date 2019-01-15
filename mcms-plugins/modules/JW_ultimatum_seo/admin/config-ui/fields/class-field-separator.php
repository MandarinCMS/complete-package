<?php
/**
 * @package MCMSSEO\Admin\ConfigurationUI
 */

/**
 * Class MCMSSEO_Config_Field_Separator
 */
class MCMSSEO_Config_Field_Separator extends MCMSSEO_Config_Field_Choice {

	/**
	 * MCMSSEO_Config_Field_Separator constructor.
	 */
	public function __construct() {
		parent::__construct( 'separator' );

		$this->set_property( 'label', __( 'Title Separator', 'mandarincms-seo' ) );
		$this->set_property( 'explanation', __( 'Choose the symbol to use as your title separator. This will display, for instance, between your post title and site name. Symbols are shown in the size they\'ll appear in the search results.', 'mandarincms-seo' ) );

		$this->add_choice( 'sc-dash', '-', __( 'Dash', 'mandarincms-seo' ) );
		$this->add_choice( 'sc-ndash', '&ndash;', __( 'En dash', 'mandarincms-seo' ) );
		$this->add_choice( 'sc-mdash', '&mdash;', __( 'Em dash', 'mandarincms-seo' ) );
		$this->add_choice( 'sc-middot', '&middot;', __( 'Middle dot', 'mandarincms-seo' ) );
		$this->add_choice( 'sc-bull', '&bull;', __( 'Bullet', 'mandarincms-seo' ) );
		$this->add_choice( 'sc-star', '*', __( 'Asterisk', 'mandarincms-seo' ) );
		$this->add_choice( 'sc-smstar', '&#8902;', __( 'Low asterisk', 'mandarincms-seo' ) );
		$this->add_choice( 'sc-pipe', '|', __( 'Vertical bar', 'mandarincms-seo' ) );
		$this->add_choice( 'sc-tilde', '~', __( 'Small tilde', 'mandarincms-seo' ) );
		$this->add_choice( 'sc-laquo', '&laquo;', __( 'Left angle quotation mark', 'mandarincms-seo' ) );
		$this->add_choice( 'sc-raquo', '&raquo;', __( 'Right angle quotation mark', 'mandarincms-seo' ) );
		$this->add_choice( 'sc-lt', '&lt;', __( 'Less than sign', 'mandarincms-seo' ) );
		$this->add_choice( 'sc-gt', '&gt;', __( 'Greater than sign', 'mandarincms-seo' ) );
	}

	/**
	 * Set adapter
	 *
	 * @param MCMSSEO_Configuration_Options_Adapter $adapter Adapter to register lookup on.
	 */
	public function set_adapter( MCMSSEO_Configuration_Options_Adapter $adapter ) {
		$adapter->add_ultimatum_lookup( $this->get_identifier(), 'mcmsseo_titles', 'separator' );
	}
}
