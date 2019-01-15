<?php
/**
 * Administration API: Core Ajax handlers
 *
 * @package MandarinCMS
 * @subpackage Administration
 * @since 2.1.0
 */

//
// No-privilege Ajax handlers.
//

/**
 * Ajax handler for the Heartbeat API in
 * the no-privilege context.
 *
 * Runs when the user is not logged in.
 *
 * @since 3.6.0
 */
function mcms_ajax_nopriv_heartbeat() {
	$response = array();

	// screen_id is the same as $current_screen->id and the JS global 'pagenow'.
	if ( ! empty($_POST['screen_id']) )
		$screen_id = sanitize_key($_POST['screen_id']);
	else
		$screen_id = 'front';

	if ( ! empty($_POST['data']) ) {
		$data = mcms_unslash( (array) $_POST['data'] );

		/**
		 * Filters Heartbeat Ajax response in no-privilege environments.
		 *
		 * @since 3.6.0
		 *
		 * @param array|object $response  The no-priv Heartbeat response object or array.
		 * @param array        $data      An array of data passed via $_POST.
		 * @param string       $screen_id The screen id.
		 */
		$response = apply_filters( 'heartbeat_nopriv_received', $response, $data, $screen_id );
	}

	/**
	 * Filters Heartbeat Ajax response when no data is passed.
	 *
	 * @since 3.6.0
	 *
	 * @param array|object $response  The Heartbeat response object or array.
	 * @param string       $screen_id The screen id.
	 */
	$response = apply_filters( 'heartbeat_nopriv_send', $response, $screen_id );

	/**
	 * Fires when Heartbeat ticks in no-privilege environments.
	 *
	 * Allows the transport to be easily replaced with long-polling.
	 *
	 * @since 3.6.0
	 *
	 * @param array|object $response  The no-priv Heartbeat response.
	 * @param string       $screen_id The screen id.
	 */
	do_action( 'heartbeat_nopriv_tick', $response, $screen_id );

	// Send the current time according to the server.
	$response['server_time'] = time();

	mcms_send_json($response);
}

//
// GET-based Ajax handlers.
//

/**
 * Ajax handler for fetching a list table.
 *
 * @since 3.1.0
 */
function mcms_ajax_fetch_list() {
	$list_class = $_GET['list_args']['class'];
	check_ajax_referer( "fetch-list-$list_class", '_ajax_fetch_list_nonce' );

	$mcms_list_table = _get_list_table( $list_class, array( 'screen' => $_GET['list_args']['screen']['id'] ) );
	if ( ! $mcms_list_table ) {
		mcms_die( 0 );
	}

	if ( ! $mcms_list_table->ajax_user_can() ) {
		mcms_die( -1 );
	}

	$mcms_list_table->ajax_response();

	mcms_die( 0 );
}

/**
 * Ajax handler for tag search.
 *
 * @since 3.1.0
 */
function mcms_ajax_ajax_tag_search() {
	if ( ! isset( $_GET['tax'] ) ) {
		mcms_die( 0 );
	}

	$taxonomy = sanitize_key( $_GET['tax'] );
	$tax = get_taxonomy( $taxonomy );
	if ( ! $tax ) {
		mcms_die( 0 );
	}

	if ( ! current_user_can( $tax->cap->assign_terms ) ) {
		mcms_die( -1 );
	}

	$s = mcms_unslash( $_GET['q'] );

	$comma = _x( ',', 'tag delimiter' );
	if ( ',' !== $comma )
		$s = str_replace( $comma, ',', $s );
	if ( false !== strpos( $s, ',' ) ) {
		$s = explode( ',', $s );
		$s = $s[count( $s ) - 1];
	}
	$s = trim( $s );

	/**
	 * Filters the minimum number of characters required to fire a tag search via Ajax.
	 *
	 * @since 4.0.0
	 *
	 * @param int         $characters The minimum number of characters required. Default 2.
	 * @param MCMS_Taxonomy $tax        The taxonomy object.
	 * @param string      $s          The search term.
	 */
	$term_search_min_chars = (int) apply_filters( 'term_search_min_chars', 2, $tax, $s );

	/*
	 * Require $term_search_min_chars chars for matching (default: 2)
	 * ensure it's a non-negative, non-zero integer.
	 */
	if ( ( $term_search_min_chars == 0 ) || ( strlen( $s ) < $term_search_min_chars ) ){
		mcms_die();
	}

	$results = get_terms( $taxonomy, array( 'name__like' => $s, 'fields' => 'names', 'hide_empty' => false ) );

	echo join( $results, "\n" );
	mcms_die();
}

/**
 * Ajax handler for compression testing.
 *
 * @since 3.1.0
 */
