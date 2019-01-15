<?php
/**
 * List Table API: MCMS_MySkins_List_Table class
 *
 * @package MandarinCMS
 * @subpackage Administration
 * @since 3.1.0
 */

/**
 * Core class used to implement displaying installed myskins in a list table.
 *
 * @since 3.1.0
 * @access private
 *
 * @see MCMS_List_Table
 */
class MCMS_MySkins_List_Table extends MCMS_List_Table {

	protected $search_terms = array();
	public $features = array();

	/**
	 * Constructor.
	 *
	 * @since 3.1.0
	 *
	 * @see MCMS_List_Table::__construct() for more information on default arguments.
	 *
	 * @param array $args An associative array of arguments.
	 */
	public function __construct( $args = array() ) {
		parent::__construct( array(
			'ajax' => true,
			'screen' => isset( $args['screen'] ) ? $args['screen'] : null,
		) );
	}

	/**
	 *
	 * @return bool
	 */
	public function ajax_user_can() {
		// Do not check edit_myskin_options here. Ajax calls for available myskins require switch_myskins.
		return current_user_can( 'switch_myskins' );
	}

	/**
	 */
	public function prepare_items() {
		$myskins = mcms_get_myskins( array( 'allowed' => true ) );

		if ( ! empty( $_REQUEST['s'] ) )
			$this->search_terms = array_unique( array_filter( array_map( 'trim', explode( ',', strtolower( mcms_unslash( $_REQUEST['s'] ) ) ) ) ) );

		if ( ! empty( $_REQUEST['features'] ) )
			$this->features = $_REQUEST['features'];

		if ( $this->search_terms || $this->features ) {
			foreach ( $myskins as $key => $myskin ) {
				if ( ! $this->search_myskin( $myskin ) )
					unset( $myskins[ $key ] );
			}
		}

		unset( $myskins[ get_option( 'stylesheet' ) ] );
		MCMS_MySkin::sort_by_name( $myskins );

		$per_page = 36;
		$page = $this->get_pagenum();

		$start = ( $page - 1 ) * $per_page;

		$this->items = array_slice( $myskins, $start, $per_page, true );

		$this->set_pagination_args( array(
			'total_items' => count( $myskins ),
			'per_page' => $per_page,
			'infinite_scroll' => true,
		) );
	}

	/**
	 */
	public function no_items() {
		if ( $this->search_terms || $this->features ) {
			_e( 'No items found.' );
			return;
		}

		$blog_id = get_current_blog_id();
		if ( is_multisite() ) {
			if ( current_user_can( 'install_myskins' ) && current_user_can( 'manage_network_myskins' ) ) {
				printf( __( 'You only have one myskin enabled for this site right now. Visit the Network Admin to <a href="%1$s">enable</a> or <a href="%2$s">install</a> more myskins.' ), network_admin_url( 'site-myskins.php?id=' . $blog_id ), network_admin_url( 'myskin-install.php' ) );

				return;
			} elseif ( current_user_can( 'manage_network_myskins' ) ) {
				printf( __( 'You only have one myskin enabled for this site right now. Visit the Network Admin to <a href="%1$s">enable</a> more myskins.' ), network_admin_url( 'site-myskins.php?id=' . $blog_id ) );

				return;
			}
			// Else, fallthrough. install_myskins doesn't help if you can't enable it.
		} else {
			if ( current_user_can( 'install_myskins' ) ) {
				printf( __( 'You only have one myskin installed right now. Live a little! You can choose from over 1,000 free myskins in the MandarinCMS MySkin Directory at any time: just click on the <a href="%s">Install MySkins</a> tab above.' ), admin_url( 'myskin-install.php' ) );

				return;
			}
		}
		// Fallthrough.
		printf( __( 'Only the current myskin is available to you. Contact the %s administrator for information about accessing additional myskins.' ), get_site_option( 'site_name' ) );
	}

	/**
	 * @param string $which
	 */
	public function tablenav( $which = 'top' ) {
		if ( $this->get_pagination_arg( 'total_pages' ) <= 1 )
			return;
		?>
		<div class="tablenav myskins <?php echo $which; ?>">
			<?php $this->pagination( $which ); ?>
			<span class="spinner"></span>
			<br class="clear" />
		</div>
		<?php
	}

	/**
	 */
	public function display() {
		mcms_nonce_field( "fetch-list-" . get_class( $this ), '_ajax_fetch_list_nonce' );
?>
		<?php $this->tablenav( 'top' ); ?>

		<div id="availablemyskins">
			<?php $this->display_rows_or_placeholder(); ?>
		</div>

		<?php $this->tablenav( 'bottom' ); ?>
<?php
	}

	/**
	 *
	 * @return array
	 */
	public function get_columns() {
		return array();
	}

	/**
	 */
	public function display_rows_or_placeholder() {
		if ( $this->has_items() ) {
			$this->display_rows();
		} else {
			echo '<div class="no-items">';
			$this->no_items();
			echo '</div>';
		}
	}

