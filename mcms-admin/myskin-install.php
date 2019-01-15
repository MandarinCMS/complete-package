<?php
/**
 * Install myskin administration panel.
 *
 * @package MandarinCMS
 * @subpackage Administration
 */

/** MandarinCMS Administration Bootstrap */
require_once( dirname( __FILE__ ) . '/admin.php' );
require( BASED_TREE_URI . 'mcms-admin/includes/myskin-install.php' );

mcms_reset_vars( array( 'tab' ) );

if ( ! current_user_can('install_myskins') )
	mcms_die( __( 'Sorry, you are not allowed to install myskins on this site.' ) );

if ( is_multisite() && ! is_network_admin() ) {
	mcms_redirect( network_admin_url( 'myskin-install.php' ) );
	exit();
}

$title = __( 'Add MySkins' );
$parent_file = 'myskins.php';

if ( ! is_network_admin() ) {
	$submenu_file = 'myskins.php';
}

$installed_myskins = search_myskin_directories();

if ( false === $installed_myskins ) {
	$installed_myskins = array();
}

foreach ( $installed_myskins as $k => $v ) {
	if ( false !== strpos( $k, '/' ) ) {
		unset( $installed_myskins[ $k ] );
	}
}

mcms_localize_script( 'myskin', '_mcmsMySkinSettings', array(
	'myskins'   => false,
	'settings' => array(
		'isInstall'  => true,
		'canInstall' => current_user_can( 'install_myskins' ),
		'installURI' => current_user_can( 'install_myskins' ) ? self_admin_url( 'myskin-install.php' ) : null,
		'adminUrl'   => parse_url( self_admin_url(), PHP_URL_PATH )
	),
	'l10n' => array(
		'addNew'              => __( 'Add New MySkin' ),
		'search'              => __( 'Search MySkins' ),
		'searchPlaceholder'   => __( 'Search myskins...' ), // placeholder (no ellipsis)
		'upload'              => __( 'Upload MySkin' ),
		'back'                => __( 'Back' ),
		'error'               => sprintf(
			/* translators: %s: support forums URL */
			__( 'An unexpected error occurred. Something may be wrong with MandarinCMS.org or this server&#8217;s configuration. If you continue to have problems, please try the <a href="%s">support forums</a>.' ),
			__( 'https://mandarincms.com/support/' )
		),
		'tryAgain'            => __( 'Try Again' ),
		'myskinsFound'         => __( 'Number of MySkins found: %d' ),
		'noMySkinsFound'       => __( 'No myskins found. Try a different search.' ),
		'collapseSidebar'     => __( 'Collapse Sidebar' ),
		'expandSidebar'       => __( 'Expand Sidebar' ),
		/* translators: accessibility text */
		'selectFeatureFilter' => __( 'Select one or more MySkin features to filter by' ),
	),
	'installedMySkins' => array_keys( $installed_myskins ),
) );

mcms_enqueue_script( 'myskin' );
mcms_enqueue_script( 'updates' );

if ( $tab ) {
	/**
	 * Fires before each of the tabs are rendered on the Install MySkins page.
	 *
	 * The dynamic portion of the hook name, `$tab`, refers to the current
	 * myskin installation tab. Possible values are 'dashboard', 'search', 'upload',
	 * 'featured', 'new', or 'updated'.
	 *
	 * @since 2.8.0
	 */
	do_action( "install_myskins_pre_{$tab}" );
}

$help_overview =
	'<p>' . sprintf(
			/* translators: %s: MySkin Directory URL */
			__( 'You can find additional myskins for your site by using the MySkin Browser/Installer on this screen, which will display myskins from the <a href="%s">MandarinCMS MySkin Directory</a>. These myskins are designed and developed by third parties, are available free of charge, and are compatible with the license MandarinCMS uses.' ),
			__( 'https://mandarincms.com/myskins/' )
		) . '</p>' .
	'<p>' . __( 'You can Search for myskins by keyword, author, or tag, or can get more specific and search by criteria listed in the feature filter.' ) . ' <span id="live-search-desc">' . __( 'The search results will be updated as you type.' ) . '</span></p>' .
	'<p>' . __( 'Alternately, you can browse the myskins that are Featured, Popular, or Latest. When you find a myskin you like, you can preview it or install it.' ) . '</p>' .
	'<p>' . sprintf(
			/* translators: %s: /mcms-plugins/myskins */
			__( 'You can Upload a myskin manually if you have already downloaded its ZIP archive onto your computer (make sure it is from a trusted and original source). You can also do it the old-fashioned way and copy a downloaded myskin&#8217;s folder via FTP into your %s directory.' ),
			'<code>/mcms-plugins/myskins</code>'
		) . '</p>';

