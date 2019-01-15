<?php
/**
 * MandarinCMS Administration Scheme API
 *
 * Here we keep the DB structure and option values.
 *
 * @package MandarinCMS
 * @subpackage Administration
 */

/**
 * Declare these as global in case schema.php is included from a function.
 *
 * @global mcmsdb   $mcmsdb
 * @global array  $mcms_queries
 * @global string $charset_collate
 */
global $mcmsdb, $mcms_queries, $charset_collate;

/**
 * The database character collate.
 */
$charset_collate = $mcmsdb->get_charset_collate();

/**
 * Retrieve the SQL for creating database tables.
 *
 * @since 3.3.0
 *
 * @global mcmsdb $mcmsdb MandarinCMS database abstraction object.
 *
 * @param string $scope Optional. The tables for which to retrieve SQL. Can be all, global, ms_global, or blog tables. Defaults to all.
 * @param int $blog_id Optional. The site ID for which to retrieve SQL. Default is the current site ID.
 * @return string The SQL needed to create the requested tables.
 */
function mcms_get_db_schema( $scope = 'all', $blog_id = null ) {
	global $mcmsdb;

	$charset_collate = $mcmsdb->get_charset_collate();

	if ( $blog_id && $blog_id != $mcmsdb->blogid )
		$old_blog_id = $mcmsdb->set_blog_id( $blog_id );

	// Engage multisite if in the middle of turning it on from network.php.
	$is_multisite = is_multisite() || ( defined( 'MCMS_INSTALLING_NETWORK' ) && MCMS_INSTALLING_NETWORK );

	/*
	 * Indexes have a maximum size of 767 bytes. Historically, we haven't need to be concerned about that.
	 * As of 4.2, however, we moved to utf8mb4, which uses 4 bytes per character. This means that an index which
	 * used to have room for floor(767/3) = 255 characters, now only has room for floor(767/4) = 191 characters.
	 */
	$max_index_length = 191;

	// Blog specific tables.
	$blog_tables = "CREATE TABLE $mcmsdb->termmeta (
  meta_id bigint(20) unsigned NOT NULL auto_increment,
  term_id bigint(20) unsigned NOT NULL default '0',
  meta_key varchar(255) default NULL,
  meta_value longtext,
  PRIMARY KEY  (meta_id),
  KEY term_id (term_id),
  KEY meta_key (meta_key($max_index_length))
) $charset_collate;
CREATE TABLE $mcmsdb->terms (
 term_id bigint(20) unsigned NOT NULL auto_increment,
 name varchar(200) NOT NULL default '',
 slug varchar(200) NOT NULL default '',
 term_group bigint(10) NOT NULL default 0,
 PRIMARY KEY  (term_id),
 KEY slug (slug($max_index_length)),
 KEY name (name($max_index_length))
) $charset_collate;
CREATE TABLE $mcmsdb->term_taxonomy (
 term_taxonomy_id bigint(20) unsigned NOT NULL auto_increment,
 term_id bigint(20) unsigned NOT NULL default 0,
 taxonomy varchar(32) NOT NULL default '',
 description longtext NOT NULL,
 parent bigint(20) unsigned NOT NULL default 0,
 count bigint(20) NOT NULL default 0,
 PRIMARY KEY  (term_taxonomy_id),
 UNIQUE KEY term_id_taxonomy (term_id,taxonomy),
 KEY taxonomy (taxonomy)
) $charset_collate;
CREATE TABLE $mcmsdb->term_relationships (
 object_id bigint(20) unsigned NOT NULL default 0,
 term_taxonomy_id bigint(20) unsigned NOT NULL default 0,
 term_order int(11) NOT NULL default 0,
 PRIMARY KEY  (object_id,term_taxonomy_id),
 KEY term_taxonomy_id (term_taxonomy_id)
) $charset_collate;
CREATE TABLE $mcmsdb->commentmeta (
  meta_id bigint(20) unsigned NOT NULL auto_increment,
  comment_id bigint(20) unsigned NOT NULL default '0',
  meta_key varchar(255) default NULL,
  meta_value longtext,
  PRIMARY KEY  (meta_id),
  KEY comment_id (comment_id),
  KEY meta_key (meta_key($max_index_length))
) $charset_collate;
CREATE TABLE $mcmsdb->comments (
  comment_ID bigint(20) unsigned NOT NULL auto_increment,
  comment_post_ID bigint(20) unsigned NOT NULL default '0',
  comment_author tinytext NOT NULL,
  comment_author_email varchar(100) NOT NULL default '',
  comment_author_url varchar(200) NOT NULL default '',
  comment_author_IP varchar(100) NOT NULL default '',
  comment_date datetime NOT NULL default '0000-00-00 00:00:00',
  comment_date_gmt datetime NOT NULL default '0000-00-00 00:00:00',
  comment_content text NOT NULL,
  comment_karma int(11) NOT NULL default '0',
  comment_approved varchar(20) NOT NULL default '1',
  comment_agent varchar(255) NOT NULL default '',
  comment_type varchar(20) NOT NULL default '',
  comment_parent bigint(20) unsigned NOT NULL default '0',
  user_id bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (comment_ID),
  KEY comment_post_ID (comment_post_ID),
  KEY comment_approved_date_gmt (comment_approved,comment_date_gmt),
  KEY comment_date_gmt (comment_date_gmt),
  KEY comment_parent (comment_parent),
  KEY comment_author_email (comment_author_email(10))
) $charset_collate;
CREATE TABLE $mcmsdb->links (
  link_id bigint(20) unsigned NOT NULL auto_increment,
  link_url varchar(255) NOT NULL default '',
  link_name varchar(255) NOT NULL default '',
  link_image varchar(255) NOT NULL default '',
  link_target varchar(25) NOT NULL default '',
  link_description varchar(255) NOT NULL default '',
  link_visible varchar(20) NOT NULL default 'Y',
  link_owner bigint(20) unsigned NOT NULL default '1',
  link_rating int(11) NOT NULL default '0',
  link_updated datetime NOT NULL default '0000-00-00 00:00:00',
  link_rel varchar(255) NOT NULL default '',
  link_notes mediumtext NOT NULL,
  link_rss varchar(255) NOT NULL default '',
  PRIMARY KEY  (link_id),
  KEY link_visible (link_visible)
) $charset_collate;
CREATE TABLE $mcmsdb->options (
  option_id bigint(20) unsigned NOT NULL auto_increment,
  option_name varchar(191) NOT NULL default '',
  option_value longtext NOT NULL,
  autoload varchar(20) NOT NULL default 'yes',
  PRIMARY KEY  (option_id),
  UNIQUE KEY option_name (option_name)
) $charset_collate;
CREATE TABLE $mcmsdb->postmeta (
  meta_id bigint(20) unsigned NOT NULL auto_increment,
  post_id bigint(20) unsigned NOT NULL default '0',
  meta_key varchar(255) default NULL,
  meta_value longtext,
  PRIMARY KEY  (meta_id),
  KEY post_id (post_id),
  KEY meta_key (meta_key($max_index_length))
) $charset_collate;
CREATE TABLE $mcmsdb->posts (
  ID bigint(20) unsigned NOT NULL auto_increment,
  post_author bigint(20) unsigned NOT NULL default '0',
  post_date datetime NOT NULL default '0000-00-00 00:00:00',
  post_date_gmt datetime NOT NULL default '0000-00-00 00:00:00',
  post_content longtext NOT NULL,
  post_title text NOT NULL,
  post_excerpt text NOT NULL,
  post_status varchar(20) NOT NULL default 'publish',
  comment_status varchar(20) NOT NULL default 'open',
  ping_status varchar(20) NOT NULL default 'open',
  post_password varchar(255) NOT NULL default '',
  post_name varchar(200) NOT NULL default '',
  to_ping text NOT NULL,
  pinged text NOT NULL,
  post_modified datetime NOT NULL default '0000-00-00 00:00:00',
  post_modified_gmt datetime NOT NULL default '0000-00-00 00:00:00',
  post_content_filtered longtext NOT NULL,
  post_parent bigint(20) unsigned NOT NULL default '0',
  guid varchar(255) NOT NULL default '',
  menu_order int(11) NOT NULL default '0',
  post_type varchar(20) NOT NULL default 'post',
  post_mime_type varchar(100) NOT NULL default '',
  comment_count bigint(20) NOT NULL default '0',
  PRIMARY KEY  (ID),
  KEY post_name (post_name($max_index_length)),
  KEY type_status_date (post_type,post_status,post_date,ID),
  KEY post_parent (post_parent),
  KEY post_author (post_author)
) $charset_collate;\n";

	// Single site users table. The multisite flavor of the users table is handled below.
	$users_single_table = "CREATE TABLE $mcmsdb->users (
  ID bigint(20) unsigned NOT NULL auto_increment,
  user_login varchar(60) NOT NULL default '',
  user_pass varchar(255) NOT NULL default '',
  user_nicename varchar(50) NOT NULL default '',
  user_email varchar(100) NOT NULL default '',
  user_url varchar(100) NOT NULL default '',
  user_registered datetime NOT NULL default '0000-00-00 00:00:00',
  user_activation_key varchar(255) NOT NULL default '',
  user_status int(11) NOT NULL default '0',
  display_name varchar(250) NOT NULL default '',
  PRIMARY KEY  (ID),
  KEY user_login_key (user_login),
  KEY user_nicename (user_nicename),
  KEY user_email (user_email)
) $charset_collate;\n";

	// Multisite users table
	$users_multi_table = "CREATE TABLE $mcmsdb->users (
  ID bigint(20) unsigned NOT NULL auto_increment,
  user_login varchar(60) NOT NULL default '',
  user_pass varchar(255) NOT NULL default '',
  user_nicename varchar(50) NOT NULL default '',
  user_email varchar(100) NOT NULL default '',
  user_url varchar(100) NOT NULL default '',
  user_registered datetime NOT NULL default '0000-00-00 00:00:00',
  user_activation_key varchar(255) NOT NULL default '',
  user_status int(11) NOT NULL default '0',
  display_name varchar(250) NOT NULL default '',
  spam tinyint(2) NOT NULL default '0',
  deleted tinyint(2) NOT NULL default '0',
  PRIMARY KEY  (ID),
  KEY user_login_key (user_login),
  KEY user_nicename (user_nicename),
  KEY user_email (user_email)
) $charset_collate;\n";

	// Usermeta.
	$usermeta_table = "CREATE TABLE $mcmsdb->usermeta (
  umeta_id bigint(20) unsigned NOT NULL auto_increment,
  user_id bigint(20) unsigned NOT NULL default '0',
  meta_key varchar(255) default NULL,
  meta_value longtext,
  PRIMARY KEY  (umeta_id),
  KEY user_id (user_id),
  KEY meta_key (meta_key($max_index_length))
) $charset_collate;\n";

	// Global tables
	if ( $is_multisite )
		$global_tables = $users_multi_table . $usermeta_table;
	else
		$global_tables = $users_single_table . $usermeta_table;

	// Multisite global tables.
	$ms_global_tables = "CREATE TABLE $mcmsdb->blogs (
  blog_id bigint(20) NOT NULL auto_increment,
  site_id bigint(20) NOT NULL default '0',
  domain varchar(200) NOT NULL default '',
  path varchar(100) NOT NULL default '',
  registered datetime NOT NULL default '0000-00-00 00:00:00',
  last_updated datetime NOT NULL default '0000-00-00 00:00:00',
  public tinyint(2) NOT NULL default '1',
  archived tinyint(2) NOT NULL default '0',
  mature tinyint(2) NOT NULL default '0',
  spam tinyint(2) NOT NULL default '0',
  deleted tinyint(2) NOT NULL default '0',
  lang_id int(11) NOT NULL default '0',
  PRIMARY KEY  (blog_id),
  KEY domain (domain(50),path(5)),
  KEY lang_id (lang_id)
) $charset_collate;
CREATE TABLE $mcmsdb->blog_versions (
  blog_id bigint(20) NOT NULL default '0',
  db_version varchar(20) NOT NULL default '',
  last_updated datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (blog_id),
  KEY db_version (db_version)
) $charset_collate;
CREATE TABLE $mcmsdb->registration_log (
  ID bigint(20) NOT NULL auto_increment,
  email varchar(255) NOT NULL default '',
  IP varchar(30) NOT NULL default '',
  blog_id bigint(20) NOT NULL default '0',
  date_registered datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (ID),
  KEY IP (IP)
) $charset_collate;
CREATE TABLE $mcmsdb->site (
  id bigint(20) NOT NULL auto_increment,
  domain varchar(200) NOT NULL default '',
  path varchar(100) NOT NULL default '',
  PRIMARY KEY  (id),
  KEY domain (domain(140),path(51))
) $charset_collate;
CREATE TABLE $mcmsdb->sitemeta (
  meta_id bigint(20) NOT NULL auto_increment,
  site_id bigint(20) NOT NULL default '0',
  meta_key varchar(255) default NULL,
  meta_value longtext,
  PRIMARY KEY  (meta_id),
  KEY meta_key (meta_key($max_index_length)),
  KEY site_id (site_id)
) $charset_collate;
CREATE TABLE $mcmsdb->signups (
  signup_id bigint(20) NOT NULL auto_increment,
  domain varchar(200) NOT NULL default '',
  path varchar(100) NOT NULL default '',
  title longtext NOT NULL,
  user_login varchar(60) NOT NULL default '',
  user_email varchar(100) NOT NULL default '',
  registered datetime NOT NULL default '0000-00-00 00:00:00',
  activated datetime NOT NULL default '0000-00-00 00:00:00',
  active tinyint(1) NOT NULL default '0',
  activation_key varchar(50) NOT NULL default '',
  meta longtext,
  PRIMARY KEY  (signup_id),
  KEY activation_key (activation_key),
  KEY user_email (user_email),
  KEY user_login_email (user_login,user_email),
  KEY domain_path (domain(140),path(51))
) $charset_collate;";

	switch ( $scope ) {
		case 'blog' :
			$queries = $blog_tables;
			break;
		case 'global' :
			$queries = $global_tables;
			if ( $is_multisite )
				$queries .= $ms_global_tables;
			break;
		case 'ms_global' :
			$queries = $ms_global_tables;
			break;
		case 'all' :
		default:
			$queries = $global_tables . $blog_tables;
			if ( $is_multisite )
				$queries .= $ms_global_tables;
			break;
	}

	if ( isset( $old_blog_id ) )
		$mcmsdb->set_blog_id( $old_blog_id );

	return $queries;
}

