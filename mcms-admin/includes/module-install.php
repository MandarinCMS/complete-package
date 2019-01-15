<?php
/**
 * MandarinCMS Module Install Administration API
 *
 * @package MandarinCMS
 * @subpackage Administration
 */

/**
 * Retrieves module installer pages from the MandarinCMS.org Modules API.
 *
 * It is possible for a module to override the Module API result with three
 * filters. Assume this is for modules, which can extend on the Module Info to
 * offer more choices. This is very powerful and must be used with care when
 * overriding the filters.
 *
 * The first filter, {@see 'modules_api_args'}, is for the args and gives the action
 * as the second parameter. The hook for {@see 'modules_api_args'} must ensure that
 * an object is returned.
 *
 * The second filter, {@see 'modules_api'}, allows a module to override the MandarinCMS.org
 * Module Installation API entirely. If `$action` is 'query_modules' or 'module_information',
 * an object MUST be passed. If `$action` is 'hot_tags' or 'hot_categories', an array MUST
 * be passed.
 *
 * Finally, the third filter, {@see 'modules_api_result'}, makes it possible to filter the
 * response object or array, depending on the `$action` type.
 *
 * Supported arguments per action:
 *
 * | Argument Name        | query_modules | module_information | hot_tags | hot_categories |
 * | -------------------- | :-----------: | :----------------: | :------: | :------------: |
 * | `$slug`              | No            |  Yes               | No       | No             |
 * | `$per_page`          | Yes           |  No                | No       | No             |
 * | `$page`              | Yes           |  No                | No       | No             |
 * | `$number`            | No            |  No                | Yes      | Yes            |
 * | `$search`            | Yes           |  No                | No       | No             |
 * | `$tag`               | Yes           |  No                | No       | No             |
 * | `$author`            | Yes           |  No                | No       | No             |
 * | `$user`              | Yes           |  No                | No       | No             |
 * | `$browse`            | Yes           |  No                | No       | No             |
 * | `$locale`            | Yes           |  Yes               | No       | No             |
 * | `$installed_modules` | Yes           |  No                | No       | No             |
 * | `$is_ssl`            | Yes           |  Yes               | No       | No             |
 * | `$fields`            | Yes           |  Yes               | No       | No             |
 *
 * @since 2.7.0
 *
 * @param string       $action API action to perform: 'query_modules', 'module_information',
 *                             'hot_tags' or 'hot_categories'.
 * @param array|object $args   {
 *     Optional. Array or object of arguments to serialize for the Module Info API.
 *
 *     @type string  $slug              The module slug. Default empty.
 *     @type int     $per_page          Number of modules per page. Default 24.
 *     @type int     $page              Number of current page. Default 1.
 *     @type int     $number            Number of tags or categories to be queried.
 *     @type string  $search            A search term. Default empty.
 *     @type string  $tag               Tag to filter modules. Default empty.
 *     @type string  $author            Username of an module author to filter modules. Default empty.
 *     @type string  $user              Username to query for their favorites. Default empty.
 *     @type string  $browse            Browse view: 'popular', 'new', 'beta', 'recommended'.
 *     @type string  $locale            Locale to provide context-sensitive results. Default is the value
 *                                      of get_locale().
 *     @type string  $installed_modules Installed modules to provide context-sensitive results.
 *     @type bool    $is_ssl            Whether links should be returned with https or not. Default false.
 *     @type array   $fields            {
 *         Array of fields which should or should not be returned.
 *
 *         @type bool $short_description Whether to return the module short description. Default true.
 *         @type bool $description       Whether to return the module full description. Default false.
 *         @type bool $sections          Whether to return the module readme sections: description, installation,
 *                                       FAQ, screenshots, other notes, and changelog. Default false.
 *         @type bool $tested            Whether to return the 'Compatible up to' value. Default true.
 *         @type bool $requires          Whether to return the required MandarinCMS version. Default true.
 *         @type bool $rating            Whether to return the rating in percent and total number of ratings.
 *                                       Default true.
 *         @type bool $ratings           Whether to return the number of rating for each star (1-5). Default true.
 *         @type bool $downloaded        Whether to return the download count. Default true.
 *         @type bool $downloadlink      Whether to return the download link for the package. Default true.
 *         @type bool $last_updated      Whether to return the date of the last update. Default true.
 *         @type bool $added             Whether to return the date when the module was added to the mandarincms.com
 *                                       repository. Default true.
 *         @type bool $tags              Whether to return the assigned tags. Default true.
 *         @type bool $compatibility     Whether to return the MandarinCMS compatibility list. Default true.
 *         @type bool $homepage          Whether to return the module homepage link. Default true.
 *         @type bool $versions          Whether to return the list of all available versions. Default false.
 *         @type bool $donate_link       Whether to return the donation link. Default true.
 *         @type bool $reviews           Whether to return the module reviews. Default false.
 *         @type bool $banners           Whether to return the banner images links. Default false.
 *         @type bool $icons             Whether to return the icon links. Default false.
 *         @type bool $active_installs   Whether to return the number of active installations. Default false.
 *         @type bool $group             Whether to return the assigned group. Default false.
 *         @type bool $contributors      Whether to return the list of contributors. Default false.
 *     }
 * }
 * @return object|array|MCMS_Error Response object or array on success, MCMS_Error on failure. See the
 *         {@link https://developer.mandarincms.com/reference/functions/modules_api/ function reference article}
 *         for more information on the make-up of possible return values depending on the value of `$action`.
 */