get_current_screen()->add_help_tab( array(
	'id'      => 'overview',
	'title'   => __('Overview'),
	'content' => $help_overview
) );

$help_installing =
	'<p>' . __('Once you have generated a list of myskins, you can preview and install any of them. Click on the thumbnail of the myskin you&#8217;re interested in previewing. It will open up in a full-screen Preview page to give you a better idea of how that myskin will look.') . '</p>' .
	'<p>' . __('To install the myskin so you can preview it with your site&#8217;s content and customize its myskin options, click the "Install" button at the top of the left-hand pane. The myskin files will be downloaded to your website automatically. When this is complete, the myskin is now available for activation, which you can do by clicking the "Activate" link, or by navigating to your Manage MySkins screen and clicking the "Live Preview" link under any installed myskin&#8217;s thumbnail image.') . '</p>';

get_current_screen()->add_help_tab( array(
	'id'      => 'installing',
	'title'   => __('Previewing and Installing'),
	'content' => $help_installing
) );

get_current_screen()->set_help_sidebar(
	'<p><strong>' . __('For more information:') . '</strong></p>' .
	'<p>' . __('<a href="https://dev.mandarincms.com/Using_MySkins#Adding_New_MySkins">Documentation on Adding New MySkins</a>') . '</p>' .
	'<p>' . __('<a href="https://mandarincms.com/support/">Support Forums</a>') . '</p>'
);

include(BASED_TREE_URI . 'mcms-admin/admin-header.php');