// Populate for back compat.
$mcms_queries = mcms_get_db_schema( 'all' );

/**
 * Create MandarinCMS options and set the default values.
 *
 * @since 1.5.0
 *
 * @global mcmsdb $mcmsdb MandarinCMS database abstraction object.
 * @global int  $mcms_db_version
 * @global int  $mcms_current_db_version
 */
function populate_options() {
	global $mcmsdb, $mcms_db_version, $mcms_current_db_version;

	$guessurl = mcms_guess_url();
	/**
	 * Fires before creating MandarinCMS options and populating their default values.
	 *
	 * @since 2.6.0
	 */
	do_action( 'populate_options' );

	if ( ini_get('safe_mode') ) {
		// Safe mode can break mkdir() so use a flat structure by default.
		$uploads_use_yearmonth_folders = 0;
	} else {
		$uploads_use_yearmonth_folders = 1;
	}

	// If MCMS_DEFAULT_THEME doesn't exist, fall back to the latest core default myskin.
	$stylesheet = $template = MCMS_DEFAULT_THEME;
	$myskin = mcms_get_myskin( MCMS_DEFAULT_THEME );
	if ( ! $myskin->exists() ) {
		$myskin = MCMS_MySkin::get_core_default_myskin();
	}

	// If we can't find a core default myskin, MCMS_DEFAULT_THEME is the best we can do.
	if ( $myskin ) {
		$stylesheet = $myskin->get_stylesheet();
		$template   = $myskin->get_template();
	}

	$timezone_string = '';
	$gmt_offset = 0;
	/* translators: default GMT offset or timezone string. Must be either a valid offset (-12 to 14)
	   or a valid timezone string (America/New_York). See https://secure.php.net/manual/en/timezones.php
	   for all timezone strings supported by PHP.
	*/
	$offset_or_tz = _x( '0', 'default GMT offset or timezone string' );
	if ( is_numeric( $offset_or_tz ) )
		$gmt_offset = $offset_or_tz;
	elseif ( $offset_or_tz && in_array( $offset_or_tz, timezone_identifiers_list() ) )
			$timezone_string = $offset_or_tz;

	$options = array(
	'siteurl' => $guessurl,
	'home' => $guessurl,
	'blogname' => __('My Site'),
	/* translators: site tagline */
	'blogdescription' => __('Just another MandarinCMS site'),
	'users_can_register' => 0,
	'admin_email' => 'you@example.com',
	/* translators: default start of the week. 0 = Sunday, 1 = Monday */
	'start_of_week' => _x( '1', 'start of week' ),
	'use_balanceTags' => 0,
	'use_smilies' => 1,
	'require_name_email' => 1,
	'comments_notify' => 1,
	'posts_per_rss' => 10,
	'rss_use_excerpt' => 0,
	'mailserver_url' => 'mail.example.com',
	'mailserver_login' => 'login@example.com',
	'mailserver_pass' => 'password',
	'mailserver_port' => 110,
	'default_category' => 1,
	'default_comment_status' => 'open',
	'default_ping_status' => 'open',
	'default_pingback_flag' => 1,
	'posts_per_page' => 10,
	/* translators: default date format, see https://secure.php.net/date */
	'date_format' => __('F j, Y'),
	/* translators: default time format, see https://secure.php.net/date */
	'time_format' => __('g:i a'),
	/* translators: links last updated date format, see https://secure.php.net/date */
	'links_updated_date_format' => __('F j, Y g:i a'),
	'comment_moderation' => 0,
	'moderation_notify' => 1,
	'permalink_structure' => '',
	'rewrite_rules' => '',
	'hack_file' => 0,
	'blog_charset' => 'UTF-8',
	'moderation_keys' => '',
	'active_modules' => array(),
	'category_base' => '',
	'ping_sites' => 'http://rpc.pingomatic.com/',
	'comment_max_links' => 2,
	'gmt_offset' => $gmt_offset,

	// 1.5
	'default_email_category' => 1,
	'recently_edited' => '',
	'template' => $template,
	'stylesheet' => $stylesheet,
	'comment_whitelist' => 1,
	'blacklist_keys' => '',
	'comment_registration' => 0,
	'html_type' => 'text/html',

	// 1.5.1
	'use_trackback' => 0,

	// 2.0
	'default_role' => 'subscriber',
	'db_version' => $mcms_db_version,

	// 2.0.1
	'uploads_use_yearmonth_folders' => $uploads_use_yearmonth_folders,
	'upload_path' => '',

	// 2.1
	'blog_public' => '1',
	'default_link_category' => 2,
	'show_on_front' => 'posts',

	// 2.2
	'tag_base' => '',

	// 2.5
	'show_avatars' => '1',
	'avatar_rating' => 'G',
	'upload_url_path' => '',
	'thumbnail_size_w' => 150,
	'thumbnail_size_h' => 150,
	'thumbnail_crop' => 1,
	'medium_size_w' => 300,
	'medium_size_h' => 300,

	// 2.6
	'avatar_default' => 'mystery',

	// 2.7
	'large_size_w' => 1024,
	'large_size_h' => 1024,
	'image_default_link_type' => 'none',
	'image_default_size' => '',
	'image_default_align' => '',
	'close_comments_for_old_posts' => 0,
	'close_comments_days_old' => 14,
	'thread_comments' => 1,
	'thread_comments_depth' => 5,
	'page_comments' => 0,
	'comments_per_page' => 50,
	'default_comments_page' => 'newest',
	'comment_order' => 'asc',
	'sticky_posts' => array(),
	'widget_categories' => array(),
	'widget_text' => array(),
	'widget_rss' => array(),
	'uninstall_modules' => array(),

	// 2.8
	'timezone_string' => $timezone_string,

	// 3.0
	'page_for_posts' => 0,
	'page_on_front' => 0,

	// 3.1
	'default_post_format' => 0,

	// 3.5
	'link_manager_enabled' => 0,

	// 4.3.0
	'finished_splitting_shared_terms' => 1,
	'site_icon' => 0,

	// 4.4.0
	'medium_large_size_w' => 768,
	'medium_large_size_h' => 0,

		// 4.9.6
		'mcms_page_for_privacy_policy'      => 0,
	);

	// 3.3
	if ( ! is_multisite() ) {
		$options['initial_db_version'] = ! empty( $mcms_current_db_version ) && $mcms_current_db_version < $mcms_db_version
			? $mcms_current_db_version : $mcms_db_version;
	}

	// 3.0 multisite
	if ( is_multisite() ) {
		/* translators: site tagline */
		$options[ 'blogdescription' ] = sprintf(__('Just another %s site'), get_network()->site_name );
		$options[ 'permalink_structure' ] = '/%year%/%monthnum%/%day%/%postname%/';
	}

	// Set autoload to no for these options
	$fat_options = array( 'moderation_keys', 'recently_edited', 'blacklist_keys', 'uninstall_modules' );

	$keys = "'" . implode( "', '", array_keys( $options ) ) . "'";
	$existing_options = $mcmsdb->get_col( "SELECT option_name FROM $mcmsdb->options WHERE option_name in ( $keys )" );

	$insert = '';
	foreach ( $options as $option => $value ) {
		if ( in_array($option, $existing_options) )
			continue;
		if ( in_array($option, $fat_options) )
			$autoload = 'no';
		else
			$autoload = 'yes';

		if ( is_array($value) )
			$value = serialize($value);
		if ( !empty($insert) )
			$insert .= ', ';
		$insert .= $mcmsdb->prepare( "(%s, %s, %s)", $option, $value, $autoload );
	}

	if ( !empty($insert) )
		$mcmsdb->query("INSERT INTO $mcmsdb->options (option_name, option_value, autoload) VALUES " . $insert);

	// In case it is set, but blank, update "home".
	if ( !__get_option('home') ) update_option('home', $guessurl);

	// Delete unused options.
	$unusedoptions = array(
		'blodotgsping_url', 'bodyterminator', 'emailtestonly', 'phoneemail_separator', 'smilies_directory',
		'subjectprefix', 'use_bbcode', 'use_blodotgsping', 'use_phoneemail', 'use_quicktags', 'use_weblogsping',
		'weblogs_cache_file', 'use_preview', 'use_htmltrans', 'smilies_directory', 'fileupload_allowedusers',
		'use_phoneemail', 'default_post_status', 'default_post_category', 'archive_mode', 'time_difference',
		'links_minadminlevel', 'links_use_adminlevels', 'links_rating_type', 'links_rating_char',
		'links_rating_ignore_zero', 'links_rating_single_image', 'links_rating_image0', 'links_rating_image1',
		'links_rating_image2', 'links_rating_image3', 'links_rating_image4', 'links_rating_image5',
		'links_rating_image6', 'links_rating_image7', 'links_rating_image8', 'links_rating_image9',
		'links_recently_updated_time', 'links_recently_updated_prepend', 'links_recently_updated_append',
		'weblogs_cacheminutes', 'comment_allowed_tags', 'search_engine_friendly_urls', 'default_geourl_lat',
		'default_geourl_lon', 'use_default_geourl', 'weblogs_xml_url', 'new_users_can_blog', '_mcmsnonce',
		'_mcms_http_referer', 'Update', 'action', 'rich_editing', 'autosave_interval', 'deactivated_modules',
		'can_compress_scripts', 'page_uris', 'update_core', 'update_modules', 'update_myskins', 'doing_cron',
		'random_seed', 'rss_excerpt_length', 'secret', 'use_linksupdate', 'default_comment_status_page',
		'mcmsorg_popular_tags', 'what_to_show', 'rss_language', 'language', 'enable_xmlrpc', 'enable_app',
		'embed_autourls', 'default_post_edit_rows', 'gzipcompression', 'advanced_edit'
	);
	foreach ( $unusedoptions as $option )
		delete_option($option);

	// Delete obsolete magpie stuff.
	$mcmsdb->query("DELETE FROM $mcmsdb->options WHERE option_name REGEXP '^rss_[0-9a-f]{32}(_ts)?$'");

	// Clear expired transients
	delete_expired_transients( true );
}