function modules_api( $action, $args = array() ) {

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
	 * Filters the MandarinCMS.org Module Installation API arguments.
	 *
	 * Important: An object MUST be returned to this filter.
	 *
	 * @since 2.7.0
	 *
	 * @param object $args   Module API arguments.
	 * @param string $action The type of information being requested from the Module Installation API.
	 */
	$args = apply_filters( 'modules_api_args', $args, $action );

	/**
	 * Filters the response for the current MandarinCMS.org Module Installation API request.
	 *
	 * Passing a non-false value will effectively short-circuit the MandarinCMS.org API request.
	 *
	 * If `$action` is 'query_modules' or 'module_information', an object MUST be passed.
	 * If `$action` is 'hot_tags' or 'hot_categories', an array should be passed.
	 *
	 * @since 2.7.0
	 *
	 * @param false|object|array $result The result object or array. Default false.
	 * @param string             $action The type of information being requested from the Module Installation API.
	 * @param object             $args   Module API arguments.
	 */
	$res = apply_filters( 'modules_api', false, $action, $args );

	if ( false === $res ) {
		// include an unmodified $mcms_version
		include( BASED_TREE_URI . MCMSINC . '/version.php' );

		$url = $http_url = 'http://api.mandarincms.com/modules/info/1.0/';
		if ( $ssl = mcms_http_supports( array( 'ssl' ) ) )
			$url = set_url_scheme( $url, 'https' );

		$http_args = array(
			'timeout' => 15,
			'user-agent' => 'MandarinCMS/' . $mcms_version . '; ' . home_url( '/' ),
			'body' => array(
				'action' => $action,
				'request' => serialize( $args )
			)
		);
		$request = mcms_remote_post( $url, $http_args );

		if ( $ssl && is_mcms_error( $request ) ) {
			trigger_error(
				sprintf(
					/* translators: %s: support forums URL */
					__( 'An unexpected error occurred. Something may be wrong with MandarinCMS.org or this server&#8217;s configuration. If you continue to have problems, please try the <a href="%s">support forums</a>.' ),
					__( 'https://mandarincms.com/support/' )
				) . ' ' . __( '(MandarinCMS could not establish a secure connection to MandarinCMS.org. Please contact your server administrator.)' ),
				headers_sent() || MCMS_DEBUG ? E_USER_WARNING : E_USER_NOTICE
			);
			$request = mcms_remote_post( $http_url, $http_args );
		}

		if ( is_mcms_error($request) ) {
			$res = new MCMS_Error( 'modules_api_failed',
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
				$res = new MCMS_Error( 'modules_api_failed',
					sprintf(
						/* translators: %s: support forums URL */
						__( 'An unexpected error occurred. Something may be wrong with MandarinCMS.org or this server&#8217;s configuration. If you continue to have problems, please try the <a href="%s">support forums</a>.' ),
						__( 'https://mandarincms.com/support/' )
					),
					mcms_remote_retrieve_body( $request )
				);
			}
		}
	} elseif ( !is_mcms_error($res) ) {
		$res->external = true;
	}

	/**
	 * Filters the Module Installation API response results.
	 *
	 * @since 2.7.0
	 *
	 * @param object|MCMS_Error $res    Response object or MCMS_Error.
	 * @param string          $action The type of information being requested from the Module Installation API.
	 * @param object          $args   Module API arguments.
	 */
	return apply_filters( 'modules_api_result', $res, $action, $args );
}

