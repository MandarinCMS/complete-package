<?php
/**
 * MySkin, template, and stylesheet functions.
 *
 * @package MandarinCMS
 * @subpackage MySkin
 */

/**
 * Returns an array of MCMS_MySkin objects based on the arguments.
 *
 * Despite advances over get_myskins(), this function is quite expensive, and grows
 * linearly with additional myskins. Stick to mcms_get_myskin() if possible.
 *
 * @since 3.4.0
 *
 * @global array $mcms_myskin_directories
 * @staticvar array $_myskins
 *
 * @param array $args The search arguments. Optional.
 * - errors      mixed  True to return myskins with errors, false to return myskins without errors, null
 *                      to return all myskins. Defaults to false.
 * - allowed     mixed  (Multisite) True to return only allowed myskins for a site. False to return only
 *                      disallowed myskins for a site. 'site' to return only site-allowed myskins. 'network'
 *                      to return only network-allowed myskins. Null to return all myskins. Defaults to null.
 * - blog_id     int    (Multisite) The blog ID used to calculate which myskins are allowed. Defaults to 0,
 *                      synonymous for the current blog.
 * @return array Array of MCMS_MySkin objects.
 */
function mcms_get_myskins( $args = array() ) {
	global $mcms_myskin_directories;

	$defaults = array( 'errors' => false, 'allowed' => null, 'blog_id' => 0 );
	$args = mcms_parse_args( $args, $defaults );

	$myskin_directories = search_myskin_directories();

	if ( is_array( $mcms_myskin_directories ) && count( $mcms_myskin_directories ) > 1 ) {
		// Make sure the current myskin wins out, in case search_myskin_directories() picks the wrong
		// one in the case of a conflict. (Normally, last registered myskin root wins.)
		$current_myskin = get_stylesheet();
		if ( isset( $myskin_directories[ $current_myskin ] ) ) {
			$root_of_current_myskin = get_raw_myskin_root( $current_myskin );
			if ( ! in_array( $root_of_current_myskin, $mcms_myskin_directories ) )
				$root_of_current_myskin = MCMS_CONTENT_DIR . $root_of_current_myskin;
			$myskin_directories[ $current_myskin ]['myskin_root'] = $root_of_current_myskin;
		}
	}

	if ( empty( $myskin_directories ) )
		return array();

	if ( is_multisite() && null !== $args['allowed'] ) {
		$allowed = $args['allowed'];
		if ( 'network' === $allowed )
			$myskin_directories = array_intersect_key( $myskin_directories, MCMS_MySkin::get_allowed_on_network() );
		elseif ( 'site' === $allowed )
			$myskin_directories = array_intersect_key( $myskin_directories, MCMS_MySkin::get_allowed_on_site( $args['blog_id'] ) );
		elseif ( $allowed )
			$myskin_directories = array_intersect_key( $myskin_directories, MCMS_MySkin::get_allowed( $args['blog_id'] ) );
		else
			$myskin_directories = array_diff_key( $myskin_directories, MCMS_MySkin::get_allowed( $args['blog_id'] ) );
	}

	$myskins = array();
	static $_myskins = array();

	foreach ( $myskin_directories as $myskin => $myskin_root ) {
		if ( isset( $_myskins[ $myskin_root['myskin_root'] . '/' . $myskin ] ) )
			$myskins[ $myskin ] = $_myskins[ $myskin_root['myskin_root'] . '/' . $myskin ];
		else
			$myskins[ $myskin ] = $_myskins[ $myskin_root['myskin_root'] . '/' . $myskin ] = new MCMS_MySkin( $myskin, $myskin_root['myskin_root'] );
	}

	if ( null !== $args['errors'] ) {
		foreach ( $myskins as $myskin => $mcms_myskin ) {
			if ( $mcms_myskin->errors() != $args['errors'] )
				unset( $myskins[ $myskin ] );
		}
	}

	return $myskins;
}

/**
 * Gets a MCMS_MySkin object for a myskin.
 *
 * @since 3.4.0
 *
 * @global array $mcms_myskin_directories
 *
 * @param string $stylesheet Directory name for the myskin. Optional. Defaults to current myskin.
 * @param string $myskin_root Absolute path of the myskin root to look in. Optional. If not specified, get_raw_myskin_root()
 * 	                         is used to calculate the myskin root for the $stylesheet provided (or current myskin).
 * @return MCMS_MySkin MySkin object. Be sure to check the object's exists() method if you need to confirm the myskin's existence.
 */
function mcms_get_myskin( $stylesheet = null, $myskin_root = null ) {
	global $mcms_myskin_directories;

	if ( empty( $stylesheet ) )
		$stylesheet = get_stylesheet();

	if ( empty( $myskin_root ) ) {
		$myskin_root = get_raw_myskin_root( $stylesheet );
		if ( false === $myskin_root )
			$myskin_root = MCMS_CONTENT_DIR . '/myskins';
		elseif ( ! in_array( $myskin_root, (array) $mcms_myskin_directories ) )
			$myskin_root = MCMS_CONTENT_DIR . $myskin_root;
	}

	return new MCMS_MySkin( $stylesheet, $myskin_root );
}

/**
 * Clears the cache held by get_myskin_roots() and MCMS_MySkin.
 *
 * @since 3.5.0
 * @param bool $clear_update_cache Whether to clear the MySkin updates cache
 */
function mcms_clean_myskins_cache( $clear_update_cache = true ) {
	if ( $clear_update_cache )
		delete_site_transient( 'update_myskins' );
	search_myskin_directories( true );
	foreach ( mcms_get_myskins( array( 'errors' => null ) ) as $myskin )
		$myskin->cache_delete();
}

/**
 * Whether a child myskin is in use.
 *
 * @since 3.0.0
 *
 * @return bool true if a child myskin is in use, false otherwise.
 **/
function is_child_myskin() {
	return ( TEMPLATEPATH !== STYLESHEETPATH );
}

/**
 * Retrieve name of the current stylesheet.
 *
 * The myskin name that the administrator has currently set the front end myskin
 * as.
 *
 * For all intents and purposes, the template name and the stylesheet name are
 * going to be the same for most cases.
 *
 * @since 1.5.0
 *
 * @return string Stylesheet name.
 */
function get_stylesheet() {
	/**
	 * Filters the name of current stylesheet.
	 *
	 * @since 1.5.0
	 *
	 * @param string $stylesheet Name of the current stylesheet.
	 */
	return apply_filters( 'stylesheet', get_option( 'stylesheet' ) );
}

/**
 * Retrieve stylesheet directory path for current myskin.
 *
 * @since 1.5.0
 *
 * @return string Path to current myskin directory.
 */
function get_stylesheet_directory() {
	$stylesheet = get_stylesheet();
	$myskin_root = get_myskin_root( $stylesheet );
	$stylesheet_dir = "$myskin_root/$stylesheet";

	/**
	 * Filters the stylesheet directory path for current myskin.
	 *
	 * @since 1.5.0
	 *
	 * @param string $stylesheet_dir Absolute path to the current myskin.
	 * @param string $stylesheet     Directory name of the current myskin.
	 * @param string $myskin_root     Absolute path to myskins directory.
	 */
	return apply_filters( 'stylesheet_directory', $stylesheet_dir, $stylesheet, $myskin_root );
}

/**
 * Retrieve stylesheet directory URI.
 *
 * @since 1.5.0
 *
 * @return string
 */
function get_stylesheet_directory_uri() {
	$stylesheet = str_replace( '%2F', '/', rawurlencode( get_stylesheet() ) );
	$myskin_root_uri = get_myskin_root_uri( $stylesheet );
	$stylesheet_dir_uri = "$myskin_root_uri/$stylesheet";

	/**
	 * Filters the stylesheet directory URI.
	 *
	 * @since 1.5.0
	 *
	 * @param string $stylesheet_dir_uri Stylesheet directory URI.
	 * @param string $stylesheet         Name of the activated myskin's directory.
	 * @param string $myskin_root_uri     MySkins root URI.
	 */
	return apply_filters( 'stylesheet_directory_uri', $stylesheet_dir_uri, $stylesheet, $myskin_root_uri );
}

/**
 * Retrieves the URI of current myskin stylesheet.
 *
 * The stylesheet file name is 'style.css' which is appended to the stylesheet directory URI path.
 * See get_stylesheet_directory_uri().
 *
 * @since 1.5.0
 *
 * @return string
 */
function get_stylesheet_uri() {
	$stylesheet_dir_uri = get_stylesheet_directory_uri();
	$stylesheet_uri = $stylesheet_dir_uri . '/style.css';
	/**
	 * Filters the URI of the current myskin stylesheet.
	 *
	 * @since 1.5.0
	 *
	 * @param string $stylesheet_uri     Stylesheet URI for the current myskin/child myskin.
	 * @param string $stylesheet_dir_uri Stylesheet directory URI for the current myskin/child myskin.
	 */
	return apply_filters( 'stylesheet_uri', $stylesheet_uri, $stylesheet_dir_uri );
}

/**
 * Retrieves the localized stylesheet URI.
 *
 * The stylesheet directory for the localized stylesheet files are located, by
 * default, in the base myskin directory. The name of the locale file will be the
 * locale followed by '.css'. If that does not exist, then the text direction
 * stylesheet will be checked for existence, for example 'ltr.css'.
 *
 * The myskin may change the location of the stylesheet directory by either using
 * the {@see 'stylesheet_directory_uri'} or {@see 'locale_stylesheet_uri'} filters.
 *
 * If you want to change the location of the stylesheet files for the entire
 * MandarinCMS workflow, then change the former. If you just have the locale in a
 * separate folder, then change the latter.
 *
 * @since 2.1.0
 *
 * @global MCMS_Locale $mcms_locale
 *
 * @return string
 */
function get_locale_stylesheet_uri() {
	global $mcms_locale;
	$stylesheet_dir_uri = get_stylesheet_directory_uri();
	$dir = get_stylesheet_directory();
	$locale = get_locale();
	if ( file_exists("$dir/$locale.css") )
		$stylesheet_uri = "$stylesheet_dir_uri/$locale.css";
	elseif ( !empty($mcms_locale->text_direction) && file_exists("$dir/{$mcms_locale->text_direction}.css") )
		$stylesheet_uri = "$stylesheet_dir_uri/{$mcms_locale->text_direction}.css";
	else
		$stylesheet_uri = '';
	/**
	 * Filters the localized stylesheet URI.
	 *
	 * @since 2.1.0
	 *
	 * @param string $stylesheet_uri     Localized stylesheet URI.
	 * @param string $stylesheet_dir_uri Stylesheet directory URI.
	 */
	return apply_filters( 'locale_stylesheet_uri', $stylesheet_uri, $stylesheet_dir_uri );
}

/**
 * Retrieve name of the current myskin.
 *
 * @since 1.5.0
 *
 * @return string Template name.
 */
function get_template() {
	/**
	 * Filters the name of the current myskin.
	 *
	 * @since 1.5.0
	 *
	 * @param string $template Current myskin's directory name.
	 */
	return apply_filters( 'template', get_option( 'template' ) );
}

/**
 * Retrieve current myskin directory.
 *
 * @since 1.5.0
 *
 * @return string Template directory path.
 */
function get_template_directory() {
	$template = get_template();
	$myskin_root = get_myskin_root( $template );
	$template_dir = "$myskin_root/$template";

	/**
	 * Filters the current myskin directory path.
	 *
	 * @since 1.5.0
	 *
	 * @param string $template_dir The URI of the current myskin directory.
	 * @param string $template     Directory name of the current myskin.
	 * @param string $myskin_root   Absolute path to the myskins directory.
	 */
	return apply_filters( 'template_directory', $template_dir, $template, $myskin_root );
}

/**
 * Retrieve myskin directory URI.
 *
 * @since 1.5.0
 *
 * @return string Template directory URI.
 */
function get_template_directory_uri() {
	$template = str_replace( '%2F', '/', rawurlencode( get_template() ) );
	$myskin_root_uri = get_myskin_root_uri( $template );
	$template_dir_uri = "$myskin_root_uri/$template";

	/**
	 * Filters the current myskin directory URI.
	 *
	 * @since 1.5.0
	 *
	 * @param string $template_dir_uri The URI of the current myskin directory.
	 * @param string $template         Directory name of the current myskin.
	 * @param string $myskin_root_uri   The myskins root URI.
	 */
	return apply_filters( 'template_directory_uri', $template_dir_uri, $template, $myskin_root_uri );
}

/**
 * Retrieve myskin roots.
 *
 * @since 2.9.0
 *
 * @global array $mcms_myskin_directories
 *
 * @return array|string An array of myskin roots keyed by template/stylesheet or a single myskin root if all myskins have the same root.
 */
function get_myskin_roots() {
	global $mcms_myskin_directories;

	if ( ! is_array( $mcms_myskin_directories ) || count( $mcms_myskin_directories ) <= 1 ) {
		return '/myskins';
	}

	$myskin_roots = get_site_transient( 'myskin_roots' );
	if ( false === $myskin_roots ) {
		search_myskin_directories( true ); // Regenerate the transient.
		$myskin_roots = get_site_transient( 'myskin_roots' );
	}
	return $myskin_roots;
}

/**
 * Register a directory that contains myskins.
 *
 * @since 2.9.0
 *
 * @global array $mcms_myskin_directories
 *
 * @param string $directory Either the full filesystem path to a myskin folder or a folder within MCMS_CONTENT_DIR
 * @return bool
 */
