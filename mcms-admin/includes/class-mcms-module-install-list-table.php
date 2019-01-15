<?php
/**
 * List Table API: MCMS_Module_Install_List_Table class
 *
 * @package MandarinCMS
 * @subpackage Administration
 * @since 3.1.0
 */

/**
 * Core class used to implement displaying modules to install in a list table.
 *
 * @since 3.1.0
 * @access private
 *
 * @see MCMS_List_Table
 */
class MCMS_Module_Install_List_Table extends MCMS_List_Table {

	public $order = 'ASC';
	public $orderby = null;
	public $groups = array();

	private $error;

	/**
	 *
	 * @return bool
	 */
	public function ajax_user_can() {
		return current_user_can('install_modules');
	}

	/**
	 * Return the list of known modules.
	 *
	 * Uses the transient data from the updates API to determine the known
	 * installed modules.
	 *
	 * @since 4.9.0
	 * @access protected
	 *
	 * @return array
	 */
	protected function get_installed_modules() {
		$modules = array();

		$module_info = get_site_transient( 'update_modules' );
		if ( isset( $module_info->no_update ) ) {
			foreach ( $module_info->no_update as $module ) {
				$module->upgrade          = false;
				$modules[ $module->slug ] = $module;
			}
		}

		if ( isset( $module_info->response ) ) {
			foreach ( $module_info->response as $module ) {
				$module->upgrade          = true;
				$modules[ $module->slug ] = $module;
			}
		}

		return $modules;
	}

	/**
	 * Return a list of slugs of installed modules, if known.
	 *
	 * Uses the transient data from the updates API to determine the slugs of
	 * known installed modules. This might be better elsewhere, perhaps even
	 * within get_modules().
	 *
	 * @since 4.0.0
	 *
	 * @return array
	 */
	protected function get_installed_module_slugs() {
		return array_keys( $this->get_installed_modules() );
	}

