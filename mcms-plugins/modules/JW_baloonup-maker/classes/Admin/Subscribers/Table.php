<?php
/*******************************************************************************
 * Copyright (c) 2018, MCMS BaloonUp Maker
 ******************************************************************************/

if ( ! defined( 'BASED_TREE_URI' ) ) {
	exit;
}

/**
 * Class PUM_Admin_Subscribers_Table
 */
class PUM_Admin_Subscribers_Table extends PUM_ListTable {

	/**
	 * Constructor.
	 *
	 * The child class should call this constructor from its own constructor to override
	 * the default $args.
	 *
	 * @param array|string $args     {
	 *                               Array or string of arguments.
	 *
	 * @type string        $plural   Plural value used for labels and the objects being listed.
	 *                            This affects things such as CSS class-names and nonces used
	 *                            in the list table, e.g. 'posts'. Default empty.
	 * @type string        $singular Singular label for an object being listed, e.g. 'post'.
	 *                            Default empty
	 * @type bool          $ajax     Whether the list table supports Ajax. This includes loading
	 *                            and sorting data, for example. If true, the class will call
	 *                            the _js_vars() method in the footer to provide variables
	 *                            to any scripts handling Ajax events. Default false.
	 * @type string        $screen   String containing the hook name used to determine the current
	 *                            screen. If left null, the current screen will be automatically set.
	 *                            Default null.
	 * }
	 */
	public function __construct( $args = array() ) {
		$args = mcms_parse_args( $args, array(
			'plural'   => 'subscribers',    // Plural value used for labels and the objects being listed.
			'singular' => 'subscriber',        // Singular label for an object being listed, e.g. 'post'.
			'ajax'     => false,        // If true, the parent class will call the _js_vars() method in the footer
		) );

		parent::__construct( $args );
	}

	/**
	 * Prepares the list of items for displaying.
	 *
	 * @uses PUM_ListTable::set_pagination_args()
	 */
	public function prepare_items() {
		$this->_column_headers = $this->get_column_info();

		// check and process any actions such as bulk actions.
		$this->handle_table_actions();

		$limit = $this->get_items_per_page( 'pum_subscribers_per_page' );

		$query_args = array(
			's'       => isset( $_REQUEST['s'] ) ? sanitize_text_field( $_REQUEST['s'] ) : null,
			'limit'   => $limit,
			'page'    => $this->get_pagenum(),
			'orderby' => isset( $_REQUEST['orderby'] ) ? sanitize_text_field( $_REQUEST['orderby'] ) : null,
			'order'   => isset( $_REQUEST['order'] ) ? sanitize_text_field( $_REQUEST['order'] ) : null,
		);

		$this->items = PUM_DB_Subscribers::instance()->query( $query_args, 'ARRAY_A' );

		$total_subscribers = PUM_DB_Subscribers::instance()->total_rows( $query_args );

		$this->set_pagination_args( array(
			'total_items' => $total_subscribers,
			'per_page'    => $limit,
			'total_pages' => ceil( $total_subscribers / $limit ),
		) );
	}


	/**
	 * Get a list of columns. The format is:
	 * 'internal-name' => 'Title'
	 *
	 * @return array
	 */
	public function get_columns() {
		return apply_filters( 'pum_subscribers_table_columns', array(
			'cb'       => '<input type="checkbox" />', // to display the checkbox.
			'email'    => __( 'Email', 'baloonup-maker' ),
			'name'     => __( 'Full Name', 'baloonup-maker' ),
			'fname'    => __( 'First Name', 'baloonup-maker' ),
			'lname'    => __( 'Last Name', 'baloonup-maker' ),
			'baloonup_id' => __( 'BaloonUp', 'baloonup-maker' ),
			//'user_id'  => __( 'User ID', 'baloonup-maker' ),
			'created'  => _x( 'Subscribed On', 'column name', 'baloonup-maker' ),
		) );
	}