function register_myskin_directory( $directory ) {
	global $mcms_myskin_directories;

	if ( ! file_exists( $directory ) ) {
		// Try prepending as the myskin directory could be relative to the content directory
		$directory = MCMS_CONTENT_DIR . '/' . $directory;
		// If this directory does not exist, return and do not register
		if ( ! file_exists( $directory ) ) {
			return false;
		}
	}

	if ( ! is_array( $mcms_myskin_directories ) ) {
		$mcms_myskin_directories = array();
	}

	$untrailed = untrailingslashit( $directory );
	if ( ! empty( $untrailed ) && ! in_array( $untrailed, $mcms_myskin_directories ) ) {
		$mcms_myskin_directories[] = $untrailed;
	}

	return true;
}

/**
 * Search all registered myskin directories for complete and valid myskins.
 *
 * @since 2.9.0
 *
 * @global array $mcms_myskin_directories
 * @staticvar array $found_myskins
 *
 * @param bool $force Optional. Whether to force a new directory scan. Defaults to false.
 * @return array|false Valid myskins found
 */
function search_myskin_directories( $force = false ) {
	global $mcms_myskin_directories;
	static $found_myskins = null;

	if ( empty( $mcms_myskin_directories ) )
		return false;

	if ( ! $force && isset( $found_myskins ) )
		return $found_myskins;

	$found_myskins = array();

	$mcms_myskin_directories = (array) $mcms_myskin_directories;
	$relative_myskin_roots = array();

	// Set up maybe-relative, maybe-absolute array of myskin directories.
	// We always want to return absolute, but we need to cache relative
	// to use in get_myskin_root().
	foreach ( $mcms_myskin_directories as $myskin_root ) {
		if ( 0 === strpos( $myskin_root, MCMS_CONTENT_DIR ) )
			$relative_myskin_roots[ str_replace( MCMS_CONTENT_DIR, '', $myskin_root ) ] = $myskin_root;
		else
			$relative_myskin_roots[ $myskin_root ] = $myskin_root;
	}

	/**
	 * Filters whether to get the cache of the registered myskin directories.
	 *
	 * @since 3.4.0
	 *
	 * @param bool   $cache_expiration Whether to get the cache of the myskin directories. Default false.
	 * @param string $cache_directory  Directory to be searched for the cache.
	 */
	if ( $cache_expiration = apply_filters( 'mcms_cache_myskins_persistently', false, 'search_myskin_directories' ) ) {
		$cached_roots = get_site_transient( 'myskin_roots' );
		if ( is_array( $cached_roots ) ) {
			foreach ( $cached_roots as $myskin_dir => $myskin_root ) {
				// A cached myskin root is no longer around, so skip it.
				if ( ! isset( $relative_myskin_roots[ $myskin_root ] ) )
					continue;
				$found_myskins[ $myskin_dir ] = array(
					'myskin_file' => $myskin_dir . '/style.css',
					'myskin_root' => $relative_myskin_roots[ $myskin_root ], // Convert relative to absolute.
				);
			}
			return $found_myskins;
		}
		if ( ! is_int( $cache_expiration ) )
			$cache_expiration = 1800; // half hour
	} else {
		$cache_expiration = 1800; // half hour
	}

	/* Loop the registered myskin directories and extract all myskins */
	foreach ( $mcms_myskin_directories as $myskin_root ) {

		// Start with directories in the root of the current myskin directory.
		$dirs = @ scandir( $myskin_root );
		if ( ! $dirs ) {
			trigger_error( "$myskin_root is not readable", E_USER_NOTICE );
			continue;
		}
		foreach ( $dirs as $dir ) {
			if ( ! is_dir( $myskin_root . '/' . $dir ) || $dir[0] == '.' || $dir == 'CVS' )
				continue;
			if ( file_exists( $myskin_root . '/' . $dir . '/style.css' ) ) {
				// mcms-plugins/myskins/a-single-myskin
				// mcms-plugins/myskins is $myskin_root, a-single-myskin is $dir
				$found_myskins[ $dir ] = array(
					'myskin_file' => $dir . '/style.css',
					'myskin_root' => $myskin_root,
				);
			} else {
				$found_myskin = false;
				// mcms-plugins/myskins/a-folder-of-myskins/*
				// mcms-plugins/myskins is $myskin_root, a-folder-of-myskins is $dir, then myskins are $sub_dirs
				$sub_dirs = @ scandir( $myskin_root . '/' . $dir );
				if ( ! $sub_dirs ) {
					trigger_error( "$myskin_root/$dir is not readable", E_USER_NOTICE );
					continue;
				}
				foreach ( $sub_dirs as $sub_dir ) {
					if ( ! is_dir( $myskin_root . '/' . $dir . '/' . $sub_dir ) || $dir[0] == '.' || $dir == 'CVS' )
						continue;
					if ( ! file_exists( $myskin_root . '/' . $dir . '/' . $sub_dir . '/style.css' ) )
						continue;
					$found_myskins[ $dir . '/' . $sub_dir ] = array(
						'myskin_file' => $dir . '/' . $sub_dir . '/style.css',
						'myskin_root' => $myskin_root,
					);
					$found_myskin = true;
				}
				// Never mind the above, it's just a myskin missing a style.css.
				// Return it; MCMS_MySkin will catch the error.
				if ( ! $found_myskin )
					$found_myskins[ $dir ] = array(
						'myskin_file' => $dir . '/style.css',
						'myskin_root' => $myskin_root,
					);
			}
		}
	}

	asort( $found_myskins );

	$myskin_roots = array();
	$relative_myskin_roots = array_flip( $relative_myskin_roots );

	foreach ( $found_myskins as $myskin_dir => $myskin_data ) {
		$myskin_roots[ $myskin_dir ] = $relative_myskin_roots[ $myskin_data['myskin_root'] ]; // Convert absolute to relative.
	}

	if ( $myskin_roots != get_site_transient( 'myskin_roots' ) )
		set_site_transient( 'myskin_roots', $myskin_roots, $cache_expiration );

	return $found_myskins;
}

/**
 * Retrieve path to myskins directory.
 *
 * Does not have trailing slash.
 *
 * @since 1.5.0
 *
 * @global array $mcms_myskin_directories
 *
 * @param string $stylesheet_or_template The stylesheet or template name of the myskin
 * @return string MySkin path.
 */
function get_myskin_root( $stylesheet_or_template = false ) {
	global $mcms_myskin_directories;

	if ( $stylesheet_or_template && $myskin_root = get_raw_myskin_root( $stylesheet_or_template ) ) {
		// Always prepend MCMS_CONTENT_DIR unless the root currently registered as a myskin directory.
		// This gives relative myskin roots the benefit of the doubt when things go haywire.
		if ( ! in_array( $myskin_root, (array) $mcms_myskin_directories ) )
			$myskin_root = MCMS_CONTENT_DIR . $myskin_root;
	} else {
		$myskin_root = MCMS_CONTENT_DIR . '/myskins';
	}

	/**
	 * Filters the absolute path to the myskins directory.
	 *
	 * @since 1.5.0
	 *
	 * @param string $myskin_root Absolute path to myskins directory.
	 */
	return apply_filters( 'myskin_root', $myskin_root );
}

/**
 * Retrieve URI for myskins directory.
 *
 * Does not have trailing slash.
 *
 * @since 1.5.0
 *
 * @global array $mcms_myskin_directories
 *
 * @param string $stylesheet_or_template Optional. The stylesheet or template name of the myskin.
 * 	                                     Default is to leverage the main myskin root.
 * @param string $myskin_root             Optional. The myskin root for which calculations will be based, preventing
 * 	                                     the need for a get_raw_myskin_root() call.
 * @return string MySkins URI.
 */
function get_myskin_root_uri( $stylesheet_or_template = false, $myskin_root = false ) {
	global $mcms_myskin_directories;

	if ( $stylesheet_or_template && ! $myskin_root )
		$myskin_root = get_raw_myskin_root( $stylesheet_or_template );

	if ( $stylesheet_or_template && $myskin_root ) {
		if ( in_array( $myskin_root, (array) $mcms_myskin_directories ) ) {
			// Absolute path. Make an educated guess. YMMV -- but note the filter below.
			if ( 0 === strpos( $myskin_root, MCMS_CONTENT_DIR ) )
				$myskin_root_uri = content_url( str_replace( MCMS_CONTENT_DIR, '', $myskin_root ) );
			elseif ( 0 === strpos( $myskin_root, BASED_TREE_URI ) )
				$myskin_root_uri = site_url( str_replace( BASED_TREE_URI, '', $myskin_root ) );
			elseif ( 0 === strpos( $myskin_root, MCMS_PLUGIN_DIR ) || 0 === strpos( $myskin_root, MCMSMU_PLUGIN_DIR ) )
				$myskin_root_uri = modules_url( basename( $myskin_root ), $myskin_root );
			else
				$myskin_root_uri = $myskin_root;
		} else {
			$myskin_root_uri = content_url( $myskin_root );
		}
	} else {
		$myskin_root_uri = content_url( 'myskins' );
	}

	/**
	 * Filters the URI for myskins directory.
	 *
	 * @since 1.5.0
	 *
	 * @param string $myskin_root_uri         The URI for myskins directory.
	 * @param string $siteurl                MandarinCMS web address which is set in General Options.
	 * @param string $stylesheet_or_template Stylesheet or template name of the myskin.
	 */
	return apply_filters( 'myskin_root_uri', $myskin_root_uri, get_option( 'siteurl' ), $stylesheet_or_template );
}

/**
 * Get the raw myskin root relative to the content directory with no filters applied.
 *
 * @since 3.1.0
 *
 * @global array $mcms_myskin_directories
 *
 * @param string $stylesheet_or_template The stylesheet or template name of the myskin
 * @param bool   $skip_cache             Optional. Whether to skip the cache.
 *                                       Defaults to false, meaning the cache is used.
 * @return string MySkin root
 */
function get_raw_myskin_root( $stylesheet_or_template, $skip_cache = false ) {
	global $mcms_myskin_directories;

	if ( ! is_array( $mcms_myskin_directories ) || count( $mcms_myskin_directories ) <= 1 ) {
		return '/myskins';
	}

	$myskin_root = false;

	// If requesting the root for the current myskin, consult options to avoid calling get_myskin_roots()
	if ( ! $skip_cache ) {
		if ( get_option('stylesheet') == $stylesheet_or_template )
			$myskin_root = get_option('stylesheet_root');
		elseif ( get_option('template') == $stylesheet_or_template )
			$myskin_root = get_option('template_root');
	}

	if ( empty($myskin_root) ) {
		$myskin_roots = get_myskin_roots();
		if ( !empty($myskin_roots[$stylesheet_or_template]) )
			$myskin_root = $myskin_roots[$stylesheet_or_template];
	}

	return $myskin_root;
}

/**
 * Display localized stylesheet link element.
 *
 * @since 2.1.0
 */
function locale_stylesheet() {
	$stylesheet = get_locale_stylesheet_uri();
	if ( empty($stylesheet) )
		return;
	echo '<link rel="stylesheet" href="' . $stylesheet . '" type="text/css" media="screen" />';
}

/**
 * Switches the myskin.
 *
 * Accepts one argument: $stylesheet of the myskin. It also accepts an additional function signature
 * of two arguments: $template then $stylesheet. This is for backward compatibility.
 *
 * @since 2.5.0
 *
 * @global array                $mcms_myskin_directories
 * @global MCMS_Customize_Manager $mcms_customize
 * @global array                $sidebars_widgets
 *
 * @param string $stylesheet Stylesheet name
 */
function switch_myskin( $stylesheet ) {
	global $mcms_myskin_directories, $mcms_customize, $sidebars_widgets;

	$_sidebars_widgets = null;
	if ( 'mcms_ajax_customize_save' === current_action() ) {
		$old_sidebars_widgets_data_setting = $mcms_customize->get_setting( 'old_sidebars_widgets_data' );
		if ( $old_sidebars_widgets_data_setting ) {
			$_sidebars_widgets = $mcms_customize->post_value( $old_sidebars_widgets_data_setting );
		}
	} elseif ( is_array( $sidebars_widgets ) ) {
		$_sidebars_widgets = $sidebars_widgets;
	}

	if ( is_array( $_sidebars_widgets ) ) {
		set_myskin_mod( 'sidebars_widgets', array( 'time' => time(), 'data' => $_sidebars_widgets ) );
	}

	$nav_menu_locations = get_myskin_mod( 'nav_menu_locations' );
	update_option( 'myskin_switch_menu_locations', $nav_menu_locations );

	if ( func_num_args() > 1 ) {
		$stylesheet = func_get_arg( 1 );
	}

	$old_myskin = mcms_get_myskin();
	$new_myskin = mcms_get_myskin( $stylesheet );
	$template  = $new_myskin->get_template();

	update_option( 'template', $template );
	update_option( 'stylesheet', $stylesheet );

	if ( count( $mcms_myskin_directories ) > 1 ) {
		update_option( 'template_root', get_raw_myskin_root( $template, true ) );
		update_option( 'stylesheet_root', get_raw_myskin_root( $stylesheet, true ) );
	} else {
		delete_option( 'template_root' );
		delete_option( 'stylesheet_root' );
	}

	$new_name  = $new_myskin->get('Name');

	update_option( 'current_myskin', $new_name );

	// Migrate from the old mods_{name} option to myskin_mods_{slug}.
	if ( is_admin() && false === get_option( 'myskin_mods_' . $stylesheet ) ) {
		$default_myskin_mods = (array) get_option( 'mods_' . $new_name );
		if ( ! empty( $nav_menu_locations ) && empty( $default_myskin_mods['nav_menu_locations'] ) ) {
			$default_myskin_mods['nav_menu_locations'] = $nav_menu_locations;
		}
		add_option( "myskin_mods_$stylesheet", $default_myskin_mods );
	} else {
		/*
		 * Since retrieve_widgets() is called when initializing a myskin in the Customizer,
		 * we need to remove the myskin mods to avoid overwriting changes made via
		 * the Customizer when accessing mcms-admin/widgets.php.
		 */
		if ( 'mcms_ajax_customize_save' === current_action() ) {
			remove_myskin_mod( 'sidebars_widgets' );
		}
	}

	update_option( 'myskin_switched', $old_myskin->get_stylesheet() );

	/**
	 * Fires after the myskin is switched.
	 *
	 * @since 1.5.0
	 * @since 4.5.0 Introduced the `$old_myskin` parameter.
	 *
	 * @param string   $new_name  Name of the new myskin.
	 * @param MCMS_MySkin $new_myskin MCMS_MySkin instance of the new myskin.
	 * @param MCMS_MySkin $old_myskin MCMS_MySkin instance of the old myskin.
	 */
	do_action( 'switch_myskin', $new_name, $new_myskin, $old_myskin );
}

