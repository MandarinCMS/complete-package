<?php
/**
 * @package MCMSSEO\Admin\ConfigurationUI
 */

/**
 * Class MCMSSEO_Configuration_Service
 */
class MCMSSEO_Configuration_Service {

	/** @var MCMSSEO_Configuration_Structure */
	protected $structure;

	/** @var MCMSSEO_Configuration_Components */
	protected $components;

	/** @var MCMSSEO_Configuration_Storage */
	protected $storage;

	/** @var MCMSSEO_Configuration_Endpoint */
	protected $endpoint;

	/** @var MCMSSEO_Configuration_Options_Adapter */
	protected $adapter;

	/**
	 * Hook into the REST API
	 */
	public function register_hooks() {
		add_action( 'rest_api_init', array( $this, 'initialize' ) );
	}

	/**
	 * Register the service and boot handlers
	 */
	public function initialize() {
		$this->endpoint->register();
	}

	/**
	 * Set default handlers
	 */
	public function set_default_providers() {
		$this->set_storage( new MCMSSEO_Configuration_Storage() );
		$this->set_options_adapter( new MCMSSEO_Configuration_Options_Adapter() );
		$this->set_components( new MCMSSEO_Configuration_Components() );
		$this->set_endpoint( new MCMSSEO_Configuration_Endpoint() );
		$this->set_structure( new MCMSSEO_Configuration_Structure() );
	}

	/**
	 * Set storage handler
	 *
	 * @param MCMSSEO_Configuration_Storage $storage Storage handler to use.
	 */
	public function set_storage( MCMSSEO_Configuration_Storage $storage ) {
		$this->storage = $storage;
	}

	/**
	 * Set endpoint handler
	 *
	 * @param MCMSSEO_Configuration_Endpoint $endpoint Endpoint implementation to use.
	 */
	public function set_endpoint( MCMSSEO_Configuration_Endpoint $endpoint ) {
		$this->endpoint = $endpoint;
		$this->endpoint->set_service( $this );
	}

	/**
	 * Set the options adapter
	 *
	 * @param MCMSSEO_Configuration_Options_Adapter $adapter Adapter to use.
	 */
	public function set_options_adapter( MCMSSEO_Configuration_Options_Adapter $adapter ) {
		$this->adapter = $adapter;
	}

	/**
	 * Set components provider
	 *
	 * @param MCMSSEO_Configuration_Components $components Component provider to use.
	 */
	public function set_components( MCMSSEO_Configuration_Components $components ) {
		$this->components = $components;
	}

	/**
	 * Set structure provider
	 *
	 * @param MCMSSEO_Configuration_Structure $structure Structure provider to use.
	 */
	public function set_structure( MCMSSEO_Configuration_Structure $structure ) {
		$this->structure = $structure;
	}

	/**
	 * Populate the configuration
	 */
	protected function populate_configuration() {
		$this->storage->set_adapter( $this->adapter );
		$this->storage->add_default_fields();

		$this->components->initialize();
		$this->components->set_storage( $this->storage );
	}

	/**
	 * Used by endpoint to retrieve configuration
	 *
	 * @return array List of settings.
	 */
	public function get_configuration() {
		$this->populate_configuration();

		$fields = $this->storage->retrieve();
		$steps  = $this->structure->retrieve();

		return array(
			'fields' => $fields,
			'steps'  => $steps,
		);
	}

	/**
	 * Used by endpoint to store changes
	 *
	 * @param MCMS_REST_Request $request Request from the REST API.
	 *
	 * @return array List of feedback per option if saving succeeded.
	 */
	public function set_configuration( MCMS_REST_Request $request ) {
		$this->populate_configuration();

		return $this->storage->store( $request->get_json_params() );
	}
}
