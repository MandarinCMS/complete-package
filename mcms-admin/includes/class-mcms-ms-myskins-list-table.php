<?php
/**
 * List Table API: MCMS_MS_MySkins_List_Table class
 *
 * @package MandarinCMS
 * @subpackage Administration
 * @since 3.1.0
 */

/**
 * Core class used to implement displaying myskins in a list table for the network admin.
 *
 * @since 3.1.0
 * @access private
 *
 * @see MCMS_List_Table
 */
class MCMS_MS_MySkins_List_Table extends MCMS_List_Table {

	public $site_id;
	public $is_site_myskins;

	private $has_items;

	/**
	 * Constructor.
	 *
	 * @since 3.1.0
	 *
	 * @see MCMS_List_Table::__construct() for more information on default arguments.
	 *
	 * @global string $status
	 * @global int    $page
	 *
	 * @param array $args An associative array of arguments.
	 */
	public function __construct( $args = array() ) {
		global $status, $page;

		parent::__construct( array(
			'plural' => 'myskins',
			'screen' => isset( $args['screen'] ) ? $args['screen'] : null,
		) );

		$status = isset( $_REQUEST['myskin_status'] ) ? $_REQUEST['myskin_status'] : 'all';
		if ( !in_array( $status, array( 'all', 'enabled', 'disabled', 'upgrade', 'search', 'broken' ) ) )
			$status = 'all';

		$page = $this->get_pagenum();

		$this->is_site_myskins = ( 'site-myskins-network' === $this->screen->id ) ? true : false;

		if ( $this->is_site_myskins )
			$this->site_id = isset( $_REQUEST['id'] ) ? intval( $_REQUEST['id'] ) : 0;
	}

	/**
	 *
	 * @return array
	 */
	protected function get_table_classes() {
		// todo: remove and add CSS for .myskins
		return array( 'widefat', 'modules' );
	}

	/**
	 *
	 * @return bool
	 */
	public function ajax_user_can() {
		if ( $this->is_site_myskins )
			return current_user_can( 'manage_sites' );
		else
			return current_user_can( 'manage_network_myskins' );
	}

	/**
	 *
	 * @global string $status
	 * @global array $totals
	 * @global int $page
	 * @global string $orderby
	 * @global string $order
	 * @global string $s
	 */
	public function prepare_items() {
		global $status, $totals, $page, $orderby, $order, $s;

		mcms_reset_vars( array( 'orderby', 'order', 's' ) );

		$myskins = array(
			/**
			 * Filters the full array of MCMS_MySkin objects to list in the Multisite
			 * myskins list table.
			 *
			 * @since 3.1.0
			 *
			 * @param array $all An array of MCMS_MySkin objects to display in the list table.
			 */
			'all' => apply_filters( 'all_myskins', mcms_get_myskins() ),
			'search' => array(),
			'enabled' => array(),
			'disabled' => array(),
			'upgrade' => array(),
			'broken' => $this->is_site_myskins ? array() : mcms_get_myskins( array( 'errors' => true ) ),
		);

		if ( $this->is_site_myskins ) {
			$myskins_per_page = $this->get_items_per_page( 'site_myskins_network_per_page' );
			$allowed_where = 'site';
		} else {
			$myskins_per_page = $this->get_items_per_page( 'myskins_network_per_page' );
			$allowed_where = 'network';
		}

		$maybe_update = current_user_can( 'update_myskins' ) && ! $this->is_site_myskins && $current = get_site_transient( 'update_myskins' );

		foreach ( (array) $myskins['all'] as $key => $myskin ) {
			if ( $this->is_site_myskins && $myskin->is_allowed( 'network' ) ) {
				unset( $myskins['all'][ $key ] );
				continue;
			}

			if ( $maybe_update && isset( $current->response[ $key ] ) ) {
				$myskins['all'][ $key ]->update = true;
				$myskins['upgrade'][ $key ] = $myskins['all'][ $key ];
			}

			$filter = $myskin->is_allowed( $allowed_where, $this->site_id ) ? 'enabled' : 'disabled';
			$myskins[ $filter ][ $key ] = $myskins['all'][ $key ];
		}

		if ( $s ) {
			$status = 'search';
			$myskins['search'] = array_filter( array_merge( $myskins['all'], $myskins['broken'] ), array( $this, '_search_callback' ) );
		}

		$totals = array();
		foreach ( $myskins as $type => $list )
			$totals[ $type ] = count( $list );

		if ( empty( $myskins[ $status ] ) && !in_array( $status, array( 'all', 'search' ) ) )
			$status = 'all';

		$this->items = $myskins[ $status ];
		MCMS_MySkin::sort_by_name( $this->items );

		$this->has_items = ! empty( $myskins['all'] );
		$total_this_page = $totals[ $status ];

		mcms_localize_script( 'updates', '_mcmsUpdatesItemCounts', array(
			'myskins' => $totals,
			'totals' => mcms_get_update_data(),
		) );

		if ( $orderby ) {
			$orderby = ucfirst( $orderby );
			$order = strtoupper( $order );

			if ( $orderby === 'Name' ) {
				if ( 'ASC' === $order ) {
					$this->items = array_reverse( $this->items );
				}
			} else {
				uasort( $this->items, array( $this, '_order_callback' ) );
			}
		}

		$start = ( $page - 1 ) * $myskins_per_page;

		if ( $total_this_page > $myskins_per_page )
			$this->items = array_slice( $this->items, $start, $myskins_per_page, true );

		$this->set_pagination_args( array(
			'total_items' => $total_this_page,
			'per_page' => $myskins_per_page,
		) );
	}