/**
 * Checks that current myskin files 'index.php' and 'style.css' exists.
 *
 * Does not initially check the default myskin, which is the fallback and should always exist.
 * But if it doesn't exist, it'll fall back to the latest core default myskin that does exist.
 * Will switch myskin to the fallback myskin if current myskin does not validate.
 *
 * You can use the {@see 'validate_current_myskin'} filter to return false to
 * disable this functionality.
 *
 * @since 1.5.0
 * @see MCMS_DEFAULT_THEME
 *
 * @return bool
 */
function validate_current_myskin() {
	/**
	 * Filters whether to validate the current myskin.
	 *
	 * @since 2.7.0
	 *
	 * @param bool $validate Whether to validate the current myskin. Default true.
	 */
	if ( mcms_installing() || ! apply_filters( 'validate_current_myskin', true ) )
		return true;

	if ( ! file_exists( get_template_directory() . '/index.php' ) ) {
		// Invalid.
	} elseif ( ! file_exists( get_template_directory() . '/style.css' ) ) {
		// Invalid.
	} elseif ( is_child_myskin() && ! file_exists( get_stylesheet_directory() . '/style.css' ) ) {
		// Invalid.
	} else {
		// Valid.
		return true;
	}

	$default = mcms_get_myskin( MCMS_DEFAULT_THEME );
	if ( $default->exists() ) {
		switch_myskin( MCMS_DEFAULT_THEME );
		return false;
	}

	/**
	 * If we're in an invalid state but MCMS_DEFAULT_THEME doesn't exist,
	 * switch to the latest core default myskin that's installed.
	 * If it turns out that this latest core default myskin is our current
	 * myskin, then there's nothing we can do about that, so we have to bail,
	 * rather than going into an infinite loop. (This is why there are
	 * checks against MCMS_DEFAULT_THEME above, also.) We also can't do anything
	 * if it turns out there is no default myskin installed. (That's `false`.)
	 */
	$default = MCMS_MySkin::get_core_default_myskin();
	if ( false === $default || get_stylesheet() == $default->get_stylesheet() ) {
		return true;
	}

	switch_myskin( $default->get_stylesheet() );
	return false;
}

/**
 * Retrieve all myskin modifications.
 *
 * @since 3.1.0
 *
 * @return array|void MySkin modifications.
 */
function get_myskin_mods() {
	$myskin_slug = get_option( 'stylesheet' );
	$mods = get_option( "myskin_mods_$myskin_slug" );
	if ( false === $mods ) {
		$myskin_name = get_option( 'current_myskin' );
		if ( false === $myskin_name )
			$myskin_name = mcms_get_myskin()->get('Name');
		$mods = get_option( "mods_$myskin_name" ); // Deprecated location.
		if ( is_admin() && false !== $mods ) {
			update_option( "myskin_mods_$myskin_slug", $mods );
			delete_option( "mods_$myskin_name" );
		}
	}
	return $mods;
}

/**
 * Retrieve myskin modification value for the current myskin.
 *
 * If the modification name does not exist, then the $default will be passed
 * through {@link https://secure.php.net/sprintf sprintf()} PHP function with the first
 * string the template directory URI and the second string the stylesheet
 * directory URI.
 *
 * @since 2.1.0
 *
 * @param string      $name    MySkin modification name.
 * @param bool|string $default
 * @return string
 */
function get_myskin_mod( $name, $default = false ) {
	$mods = get_myskin_mods();

	if ( isset( $mods[$name] ) ) {
		/**
		 * Filters the myskin modification, or 'myskin_mod', value.
		 *
		 * The dynamic portion of the hook name, `$name`, refers to
		 * the key name of the modification array. For example,
		 * 'header_textcolor', 'header_image', and so on depending
		 * on the myskin options.
		 *
		 * @since 2.2.0
		 *
		 * @param string $current_mod The value of the current myskin modification.
		 */
		return apply_filters( "myskin_mod_{$name}", $mods[$name] );
	}

	if ( is_string( $default ) )
		$default = sprintf( $default, get_template_directory_uri(), get_stylesheet_directory_uri() );

	/** This filter is documented in mcms-roots/myskin.php */
	return apply_filters( "myskin_mod_{$name}", $default );
}

/**
 * Update myskin modification value for the current myskin.
 *
 * @since 2.1.0
 *
 * @param string $name  MySkin modification name.
 * @param mixed  $value MySkin modification value.
 */
function set_myskin_mod( $name, $value ) {
	$mods = get_myskin_mods();
	$old_value = isset( $mods[ $name ] ) ? $mods[ $name ] : false;

	/**
	 * Filters the myskin mod value on save.
	 *
	 * The dynamic portion of the hook name, `$name`, refers to the key name of
	 * the modification array. For example, 'header_textcolor', 'header_image',
	 * and so on depending on the myskin options.
	 *
	 * @since 3.9.0
	 *
	 * @param string $value     The new value of the myskin mod.
	 * @param string $old_value The current value of the myskin mod.
	 */
	$mods[ $name ] = apply_filters( "pre_set_myskin_mod_{$name}", $value, $old_value );

	$myskin = get_option( 'stylesheet' );
	update_option( "myskin_mods_$myskin", $mods );
}

/**
 * Remove myskin modification name from current myskin list.
 *
 * If removing the name also removes all elements, then the entire option will
 * be removed.
 *
 * @since 2.1.0
 *
 * @param string $name MySkin modification name.
 */
function remove_myskin_mod( $name ) {
	$mods = get_myskin_mods();

	if ( ! isset( $mods[ $name ] ) )
		return;

	unset( $mods[ $name ] );

	if ( empty( $mods ) ) {
		remove_myskin_mods();
		return;
	}
	$myskin = get_option( 'stylesheet' );
	update_option( "myskin_mods_$myskin", $mods );
}

/**
 * Remove myskin modifications option for current myskin.
 *
 * @since 2.1.0
 */
function remove_myskin_mods() {
	delete_option( 'myskin_mods_' . get_option( 'stylesheet' ) );

	// Old style.
	$myskin_name = get_option( 'current_myskin' );
	if ( false === $myskin_name )
		$myskin_name = mcms_get_myskin()->get('Name');
	delete_option( 'mods_' . $myskin_name );
}

/**
 * Retrieves the custom header text color in 3- or 6-digit hexadecimal form.
 *
 * @since 2.1.0
 *
 * @return string Header text color in 3- or 6-digit hexadecimal form (minus the hash symbol).
 */
function get_header_textcolor() {
	return get_myskin_mod('header_textcolor', get_myskin_support( 'custom-header', 'default-text-color' ) );
}

/**
 * Displays the custom header text color in 3- or 6-digit hexadecimal form (minus the hash symbol).
 *
 * @since 2.1.0
 */
function header_textcolor() {
	echo get_header_textcolor();
}

/**
 * Whether to display the header text.
 *
 * @since 3.4.0
 *
 * @return bool
 */
function display_header_text() {
	if ( ! current_myskin_supports( 'custom-header', 'header-text' ) )
		return false;

	$text_color = get_myskin_mod( 'header_textcolor', get_myskin_support( 'custom-header', 'default-text-color' ) );
	return 'blank' !== $text_color;
}

/**
 * Check whether a header image is set or not.
 *
 * @since 4.2.0
 *
 * @see get_header_image()
 *
 * @return bool Whether a header image is set or not.
 */
function has_header_image() {
	return (bool) get_header_image();
}

/**
 * Retrieve header image for custom header.
 *
 * @since 2.1.0
 *
 * @return string|false
 */
function get_header_image() {
	$url = get_myskin_mod( 'header_image', get_myskin_support( 'custom-header', 'default-image' ) );

	if ( 'remove-header' == $url )
		return false;

	if ( is_random_header_image() )
		$url = get_random_header_image();

	return esc_url_raw( set_url_scheme( $url ) );
}

/**
 * Create image tag markup for a custom header image.
 *
 * @since 4.4.0
 *
 * @param array $attr Optional. Additional attributes for the image tag. Can be used
 *                              to override the default attributes. Default empty.
 * @return string HTML image element markup or empty string on failure.
 */
function get_header_image_tag( $attr = array() ) {
	$header = get_custom_header();
	$header->url = get_header_image();

	if ( ! $header->url ) {
		return '';
	}

	$width = absint( $header->width );
	$height = absint( $header->height );

	$attr = mcms_parse_args(
		$attr,
		array(
			'src' => $header->url,
			'width' => $width,
			'height' => $height,
			'alt' => get_bloginfo( 'name' ),
		)
	);

	// Generate 'srcset' and 'sizes' if not already present.
	if ( empty( $attr['srcset'] ) && ! empty( $header->attachment_id ) ) {
		$image_meta = get_post_meta( $header->attachment_id, '_mcms_attachment_metadata', true );
		$size_array = array( $width, $height );

		if ( is_array( $image_meta ) ) {
			$srcset = mcms_calculate_image_srcset( $size_array, $header->url, $image_meta, $header->attachment_id );
			$sizes = ! empty( $attr['sizes'] ) ? $attr['sizes'] : mcms_calculate_image_sizes( $size_array, $header->url, $image_meta, $header->attachment_id );

			if ( $srcset && $sizes ) {
				$attr['srcset'] = $srcset;
				$attr['sizes'] = $sizes;
			}
		}
	}

	$attr = array_map( 'esc_attr', $attr );
	$html = '<img';

	foreach ( $attr as $name => $value ) {
		$html .= ' ' . $name . '="' . $value . '"';
	}

	$html .= ' />';

	/**
	 * Filters the markup of header images.
	 *
	 * @since 4.4.0
	 *
	 * @param string $html   The HTML image tag markup being filtered.
	 * @param object $header The custom header object returned by 'get_custom_header()'.
	 * @param array  $attr   Array of the attributes for the image tag.
	 */
	return apply_filters( 'get_header_image_tag', $html, $header, $attr );
}

/**
 * Display the image markup for a custom header image.
 *
 * @since 4.4.0
 *
 * @param array $attr Optional. Attributes for the image markup. Default empty.
 */
function the_header_image_tag( $attr = array() ) {
	echo get_header_image_tag( $attr );
}

/**
 * Get random header image data from registered images in myskin.
 *
 * @since 3.4.0
 *
 * @access private
 *
 * @global array  $_mcms_default_headers
 * @staticvar object $_mcms_random_header
 *
 * @return object
 */
function _get_random_header_data() {
	static $_mcms_random_header = null;

	if ( empty( $_mcms_random_header ) ) {
		global $_mcms_default_headers;
		$header_image_mod = get_myskin_mod( 'header_image', '' );
		$headers = array();

		if ( 'random-uploaded-image' == $header_image_mod )
			$headers = get_uploaded_header_images();
		elseif ( ! empty( $_mcms_default_headers ) ) {
			if ( 'random-default-image' == $header_image_mod ) {
				$headers = $_mcms_default_headers;
			} else {
				if ( current_myskin_supports( 'custom-header', 'random-default' ) )
					$headers = $_mcms_default_headers;
			}
		}

		if ( empty( $headers ) )
			return new stdClass;

		$_mcms_random_header = (object) $headers[ array_rand( $headers ) ];

		$_mcms_random_header->url =  sprintf( $_mcms_random_header->url, get_template_directory_uri(), get_stylesheet_directory_uri() );
		$_mcms_random_header->thumbnail_url =  sprintf( $_mcms_random_header->thumbnail_url, get_template_directory_uri(), get_stylesheet_directory_uri() );
	}
	return $_mcms_random_header;
}

/**
 * Get random header image url from registered images in myskin.
 *
 * @since 3.2.0
 *
 * @return string Path to header image
 */
function get_random_header_image() {
	$random_image = _get_random_header_data();
	if ( empty( $random_image->url ) )
		return '';
	return $random_image->url;
}

/**
 * Check if random header image is in use.
 *
 * Always true if user expressly chooses the option in Dexign > Header.
 * Also true if myskin has multiple header images registered, no specific header image
 * is chosen, and myskin turns on random headers with add_myskin_support().
 *
 * @since 3.2.0
 *
 * @param string $type The random pool to use. any|default|uploaded
 * @return bool
 */
function is_random_header_image( $type = 'any' ) {
	$header_image_mod = get_myskin_mod( 'header_image', get_myskin_support( 'custom-header', 'default-image' ) );

	if ( 'any' == $type ) {
		if ( 'random-default-image' == $header_image_mod || 'random-uploaded-image' == $header_image_mod || ( '' != get_random_header_image() && empty( $header_image_mod ) ) )
			return true;
	} else {
		if ( "random-$type-image" == $header_image_mod )
			return true;
		elseif ( 'default' == $type && empty( $header_image_mod ) && '' != get_random_header_image() )
			return true;
	}

	return false;
}

