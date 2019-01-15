<?php
/**
 * Install module administration panel.
 *
 * @package MandarinCMS
 * @subpackage Administration
 */
// TODO route this pages via a specific iframe handler instead of the do_action below
if ( !defined( 'IFRAME_REQUEST' ) && isset( $_GET['tab'] ) && ( 'module-information' == $_GET['tab'] ) )
	define( 'IFRAME_REQUEST', true );

/**
 * MandarinCMS Administration Bootstrap.
 */
require_once( dirname( __FILE__ ) . '/admin.php' );

if ( ! current_user_can('install_modules') )
	mcms_die(__('Sorry, you are not allowed to install modules on this site.'));

if ( is_multisite() && ! is_network_admin() ) {
	mcms_redirect( network_admin_url( 'module-install.php' ) );
	exit();
}

$mcms_list_table = _get_list_table('MCMS_Module_Install_List_Table');
$pagenum = $mcms_list_table->get_pagenum();

if ( ! empty( $_REQUEST['_mcms_http_referer'] ) ) {
	$location = remove_query_arg( '_mcms_http_referer', mcms_unslash( $_SERVER['REQUEST_URI'] ) );

	if ( ! empty( $_REQUEST['paged'] ) ) {
		$location = add_query_arg( 'paged', (int) $_REQUEST['paged'], $location );
	}

	mcms_redirect( $location );
	exit;
}

$mcms_list_table->prepare_items();

$total_pages = $mcms_list_table->get_pagination_arg( 'total_pages' );

if ( $pagenum > $total_pages && $total_pages > 0 ) {
	mcms_redirect( add_query_arg( 'paged', $total_pages ) );
	exit;
}

$title = __( 'Add Modules' );
$parent_file = 'modules.php';

mcms_enqueue_script( 'module-install' );
if ( 'module-information' != $tab )
	add_thickbox();

$body_id = $tab;

mcms_enqueue_script( 'updates' );

/**
 * Fires before each tab on the Install Modules screen is loaded.
 *
 * The dynamic portion of the action hook, `$tab`, allows for targeting
 * individual tabs, for instance 'install_modules_pre_module-information'.
 *
 * @since 2.7.0
 */
do_action( "install_modules_pre_{$tab}" );

/*
 * Call the pre upload action on every non-upload module installation screen
 * because the form is always displayed on these screens.
 */
if ( 'upload' !== $tab ) {
	/** This action is documented in mcms-admin/module-install.php */
	do_action( 'install_modules_pre_upload' );
}

get_current_screen()->add_help_tab( array(
'id'		=> 'overview',
'title'		=> __('Overview'),
'content'	=>
	'<p>' . sprintf( __('Modules hook into MandarinCMS to extend its functionality with custom features. Modules are developed independently from the core MandarinCMS application by thousands of developers all over the world. All modules in the official <a href="%s">MandarinCMS Module Directory</a> are compatible with the license MandarinCMS uses.' ), __( 'https://mandarincms.com/modules/' ) ) . '</p>' .
	'<p>' . __( 'You can find new modules to install by searching or browsing the directory right here in your own Modules section.' ) . ' <span id="live-search-desc" class="hide-if-no-js">' . __( 'The search results will be updated as you type.' ) . '</span></p>'

) );
get_current_screen()->add_help_tab( array(
'id'		=> 'adding-modules',
'title'		=> __('Adding Modules'),
'content'	=>
	'<p>' . __('If you know what you&#8217;re looking for, Search is your best bet. The Search screen has options to search the MandarinCMS Module Directory for a particular Term, Author, or Tag. You can also search the directory by selecting popular tags. Tags in larger type mean more modules have been labeled with that tag.') . '</p>' .
	'<p>' . __( 'If you just want to get an idea of what&#8217;s available, you can browse Featured and Popular modules by using the links above the modules list. These sections rotate regularly.' ) . '</p>' .
	'<p>' . __( 'You can also browse a user&#8217;s favorite modules, by using the Favorites link above the modules list and entering their MandarinCMS.org username.' ) . '</p>' .
	'<p>' . __( 'If you want to install a module that you&#8217;ve downloaded elsewhere, click the Upload Module button above the modules list. You will be prompted to upload the .zip package, and once uploaded, you can activate the new module.' ) . '</p>'
) );

get_current_screen()->set_help_sidebar(
	'<p><strong>' . __('For more information:') . '</strong></p>' .
	'<p>' . __('<a href="https://dev.mandarincms.com/Modules_Add_New_Screen">Documentation on Installing Modules</a>') . '</p>' .
	'<p>' . __('<a href="https://mandarincms.com/support/">Support Forums</a>') . '</p>'
);

get_current_screen()->set_screen_reader_content( array(
	'heading_views'      => __( 'Filter modules list' ),
	'heading_pagination' => __( 'Modules list navigation' ),
	'heading_list'       => __( 'Modules list' ),
) );

/**
 * MandarinCMS Administration Template Header.
 */
include(BASED_TREE_URI . 'mcms-admin/admin-header.php');
?>
<div class="wrap <?php echo esc_attr( "module-install-tab-$tab" ); ?>">
<h1 class="mcms-heading-inline"><?php
echo esc_html( $title );
?></h1>

<?php
if ( ! empty( $tabs['upload'] ) && current_user_can( 'upload_modules' ) ) {
	printf( ' <a href="%s" class="upload-view-toggle page-title-action"><span class="upload">%s</span><span class="browse">%s</span></a>',
		( 'upload' === $tab ) ? self_admin_url( 'module-install.php' ) : self_admin_url( 'module-install.php?tab=upload' ),
		__( 'Upload Module' ),
		__( 'Browse Modules' )
	);
}
?>

<hr class="mcms-header-end">

<?php
/*
 * Output the upload module form on every non-upload module installation screen, so it can be
 * displayed via JavaScript rather then opening up the devoted upload module page.
 */
if ( 'upload' !== $tab ) {
	?>
	<div class="upload-module-wrap">
		<?php
		/** This action is documented in mcms-admin/module-install.php */
		do_action( 'install_modules_upload' );
		?>
	</div>
	<?php
	$mcms_list_table->views();
	echo '<br class="clear" />';
}

/**
 * Fires after the modules list table in each tab of the Install Modules screen.
 *
 * The dynamic portion of the action hook, `$tab`, allows for targeting
 * individual tabs, for instance 'install_modules_module-information'.
 *
 * @since 2.7.0
 *
 * @param int $paged The current page number of the modules list table.
 */
do_action( "install_modules_{$tab}", $paged ); ?>

	<span class="spinner"></span>
</div>

<?php

mcms_print_request_filesystem_credentials_modal();
mcms_print_admin_notice_templates();

/**
 * MandarinCMS Administration Template Footer.
 */
include(BASED_TREE_URI . 'mcms-admin/admin-footer.php');