	/**
	 * @staticvar string $term
	 * @param MCMS_MySkin $myskin
	 * @return bool
	 */
	public function _search_callback( $myskin ) {
		static $term = null;
		if ( is_null( $term ) )
			$term = mcms_unslash( $_REQUEST['s'] );

		foreach ( array( 'Name', 'Description', 'Author', 'Author', 'AuthorURI' ) as $field ) {
			// Don't mark up; Do translate.
			if ( false !== stripos( $myskin->display( $field, false, true ), $term ) )
				return true;
		}

		if ( false !== stripos( $myskin->get_stylesheet(), $term ) )
			return true;

		if ( false !== stripos( $myskin->get_template(), $term ) )
			return true;

		return false;
	}

	// Not used by any core columns.
	/**
	 * @global string $orderby
	 * @global string $order
	 * @param array $myskin_a
	 * @param array $myskin_b
	 * @return int
	 */
	public function _order_callback( $myskin_a, $myskin_b ) {
		global $orderby, $order;

		$a = $myskin_a[ $orderby ];
		$b = $myskin_b[ $orderby ];

		if ( $a == $b )
			return 0;

		if ( 'DESC' === $order )
			return ( $a < $b ) ? 1 : -1;
		else
			return ( $a < $b ) ? -1 : 1;
	}

	/**
	 */
	public function no_items() {
		if ( $this->has_items ) {
			_e( 'No myskins found.' );
		} else {
			_e( 'You do not appear to have any myskins available at this time.' );
		}
	}

	/**
	 *
	 * @return array
	 */
	public function get_columns() {
		return array(
			'cb'          => '<input type="checkbox" />',
			'name'        => __( 'MySkin' ),
			'description' => __( 'Description' ),
		);
	}

	/**
	 *
	 * @return array
	 */
	protected function get_sortable_columns() {
		return array(
			'name'         => 'name',
		);
	}

	/**
	 * Gets the name of the primary column.
	 *
	 * @since 4.3.0
	 *
	 * @return string Unalterable name of the primary column name, in this case, 'name'.
	 */
	protected function get_primary_column_name() {
		return 'name';
	}