function mcms_ajax_mcms_compression_test() {
	if ( !current_user_can( 'manage_options' ) )
		mcms_die( -1 );

	if ( ini_get('zlib.output_compression') || 'ob_gzhandler' == ini_get('output_handler') ) {
		update_site_option('can_compress_scripts', 0);
		mcms_die( 0 );
	}

	if ( isset($_GET['test']) ) {
		header( 'Expires: Wed, 11 Jan 1984 05:00:00 GMT' );
		header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
		header( 'Cache-Control: no-cache, must-revalidate, max-age=0' );
		header('Content-Type: application/javascript; charset=UTF-8');
		$force_gzip = ( defined('ENFORCE_GZIP') && ENFORCE_GZIP );
		$test_str = '"mcmsCompressionTest Lorem ipsum dolor sit amet consectetuer mollis sapien urna ut a. Eu nonummy condimentum fringilla tempor pretium platea vel nibh netus Maecenas. Hac molestie amet justo quis pellentesque est ultrices interdum nibh Morbi. Cras mattis pretium Phasellus ante ipsum ipsum ut sociis Suspendisse Lorem. Ante et non molestie. Porta urna Vestibulum egestas id congue nibh eu risus gravida sit. Ac augue auctor Ut et non a elit massa id sodales. Elit eu Nulla at nibh adipiscing mattis lacus mauris at tempus. Netus nibh quis suscipit nec feugiat eget sed lorem et urna. Pellentesque lacus at ut massa consectetuer ligula ut auctor semper Pellentesque. Ut metus massa nibh quam Curabitur molestie nec mauris congue. Volutpat molestie elit justo facilisis neque ac risus Ut nascetur tristique. Vitae sit lorem tellus et quis Phasellus lacus tincidunt nunc Fusce. Pharetra wisi Suspendisse mus sagittis libero lacinia Integer consequat ac Phasellus. Et urna ac cursus tortor aliquam Aliquam amet tellus volutpat Vestibulum. Justo interdum condimentum In augue congue tellus sollicitudin Quisque quis nibh."';

		 if ( 1 == $_GET['test'] ) {
		 	echo $test_str;
		 	mcms_die();
		 } elseif ( 2 == $_GET['test'] ) {
			if ( !isset($_SERVER['HTTP_ACCEPT_ENCODING']) )
				mcms_die( -1 );
			if ( false !== stripos( $_SERVER['HTTP_ACCEPT_ENCODING'], 'deflate') && function_exists('gzdeflate') && ! $force_gzip ) {
				header('Content-Encoding: deflate');
				$out = gzdeflate( $test_str, 1 );
			} elseif ( false !== stripos( $_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') && function_exists('gzencode') ) {
				header('Content-Encoding: gzip');
				$out = gzencode( $test_str, 1 );
			} else {
				mcms_die( -1 );
			}
			echo $out;
			mcms_die();
		} elseif ( 'no' == $_GET['test'] ) {
			check_ajax_referer( 'update_can_compress_scripts' );
			update_site_option('can_compress_scripts', 0);
		} elseif ( 'yes' == $_GET['test'] ) {
			check_ajax_referer( 'update_can_compress_scripts' );
			update_site_option('can_compress_scripts', 1);
		}
	}

	mcms_die( 0 );
}

/**
 * Ajax handler for image editor previews.
 *
 * @since 3.1.0
 */
function mcms_ajax_imgedit_preview() {
	$post_id = intval($_GET['postid']);
	if ( empty($post_id) || !current_user_can('edit_post', $post_id) )
		mcms_die( -1 );

	check_ajax_referer( "image_editor-$post_id" );

	include_once( BASED_TREE_URI . 'mcms-admin/includes/image-edit.php' );
	if ( ! stream_preview_image($post_id) )
		mcms_die( -1 );

	mcms_die();
}

/**
 * Ajax handler for oEmbed caching.
 *
 * @since 3.1.0
 *
 * @global MCMS_Embed $mcms_embed
 */
function mcms_ajax_oembed_cache() {
	$GLOBALS['mcms_embed']->cache_oembed( $_GET['post'] );
	mcms_die( 0 );
}

/**
 * Ajax handler for user autocomplete.
 *
 * @since 3.4.0
 */
function mcms_ajax_autocomplete_user() {
	if ( ! is_multisite() || ! current_user_can( 'promote_users' ) || mcms_is_large_network( 'users' ) )
		mcms_die( -1 );

	/** This filter is documented in mcms-admin/user-new.php */
	if ( ! current_user_can( 'manage_network_users' ) && ! apply_filters( 'autocomplete_users_for_site_admins', false ) )
		mcms_die( -1 );

	$return = array();

	// Check the type of request
	// Current allowed values are `add` and `search`
	if ( isset( $_REQUEST['autocomplete_type'] ) && 'search' === $_REQUEST['autocomplete_type'] ) {
		$type = $_REQUEST['autocomplete_type'];
	} else {
		$type = 'add';
	}

	// Check the desired field for value
	// Current allowed values are `user_email` and `user_login`
	if ( isset( $_REQUEST['autocomplete_field'] ) && 'user_email' === $_REQUEST['autocomplete_field'] ) {
		$field = $_REQUEST['autocomplete_field'];
	} else {
		$field = 'user_login';
	}

	// Exclude current users of this blog
	if ( isset( $_REQUEST['site_id'] ) ) {
		$id = absint( $_REQUEST['site_id'] );
	} else {
		$id = get_current_blog_id();
	}

	$include_blog_users = ( $type == 'search' ? get_users( array( 'blog_id' => $id, 'fields' => 'ID' ) ) : array() );
	$exclude_blog_users = ( $type == 'add' ? get_users( array( 'blog_id' => $id, 'fields' => 'ID' ) ) : array() );

	$users = get_users( array(
		'blog_id' => false,
		'search'  => '*' . $_REQUEST['term'] . '*',
		'include' => $include_blog_users,
		'exclude' => $exclude_blog_users,
		'search_columns' => array( 'user_login', 'user_nicename', 'user_email' ),
	) );

	foreach ( $users as $user ) {
		$return[] = array(
			/* translators: 1: user_login, 2: user_email */
			'label' => sprintf( _x( '%1$s (%2$s)', 'user autocomplete result' ), $user->user_login, $user->user_email ),
			'value' => $user->$field,
		);
	}

	mcms_die( mcms_json_encode( $return ) );
}

/**
 * Handles AJAX requests for community events
 *
 * @since 4.8.0
 */
function mcms_ajax_get_community_events() {
	require_once( BASED_TREE_URI . 'mcms-admin/includes/class-mcms-community-events.php' );

	check_ajax_referer( 'community_events' );

	$search         = isset( $_POST['location'] ) ? mcms_unslash( $_POST['location'] ) : '';
	$timezone       = isset( $_POST['timezone'] ) ? mcms_unslash( $_POST['timezone'] ) : '';
	$user_id        = get_current_user_id();
	$saved_location = get_user_option( 'community-events-location', $user_id );
	$events_client  = new MCMS_Community_Events( $user_id, $saved_location );
	$events         = $events_client->get_events( $search, $timezone );
	$ip_changed     = false;

	if ( is_mcms_error( $events ) ) {
		mcms_send_json_error( array(
			'error' => $events->get_error_message(),
		) );
	} else {
		if ( empty( $saved_location['ip'] ) && ! empty( $events['location']['ip'] ) ) {
			$ip_changed = true;
		} elseif ( isset( $saved_location['ip'] ) && ! empty( $events['location']['ip'] ) && $saved_location['ip'] !== $events['location']['ip'] ) {
			$ip_changed = true;
		}

		/*
		 * The location should only be updated when it changes. The API doesn't always return
		 * a full location; sometimes it's missing the description or country. The location
		 * that was saved during the initial request is known to be good and complete, though.
		 * It should be left in tact until the user explicitly changes it (either by manually
		 * searching for a new location, or by changing their IP address).
		 *
		 * If the location were updated with an incomplete response from the API, then it could
		 * break assumptions that the UI makes (e.g., that there will always be a description
		 * that corresponds to a latitude/longitude location).
		 *
		 * The location is stored network-wide, so that the user doesn't have to set it on each site.
		 */
		if ( $ip_changed || $search ) {
			update_user_option( $user_id, 'community-events-location', $events['location'], true );
		}

		mcms_send_json_success( $events );
	}
}

/**
 * Ajax handler for dashboard widgets.
 *
 * @since 3.4.0
 */
function mcms_ajax_dashboard_widgets() {
	require_once BASED_TREE_URI . 'mcms-admin/includes/dashboard.php';

	$pagenow = $_GET['pagenow'];
	if ( $pagenow === 'dashboard-user' || $pagenow === 'dashboard-network' || $pagenow === 'dashboard' ) {
		set_current_screen( $pagenow );
	}

	switch ( $_GET['widget'] ) {
		case 'dashboard_primary' :
			mcms_dashboard_primary();
			break;
	}
	mcms_die();
}

/**
 * Ajax handler for Customizer preview logged-in status.
 *
 * @since 3.4.0
 */
function mcms_ajax_logged_in() {
	mcms_die( 1 );
}

//
// Ajax helpers.
//

/**
 * Sends back current comment total and new page links if they need to be updated.
 *
 * Contrary to normal success Ajax response ("1"), die with time() on success.
 *
 * @access private
 * @since 2.7.0
 *
 * @param int $comment_id
 * @param int $delta
 */
function _mcms_ajax_delete_comment_response( $comment_id, $delta = -1 ) {
	$total    = isset( $_POST['_total'] )    ? (int) $_POST['_total']    : 0;
	$per_page = isset( $_POST['_per_page'] ) ? (int) $_POST['_per_page'] : 0;
	$page     = isset( $_POST['_page'] )     ? (int) $_POST['_page']     : 0;
	$url      = isset( $_POST['_url'] )      ? esc_url_raw( $_POST['_url'] ) : '';

	// JS didn't send us everything we need to know. Just die with success message
	if ( ! $total || ! $per_page || ! $page || ! $url ) {
		$time           = time();
		$comment        = get_comment( $comment_id );
		$comment_status = '';
		$comment_link   = '';

		if ( $comment ) {
			$comment_status = $comment->comment_approved;
		}

		if ( 1 === (int) $comment_status ) {
			$comment_link = get_comment_link( $comment );
		}

		$counts = mcms_count_comments();

		$x = new MCMS_Ajax_Response( array(
			'what' => 'comment',
			// Here for completeness - not used.
			'id' => $comment_id,
			'supplemental' => array(
				'status' => $comment_status,
				'postId' => $comment ? $comment->comment_post_ID : '',
				'time' => $time,
				'in_moderation' => $counts->moderated,
				'i18n_comments_text' => sprintf(
					_n( '%s Comment', '%s Comments', $counts->approved ),
					number_format_i18n( $counts->approved )
				),
				'i18n_moderation_text' => sprintf(
					_nx( '%s in moderation', '%s in moderation', $counts->moderated, 'comments' ),
					number_format_i18n( $counts->moderated )
				),
				'comment_link' => $comment_link,
			)
		) );
		$x->send();
	}

	$total += $delta;
	if ( $total < 0 )
		$total = 0;

	// Only do the expensive stuff on a page-break, and about 1 other time per page
	if ( 0 == $total % $per_page || 1 == mt_rand( 1, $per_page ) ) {
		$post_id = 0;
		// What type of comment count are we looking for?
		$status = 'all';
		$parsed = parse_url( $url );
		if ( isset( $parsed['query'] ) ) {
			parse_str( $parsed['query'], $query_vars );
			if ( !empty( $query_vars['comment_status'] ) )
				$status = $query_vars['comment_status'];
			if ( !empty( $query_vars['p'] ) )
				$post_id = (int) $query_vars['p'];
			if ( ! empty( $query_vars['comment_type'] ) )
				$type = $query_vars['comment_type'];
		}

		if ( empty( $type ) ) {
			// Only use the comment count if not filtering by a comment_type.
			$comment_count = mcms_count_comments($post_id);

			// We're looking for a known type of comment count.
			if ( isset( $comment_count->$status ) ) {
				$total = $comment_count->$status;
			}
		}
		// Else use the decremented value from above.
	}

	// The time since the last comment count.
	$time = time();
	$comment = get_comment( $comment_id );

	$x = new MCMS_Ajax_Response( array(
		'what' => 'comment',
		// Here for completeness - not used.
		'id' => $comment_id,
		'supplemental' => array(
			'status' => $comment ? $comment->comment_approved : '',
			'postId' => $comment ? $comment->comment_post_ID : '',
			'total_items_i18n' => sprintf( _n( '%s item', '%s items', $total ), number_format_i18n( $total ) ),
			'total_pages' => ceil( $total / $per_page ),
			'total_pages_i18n' => number_format_i18n( ceil( $total / $per_page ) ),
			'total' => $total,
			'time' => $time
		)
	) );
	$x->send();
}

//
// POST-based Ajax handlers.
//

/**
 * Ajax handler for adding a hierarchical term.
 *
 * @access private
 * @since 3.1.0
 */
function _mcms_ajax_add_hierarchical_term() {
	$action = $_POST['action'];
	$taxonomy = get_taxonomy(substr($action, 4));
	check_ajax_referer( $action, '_ajax_nonce-add-' . $taxonomy->name );
	if ( !current_user_can( $taxonomy->cap->edit_terms ) )
		mcms_die( -1 );
	$names = explode(',', $_POST['new'.$taxonomy->name]);
	$parent = isset($_POST['new'.$taxonomy->name.'_parent']) ? (int) $_POST['new'.$taxonomy->name.'_parent'] : 0;
	if ( 0 > $parent )
		$parent = 0;
	if ( $taxonomy->name == 'category' )
		$post_category = isset($_POST['post_category']) ? (array) $_POST['post_category'] : array();
	else
		$post_category = ( isset($_POST['tax_input']) && isset($_POST['tax_input'][$taxonomy->name]) ) ? (array) $_POST['tax_input'][$taxonomy->name] : array();
	$checked_categories = array_map( 'absint', (array) $post_category );
	$popular_ids = mcms_popular_terms_checklist($taxonomy->name, 0, 10, false);

	foreach ( $names as $cat_name ) {
		$cat_name = trim($cat_name);
		$category_nicename = sanitize_title($cat_name);
		if ( '' === $category_nicename )
			continue;

		$cat_id = mcms_insert_term( $cat_name, $taxonomy->name, array( 'parent' => $parent ) );
		if ( ! $cat_id || is_mcms_error( $cat_id ) ) {
			continue;
		} else {
			$cat_id = $cat_id['term_id'];
		}
		$checked_categories[] = $cat_id;
		if ( $parent ) // Do these all at once in a second
			continue;

		ob_start();

		mcms_terms_checklist( 0, array( 'taxonomy' => $taxonomy->name, 'descendants_and_self' => $cat_id, 'selected_cats' => $checked_categories, 'popular_cats' => $popular_ids ));

		$data = ob_get_clean();

		$add = array(
			'what' => $taxonomy->name,
			'id' => $cat_id,
			'data' => str_replace( array("\n", "\t"), '', $data),
			'position' => -1
		);
	}

	if ( $parent ) { // Foncy - replace the parent and all its children
		$parent = get_term( $parent, $taxonomy->name );
		$term_id = $parent->term_id;

		while ( $parent->parent ) { // get the top parent
			$parent = get_term( $parent->parent, $taxonomy->name );
			if ( is_mcms_error( $parent ) )
				break;
			$term_id = $parent->term_id;
		}

		ob_start();

		mcms_terms_checklist( 0, array('taxonomy' => $taxonomy->name, 'descendants_and_self' => $term_id, 'selected_cats' => $checked_categories, 'popular_cats' => $popular_ids));

		$data = ob_get_clean();

		$add = array(
			'what' => $taxonomy->name,
			'id' => $term_id,
			'data' => str_replace( array("\n", "\t"), '', $data),
			'position' => -1
		);
	}

	ob_start();

	mcms_dropdown_categories( array(
		'taxonomy' => $taxonomy->name, 'hide_empty' => 0, 'name' => 'new'.$taxonomy->name.'_parent', 'orderby' => 'name',
		'hierarchical' => 1, 'show_option_none' => '&mdash; '.$taxonomy->labels->parent_item.' &mdash;'
	) );

	$sup = ob_get_clean();

	$add['supplemental'] = array( 'newcat_parent' => $sup );

	$x = new MCMS_Ajax_Response( $add );
	$x->send();
}

/**
 * Ajax handler for deleting a comment.
 *
 * @since 3.1.0
 */
function mcms_ajax_delete_comment() {
	$id = isset( $_POST['id'] ) ? (int) $_POST['id'] : 0;

	if ( !$comment = get_comment( $id ) )
		mcms_die( time() );
	if ( ! current_user_can( 'edit_comment', $comment->comment_ID ) )
		mcms_die( -1 );

	check_ajax_referer( "delete-comment_$id" );
	$status = mcms_get_comment_status( $comment );

	$delta = -1;
	if ( isset($_POST['trash']) && 1 == $_POST['trash'] ) {
		if ( 'trash' == $status )
			mcms_die( time() );
		$r = mcms_trash_comment( $comment );
	} elseif ( isset($_POST['untrash']) && 1 == $_POST['untrash'] ) {
		if ( 'trash' != $status )
			mcms_die( time() );
		$r = mcms_untrash_comment( $comment );
		if ( ! isset( $_POST['comment_status'] ) || $_POST['comment_status'] != 'trash' ) // undo trash, not in trash
			$delta = 1;
	} elseif ( isset($_POST['spam']) && 1 == $_POST['spam'] ) {
		if ( 'spam' == $status )
			mcms_die( time() );
		$r = mcms_spam_comment( $comment );
	} elseif ( isset($_POST['unspam']) && 1 == $_POST['unspam'] ) {
		if ( 'spam' != $status )
			mcms_die( time() );
		$r = mcms_unspam_comment( $comment );
		if ( ! isset( $_POST['comment_status'] ) || $_POST['comment_status'] != 'spam' ) // undo spam, not in spam
			$delta = 1;
	} elseif ( isset($_POST['delete']) && 1 == $_POST['delete'] ) {
		$r = mcms_delete_comment( $comment );
	} else {
		mcms_die( -1 );
	}

	if ( $r ) // Decide if we need to send back '1' or a more complicated response including page links and comment counts
		_mcms_ajax_delete_comment_response( $comment->comment_ID, $delta );
	mcms_die( 0 );
}

/**
 * Ajax handler for deleting a tag.
 *
 * @since 3.1.0
 */
function mcms_ajax_delete_tag() {
	$tag_id = (int) $_POST['tag_ID'];
	check_ajax_referer( "delete-tag_$tag_id" );

	if ( ! current_user_can( 'delete_term', $tag_id ) ) {
		mcms_die( -1 );
	}

	$taxonomy = !empty($_POST['taxonomy']) ? $_POST['taxonomy'] : 'post_tag';
	$tag = get_term( $tag_id, $taxonomy );
	if ( !$tag || is_mcms_error( $tag ) )
		mcms_die( 1 );

	if ( mcms_delete_term($tag_id, $taxonomy))
		mcms_die( 1 );
	else
		mcms_die( 0 );
}

/**
 * Ajax handler for deleting a link.
 *
 * @since 3.1.0
 */
function mcms_ajax_delete_link() {
	$id = isset( $_POST['id'] ) ? (int) $_POST['id'] : 0;

	check_ajax_referer( "delete-bookmark_$id" );
	if ( !current_user_can( 'manage_links' ) )
		mcms_die( -1 );

	$link = get_bookmark( $id );
	if ( !$link || is_mcms_error( $link ) )
		mcms_die( 1 );

	if ( mcms_delete_link( $id ) )
		mcms_die( 1 );
	else
		mcms_die( 0 );
}

/**
 * Ajax handler for deleting meta.
 *
 * @since 3.1.0
 */
function mcms_ajax_delete_meta() {
	$id = isset( $_POST['id'] ) ? (int) $_POST['id'] : 0;

	check_ajax_referer( "delete-meta_$id" );
	if ( !$meta = get_metadata_by_mid( 'post', $id ) )
		mcms_die( 1 );

	if ( is_protected_meta( $meta->meta_key, 'post' ) || ! current_user_can( 'delete_post_meta',  $meta->post_id, $meta->meta_key ) )
		mcms_die( -1 );
	if ( delete_meta( $meta->meta_id ) )
		mcms_die( 1 );
	mcms_die( 0 );
}

/**
 * Ajax handler for deleting a post.
 *
 * @since 3.1.0
 *
 * @param string $action Action to perform.
 */
function mcms_ajax_delete_post( $action ) {
	if ( empty( $action ) )
		$action = 'delete-post';
	$id = isset( $_POST['id'] ) ? (int) $_POST['id'] : 0;

	check_ajax_referer( "{$action}_$id" );
	if ( !current_user_can( 'delete_post', $id ) )
		mcms_die( -1 );

	if ( !get_post( $id ) )
		mcms_die( 1 );

	if ( mcms_delete_post( $id ) )
		mcms_die( 1 );
	else
		mcms_die( 0 );
}

/**
 * Ajax handler for sending a post to the trash.
 *
 * @since 3.1.0
 *
 * @param string $action Action to perform.
 */
function mcms_ajax_trash_post( $action ) {
	if ( empty( $action ) )
		$action = 'trash-post';
	$id = isset( $_POST['id'] ) ? (int) $_POST['id'] : 0;

	check_ajax_referer( "{$action}_$id" );
	if ( !current_user_can( 'delete_post', $id ) )
		mcms_die( -1 );

	if ( !get_post( $id ) )
		mcms_die( 1 );

	if ( 'trash-post' == $action )
		$done = mcms_trash_post( $id );
	else
		$done = mcms_untrash_post( $id );

	if ( $done )
		mcms_die( 1 );

	mcms_die( 0 );
}

/**
 * Ajax handler to restore a post from the trash.
 *
 * @since 3.1.0
 *
 * @param string $action Action to perform.
 */
function mcms_ajax_untrash_post( $action ) {
	if ( empty( $action ) )
		$action = 'untrash-post';
	mcms_ajax_trash_post( $action );
}

/**
 * @since 3.1.0
 *
 * @param string $action
 */
function mcms_ajax_delete_page( $action ) {
	if ( empty( $action ) )
		$action = 'delete-page';
	$id = isset( $_POST['id'] ) ? (int) $_POST['id'] : 0;

	check_ajax_referer( "{$action}_$id" );
	if ( !current_user_can( 'delete_page', $id ) )
		mcms_die( -1 );

	if ( ! get_post( $id ) )
		mcms_die( 1 );

	if ( mcms_delete_post( $id ) )
		mcms_die( 1 );
	else
		mcms_die( 0 );
}

/**
 * Ajax handler to dim a comment.
 *
 * @since 3.1.0
 */
function mcms_ajax_dim_comment() {
	$id = isset( $_POST['id'] ) ? (int) $_POST['id'] : 0;

	if ( !$comment = get_comment( $id ) ) {
		$x = new MCMS_Ajax_Response( array(
			'what' => 'comment',
			'id' => new MCMS_Error('invalid_comment', sprintf(__('Comment %d does not exist'), $id))
		) );
		$x->send();
	}

	if ( ! current_user_can( 'edit_comment', $comment->comment_ID ) && ! current_user_can( 'moderate_comments' ) )
		mcms_die( -1 );

	$current = mcms_get_comment_status( $comment );
	if ( isset( $_POST['new'] ) && $_POST['new'] == $current )
		mcms_die( time() );

	check_ajax_referer( "approve-comment_$id" );
	if ( in_array( $current, array( 'unapproved', 'spam' ) ) ) {
		$result = mcms_set_comment_status( $comment, 'approve', true );
	} else {
		$result = mcms_set_comment_status( $comment, 'hold', true );
	}

	if ( is_mcms_error($result) ) {
		$x = new MCMS_Ajax_Response( array(
			'what' => 'comment',
			'id' => $result
		) );
		$x->send();
	}

	// Decide if we need to send back '1' or a more complicated response including page links and comment counts
	_mcms_ajax_delete_comment_response( $comment->comment_ID );
	mcms_die( 0 );
}

/**
 * Ajax handler for adding a link category.
 *
 * @since 3.1.0
 *
 * @param string $action Action to perform.
 */
function mcms_ajax_add_link_category( $action ) {
	if ( empty( $action ) )
		$action = 'add-link-category';
	check_ajax_referer( $action );
	$tax = get_taxonomy( 'link_category' );
	if ( ! current_user_can( $tax->cap->manage_terms ) ) {
		mcms_die( -1 );
	}
	$names = explode(',', mcms_unslash( $_POST['newcat'] ) );
	$x = new MCMS_Ajax_Response();
	foreach ( $names as $cat_name ) {
		$cat_name = trim($cat_name);
		$slug = sanitize_title($cat_name);
		if ( '' === $slug )
			continue;

		$cat_id = mcms_insert_term( $cat_name, 'link_category' );
		if ( ! $cat_id || is_mcms_error( $cat_id ) ) {
			continue;
		} else {
			$cat_id = $cat_id['term_id'];
		}
		$cat_name = esc_html( $cat_name );
		$x->add( array(
			'what' => 'link-category',
			'id' => $cat_id,
			'data' => "<li id='link-category-$cat_id'><label for='in-link-category-$cat_id' class='selectit'><input value='" . esc_attr($cat_id) . "' type='checkbox' checked='checked' name='link_category[]' id='in-link-category-$cat_id'/> $cat_name</label></li>",
			'position' => -1
		) );
	}
	$x->send();
}

/**
 * Ajax handler to add a tag.
 *
 * @since 3.1.0
 */
function mcms_ajax_add_tag() {
	check_ajax_referer( 'add-tag', '_mcmsnonce_add-tag' );
	$taxonomy = !empty($_POST['taxonomy']) ? $_POST['taxonomy'] : 'post_tag';
	$tax = get_taxonomy($taxonomy);

	if ( !current_user_can( $tax->cap->edit_terms ) )
		mcms_die( -1 );

	$x = new MCMS_Ajax_Response();

	$tag = mcms_insert_term($_POST['tag-name'], $taxonomy, $_POST );

	if ( !$tag || is_mcms_error($tag) || (!$tag = get_term( $tag['term_id'], $taxonomy )) ) {
		$message = __('An error has occurred. Please reload the page and try again.');
		if ( is_mcms_error($tag) && $tag->get_error_message() )
			$message = $tag->get_error_message();

		$x->add( array(
			'what' => 'taxonomy',
			'data' => new MCMS_Error('error', $message )
		) );
		$x->send();
	}

	$mcms_list_table = _get_list_table( 'MCMS_Terms_List_Table', array( 'screen' => $_POST['screen'] ) );

	$level = 0;
	if ( is_taxonomy_hierarchical($taxonomy) ) {
		$level = count( get_ancestors( $tag->term_id, $taxonomy, 'taxonomy' ) );
		ob_start();
		$mcms_list_table->single_row( $tag, $level );
		$noparents = ob_get_clean();
	}

	ob_start();
	$mcms_list_table->single_row( $tag );
	$parents = ob_get_clean();

	$x->add( array(
		'what' => 'taxonomy',
		'supplemental' => compact('parents', 'noparents')
	) );
	$x->add( array(
		'what' => 'term',
		'position' => $level,
		'supplemental' => (array) $tag
	) );
	$x->send();
}

/**
 * Ajax handler for getting a tagcloud.
 *
 * @since 3.1.0
 */
function mcms_ajax_get_tagcloud() {
	if ( ! isset( $_POST['tax'] ) ) {
		mcms_die( 0 );
	}

	$taxonomy = sanitize_key( $_POST['tax'] );
	$tax = get_taxonomy( $taxonomy );
	if ( ! $tax ) {
		mcms_die( 0 );
	}

	if ( ! current_user_can( $tax->cap->assign_terms ) ) {
		mcms_die( -1 );
	}

	$tags = get_terms( $taxonomy, array( 'number' => 45, 'orderby' => 'count', 'order' => 'DESC' ) );

	if ( empty( $tags ) )
		mcms_die( $tax->labels->not_found );

	if ( is_mcms_error( $tags ) )
		mcms_die( $tags->get_error_message() );

	foreach ( $tags as $key => $tag ) {
		$tags[ $key ]->link = '#';
		$tags[ $key ]->id = $tag->term_id;
	}

	// We need raw tag names here, so don't filter the output
	$return = mcms_generate_tag_cloud( $tags, array( 'filter' => 0, 'format' => 'list' ) );

	if ( empty($return) )
		mcms_die( 0 );

	echo $return;

	mcms_die();
}

/**
 * Ajax handler for getting comments.
 *
 * @since 3.1.0
 *
 * @global int           $post_id
 *
 * @param string $action Action to perform.
 */
function mcms_ajax_get_comments( $action ) {
	global $post_id;
	if ( empty( $action ) ) {
		$action = 'get-comments';
	}
	check_ajax_referer( $action );

	if ( empty( $post_id ) && ! empty( $_REQUEST['p'] ) ) {
		$id = absint( $_REQUEST['p'] );
		if ( ! empty( $id ) ) {
			$post_id = $id;
		}
	}

	if ( empty( $post_id ) ) {
		mcms_die( -1 );
	}

	$mcms_list_table = _get_list_table( 'MCMS_Post_Comments_List_Table', array( 'screen' => 'edit-comments' ) );

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		mcms_die( -1 );
	}

	$mcms_list_table->prepare_items();

	if ( ! $mcms_list_table->has_items() ) {
		mcms_die( 1 );
	}

	$x = new MCMS_Ajax_Response();
	ob_start();
	foreach ( $mcms_list_table->items as $comment ) {
		if ( ! current_user_can( 'edit_comment', $comment->comment_ID ) && 0 === $comment->comment_approved )
			continue;
		get_comment( $comment );
		$mcms_list_table->single_row( $comment );
	}
	$comment_list_item = ob_get_clean();

	$x->add( array(
		'what' => 'comments',
		'data' => $comment_list_item
	) );
	$x->send();
}

/**
 * Ajax handler for replying to a comment.
 *
 * @since 3.1.0
 *
 * @param string $action Action to perform.
 */
function mcms_ajax_replyto_comment( $action ) {
	if ( empty( $action ) )
		$action = 'replyto-comment';

	check_ajax_referer( $action, '_ajax_nonce-replyto-comment' );

	$comment_post_ID = (int) $_POST['comment_post_ID'];
	$post = get_post( $comment_post_ID );
	if ( ! $post )
		mcms_die( -1 );

	if ( !current_user_can( 'edit_post', $comment_post_ID ) )
		mcms_die( -1 );

	if ( empty( $post->post_status ) )
		mcms_die( 1 );
	elseif ( in_array($post->post_status, array('draft', 'pending', 'trash') ) )
		mcms_die( __('ERROR: you are replying to a comment on a draft post.') );

	$user = mcms_get_current_user();
	if ( $user->exists() ) {
		$user_ID = $user->ID;
		$comment_author       = mcms_slash( $user->display_name );
		$comment_author_email = mcms_slash( $user->user_email );
		$comment_author_url   = mcms_slash( $user->user_url );
		$comment_content      = trim( $_POST['content'] );
		$comment_type         = isset( $_POST['comment_type'] ) ? trim( $_POST['comment_type'] ) : '';
		if ( current_user_can( 'unfiltered_html' ) ) {
			if ( ! isset( $_POST['_mcms_unfiltered_html_comment'] ) )
				$_POST['_mcms_unfiltered_html_comment'] = '';

			if ( mcms_create_nonce( 'unfiltered-html-comment' ) != $_POST['_mcms_unfiltered_html_comment'] ) {
				kses_remove_filters(); // start with a clean slate
				kses_init_filters(); // set up the filters
			}
		}
	} else {
		mcms_die( __( 'Sorry, you must be logged in to reply to a comment.' ) );
	}

	if ( '' == $comment_content )
		mcms_die( __( 'ERROR: please type a comment.' ) );

	$comment_parent = 0;
	if ( isset( $_POST['comment_ID'] ) )
		$comment_parent = absint( $_POST['comment_ID'] );
	$comment_auto_approved = false;
	$commentdata = compact('comment_post_ID', 'comment_author', 'comment_author_email', 'comment_author_url', 'comment_content', 'comment_type', 'comment_parent', 'user_ID');

	// Automatically approve parent comment.
	if ( !empty($_POST['approve_parent']) ) {
		$parent = get_comment( $comment_parent );

		if ( $parent && $parent->comment_approved === '0' && $parent->comment_post_ID == $comment_post_ID ) {
			if ( ! current_user_can( 'edit_comment', $parent->comment_ID ) ) {
				mcms_die( -1 );
			}

			if ( mcms_set_comment_status( $parent, 'approve' ) )
				$comment_auto_approved = true;
		}
	}

	$comment_id = mcms_new_comment( $commentdata );

	if ( is_mcms_error( $comment_id ) ) {
		mcms_die( $comment_id->get_error_message() );
	}

	$comment = get_comment($comment_id);
	if ( ! $comment ) mcms_die( 1 );

	$position = ( isset($_POST['position']) && (int) $_POST['position'] ) ? (int) $_POST['position'] : '-1';

	ob_start();
	if ( isset( $_REQUEST['mode'] ) && 'dashboard' == $_REQUEST['mode'] ) {
		require_once( BASED_TREE_URI . 'mcms-admin/includes/dashboard.php' );
		_mcms_dashboard_recent_comments_row( $comment );
	} else {
		if ( isset( $_REQUEST['mode'] ) && 'single' == $_REQUEST['mode'] ) {
			$mcms_list_table = _get_list_table('MCMS_Post_Comments_List_Table', array( 'screen' => 'edit-comments' ) );
		} else {
			$mcms_list_table = _get_list_table('MCMS_Comments_List_Table', array( 'screen' => 'edit-comments' ) );
		}
		$mcms_list_table->single_row( $comment );
	}
	$comment_list_item = ob_get_clean();

	$response =  array(
		'what' => 'comment',
		'id' => $comment->comment_ID,
		'data' => $comment_list_item,
		'position' => $position
	);

	$counts = mcms_count_comments();
	$response['supplemental'] = array(
		'in_moderation' => $counts->moderated,
		'i18n_comments_text' => sprintf(
			_n( '%s Comment', '%s Comments', $counts->approved ),
			number_format_i18n( $counts->approved )
		),
		'i18n_moderation_text' => sprintf(
			_nx( '%s in moderation', '%s in moderation', $counts->moderated, 'comments' ),
			number_format_i18n( $counts->moderated )
		)
	);

	if ( $comment_auto_approved ) {
		$response['supplemental']['parent_approved'] = $parent->comment_ID;
		$response['supplemental']['parent_post_id'] = $parent->comment_post_ID;
	}

	$x = new MCMS_Ajax_Response();
	$x->add( $response );
	$x->send();
}