	/**
	 */
	public function display_rows() {
		$myskins = $this->items;

		foreach ( $myskins as $myskin ):
			?><div class="available-myskin"><?php

			$template   = $myskin->get_template();
			$stylesheet = $myskin->get_stylesheet();
			$title      = $myskin->display('Name');
			$version    = $myskin->display('Version');
			$author     = $myskin->display('Author');

			$activate_link = mcms_nonce_url( "myskins.php?action=activate&amp;template=" . urlencode( $template ) . "&amp;stylesheet=" . urlencode( $stylesheet ), 'switch-myskin_' . $stylesheet );

			$actions = array();
			$actions['activate'] = '<a href="' . $activate_link . '" class="activatelink" title="'
				. esc_attr( sprintf( __( 'Activate &#8220;%s&#8221;' ), $title ) ) . '">' . __( 'Activate' ) . '</a>';

			if ( current_user_can( 'edit_myskin_options' ) && current_user_can( 'customize' ) ) {
				$actions['preview'] .= '<a href="' . mcms_customize_url( $stylesheet ) . '" class="load-customize hide-if-no-customize">'
					. __( 'Live Preview' ) . '</a>';
			}

			if ( ! is_multisite() && current_user_can( 'delete_myskins' ) )
				$actions['delete'] = '<a class="submitdelete deletion" href="' . mcms_nonce_url( 'myskins.php?action=delete&amp;stylesheet=' . urlencode( $stylesheet ), 'delete-myskin_' . $stylesheet )
					. '" onclick="' . "return confirm( '" . esc_js( sprintf( __( "You are about to delete this myskin '%s'\n  'Cancel' to stop, 'OK' to delete." ), $title ) )
					. "' );" . '">' . __( 'Delete' ) . '</a>';

			/** This filter is documented in mcms-admin/includes/class-mcms-ms-myskins-list-table.php */
			$actions       = apply_filters( 'myskin_action_links', $actions, $myskin, 'all' );

			/** This filter is documented in mcms-admin/includes/class-mcms-ms-myskins-list-table.php */
			$actions       = apply_filters( "myskin_action_links_$stylesheet", $actions, $myskin, 'all' );
			$delete_action = isset( $actions['delete'] ) ? '<div class="delete-myskin">' . $actions['delete'] . '</div>' : '';
			unset( $actions['delete'] );

			?>

			<span class="screenshot hide-if-customize">
				<?php if ( $screenshot = $myskin->get_screenshot() ) : ?>
					<img src="<?php echo esc_url( $screenshot ); ?>" alt="" />
				<?php endif; ?>
			</span>
			<a href="<?php echo mcms_customize_url( $stylesheet ); ?>" class="screenshot load-customize hide-if-no-customize">
				<?php if ( $screenshot = $myskin->get_screenshot() ) : ?>
					<img src="<?php echo esc_url( $screenshot ); ?>" alt="" />
				<?php endif; ?>
			</a>

			<h3><?php echo $title; ?></h3>
			<div class="myskin-author"><?php printf( __( 'By %s' ), $author ); ?></div>
			<div class="action-links">
				<ul>
					<?php foreach ( $actions as $action ): ?>
						<li><?php echo $action; ?></li>
					<?php endforeach; ?>
					<li class="hide-if-no-js"><a href="#" class="myskin-detail"><?php _e('Details') ?></a></li>
				</ul>
				<?php echo $delete_action; ?>

				<?php myskin_update_available( $myskin ); ?>
			</div>

			<div class="myskindetaildiv hide-if-js">
				<p><strong><?php _e('Version:'); ?></strong> <?php echo $version; ?></p>
				<p><?php echo $myskin->display('Description'); ?></p>
				<?php if ( $myskin->parent() ) {
					printf( ' <p class="howto">' . __( 'This <a href="%1$s">child myskin</a> requires its parent myskin, %2$s.' ) . '</p>',
						__( 'https://dev.mandarincms.com/Child_MySkins' ),
						$myskin->parent()->display( 'Name' ) );
				} ?>
			</div>

			</div>
		<?php
		endforeach;
	}

	/**
	 * @param MCMS_MySkin $myskin
	 * @return bool
	 */
	public function search_myskin( $myskin ) {
		// Search the features
		foreach ( $this->features as $word ) {
			if ( ! in_array( $word, $myskin->get('Tags') ) )
				return false;
		}

		// Match all phrases
		foreach ( $this->search_terms as $word ) {
			if ( in_array( $word, $myskin->get('Tags') ) )
				continue;

			foreach ( array( 'Name', 'Description', 'Author', 'AuthorURI' ) as $header ) {
				// Don't mark up; Do translate.
				if ( false !== stripos( strip_tags( $myskin->display( $header, false, true ) ), $word ) ) {
					continue 2;
				}
			}

			if ( false !== stripos( $myskin->get_stylesheet(), $word ) )
				continue;

			if ( false !== stripos( $myskin->get_template(), $word ) )
				continue;

			return false;
		}

		return true;
	}

	/**
	 * Send required variables to JavaScript land
	 *
	 * @since 3.4.0
	 *
	 * @param array $extra_args
	 */
	public function _js_vars( $extra_args = array() ) {
		$search_string = isset( $_REQUEST['s'] ) ? esc_attr( mcms_unslash( $_REQUEST['s'] ) ) : '';

		$args = array(
			'search' => $search_string,
			'features' => $this->features,
			'paged' => $this->get_pagenum(),
			'total_pages' => ! empty( $this->_pagination_args['total_pages'] ) ? $this->_pagination_args['total_pages'] : 1,
		);

		if ( is_array( $extra_args ) )
			$args = array_merge( $args, $extra_args );

		printf( "<script type='text/javascript'>var myskin_list_args = %s;</script>\n", mcms_json_encode( $args ) );
		parent::_js_vars();
	}
}