	/**
	 *
	 * @global array $totals
	 * @global string $status
	 * @return array
	 */
	protected function get_views() {
		global $totals, $status;

		$status_links = array();
		foreach ( $totals as $type => $count ) {
			if ( !$count )
				continue;

			switch ( $type ) {
				case 'all':
					$text = _nx( 'All <span class="count">(%s)</span>', 'All <span class="count">(%s)</span>', $count, 'myskins' );
					break;
				case 'enabled':
					$text = _n( 'Enabled <span class="count">(%s)</span>', 'Enabled <span class="count">(%s)</span>', $count );
					break;
				case 'disabled':
					$text = _n( 'Disabled <span class="count">(%s)</span>', 'Disabled <span class="count">(%s)</span>', $count );
					break;
				case 'upgrade':
					$text = _n( 'Update Available <span class="count">(%s)</span>', 'Update Available <span class="count">(%s)</span>', $count );
					break;
				case 'broken' :
					$text = _n( 'Broken <span class="count">(%s)</span>', 'Broken <span class="count">(%s)</span>', $count );
					break;
			}

			if ( $this->is_site_myskins )
				$url = 'site-myskins.php?id=' . $this->site_id;
			else
				$url = 'myskins.php';

			if ( 'search' != $type ) {
				$status_links[$type] = sprintf( "<a href='%s'%s>%s</a>",
					esc_url( add_query_arg('myskin_status', $type, $url) ),
					( $type === $status ) ? ' class="current" aria-current="page"' : '',
					sprintf( $text, number_format_i18n( $count ) )
				);
			}
		}

		return $status_links;
	}

	/**
	 * @global string $status
	 *
	 * @return array
	 */
	protected function get_bulk_actions() {
		global $status;

		$actions = array();
		if ( 'enabled' != $status )
			$actions['enable-selected'] = $this->is_site_myskins ? __( 'Enable' ) : __( 'Network Enable' );
		if ( 'disabled' != $status )
			$actions['disable-selected'] = $this->is_site_myskins ? __( 'Disable' ) : __( 'Network Disable' );
		if ( ! $this->is_site_myskins ) {
			if ( current_user_can( 'update_myskins' ) )
				$actions['update-selected'] = __( 'Update' );
			if ( current_user_can( 'delete_myskins' ) )
				$actions['delete-selected'] = __( 'Delete' );
		}
		return $actions;
	}

	/**
	 */
	public function display_rows() {
		foreach ( $this->items as $myskin )
			$this->single_row( $myskin );
	}

	/**
	 * Handles the checkbox column output.
	 *
	 * @since 4.3.0
	 *
	 * @param MCMS_MySkin $myskin The current MCMS_MySkin object.
	 */
	public function column_cb( $myskin ) {
		$checkbox_id = 'checkbox_' . md5( $myskin->get('Name') );
		?>
		<input type="checkbox" name="checked[]" value="<?php echo esc_attr( $myskin->get_stylesheet() ) ?>" id="<?php echo $checkbox_id ?>" />
		<label class="screen-reader-text" for="<?php echo $checkbox_id ?>" ><?php _e( 'Select' ) ?>  <?php echo $myskin->display( 'Name' ) ?></label>
		<?php
	}

