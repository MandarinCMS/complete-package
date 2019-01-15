<?php
/**
 * @package MCMSSEO\Admin
 */

if ( ! defined( 'MCMSSEO_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

/**
 * @todo [JRF => testers] Extensively test the export & import of the (new) settings!
 * If that all works fine, getting testers to export before and after upgrade will make testing easier.
 *
 * @todo [Ultimatum] The import for the RSS Footer module checks for data already entered via Ultimatum SEO,
 * the other import routines should do that too.
 */

$yform = Ultimatum_Form::get_instance();

$replace = false;

/**
 * The import method is used to dermine if there should be something imported.
 *
 * In case of POST the user is on the Ultimatum SEO import page and in case of the GET the user sees a notice from
 * Ultimatum SEO that we can import stuff for that module.
 */
if ( filter_input( INPUT_POST, 'import' ) || filter_input( INPUT_GET, 'import' ) ) {

	check_admin_referer( 'mcmsseo-import' );

	$post_mcmsseo = filter_input( INPUT_POST, 'mcmsseo', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
	$replace    = ( ! empty( $post_mcmsseo['deleteolddata'] ) && $post_mcmsseo['deleteolddata'] === 'on' );

	if ( ! empty( $post_mcmsseo['importwoo'] ) ) {
		$import = new MCMSSEO_Import_WooMySkins_SEO( $replace );
	}

	if ( ! empty( $post_mcmsseo['importaioseo'] ) || filter_input( INPUT_GET, 'importaioseo' ) ) {
		$import = new MCMSSEO_Import_AIOSEO( $replace );
	}

	if ( ! empty( $post_mcmsseo['importheadspace'] ) ) {
		$import = new MCMSSEO_Import_External( $replace );
		$import->import_headspace();
	}

	if ( ! empty( $post_mcmsseo['importmcmsseo'] ) || filter_input( INPUT_GET, 'importmcmsseo' ) ) {
		$import = new MCMSSEO_Import_MCMSSEO( $replace );
	}

	// Allow custom import actions.
	do_action( 'mcmsseo_handle_import' );

}

if ( isset( $_FILES['settings_import_file'] ) ) {
	check_admin_referer( 'mcmsseo-import-file' );

	$import = new MCMSSEO_Import();
}

if ( isset( $import ) ) {
	/**
	 * Allow customization of import&export message
	 *
	 * @api  string  $msg  The message.
	 */
	$msg = apply_filters( 'mcmsseo_import_message', $import->msg );

	// Check if we've deleted old data and adjust message to match it.
	if ( $replace ) {
		$msg .= ' ' . __( 'The old data of the imported module was deleted successfully.', 'mandarincms-seo' );
	}

	if ( $msg != '' ) {

		$status = ( $import->success ) ? 'updated' : 'error';

		echo '<div id="message" class="message ', $status, '"><p>', $msg, '</p></div>';
	}
}

$tabs = array(
	'mcmsseo-import' => array(
		'label'                => __( 'Import', 'mandarincms-seo' ),
		'screencast_video_url' => 'https://jiiworks.net/screencast-tools-import-export',
	),
	'mcmsseo-export' => array(
		'label'                => __( 'Export', 'mandarincms-seo' ),
		'screencast_video_url' => 'https://jiiworks.net/screencast-tools-import-export',
	),
	'import-seo'   => array(
		'label'                => __( 'Import from other SEO modules', 'mandarincms-seo' ),
		'screencast_video_url' => 'https://jiiworks.net/screencast-tools-import-export',
	),
);

?>
	<br/><br/>

	<h2 class="nav-tab-wrapper" id="mcmsseo-tabs">
		<?php foreach ( $tabs as $identifier => $tab ) : ?>
		<a class="nav-tab" id="<?php echo $identifier; ?>-tab" href="#top#<?php echo $identifier; ?>"><?php echo $tab['label']; ?></a>
		<?php endforeach; ?>

		<?php
		/**
		 * Allow adding a custom import tab header
		 */
		do_action( 'mcmsseo_import_tab_header' );
		?>
	</h2>

<?php
foreach ( $tabs as $identifier => $tab ) {

	printf( '<div id="%s" class="mcmsseotab">', $identifier );

	if ( ! empty( $tab['screencast_video_url'] ) ) {
		$tab_video_url = $tab['screencast_video_url'];

		$helpcenter_tab = new MCMSSEO_Option_Tab( $identifier, $tab['label'],
			array( 'video_url' => $tab['screencast_video_url'] ) );

		$helpcenter = new MCMSSEO_Help_Center( $identifier, $helpcenter_tab );
		$helpcenter->output_help_center();
	}

	require_once MCMSSEO_PATH . 'admin/views/tabs/tool/' . $identifier . '.php';

	echo '</div>';
}

/**
 * Allow adding a custom import tab
 */
do_action( 'mcmsseo_import_tab_content' );
