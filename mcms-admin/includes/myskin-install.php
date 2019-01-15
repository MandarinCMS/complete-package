<?php
/**
 * MandarinCMS MySkin Installation Administration API
 *
 * @package MandarinCMS
 * @subpackage Administration
 */

$myskins_allowedtags = array('a' => array('href' => array(), 'title' => array(), 'target' => array()),
	'abbr' => array('title' => array()), 'acronym' => array('title' => array()),
	'code' => array(), 'pre' => array(), 'em' => array(), 'strong' => array(),
	'div' => array(), 'p' => array(), 'ul' => array(), 'ol' => array(), 'li' => array(),
	'h1' => array(), 'h2' => array(), 'h3' => array(), 'h4' => array(), 'h5' => array(), 'h6' => array(),
	'img' => array('src' => array(), 'class' => array(), 'alt' => array())
);

$myskin_field_defaults = array( 'description' => true, 'sections' => false, 'tested' => true, 'requires' => true,
	'rating' => true, 'downloaded' => true, 'downloadlink' => true, 'last_updated' => true, 'homepage' => true,
	'tags' => true, 'num_ratings' => true
);

/**
 * Retrieve list of MandarinCMS myskin features (aka myskin tags)
 *
 * @since 2.8.0
 *
 * @deprecated since 3.1.0 Use get_myskin_feature_list() instead.
 *
 * @return array
 */
function install_myskins_feature_list() {
	_deprecated_function( __FUNCTION__, '3.1.0', 'get_myskin_feature_list()' );

	if ( !$cache = get_transient( 'mcmsorg_myskin_feature_list' ) )
		set_transient( 'mcmsorg_myskin_feature_list', array(), 3 * HOUR_IN_SECONDS );

	if ( $cache )
		return $cache;

	$feature_list = myskins_api( 'feature_list', array() );
	if ( is_mcms_error( $feature_list ) )
		return array();

	set_transient( 'mcmsorg_myskin_feature_list', $feature_list, 3 * HOUR_IN_SECONDS );

	return $feature_list;
}

/**
 * Display search form for searching myskins.
 *
 * @since 2.8.0
 *
 * @param bool $type_selector
 */
function install_myskin_search_form( $type_selector = true ) {
	$type = isset( $_REQUEST['type'] ) ? mcms_unslash( $_REQUEST['type'] ) : 'term';
	$term = isset( $_REQUEST['s'] ) ? mcms_unslash( $_REQUEST['s'] ) : '';
	if ( ! $type_selector )
		echo '<p class="install-help">' . __( 'Search for myskins by keyword.' ) . '</p>';
	?>
<form id="search-myskins" method="get">
	<input type="hidden" name="tab" value="search" />
	<?php if ( $type_selector ) : ?>
	<label class="screen-reader-text" for="typeselector"><?php _e('Type of search'); ?></label>
	<select	name="type" id="typeselector">
	<option value="term" <?php selected('term', $type) ?>><?php _e('Keyword'); ?></option>
	<option value="author" <?php selected('author', $type) ?>><?php _e('Author'); ?></option>
	<option value="tag" <?php selected('tag', $type) ?>><?php _ex('Tag', 'MySkin Installer'); ?></option>
	</select>
	<label class="screen-reader-text" for="s"><?php
	switch ( $type ) {
		case 'term':
			_e( 'Search by keyword' );
			break;
		case 'author':
			_e( 'Search by author' );
			break;
		case 'tag':
			_e( 'Search by tag' );
			break;
	}
	?></label>
	<?php else : ?>
	<label class="screen-reader-text" for="s"><?php _e('Search by keyword'); ?></label>
	<?php endif; ?>
	<input type="search" name="s" id="s" size="30" value="<?php echo esc_attr($term) ?>" autofocus="autofocus" />
	<?php submit_button( __( 'Search' ), '', 'search', false ); ?>
</form>
<?php
}