/**
 * Retrieve popular MandarinCMS module tags.
 *
 * @since 2.7.0
 *
 * @param array $args
 * @return array
 */
function install_popular_tags( $args = array() ) {
	$key = md5(serialize($args));
	if ( false !== ($tags = get_site_transient('poptags_' . $key) ) )
		return $tags;

	$tags = modules_api('hot_tags', $args);

	if ( is_mcms_error($tags) )
		return $tags;

	set_site_transient( 'poptags_' . $key, $tags, 3 * HOUR_IN_SECONDS );

	return $tags;
}

/**
 * @since 2.7.0
 */
function install_dashboard() {
	?>
	<p><?php printf( __( 'Modules extend and expand the functionality of MandarinCMS. You may automatically install modules from the <a href="%1$s">MandarinCMS Module Directory</a> or upload a module in .zip format by clicking the button at the top of this page.' ), __( 'https://mandarincms.com/modules/' ) ); ?></p>

	<?php display_modules_table(); ?>

	<div class="modules-popular-tags-wrapper">
	<h2><?php _e( 'Popular tags' ) ?></h2>
	<p><?php _e( 'You may also browse based on the most popular tags in the Module Directory:' ) ?></p>
	<?php

	$api_tags = install_popular_tags();

	echo '<p class="popular-tags">';
	if ( is_mcms_error($api_tags) ) {
		echo $api_tags->get_error_message();
	} else {
		//Set up the tags in a way which can be interpreted by mcms_generate_tag_cloud()
		$tags = array();
		foreach ( (array) $api_tags as $tag ) {
			$url = self_admin_url( 'module-install.php?tab=search&type=tag&s=' . urlencode( $tag['name'] ) );
			$data = array(
				'link' => esc_url( $url ),
				'name' => $tag['name'],
				'slug' => $tag['slug'],
				'id' => sanitize_title_with_dashes( $tag['name'] ),
				'count' => $tag['count']
			);
			$tags[ $tag['name'] ] = (object) $data;
		}
		echo mcms_generate_tag_cloud($tags, array( 'single_text' => __('%s module'), 'multiple_text' => __('%s modules') ) );
	}
	echo '</p><br class="clear" /></div>';
}

/**
 * Displays a search form for searching modules.
 *
 * @since 2.7.0
 * @since 4.6.0 The `$type_selector` parameter was deprecated.
 *
 * @param bool $deprecated Not used.
 */
function install_search_form( $deprecated = true ) {
	$type = isset( $_REQUEST['type'] ) ? mcms_unslash( $_REQUEST['type'] ) : 'term';
	$term = isset( $_REQUEST['s'] ) ? mcms_unslash( $_REQUEST['s'] ) : '';
	?><form class="search-form search-modules" method="get">
		<input type="hidden" name="tab" value="search" />
		<label class="screen-reader-text" for="typeselector"><?php _e( 'Search modules by:' ); ?></label>
		<select name="type" id="typeselector">
			<option value="term"<?php selected( 'term', $type ); ?>><?php _e( 'Keyword' ); ?></option>
			<option value="author"<?php selected( 'author', $type ); ?>><?php _e( 'Author' ); ?></option>
			<option value="tag"<?php selected( 'tag', $type ); ?>><?php _ex( 'Tag', 'Module Installer' ); ?></option>
		</select>
		<label><span class="screen-reader-text"><?php _e( 'Search Modules' ); ?></span>
			<input type="search" name="s" value="<?php echo esc_attr( $term ) ?>" class="mcms-filter-search" placeholder="<?php esc_attr_e( 'Search modules...' ); ?>" />
		</label>
		<?php submit_button( __( 'Search Modules' ), 'hide-if-js', false, false, array( 'id' => 'search-submit' ) ); ?>
	</form><?php
}