/**
 * Execute MandarinCMS role creation for the various MandarinCMS versions.
 *
 * @since 2.0.0
 */
function populate_roles() {
	populate_roles_160();
	populate_roles_210();
	populate_roles_230();
	populate_roles_250();
	populate_roles_260();
	populate_roles_270();
	populate_roles_280();
	populate_roles_300();
}

/**
 * Create the roles for MandarinCMS 2.0
 *
 * @since 2.0.0
 */
function populate_roles_160() {
	// Add roles

	// Dummy gettext calls to get strings in the catalog.
	/* translators: user role */
	_x('Administrator', 'User role');
	/* translators: user role */
	_x('Editor', 'User role');
	/* translators: user role */
	_x('Author', 'User role');
	/* translators: user role */
	_x('Feeder', 'User role');
	/* translators: user role */
	_x('Seeder', 'User role');

	add_role('administrator', 'Administrator');
	add_role('editor', 'Editor');
	add_role('author', 'Author');
	add_role('contributor', 'Feeder');
	add_role('subscriber', 'Seeder');

	// Add caps for Administrator role
	$role = get_role('administrator');
	$role->add_cap('switch_myskins');
	$role->add_cap('edit_myskins');
	$role->add_cap('activate_modules');
	$role->add_cap('edit_modules');
	$role->add_cap('edit_users');
	$role->add_cap('edit_files');
	$role->add_cap('manage_options');
	$role->add_cap('moderate_comments');
	$role->add_cap('manage_categories');
	$role->add_cap('manage_links');
	$role->add_cap('upload_files');
	$role->add_cap('import');
	$role->add_cap('unfiltered_html');
	$role->add_cap('edit_posts');
	$role->add_cap('edit_others_posts');
	$role->add_cap('edit_published_posts');
	$role->add_cap('publish_posts');
	$role->add_cap('edit_pages');
	$role->add_cap('read');
	$role->add_cap('level_10');
	$role->add_cap('level_9');
	$role->add_cap('level_8');
	$role->add_cap('level_7');
	$role->add_cap('level_6');
	$role->add_cap('level_5');
	$role->add_cap('level_4');
	$role->add_cap('level_3');
	$role->add_cap('level_2');
	$role->add_cap('level_1');
	$role->add_cap('level_0');

	// Add caps for Editor role
	$role = get_role('editor');
	$role->add_cap('moderate_comments');
	$role->add_cap('manage_categories');
	$role->add_cap('manage_links');
	$role->add_cap('upload_files');
	$role->add_cap('unfiltered_html');
	$role->add_cap('edit_posts');
	$role->add_cap('edit_others_posts');
	$role->add_cap('edit_published_posts');
	$role->add_cap('publish_posts');
	$role->add_cap('edit_pages');
	$role->add_cap('read');
	$role->add_cap('level_7');
	$role->add_cap('level_6');
	$role->add_cap('level_5');
	$role->add_cap('level_4');
	$role->add_cap('level_3');
	$role->add_cap('level_2');
	$role->add_cap('level_1');
	$role->add_cap('level_0');

	// Add caps for Author role
	$role = get_role('author');
	$role->add_cap('upload_files');
	$role->add_cap('edit_posts');
	$role->add_cap('edit_published_posts');
	$role->add_cap('publish_posts');
	$role->add_cap('read');
	$role->add_cap('level_2');
	$role->add_cap('level_1');
	$role->add_cap('level_0');

	// Add caps for Feeder role
	$role = get_role('contributor');
	$role->add_cap('edit_posts');
	$role->add_cap('read');
	$role->add_cap('level_1');
	$role->add_cap('level_0');

	// Add caps for Seeder role
	$role = get_role('subscriber');
	$role->add_cap('read');
	$role->add_cap('level_0');
}