/**
 * Display header image URL.
 *
 * @since 2.1.0
 */
function header_image() {
	$image = get_header_image();
	if ( $image ) {
		echo esc_url( $image );
	}
}

/**
 * Get the header images uploaded for the current myskin.
 *
 * @since 3.2.0
 *
 * @return array
 */
function get_uploaded_header_images() {
	$header_images = array();

	// @todo caching
	$headers = get_posts( array( 'post_type' => 'attachment', 'meta_key' => '_mcms_attachment_is_custom_header', 'meta_value' => get_option('stylesheet'), 'orderby' => 'none', 'nopaging' => true ) );

	if ( empty( $headers ) )
		return array();

	foreach ( (array) $headers as $header ) {
		$url = esc_url_raw( mcms_get_attachment_url( $header->ID ) );
		$header_data = mcms_get_attachment_metadata( $header->ID );
		$header_index = $header->ID;

		$header_images[$header_index] = array();
		$header_images[$header_index]['attachment_id'] = $header->ID;
		$header_images[$header_index]['url'] =  $url;
		$header_images[$header_index]['thumbnail_url'] = $url;
		$header_images[$header_index]['alt_text'] = get_post_meta( $header->ID, '_mcms_attachment_image_alt', true );
		$header_images[$header_index]['attachment_parent'] = isset( $header_data['attachment_parent'] ) ? $header_data['attachment_parent'] : '';

		if ( isset( $header_data['width'] ) )
			$header_images[$header_index]['width'] = $header_data['width'];
		if ( isset( $header_data['height'] ) )
			$header_images[$header_index]['height'] = $header_data['height'];
	}

	return $header_images;
}

/**
 * Get the header image data.
 *
 * @since 3.4.0
 *
 * @global array $_mcms_default_headers
 *
 * @return object
 */
function get_custom_header() {
	global $_mcms_default_headers;

	if ( is_random_header_image() ) {
		$data = _get_random_header_data();
	} else {
		$data = get_myskin_mod( 'header_image_data' );
		if ( ! $data && current_myskin_supports( 'custom-header', 'default-image' ) ) {
			$directory_args = array( get_template_directory_uri(), get_stylesheet_directory_uri() );
			$data = array();
			$data['url'] = $data['thumbnail_url'] = vsprintf( get_myskin_support( 'custom-header', 'default-image' ), $directory_args );
			if ( ! empty( $_mcms_default_headers ) ) {
				foreach ( (array) $_mcms_default_headers as $default_header ) {
					$url = vsprintf( $default_header['url'], $directory_args );
					if ( $data['url'] == $url ) {
						$data = $default_header;
						$data['url'] = $url;
						$data['thumbnail_url'] = vsprintf( $data['thumbnail_url'], $directory_args );
						break;
					}
				}
			}
		}
	}

	$default = array(
		'url'           => '',
		'thumbnail_url' => '',
		'width'         => get_myskin_support( 'custom-header', 'width' ),
		'height'        => get_myskin_support( 'custom-header', 'height' ),
		'video'         => get_myskin_support( 'custom-header', 'video' ),
	);
	return (object) mcms_parse_args( $data, $default );
}

/**
 * Register a selection of default headers to be displayed by the custom header admin UI.
 *
 * @since 3.0.0
 *
 * @global array $_mcms_default_headers
 *
 * @param array $headers Array of headers keyed by a string id. The ids point to arrays containing 'url', 'thumbnail_url', and 'description' keys.
 */
function register_default_headers( $headers ) {
	global $_mcms_default_headers;

	$_mcms_default_headers = array_merge( (array) $_mcms_default_headers, (array) $headers );
}

/**
 * Unregister default headers.
 *
 * This function must be called after register_default_headers() has already added the
 * header you want to remove.
 *
 * @see register_default_headers()
 * @since 3.0.0
 *
 * @global array $_mcms_default_headers
 *
 * @param string|array $header The header string id (key of array) to remove, or an array thereof.
 * @return bool|void A single header returns true on success, false on failure.
 *                   There is currently no return value for multiple headers.
 */
function unregister_default_headers( $header ) {
	global $_mcms_default_headers;
	if ( is_array( $header ) ) {
		array_map( 'unregister_default_headers', $header );
	} elseif ( isset( $_mcms_default_headers[ $header ] ) ) {
		unset( $_mcms_default_headers[ $header ] );
		return true;
	} else {
		return false;
	}
}

/**
 * Check whether a header video is set or not.
 *
 * @since 4.7.0
 *
 * @see get_header_video_url()
 *
 * @return bool Whether a header video is set or not.
 */
function has_header_video() {
	return (bool) get_header_video_url();
}

/**
 * Retrieve header video URL for custom header.
 *
 * Uses a local video if present, or falls back to an external video.
 *
 * @since 4.7.0
 *
 * @return string|false Header video URL or false if there is no video.
 */
function get_header_video_url() {
	$id = absint( get_myskin_mod( 'header_video' ) );
	$url = esc_url( get_myskin_mod( 'external_header_video' ) );

	if ( $id ) {
		// Get the file URL from the attachment ID.
		$url = mcms_get_attachment_url( $id );
	}

	/**
	 * Filters the header video URL.
	 *
	 * @since 4.7.3
	 *
	 * @param string $url Header video URL, if available.
	 */
	$url = apply_filters( 'get_header_video_url', $url );

	if ( ! $id && ! $url ) {
		return false;
	}

	return esc_url_raw( set_url_scheme( $url ) );
}

/**
 * Display header video URL.
 *
 * @since 4.7.0
 */
function the_header_video_url() {
	$video = get_header_video_url();
	if ( $video ) {
		echo esc_url( $video );
	}
}

/**
 * Retrieve header video settings.
 *
 * @since 4.7.0
 *
 * @return array
 */
function get_header_video_settings() {
	$header     = get_custom_header();
	$video_url  = get_header_video_url();
	$video_type = mcms_check_filetype( $video_url, mcms_get_mime_types() );

	$settings = array(
		'mimeType'  => '',
		'posterUrl' => get_header_image(),
		'videoUrl'  => $video_url,
		'width'     => absint( $header->width ),
		'height'    => absint( $header->height ),
		'minWidth'  => 900,
		'minHeight' => 500,
		'l10n'      => array(
			'pause'      => __( 'Pause' ),
			'play'       => __( 'Play' ),
			'pauseSpeak' => __( 'Video is paused.'),
			'playSpeak'  => __( 'Video is playing.'),
		),
	);

	if ( preg_match( '#^https?://(?:www\.)?(?:youtube\.com/watch|youtu\.be/)#', $video_url ) ) {
		$settings['mimeType'] = 'video/x-youtube';
	} elseif ( ! empty( $video_type['type'] ) ) {
		$settings['mimeType'] = $video_type['type'];
	}

	return apply_filters( 'header_video_settings', $settings );
}

/**
 * Check whether a custom header is set or not.
 *
 * @since 4.7.0
 *
 * @return bool True if a custom header is set. False if not.
 */
function has_custom_header() {
	if ( has_header_image() || ( has_header_video() && is_header_video_active() ) ) {
		return true;
	}

	return false;
}

/**
 * Checks whether the custom header video is eligible to show on the current page.
 *
 * @since 4.7.0
 *
 * @return bool True if the custom header video should be shown. False if not.
 */
function is_header_video_active() {
	if ( ! get_myskin_support( 'custom-header', 'video' ) ) {
		return false;
	}

	$video_active_cb = get_myskin_support( 'custom-header', 'video-active-callback' );

	if ( empty( $video_active_cb ) || ! is_callable( $video_active_cb ) ) {
		$show_video = true;
	} else {
		$show_video = call_user_func( $video_active_cb );
	}

	/**
	 * Modify whether the custom header video is eligible to show on the current page.
	 *
	 * @since 4.7.0
	 *
	 * @param bool $show_video Whether the custom header video should be shown. Returns the value
	 *                         of the myskin setting for the `custom-header`'s `video-active-callback`.
	 *                         If no callback is set, the default value is that of `is_front_page()`.
	 */
	return apply_filters( 'is_header_video_active', $show_video );
}

/**
 * Retrieve the markup for a custom header.
 *
 * The container div will always be returned in the Customizer preview.
 *
 * @since 4.7.0
 *
 * @return string The markup for a custom header on success.
 */
function get_custom_header_markup() {
	if ( ! has_custom_header() && ! is_customize_preview() ) {
		return '';
	}

	return sprintf(
		'<div id="mcms-custom-header" class="mcms-custom-header">%s</div>',
		get_header_image_tag()
	);
}

/**
 * Print the markup for a custom header.
 *
 * A container div will always be printed in the Customizer preview.
 *
 * @since 4.7.0
 */
function the_custom_header_markup() {
	$custom_header = get_custom_header_markup();
	if ( empty( $custom_header ) ) {
		return;
	}

	echo $custom_header;

	if ( is_header_video_active() && ( has_header_video() || is_customize_preview() ) ) {
		mcms_enqueue_script( 'mcms-custom-header' );
		mcms_localize_script( 'mcms-custom-header', '_mcmsCustomHeaderSettings', get_header_video_settings() );
	}
}

/**
 * Retrieve background image for custom background.
 *
 * @since 3.0.0
 *
 * @return string
 */
function get_background_image() {
	return get_myskin_mod('background_image', get_myskin_support( 'custom-background', 'default-image' ) );
}

/**
 * Display background image path.
 *
 * @since 3.0.0
 */
function background_image() {
	echo get_background_image();
}

/**
 * Retrieve value for custom background color.
 *
 * @since 3.0.0
 *
 * @return string
 */
function get_background_color() {
	return get_myskin_mod('background_color', get_myskin_support( 'custom-background', 'default-color' ) );
}

/**
 * Display background color value.
 *
 * @since 3.0.0
 */
function background_color() {
	echo get_background_color();
}

/**
 * Default custom background callback.
 *
 * @since 3.0.0
 */
function _custom_background_cb() {
	// $background is the saved custom image, or the default image.
	$background = set_url_scheme( get_background_image() );

	// $color is the saved custom color.
	// A default has to be specified in style.css. It will not be printed here.
	$color = get_background_color();

	if ( $color === get_myskin_support( 'custom-background', 'default-color' ) ) {
		$color = false;
	}

	if ( ! $background && ! $color ) {
		if ( is_customize_preview() ) {
			echo '<style type="text/css" id="custom-background-css"></style>';
		}
		return;
	}

	$style = $color ? "background-color: #$color;" : '';

	if ( $background ) {
		$image = ' background-image: url("' . esc_url_raw( $background ) . '");';

		// Background Position.
		$position_x = get_myskin_mod( 'background_position_x', get_myskin_support( 'custom-background', 'default-position-x' ) );
		$position_y = get_myskin_mod( 'background_position_y', get_myskin_support( 'custom-background', 'default-position-y' ) );

		if ( ! in_array( $position_x, array( 'left', 'center', 'right' ), true ) ) {
			$position_x = 'left';
		}

		if ( ! in_array( $position_y, array( 'top', 'center', 'bottom' ), true ) ) {
			$position_y = 'top';
		}

		$position = " background-position: $position_x $position_y;";

		// Background Size.
		$size = get_myskin_mod( 'background_size', get_myskin_support( 'custom-background', 'default-size' ) );

		if ( ! in_array( $size, array( 'auto', 'contain', 'cover' ), true ) ) {
			$size = 'auto';
		}

		$size = " background-size: $size;";

		// Background Repeat.
		$repeat = get_myskin_mod( 'background_repeat', get_myskin_support( 'custom-background', 'default-repeat' ) );

		if ( ! in_array( $repeat, array( 'repeat-x', 'repeat-y', 'repeat', 'no-repeat' ), true ) ) {
			$repeat = 'repeat';
		}

		$repeat = " background-repeat: $repeat;";

		// Background Scroll.
		$attachment = get_myskin_mod( 'background_attachment', get_myskin_support( 'custom-background', 'default-attachment' ) );

		if ( 'fixed' !== $attachment ) {
			$attachment = 'scroll';
		}

		$attachment = " background-attachment: $attachment;";

		$style .= $image . $position . $size . $repeat . $attachment;
	}
?>
<style type="text/css" id="custom-background-css">
body.custom-background { <?php echo trim( $style ); ?> }
</style>
<?php
}

/**
 * Render the Custom CSS style element.
 *
 * @since 4.7.0
 */
function mcms_custom_css_cb() {
	$styles = mcms_get_custom_css();
	if ( $styles || is_customize_preview() ) : ?>
		<style type="text/css" id="mcms-custom-css">
			<?php echo strip_tags( $styles ); // Note that esc_html() cannot be used because `div &gt; span` is not interpreted properly. ?>
		</style>
	<?php endif;
}

/**
 * Fetch the `custom_css` post for a given myskin.
 *
 * @since 4.7.0
 *
 * @param string $stylesheet Optional. A myskin object stylesheet name. Defaults to the current myskin.
 * @return MCMS_Post|null The custom_css post or null if none exists.
 */
