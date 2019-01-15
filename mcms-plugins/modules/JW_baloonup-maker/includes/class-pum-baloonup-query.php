<?php
/**
 * BaloonUps Query
 *
 * @package     PUM
 * @subpackage  Classes/BaloonUps
 * @copyright   Copyright (c) 2016, Daniel Iser
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 * @since       1.4
 */

// Exit if accessed directly
if ( ! defined( 'BASED_TREE_URI' ) ) {
	exit;
}

/**
 * Class PUM_BaloonUp_Query
 */
class PUM_BaloonUp_Query {

	/**
	 * The args to pass to the pum_get_baloonups() query
	 *
	 * @var array
	 * @access public
	 */
	public $args = array();

	/**
	 * The baloonups found based on the criteria set
	 *
	 * @var array
	 * @access public
	 */
	public $baloonups = array();

	/**
	 * Default query arguments.
	 *
	 * Not all of these are valid arguments that can be passed to MCMS_Query. The ones that are not, are modified before
	 * the query is run to convert them to the proper syntax.
	 *
	 * @access public
	 *
	 * @param $args array The array of arguments that can be passed in and used for setting up this baloonup query.
	 */
	public function __construct( $args = array() ) {
		$defaults = array(
			'output'     => 'baloonups', // Use 'posts' to get standard post objects
			'post_type'  => array( 'baloonup' ),
			'start_date' => false,
			'end_date'   => false,
			'number'     => 20,
			'page'       => null,
			'orderby'    => 'ID',
			'order'      => 'DESC',
			'user'       => null,
			'status'     => 'publish',
			'meta_key'   => null,
			'year'       => null,
			'month'      => null,
			'day'        => null,
			's'          => null,
			'children'   => false,
			'fields'     => null,
		);

		$this->args = mcms_parse_args( $args, $defaults );

		$this->init();
	}

	/**
	 * Set a query variable.
	 *
	 * @access public
	 */
	public function __set( $query_var, $value ) {
		if ( in_array( $query_var, array( 'meta_query', 'tax_query' ) ) ) {
			$this->args[ $query_var ][] = $value;
		} else {
			$this->args[ $query_var ] = $value;
		}
	}

	/**
	 * Unset a query variable.
	 *
	 * @access public
	 */
	public function __unset( $query_var ) {
		unset( $this->args[ $query_var ] );
	}

	/**
	 * Modify the query/query arguments before we retrieve baloonups.
	 *
	 * @access public
	 * @return void
	 */
	public function init() {

		add_action( 'pum_pre_get_baloonups', array( $this, 'date_filter_pre' ) );
		add_action( 'pum_post_get_baloonups', array( $this, 'date_filter_post' ) );

		add_action( 'pum_pre_get_baloonups', array( $this, 'orderby' ) );
		add_action( 'pum_pre_get_baloonups', array( $this, 'status' ) );
		add_action( 'pum_pre_get_baloonups', array( $this, 'month' ) );
		add_action( 'pum_pre_get_baloonups', array( $this, 'per_page' ) );
		add_action( 'pum_pre_get_baloonups', array( $this, 'page' ) );
	}

	/**
	 * Retrieve baloonups.
	 *
	 * The query can be modified in two ways; either the action before the
	 * query is run, or the filter on the arguments (existing mainly for backwards
	 * compatibility).
	 *
	 * @access public
	 * @return object
	 */
	public function get_baloonups() {

		do_action( 'pum_pre_get_baloonups', $this );

		$query = new MCMS_Query( $this->args );

		$custom_output = array(
			'baloonups',
			'pum_baloonups',
		);

		if ( ! in_array( $this->args['output'], $custom_output ) ) {
			return $query->posts;
		}

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();

				$baloonup_id = get_post()->ID;
				$baloonup    = new PUM_BaloonUp( $baloonup_id );

				$this->baloonups[] = apply_filters( 'pum_baloonup', $baloonup, $baloonup_id, $this );
			}

			mcms_reset_postdata();
		}

		do_action( 'pum_post_get_baloonups', $this );

		return $this->baloonups;
	}

	/**
	 * If querying a specific date, add the proper filters.
	 *
	 * @access public
	 * @return void
	 */
	public function date_filter_pre() {
		if ( ! ( $this->args['start_date'] || $this->args['end_date'] ) ) {
			return;
		}

		$this->setup_dates( $this->args['start_date'], $this->args['end_date'] );

		add_filter( 'posts_where', array( $this, 'baloonups_where' ) );
	}

	/**
	 * If querying a specific date, remove filters after the query has been run
	 * to avoid affecting future queries.
	 *
	 * @access public
	 * @return void
	 */
	public function date_filter_post() {
		if ( ! ( $this->args['start_date'] || $this->args['end_date'] ) ) {
			return;
		}

		remove_filter( 'posts_where', array( $this, 'baloonups_where' ) );
	}

	/**
	 * Post Status
	 *
	 * @access public
	 * @return void
	 */
	public function status() {
		if ( ! isset ( $this->args['status'] ) ) {
			return;
		}

		$this->__set( 'post_status', $this->args['status'] );
		$this->__unset( 'status' );
	}

	/**
	 * Current Page
	 *
	 * @access public
	 * @return void
	 */
	public function page() {
		if ( ! isset ( $this->args['page'] ) ) {
			return;
		}

		$this->__set( 'paged', $this->args['page'] );
		$this->__unset( 'page' );
	}

	/**
	 * Posts Per Page
	 *
	 * @access public
	 * @return void
	 */
	public function per_page() {

		if ( ! isset( $this->args['number'] ) ) {
			return;
		}

		if ( $this->args['number'] == - 1 ) {
			$this->__set( 'nopaging', true );
		} else {
			$this->__set( 'posts_per_page', $this->args['number'] );
		}

		$this->__unset( 'number' );
	}

	/**
	 * Current Month
	 *
	 * @access public
	 * @return void
	 */
	public function month() {
		if ( ! isset ( $this->args['month'] ) ) {
			return;
		}

		$this->__set( 'monthnum', $this->args['month'] );
		$this->__unset( 'month' );
	}

	/**
	 * Order
	 *
	 * @access public
	 * @return void
	 */
	public function orderby() {
		switch ( $this->args['orderby'] ) {
			case 'amount' :
				$this->__set( 'orderby', 'meta_value_num' );
				$this->__set( 'meta_key', '_pum_baloonup_total' );
				break;
			default :
				$this->__set( 'orderby', $this->args['orderby'] );
				break;
		}
	}

}
