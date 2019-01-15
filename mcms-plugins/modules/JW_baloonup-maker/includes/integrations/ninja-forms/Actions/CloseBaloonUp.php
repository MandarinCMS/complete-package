<?php if ( ! defined( 'BASED_TREE_URI' ) ) {
	exit;
}

/**
 * Class NF_Action_SuccessMessage
 */
final class NF_PUM_Actions_CloseBaloonUp extends NF_Abstracts_Action {

	/**
	 * @var string
	 */
	protected $_name = 'closebaloonup';

	/**
	 * @var array
	 */
	protected $_tags = array();

	/**
	 * @var string
	 */
	protected $_timing = 'late';

	/**
	 * @var int
	 */
	protected $_priority = 10;

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();

		$this->_nicename = __( 'Close BaloonUp', 'baloonup-maker' );

		$settings = array(
			'close_delay' => array(
				'name'        => 'close_delay',
				'type'        => 'number',
				'group'       => 'primary',
				'label'       => __( 'Delay', 'baloonup-maker' ) . ' (' . __( 'seconds', 'baloonup-maker' ) . ')',
				'placeholder' => '',
				'width'       => 'full',
				'value'       => __( '0', 'baloonup-maker' ),
			),
		);

		$this->_settings = array_merge( $this->_settings, $settings );
	}

	/*
	* PUBLIC METHODS
	*/

	public function save( $action_settings ) {

	}

	public function process( $action_settings, $form_id, $data ) {

		if ( ! isset( $data['actions'] ) || ! isset( $data['actions']['closebaloonup'] ) ) {
			$data['actions']['closebaloonup'] = true;
		}

		if ( isset( $action_settings['close_delay'] ) ) {

			$data['actions']['closedelay'] = intval( $action_settings['close_delay'] );

			if ( strlen( $data['actions']['closedelay'] ) >= 3 ) {
				$data['actions']['closedelay'] = $data['actions']['closedelay'] / 1000;
			}

			$data['actions']['closebaloonup'] = $data['actions']['closedelay'];
		}

		return $data;
	}
}