function mcms_get_custom_css_post( $stylesheet = '' ) {
	if ( empty( $stylesheet ) ) {
		$stylesheet = get_stylesheet();
	}

	$custom_css_query_vars = array(
		'post_type'              => 'custom_css',
		'post_status'            => get_post_stati(),
		'name'                   => sanitize_title( $stylesheet ),
		'posts_per_page'         => 1,
		'no_found_rows'          => true,
		'cache_results'          => true,
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false,
		'lazy_load_term_meta'    => false,
	);

	$post = null;
	if ( get_stylesheet() === $stylesheet ) {
		$post_id = get_myskin_mod( 'custom_css_post_id' );

		if ( $post_id > 0 && get_post( $post_id ) ) {
			$post = get_post( $post_id );
		}

		// `-1` indicates no post exists; no query necessary.
		if ( ! $post && -1 !== $post_id ) {
			$query = new MCMS_Query( $custom_css_query_vars );
			$post = $query->post;
			/*
			 * Cache the lookup. See mcms_update_custom_css_post().
			 * @todo This should get cleared if a custom_css post is added/removed.
			 */
			set_myskin_mod( 'custom_css_post_id', $post ? $post->ID : -1 );
		}
	} else {
		$query = new MCMS_Query( $custom_css_query_vars );
		$post = $query->post;
	}

	return $post;
}

/**
 * Fetch the saved Custom CSS content for rendering.
 *
 * @since 4.7.0
 *
 * @param string $stylesheet Optional. A myskin object stylesheet name. Defaults to the current myskin.
 * @return string The Custom CSS Post content.
 */
function mcms_get_custom_css( $stylesheet = '' ) {
	$css = '';

	if ( empty( $stylesheet ) ) {
		$stylesheet = get_stylesheet();
	}

	$post = mcms_get_custom_css_post( $stylesheet );
	if ( $post ) {
		$css = $post->post_content;
	}

	/**
	 * Filters the Custom CSS Output into the <head>.
	 *
	 * @since 4.7.0
	 *
	 * @param string $css        CSS pulled in from the Custom CSS CPT.
	 * @param string $stylesheet The myskin stylesheet name.
	 */
	$css = apply_filters( 'mcms_get_custom_css', $css, $stylesheet );

	return $css;
}

/**
 * Update the `custom_css` post for a given myskin.
 *
 * Inserts a `custom_css` post when one doesn't yet exist.
 *
 * @since 4.7.0
 *
 * @param string $css CSS, stored in `post_content`.
 * @param array  $args {
 *     Args.
 *
 *     @type string $preprocessed Pre-processed CSS, stored in `post_content_filtered`. Normally empty string. Optional.
 *     @type string $stylesheet   Stylesheet (child myskin) to update. Optional, defaults to current myskin/stylesheet.
 * }
 * @return MCMS_Post|MCMS_Error Post on success, error on failure.
 */
function mcms_update_custom_css_post( $css, $args = array() ) {
	$args = mcms_parse_args( $args, array(
		'preprocessed' => '',
		'stylesheet' => get_stylesheet(),
	) );

	$data = array(
		'css' => $css,
		'preprocessed' => $args['preprocessed'],
	);

	/**
	 * Filters the `css` (`post_content`) and `preprocessed` (`post_content_filtered`) args for a `custom_css` post being updated.
	 *
	 * This filter can be used by module that offer CSS pre-processors, to store the original
	 * pre-processed CSS in `post_content_filtered` and then store processed CSS in `post_content`.
	 * When used in this way, the `post_content_filtered` should be supplied as the setting value
	 * instead of `post_content` via a the `customize_value_custom_css` filter, for example:
	 *
	 * <code>
	 * add_filter( 'customize_value_custom_css', function( $value, $setting ) {
	 *     $post = mcms_get_custom_css_post( $setting->stylesheet );
	 *     if ( $post && ! empty( $post->post_content_filtered ) ) {
	 *         $css = $post->post_content_filtered;
	 *     }
	 *     return $css;
	 * }, 10, 2 );
	 * </code>
	 *
	 * @since 4.7.0
	 * @param array $data {
	 *     Custom CSS data.
	 *
	 *     @type string $css          CSS stored in `post_content`.
	 *     @type string $preprocessed Pre-processed CSS stored in `post_content_filtered`. Normally empty string.
	 * }
	 * @param array $args {
	 *     The args passed into `mcms_update_custom_css_post()` merged with defaults.
	 *
	 *     @type string $css          The original CSS passed in to be updated.
	 *     @type string $preprocessed The original preprocessed CSS passed in to be updated.
	 *     @type string $stylesheet   The stylesheet (myskin) being updated.
	 * }
	 */
	$data = apply_filters( 'update_custom_css_data', $data, array_merge( $args, compact( 'css' ) ) );

	$post_data = array(
		'post_title' => $args['stylesheet'],
		'post_name' => sanitize_title( $args['stylesheet'] ),
		'post_type' => 'custom_css',
		'post_status' => 'publish',
		'post_content' => $data['css'],
		'post_content_filtered' => $data['preprocessed'],
	);

	// Update post if it already exists, otherwise create a new one.
	$post = mcms_get_custom_css_post( $args['stylesheet'] );
	if ( $post ) {
		$post_data['ID'] = $post->ID;
		$r = mcms_update_post( mcms_slash( $post_data ), true );
	} else {
		$r = mcms_insert_post( mcms_slash( $post_data ), true );

		if ( ! is_mcms_error( $r ) ) {
			if ( get_stylesheet() === $args['stylesheet'] ) {
				set_myskin_mod( 'custom_css_post_id', $r );
			}

			// Trigger creation of a revision. This should be removed once #30854 is resolved.
			if ( 0 === count( mcms_get_post_revisions( $r ) ) ) {
				mcms_save_post_revision( $r );
			}
		}
	}

	if ( is_mcms_error( $r ) ) {
		return $r;
	}
	return get_post( $r );
}

/**
 * Add callback for custom TinyMCE editor stylesheets.
 *
 * The parameter $stylesheet is the name of the stylesheet, relative to
 * the myskin root. It also accepts an array of stylesheets.
 * It is optional and defaults to 'editor-style.css'.
 *
 * This function automatically adds another stylesheet with -rtl prefix, e.g. editor-style-rtl.css.
 * If that file doesn't exist, it is removed before adding the stylesheet(s) to TinyMCE.
 * If an array of stylesheets is passed to add_editor_style(),
 * RTL is only added for the first stylesheet.
 *
 * Since version 3.4 the TinyMCE body has .rtl CSS class.
 * It is a better option to use that class and add any RTL styles to the main stylesheet.
 *
 * @since 3.0.0
 *
 * @global array $editor_styles
 *
 * @param array|string $stylesheet Optional. Stylesheet name or array thereof, relative to myskin root.
 * 	                               Defaults to 'editor-style.css'
 */
function add_editor_style( $stylesheet = 'editor-style.css' ) {
	add_myskin_support( 'editor-style' );

	if ( ! is_admin() )
		return;

	global $editor_styles;
	$editor_styles = (array) $editor_styles;
	$stylesheet    = (array) $stylesheet;
	if ( is_rtl() ) {
		$rtl_stylesheet = str_replace('.css', '-rtl.css', $stylesheet[0]);
		$stylesheet[] = $rtl_stylesheet;
	}

	$editor_styles = array_merge( $editor_styles, $stylesheet );
}

/**
 * Removes all visual editor stylesheets.
 *
 * @since 3.1.0
 *
 * @global array $editor_styles
 *
 * @return bool True on success, false if there were no stylesheets to remove.
 */
function remove_editor_styles() {
	if ( ! current_myskin_supports( 'editor-style' ) )
		return false;
	_remove_myskin_support( 'editor-style' );
	if ( is_admin() )
		$GLOBALS['editor_styles'] = array();
	return true;
}

/**
 * Retrieve any registered editor stylesheets
 *
 * @since 4.0.0
 *
 * @global array $editor_styles Registered editor stylesheets
 *
 * @return array If registered, a list of editor stylesheet URLs.
 */
function get_editor_stylesheets() {
	$stylesheets = array();
	// load editor_style.css if the current myskin supports it
	if ( ! empty( $GLOBALS['editor_styles'] ) && is_array( $GLOBALS['editor_styles'] ) ) {
		$editor_styles = $GLOBALS['editor_styles'];

		$editor_styles = array_unique( array_filter( $editor_styles ) );
		$style_uri = get_stylesheet_directory_uri();
		$style_dir = get_stylesheet_directory();

		// Support externally referenced styles (like, say, fonts).
		foreach ( $editor_styles as $key => $file ) {
			if ( preg_match( '~^(https?:)?//~', $file ) ) {
				$stylesheets[] = esc_url_raw( $file );
				unset( $editor_styles[ $key ] );
			}
		}

		// Look in a parent myskin first, that way child myskin CSS overrides.
		if ( is_child_myskin() ) {
			$template_uri = get_template_directory_uri();
			$template_dir = get_template_directory();

			foreach ( $editor_styles as $key => $file ) {
				if ( $file && file_exists( "$template_dir/$file" ) ) {
					$stylesheets[] = "$template_uri/$file";
				}
			}
		}

		foreach ( $editor_styles as $file ) {
			if ( $file && file_exists( "$style_dir/$file" ) ) {
				$stylesheets[] = "$style_uri/$file";
			}
		}
	}

	/**
	 * Filters the array of stylesheets applied to the editor.
	 *
	 * @since 4.3.0
	 *
	 * @param array $stylesheets Array of stylesheets to be applied to the editor.
	 */
	return apply_filters( 'editor_stylesheets', $stylesheets );
}

/**
 * Expand a myskin's starter content configuration using core-provided data.
 *
 * @since 4.7.0
 *
 * @return array Array of starter content.
 */