	/**
	 * Handles the name column output.
	 *
	 * @since 4.3.0
	 *
	 * @global string $status
	 * @global int    $page
	 * @global string $s
	 *
	 * @param MCMS_MySkin $myskin The current MCMS_MySkin object.
	 */
	public function column_name( $myskin ) {
		global $status, $page, $s;

		$context = $status;

		if ( $this->is_site_myskins ) {
			$url = "site-myskins.php?id={$this->site_id}&amp;";
			$allowed = $myskin->is_allowed( 'site', $this->site_id );
		} else {
			$url = 'myskins.php?';
			$allowed = $myskin->is_allowed( 'network' );
		}

		// Pre-order.
		$actions = array(
			'enable' => '',
			'disable' => '',
			'delete' => ''
		);

		$stylesheet = $myskin->get_stylesheet();
		$myskin_key = urlencode( $stylesheet );

		if ( ! $allowed ) {
			if ( ! $myskin->errors() ) {
				$url = add_query_arg( array(
					'action' => 'enable',
					'myskin'  => $myskin_key,
					'paged'  => $page,
					's'      => $s,
				), $url );

				if ( $this->is_site_myskins ) {
					/* translators: %s: myskin name */
					$aria_label = sprintf( __( 'Enable %s' ), $myskin->display( 'Name' ) );
				} else {
					/* translators: %s: myskin name */
					$aria_label = sprintf( __( 'Network Enable %s' ), $myskin->display( 'Name' ) );
				}

				$actions['enable'] = sprintf( '<a href="%s" class="edit" aria-label="%s">%s</a>',
					esc_url( mcms_nonce_url( $url, 'enable-myskin_' . $stylesheet ) ),
					esc_attr( $aria_label ),
					( $this->is_site_myskins ? __( 'Enable' ) : __( 'Network Enable' ) )
				);
			}
		} else {
			$url = add_query_arg( array(
				'action' => 'disable',
				'myskin'  => $myskin_key,
				'paged'  => $page,
				's'      => $s,
			), $url );

			if ( $this->is_site_myskins ) {
				/* translators: %s: myskin name */
				$aria_label = sprintf( __( 'Disable %s' ), $myskin->display( 'Name' ) );
			} else {
				/* translators: %s: myskin name */
				$aria_label = sprintf( __( 'Network Disable %s' ), $myskin->display( 'Name' ) );
			}

			$actions['disable'] = sprintf( '<a href="%s" aria-label="%s">%s</a>',
				esc_url( mcms_nonce_url( $url, 'disable-myskin_' . $stylesheet ) ),
				esc_attr( $aria_label ),
				( $this->is_site_myskins ? __( 'Disable' ) : __( 'Network Disable' ) )
			);
		}

		if ( ! $allowed && current_user_can( 'delete_myskins' ) && ! $this->is_site_myskins && $stylesheet != get_option( 'stylesheet' ) && $stylesheet != get_option( 'template' ) ) {
			$url = add_query_arg( array(
				'action'       => 'delete-selected',
				'checked[]'    => $myskin_key,
				'myskin_status' => $context,
				'paged'        => $page,
				's'            => $s,
			), 'myskins.php' );

			/* translators: %s: myskin name */
			$aria_label = sprintf( _x( 'Delete %s', 'myskin' ), $myskin->display( 'Name' ) );

			$actions['delete'] = sprintf( '<a href="%s" class="delete" aria-label="%s">%s</a>',
				esc_url( mcms_nonce_url( $url, 'bulk-myskins' ) ),
				esc_attr( $aria_label ),
				__( 'Delete' )
			);
		}
		/**
		 * Filters the action links displayed for each myskin in the Multisite
		 * myskins list table.
		 *
		 * The action links displayed are determined by the myskin's status, and
		 * which Multisite myskins list table is being displayed - the Network
		 * myskins list table (myskins.php), which displays all installed myskins,
		 * or the Site myskins list table (site-myskins.php), which displays the
		 * non-network enabled myskins when editing a site in the Network admin.
		 *
		 * The default action links for the Network myskins list table include
		 * 'Network Enable', 'Network Disable', and 'Delete'.
		 *
		 * The default action links for the Site myskins list table include
		 * 'Enable', and 'Disable'.
		 *
		 * @since 2.8.0
		 *
		 * @param array    $actions An array of action links.
		 * @param MCMS_MySkin $myskin   The current MCMS_MySkin object.
		 * @param string   $context Status of the myskin, one of 'all', 'enabled', or 'disabled'.
		 */
		$actions = apply_filters( 'myskin_action_links', array_filter( $actions ), $myskin, $context );

		/**
		 * Filters the action links of a specific myskin in the Multisite myskins
		 * list table.
		 *
		 * The dynamic portion of the hook name, `$stylesheet`, refers to the
		 * directory name of the myskin, which in most cases is synonymous
		 * with the template name.
		 *
		 * @since 3.1.0
		 *
		 * @param array    $actions An array of action links.
		 * @param MCMS_MySkin $myskin   The current MCMS_MySkin object.
		 * @param string   $context Status of the myskin, one of 'all', 'enabled', or 'disabled'.
		 */
		$actions = apply_filters( "myskin_action_links_{$stylesheet}", $actions, $myskin, $context );

		echo $this->row_actions( $actions, true );
	}