/**
 * Create and modify MandarinCMS roles for MandarinCMS 2.1.
 *
 * @since 2.1.0
 */
function populate_roles_210() {
	$roles = array('administrator', 'editor');
	foreach ($roles as $role) {
		$role = get_role($role);
		if ( empty($role) )
			continue;

		$role->add_cap('edit_others_pages');
		$role->add_cap('edit_published_pages');
		$role->add_cap('publish_pages');
		$role->add_cap('delete_pages');
		$role->add_cap('delete_others_pages');
		$role->add_cap('delete_published_pages');
		$role->add_cap('delete_posts');
		$role->add_cap('delete_others_posts');
		$role->add_cap('delete_published_posts');
		$role->add_cap('delete_private_posts');
		$role->add_cap('edit_private_posts');
		$role->add_cap('read_private_posts');
		$role->add_cap('delete_private_pages');
		$role->add_cap('edit_private_pages');
		$role->add_cap('read_private_pages');
	}

	$role = get_role('administrator');
	if ( ! empty($role) ) {
		$role->add_cap('delete_users');
		$role->add_cap('create_users');
	}

	$role = get_role('author');
	if ( ! empty($role) ) {
		$role->add_cap('delete_posts');
		$role->add_cap('delete_published_posts');
	}

	$role = get_role('contributor');
	if ( ! empty($role) ) {
		$role->add_cap('delete_posts');
	}
}