/**
 * Ajax handler for editing a comment.
 *
 * @since 3.1.0
 */
function mcms_ajax_edit_comment() {
	check_ajax_referer( 'replyto-comment', '_ajax_nonce-replyto-comment' );

	$comment_id = (int) $_POST['comment_ID'];
	if ( ! current_user_can( 'edit_comment', $comment_id ) )
		mcms_die( -1 );

	if ( '' == $_POST['content'] )
		mcms_die( __( 'ERROR: please type a comment.' ) );

	if ( isset( $_POST['status'] ) )
		$_POST['comment_status'] = $_POST['status'];
	edit_comment();

	$position = ( isset($_POST['position']) && (int) $_POST['position']) ? (int) $_POST['position'] : '-1';
	$checkbox = ( isset($_POST['checkbox']) && true == $_POST['checkbox'] ) ? 1 : 0;
	$mcms_list_table = _get_list_table( $checkbox ? 'MCMS_Comments_List_Table' : 'MCMS_Post_Comments_List_Table', array( 'screen' => 'edit-comments' ) );

	$comment = get_comment( $comment_id );
	if ( empty( $comment->comment_ID ) )
		mcms_die( -1 );

	ob_start();
	$mcms_list_table->single_row( $comment );
	$comment_list_item = ob_get_clean();

	$x = new MCMS_Ajax_Response();

	$x->add( array(
		'what' => 'edit_comment',
		'id' => $comment->comment_ID,
		'data' => $comment_list_item,
		'position' => $position
	));

	$x->send();
}

/**
 * Ajax handler for adding a menu item.
 *
 * @since 3.1.0
 */
function mcms_ajax_add_menu_item() {
	check_ajax_referer( 'add-menu_item', 'menu-settings-column-nonce' );

	if ( ! current_user_can( 'edit_myskin_options' ) )
		mcms_die( -1 );

	require_once BASED_TREE_URI . 'mcms-admin/includes/nav-menu.php';

	// For performance reasons, we omit some object properties from the checklist.
	// The following is a hacky way to restore them when adding non-custom items.

	$menu_items_data = array();
	foreach ( (array) $_POST['menu-item'] as $menu_item_data ) {
		if (
			! empty( $menu_item_data['menu-item-type'] ) &&
			'custom' != $menu_item_data['menu-item-type'] &&
			! empty( $menu_item_data['menu-item-object-id'] )
		) {
			switch( $menu_item_data['menu-item-type'] ) {
				case 'post_type' :
					$_object = get_post( $menu_item_data['menu-item-object-id'] );
				break;

				case 'post_type_archive' :
					$_object = get_post_type_object( $menu_item_data['menu-item-object'] );
				break;

				case 'taxonomy' :
					$_object = get_term( $menu_item_data['menu-item-object-id'], $menu_item_data['menu-item-object'] );
				break;
			}

			$_menu_items = array_map( 'mcms_setup_nav_menu_item', array( $_object ) );
			$_menu_item = reset( $_menu_items );

			// Restore the missing menu item properties
			$menu_item_data['menu-item-description'] = $_menu_item->description;
		}

		$menu_items_data[] = $menu_item_data;
	}

	$item_ids = mcms_save_nav_menu_items( 0, $menu_items_data );
	if ( is_mcms_error( $item_ids ) )
		mcms_die( 0 );

	$menu_items = array();

	foreach ( (array) $item_ids as $menu_item_id ) {
		$menu_obj = get_post( $menu_item_id );
		if ( ! empty( $menu_obj->ID ) ) {
			$menu_obj = mcms_setup_nav_menu_item( $menu_obj );
			$menu_obj->label = $menu_obj->title; // don't show "(pending)" in ajax-added items
			$menu_items[] = $menu_obj;
		}
	}

	/** This filter is documented in mcms-admin/includes/nav-menu.php */
	$walker_class_name = apply_filters( 'mcms_edit_nav_menu_walker', 'Walker_Nav_Menu_Edit', $_POST['menu'] );

	if ( ! class_exists( $walker_class_name ) )
		mcms_die( 0 );

	if ( ! empty( $menu_items ) ) {
		$args = array(
			'after' => '',
			'before' => '',
			'link_after' => '',
			'link_before' => '',
			'walker' => new $walker_class_name,
		);
		echo walk_nav_menu_tree( $menu_items, 0, (object) $args );
	}
	mcms_die();
}

/**
 * Ajax handler for adding meta.
 *
 * @since 3.1.0
 */
function mcms_ajax_add_meta() {
	check_ajax_referer( 'add-meta', '_ajax_nonce-add-meta' );
	$c = 0;
	$pid = (int) $_POST['post_id'];
	$post = get_post( $pid );

	if ( isset($_POST['metakeyselect']) || isset($_POST['metakeyinput']) ) {
		if ( !current_user_can( 'edit_post', $pid ) )
			mcms_die( -1 );
		if ( isset($_POST['metakeyselect']) && '#NONE#' == $_POST['metakeyselect'] && empty($_POST['metakeyinput']) )
			mcms_die( 1 );

		// If the post is an autodraft, save the post as a draft and then attempt to save the meta.
		if ( $post->post_status == 'auto-draft' ) {
			$post_data = array();
			$post_data['action'] = 'draft'; // Warning fix
			$post_data['post_ID'] = $pid;
			$post_data['post_type'] = $post->post_type;
			$post_data['post_status'] = 'draft';
			$now = current_time('timestamp', 1);
			/* translators: 1: Post creation date, 2: Post creation time */
			$post_data['post_title'] = sprintf( __( 'Draft created on %1$s at %2$s' ), date( __( 'F j, Y' ), $now ), date( __( 'g:i a' ), $now ) );

			$pid = edit_post( $post_data );
			if ( $pid ) {
				if ( is_mcms_error( $pid ) ) {
					$x = new MCMS_Ajax_Response( array(
						'what' => 'meta',
						'data' => $pid
					) );
					$x->send();
				}

				if ( !$mid = add_meta( $pid ) )
					mcms_die( __( 'Please provide a custom field value.' ) );
			} else {
				mcms_die( 0 );
			}
		} elseif ( ! $mid = add_meta( $pid ) ) {
			mcms_die( __( 'Please provide a custom field value.' ) );
		}

		$meta = get_metadata_by_mid( 'post', $mid );
		$pid = (int) $meta->post_id;
		$meta = get_object_vars( $meta );
		$x = new MCMS_Ajax_Response( array(
			'what' => 'meta',
			'id' => $mid,
			'data' => _list_meta_row( $meta, $c ),
			'position' => 1,
			'supplemental' => array('postid' => $pid)
		) );
	} else { // Update?
		$mid = (int) key( $_POST['meta'] );
		$key = mcms_unslash( $_POST['meta'][$mid]['key'] );
		$value = mcms_unslash( $_POST['meta'][$mid]['value'] );
		if ( '' == trim($key) )
			mcms_die( __( 'Please provide a custom field name.' ) );
		if ( '' == trim($value) )
			mcms_die( __( 'Please provide a custom field value.' ) );
		if ( ! $meta = get_metadata_by_mid( 'post', $mid ) )
			mcms_die( 0 ); // if meta doesn't exist
		if ( is_protected_meta( $meta->meta_key, 'post' ) || is_protected_meta( $key, 'post' ) ||
			! current_user_can( 'edit_post_meta', $meta->post_id, $meta->meta_key ) ||
			! current_user_can( 'edit_post_meta', $meta->post_id, $key ) )
			mcms_die( -1 );
		if ( $meta->meta_value != $value || $meta->meta_key != $key ) {
			if ( !$u = update_metadata_by_mid( 'post', $mid, $value, $key ) )
				mcms_die( 0 ); // We know meta exists; we also know it's unchanged (or DB error, in which case there are bigger problems).
		}

		$x = new MCMS_Ajax_Response( array(
			'what' => 'meta',
			'id' => $mid, 'old_id' => $mid,
			'data' => _list_meta_row( array(
				'meta_key' => $key,
				'meta_value' => $value,
				'meta_id' => $mid
			), $c ),
			'position' => 0,
			'supplemental' => array('postid' => $meta->post_id)
		) );
	}
	$x->send();
}

/**
 * Ajax handler for adding a user.
 *
 * @since 3.1.0
 *
 * @param string $action Action to perform.
 */
function mcms_ajax_add_user( $action ) {
	if ( empty( $action ) ) {
		$action = 'add-user';
	}

	check_ajax_referer( $action );
	if ( ! current_user_can('create_users') )
		mcms_die( -1 );
	if ( ! $user_id = edit_user() ) {
		mcms_die( 0 );
	} elseif ( is_mcms_error( $user_id ) ) {
		$x = new MCMS_Ajax_Response( array(
			'what' => 'user',
			'id' => $user_id
		) );
		$x->send();
	}
	$user_object = get_userdata( $user_id );

	$mcms_list_table = _get_list_table('MCMS_Users_List_Table');

	$role = current( $user_object->roles );

	$x = new MCMS_Ajax_Response( array(
		'what' => 'user',
		'id' => $user_id,
		'data' => $mcms_list_table->single_row( $user_object, '', $role ),
		'supplemental' => array(
			'show-link' => sprintf(
				/* translators: %s: the new user */
				__( 'User %s added' ),
				'<a href="#user-' . $user_id . '">' . $user_object->user_login . '</a>'
			),
			'role' => $role,
		)
	) );
	$x->send();
}

/**
 * Ajax handler for closed post boxes.
 *
 * @since 3.1.0
 */
function mcms_ajax_closed_postboxes() {
	check_ajax_referer( 'closedpostboxes', 'closedpostboxesnonce' );
	$closed = isset( $_POST['closed'] ) ? explode( ',', $_POST['closed']) : array();
	$closed = array_filter($closed);

	$hidden = isset( $_POST['hidden'] ) ? explode( ',', $_POST['hidden']) : array();
	$hidden = array_filter($hidden);

	$page = isset( $_POST['page'] ) ? $_POST['page'] : '';

	if ( $page != sanitize_key( $page ) )
		mcms_die( 0 );

	if ( ! $user = mcms_get_current_user() )
		mcms_die( -1 );

	if ( is_array($closed) )
		update_user_option($user->ID, "closedpostboxes_$page", $closed, true);

	if ( is_array($hidden) ) {
		$hidden = array_diff( $hidden, array('submitdiv', 'linksubmitdiv', 'manage-menu', 'create-menu') ); // postboxes that are always shown
		update_user_option($user->ID, "metaboxhidden_$page", $hidden, true);
	}

	mcms_die( 1 );
}

/**
 * Ajax handler for hidden columns.
 *
 * @since 3.1.0
 */
function mcms_ajax_hidden_columns() {
	check_ajax_referer( 'screen-options-nonce', 'screenoptionnonce' );
	$page = isset( $_POST['page'] ) ? $_POST['page'] : '';

	if ( $page != sanitize_key( $page ) )
		mcms_die( 0 );

	if ( ! $user = mcms_get_current_user() )
		mcms_die( -1 );

	$hidden = ! empty( $_POST['hidden'] ) ? explode( ',', $_POST['hidden'] ) : array();
	update_user_option( $user->ID, "manage{$page}columnshidden", $hidden, true );

	mcms_die( 1 );
}

/**
 * Ajax handler for updating whether to display the welcome panel.
 *
 * @since 3.1.0
 */
function mcms_ajax_update_welcome_panel() {
	check_ajax_referer( 'welcome-panel-nonce', 'welcomepanelnonce' );

	if ( ! current_user_can( 'edit_myskin_options' ) )
		mcms_die( -1 );

	update_user_meta( get_current_user_id(), 'show_welcome_panel', empty( $_POST['visible'] ) ? 0 : 1 );

	mcms_die( 1 );
}

/**
 * Ajax handler for retrieving menu meta boxes.
 *
 * @since 3.1.0
 */
function mcms_ajax_menu_get_metabox() {
	if ( ! current_user_can( 'edit_myskin_options' ) )
		mcms_die( -1 );

	require_once BASED_TREE_URI . 'mcms-admin/includes/nav-menu.php';

	if ( isset( $_POST['item-type'] ) && 'post_type' == $_POST['item-type'] ) {
		$type = 'posttype';
		$callback = 'mcms_nav_menu_item_post_type_meta_box';
		$items = (array) get_post_types( array( 'show_in_nav_menus' => true ), 'object' );
	} elseif ( isset( $_POST['item-type'] ) && 'taxonomy' == $_POST['item-type'] ) {
		$type = 'taxonomy';
		$callback = 'mcms_nav_menu_item_taxonomy_meta_box';
		$items = (array) get_taxonomies( array( 'show_ui' => true ), 'object' );
	}

	if ( ! empty( $_POST['item-object'] ) && isset( $items[$_POST['item-object']] ) ) {
		$menus_meta_box_object = $items[ $_POST['item-object'] ];

		/** This filter is documented in mcms-admin/includes/nav-menu.php */
		$item = apply_filters( 'nav_menu_meta_box_object', $menus_meta_box_object );
		ob_start();
		call_user_func_array($callback, array(
			null,
			array(
				'id' => 'add-' . $item->name,
				'title' => $item->labels->name,
				'callback' => $callback,
				'args' => $item,
			)
		));

		$markup = ob_get_clean();

		echo mcms_json_encode(array(
			'replace-id' => $type . '-' . $item->name,
			'markup' => $markup,
		));
	}

	mcms_die();
}

/**
 * Ajax handler for internal linking.
 *
 * @since 3.1.0
 */