/**
 * Display tags filter for myskins.
 *
 * @since 2.8.0
 */
function install_myskins_dashboard() {
	install_myskin_search_form( false );
?>
<h4><?php _e('Feature Filter') ?></h4>
<p class="install-help"><?php _e( 'Find a myskin based on specific features.' ); ?></p>

<form method="get">
	<input type="hidden" name="tab" value="search" />
	<?php
	$feature_list = get_myskin_feature_list();
	echo '<div class="feature-filter">';

	foreach ( (array) $feature_list as $feature_name => $features ) {
		$feature_name = esc_html( $feature_name );
		echo '<div class="feature-name">' . $feature_name . '</div>';

		echo '<ol class="feature-group">';
		foreach ( $features as $feature => $feature_name ) {
			$feature_name = esc_html( $feature_name );
			$feature = esc_attr($feature);
?>

<li>
	<input type="checkbox" name="features[]" id="feature-id-<?php echo $feature; ?>" value="<?php echo $feature; ?>" />
	<label for="feature-id-<?php echo $feature; ?>"><?php echo $feature_name; ?></label>
</li>

<?php	} ?>
</ol>
<br class="clear" />
<?php
	} ?>

</div>
<br class="clear" />
<?php submit_button( __( 'Find MySkins' ), '', 'search' ); ?>
</form>
<?php
}

/**
 * @since 2.8.0
 */
function install_myskins_upload() {
?>
<p class="install-help"><?php _e('If you have a myskin in a .zip format, you may install it by uploading it here.'); ?></p>
<form method="post" enctype="multipart/form-data" class="mcms-upload-form" action="<?php echo self_admin_url('update.php?action=upload-myskin'); ?>">
	<?php mcms_nonce_field( 'myskin-upload' ); ?>
	<label class="screen-reader-text" for="myskinzip"><?php _e( 'MySkin zip file' ); ?></label>
	<input type="file" id="myskinzip" name="myskinzip" />
	<?php submit_button( __( 'Install Now' ), '', 'install-myskin-submit', false ); ?>
</form>
	<?php
}

/**
 * Prints a myskin on the Install MySkins pages.
 *
 * @deprecated 3.4.0
 *
 * @global MCMS_MySkin_Install_List_Table $mcms_list_table
 *
 * @param object $myskin
 */
function display_myskin( $myskin ) {
	_deprecated_function( __FUNCTION__, '3.4.0' );
	global $mcms_list_table;
	if ( ! isset( $mcms_list_table ) ) {
		$mcms_list_table = _get_list_table('MCMS_MySkin_Install_List_Table');
	}
	$mcms_list_table->prepare_items();
	$mcms_list_table->single_row( $myskin );
}

/**
 * Display myskin content based on myskin list.
 *
 * @since 2.8.0
 *
 * @global MCMS_MySkin_Install_List_Table $mcms_list_table
 */
function display_myskins() {
	global $mcms_list_table;

	if ( ! isset( $mcms_list_table ) ) {
		$mcms_list_table = _get_list_table('MCMS_MySkin_Install_List_Table');
	}
	$mcms_list_table->prepare_items();
	$mcms_list_table->display();

}

/**
 * Display myskin information in dialog box form.
 *
 * @since 2.8.0
 *
 * @global MCMS_MySkin_Install_List_Table $mcms_list_table
 */
function install_myskin_information() {
	global $mcms_list_table;

	$myskin = myskins_api( 'myskin_information', array( 'slug' => mcms_unslash( $_REQUEST['myskin'] ) ) );

	if ( is_mcms_error( $myskin ) )
		mcms_die( $myskin );

	iframe_header( __('MySkin Installation') );
	if ( ! isset( $mcms_list_table ) ) {
		$mcms_list_table = _get_list_table('MCMS_MySkin_Install_List_Table');
	}
	$mcms_list_table->myskin_installer_single( $myskin );
	iframe_footer();
	exit;
}