/**
 * Upload from zip
 * @since 2.8.0
 */
function install_modules_upload() {
?>
<div class="upload-module">
	<p class="install-help"><?php _e('If you have a module in a .zip format, you may install it by uploading it here.'); ?></p>
	<form method="post" enctype="multipart/form-data" class="mcms-upload-form" action="<?php echo self_admin_url('update.php?action=upload-module'); ?>">
		<?php mcms_nonce_field( 'module-upload' ); ?>
		<label class="screen-reader-text" for="modulezip"><?php _e( 'Module zip file' ); ?></label>
		<input type="file" id="modulezip" name="modulezip" />
		<?php submit_button( __( 'Install Now' ), '', 'install-module-submit', false ); ?>
	</form>
</div>
<?php
}

/**
 * Show a username form for the favorites page
 * @since 3.5.0
 *
 */
function install_modules_favorites_form() {
	$user   = get_user_option( 'mcmsorg_favorites' );
	$action = 'save_mcmsorg_username_' . get_current_user_id();
	?>
	<p class="install-help"><?php _e( 'If you have marked modules as favorites on MandarinCMS.org, you can browse them here.' ); ?></p>
	<form method="get">
		<input type="hidden" name="tab" value="favorites" />
		<p>
			<label for="user"><?php _e( 'Your MandarinCMS.org username:' ); ?></label>
			<input type="search" id="user" name="user" value="<?php echo esc_attr( $user ); ?>" />
			<input type="submit" class="button" value="<?php esc_attr_e( 'Get Favorites' ); ?>" />
			<input type="hidden" id="mcmsorg-username-nonce" name="_mcmsnonce" value="<?php echo esc_attr( mcms_create_nonce( $action ) ); ?>" />
		</p>
	</form>
	<?php
}

/**
 * Display module content based on module list.
 *
 * @since 2.7.0
 *
 * @global MCMS_List_Table $mcms_list_table
 */
function display_modules_table() {
	global $mcms_list_table;

	switch ( current_filter() ) {
		case 'install_modules_favorites' :
			if ( empty( $_GET['user'] ) && ! get_user_option( 'mcmsorg_favorites' ) ) {
				return;
			}
			break;
		case 'install_modules_recommended' :
			echo '<p>' . __( 'These suggestions are based on the modules you and other users have installed.' ) . '</p>';
			break;
		case 'install_modules_beta' :
			printf(
				'<p>' . __( 'You are using a development version of MandarinCMS. These feature modules are also under development. <a href="%s">Learn more</a>.' ) . '</p>',
				'https://make.mandarincms.com/core/handbook/about/release-cycle/features-as-modules/'
			);
			break;
	}

	?>
	<form id="module-filter" method="post">
		<?php $mcms_list_table->display(); ?>
	</form>
	<?php
}

/**
 * Determine the status we can perform on a module.
 *
 * @since 3.0.0
 *
 * @param  array|object $api  Data about the module retrieved from the API.
 * @param  bool         $loop Optional. Disable further loops. Default false.
 * @return array {
 *     Module installation status data.
 *
 *     @type string $status  Status of a module. Could be one of 'install', 'update_available', 'latest_installed' or 'newer_installed'.
 *     @type string $url     Module installation URL.
 *     @type string $version The most recent version of the module.
 *     @type string $file    Module filename relative to the modules directory.
 * }
 */