function mcms_ajax_mcms_link_ajax() {
	check_ajax_referer( 'internal-linking', '_ajax_linking_nonce' );

	$args = array();

	if ( isset( $_POST['search'] ) ) {
		$args['s'] = mcms_unslash( $_POST['search'] );
	}

	if ( isset( $_POST['term'] ) ) {
		$args['s'] = mcms_unslash( $_POST['term'] );
	}

	$args['pagenum'] = ! empty( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;

	if ( ! class_exists( '_MCMS_Editors', false ) ) {
		require( BASED_TREE_URI . MCMSINC . '/class-mcms-editor.php' );
	}

	$results = _MCMS_Editors::mcms_link_query( $args );

	if ( ! isset( $results ) )
		mcms_die( 0 );

	echo mcms_json_encode( $results );
	echo "\n";

	mcms_die();
}

/**
 * Ajax handler for menu locations save.
 *
 * @since 3.1.0
 */
function mcms_ajax_menu_locations_save() {
	if ( ! current_user_can( 'edit_myskin_options' ) )
		mcms_die( -1 );
	check_ajax_referer( 'add-menu_item', 'menu-settings-column-nonce' );
	if ( ! isset( $_POST['menu-locations'] ) )
		mcms_die( 0 );
	set_myskin_mod( 'nav_menu_locations', array_map( 'absint', $_POST['menu-locations'] ) );
	mcms_die( 1 );
}

/**
 * Ajax handler for saving the meta box order.
 *
 * @since 3.1.0
 */
function mcms_ajax_meta_box_order() {
	check_ajax_referer( 'meta-box-order' );
	$order = isset( $_POST['order'] ) ? (array) $_POST['order'] : false;
	$page_columns = isset( $_POST['page_columns'] ) ? $_POST['page_columns'] : 'auto';

	if ( $page_columns != 'auto' )
		$page_columns = (int) $page_columns;

	$page = isset( $_POST['page'] ) ? $_POST['page'] : '';

	if ( $page != sanitize_key( $page ) )
		mcms_die( 0 );

	if ( ! $user = mcms_get_current_user() )
		mcms_die( -1 );

	if ( $order )
		update_user_option($user->ID, "meta-box-order_$page", $order, true);

	if ( $page_columns )
		update_user_option($user->ID, "screen_layout_$page", $page_columns, true);

	mcms_die( 1 );
}

/**
 * Ajax handler for menu quick searching.
 *
 * @since 3.1.0
 */
function mcms_ajax_menu_quick_search() {
	if ( ! current_user_can( 'edit_myskin_options' ) )
		mcms_die( -1 );

	require_once BASED_TREE_URI . 'mcms-admin/includes/nav-menu.php';

	_mcms_ajax_menu_quick_search( $_POST );

	mcms_die();
}

/**
 * Ajax handler to retrieve a permalink.
 *
 * @since 3.1.0
 */
function mcms_ajax_get_permalink() {
	check_ajax_referer( 'getpermalink', 'getpermalinknonce' );
	$post_id = isset($_POST['post_id'])? intval($_POST['post_id']) : 0;
	mcms_die( get_preview_post_link( $post_id ) );
}

/**
 * Ajax handler to retrieve a sample permalink.
 *
 * @since 3.1.0
 */
function mcms_ajax_sample_permalink() {
	check_ajax_referer( 'samplepermalink', 'samplepermalinknonce' );
	$post_id = isset($_POST['post_id'])? intval($_POST['post_id']) : 0;
	$title = isset($_POST['new_title'])? $_POST['new_title'] : '';
	$slug = isset($_POST['new_slug'])? $_POST['new_slug'] : null;
	mcms_die( get_sample_permalink_html( $post_id, $title, $slug ) );
}

/**
 * Ajax handler for Quick Edit saving a post from a list table.
 *
 * @since 3.1.0
 *
 * @global string $mode List table view mode.
 */
function mcms_ajax_inline_save() {
	global $mode;

	check_ajax_referer( 'inlineeditnonce', '_inline_edit' );

	if ( ! isset($_POST['post_ID']) || ! ( $post_ID = (int) $_POST['post_ID'] ) )
		mcms_die();

	if ( 'page' == $_POST['post_type'] ) {
		if ( ! current_user_can( 'edit_page', $post_ID ) )
			mcms_die( __( 'Sorry, you are not allowed to edit this page.' ) );
	} else {
		if ( ! current_user_can( 'edit_post', $post_ID ) )
			mcms_die( __( 'Sorry, you are not allowed to edit this post.' ) );
	}

	if ( $last = mcms_check_post_lock( $post_ID ) ) {
		$last_user = get_userdata( $last );
		$last_user_name = $last_user ? $last_user->display_name : __( 'Someone' );
		printf( $_POST['post_type'] == 'page' ? __( 'Saving is disabled: %s is currently editing this page.' ) : __( 'Saving is disabled: %s is currently editing this post.' ),	esc_html( $last_user_name ) );
		mcms_die();
	}

	$data = &$_POST;

	$post = get_post( $post_ID, ARRAY_A );

	// Since it's coming from the database.
	$post = mcms_slash($post);

	$data['content'] = $post['post_content'];
	$data['excerpt'] = $post['post_excerpt'];

	// Rename.
	$data['user_ID'] = get_current_user_id();

	if ( isset($data['post_parent']) )
		$data['parent_id'] = $data['post_parent'];

	// Status.
	if ( isset( $data['keep_private'] ) && 'private' == $data['keep_private'] ) {
		$data['visibility']  = 'private';
		$data['post_status'] = 'private';
	} else {
		$data['post_status'] = $data['_status'];
	}

	if ( empty($data['comment_status']) )
		$data['comment_status'] = 'closed';
	if ( empty($data['ping_status']) )
		$data['ping_status'] = 'closed';

	// Exclude terms from taxonomies that are not supposed to appear in Quick Edit.
	if ( ! empty( $data['tax_input'] ) ) {
		foreach ( $data['tax_input'] as $taxonomy => $terms ) {
			$tax_object = get_taxonomy( $taxonomy );
			/** This filter is documented in mcms-admin/includes/class-mcms-posts-list-table.php */
			if ( ! apply_filters( 'quick_edit_show_taxonomy', $tax_object->show_in_quick_edit, $taxonomy, $post['post_type'] ) ) {
				unset( $data['tax_input'][ $taxonomy ] );
			}
		}
	}

	// Hack: mcms_unique_post_slug() doesn't work for drafts, so we will fake that our post is published.
	if ( ! empty( $data['post_name'] ) && in_array( $post['post_status'], array( 'draft', 'pending' ) ) ) {
		$post['post_status'] = 'publish';
		$data['post_name'] = mcms_unique_post_slug( $data['post_name'], $post['ID'], $post['post_status'], $post['post_type'], $post['post_parent'] );
	}

	// Update the post.
	edit_post();

	$mcms_list_table = _get_list_table( 'MCMS_Posts_List_Table', array( 'screen' => $_POST['screen'] ) );

	$mode = $_POST['post_view'] === 'excerpt' ? 'excerpt' : 'list';

	$level = 0;
	if ( is_post_type_hierarchical( $mcms_list_table->screen->post_type ) ) {
		$request_post = array( get_post( $_POST['post_ID'] ) );
		$parent       = $request_post[0]->post_parent;

		while ( $parent > 0 ) {
			$parent_post = get_post( $parent );
			$parent      = $parent_post->post_parent;
			$level++;
		}
	}

	$mcms_list_table->display_rows( array( get_post( $_POST['post_ID'] ) ), $level );

	mcms_die();
}

/**
 * Ajax handler for quick edit saving for a term.
 *
 * @since 3.1.0
 */
function mcms_ajax_inline_save_tax() {
	check_ajax_referer( 'taxinlineeditnonce', '_inline_edit' );

	$taxonomy = sanitize_key( $_POST['taxonomy'] );
	$tax = get_taxonomy( $taxonomy );
	if ( ! $tax )
		mcms_die( 0 );

	if ( ! isset( $_POST['tax_ID'] ) || ! ( $id = (int) $_POST['tax_ID'] ) ) {
		mcms_die( -1 );
	}

	if ( ! current_user_can( 'edit_term', $id ) ) {
		mcms_die( -1 );
	}

	$mcms_list_table = _get_list_table( 'MCMS_Terms_List_Table', array( 'screen' => 'edit-' . $taxonomy ) );

	$tag = get_term( $id, $taxonomy );
	$_POST['description'] = $tag->description;

	$updated = mcms_update_term($id, $taxonomy, $_POST);
	if ( $updated && !is_mcms_error($updated) ) {
		$tag = get_term( $updated['term_id'], $taxonomy );
		if ( !$tag || is_mcms_error( $tag ) ) {
			if ( is_mcms_error($tag) && $tag->get_error_message() )
				mcms_die( $tag->get_error_message() );
			mcms_die( __( 'Item not updated.' ) );
		}
	} else {
		if ( is_mcms_error($updated) && $updated->get_error_message() )
			mcms_die( $updated->get_error_message() );
		mcms_die( __( 'Item not updated.' ) );
	}
	$level = 0;
	$parent = $tag->parent;
	while ( $parent > 0 ) {
		$parent_tag = get_term( $parent, $taxonomy );
		$parent = $parent_tag->parent;
		$level++;
	}
	$mcms_list_table->single_row( $tag, $level );
	mcms_die();
}

/**
 * Ajax handler for querying posts for the Find Posts modal.
 *
 * @see window.findPosts
 *
 * @since 3.1.0
 */
function mcms_ajax_find_posts() {
	check_ajax_referer( 'find-posts' );

	$post_types = get_post_types( array( 'public' => true ), 'objects' );
	unset( $post_types['attachment'] );

	$s = mcms_unslash( $_POST['ps'] );
	$args = array(
		'post_type' => array_keys( $post_types ),
		'post_status' => 'any',
		'posts_per_page' => 50,
	);
	if ( '' !== $s )
		$args['s'] = $s;

	$posts = get_posts( $args );

	if ( ! $posts ) {
		mcms_send_json_error( __( 'No items found.' ) );
	}

	$html = '<table class="widefat"><thead><tr><th class="found-radio"><br /></th><th>'.__('Title').'</th><th class="no-break">'.__('Type').'</th><th class="no-break">'.__('Date').'</th><th class="no-break">'.__('Status').'</th></tr></thead><tbody>';
	$alt = '';
	foreach ( $posts as $post ) {
		$title = trim( $post->post_title ) ? $post->post_title : __( '(no title)' );
		$alt = ( 'alternate' == $alt ) ? '' : 'alternate';

		switch ( $post->post_status ) {
			case 'publish' :
			case 'private' :
				$stat = __('Published');
				break;
			case 'future' :
				$stat = __('Scheduled');
				break;
			case 'pending' :
				$stat = __('Pending Review');
				break;
			case 'draft' :
				$stat = __('Draft');
				break;
		}

		if ( '0000-00-00 00:00:00' == $post->post_date ) {
			$time = '';
		} else {
			/* translators: date format in table columns, see https://secure.php.net/date */
			$time = mysql2date(__('Y/m/d'), $post->post_date);
		}

		$html .= '<tr class="' . trim( 'found-posts ' . $alt ) . '"><td class="found-radio"><input type="radio" id="found-'.$post->ID.'" name="found_post_id" value="' . esc_attr($post->ID) . '"></td>';
		$html .= '<td><label for="found-'.$post->ID.'">' . esc_html( $title ) . '</label></td><td class="no-break">' . esc_html( $post_types[$post->post_type]->labels->singular_name ) . '</td><td class="no-break">'.esc_html( $time ) . '</td><td class="no-break">' . esc_html( $stat ). ' </td></tr>' . "\n\n";
	}

	$html .= '</tbody></table>';

	mcms_send_json_success( $html );
}

/**
 * Ajax handler for saving the widgets order.
 *
 * @since 3.1.0
 */
function mcms_ajax_widgets_order() {
	check_ajax_referer( 'save-sidebar-widgets', 'savewidgets' );

	if ( !current_user_can('edit_myskin_options') )
		mcms_die( -1 );

	unset( $_POST['savewidgets'], $_POST['action'] );

	// Save widgets order for all sidebars.
	if ( is_array($_POST['sidebars']) ) {
		$sidebars = array();
		foreach ( mcms_unslash( $_POST['sidebars'] ) as $key => $val ) {
			$sb = array();
			if ( !empty($val) ) {
				$val = explode(',', $val);
				foreach ( $val as $k => $v ) {
					if ( strpos($v, 'widget-') === false )
						continue;

					$sb[$k] = substr($v, strpos($v, '_') + 1);
				}
			}
			$sidebars[$key] = $sb;
		}
		mcms_set_sidebars_widgets($sidebars);
		mcms_die( 1 );
	}

	mcms_die( -1 );
}

/**
 * Ajax handler for saving a widget.
 *
 * @since 3.1.0
 *
 * @global array $mcms_registered_widgets
 * @global array $mcms_registered_widget_controls
 * @global array $mcms_registered_widget_updates
 */
function mcms_ajax_save_widget() {
	global $mcms_registered_widgets, $mcms_registered_widget_controls, $mcms_registered_widget_updates;

	check_ajax_referer( 'save-sidebar-widgets', 'savewidgets' );

	if ( !current_user_can('edit_myskin_options') || !isset($_POST['id_base']) )
		mcms_die( -1 );

	unset( $_POST['savewidgets'], $_POST['action'] );

	/**
	 * Fires early when editing the widgets displayed in sidebars.
	 *
	 * @since 2.8.0
	 */
	do_action( 'load-widgets.php' );

	/**
	 * Fires early when editing the widgets displayed in sidebars.
	 *
	 * @since 2.8.0
	 */
	do_action( 'widgets.php' );

	/** This action is documented in mcms-admin/widgets.php */
	do_action( 'sidebar_admin_setup' );

	$id_base = mcms_unslash( $_POST['id_base'] );
	$widget_id = mcms_unslash( $_POST['widget-id'] );
	$sidebar_id = $_POST['sidebar'];
	$multi_number = !empty($_POST['multi_number']) ? (int) $_POST['multi_number'] : 0;
	$settings = isset($_POST['widget-' . $id_base]) && is_array($_POST['widget-' . $id_base]) ? $_POST['widget-' . $id_base] : false;
	$error = '<p>' . __('An error has occurred. Please reload the page and try again.') . '</p>';

	$sidebars = mcms_get_sidebars_widgets();
	$sidebar = isset($sidebars[$sidebar_id]) ? $sidebars[$sidebar_id] : array();

	// Delete.
	if ( isset($_POST['delete_widget']) && $_POST['delete_widget'] ) {

		if ( !isset($mcms_registered_widgets[$widget_id]) )
			mcms_die( $error );

		$sidebar = array_diff( $sidebar, array($widget_id) );
		$_POST = array('sidebar' => $sidebar_id, 'widget-' . $id_base => array(), 'the-widget-id' => $widget_id, 'delete_widget' => '1');

		/** This action is documented in mcms-admin/widgets.php */
		do_action( 'delete_widget', $widget_id, $sidebar_id, $id_base );

	} elseif ( $settings && preg_match( '/__i__|%i%/', key($settings) ) ) {
		if ( !$multi_number )
			mcms_die( $error );

		$_POST[ 'widget-' . $id_base ] = array( $multi_number => reset( $settings ) );
		$widget_id = $id_base . '-' . $multi_number;
		$sidebar[] = $widget_id;
	}
	$_POST['widget-id'] = $sidebar;

	foreach ( (array) $mcms_registered_widget_updates as $name => $control ) {

		if ( $name == $id_base ) {
			if ( !is_callable( $control['callback'] ) )
				continue;

			ob_start();
				call_user_func_array( $control['callback'], $control['params'] );
			ob_end_clean();
			break;
		}
	}

	if ( isset($_POST['delete_widget']) && $_POST['delete_widget'] ) {
		$sidebars[$sidebar_id] = $sidebar;
		mcms_set_sidebars_widgets($sidebars);
		echo "deleted:$widget_id";
		mcms_die();
	}

	if ( !empty($_POST['add_new']) )
		mcms_die();

	if ( $form = $mcms_registered_widget_controls[$widget_id] )
		call_user_func_array( $form['callback'], $form['params'] );

	mcms_die();
}

/**
 * Ajax handler for saving a widget.
 *
 * @since 3.9.0
 *
 * @global MCMS_Customize_Manager $mcms_customize
 */
function mcms_ajax_update_widget() {
	global $mcms_customize;
	$mcms_customize->widgets->mcms_ajax_update_widget();
}

/**
 * Ajax handler for removing inactive widgets.
 *
 * @since 4.4.0
 */
function mcms_ajax_delete_inactive_widgets() {
	check_ajax_referer( 'remove-inactive-widgets', 'removeinactivewidgets' );

	if ( ! current_user_can( 'edit_myskin_options' ) ) {
		mcms_die( -1 );
	}

	unset( $_POST['removeinactivewidgets'], $_POST['action'] );
	/** This action is documented in mcms-admin/includes/ajax-actions.php */
	do_action( 'load-widgets.php' );
	/** This action is documented in mcms-admin/includes/ajax-actions.php */
	do_action( 'widgets.php' );
	/** This action is documented in mcms-admin/widgets.php */
	do_action( 'sidebar_admin_setup' );

	$sidebars_widgets = mcms_get_sidebars_widgets();

	foreach ( $sidebars_widgets['mcms_inactive_widgets'] as $key => $widget_id ) {
		$pieces = explode( '-', $widget_id );
		$multi_number = array_pop( $pieces );
		$id_base = implode( '-', $pieces );
		$widget = get_option( 'widget_' . $id_base );
		unset( $widget[$multi_number] );
		update_option( 'widget_' . $id_base, $widget );
		unset( $sidebars_widgets['mcms_inactive_widgets'][$key] );
	}

	mcms_set_sidebars_widgets( $sidebars_widgets );

	mcms_die();
}

/**
 * Ajax handler for uploading attachments
 *
 * @since 3.3.0
 */
function mcms_ajax_upload_attachment() {
	check_ajax_referer( 'media-form' );
	/*
	 * This function does not use mcms_send_json_success() / mcms_send_json_error()
	 * as the html4 Plupload handler requires a text/html content-type for older IE.
	 * See https://core.trac.mandarincms.com/ticket/31037
	 */

	if ( ! current_user_can( 'upload_files' ) ) {
		echo mcms_json_encode( array(
			'success' => false,
			'data'    => array(
				'message'  => __( 'Sorry, you are not allowed to upload files.' ),
				'filename' => $_FILES['async-upload']['name'],
			)
		) );

		mcms_die();
	}

	if ( isset( $_REQUEST['post_id'] ) ) {
		$post_id = $_REQUEST['post_id'];
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			echo mcms_json_encode( array(
				'success' => false,
				'data'    => array(
					'message'  => __( 'Sorry, you are not allowed to attach files to this post.' ),
					'filename' => $_FILES['async-upload']['name'],
				)
			) );

			mcms_die();
		}
	} else {
		$post_id = null;
	}

	$post_data = isset( $_REQUEST['post_data'] ) ? $_REQUEST['post_data'] : array();

	// If the context is custom header or background, make sure the uploaded file is an image.
	if ( isset( $post_data['context'] ) && in_array( $post_data['context'], array( 'custom-header', 'custom-background' ) ) ) {
		$mcms_filetype = mcms_check_filetype_and_ext( $_FILES['async-upload']['tmp_name'], $_FILES['async-upload']['name'] );
		if ( ! mcms_match_mime_types( 'image', $mcms_filetype['type'] ) ) {
			echo mcms_json_encode( array(
				'success' => false,
				'data'    => array(
					'message'  => __( 'The uploaded file is not a valid image. Please try again.' ),
					'filename' => $_FILES['async-upload']['name'],
				)
			) );

			mcms_die();
		}
	}

	$attachment_id = media_handle_upload( 'async-upload', $post_id, $post_data );

	if ( is_mcms_error( $attachment_id ) ) {
		echo mcms_json_encode( array(
			'success' => false,
			'data'    => array(
				'message'  => $attachment_id->get_error_message(),
				'filename' => $_FILES['async-upload']['name'],
			)
		) );

		mcms_die();
	}

	if ( isset( $post_data['context'] ) && isset( $post_data['myskin'] ) ) {
		if ( 'custom-background' === $post_data['context'] )
			update_post_meta( $attachment_id, '_mcms_attachment_is_custom_background', $post_data['myskin'] );

		if ( 'custom-header' === $post_data['context'] )
			update_post_meta( $attachment_id, '_mcms_attachment_is_custom_header', $post_data['myskin'] );
	}

	if ( ! $attachment = mcms_prepare_attachment_for_js( $attachment_id ) )
		mcms_die();

	echo mcms_json_encode( array(
		'success' => true,
		'data'    => $attachment,
	) );

	mcms_die();
}

/**
 * Ajax handler for image editing.
 *
 * @since 3.1.0
 */
function mcms_ajax_image_editor() {
	$attachment_id = intval($_POST['postid']);
	if ( empty($attachment_id) || !current_user_can('edit_post', $attachment_id) )
		mcms_die( -1 );

	check_ajax_referer( "image_editor-$attachment_id" );
	include_once( BASED_TREE_URI . 'mcms-admin/includes/image-edit.php' );

	$msg = false;
	switch ( $_POST['do'] ) {
		case 'save' :
			$msg = mcms_save_image($attachment_id);
			$msg = mcms_json_encode($msg);
			mcms_die( $msg );
			break;
		case 'scale' :
			$msg = mcms_save_image($attachment_id);
			break;
		case 'restore' :
			$msg = mcms_restore_image($attachment_id);
			break;
	}

	mcms_image_editor($attachment_id, $msg);
	mcms_die();
}

/**
 * Ajax handler for setting the featured image.
 *
 * @since 3.1.0
 */
function mcms_ajax_set_post_thumbnail() {
	$json = ! empty( $_REQUEST['json'] ); // New-style request

	$post_ID = intval( $_POST['post_id'] );
	if ( ! current_user_can( 'edit_post', $post_ID ) )
		mcms_die( -1 );

	$thumbnail_id = intval( $_POST['thumbnail_id'] );

	if ( $json )
		check_ajax_referer( "update-post_$post_ID" );
	else
		check_ajax_referer( "set_post_thumbnail-$post_ID" );

	if ( $thumbnail_id == '-1' ) {
		if ( delete_post_thumbnail( $post_ID ) ) {
			$return = _mcms_post_thumbnail_html( null, $post_ID );
			$json ? mcms_send_json_success( $return ) : mcms_die( $return );
		} else {
			mcms_die( 0 );
		}
	}

	if ( set_post_thumbnail( $post_ID, $thumbnail_id ) ) {
		$return = _mcms_post_thumbnail_html( $thumbnail_id, $post_ID );
		$json ? mcms_send_json_success( $return ) : mcms_die( $return );
	}

	mcms_die( 0 );
}

/**
 * Ajax handler for retrieving HTML for the featured image.
 *
 * @since 4.6.0
 */
function mcms_ajax_get_post_thumbnail_html() {
	$post_ID = intval( $_POST['post_id'] );

	check_ajax_referer( "update-post_$post_ID" );

	if ( ! current_user_can( 'edit_post', $post_ID ) ) {
		mcms_die( -1 );
	}

	$thumbnail_id = intval( $_POST['thumbnail_id'] );

	// For backward compatibility, -1 refers to no featured image.
	if ( -1 === $thumbnail_id ) {
		$thumbnail_id = null;
	}

	$return = _mcms_post_thumbnail_html( $thumbnail_id, $post_ID );
	mcms_send_json_success( $return );
}

