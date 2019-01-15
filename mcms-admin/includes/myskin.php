<?php
/**
 * MandarinCMS MySkin Administration API
 *
 * @package MandarinCMS
 * @subpackage Administration
 */

/**
 * Remove a myskin
 *
 * @since 2.8.0
 *
 * @global MCMS_Filesystem_Base $mcms_filesystem Subclass
 *
 * @param string $stylesheet Stylesheet of the myskin to delete
 * @param string $redirect Redirect to page when complete.
 * @return void|bool|MCMS_Error When void, echoes content.
 */
function delete_myskin($stylesheet, $redirect = '') {
	global $mcms_filesystem;

	if ( empty($stylesheet) )
		return false;

	if ( empty( $redirect ) ) {
		$redirect = mcms_nonce_url('myskins.php?action=delete&stylesheet=' . urlencode( $stylesheet ), 'delete-myskin_' . $stylesheet);
	}

	ob_start();
	$credentials = request_filesystem_credentials( $redirect );
	$data = ob_get_clean();

	if ( false === $credentials ) {
		if ( ! empty( $data ) ){
			include_once( BASED_TREE_URI . 'mcms-admin/admin-header.php');
			echo $data;
			include( BASED_TREE_URI . 'mcms-admin/admin-footer.php');
			exit;
		}
		return;
	}

	if ( ! MCMS_Filesystem( $credentials ) ) {
		ob_start();
		request_filesystem_credentials( $redirect, '', true ); // Failed to connect, Error and request again.
		$data = ob_get_clean();

		if ( ! empty($data) ) {
			include_once( BASED_TREE_URI . 'mcms-admin/admin-header.php');
			echo $data;
			include( BASED_TREE_URI . 'mcms-admin/admin-footer.php');
			exit;
		}
		return;
	}

	if ( ! is_object($mcms_filesystem) )
		return new MCMS_Error('fs_unavailable', __('Could not access filesystem.'));

	if ( is_mcms_error($mcms_filesystem->errors) && $mcms_filesystem->errors->get_error_code() )
		return new MCMS_Error('fs_error', __('Filesystem error.'), $mcms_filesystem->errors);

	// Get the base module folder.
	$myskins_dir = $mcms_filesystem->mcms_myskins_dir();
	if ( empty( $myskins_dir ) ) {
		return new MCMS_Error( 'fs_no_myskins_dir', __( 'Unable to locate MandarinCMS myskin directory.' ) );
	}

	$myskins_dir = trailingslashit( $myskins_dir );
	$myskin_dir = trailingslashit( $myskins_dir . $stylesheet );
	$deleted = $mcms_filesystem->delete( $myskin_dir, true );

	if ( ! $deleted ) {
		return new MCMS_Error( 'could_not_remove_myskin', sprintf( __( 'Could not fully remove the myskin %s.' ), $stylesheet ) );
	}

	$myskin_translations = mcms_get_installed_translations( 'myskins' );

	// Remove language files, silently.
	if ( ! empty( $myskin_translations[ $stylesheet ] ) ) {
		$translations = $myskin_translations[ $stylesheet ];

		foreach ( $translations as $translation => $data ) {
			$mcms_filesystem->delete( MCMS_LANG_DIR . '/myskins/' . $stylesheet . '-' . $translation . '.po' );
			$mcms_filesystem->delete( MCMS_LANG_DIR . '/myskins/' . $stylesheet . '-' . $translation . '.mo' );
		}
	}

	// Remove the myskin from allowed myskins on the network.
	if ( is_multisite() ) {
		MCMS_MySkin::network_disable_myskin( $stylesheet );
	}

	// Force refresh of myskin update information.
	delete_site_transient( 'update_myskins' );

	return true;
}

/**
 * Get the Page Templates available in this myskin
 *
 * @since 1.5.0
 * @since 4.7.0 Added the `$post_type` parameter.
 *
 * @param MCMS_Post|null $post      Optional. The post being edited, provided for context.
 * @param string       $post_type Optional. Post type to get the templates for. Default 'page'.
 * @return array Key is the template name, value is the filename of the template
 */
function get_page_templates( $post = null, $post_type = 'page' ) {
	return array_flip( mcms_get_myskin()->get_page_templates( $post, $post_type ) );
}

