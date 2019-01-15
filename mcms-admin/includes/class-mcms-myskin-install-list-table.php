<?php
/**
 * List Table API: MCMS_MySkin_Install_List_Table class
 *
 * @package MandarinCMS
 * @subpackage Administration
 * @since 3.1.0
 */

/**
 * Core class used to implement displaying myskins to install in a list table.
 *
 * @since 3.1.0
 * @access private
 *
 * @see MCMS_MySkins_List_Table
 */
class MCMS_MySkin_Install_List_Table extends MCMS_MySkins_List_Table {

	public $features = array();

	/**
	 *
	 * @return bool
	 */
	public function ajax_user_can() {
		return current_user_can( 'install_myskins' );
	}

	/**
	 *
	 * @global array  $tabs
	 * @global string $tab
	 * @global int    $paged
	 * @global string $type
	 * @global array  $myskin_field_defaults
	 */
	public function prepare_items() {
		include( BASED_TREE_URI . 'mcms-admin/includes/myskin-install.php' );

		global $tabs, $tab, $paged, $type, $myskin_field_defaults;
		mcms_reset_vars( array( 'tab' ) );

		$search_terms = array();
		$search_string = '';
		if ( ! empty( $_REQUEST['s'] ) ){
			$search_string = strtolower( mcms_unslash( $_REQUEST['s'] ) );
			$search_terms = array_unique( array_filter( array_map( 'trim', explode( ',', $search_string ) ) ) );
		}

		if ( ! empty( $_REQUEST['features'] ) )
			$this->features = $_REQUEST['features'];

		$paged = $this->get_pagenum();

		$per_page = 36;

		// These are the tabs which are shown on the page,
		$tabs = array();
		$tabs['dashboard'] = __( 'Search' );
		if ( 'search' === $tab )
			$tabs['search']	= __( 'Search Results' );
		$tabs['upload'] = __( 'Upload' );
		$tabs['featured'] = _x( 'Featured', 'myskins' );
		//$tabs['popular']  = _x( 'Popular', 'myskins' );
		$tabs['new']      = _x( 'Latest', 'myskins' );
		$tabs['updated']  = _x( 'Recently Updated', 'myskins' );

		$nonmenu_tabs = array( 'myskin-information' ); // Valid actions to perform which do not have a Menu item.

		/** This filter is documented in mcms-admin/myskin-install.php */
		$tabs = apply_filters( 'install_myskins_tabs', $tabs );

		/**
		 * Filters tabs not associated with a menu item on the Install MySkins screen.
		 *
		 * @since 2.8.0
		 *
		 * @param array $nonmenu_tabs The tabs that don't have a menu item on
		 *                            the Install MySkins screen.
		 */
		$nonmenu_tabs = apply_filters( 'install_myskins_nonmenu_tabs', $nonmenu_tabs );

		// If a non-valid menu tab has been selected, And it's not a non-menu action.
		if ( empty( $tab ) || ( ! isset( $tabs[ $tab ] ) && ! in_array( $tab, (array) $nonmenu_tabs ) ) )
			$tab = key( $tabs );

		$args = array( 'page' => $paged, 'per_page' => $per_page, 'fields' => $myskin_field_defaults );

		switch ( $tab ) {
			case 'search':
				$type = isset( $_REQUEST['type'] ) ? mcms_unslash( $_REQUEST['type'] ) : 'term';
				switch ( $type ) {
					case 'tag':
						$args['tag'] = array_map( 'sanitize_key', $search_terms );
						break;
					case 'term':
						$args['search'] = $search_string;
						break;
					case 'author':
						$args['author'] = $search_string;
						break;
				}

				if ( ! empty( $this->features ) ) {
					$args['tag'] = $this->features;
					$_REQUEST['s'] = implode( ',', $this->features );
					$_REQUEST['type'] = 'tag';
				}

				add_action( 'install_myskins_table_header', 'install_myskin_search_form', 10, 0 );
				break;

			case 'featured':
			// case 'popular':
			case 'new':
			case 'updated':
				$args['browse'] = $tab;
				break;

			default:
				$args = false;
				break;
		}

		/**
		 * Filters API request arguments for each Install MySkins screen tab.
		 *
		 * The dynamic portion of the hook name, `$tab`, refers to the myskin install
		 * tabs. Default tabs are 'dashboard', 'search', 'upload', 'featured',
		 * 'new', and 'updated'.
		 *
		 * @since 3.7.0
		 *
		 * @param array $args An array of myskins API arguments.
		 */
		$args = apply_filters( "install_myskins_table_api_args_{$tab}", $args );

		if ( ! $args )
			return;

		$api = myskins_api( 'query_myskins', $args );

		if ( is_mcms_error( $api ) )
			mcms_die( $api->get_error_message() . '</p> <p><a href="#" onclick="document.location.reload(); return false;">' . __( 'Try again' ) . '</a>' );

		$this->items = $api->myskins;

		$this->set_pagination_args( array(
			'total_items' => $api->info['results'],
			'per_page' => $args['per_page'],
			'infinite_scroll' => true,
		) );
	}