	/**
	 * Get a list of sortable columns. The format is:
	 * 'internal-name' => 'orderby'
	 * or
	 * 'internal-name' => array( 'orderby', true )
	 *
	 * The second format will make the initial sorting order be descending
	 * \     *
	 *
	 * @return array
	 */
	protected function get_sortable_columns() {
		return apply_filters( 'pum_subscribers_table_columns', array(
			'email'    => 'email',
			'fname'    => 'fname',
			'lname'    => 'lname',
			'baloonup_id' => 'baloonup_id',
			'created'  => 'created',
		) );
	}

	/**
	 * Gets the name of the primary column.
	 *
	 * @return string The name of the primary column.
	 */
	protected function get_primary_column_name() {
		return 'email';
	}


	/**
	 * Text displayed when no user data is available
	 */
	public function no_items() {
		_e( 'No subscribers available.', 'baloonup-maker' );
	}

	/**
	 * Render a column when no column specific method exists.
	 *
	 * @param array  $item
	 * @param string $column_name
	 *
	 * @return mixed
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'created':
				return date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $item[ $column_name ] ) );
			default:
				return $item[ $column_name ];
		}
	}

	/**
	 * Get value for checkbox column.
	 *
	 * The special 'cb' column
	 *
	 * @param object $item A row's data
	 *
	 * @return string Text to be placed inside the column <td>.
	 */
	protected function column_cb( $item ) {
		$label = sprintf( '<label class="screen-reader-text" for="subscriber_%d">%s</label>', $item['ID'], sprintf( __( 'Select %s' ), $item['name'] ) );

		$input = sprintf( '<input type="checkbox" name="%1$s[]" id="subscriber_%2$d" value="%2$d" />', $this->_args['singular'], $item['ID'] );

		return sprintf( '%s%s', $label, $input );
	}

	/** ************************************************************************
	 * Recommended. This is a custom column method and is responsible for what
	 * is rendered in any column with a name/slug of 'title'. Every time the class
	 * needs to render a column, it first looks for a method named
	 * column_{$column_title} - if it exists, that method is run. If it doesn't
	 * exist, column_default() is called instead.
	 *
	 * This example also illustrates how to implement rollover actions. Actions
	 * should be an associative array formatted as 'slug'=>'link html' - and you
	 * will need to generate the URLs yourself. You could even ensure the links
	 *
	 *
	 * @see MCMS_List_Table::::single_row_columns()
	 *
	 * @param array $item A singular item (one full row's worth of data)
	 *
	 * @return string Text to be placed inside the column <td> (movie title only)
	 **************************************************************************/
	function column_email( $item ) {

		$url = add_query_arg( array(
			'page'       => $_REQUEST['page'],
			'subscriber' => $item['ID'],
			'_mcmsnonce'   => mcms_create_nonce( 'pum_subscribers_table_action_nonce' ),
		), admin_url( 'edit.php?page=pum-subscribers&post_type=baloonup' ) );

		$edit_url = add_query_arg( array(
			'action' => 'edit',
		), $url );

		$delete_url = add_query_arg( array(
			'action' => 'delete',
		), $url );

		//Build row actions
		$actions = array(
			//'edit'   => sprintf( '<a href="%s">Edit</a>', $edit_url ),
			'delete' => sprintf( '<a href="%s">Delete</a>', $delete_url ),
		);

		//Return the title contents
		return sprintf( '%1$s <span style="color:silver">(id:%2$s)</span>%3$s', /*$1%s*/
			$item['email'], /*$2%s*/
			$item['ID'], /*$3%s*/
			$this->row_actions( $actions ) );
	}


