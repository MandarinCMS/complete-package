<?php
/**
 * Helper functions for displaying a list of items in an ajaxified HTML table.
 *
 * @package MandarinCMS
 * @subpackage List_Table
 * @since 3.1.0
 */

/**
 * Fetch an instance of a MCMS_List_Table class.
 *
 * @access private
 * @since 3.1.0
 *
 * @global string $hook_suffix
 *
 * @param string $class The type of the list table, which is the class name.
 * @param array $args Optional. Arguments to pass to the class. Accepts 'screen'.
 * @return object|bool Object on success, false if the class does not exist.
 */
function _get_list_table( $class, $args = array() ) {
	$core_classes = array(
		//Site Admin
		'MCMS_Posts_List_Table' => 'posts',
		'MCMS_Media_List_Table' => 'media',
		'MCMS_Terms_List_Table' => 'terms',
		'MCMS_Users_List_Table' => 'users',
		'MCMS_Comments_List_Table' => 'comments',
		'MCMS_Post_Comments_List_Table' => array( 'comments', 'post-comments' ),
		'MCMS_Links_List_Table' => 'links',
		'MCMS_Module_Install_List_Table' => 'module-install',
		'MCMS_MySkins_List_Table' => 'myskins',
		'MCMS_MySkin_Install_List_Table' => array( 'myskins', 'myskin-install' ),
		'MCMS_Modules_List_Table' => 'modules',
		// Network Admin
		'MCMS_MS_Sites_List_Table' => 'ms-sites',
		'MCMS_MS_Users_List_Table' => 'ms-users',
		'MCMS_MS_MySkins_List_Table' => 'ms-myskins',
	);

	if ( isset( $core_classes[ $class ] ) ) {
		foreach ( (array) $core_classes[ $class ] as $required )
			require_once( BASED_TREE_URI . 'mcms-admin/includes/class-mcms-' . $required . '-list-table.php' );

		if ( isset( $args['screen'] ) )
			$args['screen'] = convert_to_screen( $args['screen'] );
		elseif ( isset( $GLOBALS['hook_suffix'] ) )
			$args['screen'] = get_current_screen();
		else
			$args['screen'] = null;

		return new $class( $args );
	}

	return false;
}

/**
 * Register column headers for a particular screen.
 *
 * @since 2.7.0
 *
 * @param string $screen The handle for the screen to add help to. This is usually the hook name returned by the add_*_page() functions.
 * @param array $columns An array of columns with column IDs as the keys and translated column names as the values
 * @see get_column_headers(), print_column_headers(), get_hidden_columns()
 */
function register_column_headers($screen, $columns) {
	new _MCMS_List_Table_Compat( $screen, $columns );
}

/**
 * Prints column headers for a particular screen.
 *
 * @since 2.7.0
 *
 * @param string|MCMS_Screen $screen  The screen hook name or screen object.
 * @param bool             $with_id Whether to set the id attribute or not.
 */
function print_column_headers( $screen, $with_id = true ) {
	$mcms_list_table = new _MCMS_List_Table_Compat($screen);

	$mcms_list_table->print_column_headers( $with_id );
}