function get_myskin_starter_content() {
	$myskin_support = get_myskin_support( 'starter-content' );
	if ( is_array( $myskin_support ) && ! empty( $myskin_support[0] ) && is_array( $myskin_support[0] ) ) {
		$config = $myskin_support[0];
	} else {
		$config = array();
	}

	$core_content = array(
		'widgets' => array(
			'text_business_info' => array( 'text', array(
				'title' => _x( 'Find Us', 'MySkin starter content' ),
				'text' => join( '', array(
					'<strong>' . _x( 'Address', 'MySkin starter content' ) . "</strong>\n",
					_x( '123 Main Street', 'MySkin starter content' ) . "\n" . _x( 'New York, NY 10001', 'MySkin starter content' ) . "\n\n",
					'<strong>' . _x( 'Hours', 'MySkin starter content' ) . "</strong>\n",
					_x( 'Monday&mdash;Friday: 9:00AM&ndash;5:00PM', 'MySkin starter content' ) . "\n" . _x( 'Saturday &amp; Sunday: 11:00AM&ndash;3:00PM', 'MySkin starter content' )
				) ),
				'filter' => true,
				'visual' => true,
			) ),
			'text_about' => array( 'text', array(
				'title' => _x( 'About This Site', 'MySkin starter content' ),
				'text' => _x( 'This may be a good place to introduce yourself and your site or include some credits.', 'MySkin starter content' ),
				'filter' => true,
				'visual' => true,
			) ),
			'archives' => array( 'archives', array(
				'title' => _x( 'Archives', 'MySkin starter content' ),
			) ),
			'calendar' => array( 'calendar', array(
				'title' => _x( 'Calendar', 'MySkin starter content' ),
			) ),
			'categories' => array( 'categories', array(
				'title' => _x( 'Categories', 'MySkin starter content' ),
			) ),
			'meta' => array( 'meta', array(
				'title' => _x( 'Meta', 'MySkin starter content' ),
			) ),
			'recent-comments' => array( 'recent-comments', array(
				'title' => _x( 'Recent Comments', 'MySkin starter content' ),
			) ),
			'recent-posts' => array( 'recent-posts', array(
				'title' => _x( 'Recent Posts', 'MySkin starter content' ),
			) ),
			'search' => array( 'search', array(
				'title' => _x( 'Search', 'MySkin starter content' ),
			) ),
		),
		'nav_menus' => array(
			'link_home' => array(
				'type' => 'custom',
				'title' => _x( 'Home', 'MySkin starter content' ),
				'url' => home_url( '/' ),
			),
			'page_home' => array( // Deprecated in favor of link_home.
				'type' => 'post_type',
				'object' => 'page',
				'object_id' => '{{home}}',
			),
			'page_about' => array(
				'type' => 'post_type',
				'object' => 'page',
				'object_id' => '{{about}}',
			),
			'page_blog' => array(
				'type' => 'post_type',
				'object' => 'page',
				'object_id' => '{{blog}}',
			),
			'page_news' => array(
				'type' => 'post_type',
				'object' => 'page',
				'object_id' => '{{news}}',
			),
			'page_contact' => array(
				'type' => 'post_type',
				'object' => 'page',
				'object_id' => '{{contact}}',
			),

			'link_email' => array(
				'title' => _x( 'Email', 'MySkin starter content' ),
				'url' => 'mailto:mandarincms@example.com',
			),
			'link_facebook' => array(
				'title' => _x( 'Facebook', 'MySkin starter content' ),
				'url' => 'https://www.facebook.com/mandarincms',
			),
			'link_foursquare' => array(
				'title' => _x( 'Foursquare', 'MySkin starter content' ),
				'url' => 'https://foursquare.com/',
			),
			'link_github' => array(
				'title' => _x( 'GitHub', 'MySkin starter content' ),
				'url' => 'https://github.com/mandarincms/',
			),
			'link_instagram' => array(
				'title' => _x( 'Instagram', 'MySkin starter content' ),
				'url' => 'https://www.instagram.com/explore/tags/wordcamp/',
			),
			'link_linkedin' => array(
				'title' => _x( 'LinkedIn', 'MySkin starter content' ),
				'url' => 'https://www.linkedin.com/company/1089783',
			),
			'link_pinterest' => array(
				'title' => _x( 'Pinterest', 'MySkin starter content' ),
				'url' => 'https://www.pinterest.com/',
			),
			'link_twitter' => array(
				'title' => _x( 'Twitter', 'MySkin starter content' ),
				'url' => 'https://twitter.com/mandarincms',
			),
			'link_yelp' => array(
				'title' => _x( 'Yelp', 'MySkin starter content' ),
				'url' => 'https://www.yelp.com',
			),
			'link_youtube' => array(
				'title' => _x( 'YouTube', 'MySkin starter content' ),
				'url' => 'https://www.youtube.com/channel/UCdof4Ju7amm1chz1gi1T2ZA',
			),
		),
		'posts' => array(
			'home' => array(
				'post_type' => 'page',
				'post_title' => _x( 'Home', 'MySkin starter content' ),
				'post_content' => _x( 'Welcome to your site! This is your homepage, which is what most visitors will see when they come to your site for the first time.', 'MySkin starter content' ),
			),
			'about' => array(
				'post_type' => 'page',
				'post_title' => _x( 'About', 'MySkin starter content' ),
				'post_content' => _x( 'You might be an artist who would like to introduce yourself and your work here or maybe you&rsquo;re a business with a mission to describe.', 'MySkin starter content' ),
			),
			'contact' => array(
				'post_type' => 'page',
				'post_title' => _x( 'Contact', 'MySkin starter content' ),
				'post_content' => _x( 'This is a page with some basic contact information, such as an address and phone number. You might also try a module to add a contact form.', 'MySkin starter content' ),
			),
			'blog' => array(
				'post_type' => 'page',
				'post_title' => _x( 'Blog', 'MySkin starter content' ),
			),
			'news' => array(
				'post_type' => 'page',
				'post_title' => _x( 'News', 'MySkin starter content' ),
			),

			'homepage-section' => array(
				'post_type' => 'page',
				'post_title' => _x( 'A homepage section', 'MySkin starter content' ),
				'post_content' => _x( 'This is an example of a homepage section. Homepage sections can be any page other than the homepage itself, including the page that shows your latest blog posts.', 'MySkin starter content' ),
			),
		),
	);

	$content = array();

	foreach ( $config as $type => $args ) {
		switch( $type ) {
			// Use options and myskin_mods as-is.
			case 'options' :
			case 'myskin_mods' :
				$content[ $type ] = $config[ $type ];
				break;

			// Widgets are grouped into sidebars.
			case 'widgets' :
				foreach ( $config[ $type ] as $sidebar_id => $widgets ) {
					foreach ( $widgets as $id => $widget ) {
						if ( is_array( $widget ) ) {

							// Item extends core content.
							if ( ! empty( $core_content[ $type ][ $id ] ) ) {
								$widget = array(
									$core_content[ $type ][ $id ][0],
									array_merge( $core_content[ $type ][ $id ][1], $widget ),
								);
							}

							$content[ $type ][ $sidebar_id ][] = $widget;
						} elseif ( is_string( $widget ) && ! empty( $core_content[ $type ] ) && ! empty( $core_content[ $type ][ $widget ] ) ) {
							$content[ $type ][ $sidebar_id ][] = $core_content[ $type ][ $widget ];
						}
					}
				}
				break;

			// And nav menu items are grouped into nav menus.
			case 'nav_menus' :
				foreach ( $config[ $type ] as $nav_menu_location => $nav_menu ) {

					// Ensure nav menus get a name.
					if ( empty( $nav_menu['name'] ) ) {
						$nav_menu['name'] = $nav_menu_location;
					}

					$content[ $type ][ $nav_menu_location ]['name'] = $nav_menu['name'];

					foreach ( $nav_menu['items'] as $id => $nav_menu_item ) {
						if ( is_array( $nav_menu_item ) ) {

							// Item extends core content.
							if ( ! empty( $core_content[ $type ][ $id ] ) ) {
								$nav_menu_item = array_merge( $core_content[ $type ][ $id ], $nav_menu_item );
							}

							$content[ $type ][ $nav_menu_location ]['items'][] = $nav_menu_item;
						} elseif ( is_string( $nav_menu_item ) && ! empty( $core_content[ $type ] ) && ! empty( $core_content[ $type ][ $nav_menu_item ] ) ) {
							$content[ $type ][ $nav_menu_location ]['items'][] = $core_content[ $type ][ $nav_menu_item ];
						}
					}
				}
				break;

			// Attachments are posts but have special treatment.
			case 'attachments' :
				foreach ( $config[ $type ] as $id => $item ) {
					if ( ! empty( $item['file'] ) ) {
						$content[ $type ][ $id ] = $item;
					}
				}
				break;

			// All that's left now are posts (besides attachments). Not a default case for the sake of clarity and future work.
			case 'posts' :
				foreach ( $config[ $type ] as $id => $item ) {
					if ( is_array( $item ) ) {

						// Item extends core content.
						if ( ! empty( $core_content[ $type ][ $id ] ) ) {
							$item = array_merge( $core_content[ $type ][ $id ], $item );
						}

						// Enforce a subset of fields.
						$content[ $type ][ $id ] = mcms_array_slice_assoc(
							$item,
							array(
								'post_type',
								'post_title',
								'post_excerpt',
								'post_name',
								'post_content',
								'menu_order',
								'comment_status',
								'thumbnail',
								'template',
							)
						);
					} elseif ( is_string( $item ) && ! empty( $core_content[ $type ][ $item ] ) ) {
						$content[ $type ][ $item ] = $core_content[ $type ][ $item ];
					}
				}
				break;
		}
	}

	/**
	 * Filters the expanded array of starter content.
	 *
	 * @since 4.7.0
	 *
	 * @param array $content Array of starter content.
	 * @param array $config  Array of myskin-specific starter content configuration.
	 */
	return apply_filters( 'get_myskin_starter_content', $content, $config );
}

/**
 * Registers myskin support for a given feature.
 *
 * Must be called in the myskin's functions.php file to work.
 * If attached to a hook, it must be {@see 'after_setup_myskin'}.
 * The {@see 'init'} hook may be too late for some features.
 *
 * @since 2.9.0
 * @since 3.6.0 The `html5` feature was added
 * @since 3.9.0 The `html5` feature now also accepts 'gallery' and 'caption'
 * @since 4.1.0 The `title-tag` feature was added
 * @since 4.5.0 The `customize-selective-refresh-widgets` feature was added
 * @since 4.7.0 The `starter-content` feature was added
 *
 * @global array $_mcms_myskin_features
 *
 * @param string $feature  The feature being added. Likely core values include 'post-formats',
 *                         'post-thumbnails', 'html5', 'custom-logo', 'custom-header-uploads',
 *                         'custom-header', 'custom-background', 'title-tag', 'starter-content', etc.
 * @param mixed  $args,... Optional extra arguments to pass along with certain features.
 * @return void|bool False on failure, void otherwise.
 */
function add_myskin_support( $feature ) {
	global $_mcms_myskin_features;

	if ( func_num_args() == 1 )
		$args = true;
	else
		$args = array_slice( func_get_args(), 1 );

	switch ( $feature ) {
		case 'post-thumbnails':
			// All post types are already supported.
			if ( true === get_myskin_support( 'post-thumbnails' ) ) {
				return;
			}

			/*
			 * Merge post types with any that already declared their support
			 * for post thumbnails.
			 */
			if ( is_array( $args[0] ) && isset( $_mcms_myskin_features['post-thumbnails'] ) ) {
				$args[0] = array_unique( array_merge( $_mcms_myskin_features['post-thumbnails'][0], $args[0] ) );
			}

			break;

		case 'post-formats' :
			if ( is_array( $args[0] ) ) {
				$post_formats = get_post_format_slugs();
				unset( $post_formats['standard'] );

				$args[0] = array_intersect( $args[0], array_keys( $post_formats ) );
			}
			break;

		case 'html5' :
			// You can't just pass 'html5', you need to pass an array of types.
			if ( empty( $args[0] ) ) {
				// Build an array of types for back-compat.
				$args = array( 0 => array( 'comment-list', 'comment-form', 'search-form' ) );
			} elseif ( ! is_array( $args[0] ) ) {
				_doing_it_wrong( "add_myskin_support( 'html5' )", __( 'You need to pass an array of types.' ), '3.6.1' );
				return false;
			}

			// Calling 'html5' again merges, rather than overwrites.
			if ( isset( $_mcms_myskin_features['html5'] ) )
				$args[0] = array_merge( $_mcms_myskin_features['html5'][0], $args[0] );
			break;

		case 'custom-logo':
			if ( ! is_array( $args ) ) {
				$args = array( 0 => array() );
			}
			$defaults = array(
				'width'       => null,
				'height'      => null,
				'flex-width'  => false,
				'flex-height' => false,
				'header-text' => '',
			);
			$args[0] = mcms_parse_args( array_intersect_key( $args[0], $defaults ), $defaults );

			// Allow full flexibility if no size is specified.
			if ( is_null( $args[0]['width'] ) && is_null( $args[0]['height'] ) ) {
				$args[0]['flex-width']  = true;
				$args[0]['flex-height'] = true;
			}
			break;

		case 'custom-header-uploads' :
			return add_myskin_support( 'custom-header', array( 'uploads' => true ) );

		case 'custom-header' :
			if ( ! is_array( $args ) )
				$args = array( 0 => array() );

			$defaults = array(
				'default-image' => '',
				'random-default' => false,
				'width' => 0,
				'height' => 0,
				'flex-height' => false,
				'flex-width' => false,
				'default-text-color' => '',
				'header-text' => true,
				'uploads' => true,
				'mcms-head-callback' => '',
				'admin-head-callback' => '',
				'admin-preview-callback' => '',
				'video' => false,
				'video-active-callback' => 'is_front_page',
			);

			$jit = isset( $args[0]['__jit'] );
			unset( $args[0]['__jit'] );

			// Merge in data from previous add_myskin_support() calls.
			// The first value registered wins. (A child myskin is set up first.)
			if ( isset( $_mcms_myskin_features['custom-header'] ) )
				$args[0] = mcms_parse_args( $_mcms_myskin_features['custom-header'][0], $args[0] );

			// Load in the defaults at the end, as we need to insure first one wins.
			// This will cause all constants to be defined, as each arg will then be set to the default.
			if ( $jit )
				$args[0] = mcms_parse_args( $args[0], $defaults );

			// If a constant was defined, use that value. Otherwise, define the constant to ensure
			// the constant is always accurate (and is not defined later,  overriding our value).
			// As stated above, the first value wins.
			// Once we get to mcms_loaded (just-in-time), define any constants we haven't already.
			// Constants are lame. Don't reference them. This is just for backward compatibility.

			if ( defined( 'NO_HEADER_TEXT' ) )
				$args[0]['header-text'] = ! NO_HEADER_TEXT;
			elseif ( isset( $args[0]['header-text'] ) )
				define( 'NO_HEADER_TEXT', empty( $args[0]['header-text'] ) );

			if ( defined( 'HEADER_IMAGE_WIDTH' ) )
				$args[0]['width'] = (int) HEADER_IMAGE_WIDTH;
			elseif ( isset( $args[0]['width'] ) )
				define( 'HEADER_IMAGE_WIDTH', (int) $args[0]['width'] );

			if ( defined( 'HEADER_IMAGE_HEIGHT' ) )
				$args[0]['height'] = (int) HEADER_IMAGE_HEIGHT;
			elseif ( isset( $args[0]['height'] ) )
				define( 'HEADER_IMAGE_HEIGHT', (int) $args[0]['height'] );

			if ( defined( 'HEADER_TEXTCOLOR' ) )
				$args[0]['default-text-color'] = HEADER_TEXTCOLOR;
			elseif ( isset( $args[0]['default-text-color'] ) )
				define( 'HEADER_TEXTCOLOR', $args[0]['default-text-color'] );

			if ( defined( 'HEADER_IMAGE' ) )
				$args[0]['default-image'] = HEADER_IMAGE;
			elseif ( isset( $args[0]['default-image'] ) )
				define( 'HEADER_IMAGE', $args[0]['default-image'] );

			if ( $jit && ! empty( $args[0]['default-image'] ) )
				$args[0]['random-default'] = false;

			// If headers are supported, and we still don't have a defined width or height,
			// we have implicit flex sizes.
			if ( $jit ) {
				if ( empty( $args[0]['width'] ) && empty( $args[0]['flex-width'] ) )
					$args[0]['flex-width'] = true;
				if ( empty( $args[0]['height'] ) && empty( $args[0]['flex-height'] ) )
					$args[0]['flex-height'] = true;
			}

			break;

		case 'custom-background' :
			if ( ! is_array( $args ) )
				$args = array( 0 => array() );

			$defaults = array(
				'default-image'          => '',
				'default-preset'         => 'default',
				'default-position-x'     => 'left',
				'default-position-y'     => 'top',
				'default-size'           => 'auto',
				'default-repeat'         => 'repeat',
				'default-attachment'     => 'scroll',
				'default-color'          => '',
				'mcms-head-callback'       => '_custom_background_cb',
				'admin-head-callback'    => '',
				'admin-preview-callback' => '',
			);

			$jit = isset( $args[0]['__jit'] );
			unset( $args[0]['__jit'] );

			// Merge in data from previous add_myskin_support() calls. The first value registered wins.
			if ( isset( $_mcms_myskin_features['custom-background'] ) )
				$args[0] = mcms_parse_args( $_mcms_myskin_features['custom-background'][0], $args[0] );

			if ( $jit )
				$args[0] = mcms_parse_args( $args[0], $defaults );

			if ( defined( 'BACKGROUND_COLOR' ) )
				$args[0]['default-color'] = BACKGROUND_COLOR;
			elseif ( isset( $args[0]['default-color'] ) || $jit )
				define( 'BACKGROUND_COLOR', $args[0]['default-color'] );

			if ( defined( 'BACKGROUND_IMAGE' ) )
				$args[0]['default-image'] = BACKGROUND_IMAGE;
			elseif ( isset( $args[0]['default-image'] ) || $jit )
				define( 'BACKGROUND_IMAGE', $args[0]['default-image'] );

			break;

		// Ensure that 'title-tag' is accessible in the admin.
		case 'title-tag' :
			// Can be called in functions.php but must happen before mcms_loaded, i.e. not in header.php.
			if ( did_action( 'mcms_loaded' ) ) {
				/* translators: 1: MySkin support 2: hook name */
				_doing_it_wrong( "add_myskin_support( 'title-tag' )", sprintf( __( 'MySkin support for %1$s should be registered before the %2$s hook.' ),
					'<code>title-tag</code>', '<code>mcms_loaded</code>' ), '4.1.0' );

				return false;
			}
	}

	$_mcms_myskin_features[ $feature ] = $args;
}