?>
<div class="wrap">
	<h1 class="mcms-heading-inline"><?php echo esc_html( $title ); ?></h1>

	<?php

	/**
	 * Filters the tabs shown on the Add MySkins screen.
	 *
	 * This filter is for backward compatibility only, for the suppression of the upload tab.
	 *
	 * @since 2.8.0
	 *
	 * @param array $tabs The tabs shown on the Add MySkins screen. Default is 'upload'.
	 */
	$tabs = apply_filters( 'install_myskins_tabs', array( 'upload' => __( 'Upload MySkin' ) ) );
	if ( ! empty( $tabs['upload'] ) && current_user_can( 'upload_myskins' ) ) {
		echo ' <button type="button" class="upload-view-toggle page-title-action hide-if-no-js" aria-expanded="false">' . __( 'Upload MySkin' ) . '</button>';
	}
	?>

	<hr class="mcms-header-end">

	<div class="error hide-if-js">
		<p><?php _e( 'The MySkin Installer screen requires JavaScript.' ); ?></p>
	</div>

	<div class="upload-myskin">
	<?php install_myskins_upload(); ?>
	</div>

	<h2 class="screen-reader-text hide-if-no-js"><?php _e( 'Filter myskins list' ); ?></h2>

	<div class="mcms-filter hide-if-no-js">
		<div class="filter-count">
			<span class="count myskin-count"></span>
		</div>

		<ul class="filter-links">
			<li><a href="#" data-sort="featured"><?php _ex( 'Featured', 'myskins' ); ?></a></li>
			<li><a href="#" data-sort="popular"><?php _ex( 'Popular', 'myskins' ); ?></a></li>
			<li><a href="#" data-sort="new"><?php _ex( 'Latest', 'myskins' ); ?></a></li>
			<li><a href="#" data-sort="favorites"><?php _ex( 'Favorites', 'myskins' ); ?></a></li>
		</ul>

		<button type="button" class="button drawer-toggle" aria-expanded="false"><?php _e( 'Feature Filter' ); ?></button>

		<form class="search-form"></form>

		<div class="favorites-form">
			<?php
			$action = 'save_mcmsorg_username_' . get_current_user_id();
			if ( isset( $_GET['_mcmsnonce'] ) && mcms_verify_nonce( mcms_unslash( $_GET['_mcmsnonce'] ), $action ) ) {
				$user = isset( $_GET['user'] ) ? mcms_unslash( $_GET['user'] ) : get_user_option( 'mcmsorg_favorites' );
				update_user_meta( get_current_user_id(), 'mcmsorg_favorites', $user );
			} else {
				$user = get_user_option( 'mcmsorg_favorites' );
			}
			?>
			<p class="install-help"><?php _e( 'If you have marked myskins as favorites on MandarinCMS.org, you can browse them here.' ); ?></p>

			<p>
				<label for="mcmsorg-username-input"><?php _e( 'Your MandarinCMS.org username:' ); ?></label>
				<input type="hidden" id="mcmsorg-username-nonce" name="_mcmsnonce" value="<?php echo esc_attr( mcms_create_nonce( $action ) ); ?>" />
				<input type="search" id="mcmsorg-username-input" value="<?php echo esc_attr( $user ); ?>" />
				<input type="button" class="button favorites-form-submit" value="<?php esc_attr_e( 'Get Favorites' ); ?>" />
			</p>
		</div>

		<div class="filter-drawer">
			<div class="buttons">
				<button type="button" class="apply-filters button"><?php _e( 'Apply Filters' ); ?><span></span></button>
				<button type="button" class="clear-filters button" aria-label="<?php esc_attr_e( 'Clear current filters' ); ?>"><?php _e( 'Clear' ); ?></button>
			</div>
		<?php
		$feature_list = get_myskin_feature_list( false ); // Use the core list, rather than the .org API, due to inconsistencies and to ensure tags are translated.
		foreach ( $feature_list as $feature_name => $features ) {
			echo '<fieldset class="filter-group">';
			$feature_name = esc_html( $feature_name );
			echo '<legend>' . $feature_name . '</legend>';
			echo '<div class="filter-group-feature">';
			foreach ( $features as $feature => $feature_name ) {
				$feature = esc_attr( $feature );
				echo '<input type="checkbox" id="filter-id-' . $feature . '" value="' . $feature . '" /> ';
				echo '<label for="filter-id-' . $feature . '">' . $feature_name . '</label>';
			}
			echo '</div>';
			echo '</fieldset>';
		}
		?>
			<div class="buttons">
				<button type="button" class="apply-filters button"><?php _e( 'Apply Filters' ); ?><span></span></button>
				<button type="button" class="clear-filters button" aria-label="<?php esc_attr_e( 'Clear current filters' ); ?>"><?php _e( 'Clear' ); ?></button>
			</div>
			<div class="filtered-by">
				<span><?php _e( 'Filtering by:' ); ?></span>
				<div class="tags"></div>
				<button type="button" class="button-link edit-filters"><?php _e( 'Edit Filters' ); ?></button>
			</div>
		</div>
	</div>
	<h2 class="screen-reader-text hide-if-no-js"><?php _e( 'MySkins list' ); ?></h2>
	<div class="myskin-browser content-filterable"></div>
	<div class="myskin-install-overlay mcms-full-overlay expanded"></div>

	<p class="no-myskins"><?php _e( 'No myskins found. Try a different search.' ); ?></p>
	<span class="spinner"></span>

<?php
if ( $tab ) {
	/**
	 * Fires at the top of each of the tabs on the Install MySkins page.
	 *
	 * The dynamic portion of the hook name, `$tab`, refers to the current
	 * myskin installation tab. Possible values are 'dashboard', 'search', 'upload',
	 * 'featured', 'new', or 'updated'.
	 *
	 * @since 2.8.0
	 *
	 * @param int $paged Number of the current page of results being viewed.
	 */
	do_action( "install_myskins_{$tab}", $paged );
}
?>
</div>

