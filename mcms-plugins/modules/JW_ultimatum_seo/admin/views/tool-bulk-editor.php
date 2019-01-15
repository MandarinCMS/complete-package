<?php
/**
 * @package MCMSSEO\Admin
 * @since      1.5.0
 */

if ( ! defined( 'MCMSSEO_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

$options = get_option( 'mcmsseo' );

$mcmsseo_bulk_titles_table      = new MCMSSEO_Bulk_Title_Editor_List_Table();
$mcmsseo_bulk_description_table = new MCMSSEO_Bulk_Description_List_Table();

get_current_screen()->set_screen_reader_content( array(
	'heading_views'      => __( 'Filter posts list' ),
	'heading_pagination' => __( 'Posts list navigation' ),
	'heading_list'       => __( 'Posts list' ),
) );

// If type is empty, fill it with value of first tab (title).
$_GET['type'] = ( ! empty( $_GET['type'] ) ) ? $_GET['type'] : 'title';

if ( ! empty( $_REQUEST['_mcms_http_referer'] ) ) {
	mcms_redirect( remove_query_arg( array( '_mcms_http_referer', '_mcmsnonce' ), stripslashes( $_SERVER['REQUEST_URI'] ) ) );
	exit;
}

/**
 * Outputs a help center.
 *
 * @param string $id The id for the tab.
 */
function render_help_center( $id ) {
	$helpcenter_tab = new MCMSSEO_Option_Tab( 'bulk-' . $id, 'Bulk editor',
		array( 'video_url' => 'https://jiiworks.net/screencast-tools-bulk-editor' ) );

	$helpcenter = new MCMSSEO_Help_Center( 'bulk-editor' . $id, $helpcenter_tab );
	$helpcenter->output_help_center();
}

/**
 * Renders a bulk editor tab.
 *
 * @param MCMSSEO_Bulk_List_Table $table The table to render.
 * @param string                $id    The id for the tab.
 */
function get_rendered_tab( $table, $id ) {
	?>
	<div id="<?php echo $id ?>" class="mcmsseotab">
		<?php
		render_help_center( $id );
		$table->show_page();
		?>
	</div>
	<?php
}

?>
<script>
	var mcmsseo_bulk_editor_nonce = '<?php echo mcms_create_nonce( 'mcmsseo-bulk-editor' ); ?>';
</script>

<div class="wrap mcmsseo_table_page">

	<h2 class="nav-tab-wrapper" id="mcmsseo-tabs">
		<a class="nav-tab" id="title-tab" href="#top#title"><?php _e( 'Title', 'mandarincms-seo' ); ?></a>
		<a class="nav-tab" id="description-tab"
		   href="#top#description"><?php _e( 'Description', 'mandarincms-seo' ); ?></a>
	</h2>

	<div class="tabwrapper">
		<?php get_rendered_tab( $mcmsseo_bulk_titles_table, 'title' )?>
		<?php get_rendered_tab( $mcmsseo_bulk_description_table, 'description' )?>
	</div>
</div>
