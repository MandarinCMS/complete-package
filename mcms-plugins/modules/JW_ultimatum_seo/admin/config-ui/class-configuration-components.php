<?php
/**
 * @package MCMSSEO\Admin\ConfigurationUI
 */

/**
 * Class MCMSSEO_Configuration_Components
 */
class MCMSSEO_Configuration_Components {

	/** @var MCMSSEO_Config_Component[] List of registered components */
	protected $components = array();

	/** @var MCMSSEO_Configuration_Options_Adapter Adapter */
	protected $adapter;

	/**
	 * Add default components.
	 */
	public function initialize() {
		$this->add_component( new MCMSSEO_Config_Component_Connect_Google_Search_Console() );
		$this->add_component( new MCMSSEO_Config_Component_Mailchimp_Signup() );
	}

	/**
	 * Add a component
	 *
	 * @param MCMSSEO_Config_Component $component Component to add.
	 */
	public function add_component( MCMSSEO_Config_Component $component ) {
		$this->components[] = $component;
	}

	/**
	 * Sets the storage to use.
	 *
	 * @param MCMSSEO_Configuration_Storage $storage Storage to use.
	 */
	public function set_storage( MCMSSEO_Configuration_Storage $storage ) {
		$this->set_adapter( $storage->get_adapter() );

		foreach ( $this->components as $component ) {
			$storage->add_field( $component->get_field() );
		}
	}

	/**
	 * Sets the adapter to use.
	 *
	 * @param MCMSSEO_Configuration_Options_Adapter $adapter Adapter to use.
	 */
	public function set_adapter( MCMSSEO_Configuration_Options_Adapter $adapter ) {
		$this->adapter = $adapter;

		foreach ( $this->components as $component ) {
			$adapter->add_custom_lookup(
				$component->get_field()->get_identifier(),
				array(
					$component,
					'get_data',
				),
				array(
					$component,
					'set_data',
				)
			);
		}
	}
}