<script id="tmpl-myskin" type="text/template">
	<# if ( data.screenshot_url ) { #>
		<div class="myskin-screenshot">
			<img src="{{ data.screenshot_url }}" alt="" />
		</div>
	<# } else { #>
		<div class="myskin-screenshot blank"></div>
	<# } #>
	<span class="more-details"><?php _ex( 'Details &amp; Preview', 'myskin' ); ?></span>
	<div class="myskin-author">
		<?php
		/* translators: %s: MySkin author name */
		printf( __( 'By %s' ), '{{ data.author }}' );
		?>
	</div>

	<div class="myskin-id-container">
		<h3 class="myskin-name">{{ data.name }}</h3>

		<div class="myskin-actions">
			<# if ( data.installed ) { #>
				<?php
				/* translators: %s: MySkin name */
				$aria_label = sprintf( _x( 'Activate %s', 'myskin' ), '{{ data.name }}' );
				?>
				<# if ( data.activate_url ) { #>
					<a class="button button-primary activate" href="{{ data.activate_url }}" aria-label="<?php echo esc_attr( $aria_label ); ?>"><?php _e( 'Activate' ); ?></a>
				<# } #>
				<# if ( data.customize_url ) { #>
					<a class="button load-customize" href="{{ data.customize_url }}"><?php _e( 'Live Preview' ); ?></a>
				<# } else { #>
					<button class="button preview install-myskin-preview"><?php _e( 'Preview' ); ?></button>
				<# } #>
			<# } else { #>
				<?php
				/* translators: %s: MySkin name */
				$aria_label = sprintf( __( 'Install %s' ), '{{ data.name }}' );
				?>
				<a class="button button-primary myskin-install" data-name="{{ data.name }}" data-slug="{{ data.id }}" href="{{ data.install_url }}" aria-label="<?php echo esc_attr( $aria_label ); ?>"><?php _e( 'Install' ); ?></a>
				<button class="button preview install-myskin-preview"><?php _e( 'Preview' ); ?></button>
			<# } #>
		</div>
	</div>

	<# if ( data.installed ) { #>
		<div class="notice notice-success notice-alt"><p><?php _ex( 'Installed', 'myskin' ); ?></p></div>
	<# } #>
</script>

<script id="tmpl-myskin-preview" type="text/template">
	<div class="mcms-full-overlay-sidebar">
		<div class="mcms-full-overlay-header">
			<button class="close-full-overlay"><span class="screen-reader-text"><?php _e( 'Close' ); ?></span></button>
			<button class="previous-myskin"><span class="screen-reader-text"><?php _ex( 'Previous', 'Button label for a myskin' ); ?></span></button>
			<button class="next-myskin"><span class="screen-reader-text"><?php _ex( 'Next', 'Button label for a myskin' ); ?></span></button>
			<# if ( data.installed ) { #>
				<a class="button button-primary activate" href="{{ data.activate_url }}"><?php _e( 'Activate' ); ?></a>
			<# } else { #>
				<a href="{{ data.install_url }}" class="button button-primary myskin-install" data-name="{{ data.name }}" data-slug="{{ data.id }}"><?php _e( 'Install' ); ?></a>
			<# } #>
		</div>
		<div class="mcms-full-overlay-sidebar-content">
			<div class="install-myskin-info">
				<h3 class="myskin-name">{{ data.name }}</h3>
					<span class="myskin-by">
						<?php
						/* translators: %s: MySkin author name */
						printf( __( 'By %s' ), '{{ data.author }}' );
						?>
					</span>

					<img class="myskin-screenshot" src="{{ data.screenshot_url }}" alt="" />

					<div class="myskin-details">
						<# if ( data.rating ) { #>
							<div class="myskin-rating">
								{{{ data.stars }}}
								<span class="num-ratings">({{ data.num_ratings }})</span>
							</div>
						<# } else { #>
							<span class="no-rating"><?php _e( 'This myskin has not been rated yet.' ); ?></span>
						<# } #>
						<div class="myskin-version">
							<?php
							/* translators: %s: MySkin version */
							printf( __( 'Version: %s' ), '{{ data.version }}' );
							?>
						</div>
						<div class="myskin-description">{{{ data.description }}}</div>
					</div>
				</div>
			</div>
			<div class="mcms-full-overlay-footer">
				<button type="button" class="collapse-sidebar button" aria-expanded="true" aria-label="<?php esc_attr_e( 'Collapse Sidebar' ); ?>">
					<span class="collapse-sidebar-arrow"></span>
					<span class="collapse-sidebar-label"><?php _e( 'Collapse' ); ?></span>
				</button>
			</div>
		</div>
		<div class="mcms-full-overlay-main">
		<iframe src="{{ data.preview_url }}" title="<?php esc_attr_e( 'Preview' ); ?>"></iframe>
	</div>
</script>

<?php
mcms_print_request_filesystem_credentials_modal();
mcms_print_admin_notice_templates();

include(BASED_TREE_URI . 'mcms-admin/admin-footer.php');