/**
 * Tidies a filename for url display by the myskin editor.
 *
 * @since 2.9.0
 * @access private
 *
 * @param string $fullpath Full path to the myskin file
 * @param string $containingfolder Path of the myskin parent folder
 * @return string
 */
function _get_template_edit_filename($fullpath, $containingfolder) {
	return str_replace(dirname(dirname( $containingfolder )) , '', $fullpath);
}

/**
 * Check if there is an update for a myskin available.
 *
 * Will display link, if there is an update available.
 *
 * @since 2.7.0
 * @see get_myskin_update_available()
 *
 * @param MCMS_MySkin $myskin MySkin data object.
 */
function myskin_update_available( $myskin ) {
	echo get_myskin_update_available( $myskin );
}

/**
 * Retrieve the update link if there is a myskin update available.
 *
 * Will return a link if there is an update available.
 *
 * @since 3.8.0
 *
 * @staticvar object $myskins_update
 *
 * @param MCMS_MySkin $myskin MCMS_MySkin object.
 * @return false|string HTML for the update link, or false if invalid info was passed.
 */
function get_myskin_update_available( $myskin ) {
	static $myskins_update = null;

	if ( !current_user_can('update_myskins' ) )
		return false;

	if ( !isset($myskins_update) )
		$myskins_update = get_site_transient('update_myskins');

	if ( ! ( $myskin instanceof MCMS_MySkin ) ) {
		return false;
	}

	$stylesheet = $myskin->get_stylesheet();

	$html = '';

	if ( isset($myskins_update->response[ $stylesheet ]) ) {
		$update = $myskins_update->response[ $stylesheet ];
		$myskin_name = $myskin->display('Name');
		$details_url = add_query_arg(array('TB_iframe' => 'true', 'width' => 1024, 'height' => 800), $update['url']); //MySkin browser inside MCMS? replace this, Also, myskin preview JS will override this on the available list.
		$update_url = mcms_nonce_url( admin_url( 'update.php?action=upgrade-myskin&amp;myskin=' . urlencode( $stylesheet ) ), 'upgrade-myskin_' . $stylesheet );

		if ( !is_multisite() ) {
			if ( ! current_user_can('update_myskins') ) {
				/* translators: 1: myskin name, 2: myskin details URL, 3: additional link attributes, 4: version number */
				$html = sprintf( '<p><strong>' . __( 'There is a new version of %1$s available. <a href="%2$s" %3$s>View version %4$s details</a>.' ) . '</strong></p>',
					$myskin_name,
					esc_url( $details_url ),
					sprintf( 'class="thickbox open-module-details-modal" aria-label="%s"',
						/* translators: 1: myskin name, 2: version number */
						esc_attr( sprintf( __( 'View %1$s version %2$s details' ), $myskin_name, $update['new_version'] ) )
					),
					$update['new_version']
				);
			} elseif ( empty( $update['package'] ) ) {
				/* translators: 1: myskin name, 2: myskin details URL, 3: additional link attributes, 4: version number */
				$html = sprintf( '<p><strong>' . __( 'There is a new version of %1$s available. <a href="%2$s" %3$s>View version %4$s details</a>. <em>Automatic update is unavailable for this myskin.</em>' ) . '</strong></p>',
					$myskin_name,
					esc_url( $details_url ),
					sprintf( 'class="thickbox open-module-details-modal" aria-label="%s"',
						/* translators: 1: myskin name, 2: version number */
						esc_attr( sprintf( __( 'View %1$s version %2$s details' ), $myskin_name, $update['new_version'] ) )
					),
					$update['new_version']
				);
			} else {
				/* translators: 1: myskin name, 2: myskin details URL, 3: additional link attributes, 4: version number, 5: update URL, 6: additional link attributes */
				$html = sprintf( '<p><strong>' . __( 'There is a new version of %1$s available. <a href="%2$s" %3$s>View version %4$s details</a> or <a href="%5$s" %6$s>update now</a>.' ) . '</strong></p>',
					$myskin_name,
					esc_url( $details_url ),
					sprintf( 'class="thickbox open-module-details-modal" aria-label="%s"',
						/* translators: 1: myskin name, 2: version number */
						esc_attr( sprintf( __( 'View %1$s version %2$s details' ), $myskin_name, $update['new_version'] ) )
					),
					$update['new_version'],
					$update_url,
					sprintf( 'aria-label="%s" id="update-myskin" data-slug="%s"',
						/* translators: %s: myskin name */
						esc_attr( sprintf( __( 'Update %s now' ), $myskin_name ) ),
						$stylesheet
					)
				);
			}
		}
	}

	return $html;
}