function install_module_install_status($api, $loop = false) {
	// This function is called recursively, $loop prevents further loops.
	if ( is_array($api) )
		$api = (object) $api;

	// Default to a "new" module
	$status = 'install';
	$url = false;
	$update_file = false;

	/*
	 * Check to see if this module is known to be installed,
	 * and has an update awaiting it.
	 */
	$update_modules = get_site_transient('update_modules');
	if ( isset( $update_modules->response ) ) {
		foreach ( (array)$update_modules->response as $file => $module ) {
			if ( $module->slug === $api->slug ) {
				$status = 'update_available';
				$update_file = $file;
				$version = $module->new_version;
				if ( current_user_can('update_modules') )
					$url = mcms_nonce_url(self_admin_url('update.php?action=upgrade-module&module=' . $update_file), 'upgrade-module_' . $update_file);
				break;
			}
		}
	}

	if ( 'install' == $status ) {
		if ( is_dir( MCMS_PLUGIN_DIR . '/' . $api->slug ) ) {
			$installed_module = get_modules('/' . $api->slug);
			if ( empty($installed_module) ) {
				if ( current_user_can('install_modules') )
					$url = mcms_nonce_url(self_admin_url('update.php?action=install-module&module=' . $api->slug), 'install-module_' . $api->slug);
			} else {
				$key = array_keys( $installed_module );
				$key = reset( $key ); //Use the first module regardless of the name, Could have issues for multiple-modules in one directory if they share different version numbers
				$update_file = $api->slug . '/' . $key;
				if ( version_compare($api->version, $installed_module[ $key ]['Version'], '=') ){
					$status = 'latest_installed';
				} elseif ( version_compare($api->version, $installed_module[ $key ]['Version'], '<') ) {
					$status = 'newer_installed';
					$version = $installed_module[ $key ]['Version'];
				} else {
					//If the above update check failed, Then that probably means that the update checker has out-of-date information, force a refresh
					if ( ! $loop ) {
						delete_site_transient('update_modules');
						mcms_update_modules();
						return install_module_install_status($api, true);
					}
				}
			}
		} else {
			// "install" & no directory with that slug
			if ( current_user_can('install_modules') )
				$url = mcms_nonce_url(self_admin_url('update.php?action=install-module&module=' . $api->slug), 'install-module_' . $api->slug);
		}
	}
	if ( isset($_GET['from']) )
		$url .= '&amp;from=' . urlencode( mcms_unslash( $_GET['from'] ) );

	$file = $update_file;
	return compact( 'status', 'url', 'version', 'file' );
}

/**
 * Display module information in dialog box form.
 *
 * @since 2.7.0
 *
 * @global string $tab
 */