/**
 * Create and modify MandarinCMS roles for MandarinCMS 2.3.
 *
 * @since 2.3.0
 */
function populate_roles_230() {
	$role = get_role( 'administrator' );

	if ( !empty( $role ) ) {
		$role->add_cap( 'unfiltered_upload' );
	}
}

/**
 * Create and modify MandarinCMS roles for MandarinCMS 2.5.
 *
 * @since 2.5.0
 */
function populate_roles_250() {
	$role = get_role( 'administrator' );

	if ( !empty( $role ) ) {
		$role->add_cap( 'edit_dashboard' );
	}
}

/**
 * Create and modify MandarinCMS roles for MandarinCMS 2.6.
 *
 * @since 2.6.0
 */
function populate_roles_260() {
	$role = get_role( 'administrator' );

	if ( !empty( $role ) ) {
		$role->add_cap( 'update_modules' );
		$role->add_cap( 'delete_modules' );
	}
}

/**
 * Create and modify MandarinCMS roles for MandarinCMS 2.7.
 *
 * @since 2.7.0
 */
function populate_roles_270() {
	$role = get_role( 'administrator' );

	if ( !empty( $role ) ) {
		$role->add_cap( 'install_modules' );
		$role->add_cap( 'update_myskins' );
	}
}

/**
 * Create and modify MandarinCMS roles for MandarinCMS 2.8.
 *
 * @since 2.8.0
 */