	/**
	 * Handles the description column output.
	 *
	 * @since 4.3.0
	 *
	 * @global string $status
	 * @global array  $totals
	 *
	 * @param MCMS_MySkin $myskin The current MCMS_MySkin object.
	 */
	public function column_description( $myskin ) {
		global $status, $totals;
		if ( $myskin->errors() ) {
			$pre = $status === 'broken' ? __( 'Broken MySkin:' ) . ' ' : '';
			echo '<p><strong class="error-message">' . $pre . $myskin->errors()->get_error_message() . '</strong></p>';
		}

		if ( $this->is_site_myskins ) {
			$allowed = $myskin->is_allowed( 'site', $this->site_id );
		} else {
			$allowed = $myskin->is_allowed( 'network' );
		}

		$class = ! $allowed ? 'inactive' : 'active';
		if ( ! empty( $totals['upgrade'] ) && ! empty( $myskin->update ) )
			$class .= ' update';

		echo "<div class='myskin-description'><p>" . $myskin->display( 'Description' ) . "</p></div>
			<div class='$class second myskin-version-author-uri'>";

		$stylesheet = $myskin->get_stylesheet();
		$myskin_meta = array();

		if ( $myskin->get('Version') ) {
			$myskin_meta[] = sprintf( __( 'Version %s' ), $myskin->display('Version') );
		}
		$myskin_meta[] = sprintf( __( 'By %s' ), $myskin->display('Author') );

		if ( $myskin->get('MySkinURI') ) {
			/* translators: %s: myskin name */
			$aria_label = sprintf( __( 'Visit %s homepage' ), $myskin->display( 'Name' ) );

			$myskin_meta[] = sprintf( '<a href="%s" aria-label="%s">%s</a>',
				$myskin->display( 'MySkinURI' ),
				esc_attr( $aria_label ),
				__( 'Visit MySkin Site' )
			);
		}
		/**
		 * Filters the array of row meta for each myskin in the Multisite myskins
		 * list table.
		 *
		 * @since 3.1.0
		 *
		 * @param array    $myskin_meta An array of the myskin's metadata,
		 *                             including the version, author, and
		 *                             myskin URI.
		 * @param string   $stylesheet Directory name of the myskin.
		 * @param MCMS_MySkin $myskin      MCMS_MySkin object.
		 * @param string   $status     Status of the myskin.
		 */
		$myskin_meta = apply_filters( 'myskin_row_meta', $myskin_meta, $stylesheet, $myskin, $status );
		echo implode( ' | ', $myskin_meta );

		echo '</div>';
	}

	/**
	 * Handles default column output.
	 *
	 * @since 4.3.0
	 *
	 * @param MCMS_MySkin $myskin       The current MCMS_MySkin object.
	 * @param string   $column_name The current column name.
	 */
	public function column_default( $myskin, $column_name ) {
		$stylesheet = $myskin->get_stylesheet();

		/**
		 * Fires inside each custom column of the Multisite myskins list table.
		 *
		 * @since 3.1.0
		 *
		 * @param string   $column_name Name of the column.
		 * @param string   $stylesheet  Directory name of the myskin.
		 * @param MCMS_MySkin $myskin       Current MCMS_MySkin object.
		 */
		do_action( 'manage_myskins_custom_column', $column_name, $stylesheet, $myskin );
	}