	/** ************************************************************************
	 * Recommended. This is a custom column method and is responsible for what
	 * is rendered in any column with a name/slug of 'title'. Every time the class
	 * needs to render a column, it first looks for a method named
	 * column_{$column_title} - if it exists, that method is run. If it doesn't
	 * exist, column_default() is called instead.
	 *
	 * This example also illustrates how to implement rollover actions. Actions
	 * should be an associative array formatted as 'slug'=>'link html' - and you
	 * will need to generate the URLs yourself. You could even ensure the links
	 *
	 *
	 * @see MCMS_List_Table::::single_row_columns()
	 *
	 * @param array $item A singular item (one full row's worth of data)
	 *
	 * @return string Text to be placed inside the column <td> (movie title only)
	 **************************************************************************/
	function column_name( $item ) {
		$user_id = $item['user_id'] > 0 ? absint( $item['user_id'] ) : null;

		if ( $user_id ) {
			$url = admin_url( "user-edit.php?user_id=$user_id" );

			//Return the title contents
			return sprintf( '%s<br/><small style="color:silver">(%s: <a href="%s">#%s</a>)</small>', $item['name'], __( 'User ID', 'baloonup-maker' ), $url, $item['user_id'] );
		} else {
			return $item['name'];
		}
	}


	/** ************************************************************************
	 * Recommended. This is a custom column method and is responsible for what
	 * is rendered in any column with a name/slug of 'title'. Every time the class
	 * needs to render a column, it first looks for a method named
	 * column_{$column_title} - if it exists, that method is run. If it doesn't
	 * exist, column_default() is called instead.
	 *
	 * This example also illustrates how to implement rollover actions. Actions
	 * should be an associative array formatted as 'slug'=>'link html' - and you
	 * will need to generate the URLs yourself. You could even ensure the links
	 *
	 *
	 * @see MCMS_List_Table::::single_row_columns()
	 *
	 * @param array $item A singular item (one full row's worth of data)
	 *
	 * @return string Text to be placed inside the column <td> (movie title only)
	 **************************************************************************/
	function column_baloonup_id( $item ) {
		$baloonup_id = $item['baloonup_id'] > 0 ? absint( $item['baloonup_id'] ) : null;

		$baloonup = pum_get_baloonup( $baloonup_id );

		if ( $baloonup_id && pum_is_baloonup( $baloonup ) ) {
			$url = admin_url( "post.php?post={$baloonup_id}&action=edit" );;

			//Return the title contents
			return sprintf( '%s<br/><small style="color:silver">(%s: <a href="%s">#%s</a>)</small>', $baloonup->post_title, __( 'ID', 'baloonup-maker' ), $url, $item['baloonup_id'] );
		} else {
			return __( 'N/A', 'baloonup-maker' );
		}
	}


	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		/*
		 * on hitting apply in bulk actions the url params are set as
		 * ?action=bulk-download&paged=1&action2=-1
		 *
		 * action and action2 are set based on the triggers above or below the table
		 */
		$actions = array(
			'bulk-delete' => __( 'Delete', 'baloonup-maker' ),
		);