/**
 * Retrieve list of MandarinCMS myskin features (aka myskin tags)
 *
 * @since 3.1.0
 *
 * @param bool $api Optional. Whether try to fetch tags from the MandarinCMS.org API. Defaults to true.
 * @return array Array of features keyed by category with translations keyed by slug.
 */
function get_myskin_feature_list( $api = true ) {
	// Hard-coded list is used if api not accessible.
	$features = array(

		__( 'Subject' )  => array(
			'blog'           => __( 'Blog' ),
			'e-commerce'     => __( 'E-Commerce' ),
			'education'      => __( 'Education' ),
			'entertainment'  => __( 'Entertainment' ),
			'food-and-drink' => __( 'Food & Drink' ),
			'holiday'        => __( 'Holiday' ),
			'news'           => __( 'News' ),
			'photography'    => __( 'Photography' ),
			'portfolio'      => __( 'Portfolio' ),
		),

		__( 'Features' ) => array(
			'accessibility-ready'   => __( 'Accessibility Ready' ),
			'custom-background'     => __( 'Custom Background' ),
			'custom-colors'         => __( 'Custom Colors' ),
			'custom-header'         => __( 'Custom Header' ),
			'custom-logo'           => __( 'Custom Logo' ),
			'editor-style'          => __( 'Editor Style' ),
			'featured-image-header' => __( 'Featured Image Header' ),
			'featured-images'       => __( 'Featured Images' ),
			'footer-widgets'        => __( 'Footer Widgets' ),
			'full-width-template'   => __( 'Full Width Template' ),
			'post-formats'          => __( 'Post Formats' ),
			'sticky-post'           => __( 'Sticky Post' ),
			'myskin-options'         => __( 'MySkin Options' ),
		),

		__( 'Layout' ) => array(
			'grid-layout'   => __( 'Grid Layout' ),
			'one-column'    => __( 'One Column' ),
			'two-columns'   => __( 'Two Columns' ),
			'three-columns' => __( 'Three Columns' ),
			'four-columns'  => __( 'Four Columns' ),
			'left-sidebar'  => __( 'Left Sidebar' ),
			'right-sidebar' => __( 'Right Sidebar' ),
		)

	);

	if ( ! $api || ! current_user_can( 'install_myskins' ) )
		return $features;

	if ( !$feature_list = get_site_transient( 'mcmsorg_myskin_feature_list' ) )
		set_site_transient( 'mcmsorg_myskin_feature_list', array(), 3 * HOUR_IN_SECONDS );

	if ( !$feature_list ) {
		$feature_list = myskins_api( 'feature_list', array() );
		if ( is_mcms_error( $feature_list ) )
			return $features;
	}

	if ( !$feature_list )
		return $features;

	set_site_transient( 'mcmsorg_myskin_feature_list', $feature_list, 3 * HOUR_IN_SECONDS );

	$category_translations = array(
		'Layout'   => __( 'Layout' ),
		'Features' => __( 'Features' ),
		'Subject'  => __( 'Subject' ),
	);

	// Loop over the mcmsorg canonical list and apply translations
	$mcmsorg_features = array();
	foreach ( (array) $feature_list as $feature_category => $feature_items ) {
		if ( isset($category_translations[$feature_category]) )
			$feature_category = $category_translations[$feature_category];
		$mcmsorg_features[$feature_category] = array();

		foreach ( $feature_items as $feature ) {
			if ( isset($features[$feature_category][$feature]) )
				$mcmsorg_features[$feature_category][$feature] = $features[$feature_category][$feature];
			else
				$mcmsorg_features[$feature_category][$feature] = $feature;
		}
	}

	return $mcmsorg_features;
}