function install_module_information() {
	global $tab;

	if ( empty( $_REQUEST['module'] ) ) {
		return;
	}

	$api = modules_api( 'module_information', array(
		'slug' => mcms_unslash( $_REQUEST['module'] ),
		'is_ssl' => is_ssl(),
		'fields' => array(
			'banners' => true,
			'reviews' => true,
			'downloaded' => false,
			'active_installs' => true
		)
	) );

	if ( is_mcms_error( $api ) ) {
		mcms_die( $api );
	}

	$modules_allowedtags = array(
		'a' => array( 'href' => array(), 'title' => array(), 'target' => array() ),
		'abbr' => array( 'title' => array() ), 'acronym' => array( 'title' => array() ),
		'code' => array(), 'pre' => array(), 'em' => array(), 'strong' => array(),
		'div' => array( 'class' => array() ), 'span' => array( 'class' => array() ),
		'p' => array(), 'br' => array(), 'ul' => array(), 'ol' => array(), 'li' => array(),
		'h1' => array(), 'h2' => array(), 'h3' => array(), 'h4' => array(), 'h5' => array(), 'h6' => array(),
		'img' => array( 'src' => array(), 'class' => array(), 'alt' => array() ),
		'blockquote' => array( 'cite' => true ),
	);

	$modules_section_titles = array(
		'description'  => _x( 'Description',  'Module installer section title' ),
		'installation' => _x( 'Installation', 'Module installer section title' ),
		'faq'          => _x( 'FAQ',          'Module installer section title' ),
		'screenshots'  => _x( 'Screenshots',  'Module installer section title' ),
		'changelog'    => _x( 'Changelog',    'Module installer section title' ),
		'reviews'      => _x( 'Reviews',      'Module installer section title' ),
		'other_notes'  => _x( 'Other Notes',  'Module installer section title' )
	);

	// Sanitize HTML
	foreach ( (array) $api->sections as $section_name => $content ) {
		$api->sections[$section_name] = mcms_kses( $content, $modules_allowedtags );
	}

	foreach ( array( 'version', 'author', 'requires', 'tested', 'homepage', 'downloaded', 'slug' ) as $key ) {
		if ( isset( $api->$key ) ) {
			$api->$key = mcms_kses( $api->$key, $modules_allowedtags );
		}
	}

	$_tab = esc_attr( $tab );

	$section = isset( $_REQUEST['section'] ) ? mcms_unslash( $_REQUEST['section'] ) : 'description'; // Default to the Description tab, Do not translate, API returns English.
	if ( empty( $section ) || ! isset( $api->sections[ $section ] ) ) {
		$section_titles = array_keys( (array) $api->sections );
		$section = reset( $section_titles );
	}

	iframe_header( __( 'Module Installation' ) );

	$_with_banner = '';

	if ( ! empty( $api->banners ) && ( ! empty( $api->banners['low'] ) || ! empty( $api->banners['high'] ) ) ) {
		$_with_banner = 'with-banner';
		$low  = empty( $api->banners['low'] ) ? $api->banners['high'] : $api->banners['low'];
		$high = empty( $api->banners['high'] ) ? $api->banners['low'] : $api->banners['high'];
		?>
		<style type="text/css">
			#module-information-title.with-banner {
				background-image: url( <?php echo esc_url( $low ); ?> );
			}
			@media only screen and ( -webkit-min-device-pixel-ratio: 1.5 ) {
				#module-information-title.with-banner {
					background-image: url( <?php echo esc_url( $high ); ?> );
				}
			}
		</style>
		<?php
	}

	echo '<div id="module-information-scrollable">';
	echo "<div id='{$_tab}-title' class='{$_with_banner}'><div class='vignette'></div><h2>{$api->name}</h2></div>";
	echo "<div id='{$_tab}-tabs' class='{$_with_banner}'>\n";

	foreach ( (array) $api->sections as $section_name => $content ) {
		if ( 'reviews' === $section_name && ( empty( $api->ratings ) || 0 === array_sum( (array) $api->ratings ) ) ) {
			continue;
		}

		if ( isset( $modules_section_titles[ $section_name ] ) ) {
			$title = $modules_section_titles[ $section_name ];
		} else {
			$title = ucwords( str_replace( '_', ' ', $section_name ) );
		}

		$class = ( $section_name === $section ) ? ' class="current"' : '';
		$href = add_query_arg( array('tab' => $tab, 'section' => $section_name) );
		$href = esc_url( $href );
		$san_section = esc_attr( $section_name );
		echo "\t<a name='$san_section' href='$href' $class>$title</a>\n";
	}

	echo "</div>\n";

	?>