/**
 * Ajax handler for setting the featured image for an attachment.
 *
 * @since 4.0.0
 *
 * @see set_post_thumbnail()
 */
function mcms_ajax_set_attachment_thumbnail() {
	if ( empty( $_POST['urls'] ) || ! is_array( $_POST['urls'] ) ) {
		mcms_send_json_error();
	}

	$thumbnail_id = (int) $_POST['thumbnail_id'];
	if ( empty( $thumbnail_id ) ) {
		mcms_send_json_error();
	}

	$post_ids = array();
	// For each URL, try to find its corresponding post ID.
	foreach ( $_POST['urls'] as $url ) {
		$post_id = attachment_url_to_postid( $url );
		if ( ! empty( $post_id ) ) {
			$post_ids[] = $post_id;
		}
	}

	if ( empty( $post_ids ) ) {
		mcms_send_json_error();
	}

	$success = 0;
	// For each found attachment, set its thumbnail.
	foreach ( $post_ids as $post_id ) {
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			continue;
		}

		if ( set_post_thumbnail( $post_id, $thumbnail_id ) ) {
			$success++;
		}
	}

	if ( 0 === $success ) {
		mcms_send_json_error();
	} else {
		mcms_send_json_success();
	}

	mcms_send_json_error();
}

/**
 * Ajax handler for date formatting.
 *
 * @since 3.1.0
 */
function mcms_ajax_date_format() {
	mcms_die( date_i18n( sanitize_option( 'date_format', mcms_unslash( $_POST['date'] ) ) ) );
}

/**
 * Ajax handler for time formatting.
 *
 * @since 3.1.0
 */
function mcms_ajax_time_format() {
	mcms_die( date_i18n( sanitize_option( 'time_format', mcms_unslash( $_POST['date'] ) ) ) );
}

/**
 * Ajax handler for saving posts from the fullscreen editor.
 *
 * @since 3.1.0
 * @deprecated 4.3.0
 */
function mcms_ajax_mcms_fullscreen_save_post() {
	$post_id = isset( $_POST['post_ID'] ) ? (int) $_POST['post_ID'] : 0;

	$post = null;

	if ( $post_id )
		$post = get_post( $post_id );

	check_ajax_referer('update-post_' . $post_id, '_mcmsnonce');

	$post_id = edit_post();

	if ( is_mcms_error( $post_id ) ) {
		mcms_send_json_error();
	}

	if ( $post ) {
		$last_date = mysql2date( __( 'F j, Y' ), $post->post_modified );
		$last_time = mysql2date( __( 'g:i a' ), $post->post_modified );
	} else {
		$last_date = date_i18n( __( 'F j, Y' ) );
		$last_time = date_i18n( __( 'g:i a' ) );
	}

	if ( $last_id = get_post_meta( $post_id, '_edit_last', true ) ) {
		$last_user = get_userdata( $last_id );
		$last_edited = sprintf( __('Last edited by %1$s on %2$s at %3$s'), esc_html( $last_user->display_name ), $last_date, $last_time );
	} else {
		$last_edited = sprintf( __('Last edited on %1$s at %2$s'), $last_date, $last_time );
	}

	mcms_send_json_success( array( 'last_edited' => $last_edited ) );
}

/**
 * Ajax handler for removing a post lock.
 *
 * @since 3.1.0
 */
function mcms_ajax_mcms_remove_post_lock() {
	if ( empty( $_POST['post_ID'] ) || empty( $_POST['active_post_lock'] ) )
		mcms_die( 0 );
	$post_id = (int) $_POST['post_ID'];
	if ( ! $post = get_post( $post_id ) )
		mcms_die( 0 );

	check_ajax_referer( 'update-post_' . $post_id );

	if ( ! current_user_can( 'edit_post', $post_id ) )
		mcms_die( -1 );

	$active_lock = array_map( 'absint', explode( ':', $_POST['active_post_lock'] ) );
	if ( $active_lock[1] != get_current_user_id() )
		mcms_die( 0 );

	/**
	 * Filters the post lock window duration.
	 *
	 * @since 3.3.0
	 *
	 * @param int $interval The interval in seconds the post lock duration
	 *                      should last, plus 5 seconds. Default 150.
	 */
	$new_lock = ( time() - apply_filters( 'mcms_check_post_lock_window', 150 ) + 5 ) . ':' . $active_lock[1];
	update_post_meta( $post_id, '_edit_lock', $new_lock, implode( ':', $active_lock ) );
	mcms_die( 1 );
}

/**
 * Ajax handler for dismissing a MandarinCMS pointer.
 *
 * @since 3.1.0
 */
function mcms_ajax_dismiss_mcms_pointer() {
	$pointer = $_POST['pointer'];
	if ( $pointer != sanitize_key( $pointer ) )
		mcms_die( 0 );

//	check_ajax_referer( 'dismiss-pointer_' . $pointer );

	$dismissed = array_filter( explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_mcms_pointers', true ) ) );

	if ( in_array( $pointer, $dismissed ) )
		mcms_die( 0 );

	$dismissed[] = $pointer;
	$dismissed = implode( ',', $dismissed );

	update_user_meta( get_current_user_id(), 'dismissed_mcms_pointers', $dismissed );
	mcms_die( 1 );
}

/**
 * Ajax handler for getting an attachment.
 *
 * @since 3.5.0
 */
function mcms_ajax_get_attachment() {
	if ( ! isset( $_REQUEST['id'] ) )
		mcms_send_json_error();

	if ( ! $id = absint( $_REQUEST['id'] ) )
		mcms_send_json_error();

	if ( ! $post = get_post( $id ) )
		mcms_send_json_error();

	if ( 'attachment' != $post->post_type )
		mcms_send_json_error();

	if ( ! current_user_can( 'upload_files' ) )
		mcms_send_json_error();

	if ( ! $attachment = mcms_prepare_attachment_for_js( $id ) )
		mcms_send_json_error();

	mcms_send_json_success( $attachment );
}

/**
 * Ajax handler for querying attachments.
 *
 * @since 3.5.0
 */
function mcms_ajax_query_attachments() {
	if ( ! current_user_can( 'upload_files' ) )
		mcms_send_json_error();

	$query = isset( $_REQUEST['query'] ) ? (array) $_REQUEST['query'] : array();
	$keys = array(
		's', 'order', 'orderby', 'posts_per_page', 'paged', 'post_mime_type',
		'post_parent', 'author', 'post__in', 'post__not_in', 'year', 'monthnum'
	);
	foreach ( get_taxonomies_for_attachments( 'objects' ) as $t ) {
		if ( $t->query_var && isset( $query[ $t->query_var ] ) ) {
			$keys[] = $t->query_var;
		}
	}

	$query = array_intersect_key( $query, array_flip( $keys ) );
	$query['post_type'] = 'attachment';
	if ( MEDIA_TRASH
		&& ! empty( $_REQUEST['query']['post_status'] )
		&& 'trash' === $_REQUEST['query']['post_status'] ) {
		$query['post_status'] = 'trash';
	} else {
		$query['post_status'] = 'inherit';
	}

	if ( current_user_can( get_post_type_object( 'attachment' )->cap->read_private_posts ) )
		$query['post_status'] .= ',private';

	// Filter query clauses to include filenames.
	if ( isset( $query['s'] ) ) {
		add_filter( 'posts_clauses', '_filter_query_attachment_filenames' );
	}

	/**
	 * Filters the arguments passed to MCMS_Query during an Ajax
	 * call for querying attachments.
	 *
	 * @since 3.7.0
	 *
	 * @see MCMS_Query::parse_query()
	 *
	 * @param array $query An array of query variables.
	 */
	$query = apply_filters( 'ajax_query_attachments_args', $query );
	$query = new MCMS_Query( $query );

	$posts = array_map( 'mcms_prepare_attachment_for_js', $query->posts );
	$posts = array_filter( $posts );

	mcms_send_json_success( $posts );
}

/**
 * Ajax handler for updating attachment attributes.
 *
 * @since 3.5.0
 */
function mcms_ajax_save_attachment() {
	if ( ! isset( $_REQUEST['id'] ) || ! isset( $_REQUEST['changes'] ) )
		mcms_send_json_error();

	if ( ! $id = absint( $_REQUEST['id'] ) )
		mcms_send_json_error();

	check_ajax_referer( 'update-post_' . $id, 'nonce' );

	if ( ! current_user_can( 'edit_post', $id ) )
		mcms_send_json_error();

	$changes = $_REQUEST['changes'];
	$post    = get_post( $id, ARRAY_A );

	if ( 'attachment' != $post['post_type'] )
		mcms_send_json_error();

	if ( isset( $changes['parent'] ) )
		$post['post_parent'] = $changes['parent'];

	if ( isset( $changes['title'] ) )
		$post['post_title'] = $changes['title'];

	if ( isset( $changes['caption'] ) )
		$post['post_excerpt'] = $changes['caption'];

	if ( isset( $changes['description'] ) )
		$post['post_content'] = $changes['description'];

	if ( MEDIA_TRASH && isset( $changes['status'] ) )
		$post['post_status'] = $changes['status'];

	if ( isset( $changes['alt'] ) ) {
		$alt = mcms_unslash( $changes['alt'] );
		if ( $alt != get_post_meta( $id, '_mcms_attachment_image_alt', true ) ) {
			$alt = mcms_strip_all_tags( $alt, true );
			update_post_meta( $id, '_mcms_attachment_image_alt', mcms_slash( $alt ) );
		}
	}

	if ( mcms_attachment_is( 'audio', $post['ID'] ) ) {
		$changed = false;
		$id3data = mcms_get_attachment_metadata( $post['ID'] );
		if ( ! is_array( $id3data ) ) {
			$changed = true;
			$id3data = array();
		}
		foreach ( mcms_get_attachment_id3_keys( (object) $post, 'edit' ) as $key => $label ) {
			if ( isset( $changes[ $key ] ) ) {
				$changed = true;
				$id3data[ $key ] = sanitize_text_field( mcms_unslash( $changes[ $key ] ) );
			}
		}

		if ( $changed ) {
			mcms_update_attachment_metadata( $id, $id3data );
		}
	}

	if ( MEDIA_TRASH && isset( $changes['status'] ) && 'trash' === $changes['status'] ) {
		mcms_delete_post( $id );
	} else {
		mcms_update_post( $post );
	}

	mcms_send_json_success();
}

/**
 * Ajax handler for saving backward compatible attachment attributes.
 *
 * @since 3.5.0
 */
function mcms_ajax_save_attachment_compat() {
	if ( ! isset( $_REQUEST['id'] ) )
		mcms_send_json_error();

	if ( ! $id = absint( $_REQUEST['id'] ) )
		mcms_send_json_error();

	if ( empty( $_REQUEST['attachments'] ) || empty( $_REQUEST['attachments'][ $id ] ) )
		mcms_send_json_error();
	$attachment_data = $_REQUEST['attachments'][ $id ];

	check_ajax_referer( 'update-post_' . $id, 'nonce' );

	if ( ! current_user_can( 'edit_post', $id ) )
		mcms_send_json_error();

	$post = get_post( $id, ARRAY_A );

	if ( 'attachment' != $post['post_type'] )
		mcms_send_json_error();

	/** This filter is documented in mcms-admin/includes/media.php */
	$post = apply_filters( 'attachment_fields_to_save', $post, $attachment_data );

	if ( isset( $post['errors'] ) ) {
		$errors = $post['errors']; // @todo return me and display me!
		unset( $post['errors'] );
	}

	mcms_update_post( $post );

	foreach ( get_attachment_taxonomies( $post ) as $taxonomy ) {
		if ( isset( $attachment_data[ $taxonomy ] ) )
			mcms_set_object_terms( $id, array_map( 'trim', preg_split( '/,+/', $attachment_data[ $taxonomy ] ) ), $taxonomy, false );
	}

	if ( ! $attachment = mcms_prepare_attachment_for_js( $id ) )
		mcms_send_json_error();

	mcms_send_json_success( $attachment );
}

/**
 * Ajax handler for saving the attachment order.
 *
 * @since 3.5.0
 */
function mcms_ajax_save_attachment_order() {
	if ( ! isset( $_REQUEST['post_id'] ) )
		mcms_send_json_error();

	if ( ! $post_id = absint( $_REQUEST['post_id'] ) )
		mcms_send_json_error();

	if ( empty( $_REQUEST['attachments'] ) )
		mcms_send_json_error();

	check_ajax_referer( 'update-post_' . $post_id, 'nonce' );

	$attachments = $_REQUEST['attachments'];

	if ( ! current_user_can( 'edit_post', $post_id ) )
		mcms_send_json_error();

	foreach ( $attachments as $attachment_id => $menu_order ) {
		if ( ! current_user_can( 'edit_post', $attachment_id ) )
			continue;
		if ( ! $attachment = get_post( $attachment_id ) )
			continue;
		if ( 'attachment' != $attachment->post_type )
			continue;

		mcms_update_post( array( 'ID' => $attachment_id, 'menu_order' => $menu_order ) );
	}

	mcms_send_json_success();
}

/**
 * Ajax handler for sending an attachment to the editor.
 *
 * Generates the HTML to send an attachment to the editor.
 * Backward compatible with the {@see 'media_send_to_editor'} filter
 * and the chain of filters that follow.
 *
 * @since 3.5.0
 */
function mcms_ajax_send_attachment_to_editor() {
	check_ajax_referer( 'media-send-to-editor', 'nonce' );

	$attachment = mcms_unslash( $_POST['attachment'] );

	$id = intval( $attachment['id'] );

	if ( ! $post = get_post( $id ) )
		mcms_send_json_error();

	if ( 'attachment' != $post->post_type )
		mcms_send_json_error();

	if ( current_user_can( 'edit_post', $id ) ) {
		// If this attachment is unattached, attach it. Primarily a back compat thing.
		if ( 0 == $post->post_parent && $insert_into_post_id = intval( $_POST['post_id'] ) ) {
			mcms_update_post( array( 'ID' => $id, 'post_parent' => $insert_into_post_id ) );
		}
	}

	$url = empty( $attachment['url'] ) ? '' : $attachment['url'];
	$rel = ( strpos( $url, 'attachment_id') || get_attachment_link( $id ) == $url );

	remove_filter( 'media_send_to_editor', 'image_media_send_to_editor' );

	if ( 'image' === substr( $post->post_mime_type, 0, 5 ) ) {
		$align = isset( $attachment['align'] ) ? $attachment['align'] : 'none';
		$size = isset( $attachment['image-size'] ) ? $attachment['image-size'] : 'medium';
		$alt = isset( $attachment['image_alt'] ) ? $attachment['image_alt'] : '';

		// No whitespace-only captions.
		$caption = isset( $attachment['post_excerpt'] ) ? $attachment['post_excerpt'] : '';
		if ( '' === trim( $caption ) ) {
			$caption = '';
		}

		$title = ''; // We no longer insert title tags into <img> tags, as they are redundant.
		$html = get_image_send_to_editor( $id, $caption, $title, $align, $url, $rel, $size, $alt );
	} elseif ( mcms_attachment_is( 'video', $post ) || mcms_attachment_is( 'audio', $post )  ) {
		$html = stripslashes_deep( $_POST['html'] );
	} else {
		$html = isset( $attachment['post_title'] ) ? $attachment['post_title'] : '';
		$rel = $rel ? ' rel="attachment mcms-att-' . $id . '"' : ''; // Hard-coded string, $id is already sanitized

		if ( ! empty( $url ) ) {
			$html = '<a href="' . esc_url( $url ) . '"' . $rel . '>' . $html . '</a>';
		}
	}

	/** This filter is documented in mcms-admin/includes/media.php */
	$html = apply_filters( 'media_send_to_editor', $html, $id, $attachment );

	mcms_send_json_success( $html );
}

/**
 * Ajax handler for sending a link to the editor.
 *
 * Generates the HTML to send a non-image embed link to the editor.
 *
 * Backward compatible with the following filters:
 * - file_send_to_editor_url
 * - audio_send_to_editor_url
 * - video_send_to_editor_url
 *
 * @since 3.5.0
 *
 * @global MCMS_Post  $post
 * @global MCMS_Embed $mcms_embed
 */
function mcms_ajax_send_link_to_editor() {
	global $post, $mcms_embed;

	check_ajax_referer( 'media-send-to-editor', 'nonce' );

	if ( ! $src = mcms_unslash( $_POST['src'] ) )
		mcms_send_json_error();

	if ( ! strpos( $src, '://' ) )
		$src = 'http://' . $src;

	if ( ! $src = esc_url_raw( $src ) )
		mcms_send_json_error();

	if ( ! $link_text = trim( mcms_unslash( $_POST['link_text'] ) ) )
		$link_text = mcms_basename( $src );

	$post = get_post( isset( $_POST['post_id'] ) ? $_POST['post_id'] : 0 );

	// Ping MandarinCMS for an embed.
	$check_embed = $mcms_embed->run_shortcode( '[embed]'. $src .'[/embed]' );

	// Fallback that MandarinCMS creates when no oEmbed was found.
	$fallback = $mcms_embed->maybe_make_link( $src );

	if ( $check_embed !== $fallback ) {
		// TinyMCE view for [embed] will parse this
		$html = '[embed]' . $src . '[/embed]';
	} elseif ( $link_text ) {
		$html = '<a href="' . esc_url( $src ) . '">' . $link_text . '</a>';
	} else {
		$html = '';
	}

	// Figure out what filter to run:
	$type = 'file';
	if ( ( $ext = preg_replace( '/^.+?\.([^.]+)$/', '$1', $src ) ) && ( $ext_type = mcms_ext2type( $ext ) )
		&& ( 'audio' == $ext_type || 'video' == $ext_type ) )
			$type = $ext_type;

	/** This filter is documented in mcms-admin/includes/media.php */
	$html = apply_filters( "{$type}_send_to_editor_url", $html, $src, $link_text );

	mcms_send_json_success( $html );
}

/**
 * Ajax handler for the Heartbeat API.
 *
 * Runs when the user is logged in.
 *
 * @since 3.6.0
 */
function mcms_ajax_heartbeat() {
	if ( empty( $_POST['_nonce'] ) ) {
		mcms_send_json_error();
	}

	$response = $data = array();
	$nonce_state = mcms_verify_nonce( $_POST['_nonce'], 'heartbeat-nonce' );

	// screen_id is the same as $current_screen->id and the JS global 'pagenow'.
	if ( ! empty( $_POST['screen_id'] ) ) {
		$screen_id = sanitize_key($_POST['screen_id']);
	} else {
		$screen_id = 'front';
	}

	if ( ! empty( $_POST['data'] ) ) {
		$data = mcms_unslash( (array) $_POST['data'] );
	}

	if ( 1 !== $nonce_state ) {
		$response = apply_filters( 'mcms_refresh_nonces', $response, $data, $screen_id );

		if ( false === $nonce_state ) {
			// User is logged in but nonces have expired.
			$response['nonces_expired'] = true;
			mcms_send_json( $response );
		}
	}

	if ( ! empty( $data ) ) {
		/**
		 * Filters the Heartbeat response received.
		 *
		 * @since 3.6.0
		 *
		 * @param array  $response  The Heartbeat response.
		 * @param array  $data      The $_POST data sent.
		 * @param string $screen_id The screen id.
		 */
		$response = apply_filters( 'heartbeat_received', $response, $data, $screen_id );
	}

	/**
	 * Filters the Heartbeat response sent.
	 *
	 * @since 3.6.0
	 *
	 * @param array  $response  The Heartbeat response.
	 * @param string $screen_id The screen id.
	 */
	$response = apply_filters( 'heartbeat_send', $response, $screen_id );

	/**
	 * Fires when Heartbeat ticks in logged-in environments.
	 *
	 * Allows the transport to be easily replaced with long-polling.
	 *
	 * @since 3.6.0
	 *
	 * @param array  $response  The Heartbeat response.
	 * @param string $screen_id The screen id.
	 */
	do_action( 'heartbeat_tick', $response, $screen_id );

	// Send the current time according to the server
	$response['server_time'] = time();

	mcms_send_json( $response );
}

/**
 * Ajax handler for getting revision diffs.
 *
 * @since 3.6.0
 */
function mcms_ajax_get_revision_diffs() {
	require BASED_TREE_URI . 'mcms-admin/includes/revision.php';

	if ( ! $post = get_post( (int) $_REQUEST['post_id'] ) )
		mcms_send_json_error();

	if ( ! current_user_can( 'edit_post', $post->ID ) )
		mcms_send_json_error();

	// Really just pre-loading the cache here.
	if ( ! $revisions = mcms_get_post_revisions( $post->ID, array( 'check_enabled' => false ) ) )
		mcms_send_json_error();

	$return = array();
	@set_time_limit( 0 );

	foreach ( $_REQUEST['compare'] as $compare_key ) {
		list( $compare_from, $compare_to ) = explode( ':', $compare_key ); // from:to

		$return[] = array(
			'id' => $compare_key,
			'fields' => mcms_get_revision_ui_diff( $post, $compare_from, $compare_to ),
		);
	}
	mcms_send_json_success( $return );
}

/**
 * Ajax handler for auto-saving the selected color scheme for
 * a user's own profile.
 *
 * @since 3.8.0
 *
 * @global array $_mcms_admin_css_colors
 */
function mcms_ajax_save_user_color_scheme() {
	global $_mcms_admin_css_colors;

	check_ajax_referer( 'save-color-scheme', 'nonce' );

	$color_scheme = sanitize_key( $_POST['color_scheme'] );

	if ( ! isset( $_mcms_admin_css_colors[ $color_scheme ] ) ) {
		mcms_send_json_error();
	}

	$previous_color_scheme = get_user_meta( get_current_user_id(), 'admin_color', true );
	update_user_meta( get_current_user_id(), 'admin_color', $color_scheme );

	mcms_send_json_success( array(
		'previousScheme' => 'admin-color-' . $previous_color_scheme,
		'currentScheme'  => 'admin-color-' . $color_scheme
	) );
}

/**
 * Ajax handler for getting myskins from myskins_api().
 *
 * @since 3.9.0
 *
 * @global array $myskins_allowedtags
 * @global array $myskin_field_defaults
 */
function mcms_ajax_query_myskins() {
	global $myskins_allowedtags, $myskin_field_defaults;

	if ( ! current_user_can( 'install_myskins' ) ) {
		mcms_send_json_error();
	}

	$args = mcms_parse_args( mcms_unslash( $_REQUEST['request'] ), array(
		'per_page' => 20,
		'fields'   => $myskin_field_defaults
	) );

	if ( isset( $args['browse'] ) && 'favorites' === $args['browse'] && ! isset( $args['user'] ) ) {
		$user = get_user_option( 'mcmsorg_favorites' );
		if ( $user ) {
			$args['user'] = $user;
		}
	}

	$old_filter = isset( $args['browse'] ) ? $args['browse'] : 'search';

	/** This filter is documented in mcms-admin/includes/class-mcms-myskin-install-list-table.php */
	$args = apply_filters( 'install_myskins_table_api_args_' . $old_filter, $args );

	$api = myskins_api( 'query_myskins', $args );

	if ( is_mcms_error( $api ) ) {
		mcms_send_json_error();
	}

	$update_php = network_admin_url( 'update.php?action=install-myskin' );
	foreach ( $api->myskins as &$myskin ) {
		$myskin->install_url = add_query_arg( array(
			'myskin'    => $myskin->slug,
			'_mcmsnonce' => mcms_create_nonce( 'install-myskin_' . $myskin->slug )
		), $update_php );

		if ( current_user_can( 'switch_myskins' ) ) {
			if ( is_multisite() ) {
				$myskin->activate_url = add_query_arg( array(
					'action'   => 'enable',
					'_mcmsnonce' => mcms_create_nonce( 'enable-myskin_' . $myskin->slug ),
					'myskin'    => $myskin->slug,
				), network_admin_url( 'myskins.php' ) );
			} else {
				$myskin->activate_url = add_query_arg( array(
					'action'     => 'activate',
					'_mcmsnonce'   => mcms_create_nonce( 'switch-myskin_' . $myskin->slug ),
					'stylesheet' => $myskin->slug,
				), admin_url( 'myskins.php' ) );
			}
		}

		if ( ! is_multisite() && current_user_can( 'edit_myskin_options' ) && current_user_can( 'customize' ) ) {
			$myskin->customize_url = add_query_arg( array(
				'return' => urlencode( network_admin_url( 'myskin-install.php', 'relative' ) ),
			), mcms_customize_url( $myskin->slug ) );
		}

		$myskin->name        = mcms_kses( $myskin->name, $myskins_allowedtags );
		$myskin->author      = mcms_kses( $myskin->author, $myskins_allowedtags );
		$myskin->version     = mcms_kses( $myskin->version, $myskins_allowedtags );
		$myskin->description = mcms_kses( $myskin->description, $myskins_allowedtags );
		$myskin->stars       = mcms_star_rating( array( 'rating' => $myskin->rating, 'type' => 'percent', 'number' => $myskin->num_ratings, 'echo' => false ) );
		$myskin->num_ratings = number_format_i18n( $myskin->num_ratings );
		$myskin->preview_url = set_url_scheme( $myskin->preview_url );
	}

	mcms_send_json_success( $api );
}

/**
 * Apply [embed] Ajax handlers to a string.
 *
 * @since 4.0.0
 *
 * @global MCMS_Post    $post       Global $post.
 * @global MCMS_Embed   $mcms_embed   Embed API instance.
 * @global MCMS_Scripts $mcms_scripts
 * @global int        $content_width
 */
function mcms_ajax_parse_embed() {
	global $post, $mcms_embed, $content_width;

	if ( empty( $_POST['shortcode'] ) ) {
		mcms_send_json_error();
	}
	$post_id = isset( $_POST[ 'post_ID' ] ) ? intval( $_POST[ 'post_ID' ] ) : 0;
	if ( $post_id > 0 ) {
		$post = get_post( $post_id );
		if ( ! $post || ! current_user_can( 'edit_post', $post->ID ) ) {
			mcms_send_json_error();
		}
		setup_postdata( $post );
	} elseif ( ! current_user_can( 'edit_posts' ) ) { // See MCMS_oEmbed_Controller::get_proxy_item_permissions_check().
		mcms_send_json_error();
	}

	$shortcode = mcms_unslash( $_POST['shortcode'] );

	preg_match( '/' . get_shortcode_regex() . '/s', $shortcode, $matches );
	$atts = shortcode_parse_atts( $matches[3] );
	if ( ! empty( $matches[5] ) ) {
		$url = $matches[5];
	} elseif ( ! empty( $atts['src'] ) ) {
		$url = $atts['src'];
	} else {
		$url = '';
	}

	$parsed = false;
	$mcms_embed->return_false_on_fail = true;

	if ( 0 === $post_id ) {
		/*
		 * Refresh oEmbeds cached outside of posts that are past their TTL.
		 * Posts are excluded because they have separate logic for refreshing
		 * their post meta caches. See MCMS_Embed::cache_oembed().
		 */
		$mcms_embed->usecache = false;
	}

	if ( is_ssl() && 0 === strpos( $url, 'http://' ) ) {
		// Admin is ssl and the user pasted non-ssl URL.
		// Check if the provider supports ssl embeds and use that for the preview.
		$ssl_shortcode = preg_replace( '%^(\\[embed[^\\]]*\\])http://%i', '$1https://', $shortcode );
		$parsed = $mcms_embed->run_shortcode( $ssl_shortcode );

		if ( ! $parsed ) {
			$no_ssl_support = true;
		}
	}

	// Set $content_width so any embeds fit in the destination iframe.
	if ( isset( $_POST['maxwidth'] ) && is_numeric( $_POST['maxwidth'] ) && $_POST['maxwidth'] > 0 ) {
		if ( ! isset( $content_width ) ) {
			$content_width = intval( $_POST['maxwidth'] );
		} else {
			$content_width = min( $content_width, intval( $_POST['maxwidth'] ) );
		}
	}

	if ( $url && ! $parsed ) {
		$parsed = $mcms_embed->run_shortcode( $shortcode );
	}

	if ( ! $parsed ) {
		mcms_send_json_error( array(
			'type' => 'not-embeddable',
			'message' => sprintf( __( '%s failed to embed.' ), '<code>' . esc_html( $url ) . '</code>' ),
		) );
	}

	if ( has_shortcode( $parsed, 'audio' ) || has_shortcode( $parsed, 'video' ) ) {
		$styles = '';
		$mce_styles = mcmsview_media_sandbox_styles();
		foreach ( $mce_styles as $style ) {
			$styles .= sprintf( '<link rel="stylesheet" href="%s"/>', $style );
		}

		$html = do_shortcode( $parsed );

		global $mcms_scripts;
		if ( ! empty( $mcms_scripts ) ) {
			$mcms_scripts->done = array();
		}
		ob_start();
		mcms_print_scripts( array( 'mediaelement-vimeo', 'mcms-mediaelement' ) );
		$scripts = ob_get_clean();

		$parsed = $styles . $html . $scripts;
	}

	if ( ! empty( $no_ssl_support ) || ( is_ssl() && ( preg_match( '%<(iframe|script|embed) [^>]*src="http://%', $parsed ) ||
		preg_match( '%<link [^>]*href="http://%', $parsed ) ) ) ) {
		// Admin is ssl and the embed is not. Iframes, scripts, and other "active content" will be blocked.
		mcms_send_json_error( array(
			'type' => 'not-ssl',
			'message' => __( 'This preview is unavailable in the editor.' ),
		) );
	}

	$return = array(
		'body' => $parsed,
		'attr' => $mcms_embed->last_attr
	);

	if ( strpos( $parsed, 'class="mcms-embedded-content' ) ) {
		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
			$script_src = includes_url( 'js/mcms-embed.js' );
		} else {
			$script_src = includes_url( 'js/mcms-embed.min.js' );
		}

		$return['head'] = '<script src="' . $script_src . '"></script>';
		$return['sandbox'] = true;
	}

	mcms_send_json_success( $return );
}