		return $actions;
	}

	/**
	 * Process actions triggered by the user
	 *
	 * @since    1.0.0
	 *
	 */
	public function handle_table_actions() {

		//Detect when a bulk action is being triggered...
		$action1 = $this->current_action();

		if ( in_array( $action1, array( 'delete', 'bulk-delete' ) ) ) {

			// verify the nonce.
			if ( ! mcms_verify_nonce( mcms_unslash( $_REQUEST['_mcmsnonce'] ), $action1 == 'delete' ? 'pum_subscribers_table_action_nonce' : 'bulk-subscribers' ) ) {
				$this->invalid_nonce_redirect();
			} else {

				$subscribers = isset( $_REQUEST['subscriber'] ) ? $_REQUEST['subscriber'] : array();

				if ( is_numeric( $subscribers ) ) {
					$subscribers = array( $subscribers );
				}

				$subscribers = mcms_parse_id_list( $subscribers );

				if ( $subscribers ) {

					$status = array();

					foreach ( $subscribers as $subscriber_id ) {
						$status[] = PUM_DB_Subscribers::instance()->delete( $subscriber_id );
					}

					if ( ! in_array( false, $status ) ) {
						mcms_die( sprintf( _n( 'Subscriber deleted!', '%d Subscribers deleted!', count( $subscribers ), 'baloonup-maker' ), count( $subscribers ) ), __( 'Success', 'baloonup-maker' ), array(
							'response'  => 200,
							'back_link' => esc_url( admin_url( 'edit.php?page=pum-subscribers&post_type=baloonup' ) ),
						) );
					} else {
						$succeeded = count( array_filter( $status ) );
						$failed    = count( $subscribers ) - $succeeded;

						if ( count( $subscribers ) == 1 ) {
							mcms_die( __( 'Deleting subscriber failed.', 'baloonup-maker' ), __( 'Error', 'baloonup-maker' ), array(
								'response'  => 200,
								'back_link' => esc_url( admin_url( 'edit.php?page=pum-subscribers&post_type=baloonup' ) ),
							) );

						} else {
							mcms_die( sprintf( __( '%d Subscribers deleted, %d failed', 'baloonup-maker' ), $succeeded, $failed ), __( 'Error', 'baloonup-maker' ), array(
								'response'  => 200,
								'back_link' => esc_url( admin_url( 'edit.php?page=pum-subscribers&post_type=baloonup' ) ),
							) );
						}

					}
				}

				mcms_die( __( 'Uh oh, the subscribers was not deleted successfully!', 'baloonup-maker' ), __( 'Error', 'baloonup-maker' ), array(
					'response'  => 200,
					'back_link' => esc_url( admin_url( 'edit.php?page=pum-subscribers&post_type=baloonup' ) ),
				) );

				exit;
			}

		}

		/*
		 * Note: Table bulk_actions can be identified by checking $_REQUEST['action'] and $_REQUEST['action2']
		 *
		 * action - is set if checkbox from top-most select-all is set, otherwise returns -1
		 * action2 - is set if checkbox the bottom-most select-all checkbox is set, otherwise returns -1
		 */

		// check for individual row actions
		$the_table_action = $this->current_action();

		if ( 'view_usermeta' === $the_table_action ) {
			$nonce = mcms_unslash( $_REQUEST['_mcmsnonce'] );
			// verify the nonce.
			if ( ! mcms_verify_nonce( $nonce, 'view_usermeta_nonce' ) ) {
				$this->invalid_nonce_redirect();
			} else {
				$this->page_view_usermeta( absint( $_REQUEST['user_id'] ) );
				$this->graceful_exit();
			}
		}

		if ( 'add_usermeta' === $the_table_action ) {
			$nonce = mcms_unslash( $_REQUEST['_mcmsnonce'] );
			// verify the nonce.
			if ( ! mcms_verify_nonce( $nonce, 'add_usermeta_nonce' ) ) {
				$this->invalid_nonce_redirect();
			} else {
				$this->page_add_usermeta( absint( $_REQUEST['user_id'] ) );
				$this->graceful_exit();
			}
		}

		// check for table bulk actions
		if ( ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] === 'bulk-download' ) || ( isset( $_REQUEST['action2'] ) && $_REQUEST['action2'] === 'bulk-download' ) ) {

			$nonce = mcms_unslash( $_REQUEST['_mcmsnonce'] );
			// verify the nonce.
			/*
			 * Note: the nonce field is set by the parent class
			 * mcms_nonce_field( 'bulk-' . $this->_args['plural'] );
			 *
			 */
			if ( ! mcms_verify_nonce( $nonce, 'bulk-users' ) ) {
				$this->invalid_nonce_redirect();
			} else {
				$this->page_bulk_download( $_REQUEST['users'] );
				$this->graceful_exit();
			}
		}

	}

	/**
	 * Die when the nonce check fails.
	 */
	public function invalid_nonce_redirect() {
		mcms_die( __( 'Invalid Nonce', 'baloonup-maker' ), __( 'Error', 'baloonup-maker' ), array(
			'response'  => 403,
			'back_link' => esc_url( admin_url( 'edit.php?page=pum-subscribers&post_type=baloonup' ) ),
		) );
	}
}