<div id="<?php echo $_tab; ?>-content" class='<?php echo $_with_banner; ?>'>
	<div class="fyi">
		<ul>
			<?php if ( ! empty( $api->version ) ) { ?>
				<li><strong><?php _e( 'Version:' ); ?></strong> <?php echo $api->version; ?></li>
			<?php } if ( ! empty( $api->author ) ) { ?>
				<li><strong><?php _e( 'Author:' ); ?></strong> <?php echo links_add_target( $api->author, '_blank' ); ?></li>
			<?php } if ( ! empty( $api->last_updated ) ) { ?>
				<li><strong><?php _e( 'Last Updated:' ); ?></strong>
					<?php
					/* translators: %s: Time since the last update */
					printf( __( '%s ago' ), human_time_diff( strtotime( $api->last_updated ) ) );
					?>
				</li>
			<?php } if ( ! empty( $api->requires ) ) { ?>
				<li>
					<strong><?php _e( 'Requires MandarinCMS Version:' ); ?></strong>
					<?php
					/* translators: %s: version number */
					printf( __( '%s or higher' ), $api->requires );
					?>
				</li>
			<?php } if ( ! empty( $api->tested ) ) { ?>
				<li><strong><?php _e( 'Compatible up to:' ); ?></strong> <?php echo $api->tested; ?></li>
			<?php } if ( ! empty( $api->requires_php ) ) { ?>
				<li>
					<strong><?php _e( 'Requires PHP Version:' ); ?></strong>
					<?php
					/* translators: %s: version number */
					printf( __( '%s or higher' ), $api->requires_php );
					?>
				</li>
			<?php } if ( isset( $api->active_installs ) ) { ?>
				<li><strong><?php _e( 'Active Installations:' ); ?></strong> <?php
					if ( $api->active_installs >= 1000000 ) {
						_ex( '1+ Million', 'Active module installations' );
					} elseif ( 0 == $api->active_installs ) {
						_ex( 'Less Than 10', 'Active module installations' );
					} else {
						echo number_format_i18n( $api->active_installs ) . '+';
					}
					?></li>
			<?php } if ( ! empty( $api->slug ) && empty( $api->external ) ) { ?>
				<li><a target="_blank" href="<?php echo __( 'https://mandarincms.com/modules/' ) . $api->slug; ?>/"><?php _e( 'MandarinCMS.org Module Page &#187;' ); ?></a></li>
			<?php } if ( ! empty( $api->homepage ) ) { ?>
				<li><a target="_blank" href="<?php echo esc_url( $api->homepage ); ?>"><?php _e( 'Module Homepage &#187;' ); ?></a></li>
			<?php } if ( ! empty( $api->donate_link ) && empty( $api->contributors ) ) { ?>
				<li><a target="_blank" href="<?php echo esc_url( $api->donate_link ); ?>"><?php _e( 'Donate to this module &#187;' ); ?></a></li>
			<?php } ?>
		</ul>
		<?php if ( ! empty( $api->rating ) ) { ?>
			<h3><?php _e( 'Average Rating' ); ?></h3>
			<?php mcms_star_rating( array( 'rating' => $api->rating, 'type' => 'percent', 'number' => $api->num_ratings ) ); ?>
			<p aria-hidden="true" class="fyi-description"><?php printf( _n( '(based on %s rating)', '(based on %s ratings)', $api->num_ratings ), number_format_i18n( $api->num_ratings ) ); ?></p>
		<?php }

		if ( ! empty( $api->ratings ) && array_sum( (array) $api->ratings ) > 0 ) { ?>
			<h3><?php _e( 'Reviews' ); ?></h3>
			<p class="fyi-description"><?php _e( 'Read all reviews on MandarinCMS.org or write your own!' ); ?></p>
			<?php
			foreach ( $api->ratings as $key => $ratecount ) {
				// Avoid div-by-zero.
				$_rating = $api->num_ratings ? ( $ratecount / $api->num_ratings ) : 0;
				/* translators: 1: number of stars (used to determine singular/plural), 2: number of reviews */
				$aria_label = esc_attr( sprintf( _n( 'Reviews with %1$d star: %2$s. Opens in a new window.', 'Reviews with %1$d stars: %2$s. Opens in a new window.', $key ),
					$key,
					number_format_i18n( $ratecount )
				) );
				?>
				<div class="counter-container">
						<span class="counter-label"><a href="https://mandarincms.com/support/module/<?php echo $api->slug; ?>/reviews/?filter=<?php echo $key; ?>"
						                               target="_blank" aria-label="<?php echo $aria_label; ?>"><?php printf( _n( '%d star', '%d stars', $key ), $key ); ?></a></span>
						<span class="counter-back">
							<span class="counter-bar" style="width: <?php echo 92 * $_rating; ?>px;"></span>
						</span>
					<span class="counter-count" aria-hidden="true"><?php echo number_format_i18n( $ratecount ); ?></span>
				</div>
				<?php
			}
		}
		if ( ! empty( $api->contributors ) ) { ?>
			<h3><?php _e( 'Feeders' ); ?></h3>
			<ul class="contributors">
				<?php
				foreach ( (array) $api->contributors as $contrib_username => $contrib_profile ) {
					if ( empty( $contrib_username ) && empty( $contrib_profile ) ) {
						continue;
					}
					if ( empty( $contrib_username ) ) {
						$contrib_username = preg_replace( '/^.+\/(.+)\/?$/', '\1', $contrib_profile );
					}
					$contrib_username = sanitize_user( $contrib_username );
					if ( empty( $contrib_profile ) ) {
						echo "<li><img src='https://mandarincms.com/grav-redirect.php?user={$contrib_username}&amp;s=36' width='18' height='18' alt='' />{$contrib_username}</li>";
					} else {
						echo "<li><a href='{$contrib_profile}' target='_blank'><img src='https://mandarincms.com/grav-redirect.php?user={$contrib_username}&amp;s=36' width='18' height='18' alt='' />{$contrib_username}</a></li>";
					}
				}
				?>
			</ul>
			<?php if ( ! empty( $api->donate_link ) ) { ?>
				<a target="_blank" href="<?php echo esc_url( $api->donate_link ); ?>"><?php _e( 'Donate to this module &#187;' ); ?></a>
			<?php } ?>
		<?php } ?>
	</div>
	<div id="section-holder" class="wrap">
	<?php
	$mcms_version = get_bloginfo( 'version' );

	if ( ! empty( $api->tested ) && version_compare( substr( $mcms_version, 0, strlen( $api->tested ) ), $api->tested, '>' ) ) {
		echo '<div class="notice notice-warning notice-alt"><p>' . __( '<strong>Warning:</strong> This module has <strong>not been tested</strong> with your current version of MandarinCMS.' ) . '</p></div>';
	} elseif ( ! empty( $api->requires ) && version_compare( substr( $mcms_version, 0, strlen( $api->requires ) ), $api->requires, '<' ) ) {
		echo '<div class="notice notice-warning notice-alt"><p>' . __( '<strong>Warning:</strong> This module has <strong>not been marked as compatible</strong> with your version of MandarinCMS.' ) . '</p></div>';
	}

	foreach ( (array) $api->sections as $section_name => $content ) {
		$content = links_add_base_url( $content, 'https://mandarincms.com/modules/' . $api->slug . '/' );
		$content = links_add_target( $content, '_blank' );

		$san_section = esc_attr( $section_name );

		$display = ( $section_name === $section ) ? 'block' : 'none';

		echo "\t<div id='section-{$san_section}' class='section' style='display: {$display};'>\n";
		echo $content;
		echo "\t</div>\n";
	}
	echo "</div>\n";
	echo "</div>\n";
	echo "</div>\n"; // #module-information-scrollable
	echo "<div id='$tab-footer'>\n";
	if ( ! empty( $api->download_link ) && ( current_user_can( 'install_modules' ) || current_user_can( 'update_modules' ) ) ) {
		$status = install_module_install_status( $api );
		switch ( $status['status'] ) {
			case 'install':
				if ( $status['url'] ) {
					echo '<a data-slug="' . esc_attr( $api->slug ) . '" id="module_install_from_iframe" class="button button-primary right" href="' . $status['url'] . '" target="_parent">' . __( 'Install Now' ) . '</a>';
				}
				break;
			case 'update_available':
				if ( $status['url'] ) {
					echo '<a data-slug="' . esc_attr( $api->slug ) . '" data-module="' . esc_attr( $status['file'] ) . '" id="module_update_from_iframe" class="button button-primary right" href="' . $status['url'] . '" target="_parent">' . __( 'Install Update Now' ) .'</a>';
				}
				break;
			case 'newer_installed':
				/* translators: %s: Module version */
				echo '<a class="button button-primary right disabled">' . sprintf( __( 'Newer Version (%s) Installed'), $status['version'] ) . '</a>';
				break;
			case 'latest_installed':
				echo '<a class="button button-primary right disabled">' . __( 'Latest Version Installed' ) . '</a>';
				break;
		}
	}
	echo "</div>\n";

	iframe_footer();
	exit;
}