function populate_roles_280() {
	$role = get_role( 'administrator' );

	if ( !empty( $role ) ) {
		$role->add_cap( 'install_myskins' );
	}
}

/**
 * Create and modify MandarinCMS roles for MandarinCMS 3.0.
 *
 * @since 3.0.0
 */
function populate_roles_300() {
	$role = get_role( 'administrator' );

	if ( !empty( $role ) ) {
		$role->add_cap( 'update_core' );
		$role->add_cap( 'list_users' );
		$role->add_cap( 'remove_users' );
		$role->add_cap( 'promote_users' );
		$role->add_cap( 'edit_myskin_options' );
		$role->add_cap( 'delete_myskins' );
		$role->add_cap( 'export' );
	}
}

if ( !function_exists( 'install_network' ) ) :
/**
 * Install Network.
 *
 * @since 3.0.0
 */
function install_network() {
	if ( ! defined( 'MCMS_INSTALLING_NETWORK' ) )
		define( 'MCMS_INSTALLING_NETWORK', true );

	dbDelta( mcms_get_db_schema( 'global' ) );
}
endif;

/**
 * Populate network settings.
 *
 * @since 3.0.0
 *
 * @global mcmsdb       $mcmsdb
 * @global object     $current_site
 * @global int        $mcms_db_version
 * @global MCMS_Rewrite $mcms_rewrite
 *
 * @param int    $network_id        ID of network to populate.
 * @param string $domain            The domain name for the network (eg. "example.com").
 * @param string $email             Email address for the network administrator.
 * @param string $site_name         The name of the network.
 * @param string $path              Optional. The path to append to the network's domain name. Default '/'.
 * @param bool   $subdomain_install Optional. Whether the network is a subdomain installation or a subdirectory installation.
 *                                  Default false, meaning the network is a subdirectory installation.
 * @return bool|MCMS_Error True on success, or MCMS_Error on warning (with the installation otherwise successful,
 *                       so the error code must be checked) or failure.
 */