/**
 * Retrieves myskin installer pages from the MandarinCMS.org MySkins API.
 *
 * It is possible for a myskin to override the MySkins API result with three
 * filters. Assume this is for myskins, which can extend on the MySkin Info to
 * offer more choices. This is very powerful and must be used with care, when
 * overriding the filters.
 *
 * The first filter, {@see 'myskins_api_args'}, is for the args and gives the action
 * as the second parameter. The hook for {@see 'myskins_api_args'} must ensure that
 * an object is returned.
 *
 * The second filter, {@see 'myskins_api'}, allows a module to override the MandarinCMS.org
 * MySkin API entirely. If `$action` is 'query_myskins', 'myskin_information', or 'feature_list',
 * an object MUST be passed. If `$action` is 'hot_tags', an array should be passed.
 *
 * Finally, the third filter, {@see 'myskins_api_result'}, makes it possible to filter the
 * response object or array, depending on the `$action` type.
 *
 * Supported arguments per action:
 *
 * | Argument Name      | 'query_myskins' | 'myskin_information' | 'hot_tags' | 'feature_list'   |
 * | -------------------| :------------: | :-----------------: | :--------: | :--------------: |
 * | `$slug`            | No             |  Yes                | No         | No               |
 * | `$per_page`        | Yes            |  No                 | No         | No               |
 * | `$page`            | Yes            |  No                 | No         | No               |
 * | `$number`          | No             |  No                 | Yes        | No               |
 * | `$search`          | Yes            |  No                 | No         | No               |
 * | `$tag`             | Yes            |  No                 | No         | No               |
 * | `$author`          | Yes            |  No                 | No         | No               |
 * | `$user`            | Yes            |  No                 | No         | No               |
 * | `$browse`          | Yes            |  No                 | No         | No               |
 * | `$locale`          | Yes            |  Yes                | No         | No               |
 * | `$fields`          | Yes            |  Yes                | No         | No               |
 *
 * @since 2.8.0
 *
 * @param string       $action API action to perform: 'query_myskins', 'myskin_information',
 *                             'hot_tags' or 'feature_list'.
 * @param array|object $args   {
 *     Optional. Array or object of arguments to serialize for the MySkins API.
 *
 *     @type string  $slug     The myskin slug. Default empty.
 *     @type int     $per_page Number of myskins per page. Default 24.
 *     @type int     $page     Number of current page. Default 1.
 *     @type int     $number   Number of tags to be queried.
 *     @type string  $search   A search term. Default empty.
 *     @type string  $tag      Tag to filter myskins. Default empty.
 *     @type string  $author   Username of an author to filter myskins. Default empty.
 *     @type string  $user     Username to query for their favorites. Default empty.
 *     @type string  $browse   Browse view: 'featured', 'popular', 'updated', 'favorites'.
 *     @type string  $locale   Locale to provide context-sensitive results. Default is the value of get_locale().
 *     @type array   $fields   {
 *         Array of fields which should or should not be returned.
 *
 *         @type bool $description        Whether to return the myskin full description. Default false.
 *         @type bool $sections           Whether to return the myskin readme sections: description, installation,
 *                                        FAQ, screenshots, other notes, and changelog. Default false.
 *         @type bool $rating             Whether to return the rating in percent and total number of ratings.
 *                                        Default false.
 *         @type bool $ratings            Whether to return the number of rating for each star (1-5). Default false.
 *         @type bool $downloaded         Whether to return the download count. Default false.
 *         @type bool $downloadlink       Whether to return the download link for the package. Default false.
 *         @type bool $last_updated       Whether to return the date of the last update. Default false.
 *         @type bool $tags               Whether to return the assigned tags. Default false.
 *         @type bool $homepage           Whether to return the myskin homepage link. Default false.
 *         @type bool $screenshots        Whether to return the screenshots. Default false.
 *         @type int  $screenshot_count   Number of screenshots to return. Default 1.
 *         @type bool $screenshot_url     Whether to return the URL of the first screenshot. Default false.
 *         @type bool $photon_screenshots Whether to return the screenshots via Photon. Default false.
 *         @type bool $template           Whether to return the slug of the parent myskin. Default false.
 *         @type bool $parent             Whether to return the slug, name and homepage of the parent myskin. Default false.
 *         @type bool $versions           Whether to return the list of all available versions. Default false.
 *         @type bool $myskin_url          Whether to return myskin's URL. Default false.
 *         @type bool $extended_author    Whether to return nicename or nicename and display name. Default false.
 *     }
 * }
 * @return object|array|MCMS_Error Response object or array on success, MCMS_Error on failure. See the
 *         {@link https://developer.mandarincms.com/reference/functions/myskins_api/ function reference article}
 *         for more information on the make-up of possible return objects depending on the value of `$action`.
 */