/**
 * Registers the internal custom header and background routines.
 *
 * @since 3.4.0
 * @access private
 *
 * @global Custom_Image_Header $custom_image_header
 * @global Custom_Background   $custom_background
 */
function _custom_header_background_just_in_time() {
	global $custom_image_header, $custom_background;

	if ( current_myskin_supports( 'custom-header' ) ) {
		// In case any constants were defined after an add_custom_image_header() call, re-run.
		add_myskin_support( 'custom-header', array( '__jit' => true ) );

		$args = get_myskin_support( 'custom-header' );
		if ( $args[0]['mcms-head-callback'] )
			add_action( 'mcms_head', $args[0]['mcms-head-callback'] );

		if ( is_admin() ) {
			require_once( BASED_TREE_URI . 'mcms-admin/custom-header.php' );
			$custom_image_header = new Custom_Image_Header( $args[0]['admin-head-callback'], $args[0]['admin-preview-callback'] );
		}
	}

	if ( current_myskin_supports( 'custom-background' ) ) {
		// In case any constants were defined after an add_custom_background() call, re-run.
		add_myskin_support( 'custom-background', array( '__jit' => true ) );

		$args = get_myskin_support( 'custom-background' );
		add_action( 'mcms_head', $args[0]['mcms-head-callback'] );

		if ( is_admin() ) {
			require_once( BASED_TREE_URI . 'mcms-admin/custom-background.php' );
			$custom_background = new Custom_Background( $args[0]['admin-head-callback'], $args[0]['admin-preview-callback'] );
		}
	}
}

/**
 * Adds CSS to hide header text for custom logo, based on Customizer setting.
 *
 * @since 4.5.0
 * @access private
 */
function _custom_logo_header_styles() {
	if ( ! current_myskin_supports( 'custom-header', 'header-text' ) && get_myskin_support( 'custom-logo', 'header-text' ) && ! get_myskin_mod( 'header_text', true ) ) {
		$classes = (array) get_myskin_support( 'custom-logo', 'header-text' );
		$classes = array_map( 'sanitize_html_class', $classes );
		$classes = '.' . implode( ', .', $classes );

		?>
		<!-- Custom Logo: hide header text -->
		<style id="custom-logo-css" type="text/css">
			<?php echo $classes; ?> {
				position: absolute;
				clip: rect(1px, 1px, 1px, 1px);
			}
		</style>
	<?php
	}
}

/**
 * Gets the myskin support arguments passed when registering that support
 *
 * @since 3.1.0
 *
 * @global array $_mcms_myskin_features
 *
 * @param string $feature the feature to check
 * @return mixed The array of extra arguments or the value for the registered feature.
 */
function get_myskin_support( $feature ) {
	global $_mcms_myskin_features;
	if ( ! isset( $_mcms_myskin_features[ $feature ] ) )
		return false;

	if ( func_num_args() <= 1 )
		return $_mcms_myskin_features[ $feature ];

	$args = array_slice( func_get_args(), 1 );
	switch ( $feature ) {
		case 'custom-logo' :
		case 'custom-header' :
		case 'custom-background' :
			if ( isset( $_mcms_myskin_features[ $feature ][0][ $args[0] ] ) )
				return $_mcms_myskin_features[ $feature ][0][ $args[0] ];
			return false;

		default :
			return $_mcms_myskin_features[ $feature ];
	}
}

/**
 * Allows a myskin to de-register its support of a certain feature
 *
 * Should be called in the myskin's functions.php file. Generally would
 * be used for child myskins to override support from the parent myskin.
 *
 * @since 3.0.0
 * @see add_myskin_support()
 * @param string $feature the feature being added
 * @return bool|void Whether feature was removed.
 */
function remove_myskin_support( $feature ) {
	// Blacklist: for internal registrations not used directly by myskins.
	if ( in_array( $feature, array( 'editor-style', 'widgets', 'menus' ) ) )
		return false;

	return _remove_myskin_support( $feature );
}

/**
 * Do not use. Removes myskin support internally, ignorant of the blacklist.
 *
 * @access private
 * @since 3.1.0
 *
 * @global array               $_mcms_myskin_features
 * @global Custom_Image_Header $custom_image_header
 * @global Custom_Background   $custom_background
 *
 * @param string $feature
 */
function _remove_myskin_support( $feature ) {
	global $_mcms_myskin_features;

	switch ( $feature ) {
		case 'custom-header-uploads' :
			if ( ! isset( $_mcms_myskin_features['custom-header'] ) )
				return false;
			add_myskin_support( 'custom-header', array( 'uploads' => false ) );
			return; // Do not continue - custom-header-uploads no longer exists.
	}

	if ( ! isset( $_mcms_myskin_features[ $feature ] ) )
		return false;

	switch ( $feature ) {
		case 'custom-header' :
			if ( ! did_action( 'mcms_loaded' ) )
				break;
			$support = get_myskin_support( 'custom-header' );
			if ( isset( $support[0]['mcms-head-callback'] ) ) {
				remove_action( 'mcms_head', $support[0]['mcms-head-callback'] );
			}
			if ( isset( $GLOBALS['custom_image_header'] ) ) {
				remove_action( 'admin_menu', array( $GLOBALS['custom_image_header'], 'init' ) );
				unset( $GLOBALS['custom_image_header'] );
			}
			break;

		case 'custom-background' :
			if ( ! did_action( 'mcms_loaded' ) )
				break;
			$support = get_myskin_support( 'custom-background' );
			remove_action( 'mcms_head', $support[0]['mcms-head-callback'] );
			remove_action( 'admin_menu', array( $GLOBALS['custom_background'], 'init' ) );
			unset( $GLOBALS['custom_background'] );
			break;
	}

	unset( $_mcms_myskin_features[ $feature ] );
	return true;
}

/**
 * Checks a myskin's support for a given feature
 *
 * @since 2.9.0
 *
 * @global array $_mcms_myskin_features
 *
 * @param string $feature the feature being checked
 * @return bool
 */
function current_myskin_supports( $feature ) {
	global $_mcms_myskin_features;

	if ( 'custom-header-uploads' == $feature )
		return current_myskin_supports( 'custom-header', 'uploads' );

	if ( !isset( $_mcms_myskin_features[$feature] ) )
		return false;

	// If no args passed then no extra checks need be performed
	if ( func_num_args() <= 1 )
		return true;

	$args = array_slice( func_get_args(), 1 );

	switch ( $feature ) {
		case 'post-thumbnails':
			// post-thumbnails can be registered for only certain content/post types by passing
			// an array of types to add_myskin_support(). If no array was passed, then
			// any type is accepted
			if ( true === $_mcms_myskin_features[$feature] )  // Registered for all types
				return true;
			$content_type = $args[0];
			return in_array( $content_type, $_mcms_myskin_features[$feature][0] );

		case 'html5':
		case 'post-formats':
			// specific post formats can be registered by passing an array of types to
			// add_myskin_support()

			// Specific areas of HTML5 support *must* be passed via an array to add_myskin_support()

			$type = $args[0];
			return in_array( $type, $_mcms_myskin_features[$feature][0] );

		case 'custom-logo':
		case 'custom-header':
		case 'custom-background':
			// Specific capabilities can be registered by passing an array to add_myskin_support().
			return ( isset( $_mcms_myskin_features[ $feature ][0][ $args[0] ] ) && $_mcms_myskin_features[ $feature ][0][ $args[0] ] );
	}

	/**
	 * Filters whether the current myskin supports a specific feature.
	 *
	 * The dynamic portion of the hook name, `$feature`, refers to the specific myskin
	 * feature. Possible values include 'post-formats', 'post-thumbnails', 'custom-background',
	 * 'custom-header', 'menus', 'automatic-feed-links', 'html5',
	 * 'starter-content', and 'customize-selective-refresh-widgets'.
	 *
	 * @since 3.4.0
	 *
	 * @param bool   true     Whether the current myskin supports the given feature. Default true.
	 * @param array  $args    Array of arguments for the feature.
	 * @param string $feature The myskin feature.
	 */
	return apply_filters( "current_myskin_supports-{$feature}", true, $args, $_mcms_myskin_features[$feature] );
}

/**
 * Checks a myskin's support for a given feature before loading the functions which implement it.
 *
 * @since 2.9.0
 *
 * @param string $feature The feature being checked.
 * @param string $include Path to the file.
 * @return bool True if the current myskin supports the supplied feature, false otherwise.
 */
function require_if_myskin_supports( $feature, $include ) {
	if ( current_myskin_supports( $feature ) ) {
		require ( $include );
		return true;
	}
	return false;
}

/**
 * Checks an attachment being deleted to see if it's a header or background image.
 *
 * If true it removes the myskin modification which would be pointing at the deleted
 * attachment.
 *
 * @access private
 * @since 3.0.0
 * @since 4.3.0 Also removes `header_image_data`.
 * @since 4.5.0 Also removes custom logo myskin mods.
 *
 * @param int $id The attachment id.
 */
function _delete_attachment_myskin_mod( $id ) {
	$attachment_image = mcms_get_attachment_url( $id );
	$header_image     = get_header_image();
	$background_image = get_background_image();
	$custom_logo_id   = get_myskin_mod( 'custom_logo' );

	if ( $custom_logo_id && $custom_logo_id == $id ) {
		remove_myskin_mod( 'custom_logo' );
		remove_myskin_mod( 'header_text' );
	}

	if ( $header_image && $header_image == $attachment_image ) {
		remove_myskin_mod( 'header_image' );
		remove_myskin_mod( 'header_image_data' );
	}

	if ( $background_image && $background_image == $attachment_image ) {
		remove_myskin_mod( 'background_image' );
	}
}

/**
 * Checks if a myskin has been changed and runs 'after_switch_myskin' hook on the next MCMS load.
 *
 * See {@see 'after_switch_myskin'}.
 *
 * @since 3.3.0
 */
function check_myskin_switched() {
	if ( $stylesheet = get_option( 'myskin_switched' ) ) {
		$old_myskin = mcms_get_myskin( $stylesheet );

		// Prevent widget & menu mapping from running since Customizer already called it up front
		if ( get_option( 'myskin_switched_via_customizer' ) ) {
			remove_action( 'after_switch_myskin', '_mcms_menus_changed' );
			remove_action( 'after_switch_myskin', '_mcms_sidebars_changed' );
			update_option( 'myskin_switched_via_customizer', false );
		}

		if ( $old_myskin->exists() ) {
			/**
			 * Fires on the first MCMS load after a myskin switch if the old myskin still exists.
			 *
			 * This action fires multiple times and the parameters differs
			 * according to the context, if the old myskin exists or not.
			 * If the old myskin is missing, the parameter will be the slug
			 * of the old myskin.
			 *
			 * @since 3.3.0
			 *
			 * @param string   $old_name  Old myskin name.
			 * @param MCMS_MySkin $old_myskin MCMS_MySkin instance of the old myskin.
			 */
			do_action( 'after_switch_myskin', $old_myskin->get( 'Name' ), $old_myskin );
		} else {
			/** This action is documented in mcms-roots/myskin.php */
			do_action( 'after_switch_myskin', $stylesheet, $old_myskin );
		}
		flush_rewrite_rules();

		update_option( 'myskin_switched', false );
	}
}

/**
 * Includes and instantiates the MCMS_Customize_Manager class.
 *
 * Loads the Customizer at modules_loaded when accessing the customize.php admin
 * page or when any request includes a mcms_customize=on param or a customize_changeset
 * param (a UUID). This param is a signal for whether to bootstrap the Customizer when
 * MandarinCMS is loading, especially in the Customizer preview
 * or when making Customizer Ajax requests for widgets or menus.
 *
 * @since 3.4.0
 *
 * @global MCMS_Customize_Manager $mcms_customize
 */