	/**
	 */
	public function no_items() {
		_e( 'No myskins match your request.' );
	}

	/**
	 *
	 * @global array $tabs
	 * @global string $tab
	 * @return array
	 */
	protected function get_views() {
		global $tabs, $tab;

		$display_tabs = array();
		foreach ( (array) $tabs as $action => $text ) {
			$current_link_attributes = ( $action === $tab ) ? ' class="current" aria-current="page"' : '';
			$href = self_admin_url('myskin-install.php?tab=' . $action);
			$display_tabs['myskin-install-'.$action] = "<a href='$href'$current_link_attributes>$text</a>";
		}

		return $display_tabs;
	}

	/**
	 */
	public function display() {
		mcms_nonce_field( "fetch-list-" . get_class( $this ), '_ajax_fetch_list_nonce' );
?>
		<div class="tablenav top myskins">
			<div class="alignleft actions">
				<?php
				/**
				 * Fires in the Install MySkins list table header.
				 *
				 * @since 2.8.0
				 */
				do_action( 'install_myskins_table_header' );
				?>
			</div>
			<?php $this->pagination( 'top' ); ?>
			<br class="clear" />
		</div>

		<div id="availablemyskins">
			<?php $this->display_rows_or_placeholder(); ?>
		</div>

		<?php
		$this->tablenav( 'bottom' );
	}

	/**
	 */
	public function display_rows() {
		$myskins = $this->items;
		foreach ( $myskins as $myskin ) {
				?>
				<div class="available-myskin installable-myskin"><?php
					$this->single_row( $myskin );
				?></div>
		<?php } // end foreach $myskin_names

		$this->myskin_installer();
	}