	/**
	 *
	 * @global array  $tabs
	 * @global string $tab
	 * @global int    $paged
	 * @global string $type
	 * @global string $term
	 */
	public function prepare_items() {
		include( BASED_TREE_URI . 'mcms-admin/includes/module-install.php' );

		global $tabs, $tab, $paged, $type, $term;

		mcms_reset_vars( array( 'tab' ) );

		$paged = $this->get_pagenum();

		$per_page = 30;

		// These are the tabs which are shown on the page
		$tabs = array();

		if ( 'search' === $tab ) {
			$tabs['search'] = __( 'Search Results' );
		}
		if ( $tab === 'beta' || false !== strpos( get_bloginfo( 'version' ), '-' ) ) {
			$tabs['beta'] = _x( 'Beta Testing', 'Module Installer' );
		}
		$tabs['featured']    = _x( 'Featured', 'Module Installer' );
		$tabs['popular']     = _x( 'Popular', 'Module Installer' );
		$tabs['recommended'] = _x( 'Recommended', 'Module Installer' );
		$tabs['favorites']   = _x( 'Favorites', 'Module Installer' );
		if ( current_user_can( 'upload_modules' ) ) {
			// No longer a real tab. Here for filter compatibility.
			// Gets skipped in get_views().
			$tabs['upload'] = __( 'Upload Module' );
		}

		$nonmenu_tabs = array( 'module-information' ); // Valid actions to perform which do not have a Menu item.

		/**
		 * Filters the tabs shown on the Module Install screen.
		 *
		 * @since 2.7.0
		 *
		 * @param array $tabs The tabs shown on the Module Install screen. Defaults include 'featured', 'popular',
		 *                    'recommended', 'favorites', and 'upload'.
		 */
		$tabs = apply_filters( 'install_modules_tabs', $tabs );

		/**
		 * Filters tabs not associated with a menu item on the Module Install screen.
		 *
		 * @since 2.7.0
		 *
		 * @param array $nonmenu_tabs The tabs that don't have a Menu item on the Module Install screen.
		 */
		$nonmenu_tabs = apply_filters( 'install_modules_nonmenu_tabs', $nonmenu_tabs );

		// If a non-valid menu tab has been selected, And it's not a non-menu action.
		if ( empty( $tab ) || ( !isset( $tabs[ $tab ] ) && !in_array( $tab, (array) $nonmenu_tabs ) ) )
			$tab = key( $tabs );

		$installed_modules = $this->get_installed_modules();

		$args = array(
			'page' => $paged,
			'per_page' => $per_page,
			'fields' => array(
				'last_updated' => true,
				'icons' => true,
				'active_installs' => true
			),
			// Send the locale and installed module slugs to the API so it can provide context-sensitive results.
			'locale' => get_user_locale(),
			'installed_modules' => array_keys( $installed_modules ),
		);

		switch ( $tab ) {
			case 'search':
				$type = isset( $_REQUEST['type'] ) ? mcms_unslash( $_REQUEST['type'] ) : 'term';
				$term = isset( $_REQUEST['s'] ) ? mcms_unslash( $_REQUEST['s'] ) : '';

				switch ( $type ) {
					case 'tag':
						$args['tag'] = sanitize_title_with_dashes( $term );
						break;
					case 'term':
						$args['search'] = $term;
						break;
					case 'author':
						$args['author'] = $term;
						break;
				}

				break;

			case 'featured':
				$args['fields']['group'] = true;
				$this->orderby = 'group';
				// No break!
			case 'popular':
			case 'new':
			case 'beta':
			case 'recommended':
				$args['browse'] = $tab;
				break;

			case 'favorites':
				$action = 'save_mcmsorg_username_' . get_current_user_id();
				if ( isset( $_GET['_mcmsnonce'] ) && mcms_verify_nonce( mcms_unslash( $_GET['_mcmsnonce'] ), $action ) ) {
					$user = isset( $_GET['user'] ) ? mcms_unslash( $_GET['user'] ) : get_user_option( 'mcmsorg_favorites' );
					update_user_meta( get_current_user_id(), 'mcmsorg_favorites', $user );
				} else {
					$user = get_user_option( 'mcmsorg_favorites' );
				}
				if ( $user )
					$args['user'] = $user;
				else
					$args = false;

				add_action( 'install_modules_favorites', 'install_modules_favorites_form', 9, 0 );
				break;

			default:
				$args = false;
				break;
		}

		/**
		 * Filters API request arguments for each Module Install screen tab.
		 *
		 * The dynamic portion of the hook name, `$tab`, refers to the module install tabs.
		 * Default tabs include 'featured', 'popular', 'recommended', 'favorites', and 'upload'.
		 *
		 * @since 3.7.0
		 *
		 * @param array|bool $args Module Install API arguments.
		 */
		$args = apply_filters( "install_modules_table_api_args_{$tab}", $args );

		if ( !$args )
			return;

		$api = modules_api( 'query_modules', $args );

		if ( is_mcms_error( $api ) ) {
			$this->error = $api;
			return;
		}

		$this->items = $api->modules;

		if ( $this->orderby ) {
			uasort( $this->items, array( $this, 'order_callback' ) );
		}

		$this->set_pagination_args( array(
			'total_items' => $api->info['results'],
			'per_page' => $args['per_page'],
		) );

		if ( isset( $api->info['groups'] ) ) {
			$this->groups = $api->info['groups'];
		}

		if ( $installed_modules ) {
			$js_modules = array_fill_keys(
				array( 'all', 'search', 'active', 'inactive', 'recently_activated', 'mustuse', 'dropins' ),
				array()
			);

			$js_modules['all'] = array_values( mcms_list_pluck( $installed_modules, 'module' ) );
			$upgrade_modules   = mcms_filter_object_list( $installed_modules, array( 'upgrade' => true ), 'and', 'module' );

			if ( $upgrade_modules ) {
				$js_modules['upgrade'] = array_values( $upgrade_modules );
			}

			mcms_localize_script( 'updates', '_mcmsUpdatesItemCounts', array(
				'modules' => $js_modules,
				'totals'  => mcms_get_update_data(),
			) );
		}
	}

	/**
	 */
	public function no_items() {
		if ( isset( $this->error ) ) { ?>
			<div class="inline error"><p><?php echo $this->error->get_error_message(); ?></p>
				<p class="hide-if-no-js"><button class="button try-again"><?php _e( 'Try Again' ); ?></button></p>
			</div>
		<?php } else { ?>
			<div class="no-module-results"><?php _e( 'No modules found. Try a different search.' ); ?></div>
		<?php
		}
	}