function _mcms_customize_include() {

	$is_customize_admin_page = ( is_admin() && 'customize.php' == basename( $_SERVER['PHP_SELF'] ) );
	$should_include = (
		$is_customize_admin_page
		||
		( isset( $_REQUEST['mcms_customize'] ) && 'on' == $_REQUEST['mcms_customize'] )
		||
		( ! empty( $_GET['customize_changeset_uuid'] ) || ! empty( $_POST['customize_changeset_uuid'] ) )
	);

	if ( ! $should_include ) {
		return;
	}

	/*
	 * Note that mcms_unslash() is not being used on the input vars because it is
	 * called before mcms_magic_quotes() gets called. Besides this fact, none of
	 * the values should contain any characters needing slashes anyway.
	 */
	$keys = array( 'changeset_uuid', 'customize_changeset_uuid', 'customize_myskin', 'myskin', 'customize_messenger_channel', 'customize_autosaved' );
	$input_vars = array_merge(
		mcms_array_slice_assoc( $_GET, $keys ),
		mcms_array_slice_assoc( $_POST, $keys )
	);

	$myskin = null;
	$changeset_uuid = false; // Value false indicates UUID should be determined after_setup_myskin to either re-use existing saved changeset or else generate a new UUID if none exists.
	$messenger_channel = null;
	$autosaved = null;
	$branching = false; // Set initially fo false since defaults to true for back-compat; can be overridden via the customize_changeset_branching filter.

	if ( $is_customize_admin_page && isset( $input_vars['changeset_uuid'] ) ) {
		$changeset_uuid = sanitize_key( $input_vars['changeset_uuid'] );
	} elseif ( ! empty( $input_vars['customize_changeset_uuid'] ) ) {
		$changeset_uuid = sanitize_key( $input_vars['customize_changeset_uuid'] );
	}

	// Note that myskin will be sanitized via MCMS_MySkin.
	if ( $is_customize_admin_page && isset( $input_vars['myskin'] ) ) {
		$myskin = $input_vars['myskin'];
	} elseif ( isset( $input_vars['customize_myskin'] ) ) {
		$myskin = $input_vars['customize_myskin'];
	}

	if ( ! empty( $input_vars['customize_autosaved'] ) ) {
		$autosaved = true;
	}

	if ( isset( $input_vars['customize_messenger_channel'] ) ) {
		$messenger_channel = sanitize_key( $input_vars['customize_messenger_channel'] );
	}

	/*
	 * Note that settings must be previewed even outside the customizer preview
	 * and also in the customizer pane itself. This is to enable loading an existing
	 * changeset into the customizer. Previewing the settings only has to be prevented
	 * here in the case of a customize_save action because this will cause MCMS to think
	 * there is nothing changed that needs to be saved.
	 */
	$is_customize_save_action = (
		mcms_doing_ajax()
		&&
		isset( $_REQUEST['action'] )
		&&
		'customize_save' === mcms_unslash( $_REQUEST['action'] )
	);
	$settings_previewed = ! $is_customize_save_action;

	require_once BASED_TREE_URI . MCMSINC . '/class-mcms-customize-manager.php';
	$GLOBALS['mcms_customize'] = new MCMS_Customize_Manager( compact( 'changeset_uuid', 'myskin', 'messenger_channel', 'settings_previewed', 'autosaved', 'branching' ) );
}

/**
 * Publishes a snapshot's changes.
 *
 * @since 4.7.0
 * @access private
 *
 * @global mcmsdb                 $mcmsdb         MandarinCMS database abstraction object.
 * @global MCMS_Customize_Manager $mcms_customize Customizer instance.
 *
 * @param string  $new_status     New post status.
 * @param string  $old_status     Old post status.
 * @param MCMS_Post $changeset_post Changeset post object.
 */
function _mcms_customize_publish_changeset( $new_status, $old_status, $changeset_post ) {
	global $mcms_customize, $mcmsdb;

	$is_publishing_changeset = (
		'customize_changeset' === $changeset_post->post_type
		&&
		'publish' === $new_status
		&&
		'publish' !== $old_status
	);
	if ( ! $is_publishing_changeset ) {
		return;
	}

	if ( empty( $mcms_customize ) ) {
		require_once BASED_TREE_URI . MCMSINC . '/class-mcms-customize-manager.php';
		$mcms_customize = new MCMS_Customize_Manager( array(
			'changeset_uuid' => $changeset_post->post_name,
			'settings_previewed' => false,
		) );
	}

	if ( ! did_action( 'customize_register' ) ) {
		/*
		 * When running from CLI or Cron, the customize_register action will need
		 * to be triggered in order for core, myskins, and modules to register their
		 * settings. Normally core will add_action( 'customize_register' ) at
		 * priority 10 to register the core settings, and if any myskins/modules
		 * also add_action( 'customize_register' ) at the same priority, they
		 * will have a $mcms_customize with those settings registered since they
		 * call add_action() afterward, normally. However, when manually doing
		 * the customize_register action after the setup_myskin, then the order
		 * will be reversed for two actions added at priority 10, resulting in
		 * the core settings no longer being available as expected to myskins/modules.
		 * So the following manually calls the method that registers the core
		 * settings up front before doing the action.
		 */
		remove_action( 'customize_register', array( $mcms_customize, 'register_controls' ) );
		$mcms_customize->register_controls();

		/** This filter is documented in /mcms-roots/class-mcms-customize-manager.php */
		do_action( 'customize_register', $mcms_customize );
	}
	$mcms_customize->_publish_changeset_values( $changeset_post->ID ) ;

	/*
	 * Trash the changeset post if revisions are not enabled. Unpublished
	 * changesets by default get garbage collected due to the auto-draft status.
	 * When a changeset post is published, however, it would no longer get cleaned
	 * out. Ths is a problem when the changeset posts are never displayed anywhere,
	 * since they would just be endlessly piling up. So here we use the revisions
	 * feature to indicate whether or not a published changeset should get trashed
	 * and thus garbage collected.
	 */
	if ( ! mcms_revisions_enabled( $changeset_post ) ) {
		$mcms_customize->trash_changeset_post( $changeset_post->ID );
	}
}

/**
 * Filters changeset post data upon insert to ensure post_name is intact.
 *
 * This is needed to prevent the post_name from being dropped when the post is
 * transitioned into pending status by a contributor.
 *
 * @since 4.7.0
 * @see mcms_insert_post()
 *
 * @param array $post_data          An array of slashed post data.
 * @param array $supplied_post_data An array of sanitized, but otherwise unmodified post data.
 * @returns array Filtered data.
 */
function _mcms_customize_changeset_filter_insert_post_data( $post_data, $supplied_post_data ) {
	if ( isset( $post_data['post_type'] ) && 'customize_changeset' === $post_data['post_type'] ) {

		// Prevent post_name from being dropped, such as when contributor saves a changeset post as pending.
		if ( empty( $post_data['post_name'] ) && ! empty( $supplied_post_data['post_name'] ) ) {
			$post_data['post_name'] = $supplied_post_data['post_name'];
		}
	}
	return $post_data;
}

/**
 * Adds settings for the customize-loader script.
 *
 * @since 3.4.0
 */
function _mcms_customize_loader_settings() {
	$admin_origin = parse_url( admin_url() );
	$home_origin  = parse_url( home_url() );
	$cross_domain = ( strtolower( $admin_origin[ 'host' ] ) != strtolower( $home_origin[ 'host' ] ) );

	$browser = array(
		'mobile' => mcms_is_mobile(),
		'ios'    => mcms_is_mobile() && preg_match( '/iPad|iPod|iPhone/', $_SERVER['HTTP_USER_AGENT'] ),
	);

	$settings = array(
		'url'           => esc_url( admin_url( 'customize.php' ) ),
		'isCrossDomain' => $cross_domain,
		'browser'       => $browser,
		'l10n'          => array(
			'saveAlert'       => __( 'The changes you made will be lost if you navigate away from this page.' ),
			'mainIframeTitle' => __( 'Customizer' ),
		),
	);

	$script = 'var _mcmsCustomizeLoaderSettings = ' . mcms_json_encode( $settings ) . ';';

	$mcms_scripts = mcms_scripts();
	$data = $mcms_scripts->get_data( 'customize-loader', 'data' );
	if ( $data )
		$script = "$data\n$script";

	$mcms_scripts->add_data( 'customize-loader', 'data', $script );
}

/**
 * Returns a URL to load the Customizer.
 *
 * @since 3.4.0
 *
 * @param string $stylesheet Optional. MySkin to customize. Defaults to current myskin.
 * 	                         The myskin's stylesheet will be urlencoded if necessary.
 * @return string
 */
function mcms_customize_url( $stylesheet = null ) {
	$url = admin_url( 'customize.php' );
	if ( $stylesheet )
		$url .= '?myskin=' . urlencode( $stylesheet );
	return esc_url( $url );
}

/**
 * Prints a script to check whether or not the Customizer is supported,
 * and apply either the no-customize-support or customize-support class
 * to the body.
 *
 * This function MUST be called inside the body tag.
 *
 * Ideally, call this function immediately after the body tag is opened.
 * This prevents a flash of unstyled content.
 *
 * It is also recommended that you add the "no-customize-support" class
 * to the body tag by default.
 *
 * @since 3.4.0
 * @since 4.7.0 Support for IE8 and below is explicitly removed via conditional comments.
 */
function mcms_customize_support_script() {
	$admin_origin = parse_url( admin_url() );
	$home_origin  = parse_url( home_url() );
	$cross_domain = ( strtolower( $admin_origin[ 'host' ] ) != strtolower( $home_origin[ 'host' ] ) );

	?>
	<!--[if lte IE 8]>
		<script type="text/javascript">
			document.body.className = document.body.className.replace( /(^|\s)(no-)?customize-support(?=\s|$)/, '' ) + ' no-customize-support';
		</script>
	<![endif]-->
	<!--[if gte IE 9]><!-->
		<script type="text/javascript">
			(function() {
				var request, b = document.body, c = 'className', cs = 'customize-support', rcs = new RegExp('(^|\\s+)(no-)?'+cs+'(\\s+|$)');

		<?php	if ( $cross_domain ) : ?>
				request = (function(){ var xhr = new XMLHttpRequest(); return ('withCredentials' in xhr); })();
		<?php	else : ?>
				request = true;
		<?php	endif; ?>

				b[c] = b[c].replace( rcs, ' ' );
				// The customizer requires postMessage and CORS (if the site is cross domain)
				b[c] += ( window.postMessage && request ? ' ' : ' no-' ) + cs;
			}());
		</script>
	<!--<![endif]-->
	<?php
}

/**
 * Whether the site is being previewed in the Customizer.
 *
 * @since 4.0.0
 *
 * @global MCMS_Customize_Manager $mcms_customize Customizer instance.
 *
 * @return bool True if the site is being previewed in the Customizer, false otherwise.
 */
function is_customize_preview() {
	global $mcms_customize;

	return ( $mcms_customize instanceof MCMS_Customize_Manager ) && $mcms_customize->is_preview();
}

/**
 * Make sure that auto-draft posts get their post_date bumped or status changed to draft to prevent premature garbage-collection.
 *
 * When a changeset is updated but remains an auto-draft, ensure the post_date
 * for the auto-draft posts remains the same so that it will be
 * garbage-collected at the same time by `mcms_delete_auto_drafts()`. Otherwise,
 * if the changeset is updated to be a draft then update the posts
 * to have a far-future post_date so that they will never be garbage collected
 * unless the changeset post itself is deleted.
 *
 * When a changeset is updated to be a persistent draft or to be scheduled for
 * publishing, then transition any dependent auto-drafts to a draft status so
 * that they likewise will not be garbage-collected but also so that they can
 * be edited in the admin before publishing since there is not yet a post/page
 * editing flow in the Customizer. See #39752.
 *
 * @link https://core.trac.mandarincms.com/ticket/39752
 *
 * @since 4.8.0
 * @access private
 * @see mcms_delete_auto_drafts()
 *
 * @param string   $new_status Transition to this post status.
 * @param string   $old_status Previous post status.
 * @param \MCMS_Post $post       Post data.
 * @global mcmsdb $mcmsdb
 */
function _mcms_keep_alive_customize_changeset_dependent_auto_drafts( $new_status, $old_status, $post ) {
	global $mcmsdb;
	unset( $old_status );

	// Short-circuit if not a changeset or if the changeset was published.
	if ( 'customize_changeset' !== $post->post_type || 'publish' === $new_status ) {
		return;
	}

	$data = json_decode( $post->post_content, true );
	if ( empty( $data['nav_menus_created_posts']['value'] ) ) {
		return;
	}

	/*
	 * Actually, in lieu of keeping alive, trash any customization drafts here if the changeset itself is
	 * getting trashed. This is needed because when a changeset transitions to a draft, then any of the
	 * dependent auto-draft post/page stubs will also get transitioned to customization drafts which
	 * are then visible in the MCMS Admin. We cannot wait for the deletion of the changeset in which
	 * _mcms_delete_customize_changeset_dependent_auto_drafts() will be called, since they need to be
	 * trashed to remove from visibility immediately.
	 */
	if ( 'trash' === $new_status ) {
		foreach ( $data['nav_menus_created_posts']['value'] as $post_id ) {
			if ( ! empty( $post_id ) && 'draft' === get_post_status( $post_id ) ) {
				mcms_trash_post( $post_id );
			}
		}
		return;
	}

	$post_args = array();
	if ( 'auto-draft' === $new_status ) {
		/*
		 * Keep the post date for the post matching the changeset
		 * so that it will not be garbage-collected before the changeset.
		 */
		$post_args['post_date'] = $post->post_date; // Note mcms_delete_auto_drafts() only looks at this date.
	} else {
		/*
		 * Since the changeset no longer has an auto-draft (and it is not published)
		 * it is now a persistent changeset, a long-lived draft, and so any
		 * associated auto-draft posts should likewise transition into having a draft
		 * status. These drafts will be treated differently than regular drafts in
		 * that they will be tied to the given changeset. The publish metabox is
		 * replaced with a notice about how the post is part of a set of customized changes
		 * which will be published when the changeset is published.
		 */
		$post_args['post_status'] = 'draft';
	}

	foreach ( $data['nav_menus_created_posts']['value'] as $post_id ) {
		if ( empty( $post_id ) || 'auto-draft' !== get_post_status( $post_id ) ) {
			continue;
		}
		$mcmsdb->update(
			$mcmsdb->posts,
			$post_args,
			array( 'ID' => $post_id )
		);
		clean_post_cache( $post_id );
	}
}
