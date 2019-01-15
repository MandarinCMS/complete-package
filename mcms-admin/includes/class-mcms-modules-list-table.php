<?php
/**
 * List Table API: MCMS_Modules_List_Table class
 *
 * @package MandarinCMS
 * @subpackage Administration
 * @since 3.1.0
 */

/**
 * Core class used to implement displaying installed modules in a list table.
 *
 * @since 3.1.0
 * @access private
 *
 * @see MCMS_List_Table
 */
class MCMS_Modules_List_Table extends MCMS_List_Table {

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
			'plural' => 'modules',
			'screen' => isset( $args['screen'] ) ? $args['screen'] : null,
		) );

		$status = 'all';
		if ( isset( $_REQUEST['module_status'] ) && in_array( $_REQUEST['module_status'], array( 'active', 'inactive', 'recently_activated', 'upgrade', 'mustuse', 'dropins', 'search' ) ) )
			$status = $_REQUEST['module_status'];

		if ( isset($_REQUEST['s']) )
			$_SERVER['REQUEST_URI'] = add_query_arg('s', mcms_unslash($_REQUEST['s']) );

		$page = $this->get_pagenum();
	}

	/**
	 * @return array
	 */
	protected function get_table_classes() {
		return array( 'widefat', $this->_args['plural'] );
	}

	/**
	 * @return bool
	 */
	public function ajax_user_can() {
		return current_user_can('activate_modules');
	}

	/**
	 *
	 * @global string $status
	 * @global array  $modules
	 * @global array  $totals
	 * @global int    $page
	 * @global string $orderby
	 * @global string $order
	 * @global string $s
	 */
	public function prepare_items() {
		global $status, $modules, $totals, $page, $orderby, $order, $s;

		mcms_reset_vars( array( 'orderby', 'order' ) );

		/**
		 * Filters the full array of modules to list in the Modules list table.
		 *
		 * @since 3.0.0
		 *
		 * @see get_modules()
		 *
		 * @param array $all_modules An array of modules to display in the list table.
		 */
		$all_modules = apply_filters( 'all_modules', get_modules() );

		$modules = array(
			'all'                => $all_modules,
			'search'             => array(),
			'active'             => array(),
			'inactive'           => array(),
			'recently_activated' => array(),
			'upgrade'            => array(),
			'mustuse'            => array(),
			'dropins'            => array(),
		);

		$screen = $this->screen;

		if ( ! is_multisite() || ( $screen->in_admin( 'network' ) && current_user_can( 'manage_network_modules' ) ) ) {

			/**
			 * Filters whether to display the advanced modules list table.
			 *
			 * There are two types of advanced modules - must-use and drop-ins -
			 * which can be used in a single site or Multisite network.
			 *
			 * The $type parameter allows you to differentiate between the type of advanced
			 * modules to filter the display of. Contexts include 'mustuse' and 'dropins'.
			 *
			 * @since 3.0.0
			 *
			 * @param bool   $show Whether to show the advanced modules for the specified
			 *                     module type. Default true.
			 * @param string $type The module type. Accepts 'mustuse', 'dropins'.
			 */
			if ( apply_filters( 'show_advanced_modules', true, 'mustuse' ) ) {
				$modules['mustuse'] = get_mu_modules();
			}

			/** This action is documented in mcms-admin/includes/class-mcms-modules-list-table.php */
			if ( apply_filters( 'show_advanced_modules', true, 'dropins' ) )
				$modules['dropins'] = get_dropins();

			if ( current_user_can( 'update_modules' ) ) {
				$current = get_site_transient( 'update_modules' );
				foreach ( (array) $modules['all'] as $module_file => $module_data ) {
					if ( isset( $current->response[ $module_file ] ) ) {
						$modules['all'][ $module_file ]['update'] = true;
						$modules['upgrade'][ $module_file ] = $modules['all'][ $module_file ];
					}
				}
			}
		}

		if ( ! $screen->in_admin( 'network' ) ) {
			$show = current_user_can( 'manage_network_modules' );
			/**
			 * Filters whether to display network-active modules alongside modules active for the current site.
			 *
			 * This also controls the display of inactive network-only modules (modules with
			 * "Network: true" in the module header).
			 *
			 * Modules cannot be network-activated or network-deactivated from this screen.
			 *
			 * @since 4.4.0
			 *
			 * @param bool $show Whether to show network-active modules. Default is whether the current
			 *                   user can manage network modules (ie. a Super Admin).
			 */
			$show_network_active = apply_filters( 'show_network_active_modules', $show );
		}

		set_transient( 'module_slugs', array_keys( $modules['all'] ), DAY_IN_SECONDS );

		if ( $screen->in_admin( 'network' ) ) {
			$recently_activated = get_site_option( 'recently_activated', array() );
		} else {
			$recently_activated = get_option( 'recently_activated', array() );
		}

		foreach ( $recently_activated as $key => $time ) {
			if ( $time + WEEK_IN_SECONDS < time() ) {
				unset( $recently_activated[$key] );
			}
		}

		if ( $screen->in_admin( 'network' ) ) {
			update_site_option( 'recently_activated', $recently_activated );
		} else {
			update_option( 'recently_activated', $recently_activated );
		}

		$module_info = get_site_transient( 'update_modules' );

		foreach ( (array) $modules['all'] as $module_file => $module_data ) {
			// Extra info if known. array_merge() ensures $module_data has precedence if keys collide.
			if ( isset( $module_info->response[ $module_file ] ) ) {
				$modules['all'][ $module_file ] = $module_data = array_merge( (array) $module_info->response[ $module_file ], $module_data );
				// Make sure that $modules['upgrade'] also receives the extra info since it is used on ?module_status=upgrade
				if ( isset( $modules['upgrade'][ $module_file ] ) ) {
					$modules['upgrade'][ $module_file ] = $module_data = array_merge( (array) $module_info->response[ $module_file ], $module_data );
				}

			} elseif ( isset( $module_info->no_update[ $module_file ] ) ) {
				$modules['all'][ $module_file ] = $module_data = array_merge( (array) $module_info->no_update[ $module_file ], $module_data );
				// Make sure that $modules['upgrade'] also receives the extra info since it is used on ?module_status=upgrade
				if ( isset( $modules['upgrade'][ $module_file ] ) ) {
					$modules['upgrade'][ $module_file ] = $module_data = array_merge( (array) $module_info->no_update[ $module_file ], $module_data );
				}
			}

			// Filter into individual sections
			if ( is_multisite() && ! $screen->in_admin( 'network' ) && is_network_only_module( $module_file ) && ! is_module_active( $module_file ) ) {
				if ( $show_network_active ) {
					// On the non-network screen, show inactive network-only modules if allowed
					$modules['inactive'][ $module_file ] = $module_data;
				} else {
					// On the non-network screen, filter out network-only modules as long as they're not individually active
					unset( $modules['all'][ $module_file ] );
				}
			} elseif ( ! $screen->in_admin( 'network' ) && is_module_active_for_network( $module_file ) ) {
				if ( $show_network_active ) {
					// On the non-network screen, show network-active modules if allowed
					$modules['active'][ $module_file ] = $module_data;
				} else {
					// On the non-network screen, filter out network-active modules
					unset( $modules['all'][ $module_file ] );
				}
			} elseif ( ( ! $screen->in_admin( 'network' ) && is_module_active( $module_file ) )
				|| ( $screen->in_admin( 'network' ) && is_module_active_for_network( $module_file ) ) ) {
				// On the non-network screen, populate the active list with modules that are individually activated
				// On the network-admin screen, populate the active list with modules that are network activated
				$modules['active'][ $module_file ] = $module_data;
			} else {
				if ( isset( $recently_activated[ $module_file ] ) ) {
					// Populate the recently activated list with modules that have been recently activated
					$modules['recently_activated'][ $module_file ] = $module_data;
				}
				// Populate the inactive list with modules that aren't activated
				$modules['inactive'][ $module_file ] = $module_data;
			}
		}

		if ( strlen( $s ) ) {
			$status = 'search';
			$modules['search'] = array_filter( $modules['all'], array( $this, '_search_callback' ) );
		}

		$totals = array();
		foreach ( $modules as $type => $list )
			$totals[ $type ] = count( $list );

		if ( empty( $modules[ $status ] ) && !in_array( $status, array( 'all', 'search' ) ) )
			$status = 'all';

		$this->items = array();
		foreach ( $modules[ $status ] as $module_file => $module_data ) {
			// Translate, Don't Apply Markup, Sanitize HTML
			$this->items[$module_file] = _get_module_data_markup_translate( $module_file, $module_data, false, true );
		}

		$total_this_page = $totals[ $status ];

		$js_modules = array();
		foreach ( $modules as $key => $list ) {
			$js_modules[ $key ] = array_keys( (array) $list );
		}

		mcms_localize_script( 'updates', '_mcmsUpdatesItemCounts', array(
			'modules' => $js_modules,
			'totals'  => mcms_get_update_data(),
		) );

		if ( ! $orderby ) {
			$orderby = 'Name';
		} else {
			$orderby = ucfirst( $orderby );
		}

		$order = strtoupper( $order );

		uasort( $this->items, array( $this, '_order_callback' ) );

		$modules_per_page = $this->get_items_per_page( str_replace( '-', '_', $screen->id . '_per_page' ), 999 );

		$start = ( $page - 1 ) * $modules_per_page;

		if ( $total_this_page > $modules_per_page )
			$this->items = array_slice( $this->items, $start, $modules_per_page );

		$this->set_pagination_args( array(
			'total_items' => $total_this_page,
			'per_page' => $modules_per_page,
		) );
	}

	/**
	 * @global string $s URL encoded search term.
	 *
	 * @param array $module
	 * @return bool
	 */
	public function _search_callback( $module ) {
		global $s;

		foreach ( $module as $value ) {
			if ( is_string( $value ) && false !== stripos( strip_tags( $value ), urldecode( $s ) ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @global string $orderby
	 * @global string $order
	 * @param array $module_a
	 * @param array $module_b
	 * @return int
	 */
	public function _order_callback( $module_a, $module_b ) {
		global $orderby, $order;

		$a = $module_a[$orderby];
		$b = $module_b[$orderby];

		if ( $a == $b )
			return 0;

		if ( 'DESC' === $order ) {
			return strcasecmp( $b, $a );
		} else {
			return strcasecmp( $a, $b );
		}
	}

	/**
	 *
	 * @global array $modules
	 */
	public function no_items() {
		global $modules;

		if ( ! empty( $_REQUEST['s'] ) ) {
			$s = esc_html( mcms_unslash( $_REQUEST['s'] ) );

			printf( __( 'No modules found for &#8220;%s&#8221;.' ), $s );

			// We assume that somebody who can install modules in multisite is experienced enough to not need this helper link.
			if ( ! is_multisite() && current_user_can( 'install_modules' ) ) {
				echo ' <a href="' . esc_url( admin_url( 'module-install.php?tab=search&s=' . urlencode( $s ) ) ) . '">' . __( 'Search for modules in the MandarinCMS Module Directory.' ) . '</a>';
			}
		} elseif ( ! empty( $modules['all'] ) )
			_e( 'No modules found.' );
		else
			_e( 'You do not appear to have any modules available at this time.' );
	}

	/**
	 * Displays the search box.
	 *
	 * @since 4.6.0
	 *
	 * @param string $text     The 'submit' button label.
	 * @param string $input_id ID attribute value for the search input field.
	 */
	public function search_box( $text, $input_id ) {
		if ( empty( $_REQUEST['s'] ) && ! $this->has_items() ) {
			return;
		}

		$input_id = $input_id . '-search-input';

		if ( ! empty( $_REQUEST['orderby'] ) ) {
			echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
		}
		if ( ! empty( $_REQUEST['order'] ) ) {
			echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
		}
		?>
		<p class="search-box">
			<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo $text; ?>:</label>
			<input type="search" id="<?php echo esc_attr( $input_id ); ?>" class="mcms-filter-search" name="s" value="<?php _admin_search_query(); ?>" placeholder="<?php esc_attr_e( 'Search installed modules...' ); ?>"/>
			<?php submit_button( $text, 'hide-if-js', '', false, array( 'id' => 'search-submit' ) ); ?>
		</p>
		<?php
	}

	/**
	 *
	 * @global string $status
	 * @return array
	 */
	public function get_columns() {
		global $status;

		return array(
			'cb'          => !in_array( $status, array( 'mustuse', 'dropins' ) ) ? '<input type="checkbox" />' : '',
			'name'        => __( 'Module' ),
			'description' => __( 'Description' ),
		);
	}

	/**
	 * @return array
	 */
	protected function get_sortable_columns() {
		return array();
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
					$text = _nx( 'All <span class="count">(%s)</span>', 'All <span class="count">(%s)</span>', $count, 'modules' );
					break;
				case 'active':
					$text = _n( 'Active <span class="count">(%s)</span>', 'Active <span class="count">(%s)</span>', $count );
					break;
				case 'recently_activated':
					$text = _n( 'Recently Active <span class="count">(%s)</span>', 'Recently Active <span class="count">(%s)</span>', $count );
					break;
				case 'inactive':
					$text = _n( 'Inactive <span class="count">(%s)</span>', 'Inactive <span class="count">(%s)</span>', $count );
					break;
				case 'mustuse':
					$text = _n( 'Must-Use <span class="count">(%s)</span>', 'Must-Use <span class="count">(%s)</span>', $count );
					break;
				case 'dropins':
					$text = _n( 'Drop-ins <span class="count">(%s)</span>', 'Drop-ins <span class="count">(%s)</span>', $count );
					break;
				case 'upgrade':
					$text = _n( 'Update Available <span class="count">(%s)</span>', 'Update Available <span class="count">(%s)</span>', $count );
					break;
			}

			if ( 'search' !== $type ) {
				$status_links[$type] = sprintf( "<a href='%s'%s>%s</a>",
					add_query_arg('module_status', $type, 'modules.php'),
					( $type === $status ) ? ' class="current" aria-current="page"' : '',
					sprintf( $text, number_format_i18n( $count ) )
					);
			}
		}

		return $status_links;
	}

	/**
	 *
	 * @global string $status
	 * @return array
	 */
	protected function get_bulk_actions() {
		global $status;

		$actions = array();

		if ( 'active' != $status )
			$actions['activate-selected'] = $this->screen->in_admin( 'network' ) ? __( 'Network Activate' ) : __( 'Activate' );

		if ( 'inactive' != $status && 'recent' != $status )
			$actions['deactivate-selected'] = $this->screen->in_admin( 'network' ) ? __( 'Network Deactivate' ) : __( 'Deactivate' );

		if ( !is_multisite() || $this->screen->in_admin( 'network' ) ) {
			if ( current_user_can( 'update_modules' ) )
				$actions['update-selected'] = __( 'Update' );
			if ( current_user_can( 'delete_modules' ) && ( 'active' != $status ) )
				$actions['delete-selected'] = __( 'Delete' );
		}

		return $actions;
	}

	/**
	 * @global string $status
	 * @param string $which
	 */
	public function bulk_actions( $which = '' ) {
		global $status;

		if ( in_array( $status, array( 'mustuse', 'dropins' ) ) )
			return;

		parent::bulk_actions( $which );
	}

	/**
	 * @global string $status
	 * @param string $which
	 */
	protected function extra_tablenav( $which ) {
		global $status;

		if ( ! in_array($status, array('recently_activated', 'mustuse', 'dropins') ) )
			return;

		echo '<div class="alignleft actions">';

		if ( 'recently_activated' == $status ) {
			submit_button( __( 'Clear List' ), '', 'clear-recent-list', false );
		} elseif ( 'top' === $which && 'mustuse' === $status ) {
			/* translators: %s: mu-modules directory name */
			echo '<p>' . sprintf( __( 'Files in the %s directory are executed automatically.' ),
				'<code>' . str_replace( BASED_TREE_URI, '/', MCMSMU_PLUGIN_DIR ) . '</code>'
			) . '</p>';
		} elseif ( 'top' === $which && 'dropins' === $status ) {
			/* translators: %s: mcms-plugins directory name */
			echo '<p>' . sprintf( __( 'Drop-ins are advanced modules in the %s directory that replace MandarinCMS functionality when present.' ),
				'<code>' . str_replace( BASED_TREE_URI, '', MCMS_CONTENT_DIR ) . '</code>'
			) . '</p>';
		}
		echo '</div>';
	}

	/**
	 * @return string
	 */
	public function current_action() {
		if ( isset($_POST['clear-recent-list']) )
			return 'clear-recent-list';

		return parent::current_action();
	}

	/**
	 *
	 * @global string $status
	 */
	public function display_rows() {
		global $status;

		if ( is_multisite() && ! $this->screen->in_admin( 'network' ) && in_array( $status, array( 'mustuse', 'dropins' ) ) )
			return;

		foreach ( $this->items as $module_file => $module_data )
			$this->single_row( array( $module_file, $module_data ) );
	}

	/**
	 * @global string $status
	 * @global int $page
	 * @global string $s
	 * @global array $totals
	 *
	 * @param array $item
	 */
	public function single_row( $item ) {
		global $status, $page, $s, $totals;

		list( $module_file, $module_data ) = $item;
		$context = $status;
		$screen = $this->screen;

		// Pre-order.
		$actions = array(
			'deactivate' => '',
			'activate' => '',
			'details' => '',
			'delete' => '',
		);

		// Do not restrict by default
		$restrict_network_active = false;
		$restrict_network_only = false;

		if ( 'mustuse' === $context ) {
			$is_active = true;
		} elseif ( 'dropins' === $context ) {
			$dropins = _get_dropins();
			$module_name = $module_file;
			if ( $module_file != $module_data['Name'] )
				$module_name .= '<br/>' . $module_data['Name'];
			if ( true === ( $dropins[ $module_file ][1] ) ) { // Doesn't require a constant
				$is_active = true;
				$description = '<p><strong>' . $dropins[ $module_file ][0] . '</strong></p>';
			} elseif ( defined( $dropins[ $module_file ][1] ) && constant( $dropins[ $module_file ][1] ) ) { // Constant is true
				$is_active = true;
				$description = '<p><strong>' . $dropins[ $module_file ][0] . '</strong></p>';
			} else {
				$is_active = false;
				$description = '<p><strong>' . $dropins[ $module_file ][0] . ' <span class="error-message">' . __( 'Inactive:' ) . '</span></strong> ' .
					/* translators: 1: drop-in constant name, 2: database-settings.php */
					sprintf( __( 'Requires %1$s in %2$s file.' ),
						"<code>define('" . $dropins[ $module_file ][1] . "', true);</code>",
						'<code>database-settings.php</code>'
					) . '</p>';
			}
			if ( $module_data['Description'] )
				$description .= '<p>' . $module_data['Description'] . '</p>';
		} else {
			if ( $screen->in_admin( 'network' ) ) {
				$is_active = is_module_active_for_network( $module_file );
			} else {
				$is_active = is_module_active( $module_file );
				$restrict_network_active = ( is_multisite() && is_module_active_for_network( $module_file ) );
				$restrict_network_only = ( is_multisite() && is_network_only_module( $module_file ) && ! $is_active );
			}

			if ( $screen->in_admin( 'network' ) ) {
				if ( $is_active ) {
					if ( current_user_can( 'manage_network_modules' ) ) {
						/* translators: %s: module name */
						$actions['deactivate'] = '<a href="' . mcms_nonce_url( 'modules.php?action=deactivate&amp;module=' . urlencode( $module_file ) . '&amp;module_status=' . $context . '&amp;paged=' . $page . '&amp;s=' . $s, 'deactivate-module_' . $module_file ) . '" aria-label="' . esc_attr( sprintf( _x( 'Network Deactivate %s', 'module' ), $module_data['Name'] ) ) . '">' . __( 'Network Deactivate' ) . '</a>';
						}
				} else {
					if ( current_user_can( 'manage_network_modules' ) ) {
						/* translators: %s: module name */
						$actions['activate'] = '<a href="' . mcms_nonce_url( 'modules.php?action=activate&amp;module=' . urlencode( $module_file ) . '&amp;module_status=' . $context . '&amp;paged=' . $page . '&amp;s=' . $s, 'activate-module_' . $module_file ) . '" class="edit" aria-label="' . esc_attr( sprintf( _x( 'Network Activate %s', 'module' ), $module_data['Name'] ) ) . '">' . __( 'Network Activate' ) . '</a>';
					}
					if ( current_user_can( 'delete_modules' ) && ! is_module_active( $module_file ) ) {
						/* translators: %s: module name */
						$actions['delete'] = '<a href="' . mcms_nonce_url( 'modules.php?action=delete-selected&amp;checked[]=' . urlencode( $module_file ) . '&amp;module_status=' . $context . '&amp;paged=' . $page . '&amp;s=' . $s, 'bulk-modules' ) . '" class="delete" aria-label="' . esc_attr( sprintf( _x( 'Delete %s', 'module' ), $module_data['Name'] ) ) . '">' . __( 'Delete' ) . '</a>';
					}
				}
			} else {
				if ( $restrict_network_active ) {
					$actions = array(
						'network_active' => __( 'Network Active' ),
					);
				} elseif ( $restrict_network_only ) {
					$actions = array(
						'network_only' => __( 'Network Only' ),
					);
				} elseif ( $is_active ) {
					if ( current_user_can( 'deactivate_module', $module_file ) ) {
						/* translators: %s: module name */
						$actions['deactivate'] = '<a href="' . mcms_nonce_url( 'modules.php?action=deactivate&amp;module=' . urlencode( $module_file ) . '&amp;module_status=' . $context . '&amp;paged=' . $page . '&amp;s=' . $s, 'deactivate-module_' . $module_file ) . '" aria-label="' . esc_attr( sprintf( _x( 'Deactivate %s', 'module' ), $module_data['Name'] ) ) . '">' . __( 'Deactivate' ) . '</a>';
					}
				} else {
					if ( current_user_can( 'activate_module', $module_file ) ) {
						/* translators: %s: module name */
						$actions['activate'] = '<a href="' . mcms_nonce_url( 'modules.php?action=activate&amp;module=' . urlencode( $module_file ) . '&amp;module_status=' . $context . '&amp;paged=' . $page . '&amp;s=' . $s, 'activate-module_' . $module_file ) . '" class="edit" aria-label="' . esc_attr( sprintf( _x( 'Activate %s', 'module' ), $module_data['Name'] ) ) . '">' . __( 'Activate' ) . '</a>';
					}

					if ( ! is_multisite() && current_user_can( 'delete_modules' ) ) {
						/* translators: %s: module name */
						$actions['delete'] = '<a href="' . mcms_nonce_url( 'modules.php?action=delete-selected&amp;checked[]=' . urlencode( $module_file ) . '&amp;module_status=' . $context . '&amp;paged=' . $page . '&amp;s=' . $s, 'bulk-modules' ) . '" class="delete" aria-label="' . esc_attr( sprintf( _x( 'Delete %s', 'module' ), $module_data['Name'] ) ) . '">' . __( 'Delete' ) . '</a>';
					}
				} // end if $is_active

			 } // end if $screen->in_admin( 'network' )

		} // end if $context

		$actions = array_filter( $actions );

		if ( $screen->in_admin( 'network' ) ) {

			/**
			 * Filters the action links displayed for each module in the Network Admin Modules list table.
			 *
			 * @since 3.1.0
			 *
			 * @param array  $actions     An array of module action links. By default this can include 'activate',
			 *                            'deactivate', and 'delete'.
			 * @param string $module_file Path to the module file relative to the modules directory.
			 * @param array  $module_data An array of module data. See `get_module_data()`.
			 * @param string $context     The module context. By default this can include 'all', 'active', 'inactive',
			 *                            'recently_activated', 'upgrade', 'mustuse', 'dropins', and 'search'.
			 */
			$actions = apply_filters( 'network_admin_module_action_links', $actions, $module_file, $module_data, $context );

			/**
			 * Filters the list of action links displayed for a specific module in the Network Admin Modules list table.
			 *
			 * The dynamic portion of the hook name, `$module_file`, refers to the path
			 * to the module file, relative to the modules directory.
			 *
			 * @since 3.1.0
			 *
			 * @param array  $actions     An array of module action links. By default this can include 'activate',
			 *                            'deactivate', and 'delete'.
			 * @param string $module_file Path to the module file relative to the modules directory.
			 * @param array  $module_data An array of module data. See `get_module_data()`.
			 * @param string $context     The module context. By default this can include 'all', 'active', 'inactive',
			 *                            'recently_activated', 'upgrade', 'mustuse', 'dropins', and 'search'.
			 */
			$actions = apply_filters( "network_admin_module_action_links_{$module_file}", $actions, $module_file, $module_data, $context );

		} else {

			/**
			 * Filters the action links displayed for each module in the Modules list table.
			 *
			 * @since 2.5.0
			 * @since 2.6.0 The `$context` parameter was added.
			 * @since 4.9.0 The 'Edit' link was removed from the list of action links.
			 *
			 * @param array  $actions     An array of module action links. By default this can include 'activate',
			 *                            'deactivate', and 'delete'. With Multisite active this can also include
			 *                            'network_active' and 'network_only' items.
			 * @param string $module_file Path to the module file relative to the modules directory.
			 * @param array  $module_data An array of module data. See `get_module_data()`.
			 * @param string $context     The module context. By default this can include 'all', 'active', 'inactive',
			 *                            'recently_activated', 'upgrade', 'mustuse', 'dropins', and 'search'.
			 */
			$actions = apply_filters( 'module_action_links', $actions, $module_file, $module_data, $context );

			/**
			 * Filters the list of action links displayed for a specific module in the Modules list table.
			 *
			 * The dynamic portion of the hook name, `$module_file`, refers to the path
			 * to the module file, relative to the modules directory.
			 *
			 * @since 2.7.0
			 * @since 4.9.0 The 'Edit' link was removed from the list of action links.
			 *
			 * @param array  $actions     An array of module action links. By default this can include 'activate',
			 *                            'deactivate', and 'delete'. With Multisite active this can also include
			 *                            'network_active' and 'network_only' items.
			 * @param string $module_file Path to the module file relative to the modules directory.
			 * @param array  $module_data An array of module data. See `get_module_data()`.
			 * @param string $context     The module context. By default this can include 'all', 'active', 'inactive',
			 *                            'recently_activated', 'upgrade', 'mustuse', 'dropins', and 'search'.
			 */
			$actions = apply_filters( "module_action_links_{$module_file}", $actions, $module_file, $module_data, $context );

		}

		$class = $is_active ? 'active' : 'inactive';
		$checkbox_id =  "checkbox_" . md5($module_data['Name']);
		if ( $restrict_network_active || $restrict_network_only || in_array( $status, array( 'mustuse', 'dropins' ) ) ) {
			$checkbox = '';
		} else {
			$checkbox = "<label class='screen-reader-text' for='" . $checkbox_id . "' >" . sprintf( __( 'Select %s' ), $module_data['Name'] ) . "</label>"
				. "<input type='checkbox' name='checked[]' value='" . esc_attr( $module_file ) . "' id='" . $checkbox_id . "' />";
		}
		if ( 'dropins' != $context ) {
			$description = '<p>' . ( $module_data['Description'] ? $module_data['Description'] : '&nbsp;' ) . '</p>';
			$module_name = $module_data['Name'];
		}

		if ( ! empty( $totals['upgrade'] ) && ! empty( $module_data['update'] ) )
			$class .= ' update';

		$module_slug = isset( $module_data['slug'] ) ? $module_data['slug'] : sanitize_title( $module_name );
		printf( '<tr class="%s" data-slug="%s" data-module="%s">',
			esc_attr( $class ),
			esc_attr( $module_slug ),
			esc_attr( $module_file )
		);

		list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();

		foreach ( $columns as $column_name => $column_display_name ) {
			$extra_classes = '';
			if ( in_array( $column_name, $hidden ) ) {
				$extra_classes = ' hidden';
			}

			switch ( $column_name ) {
				case 'cb':
					echo "<th scope='row' class='check-column'>$checkbox</th>";
					break;
				case 'name':
					echo "<td class='module-title column-primary'><strong>$module_name</strong>";
					echo $this->row_actions( $actions, true );
					echo "</td>";
					break;
				case 'description':
					$classes = 'column-description desc';

					echo "<td class='$classes{$extra_classes}'>
						<div class='module-description'>$description</div>
						<div class='$class second module-version-author-uri'>";

					$module_meta = array();
					if ( !empty( $module_data['Version'] ) )
						$module_meta[] = sprintf( __( 'Version %s' ), $module_data['Version'] );
					if ( !empty( $module_data['Author'] ) ) {
						$author = $module_data['Author'];
						if ( !empty( $module_data['AuthorURI'] ) )
							$author = '<a href="' . $module_data['AuthorURI'] . '">' . $module_data['Author'] . '</a>';
						$module_meta[] = sprintf( __( 'By %s' ), $author );
					}

					// Details link using API info, if available
					if ( isset( $module_data['slug'] ) && current_user_can( 'install_modules' ) ) {
						$module_meta[] = sprintf( '<a href="%s" class="thickbox open-module-details-modal" aria-label="%s" data-title="%s">%s</a>',
							esc_url( network_admin_url( 'module-install.php?tab=module-information&module=' . $module_data['slug'] .
								'&TB_iframe=true&width=600&height=550' ) ),
							esc_attr( sprintf( __( 'More information about %s' ), $module_name ) ),
							esc_attr( $module_name ),
							__( 'View details' )
						);
					} elseif ( ! empty( $module_data['ModuleURI'] ) ) {
						$module_meta[] = sprintf( '<a href="%s">%s</a>',
							esc_url( $module_data['ModuleURI'] ),
							__( 'Visit module site' )
						);
					}

					/**
					 * Filters the array of row meta for each module in the Modules list table.
					 *
					 * @since 2.8.0
					 *
					 * @param array  $module_meta An array of the module's metadata,
					 *                            including the version, author,
					 *                            author URI, and module URI.
					 * @param string $module_file Path to the module file, relative to the modules directory.
					 * @param array  $module_data An array of module data.
					 * @param string $status      Status of the module. Defaults are 'All', 'Active',
					 *                            'Inactive', 'Recently Activated', 'Upgrade', 'Must-Use',
					 *                            'Drop-ins', 'Search'.
					 */
					$module_meta = apply_filters( 'module_row_meta', $module_meta, $module_file, $module_data, $status );
					echo implode( ' | ', $module_meta );

					echo "</div></td>";
					break;
				default:
					$classes = "$column_name column-$column_name $class";

					echo "<td class='$classes{$extra_classes}'>";

					/**
					 * Fires inside each custom column of the Modules list table.
					 *
					 * @since 3.1.0
					 *
					 * @param string $column_name Name of the column.
					 * @param string $module_file Path to the module file.
					 * @param array  $module_data An array of module data.
					 */
					do_action( 'manage_modules_custom_column', $column_name, $module_file, $module_data );

					echo "</td>";
			}
		}

		echo "</tr>";

		/**
		 * Fires after each row in the Modules list table.
		 *
		 * @since 2.3.0
		 *
		 * @param string $module_file Path to the module file, relative to the modules directory.
		 * @param array  $module_data An array of module data.
		 * @param string $status      Status of the module. Defaults are 'All', 'Active',
		 *                            'Inactive', 'Recently Activated', 'Upgrade', 'Must-Use',
		 *                            'Drop-ins', 'Search'.
		 */
		do_action( 'after_module_row', $module_file, $module_data, $status );

		/**
		 * Fires after each specific row in the Modules list table.
		 *
		 * The dynamic portion of the hook name, `$module_file`, refers to the path
		 * to the module file, relative to the modules directory.
		 *
		 * @since 2.7.0
		 *
		 * @param string $module_file Path to the module file, relative to the modules directory.
		 * @param array  $module_data An array of module data.
		 * @param string $status      Status of the module. Defaults are 'All', 'Active',
		 *                            'Inactive', 'Recently Activated', 'Upgrade', 'Must-Use',
		 *                            'Drop-ins', 'Search'.
		 */
		do_action( "after_module_row_{$module_file}", $module_file, $module_data, $status );
	}

	/**
	 * Gets the name of the primary column for this specific list table.
	 *
	 * @since 4.3.0
	 *
	 * @return string Unalterable name for the primary column, in this case, 'name'.
	 */
	protected function get_primary_column_name() {
		return 'name';
	}
}