	/**
	 *
	 * @global array $tabs
	 * @global string $tab
	 *
	 * @return array
	 */
	protected function get_views() {
		global $tabs, $tab;

		$display_tabs = array();
		foreach ( (array) $tabs as $action => $text ) {
			$current_link_attributes = ( $action === $tab ) ? ' class="current" aria-current="page"' : '';
			$href = self_admin_url('module-install.php?tab=' . $action);
			$display_tabs['module-install-'.$action] = "<a href='$href'$current_link_attributes>$text</a>";
		}
		// No longer a real tab.
		unset( $display_tabs['module-install-upload'] );

		return $display_tabs;
	}

	/**
	 * Override parent views so we can use the filter bar display.
	 */
	public function views() {
		$views = $this->get_views();

		/** This filter is documented in mcms-admin/inclues/class-mcms-list-table.php */
		$views = apply_filters( "views_{$this->screen->id}", $views );

		$this->screen->render_screen_reader_content( 'heading_views' );
?>
<div class="mcms-filter">
	<ul class="filter-links">
		<?php
		if ( ! empty( $views ) ) {
			foreach ( $views as $class => $view ) {
				$views[ $class ] = "\t<li class='$class'>$view";
			}
			echo implode( " </li>\n", $views ) . "</li>\n";
		}
		?>
	</ul>

	<?php install_search_form(); ?>
</div>
<?php
	}

	/**
	 * Override the parent display() so we can provide a different container.
	 */
	public function display() {
		$singular = $this->_args['singular'];

		$data_attr = '';

		if ( $singular ) {
			$data_attr = " data-mcms-lists='list:$singular'";
		}

		$this->display_tablenav( 'top' );

?>
<div class="mcms-list-table <?php echo implode( ' ', $this->get_table_classes() ); ?>">
<?php
	$this->screen->render_screen_reader_content( 'heading_list' );
?>
	<div id="the-list"<?php echo $data_attr; ?>>
		<?php $this->display_rows_or_placeholder(); ?>
	</div>
</div>
<?php
		$this->display_tablenav( 'bottom' );
	}

	/**
	 * @global string $tab
	 *
	 * @param string $which
	 */
	protected function display_tablenav( $which ) {
		if ( $GLOBALS['tab'] === 'featured' ) {
			return;
		}

		if ( 'top' === $which ) {
			mcms_referer_field();
		?>
			<div class="tablenav top">
				<div class="alignleft actions">
					<?php
					/**
					 * Fires before the Module Install table header pagination is displayed.
					 *
					 * @since 2.7.0
					 */
					do_action( 'install_modules_table_header' ); ?>
				</div>
				<?php $this->pagination( $which ); ?>
				<br class="clear" />
			</div>
		<?php } else { ?>
			<div class="tablenav bottom">
				<?php $this->pagination( $which ); ?>
				<br class="clear" />
			</div>
		<?php
		}
	}

	/**
	 * @return array
	 */
	protected function get_table_classes() {
		return array( 'widefat', $this->_args['plural'] );
	}

	/**
	 * @return array
	 */
	public function get_columns() {
		return array();
	}

	/**
	 * @param object $module_a
	 * @param object $module_b
	 * @return int
	 */
	private function order_callback( $module_a, $module_b ) {
		$orderby = $this->orderby;
		if ( ! isset( $module_a->$orderby, $module_b->$orderby ) ) {
			return 0;
		}

		$a = $module_a->$orderby;
		$b = $module_b->$orderby;

		if ( $a == $b ) {
			return 0;
		}

		if ( 'DESC' === $this->order ) {
			return ( $a < $b ) ? 1 : -1;
		} else {
			return ( $a < $b ) ? -1 : 1;
		}
	}