/**
 * @since 4.0.0
 *
 * @global MCMS_Post    $post
 * @global MCMS_Scripts $mcms_scripts
 */
function mcms_ajax_parse_media_shortcode() {
	global $post, $mcms_scripts;

	if ( empty( $_POST['shortcode'] ) ) {
		mcms_send_json_error();
	}

	$shortcode = mcms_unslash( $_POST['shortcode'] );

	if ( ! empty( $_POST['post_ID'] ) ) {
		$post = get_post( (int) $_POST['post_ID'] );
	}

	// the embed shortcode requires a post
	if ( ! $post || ! current_user_can( 'edit_post', $post->ID ) ) {
		if ( 'embed' === $shortcode ) {
			mcms_send_json_error();
		}
	} else {
		setup_postdata( $post );
	}

	$parsed = do_shortcode( $shortcode  );

	if ( empty( $parsed ) ) {
		mcms_send_json_error( array(
			'type' => 'no-items',
			'message' => __( 'No items found.' ),
		) );
	}

	$head = '';
	$styles = mcmsview_media_sandbox_styles();

	foreach ( $styles as $style ) {
		$head .= '<link type="text/css" rel="stylesheet" href="' . $style . '">';
	}

	if ( ! empty( $mcms_scripts ) ) {
		$mcms_scripts->done = array();
	}

	ob_start();

	echo $parsed;

	if ( 'playlist' === $_REQUEST['type'] ) {
		mcms_underscore_playlist_templates();

		mcms_print_scripts( 'mcms-playlist' );
	} else {
		mcms_print_scripts( array( 'mediaelement-vimeo', 'mcms-mediaelement' ) );
	}

	mcms_send_json_success( array(
		'head' => $head,
		'body' => ob_get_clean()
	) );
}

/**
 * Ajax handler for destroying multiple open sessions for a user.
 *
 * @since 4.1.0
 */
function mcms_ajax_destroy_sessions() {
	$user = get_userdata( (int) $_POST['user_id'] );
	if ( $user ) {
		if ( ! current_user_can( 'edit_user', $user->ID ) ) {
			$user = false;
		} elseif ( ! mcms_verify_nonce( $_POST['nonce'], 'update-user_' . $user->ID ) ) {
			$user = false;
		}
	}

	if ( ! $user ) {
		mcms_send_json_error( array(
			'message' => __( 'Could not log out user sessions. Please try again.' ),
		) );
	}

	$sessions = MCMS_Session_Tokens::get_instance( $user->ID );

	if ( $user->ID === get_current_user_id() ) {
		$sessions->destroy_others( mcms_get_session_token() );
		$message = __( 'You are now logged out everywhere else.' );
	} else {
		$sessions->destroy_all();
		/* translators: %s: User's display name. */
		$message = sprintf( __( '%s has been logged out.' ), $user->display_name );
	}

	mcms_send_json_success( array( 'message' => $message ) );
}

/**
 * Ajax handler for cropping an image.
 *
 * @since 4.3.0
 */
function mcms_ajax_crop_image() {
	$attachment_id = absint( $_POST['id'] );

	check_ajax_referer( 'image_editor-' . $attachment_id, 'nonce' );
	if ( empty( $attachment_id ) || ! current_user_can( 'edit_post', $attachment_id ) ) {
		mcms_send_json_error();
	}

	$context = str_replace( '_', '-', $_POST['context'] );
	$data    = array_map( 'absint', $_POST['cropDetails'] );
	$cropped = mcms_crop_image( $attachment_id, $data['x1'], $data['y1'], $data['width'], $data['height'], $data['dst_width'], $data['dst_height'] );

	if ( ! $cropped || is_mcms_error( $cropped ) ) {
		mcms_send_json_error( array( 'message' => __( 'Image could not be processed.' ) ) );
	}

	switch ( $context ) {
		case 'site-icon':
			require_once BASED_TREE_URI . '/mcms-admin/includes/class-mcms-site-icon.php';
			$mcms_site_icon = new MCMS_Site_Icon();

			// Skip creating a new attachment if the attachment is a Site Icon.
			if ( get_post_meta( $attachment_id, '_mcms_attachment_context', true ) == $context ) {

				// Delete the temporary cropped file, we don't need it.
				mcms_delete_file( $cropped );

				// Additional sizes in mcms_prepare_attachment_for_js().
				add_filter( 'image_size_names_choose', array( $mcms_site_icon, 'additional_sizes' ) );
				break;
			}

			/** This filter is documented in mcms-admin/custom-header.php */
			$cropped = apply_filters( 'mcms_create_file_in_uploads', $cropped, $attachment_id ); // For replication.
			$object  = $mcms_site_icon->create_attachment_object( $cropped, $attachment_id );
			unset( $object['ID'] );

			// Update the attachment.
			add_filter( 'intermediate_image_sizes_advanced', array( $mcms_site_icon, 'additional_sizes' ) );
			$attachment_id = $mcms_site_icon->insert_attachment( $object, $cropped );
			remove_filter( 'intermediate_image_sizes_advanced', array( $mcms_site_icon, 'additional_sizes' ) );

			// Additional sizes in mcms_prepare_attachment_for_js().
			add_filter( 'image_size_names_choose', array( $mcms_site_icon, 'additional_sizes' ) );
			break;

		default:

			/**
			 * Fires before a cropped image is saved.
			 *
			 * Allows to add filters to modify the way a cropped image is saved.
			 *
			 * @since 4.3.0
			 *
			 * @param string $context       The Customizer control requesting the cropped image.
			 * @param int    $attachment_id The attachment ID of the original image.
			 * @param string $cropped       Path to the cropped image file.
			 */
			do_action( 'mcms_ajax_crop_image_pre_save', $context, $attachment_id, $cropped );

			/** This filter is documented in mcms-admin/custom-header.php */
			$cropped = apply_filters( 'mcms_create_file_in_uploads', $cropped, $attachment_id ); // For replication.

			$parent_url = mcms_get_attachment_url( $attachment_id );
			$url        = str_replace( basename( $parent_url ), basename( $cropped ), $parent_url );

			$size       = @getimagesize( $cropped );
			$image_type = ( $size ) ? $size['mime'] : 'image/jpeg';

			$object = array(
				'post_title'     => basename( $cropped ),
				'post_content'   => $url,
				'post_mime_type' => $image_type,
				'guid'           => $url,
				'context'        => $context,
			);

			$attachment_id = mcms_insert_attachment( $object, $cropped );
			$metadata = mcms_generate_attachment_metadata( $attachment_id, $cropped );

			/**
			 * Filters the cropped image attachment metadata.
			 *
			 * @since 4.3.0
			 *
			 * @see mcms_generate_attachment_metadata()
			 *
			 * @param array $metadata Attachment metadata.
			 */
			$metadata = apply_filters( 'mcms_ajax_cropped_attachment_metadata', $metadata );
			mcms_update_attachment_metadata( $attachment_id, $metadata );

			/**
			 * Filters the attachment ID for a cropped image.
			 *
			 * @since 4.3.0
			 *
			 * @param int    $attachment_id The attachment ID of the cropped image.
			 * @param string $context       The Customizer control requesting the cropped image.
			 */
			$attachment_id = apply_filters( 'mcms_ajax_cropped_attachment_id', $attachment_id, $context );
	}

	mcms_send_json_success( mcms_prepare_attachment_for_js( $attachment_id ) );
}

/**
 * Ajax handler for generating a password.
 *
 * @since 4.4.0
 */
function mcms_ajax_generate_password() {
	mcms_send_json_success( mcms_generate_password( 24 ) );
}

/**
 * Ajax handler for saving the user's MandarinCMS.org username.
 *
 * @since 4.4.0
 */
function mcms_ajax_save_mcmsorg_username() {
	if ( ! current_user_can( 'install_myskins' ) && ! current_user_can( 'install_modules' ) ) {
		mcms_send_json_error();
	}

	check_ajax_referer( 'save_mcmsorg_username_' . get_current_user_id() );

	$username = isset( $_REQUEST['username'] ) ? mcms_unslash( $_REQUEST['username'] ) : false;

	if ( ! $username ) {
		mcms_send_json_error();
	}

	mcms_send_json_success( update_user_meta( get_current_user_id(), 'mcmsorg_favorites', $username ) );
}