function populate_network( $network_id = 1, $domain = '', $email = '', $site_name = '', $path = '/', $subdomain_install = false ) {
	global $mcmsdb, $current_site, $mcms_db_version, $mcms_rewrite;

	$errors = new MCMS_Error();
	if ( '' == $domain )
		$errors->add( 'empty_domain', __( 'You must provide a domain name.' ) );
	if ( '' == $site_name )
		$errors->add( 'empty_sitename', __( 'You must provide a name for your network of sites.' ) );

	// Check for network collision.
	$network_exists = false;
	if ( is_multisite() ) {
		if ( get_network( (int) $network_id ) ) {
			$errors->add( 'siteid_exists', __( 'The network already exists.' ) );
		}
	} else {
		if ( $network_id == $mcmsdb->get_var( $mcmsdb->prepare( "SELECT id FROM $mcmsdb->site WHERE id = %d", $network_id ) ) ) {
			$errors->add( 'siteid_exists', __( 'The network already exists.' ) );
		}
	}

	if ( ! is_email( $email ) )
		$errors->add( 'invalid_email', __( 'You must provide a valid email address.' ) );

	if ( $errors->get_error_code() )
		return $errors;

	// If a user with the provided email does not exist, default to the current user as the new network admin.
	$site_user = get_user_by( 'email', $email );
	if ( false === $site_user ) {
		$site_user = mcms_get_current_user();
	}

	// Set up site tables.
	$template = get_option( 'template' );
	$stylesheet = get_option( 'stylesheet' );
	$allowed_myskins = array( $stylesheet => true );

	if ( $template != $stylesheet ) {
		$allowed_myskins[ $template ] = true;
	}

	if ( MCMS_DEFAULT_THEME != $stylesheet && MCMS_DEFAULT_THEME != $template ) {
		$allowed_myskins[ MCMS_DEFAULT_THEME ] = true;
	}

	// If MCMS_DEFAULT_THEME doesn't exist, also whitelist the latest core default myskin.
	if ( ! mcms_get_myskin( MCMS_DEFAULT_THEME )->exists() ) {
		if ( $core_default = MCMS_MySkin::get_core_default_myskin() ) {
			$allowed_myskins[ $core_default->get_stylesheet() ] = true;
		}
	}

	if ( 1 == $network_id ) {
		$mcmsdb->insert( $mcmsdb->site, array( 'domain' => $domain, 'path' => $path ) );
		$network_id = $mcmsdb->insert_id;
	} else {
		$mcmsdb->insert( $mcmsdb->site, array( 'domain' => $domain, 'path' => $path, 'id' => $network_id ) );
	}

	mcms_cache_delete( 'networks_have_paths', 'site-options' );

	if ( !is_multisite() ) {
		$site_admins = array( $site_user->user_login );
		$users = get_users( array(
			'fields' => array( 'user_login' ),
			'role'   => 'administrator',
		) );
		if ( $users ) {
			foreach ( $users as $user ) {
				$site_admins[] = $user->user_login;
			}

			$site_admins = array_unique( $site_admins );
		}
	} else {
		$site_admins = get_site_option( 'site_admins' );
	}

	/* translators: Do not translate USERNAME, SITE_NAME, BLOG_URL, PASSWORD: those are placeholders. */
	$welcome_email = __( 'Howdy USERNAME,

Your new SITE_NAME site has been successfully set up at:
BLOG_URL

You can log in to the administrator account with the following information:

Username: USERNAME
Password: PASSWORD
Log in here: BLOG_URLsignin.php

We hope you enjoy your new site. Thanks!

--The Team @ SITE_NAME' );

	$misc_exts = array(
		// Images.
		'jpg', 'jpeg', 'png', 'gif',
		// Video.
		'mov', 'avi', 'mpg', '3gp', '3g2',
		// "audio".
		'midi', 'mid',
		// Miscellaneous.
		'pdf', 'doc', 'ppt', 'odt', 'pptx', 'docx', 'pps', 'ppsx', 'xls', 'xlsx', 'key',
	);
	$audio_exts = mcms_get_audio_extensions();
	$video_exts = mcms_get_video_extensions();
	$upload_filetypes = array_unique( array_merge( $misc_exts, $audio_exts, $video_exts ) );

	$sitemeta = array(
		'site_name' => $site_name,
		'admin_email' => $email,
		'admin_user_id' => $site_user->ID,
		'registration' => 'none',
		'upload_filetypes' => implode( ' ', $upload_filetypes ),
		'blog_upload_space' => 100,
		'fileupload_maxk' => 1500,
		'site_admins' => $site_admins,
		'allowedmyskins' => $allowed_myskins,
		'illegal_names' => array( 'www', 'web', 'root', 'admin', 'main', 'invite', 'administrator', 'files' ),
		'mcmsmu_upgrade_site' => $mcms_db_version,
		'welcome_email' => $welcome_email,
		/* translators: %s: site link */
		'first_post' => __( 'Welcome to %s. This is your first post. Edit or delete it, then start blogging!' ),
		// @todo - network admins should have a method of editing the network siteurl (used for cookie hash)
		'siteurl' => get_option( 'siteurl' ) . '/',
		'add_new_users' => '0',
		'upload_space_check_disabled' => is_multisite() ? get_site_option( 'upload_space_check_disabled' ) : '1',
		'subdomain_install' => intval( $subdomain_install ),
		'global_terms_enabled' => global_terms_enabled() ? '1' : '0',
		'ms_files_rewriting' => is_multisite() ? get_site_option( 'ms_files_rewriting' ) : '0',
		'initial_db_version' => get_option( 'initial_db_version' ),
		'active_sitewide_modules' => array(),
		'MCMSLANG' => get_locale(),
	);
	if ( ! $subdomain_install )
		$sitemeta['illegal_names'][] = 'blog';

	/**
	 * Filters meta for a network on creation.
	 *
	 * @since 3.7.0
	 *
	 * @param array $sitemeta   Associative array of network meta keys and values to be inserted.
	 * @param int   $network_id ID of network to populate.
	 */
	$sitemeta = apply_filters( 'populate_network_meta', $sitemeta, $network_id );

	$insert = '';
	foreach ( $sitemeta as $meta_key => $meta_value ) {
		if ( is_array( $meta_value ) )
			$meta_value = serialize( $meta_value );
		if ( !empty( $insert ) )
			$insert .= ', ';
		$insert .= $mcmsdb->prepare( "( %d, %s, %s)", $network_id, $meta_key, $meta_value );
	}
	$mcmsdb->query( "INSERT INTO $mcmsdb->sitemeta ( site_id, meta_key, meta_value ) VALUES " . $insert );

	/*
	 * When upgrading from single to multisite, assume the current site will
	 * become the main site of the network. When using populate_network()
	 * to create another network in an existing multisite environment, skip
	 * these steps since the main site of the new network has not yet been
	 * created.
	 */
	if ( ! is_multisite() ) {
		$current_site = new stdClass;
		$current_site->domain = $domain;
		$current_site->path = $path;
		$current_site->site_name = ucfirst( $domain );
		$mcmsdb->insert( $mcmsdb->blogs, array( 'site_id' => $network_id, 'blog_id' => 1, 'domain' => $domain, 'path' => $path, 'registered' => current_time( 'mysql' ) ) );
		$current_site->blog_id = $blog_id = $mcmsdb->insert_id;
		update_user_meta( $site_user->ID, 'source_domain', $domain );
		update_user_meta( $site_user->ID, 'primary_blog', $blog_id );

		if ( $subdomain_install )
			$mcms_rewrite->set_permalink_structure( '/%year%/%monthnum%/%day%/%postname%/' );
		else
			$mcms_rewrite->set_permalink_structure( '/blog/%year%/%monthnum%/%day%/%postname%/' );

		flush_rewrite_rules();

		if ( ! $subdomain_install )
			return true;

		$vhost_ok = false;
		$errstr = '';
		$hostname = substr( md5( time() ), 0, 6 ) . '.' . $domain; // Very random hostname!
		$page = mcms_remote_get( 'http://' . $hostname, array( 'timeout' => 5, 'httpversion' => '1.1' ) );
		if ( is_mcms_error( $page ) )
			$errstr = $page->get_error_message();
		elseif ( 200 == mcms_remote_retrieve_response_code( $page ) )
				$vhost_ok = true;

		if ( ! $vhost_ok ) {
			$msg = '<p><strong>' . __( 'Warning! Wildcard DNS may not be configured correctly!' ) . '</strong></p>';

			$msg .= '<p>' . sprintf(
				/* translators: %s: host name */
				__( 'The installer attempted to contact a random hostname (%s) on your domain.' ),
				'<code>' . $hostname . '</code>'
			);
			if ( ! empty ( $errstr ) ) {
				/* translators: %s: error message */
				$msg .= ' ' . sprintf( __( 'This resulted in an error message: %s' ), '<code>' . $errstr . '</code>' );
			}
			$msg .= '</p>';

			$msg .= '<p>' . sprintf(
				/* translators: %s: asterisk symbol (*) */
				__( 'To use a subdomain configuration, you must have a wildcard entry in your DNS. This usually means adding a %s hostname record pointing at your web server in your DNS configuration tool.' ),
				'<code>*</code>'
			) . '</p>';

			$msg .= '<p>' . __( 'You can still use your site but any subdomain you create may not be accessible. If you know your DNS is correct, ignore this message.' ) . '</p>';

			return new MCMS_Error( 'no_wildcard_dns', $msg );
		}
	}

	return true;
}