	public function display_rows() {
		$modules_allowedtags = array(
			'a' => array( 'href' => array(),'title' => array(), 'target' => array() ),
			'abbr' => array( 'title' => array() ),'acronym' => array( 'title' => array() ),
			'code' => array(), 'pre' => array(), 'em' => array(),'strong' => array(),
			'ul' => array(), 'ol' => array(), 'li' => array(), 'p' => array(), 'br' => array()
		);

		$modules_group_titles = array(
			'Performance' => _x( 'Performance', 'Module installer group title' ),
			'Social'      => _x( 'Social',      'Module installer group title' ),
			'Tools'       => _x( 'Tools',       'Module installer group title' ),
		);

		$group = null;

		foreach ( (array) $this->items as $module ) {
			if ( is_object( $module ) ) {
				$module = (array) $module;
			}

			// Display the group heading if there is one
			if ( isset( $module['group'] ) && $module['group'] != $group ) {
				if ( isset( $this->groups[ $module['group'] ] ) ) {
					$group_name = $this->groups[ $module['group'] ];
					if ( isset( $modules_group_titles[ $group_name ] ) ) {
						$group_name = $modules_group_titles[ $group_name ];
					}
				} else {
					$group_name = $module['group'];
				}

				// Starting a new group, close off the divs of the last one
				if ( ! empty( $group ) ) {
					echo '</div></div>';
				}

				echo '<div class="module-group"><h3>' . esc_html( $group_name ) . '</h3>';
				// needs an extra wrapping div for nth-child selectors to work
				echo '<div class="module-items">';

				$group = $module['group'];
			}
			$title = mcms_kses( $module['name'], $modules_allowedtags );

			// Remove any HTML from the description.
			$description = strip_tags( $module['short_description'] );
			$version = mcms_kses( $module['version'], $modules_allowedtags );

			$name = strip_tags( $title . ' ' . $version );

			$author = mcms_kses( $module['author'], $modules_allowedtags );
			if ( ! empty( $author ) ) {
				$author = ' <cite>' . sprintf( __( 'By %s' ), $author ) . '</cite>';
			}

			$action_links = array();

			if ( current_user_can( 'install_modules' ) || current_user_can( 'update_modules' ) ) {
				$status = install_module_install_status( $module );

				switch ( $status['status'] ) {
					case 'install':
						if ( $status['url'] ) {
							/* translators: 1: Module name and version. */
							$action_links[] = '<a class="install-now button" data-slug="' . esc_attr( $module['slug'] ) . '" href="' . esc_url( $status['url'] ) . '" aria-label="' . esc_attr( sprintf( __( 'Install %s now' ), $name ) ) . '" data-name="' . esc_attr( $name ) . '">' . __( 'Install Now' ) . '</a>';
						}
						break;

					case 'update_available':
						if ( $status['url'] ) {
							/* translators: 1: Module name and version */
							$action_links[] = '<a class="update-now button aria-button-if-js" data-module="' . esc_attr( $status['file'] ) . '" data-slug="' . esc_attr( $module['slug'] ) . '" href="' . esc_url( $status['url'] ) . '" aria-label="' . esc_attr( sprintf( __( 'Update %s now' ), $name ) ) . '" data-name="' . esc_attr( $name ) . '">' . __( 'Update Now' ) . '</a>';
						}
						break;

					case 'latest_installed':
					case 'newer_installed':
						if ( is_module_active( $status['file'] ) ) {
							$action_links[] = '<button type="button" class="button button-disabled" disabled="disabled">' . _x( 'Active', 'module' ) . '</button>';
						} elseif ( current_user_can( 'activate_module', $status['file'] ) ) {
							$button_text  = __( 'Activate' );
							/* translators: %s: Module name */
							$button_label = _x( 'Activate %s', 'module' );
							$activate_url = add_query_arg( array(
								'_mcmsnonce'    => mcms_create_nonce( 'activate-module_' . $status['file'] ),
								'action'      => 'activate',
								'module'      => $status['file'],
							), network_admin_url( 'modules.php' ) );

							if ( is_network_admin() ) {
								$button_text  = __( 'Network Activate' );
								/* translators: %s: Module name */
								$button_label = _x( 'Network Activate %s', 'module' );
								$activate_url = add_query_arg( array( 'networkwide' => 1 ), $activate_url );
							}

							$action_links[] = sprintf(
								'<a href="%1$s" class="button activate-now" aria-label="%2$s">%3$s</a>',
								esc_url( $activate_url ),
								esc_attr( sprintf( $button_label, $module['name'] ) ),
								$button_text
							);
						} else {
							$action_links[] = '<button type="button" class="button button-disabled" disabled="disabled">' . _x( 'Installed', 'module' ) . '</button>';
						}
						break;
				}
			}

			$details_link   = self_admin_url( 'module-install.php?tab=module-information&amp;module=' . $module['slug'] .
								'&amp;TB_iframe=true&amp;width=600&amp;height=550' );

			/* translators: 1: Module name and version. */
			$action_links[] = '<a href="' . esc_url( $details_link ) . '" class="thickbox open-module-details-modal" aria-label="' . esc_attr( sprintf( __( 'More information about %s' ), $name ) ) . '" data-title="' . esc_attr( $name ) . '">' . __( 'More Details' ) . '</a>';

			if ( !empty( $module['icons']['svg'] ) ) {
				$module_icon_url = $module['icons']['svg'];
			} elseif ( !empty( $module['icons']['2x'] ) ) {
				$module_icon_url = $module['icons']['2x'];
			} elseif ( !empty( $module['icons']['1x'] ) ) {
				$module_icon_url = $module['icons']['1x'];
			} else {
				$module_icon_url = $module['icons']['default'];
			}

			/**
			 * Filters the install action links for a module.
			 *
			 * @since 2.7.0
			 *
			 * @param array $action_links An array of module action hyperlinks. Defaults are links to Details and Install Now.
			 * @param array $module       The module currently being listed.
			 */
			$action_links = apply_filters( 'module_install_action_links', $action_links, $module );

			$last_updated_timestamp = strtotime( $module['last_updated'] );
		?>
		<div class="module-card module-card-<?php echo sanitize_html_class( $module['slug'] ); ?>">
			<div class="module-card-top">
				<div class="name column-name">
					<h3>
						<a href="<?php echo esc_url( $details_link ); ?>" class="thickbox open-module-details-modal">
						<?php echo $title; ?>
						<img src="<?php echo esc_attr( $module_icon_url ) ?>" class="module-icon" alt="">
						</a>
					</h3>
				</div>
				<div class="action-links">
					<?php
						if ( $action_links ) {
							echo '<ul class="module-action-buttons"><li>' . implode( '</li><li>', $action_links ) . '</li></ul>';
						}
					?>
				</div>
				<div class="desc column-description">
					<p><?php echo $description; ?></p>
					<p class="authors"><?php echo $author; ?></p>
				</div>
			</div>
			<div class="module-card-bottom">
				<div class="vers column-rating">
					<?php mcms_star_rating( array( 'rating' => $module['rating'], 'type' => 'percent', 'number' => $module['num_ratings'] ) ); ?>
					<span class="num-ratings" aria-hidden="true">(<?php echo number_format_i18n( $module['num_ratings'] ); ?>)</span>
				</div>
				<div class="column-updated">
					<strong><?php _e( 'Last Updated:' ); ?></strong> <?php printf( __( '%s ago' ), human_time_diff( $last_updated_timestamp ) ); ?>
				</div>
				<div class="column-downloaded">
					<?php
					if ( $module['active_installs'] >= 1000000 ) {
						$active_installs_text = _x( '1+ Million', 'Active module installations' );
					} elseif ( 0 == $module['active_installs'] ) {
						$active_installs_text = _x( 'Less Than 10', 'Active module installations' );
					} else {
						$active_installs_text = number_format_i18n( $module['active_installs'] ) . '+';
					}
					printf( __( '%s Active Installations' ), $active_installs_text );
					?>
				</div>
				<div class="column-compatibility">
					<?php
					$mcms_version = get_bloginfo( 'version' );

					if ( ! empty( $module['tested'] ) && version_compare( substr( $mcms_version, 0, strlen( $module['tested'] ) ), $module['tested'], '>' ) ) {
						echo '<span class="compatibility-untested">' . __( 'Untested with your version of MandarinCMS' ) . '</span>';
					} elseif ( ! empty( $module['requires'] ) && version_compare( substr( $mcms_version, 0, strlen( $module['requires'] ) ), $module['requires'], '<' ) ) {
						echo '<span class="compatibility-incompatible">' . __( '<strong>Incompatible</strong> with your version of MandarinCMS' ) . '</span>';
					} else {
						echo '<span class="compatibility-compatible">' . __( '<strong>Compatible</strong> with your version of MandarinCMS' ) . '</span>';
					}
					?>
				</div>
			</div>
		</div>
		<?php
		}

		// Close off the group divs of the last one
		if ( ! empty( $group ) ) {
			echo '</div></div>';
		}
	}
}