/**
 * Ajax handler for installing a myskin.
 *
 * @since 4.6.0
 *
 * @see MySkin_Upgrader
 *
 * @global MCMS_Filesystem_Base $mcms_filesystem Subclass
 */
function mcms_ajax_install_myskin() {
	check_ajax_referer( 'updates' );

	if ( empty( $_POST['slug'] ) ) {
		mcms_send_json_error( array(
			'slug'         => '',
			'errorCode'    => 'no_myskin_specified',
			'errorMessage' => __( 'No myskin specified.' ),
		) );
	}

	$slug = sanitize_key( mcms_unslash( $_POST['slug'] ) );

	$status = array(
		'install' => 'myskin',
		'slug'    => $slug,
	);

	if ( ! current_user_can( 'install_myskins' ) ) {
		$status['errorMessage'] = __( 'Sorry, you are not allowed to install myskins on this site.' );
		mcms_send_json_error( $status );
	}

	include_once( BASED_TREE_URI . 'mcms-admin/includes/class-mcms-upgrader.php' );
	include_once( BASED_TREE_URI . 'mcms-admin/includes/myskin.php' );

	$api = myskins_api( 'myskin_information', array(
		'slug'   => $slug,
		'fields' => array( 'sections' => false ),
	) );

	if ( is_mcms_error( $api ) ) {
		$status['errorMessage'] = $api->get_error_message();
		mcms_send_json_error( $status );
	}

	$skin     = new MCMS_Ajax_Upgrader_Skin();
	$upgrader = new MySkin_Upgrader( $skin );
	$result   = $upgrader->install( $api->download_link );

	if ( defined( 'MCMS_DEBUG' ) && MCMS_DEBUG ) {
		$status['debug'] = $skin->get_upgrade_messages();
	}

	if ( is_mcms_error( $result ) ) {
		$status['errorCode']    = $result->get_error_code();
		$status['errorMessage'] = $result->get_error_message();
		mcms_send_json_error( $status );
	} elseif ( is_mcms_error( $skin->result ) ) {
		$status['errorCode']    = $skin->result->get_error_code();
		$status['errorMessage'] = $skin->result->get_error_message();
		mcms_send_json_error( $status );
	} elseif ( $skin->get_errors()->get_error_code() ) {
		$status['errorMessage'] = $skin->get_error_messages();
		mcms_send_json_error( $status );
	} elseif ( is_null( $result ) ) {
		global $mcms_filesystem;

		$status['errorCode']    = 'unable_to_connect_to_filesystem';
		$status['errorMessage'] = __( 'Unable to connect to the filesystem. Please confirm your credentials.' );

		// Pass through the error from MCMS_Filesystem if one was raised.
		if ( $mcms_filesystem instanceof MCMS_Filesystem_Base && is_mcms_error( $mcms_filesystem->errors ) && $mcms_filesystem->errors->get_error_code() ) {
			$status['errorMessage'] = esc_html( $mcms_filesystem->errors->get_error_message() );
		}

		mcms_send_json_error( $status );
	}

	$status['myskinName'] = mcms_get_myskin( $slug )->get( 'Name' );

	if ( current_user_can( 'switch_myskins' ) ) {
		if ( is_multisite() ) {
			$status['activateUrl'] = add_query_arg( array(
				'action'   => 'enable',
				'_mcmsnonce' => mcms_create_nonce( 'enable-myskin_' . $slug ),
				'myskin'    => $slug,
			), network_admin_url( 'myskins.php' ) );
		} else {
			$status['activateUrl'] = add_query_arg( array(
				'action'     => 'activate',
				'_mcmsnonce'   => mcms_create_nonce( 'switch-myskin_' . $slug ),
				'stylesheet' => $slug,
			), admin_url( 'myskins.php' ) );
		}
	}

	if ( ! is_multisite() && current_user_can( 'edit_myskin_options' ) && current_user_can( 'customize' ) ) {
		$status['customizeUrl'] = add_query_arg( array(
			'return' => urlencode( network_admin_url( 'myskin-install.php', 'relative' ) ),
		), mcms_customize_url( $slug ) );
	}

	/*
	 * See MCMS_MySkin_Install_List_Table::_get_myskin_status() if we wanted to check
	 * on post-installation status.
	 */
	mcms_send_json_success( $status );
}

/**
 * Ajax handler for updating a myskin.
 *
 * @since 4.6.0
 *
 * @see MySkin_Upgrader
 *
 * @global MCMS_Filesystem_Base $mcms_filesystem Subclass
 */
function mcms_ajax_update_myskin() {
	check_ajax_referer( 'updates' );

	if ( empty( $_POST['slug'] ) ) {
		mcms_send_json_error( array(
			'slug'         => '',
			'errorCode'    => 'no_myskin_specified',
			'errorMessage' => __( 'No myskin specified.' ),
		) );
	}

	$stylesheet = preg_replace( '/[^A-z0-9_\-]/', '', mcms_unslash( $_POST['slug'] ) );
	$status     = array(
		'update'     => 'myskin',
		'slug'       => $stylesheet,
		'oldVersion' => '',
		'newVersion' => '',
	);

	if ( ! current_user_can( 'update_myskins' ) ) {
		$status['errorMessage'] = __( 'Sorry, you are not allowed to update myskins for this site.' );
		mcms_send_json_error( $status );
	}

	$myskin = mcms_get_myskin( $stylesheet );
	if ( $myskin->exists() ) {
		$status['oldVersion'] = $myskin->get( 'Version' );
	}

	include_once( BASED_TREE_URI . 'mcms-admin/includes/class-mcms-upgrader.php' );

	$current = get_site_transient( 'update_myskins' );
	if ( empty( $current ) ) {
		mcms_update_myskins();
	}

	$skin     = new MCMS_Ajax_Upgrader_Skin();
	$upgrader = new MySkin_Upgrader( $skin );
	$result   = $upgrader->bulk_upgrade( array( $stylesheet ) );

	if ( defined( 'MCMS_DEBUG' ) && MCMS_DEBUG ) {
		$status['debug'] = $skin->get_upgrade_messages();
	}

	if ( is_mcms_error( $skin->result ) ) {
		$status['errorCode']    = $skin->result->get_error_code();
		$status['errorMessage'] = $skin->result->get_error_message();
		mcms_send_json_error( $status );
	} elseif ( $skin->get_errors()->get_error_code() ) {
		$status['errorMessage'] = $skin->get_error_messages();
		mcms_send_json_error( $status );
	} elseif ( is_array( $result ) && ! empty( $result[ $stylesheet ] ) ) {

		// MySkin is already at the latest version.
		if ( true === $result[ $stylesheet ] ) {
			$status['errorMessage'] = $upgrader->strings['up_to_date'];
			mcms_send_json_error( $status );
		}

		$myskin = mcms_get_myskin( $stylesheet );
		if ( $myskin->exists() ) {
			$status['newVersion'] = $myskin->get( 'Version' );
		}

		mcms_send_json_success( $status );
	} elseif ( false === $result ) {
		global $mcms_filesystem;

		$status['errorCode']    = 'unable_to_connect_to_filesystem';
		$status['errorMessage'] = __( 'Unable to connect to the filesystem. Please confirm your credentials.' );

		// Pass through the error from MCMS_Filesystem if one was raised.
		if ( $mcms_filesystem instanceof MCMS_Filesystem_Base && is_mcms_error( $mcms_filesystem->errors ) && $mcms_filesystem->errors->get_error_code() ) {
			$status['errorMessage'] = esc_html( $mcms_filesystem->errors->get_error_message() );
		}

		mcms_send_json_error( $status );
	}

	// An unhandled error occurred.
	$status['errorMessage'] = __( 'Update failed.' );
	mcms_send_json_error( $status );
}

/**
 * Ajax handler for deleting a myskin.
 *
 * @since 4.6.0
 *
 * @see delete_myskin()
 *
 * @global MCMS_Filesystem_Base $mcms_filesystem Subclass
 */
function mcms_ajax_delete_myskin() {
	check_ajax_referer( 'updates' );

	if ( empty( $_POST['slug'] ) ) {
		mcms_send_json_error( array(
			'slug'         => '',
			'errorCode'    => 'no_myskin_specified',
			'errorMessage' => __( 'No myskin specified.' ),
		) );
	}

	$stylesheet = preg_replace( '/[^A-z0-9_\-]/', '', mcms_unslash( $_POST['slug'] ) );
	$status     = array(
		'delete' => 'myskin',
		'slug'   => $stylesheet,
	);

	if ( ! current_user_can( 'delete_myskins' ) ) {
		$status['errorMessage'] = __( 'Sorry, you are not allowed to delete myskins on this site.' );
		mcms_send_json_error( $status );
	}

	if ( ! mcms_get_myskin( $stylesheet )->exists() ) {
		$status['errorMessage'] = __( 'The requested myskin does not exist.' );
		mcms_send_json_error( $status );
	}

	// Check filesystem credentials. `delete_myskin()` will bail otherwise.
	$url = mcms_nonce_url( 'myskins.php?action=delete&stylesheet=' . urlencode( $stylesheet ), 'delete-myskin_' . $stylesheet );
	ob_start();
	$credentials = request_filesystem_credentials( $url );
	ob_end_clean();
	if ( false === $credentials || ! MCMS_Filesystem( $credentials ) ) {
		global $mcms_filesystem;

		$status['errorCode']    = 'unable_to_connect_to_filesystem';
		$status['errorMessage'] = __( 'Unable to connect to the filesystem. Please confirm your credentials.' );

		// Pass through the error from MCMS_Filesystem if one was raised.
		if ( $mcms_filesystem instanceof MCMS_Filesystem_Base && is_mcms_error( $mcms_filesystem->errors ) && $mcms_filesystem->errors->get_error_code() ) {
			$status['errorMessage'] = esc_html( $mcms_filesystem->errors->get_error_message() );
		}

		mcms_send_json_error( $status );
	}

	include_once( BASED_TREE_URI . 'mcms-admin/includes/myskin.php' );

	$result = delete_myskin( $stylesheet );

	if ( is_mcms_error( $result ) ) {
		$status['errorMessage'] = $result->get_error_message();
		mcms_send_json_error( $status );
	} elseif ( false === $result ) {
		$status['errorMessage'] = __( 'MySkin could not be deleted.' );
		mcms_send_json_error( $status );
	}

	mcms_send_json_success( $status );
}

/**
 * Ajax handler for installing a module.
 *
 * @since 4.6.0
 *
 * @see Module_Upgrader
 *
 * @global MCMS_Filesystem_Base $mcms_filesystem Subclass
 */
function mcms_ajax_install_module() {
	check_ajax_referer( 'updates' );

	if ( empty( $_POST['slug'] ) ) {
		mcms_send_json_error( array(
			'slug'         => '',
			'errorCode'    => 'no_module_specified',
			'errorMessage' => __( 'No module specified.' ),
		) );
	}

	$status = array(
		'install' => 'module',
		'slug'    => sanitize_key( mcms_unslash( $_POST['slug'] ) ),
	);

	if ( ! current_user_can( 'install_modules' ) ) {
		$status['errorMessage'] = __( 'Sorry, you are not allowed to install modules on this site.' );
		mcms_send_json_error( $status );
	}

	include_once( BASED_TREE_URI . 'mcms-admin/includes/class-mcms-upgrader.php' );
	include_once( BASED_TREE_URI . 'mcms-admin/includes/module-install.php' );

	$api = modules_api( 'module_information', array(
		'slug'   => sanitize_key( mcms_unslash( $_POST['slug'] ) ),
		'fields' => array(
			'sections' => false,
		),
	) );

	if ( is_mcms_error( $api ) ) {
		$status['errorMessage'] = $api->get_error_message();
		mcms_send_json_error( $status );
	}

	$status['moduleName'] = $api->name;

	$skin     = new MCMS_Ajax_Upgrader_Skin();
	$upgrader = new Module_Upgrader( $skin );
	$result   = $upgrader->install( $api->download_link );

	if ( defined( 'MCMS_DEBUG' ) && MCMS_DEBUG ) {
		$status['debug'] = $skin->get_upgrade_messages();
	}

	if ( is_mcms_error( $result ) ) {
		$status['errorCode']    = $result->get_error_code();
		$status['errorMessage'] = $result->get_error_message();
		mcms_send_json_error( $status );
	} elseif ( is_mcms_error( $skin->result ) ) {
		$status['errorCode']    = $skin->result->get_error_code();
		$status['errorMessage'] = $skin->result->get_error_message();
		mcms_send_json_error( $status );
	} elseif ( $skin->get_errors()->get_error_code() ) {
		$status['errorMessage'] = $skin->get_error_messages();
		mcms_send_json_error( $status );
	} elseif ( is_null( $result ) ) {
		global $mcms_filesystem;

		$status['errorCode']    = 'unable_to_connect_to_filesystem';
		$status['errorMessage'] = __( 'Unable to connect to the filesystem. Please confirm your credentials.' );

		// Pass through the error from MCMS_Filesystem if one was raised.
		if ( $mcms_filesystem instanceof MCMS_Filesystem_Base && is_mcms_error( $mcms_filesystem->errors ) && $mcms_filesystem->errors->get_error_code() ) {
			$status['errorMessage'] = esc_html( $mcms_filesystem->errors->get_error_message() );
		}

		mcms_send_json_error( $status );
	}

	$install_status = install_module_install_status( $api );
	$pagenow = isset( $_POST['pagenow'] ) ? sanitize_key( $_POST['pagenow'] ) : '';

	// If installation request is coming from import page, do not return network activation link.
	$modules_url = ( 'import' === $pagenow ) ? admin_url( 'modules.php' ) : network_admin_url( 'modules.php' );

	if ( current_user_can( 'activate_module', $install_status['file'] ) && is_module_inactive( $install_status['file'] ) ) {
		$status['activateUrl'] = add_query_arg( array(
			'_mcmsnonce' => mcms_create_nonce( 'activate-module_' . $install_status['file'] ),
			'action'   => 'activate',
			'module'   => $install_status['file'],
		), $modules_url );
	}

	if ( is_multisite() && current_user_can( 'manage_network_modules' ) && 'import' !== $pagenow ) {
		$status['activateUrl'] = add_query_arg( array( 'networkwide' => 1 ), $status['activateUrl'] );
	}

	mcms_send_json_success( $status );
}

/**
 * Ajax handler for updating a module.
 *
 * @since 4.2.0
 *
 * @see Module_Upgrader
 *
 * @global MCMS_Filesystem_Base $mcms_filesystem Subclass
 */
function mcms_ajax_update_module() {
	check_ajax_referer( 'updates' );

	if ( empty( $_POST['module'] ) || empty( $_POST['slug'] ) ) {
		mcms_send_json_error( array(
			'slug'         => '',
			'errorCode'    => 'no_module_specified',
			'errorMessage' => __( 'No module specified.' ),
		) );
	}

	$module = module_basename( sanitize_text_field( mcms_unslash( $_POST['module'] ) ) );

	$status = array(
		'update'     => 'module',
		'slug'       => sanitize_key( mcms_unslash( $_POST['slug'] ) ),
		'oldVersion' => '',
		'newVersion' => '',
	);

	if ( ! current_user_can( 'update_modules' ) || 0 !== validate_file( $module ) ) {
		$status['errorMessage'] = __( 'Sorry, you are not allowed to update modules for this site.' );
		mcms_send_json_error( $status );
	}

	$module_data          = get_module_data( MCMS_PLUGIN_DIR . '/' . $module );
	$status['module']     = $module;
	$status['moduleName'] = $module_data['Name'];

	if ( $module_data['Version'] ) {
		/* translators: %s: Module version */
		$status['oldVersion'] = sprintf( __( 'Version %s' ), $module_data['Version'] );
	}

	include_once( BASED_TREE_URI . 'mcms-admin/includes/class-mcms-upgrader.php' );

	mcms_update_modules();

	$skin     = new MCMS_Ajax_Upgrader_Skin();
	$upgrader = new Module_Upgrader( $skin );
	$result   = $upgrader->bulk_upgrade( array( $module ) );

	if ( defined( 'MCMS_DEBUG' ) && MCMS_DEBUG ) {
		$status['debug'] = $skin->get_upgrade_messages();
	}

	if ( is_mcms_error( $skin->result ) ) {
		$status['errorCode']    = $skin->result->get_error_code();
		$status['errorMessage'] = $skin->result->get_error_message();
		mcms_send_json_error( $status );
	} elseif ( $skin->get_errors()->get_error_code() ) {
		$status['errorMessage'] = $skin->get_error_messages();
		mcms_send_json_error( $status );
	} elseif ( is_array( $result ) && ! empty( $result[ $module ] ) ) {
		$module_update_data = current( $result );

		/*
		 * If the `update_modules` site transient is empty (e.g. when you update
		 * two modules in quick succession before the transient repopulates),
		 * this may be the return.
		 *
		 * Preferably something can be done to ensure `update_modules` isn't empty.
		 * For now, surface some sort of error here.
		 */
		if ( true === $module_update_data ) {
			$status['errorMessage'] = __( 'Module update failed.' );
			mcms_send_json_error( $status );
		}

		$module_data = get_modules( '/' . $result[ $module ]['destination_name'] );
		$module_data = reset( $module_data );

		if ( $module_data['Version'] ) {
			/* translators: %s: Module version */
			$status['newVersion'] = sprintf( __( 'Version %s' ), $module_data['Version'] );
		}
		mcms_send_json_success( $status );
	} elseif ( false === $result ) {
		global $mcms_filesystem;

		$status['errorCode']    = 'unable_to_connect_to_filesystem';
		$status['errorMessage'] = __( 'Unable to connect to the filesystem. Please confirm your credentials.' );

		// Pass through the error from MCMS_Filesystem if one was raised.
		if ( $mcms_filesystem instanceof MCMS_Filesystem_Base && is_mcms_error( $mcms_filesystem->errors ) && $mcms_filesystem->errors->get_error_code() ) {
			$status['errorMessage'] = esc_html( $mcms_filesystem->errors->get_error_message() );
		}

		mcms_send_json_error( $status );
	}

	// An unhandled error occurred.
	$status['errorMessage'] = __( 'Module update failed.' );
	mcms_send_json_error( $status );
}

/**
 * Ajax handler for deleting a module.
 *
 * @since 4.6.0
 *
 * @see delete_modules()
 *
 * @global MCMS_Filesystem_Base $mcms_filesystem Subclass
 */