	/**
	 * Prints a myskin from the MandarinCMS.org API.
	 *
	 * @since 3.1.0
	 *
	 * @global array $myskins_allowedtags
	 *
	 * @param object $myskin {
	 *     An object that contains myskin data returned by the MandarinCMS.org API.
	 *
	 *     @type string $name           MySkin name, e.g. 'Twenty Seventeen'.
	 *     @type string $slug           MySkin slug, e.g. 'razorleaf'.
	 *     @type string $version        MySkin version, e.g. '1.1'.
	 *     @type string $author         MySkin author username, e.g. 'melchoyce'.
	 *     @type string $preview_url    Preview URL, e.g. 'http://2017.mandarincms.net/'.
	 *     @type string $screenshot_url Screenshot URL, e.g. 'https://mandarincms.com/myskins/razorleaf/'.
	 *     @type float  $rating         Rating score.
	 *     @type int    $num_ratings    The number of ratings.
	 *     @type string $homepage       MySkin homepage, e.g. 'https://mandarincms.com/myskins/razorleaf/'.
	 *     @type string $description    MySkin description.
	 *     @type string $download_link  MySkin ZIP download URL.
	 * }
	 */
	public function single_row( $myskin ) {
		global $myskins_allowedtags;

		if ( empty( $myskin ) )
			return;

		$name   = mcms_kses( $myskin->name,   $myskins_allowedtags );
		$author = mcms_kses( $myskin->author, $myskins_allowedtags );

		$preview_title = sprintf( __('Preview &#8220;%s&#8221;'), $name );
		$preview_url   = add_query_arg( array(
			'tab'   => 'myskin-information',
			'myskin' => $myskin->slug,
		), self_admin_url( 'myskin-install.php' ) );

		$actions = array();

		$install_url = add_query_arg( array(
			'action' => 'install-myskin',
			'myskin'  => $myskin->slug,
		), self_admin_url( 'update.php' ) );

		$update_url = add_query_arg( array(
			'action' => 'upgrade-myskin',
			'myskin'  => $myskin->slug,
		), self_admin_url( 'update.php' ) );

		$status = $this->_get_myskin_status( $myskin );

		switch ( $status ) {
			case 'update_available':
				$actions[] = '<a class="install-now" href="' . esc_url( mcms_nonce_url( $update_url, 'upgrade-myskin_' . $myskin->slug ) ) . '" title="' . esc_attr( sprintf( __( 'Update to version %s' ), $myskin->version ) ) . '">' . __( 'Update' ) . '</a>';
				break;
			case 'newer_installed':
			case 'latest_installed':
				$actions[] = '<span class="install-now" title="' . esc_attr__( 'This myskin is already installed and is up to date' ) . '">' . _x( 'Installed', 'myskin' ) . '</span>';
				break;
			case 'install':
			default:
				$actions[] = '<a class="install-now" href="' . esc_url( mcms_nonce_url( $install_url, 'install-myskin_' . $myskin->slug ) ) . '" title="' . esc_attr( sprintf( __( 'Install %s' ), $name ) ) . '">' . __( 'Install Now' ) . '</a>';
				break;
		}

		$actions[] = '<a class="install-myskin-preview" href="' . esc_url( $preview_url ) . '" title="' . esc_attr( sprintf( __( 'Preview %s' ), $name ) ) . '">' . __( 'Preview' ) . '</a>';

		/**
		 * Filters the install action links for a myskin in the Install MySkins list table.
		 *
		 * @since 3.4.0
		 *
		 * @param array    $actions An array of myskin action hyperlinks. Defaults are
		 *                          links to Install Now, Preview, and Details.
		 * @param MCMS_MySkin $myskin   MySkin object.
		 */
		$actions = apply_filters( 'myskin_install_actions', $actions, $myskin );

		?>
		<a class="screenshot install-myskin-preview" href="<?php echo esc_url( $preview_url ); ?>" title="<?php echo esc_attr( $preview_title ); ?>">
			<img src="<?php echo esc_url( $myskin->screenshot_url ); ?>" width="150" alt="" />
		</a>

		<h3><?php echo $name; ?></h3>
		<div class="myskin-author"><?php printf( __( 'By %s' ), $author ); ?></div>

		<div class="action-links">
			<ul>
				<?php foreach ( $actions as $action ): ?>
					<li><?php echo $action; ?></li>
				<?php endforeach; ?>
				<li class="hide-if-no-js"><a href="#" class="myskin-detail"><?php _e('Details') ?></a></li>
			</ul>
		</div>

		<?php
		$this->install_myskin_info( $myskin );
	}

	/**
	 * Prints the wrapper for the myskin installer.
	 */
	public function myskin_installer() {
		?>
		<div id="myskin-installer" class="mcms-full-overlay expanded">
			<div class="mcms-full-overlay-sidebar">
				<div class="mcms-full-overlay-header">
					<a href="#" class="close-full-overlay button"><?php _e( 'Close' ); ?></a>
					<span class="myskin-install"></span>
				</div>
				<div class="mcms-full-overlay-sidebar-content">
					<div class="install-myskin-info"></div>
				</div>
				<div class="mcms-full-overlay-footer">
					<button type="button" class="collapse-sidebar button" aria-expanded="true" aria-label="<?php esc_attr_e( 'Collapse Sidebar' ); ?>">
						<span class="collapse-sidebar-arrow"></span>
						<span class="collapse-sidebar-label"><?php _e( 'Collapse' ); ?></span>
					</button>
				</div>
			</div>
			<div class="mcms-full-overlay-main"></div>
		</div>
		<?php
	}

	/**
	 * Prints the wrapper for the myskin installer with a provided myskin's data.
	 * Used to make the myskin installer work for no-js.
	 *
	 * @param object $myskin - A MandarinCMS.org MySkin API object.
	 */
	public function myskin_installer_single( $myskin ) {
		?>
		<div id="myskin-installer" class="mcms-full-overlay single-myskin">
			<div class="mcms-full-overlay-sidebar">
				<?php $this->install_myskin_info( $myskin ); ?>
			</div>
			<div class="mcms-full-overlay-main">
				<iframe src="<?php echo esc_url( $myskin->preview_url ); ?>"></iframe>
			</div>
		</div>
		<?php
	}