function myskins_api( $action, $args = array() ) {

	if ( is_array( $args ) ) {
		$args = (object) $args;
	}

	if ( ! isset( $args->per_page ) ) {
		$args->per_page = 24;
	}

	if ( ! isset( $args->locale ) ) {
		$args->locale = get_user_locale();
	}

	/**
	 * Filters arguments used to query for installer pages from the MandarinCMS.org MySkins API.
	 *
	 * Important: An object MUST be returned to this filter.
	 *
	 * @since 2.8.0
	 *
	 * @param object $args   Arguments used to query for installer pages from the MandarinCMS.org MySkins API.
	 * @param string $action Requested action. Likely values are 'myskin_information',
	 *                       'feature_list', or 'query_myskins'.
	 */
	$args = apply_filters( 'myskins_api_args', $args, $action );

	/**
	 * Filters whether to override the MandarinCMS.org MySkins API.
	 *
	 * Passing a non-false value will effectively short-circuit the MandarinCMS.org API request.
	 *
	 * If `$action` is 'query_myskins', 'myskin_information', or 'feature_list', an object MUST
	 * be passed. If `$action` is 'hot_tags', an array should be passed.
	 *
	 * @since 2.8.0
	 *
	 * @param false|object|array $override Whether to override the MandarinCMS.org MySkins API. Default false.
	 * @param string             $action   Requested action. Likely values are 'myskin_information',
	 *                                    'feature_list', or 'query_myskins'.
	 * @param object             $args     Arguments used to query for installer pages from the MySkins API.
	 */
	$res = apply_filters( 'myskins_api', false, $action, $args );

	if ( ! $res ) {
		// include an unmodified $mcms_version
		include( BASED_TREE_URI . MCMSINC . '/version.php' );

		$url = $http_url = 'http://api.mandarincms.com/myskins/info/1.0/';
		if ( $ssl = mcms_http_supports( array( 'ssl' ) ) )
			$url = set_url_scheme( $url, 'https' );

		$http_args = array(
			'user-agent' => 'MandarinCMS/' . $mcms_version . '; ' . home_url( '/' ),
			'body' => array(
				'action' => $action,
				'request' => serialize( $args )
			)
		);
		$request = mcms_remote_post( $url, $http_args );

		if ( $ssl && is_mcms_error( $request ) ) {
			if ( ! mcms_doing_ajax() ) {
				trigger_error(
					sprintf(
						/* translators: %s: support forums URL */
						__( 'An unexpected error occurred. Something may be wrong with MandarinCMS.org or this server&#8217;s configuration. If you continue to have problems, please try the <a href="%s">support forums</a>.' ),
						__( 'https://mandarincms.com/support/' )
					) . ' ' . __( '(MandarinCMS could not establish a secure connection to MandarinCMS.org. Please contact your server administrator.)' ),
					headers_sent() || MCMS_DEBUG ? E_USER_WARNING : E_USER_NOTICE
				);
			}
			$request = mcms_remote_post( $http_url, $http_args );
		}

		if ( is_mcms_error($request) ) {
			$res = new MCMS_Error( 'myskins_api_failed',
				sprintf(
					/* translators: %s: support forums URL */
					__( 'An unexpected error occurred. Something may be wrong with MandarinCMS.org or this server&#8217;s configuration. If you continue to have problems, please try the <a href="%s">support forums</a>.' ),
					__( 'https://mandarincms.com/support/' )
				),
				$request->get_error_message()
			);
		} else {
			$res = maybe_unserialize( mcms_remote_retrieve_body( $request ) );
			if ( ! is_object( $res ) && ! is_array( $res ) ) {
				$res = new MCMS_Error( 'myskins_api_failed',
					sprintf(
						/* translators: %s: support forums URL */
						__( 'An unexpected error occurred. Something may be wrong with MandarinCMS.org or this server&#8217;s configuration. If you continue to have problems, please try the <a href="%s">support forums</a>.' ),
						__( 'https://mandarincms.com/support/' )
					),
					mcms_remote_retrieve_body( $request )
				);
			}
		}
	}

	/**
	 * Filters the returned MandarinCMS.org MySkins API response.
	 *
	 * @since 2.8.0
	 *
	 * @param array|object|MCMS_Error $res    MandarinCMS.org MySkins API response.
	 * @param string                $action Requested action. Likely values are 'myskin_information',
	 *                                      'feature_list', or 'query_myskins'.
	 * @param object                $args   Arguments used to query for installer pages from the MandarinCMS.org MySkins API.
	 */
	return apply_filters( 'myskins_api_result', $res, $action, $args );
}