function mcms_ajax_delete_module() {
	check_ajax_referer( 'updates' );

	if ( empty( $_POST['slug'] ) || empty( $_POST['module'] ) ) {
		mcms_send_json_error( array(
			'slug'         => '',
			'errorCode'    => 'no_module_specified',
			'errorMessage' => __( 'No module specified.' ),
		) );
	}

	$module = module_basename( sanitize_text_field( mcms_unslash( $_POST['module'] ) ) );

	$status = array(
		'delete' => 'module',
		'slug'   => sanitize_key( mcms_unslash( $_POST['slug'] ) ),
	);

	if ( ! current_user_can( 'delete_modules' ) || 0 !== validate_file( $module ) ) {
		$status['errorMessage'] = __( 'Sorry, you are not allowed to delete modules for this site.' );
		mcms_send_json_error( $status );
	}

	$module_data          = get_module_data( MCMS_PLUGIN_DIR . '/' . $module );
	$status['module']     = $module;
	$status['moduleName'] = $module_data['Name'];

	if ( is_module_active( $module ) ) {
		$status['errorMessage'] = __( 'You cannot delete a module while it is active on the main site.' );
		mcms_send_json_error( $status );
	}

	// Check filesystem credentials. `delete_modules()` will bail otherwise.
	$url = mcms_nonce_url( 'modules.php?action=delete-selected&verify-delete=1&checked[]=' . $module, 'bulk-modules' );
	ob_start();
	$credentials = request_filesystem_credentials( $url );
	ob_end_clean();
	if ( false === $credentials || ! MCMS_Filesystem( $credentials ) ) {
		global $mcms_filesystem;

		$status['errorCode']    = 'unable_to_connect_to_filesystem';
		$status['errorMessage'] = __( 'Unable to connect to the filesystem. Please confirm your credentials.' );

		// Pass through the error from MCMS_Filesystem if one was raised.
		if ( $mcms_filesystem instanceof MCMS_Filesystem_Base && is_mcms_error( $mcms_filesystem->errors ) && $mcms_filesystem->errors->get_error_code() ) {
			$status['errorMessage'] = esc_html( $mcms_filesystem->errors->get_error_message() );
		}

		mcms_send_json_error( $status );
	}

	$result = delete_modules( array( $module ) );

	if ( is_mcms_error( $result ) ) {
		$status['errorMessage'] = $result->get_error_message();
		mcms_send_json_error( $status );
	} elseif ( false === $result ) {
		$status['errorMessage'] = __( 'Module could not be deleted.' );
		mcms_send_json_error( $status );
	}

	mcms_send_json_success( $status );
}

/**
 * Ajax handler for searching modules.
 *
 * @since 4.6.0
 *
 * @global string $s Search term.
 */
function mcms_ajax_search_modules() {
	check_ajax_referer( 'updates' );

	$pagenow = isset( $_POST['pagenow'] ) ? sanitize_key( $_POST['pagenow'] ) : '';
	if ( 'modules-network' === $pagenow || 'modules' === $pagenow ) {
		set_current_screen( $pagenow );
	}

	/** @var MCMS_Modules_List_Table $mcms_list_table */
	$mcms_list_table = _get_list_table( 'MCMS_Modules_List_Table', array(
		'screen' => get_current_screen(),
	) );

	$status = array();

	if ( ! $mcms_list_table->ajax_user_can() ) {
		$status['errorMessage'] = __( 'Sorry, you are not allowed to manage modules for this site.' );
		mcms_send_json_error( $status );
	}

	// Set the correct requester, so pagination works.
	$_SERVER['REQUEST_URI'] = add_query_arg( array_diff_key( $_POST, array(
		'_ajax_nonce' => null,
		'action'      => null,
	) ), network_admin_url( 'modules.php', 'relative' ) );

	$GLOBALS['s'] = mcms_unslash( $_POST['s'] );

	$mcms_list_table->prepare_items();

	ob_start();
	$mcms_list_table->display();
	$status['count'] = count( $mcms_list_table->items );
	$status['items'] = ob_get_clean();

	mcms_send_json_success( $status );
}

/**
 * Ajax handler for searching modules to install.
 *
 * @since 4.6.0
 */
function mcms_ajax_search_install_modules() {
	check_ajax_referer( 'updates' );

	$pagenow = isset( $_POST['pagenow'] ) ? sanitize_key( $_POST['pagenow'] ) : '';
	if ( 'module-install-network' === $pagenow || 'module-install' === $pagenow ) {
		set_current_screen( $pagenow );
	}

	/** @var MCMS_Module_Install_List_Table $mcms_list_table */
	$mcms_list_table = _get_list_table( 'MCMS_Module_Install_List_Table', array(
		'screen' => get_current_screen(),
	) );

	$status = array();

	if ( ! $mcms_list_table->ajax_user_can() ) {
		$status['errorMessage'] = __( 'Sorry, you are not allowed to manage modules for this site.' );
		mcms_send_json_error( $status );
	}

	// Set the correct requester, so pagination works.
	$_SERVER['REQUEST_URI'] = add_query_arg( array_diff_key( $_POST, array(
		'_ajax_nonce' => null,
		'action'      => null,
	) ), network_admin_url( 'module-install.php', 'relative' ) );

	$mcms_list_table->prepare_items();

	ob_start();
	$mcms_list_table->display();
	$status['count'] = (int) $mcms_list_table->get_pagination_arg( 'total_items' );
	$status['items'] = ob_get_clean();

	mcms_send_json_success( $status );
}

/**
 * Ajax handler for editing a myskin or module file.
 *
 * @since 4.9.0
 * @see mcms_edit_myskin_module_file()
 */
function mcms_ajax_edit_myskin_module_file() {
	$r = mcms_edit_myskin_module_file( mcms_unslash( $_POST ) ); // Validation of args is done in mcms_edit_myskin_module_file().
	if ( is_mcms_error( $r ) ) {
		mcms_send_json_error( array_merge(
			array(
				'code' => $r->get_error_code(),
				'message' => $r->get_error_message(),
			),
			(array) $r->get_error_data()
		) );
	} else {
		mcms_send_json_success( array(
			'message' => __( 'File edited successfully.' ),
		) );
	}
}

/**
 * Ajax handler for exporting a user's personal data.
 *
 * @since 4.9.6
 */
function mcms_ajax_mcms_privacy_export_personal_data() {

	if ( empty( $_POST['id'] ) ) {
		mcms_send_json_error( __( 'Missing request ID.' ) );
	}
	$request_id = (int) $_POST['id'];

	if ( $request_id < 1 ) {
		mcms_send_json_error( __( 'Invalid request ID.' ) );
	}

	if ( ! current_user_can( 'export_others_personal_data' ) ) {
		mcms_send_json_error( __( 'Invalid request.' ) );
	}

	check_ajax_referer( 'mcms-privacy-export-personal-data-' . $request_id, 'security' );

	// Get the request data.
	$request = mcms_get_user_request_data( $request_id );

	if ( ! $request || 'export_personal_data' !== $request->action_name ) {
		mcms_send_json_error( __( 'Invalid request type.' ) );
	}

	$email_address = $request->email;
	if ( ! is_email( $email_address ) ) {
		mcms_send_json_error( __( 'A valid email address must be given.' ) );
	}

	if ( ! isset( $_POST['exporter'] ) ) {
		mcms_send_json_error( __( 'Missing exporter index.' ) );
	}
	$exporter_index = (int) $_POST['exporter'];

	if ( ! isset( $_POST['page'] ) ) {
		mcms_send_json_error( __( 'Missing page index.' ) );
	}
	$page = (int) $_POST['page'];

	$send_as_email = isset( $_POST['sendAsEmail'] ) ? ( 'true' === $_POST['sendAsEmail'] ) : false;

	/**
	 * Filters the array of exporter callbacks.
	 *
	 * @since 4.9.6
	 *
	 * @param array $args {
	 *     An array of callable exporters of personal data. Default empty array.
	 *
	 *     @type array {
	 *         Array of personal data exporters.
	 *
	 *         @type string $callback               Callable exporter function that accepts an
	 *                                              email address and a page and returns an array
	 *                                              of name => value pairs of personal data.
	 *         @type string $exporter_friendly_name Translated user facing friendly name for the
	 *                                              exporter.
	 *     }
	 * }
	 */
	$exporters = apply_filters( 'mcms_privacy_personal_data_exporters', array() );

	if ( ! is_array( $exporters ) ) {
		mcms_send_json_error( __( 'An exporter has improperly used the registration filter.' ) );
	}

	// Do we have any registered exporters?
	if ( 0 < count( $exporters ) ) {
		if ( $exporter_index < 1 ) {
			mcms_send_json_error( __( 'Exporter index cannot be negative.' ) );
		}

		if ( $exporter_index > count( $exporters ) ) {
			mcms_send_json_error( __( 'Exporter index out of range.' ) );
		}

		if ( $page < 1 ) {
			mcms_send_json_error( __( 'Page index cannot be less than one.' ) );
		}

		$exporter_keys = array_keys( $exporters );
		$exporter_key  = $exporter_keys[ $exporter_index - 1 ];
		$exporter      = $exporters[ $exporter_key ];

		if ( ! is_array( $exporter ) ) {
			mcms_send_json_error(
				/* translators: %s: array index */
				sprintf( __( 'Expected an array describing the exporter at index %s.' ), $exporter_key )
			);
		}
		if ( ! array_key_exists( 'exporter_friendly_name', $exporter ) ) {
			mcms_send_json_error(
				/* translators: %s: array index */
				sprintf( __( 'Exporter array at index %s does not include a friendly name.' ), $exporter_key )
			);
		}
		if ( ! array_key_exists( 'callback', $exporter ) ) {
			mcms_send_json_error(
				/* translators: %s: exporter friendly name */
				sprintf( __( 'Exporter does not include a callback: %s.' ), esc_html( $exporter['exporter_friendly_name'] ) )
			);
		}
		if ( ! is_callable( $exporter['callback'] ) ) {
			mcms_send_json_error(
				/* translators: %s: exporter friendly name */
				sprintf( __( 'Exporter callback is not a valid callback: %s.' ), esc_html( $exporter['exporter_friendly_name'] ) )
			);
		}

		$callback               = $exporter['callback'];
		$exporter_friendly_name = $exporter['exporter_friendly_name'];

		$response = call_user_func( $callback, $email_address, $page );
		if ( is_mcms_error( $response ) ) {
			mcms_send_json_error( $response );
		}

		if ( ! is_array( $response ) ) {
			mcms_send_json_error(
				/* translators: %s: exporter friendly name */
				sprintf( __( 'Expected response as an array from exporter: %s.' ), esc_html( $exporter_friendly_name ) )
			);
		}
		if ( ! array_key_exists( 'data', $response ) ) {
			mcms_send_json_error(
				/* translators: %s: exporter friendly name */
				sprintf( __( 'Expected data in response array from exporter: %s.' ), esc_html( $exporter_friendly_name ) )
			);
		}
		if ( ! is_array( $response['data'] ) ) {
			mcms_send_json_error(
				/* translators: %s: exporter friendly name */
				sprintf( __( 'Expected data array in response array from exporter: %s.' ), esc_html( $exporter_friendly_name ) )
			);
		}
		if ( ! array_key_exists( 'done', $response ) ) {
			mcms_send_json_error(
				/* translators: %s: exporter friendly name */
				sprintf( __( 'Expected done (boolean) in response array from exporter: %s.' ), esc_html( $exporter_friendly_name ) )
			);
		}
	} else {
		// No exporters, so we're done.
		$exporter_key = '';

		$response = array(
			'data' => array(),
			'done' => true,
		);
	}

	/**
	 * Filters a page of personal data exporter data. Used to build the export report.
	 *
	 * Allows the export response to be consumed by destinations in addition to Ajax.
	 *
	 * @since 4.9.6
	 *
	 * @param array  $response        The personal data for the given exporter and page.
	 * @param int    $exporter_index  The index of the exporter that provided this data.
	 * @param string $email_address   The email address associated with this personal data.
	 * @param int    $page            The page for this response.
	 * @param int    $request_id      The privacy request post ID associated with this request.
	 * @param bool   $send_as_email   Whether the final results of the export should be emailed to the user.
	 * @param string $exporter_key    The key (slug) of the exporter that provided this data.
	 */
	$response = apply_filters( 'mcms_privacy_personal_data_export_page', $response, $exporter_index, $email_address, $page, $request_id, $send_as_email, $exporter_key );

	if ( is_mcms_error( $response ) ) {
		mcms_send_json_error( $response );
	}

	mcms_send_json_success( $response );
}

/**
 * Ajax handler for erasing personal data.
 *
 * @since 4.9.6
 */
function mcms_ajax_mcms_privacy_erase_personal_data() {

	if ( empty( $_POST['id'] ) ) {
		mcms_send_json_error( __( 'Missing request ID.' ) );
	}

	$request_id = (int) $_POST['id'];

	if ( $request_id < 1 ) {
		mcms_send_json_error( __( 'Invalid request ID.' ) );
	}

	// Both capabilities are required to avoid confusion, see `_mcms_personal_data_removal_page()`.
	if ( ! current_user_can( 'erase_others_personal_data' ) || ! current_user_can( 'delete_users' ) ) {
		mcms_send_json_error( __( 'Invalid request.' ) );
	}

	check_ajax_referer( 'mcms-privacy-erase-personal-data-' . $request_id, 'security' );

	// Get the request data.
	$request = mcms_get_user_request_data( $request_id );

	if ( ! $request || 'remove_personal_data' !== $request->action_name ) {
		mcms_send_json_error( __( 'Invalid request ID.' ) );
	}

	$email_address = $request->email;

	if ( ! is_email( $email_address ) ) {
		mcms_send_json_error( __( 'Invalid email address in request.' ) );
	}

	if ( ! isset( $_POST['eraser'] ) ) {
		mcms_send_json_error( __( 'Missing eraser index.' ) );
	}

	$eraser_index = (int) $_POST['eraser'];

	if ( ! isset( $_POST['page'] ) ) {
		mcms_send_json_error( __( 'Missing page index.' ) );
	}

	$page = (int) $_POST['page'];

	/**
	 * Filters the array of personal data eraser callbacks.
	 *
	 * @since 4.9.6
	 *
	 * @param array $args {
	 *     An array of callable erasers of personal data. Default empty array.
	 *
	 *     @type array {
	 *         Array of personal data exporters.
	 *
	 *         @type string $callback               Callable eraser that accepts an email address and
	 *                                              a page and returns an array with boolean values for
	 *                                              whether items were removed or retained and any messages
	 *                                              from the eraser, as well as if additional pages are
	 *                                              available.
	 *         @type string $exporter_friendly_name Translated user facing friendly name for the eraser.
	 *     }
	 * }
	 */
	$erasers = apply_filters( 'mcms_privacy_personal_data_erasers', array() );

	// Do we have any registered erasers?
	if ( 0 < count( $erasers ) ) {

		if ( $eraser_index < 1 ) {
			mcms_send_json_error( __( 'Eraser index cannot be less than one.' ) );
		}

		if ( $eraser_index > count( $erasers ) ) {
			mcms_send_json_error( __( 'Eraser index is out of range.' ) );
		}

		if ( $page < 1 ) {
			mcms_send_json_error( __( 'Page index cannot be less than one.' ) );
		}

		$eraser_keys = array_keys( $erasers );
		$eraser_key  = $eraser_keys[ $eraser_index - 1 ];
		$eraser      = $erasers[ $eraser_key ];

		if ( ! is_array( $eraser ) ) {
			/* translators: %d: array index */
			mcms_send_json_error( sprintf( __( 'Expected an array describing the eraser at index %d.' ), $eraser_index ) );
		}

		if ( ! array_key_exists( 'callback', $eraser ) ) {
			/* translators: %d: array index */
			mcms_send_json_error( sprintf( __( 'Eraser array at index %d does not include a callback.' ), $eraser_index ) );
		}

		if ( ! is_callable( $eraser['callback'] ) ) {
			/* translators: %d: array index */
			mcms_send_json_error( sprintf( __( 'Eraser callback at index %d is not a valid callback.' ), $eraser_index ) );
		}

		if ( ! array_key_exists( 'eraser_friendly_name', $eraser ) ) {
			/* translators: %d: array index */
			mcms_send_json_error( sprintf( __( 'Eraser array at index %d does not include a friendly name.' ), $eraser_index ) );
		}

		$callback             = $eraser['callback'];
		$eraser_friendly_name = $eraser['eraser_friendly_name'];

		$response = call_user_func( $callback, $email_address, $page );

		if ( is_mcms_error( $response ) ) {
			mcms_send_json_error( $response );
		}

		if ( ! is_array( $response ) ) {
			mcms_send_json_error(
				sprintf(
					/* translators: 1: eraser friendly name, 2: array index */
					__( 'Did not receive array from %1$s eraser (index %2$d).' ),
					esc_html( $eraser_friendly_name ),
					$eraser_index
				)
			);
		}

		if ( ! array_key_exists( 'items_removed', $response ) ) {
			mcms_send_json_error(
				sprintf(
					/* translators: 1: eraser friendly name, 2: array index */
					__( 'Expected items_removed key in response array from %1$s eraser (index %2$d).' ),
					esc_html( $eraser_friendly_name ),
					$eraser_index
				)
			);
		}

		if ( ! array_key_exists( 'items_retained', $response ) ) {
			mcms_send_json_error(
				sprintf(
					/* translators: 1: eraser friendly name, 2: array index */
					__( 'Expected items_retained key in response array from %1$s eraser (index %2$d).' ),
					esc_html( $eraser_friendly_name ),
					$eraser_index
				)
			);
		}

		if ( ! array_key_exists( 'messages', $response ) ) {
			mcms_send_json_error(
				sprintf(
					/* translators: 1: eraser friendly name, 2: array index */
					__( 'Expected messages key in response array from %1$s eraser (index %2$d).' ),
					esc_html( $eraser_friendly_name ),
					$eraser_index
				)
			);
		}

		if ( ! is_array( $response['messages'] ) ) {
			mcms_send_json_error(
				sprintf(
					/* translators: 1: eraser friendly name, 2: array index */
					__( 'Expected messages key to reference an array in response array from %1$s eraser (index %2$d).' ),
					esc_html( $eraser_friendly_name ),
					$eraser_index
				)
			);
		}

		if ( ! array_key_exists( 'done', $response ) ) {
			mcms_send_json_error(
				sprintf(
					/* translators: 1: eraser friendly name, 2: array index */
					__( 'Expected done flag in response array from %1$s eraser (index %2$d).' ),
					esc_html( $eraser_friendly_name ),
					$eraser_index
				)
			);
		}
	} else {
		// No erasers, so we're done.
		$eraser_key = '';

		$response = array(
			'items_removed'  => false,
			'items_retained' => false,
			'messages'       => array(),
			'done'           => true,
		);
	}

	/**
	 * Filters a page of personal data eraser data.
	 *
	 * Allows the erasure response to be consumed by destinations in addition to Ajax.
	 *
	 * @since 4.9.6
	 *
	 * @param array  $response        The personal data for the given exporter and page.
	 * @param int    $eraser_index    The index of the eraser that provided this data.
	 * @param string $email_address   The email address associated with this personal data.
	 * @param int    $page            The page for this response.
	 * @param int    $request_id      The privacy request post ID associated with this request.
	 * @param string $eraser_key      The key (slug) of the eraser that provided this data.
	 */
	$response = apply_filters( 'mcms_privacy_personal_data_erasure_page', $response, $eraser_index, $email_address, $page, $request_id, $eraser_key );

	if ( is_mcms_error( $response ) ) {
		mcms_send_json_error( $response );
	}

	mcms_send_json_success( $response );
}