	/**
	 * Prints the info for a myskin (to be used in the myskin installer modal).
	 *
	 * @global array $myskins_allowedtags
	 *
	 * @param object $myskin - A MandarinCMS.org MySkin API object.
	 */
	public function install_myskin_info( $myskin ) {
		global $myskins_allowedtags;

		if ( empty( $myskin ) )
			return;

		$name   = mcms_kses( $myskin->name,   $myskins_allowedtags );
		$author = mcms_kses( $myskin->author, $myskins_allowedtags );

		$install_url = add_query_arg( array(
			'action' => 'install-myskin',
			'myskin'  => $myskin->slug,
		), self_admin_url( 'update.php' ) );

		$update_url = add_query_arg( array(
			'action' => 'upgrade-myskin',
			'myskin'  => $myskin->slug,
		), self_admin_url( 'update.php' ) );

		$status = $this->_get_myskin_status( $myskin );

		?>
		<div class="install-myskin-info"><?php
			switch ( $status ) {
				case 'update_available':
					echo '<a class="myskin-install button button-primary" href="' . esc_url( mcms_nonce_url( $update_url, 'upgrade-myskin_' . $myskin->slug ) ) . '" title="' . esc_attr( sprintf( __( 'Update to version %s' ), $myskin->version ) ) . '">' . __( 'Update' ) . '</a>';
					break;
				case 'newer_installed':
				case 'latest_installed':
					echo '<span class="myskin-install" title="' . esc_attr__( 'This myskin is already installed and is up to date' ) . '">' . _x( 'Installed', 'myskin' ) . '</span>';
					break;
				case 'install':
				default:
					echo '<a class="myskin-install button button-primary" href="' . esc_url( mcms_nonce_url( $install_url, 'install-myskin_' . $myskin->slug ) ) . '">' . __( 'Install' ) . '</a>';
					break;
			} ?>
			<h3 class="myskin-name"><?php echo $name; ?></h3>
			<span class="myskin-by"><?php printf( __( 'By %s' ), $author ); ?></span>
			<?php if ( isset( $myskin->screenshot_url ) ): ?>
				<img class="myskin-screenshot" src="<?php echo esc_url( $myskin->screenshot_url ); ?>" alt="" />
			<?php endif; ?>
			<div class="myskin-details">
				<?php mcms_star_rating( array( 'rating' => $myskin->rating, 'type' => 'percent', 'number' => $myskin->num_ratings ) ); ?>
				<div class="myskin-version">
					<strong><?php _e('Version:') ?> </strong>
					<?php echo mcms_kses( $myskin->version, $myskins_allowedtags ); ?>
				</div>
				<div class="myskin-description">
					<?php echo mcms_kses( $myskin->description, $myskins_allowedtags ); ?>
				</div>
			</div>
			<input class="myskin-preview-url" type="hidden" value="<?php echo esc_url( $myskin->preview_url ); ?>" />
		</div>
		<?php
	}

	/**
	 * Send required variables to JavaScript land
	 *
	 * @since 3.4.0
	 *
	 * @global string $tab  Current tab within MySkins->Install screen
	 * @global string $type Type of search.
	 *
	 * @param array $extra_args Unused.
	 */
	public function _js_vars( $extra_args = array() ) {
		global $tab, $type;
		parent::_js_vars( compact( 'tab', 'type' ) );
	}

	/**
	 * Check to see if the myskin is already installed.
	 *
	 * @since 3.4.0
	 *
	 * @param object $myskin - A MandarinCMS.org MySkin API object.
	 * @return string MySkin status.
	 */
	private function _get_myskin_status( $myskin ) {
		$status = 'install';

		$installed_myskin = mcms_get_myskin( $myskin->slug );
		if ( $installed_myskin->exists() ) {
			if ( version_compare( $installed_myskin->get('Version'), $myskin->version, '=' ) )
				$status = 'latest_installed';
			elseif ( version_compare( $installed_myskin->get('Version'), $myskin->version, '>' ) )
				$status = 'newer_installed';
			else
				$status = 'update_available';
		}

		return $status;
	}
}