/**
 * Prepare myskins for JavaScript.
 *
 * @since 3.8.0
 *
 * @param array $myskins Optional. Array of MCMS_MySkin objects to prepare.
 *                      Defaults to all allowed myskins.
 *
 * @return array An associative array of myskin data, sorted by name.
 */
function mcms_prepare_myskins_for_js( $myskins = null ) {
	$current_myskin = get_stylesheet();

	/**
	 * Filters myskin data before it is prepared for JavaScript.
	 *
	 * Passing a non-empty array will result in mcms_prepare_myskins_for_js() returning
	 * early with that value instead.
	 *
	 * @since 4.2.0
	 *
	 * @param array      $prepared_myskins An associative array of myskin data. Default empty array.
	 * @param null|array $myskins          An array of MCMS_MySkin objects to prepare, if any.
	 * @param string     $current_myskin   The current myskin slug.
	 */
	$prepared_myskins = (array) apply_filters( 'pre_prepare_myskins_for_js', array(), $myskins, $current_myskin );

	if ( ! empty( $prepared_myskins ) ) {
		return $prepared_myskins;
	}

	// Make sure the current myskin is listed first.
	$prepared_myskins[ $current_myskin ] = array();

	if ( null === $myskins ) {
		$myskins = mcms_get_myskins( array( 'allowed' => true ) );
		if ( ! isset( $myskins[ $current_myskin ] ) ) {
			$myskins[ $current_myskin ] = mcms_get_myskin();
		}
	}

	$updates = array();
	if ( current_user_can( 'update_myskins' ) ) {
		$updates_transient = get_site_transient( 'update_myskins' );
		if ( isset( $updates_transient->response ) ) {
			$updates = $updates_transient->response;
		}
	}

	MCMS_MySkin::sort_by_name( $myskins );

	$parents = array();

	foreach ( $myskins as $myskin ) {
		$slug = $myskin->get_stylesheet();
		$encoded_slug = urlencode( $slug );

		$parent = false;
		if ( $myskin->parent() ) {
			$parent = $myskin->parent();
			$parents[ $slug ] = $parent->get_stylesheet();
			$parent = $parent->display( 'Name' );
		}

		$customize_action = null;
		if ( current_user_can( 'edit_myskin_options' ) && current_user_can( 'customize' ) ) {
			$customize_action = esc_url( add_query_arg(
				array(
					'return' => urlencode( esc_url_raw( remove_query_arg( mcms_removable_query_args(), mcms_unslash( $_SERVER['REQUEST_URI'] ) ) ) ),
				),
				mcms_customize_url( $slug )
			) );
		}

		$prepared_myskins[ $slug ] = array(
			'id'           => $slug,
			'name'         => $myskin->display( 'Name' ),
			'screenshot'   => array( $myskin->get_screenshot() ), // @todo multiple
			'description'  => $myskin->display( 'Description' ),
			'author'       => $myskin->display( 'Author', false, true ),
			'authorAndUri' => $myskin->display( 'Author' ),
			'version'      => $myskin->display( 'Version' ),
			'tags'         => $myskin->display( 'Tags' ),
			'parent'       => $parent,
			'active'       => $slug === $current_myskin,
			'hasUpdate'    => isset( $updates[ $slug ] ),
			'hasPackage'   => isset( $updates[ $slug ] ) && ! empty( $updates[ $slug ][ 'package' ] ),
			'update'       => get_myskin_update_available( $myskin ),
			'actions'      => array(
				'activate' => current_user_can( 'switch_myskins' ) ? mcms_nonce_url( admin_url( 'myskins.php?action=activate&amp;stylesheet=' . $encoded_slug ), 'switch-myskin_' . $slug ) : null,
				'customize' => $customize_action,
				'delete'   => current_user_can( 'delete_myskins' ) ? mcms_nonce_url( admin_url( 'myskins.php?action=delete&amp;stylesheet=' . $encoded_slug ), 'delete-myskin_' . $slug ) : null,
			),
		);
	}

	// Remove 'delete' action if myskin has an active child
	if ( ! empty( $parents ) && array_key_exists( $current_myskin, $parents ) ) {
		unset( $prepared_myskins[ $parents[ $current_myskin ] ]['actions']['delete'] );
	}

	/**
	 * Filters the myskins prepared for JavaScript, for myskins.php.
	 *
	 * Could be useful for changing the order, which is by name by default.
	 *
	 * @since 3.8.0
	 *
	 * @param array $prepared_myskins Array of myskins.
	 */
	$prepared_myskins = apply_filters( 'mcms_prepare_myskins_for_js', $prepared_myskins );
	$prepared_myskins = array_values( $prepared_myskins );
	return array_filter( $prepared_myskins );
}