	/**
	 * Handles the output for a single table row.
	 *
	 * @since 4.3.0
	 *
	 * @param MCMS_MySkin $item The current MCMS_MySkin object.
	 */
	public function single_row_columns( $item ) {
		list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();

		foreach ( $columns as $column_name => $column_display_name ) {
			$extra_classes = '';
			if ( in_array( $column_name, $hidden ) ) {
				$extra_classes .= ' hidden';
			}

			switch ( $column_name ) {
				case 'cb':
					echo '<th scope="row" class="check-column">';

					$this->column_cb( $item );

					echo '</th>';
					break;

				case 'name':

					$active_myskin_label = '';

					/* The presence of the site_id property means that this is a subsite view and a label for the active myskin needs to be added */
					if ( ! empty( $this->site_id ) ) {
						$stylesheet = get_blog_option( $this->site_id, 'stylesheet' );
						$template   = get_blog_option( $this->site_id, 'template' );

						/* Add a label for the active template */
						if ( $item->get_template() === $template ) {
							$active_myskin_label = ' &mdash; ' . __( 'Active MySkin' );
						}

						/* In case this is a child myskin, label it properly */
						if ( $stylesheet !== $template && $item->get_stylesheet() === $stylesheet) {
							$active_myskin_label = ' &mdash; ' . __( 'Active Child MySkin' );
						}
					}

					echo "<td class='myskin-title column-primary{$extra_classes}'><strong>" . $item->display( 'Name' ) . $active_myskin_label . '</strong>';

					$this->column_name( $item );

					echo "</td>";
					break;

				case 'description':
					echo "<td class='column-description desc{$extra_classes}'>";

					$this->column_description( $item );

					echo '</td>';
					break;

				default:
					echo "<td class='$column_name column-$column_name{$extra_classes}'>";

					$this->column_default( $item, $column_name );

					echo "</td>";
					break;
			}
		}
	}

	/**
	 * @global string $status
	 * @global array  $totals
	 *
	 * @param MCMS_MySkin $myskin
	 */
	public function single_row( $myskin ) {
		global $status, $totals;

		if ( $this->is_site_myskins ) {
			$allowed = $myskin->is_allowed( 'site', $this->site_id );
		} else {
			$allowed = $myskin->is_allowed( 'network' );
		}

		$stylesheet = $myskin->get_stylesheet();

		$class = ! $allowed ? 'inactive' : 'active';
		if ( ! empty( $totals['upgrade'] ) && ! empty( $myskin->update ) ) {
			$class .= ' update';
		}

		printf( '<tr class="%s" data-slug="%s">',
			esc_attr( $class ),
			esc_attr( $stylesheet )
		);

		$this->single_row_columns( $myskin );

		echo "</tr>";

		if ( $this->is_site_myskins )
			remove_action( "after_myskin_row_$stylesheet", 'mcms_myskin_update_row' );

		/**
		 * Fires after each row in the Multisite myskins list table.
		 *
		 * @since 3.1.0
		 *
		 * @param string   $stylesheet Directory name of the myskin.
		 * @param MCMS_MySkin $myskin      Current MCMS_MySkin object.
		 * @param string   $status     Status of the myskin.
		 */
		do_action( 'after_myskin_row', $stylesheet, $myskin, $status );

		/**
		 * Fires after each specific row in the Multisite myskins list table.
		 *
		 * The dynamic portion of the hook name, `$stylesheet`, refers to the
		 * directory name of the myskin, most often synonymous with the template
		 * name of the myskin.
		 *
		 * @since 3.5.0
		 *
		 * @param string   $stylesheet Directory name of the myskin.
		 * @param MCMS_MySkin $myskin      Current MCMS_MySkin object.
		 * @param string   $status     Status of the myskin.
		 */
		do_action( "after_myskin_row_{$stylesheet}", $stylesheet, $myskin, $status );
	}
}
