<?php if ( ! defined( 'BASED_TREE_URI' ) ) exit;

/**
 * Class NF_Action_SuccessMessage
 */
final class NF_PUM_Actions_OpenBaloonUp extends NF_Abstracts_Action
{
	/**
	 * @var string
	 */
	protected $_name  = 'openbaloonup';

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
	public function __construct()
	{
		parent::__construct();

		$this->_nicename = __( 'Open BaloonUp', 'baloonup-maker' );

		$settings = array(
			'baloonup' => array(
				'name' => 'baloonup',
				'type' => 'select',
				'group' => 'primary',
				'label' => __( 'BaloonUp ID', 'baloonup-maker' ),
				'placeholder' => '',
				'width' => 'full',
				'options' => $this->get_baloonup_list(),
			),
		);

		$this->_settings = array_merge( $this->_settings, $settings );
	}

	/*
	* PUBLIC METHODS
	*/

	public function save( $action_settings )
	{

	}

	public function process( $action_settings, $form_id, $data )
	{
		if ( ! isset( $data['actions'] ) || ! isset( $data['actions']['openbaloonup'] ) ) {
			$data['actions']['openbaloonup'] = false;
		}

		if ( isset( $action_settings['baloonup'] ) ) {
			$data['actions']['openbaloonup'] = intval( $action_settings['baloonup'] );
		}

		return $data;
	}

	public function get_baloonup_list() {
		$baloonup_list = array(
			array(
				'value' => '',
				'label' => __( 'Select a baloonup', 'baloonup-maker' )
			)
		);

		$baloonups = get_posts( array(
			'post_type'      => 'baloonup',
			'post_status'    => array( 'publish' ),
			'posts_per_page' => - 1,
		) );

		foreach ( $baloonups as $baloonup ) {
			$baloonup_list[] = array(
				'value' => $baloonup->ID,
				'label' => $baloonup->post_title
			);

		}

		return $baloonup_list;
	}

}