/**
 * Print JS templates for the myskin-browsing UI in the Customizer.
 *
 * @since 4.2.0
 */
function customize_myskins_print_templates() {
	?>
	<script type="text/html" id="tmpl-customize-myskins-details-view">
		<div class="myskin-backdrop"></div>
		<div class="myskin-wrap mcms-clearfix" role="document">
			<div class="myskin-header">
				<button type="button" class="left dashicons dashicons-no"><span class="screen-reader-text"><?php _e( 'Show previous myskin' ); ?></span></button>
				<button type="button" class="right dashicons dashicons-no"><span class="screen-reader-text"><?php _e( 'Show next myskin' ); ?></span></button>
				<button type="button" class="close dashicons dashicons-no"><span class="screen-reader-text"><?php _e( 'Close details dialog' ); ?></span></button>
			</div>
			<div class="myskin-about mcms-clearfix">
				<div class="myskin-screenshots">
				<# if ( data.screenshot && data.screenshot[0] ) { #>
					<div class="screenshot"><img src="{{ data.screenshot[0] }}" alt="" /></div>
				<# } else { #>
					<div class="screenshot blank"></div>
				<# } #>
				</div>

				<div class="myskin-info">
					<# if ( data.active ) { #>
						<span class="current-label"><?php _e( 'Current MySkin' ); ?></span>
					<# } #>
					<h2 class="myskin-name">{{{ data.name }}}<span class="myskin-version"><?php printf( __( 'Version: %s' ), '{{ data.version }}' ); ?></span></h2>
					<h3 class="myskin-author"><?php printf( __( 'By %s' ), '{{{ data.authorAndUri }}}' ); ?></h3>

					<# if ( data.stars && 0 != data.num_ratings ) { #>
						<div class="myskin-rating">
							{{{ data.stars }}}
							<span class="num-ratings">
								<?php
								/* translators: %s: number of ratings */
								echo sprintf( __( '(%s ratings)' ), '{{ data.num_ratings }}' );
								?>
							</span>
						</div>
					<# } #>

					<# if ( data.hasUpdate ) { #>
						<div class="notice notice-warning notice-alt notice-large" data-slug="{{ data.id }}">
							<h3 class="notice-title"><?php _e( 'Update Available' ); ?></h3>
							{{{ data.update }}}
						</div>
					<# } #>

					<# if ( data.parent ) { #>
						<p class="parent-myskin"><?php printf( __( 'This is a child myskin of %s.' ), '<strong>{{{ data.parent }}}</strong>' ); ?></p>
					<# } #>

					<p class="myskin-description">{{{ data.description }}}</p>

					<# if ( data.tags ) { #>
						<p class="myskin-tags"><span><?php _e( 'Tags:' ); ?></span> {{{ data.tags }}}</p>
					<# } #>
				</div>
			</div>

			<div class="myskin-actions">
				<# if ( data.active ) { #>
					<button type="button" class="button button-primary customize-myskin"><?php _e( 'Customize' ); ?></button>
				<# } else if ( 'installed' === data.type ) { #>
					<?php if ( current_user_can( 'delete_myskins' ) ) { ?>
						<# if ( data.actions && data.actions['delete'] ) { #>
							<a href="{{{ data.actions['delete'] }}}" data-slug="{{ data.id }}" class="button button-secondary delete-myskin"><?php _e( 'Delete' ); ?></a>
						<# } #>
					<?php } ?>
					<button type="button" class="button button-primary preview-myskin" data-slug="{{ data.id }}"><?php _e( 'Live Preview' ); ?></button>
				<# } else { #>
					<button type="button" class="button myskin-install" data-slug="{{ data.id }}"><?php _e( 'Install' ); ?></button>
					<button type="button" class="button button-primary myskin-install preview" data-slug="{{ data.id }}"><?php _e( 'Install &amp; Preview' ); ?></button>
				<# } #>
			</div>
		</div>
	</script>
	<?php
}
