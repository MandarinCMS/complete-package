<?php
/**
 * MandarinCMS Export Administration API
 *
 * @package MandarinCMS
 * @subpackage Administration
 */

/**
 * Version number for the export format.
 *
 * Bump this when something changes that might affect compatibility.
 *
 * @since 2.5.0
 */
define( 'WXR_VERSION', '1.2' );

/**
 * Generates the WXR export file for download.
 *
 * Default behavior is to export all content, however, note that post content will only
 * be exported for post types with the `can_export` argument enabled. Any posts with the
 * 'auto-draft' status will be skipped.
 *
 * @since 2.1.0
 *
 * @global mcmsdb    $mcmsdb MandarinCMS database abstraction object.
 * @global MCMS_Post $post Global `$post`.
 *
 * @param array $args {
 *     Optional. Arguments for generating the WXR export file for download. Default empty array.
 *
 *     @type string $content        Type of content to export. If set, only the post content of this post type
 *                                  will be exported. Accepts 'all', 'post', 'page', 'attachment', or a defined
 *                                  custom post. If an invalid custom post type is supplied, every post type for
 *                                  which `can_export` is enabled will be exported instead. If a valid custom post
 *                                  type is supplied but `can_export` is disabled, then 'posts' will be exported
 *                                  instead. When 'all' is supplied, only post types with `can_export` enabled will
 *                                  be exported. Default 'all'.
 *     @type string $author         Author to export content for. Only used when `$content` is 'post', 'page', or
 *                                  'attachment'. Accepts false (all) or a specific author ID. Default false (all).
 *     @type string $category       Category (slug) to export content for. Used only when `$content` is 'post'. If
 *                                  set, only post content assigned to `$category` will be exported. Accepts false
 *                                  or a specific category slug. Default is false (all categories).
 *     @type string $start_date     Start date to export content from. Expected date format is 'Y-m-d'. Used only
 *                                  when `$content` is 'post', 'page' or 'attachment'. Default false (since the
 *                                  beginning of time).
 *     @type string $end_date       End date to export content to. Expected date format is 'Y-m-d'. Used only when
 *                                  `$content` is 'post', 'page' or 'attachment'. Default false (latest publish date).
 *     @type string $status         Post status to export posts for. Used only when `$content` is 'post' or 'page'.
 *                                  Accepts false (all statuses except 'auto-draft'), or a specific status, i.e.
 *                                  'publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', or
 *                                  'trash'. Default false (all statuses except 'auto-draft').
 * }
 */
function export_mcms( $args = array() ) {
	global $mcmsdb, $post;

	$defaults = array( 'content' => 'all', 'author' => false, 'category' => false,
		'start_date' => false, 'end_date' => false, 'status' => false,
	);
	$args = mcms_parse_args( $args, $defaults );

	/**
	 * Fires at the beginning of an export, before any headers are sent.
	 *
	 * @since 2.3.0
	 *
	 * @param array $args An array of export arguments.
	 */
	do_action( 'export_mcms', $args );

	$sitename = sanitize_key( get_bloginfo( 'name' ) );
	if ( ! empty( $sitename ) ) {
		$sitename .= '.';
	}
	$date = date( 'Y-m-d' );
	$mcms_filename = $sitename . 'mandarincms.' . $date . '.xml';
	/**
	 * Filters the export filename.
	 *
	 * @since 4.4.0
	 *
	 * @param string $mcms_filename The name of the file for download.
	 * @param string $sitename    The site name.
	 * @param string $date        Today's date, formatted.
	 */
	$filename = apply_filters( 'export_mcms_filename', $mcms_filename, $sitename, $date );

	header( 'Content-Description: File Transfer' );
	header( 'Content-Disposition: attachment; filename=' . $filename );
	header( 'Content-Type: text/xml; charset=' . get_option( 'blog_charset' ), true );

	if ( 'all' != $args['content'] && post_type_exists( $args['content'] ) ) {
		$ptype = get_post_type_object( $args['content'] );
		if ( ! $ptype->can_export )
			$args['content'] = 'post';

		$where = $mcmsdb->prepare( "{$mcmsdb->posts}.post_type = %s", $args['content'] );
	} else {
		$post_types = get_post_types( array( 'can_export' => true ) );
		$esses = array_fill( 0, count($post_types), '%s' );
		$where = $mcmsdb->prepare( "{$mcmsdb->posts}.post_type IN (" . implode( ',', $esses ) . ')', $post_types );
	}

	if ( $args['status'] && ( 'post' == $args['content'] || 'page' == $args['content'] ) )
		$where .= $mcmsdb->prepare( " AND {$mcmsdb->posts}.post_status = %s", $args['status'] );
	else
		$where .= " AND {$mcmsdb->posts}.post_status != 'auto-draft'";

	$join = '';
	if ( $args['category'] && 'post' == $args['content'] ) {
		if ( $term = term_exists( $args['category'], 'category' ) ) {
			$join = "INNER JOIN {$mcmsdb->term_relationships} ON ({$mcmsdb->posts}.ID = {$mcmsdb->term_relationships}.object_id)";
			$where .= $mcmsdb->prepare( " AND {$mcmsdb->term_relationships}.term_taxonomy_id = %d", $term['term_taxonomy_id'] );
		}
	}

	if ( 'post' == $args['content'] || 'page' == $args['content'] || 'attachment' == $args['content'] ) {
		if ( $args['author'] )
			$where .= $mcmsdb->prepare( " AND {$mcmsdb->posts}.post_author = %d", $args['author'] );

		if ( $args['start_date'] )
			$where .= $mcmsdb->prepare( " AND {$mcmsdb->posts}.post_date >= %s", date( 'Y-m-d', strtotime($args['start_date']) ) );

		if ( $args['end_date'] )
			$where .= $mcmsdb->prepare( " AND {$mcmsdb->posts}.post_date < %s", date( 'Y-m-d', strtotime('+1 month', strtotime($args['end_date'])) ) );
	}

	// Grab a snapshot of post IDs, just in case it changes during the export.
	$post_ids = $mcmsdb->get_col( "SELECT ID FROM {$mcmsdb->posts} $join WHERE $where" );

	/*
	 * Get the requested terms ready, empty unless posts filtered by category
	 * or all content.
	 */
	$cats = $tags = $terms = array();
	if ( isset( $term ) && $term ) {
		$cat = get_term( $term['term_id'], 'category' );
		$cats = array( $cat->term_id => $cat );
		unset( $term, $cat );
	} elseif ( 'all' == $args['content'] ) {
		$categories = (array) get_categories( array( 'get' => 'all' ) );
		$tags = (array) get_tags( array( 'get' => 'all' ) );

		$custom_taxonomies = get_taxonomies( array( '_builtin' => false ) );
		$custom_terms = (array) get_terms( $custom_taxonomies, array( 'get' => 'all' ) );

		// Put categories in order with no child going before its parent.
		while ( $cat = array_shift( $categories ) ) {
			if ( $cat->parent == 0 || isset( $cats[$cat->parent] ) )
				$cats[$cat->term_id] = $cat;
			else
				$categories[] = $cat;
		}

		// Put terms in order with no child going before its parent.
		while ( $t = array_shift( $custom_terms ) ) {
			if ( $t->parent == 0 || isset( $terms[$t->parent] ) )
				$terms[$t->term_id] = $t;
			else
				$custom_terms[] = $t;
		}

		unset( $categories, $custom_taxonomies, $custom_terms );
	}

	/**
	 * Wrap given string in XML CDATA tag.
	 *
	 * @since 2.1.0
	 *
	 * @param string $str String to wrap in XML CDATA tag.
	 * @return string
	 */
	function wxr_cdata( $str ) {
		if ( ! seems_utf8( $str ) ) {
			$str = utf8_encode( $str );
		}
		// $str = ent2ncr(esc_html($str));
		$str = '<![CDATA[' . str_replace( ']]>', ']]]]><![CDATA[>', $str ) . ']]>';

		return $str;
	}

	/**
	 * Return the URL of the site
	 *
	 * @since 2.5.0
	 *
	 * @return string Site URL.
	 */
	function wxr_site_url() {
		// Multisite: the base URL.
		if ( is_multisite() )
			return network_home_url();
		// MandarinCMS (single site): the blog URL.
		else
			return get_bloginfo_rss( 'url' );
	}

	/**
	 * Output a cat_name XML tag from a given category object
	 *
	 * @since 2.1.0
	 *
	 * @param object $category Category Object
	 */
	function wxr_cat_name( $category ) {
		if ( empty( $category->name ) )
			return;

		echo '<mcms:cat_name>' . wxr_cdata( $category->name ) . "</mcms:cat_name>\n";
	}

	/**
	 * Output a category_description XML tag from a given category object
	 *
	 * @since 2.1.0
	 *
	 * @param object $category Category Object
	 */
	function wxr_category_description( $category ) {
		if ( empty( $category->description ) )
			return;

		echo '<mcms:category_description>' . wxr_cdata( $category->description ) . "</mcms:category_description>\n";
	}

	/**
	 * Output a tag_name XML tag from a given tag object
	 *
	 * @since 2.3.0
	 *
	 * @param object $tag Tag Object
	 */
	function wxr_tag_name( $tag ) {
		if ( empty( $tag->name ) )
			return;

		echo '<mcms:tag_name>' . wxr_cdata( $tag->name ) . "</mcms:tag_name>\n";
	}

	/**
	 * Output a tag_description XML tag from a given tag object
	 *
	 * @since 2.3.0
	 *
	 * @param object $tag Tag Object
	 */
	function wxr_tag_description( $tag ) {
		if ( empty( $tag->description ) )
			return;

		echo '<mcms:tag_description>' . wxr_cdata( $tag->description ) . "</mcms:tag_description>\n";
	}

	/**
	 * Output a term_name XML tag from a given term object
	 *
	 * @since 2.9.0
	 *
	 * @param object $term Term Object
	 */
	function wxr_term_name( $term ) {
		if ( empty( $term->name ) )
			return;

		echo '<mcms:term_name>' . wxr_cdata( $term->name ) . "</mcms:term_name>\n";
	}

	/**
	 * Output a term_description XML tag from a given term object
	 *
	 * @since 2.9.0
	 *
	 * @param object $term Term Object
	 */
	function wxr_term_description( $term ) {
		if ( empty( $term->description ) )
			return;

		echo "\t\t<mcms:term_description>" . wxr_cdata( $term->description ) . "</mcms:term_description>\n";
	}

	/**
	 * Output term meta XML tags for a given term object.
	 *
	 * @since 4.6.0
	 *
	 * @param MCMS_Term $term Term object.
	 */
	function wxr_term_meta( $term ) {
		global $mcmsdb;

		$termmeta = $mcmsdb->get_results( $mcmsdb->prepare( "SELECT * FROM $mcmsdb->termmeta WHERE term_id = %d", $term->term_id ) );

		foreach ( $termmeta as $meta ) {
			/**
			 * Filters whether to selectively skip term meta used for WXR exports.
			 *
			 * Returning a truthy value to the filter will skip the current meta
			 * object from being exported.
			 *
			 * @since 4.6.0
			 *
			 * @param bool   $skip     Whether to skip the current piece of term meta. Default false.
			 * @param string $meta_key Current meta key.
			 * @param object $meta     Current meta object.
			 */
			if ( ! apply_filters( 'wxr_export_skip_termmeta', false, $meta->meta_key, $meta ) ) {
				printf( "\t\t<mcms:termmeta>\n\t\t\t<mcms:meta_key>%s</mcms:meta_key>\n\t\t\t<mcms:meta_value>%s</mcms:meta_value>\n\t\t</mcms:termmeta>\n", wxr_cdata( $meta->meta_key ), wxr_cdata( $meta->meta_value ) );
			}
		}
	}

	/**
	 * Output list of authors with posts
	 *
	 * @since 3.1.0
	 *
	 * @global mcmsdb $mcmsdb MandarinCMS database abstraction object.
	 *
	 * @param array $post_ids Array of post IDs to filter the query by. Optional.
	 */
	function wxr_authors_list( array $post_ids = null ) {
		global $mcmsdb;

		if ( !empty( $post_ids ) ) {
			$post_ids = array_map( 'absint', $post_ids );
			$and = 'AND ID IN ( ' . implode( ', ', $post_ids ) . ')';
		} else {
			$and = '';
		}

		$authors = array();
		$results = $mcmsdb->get_results( "SELECT DISTINCT post_author FROM $mcmsdb->posts WHERE post_status != 'auto-draft' $and" );
		foreach ( (array) $results as $result )
			$authors[] = get_userdata( $result->post_author );

		$authors = array_filter( $authors );

		foreach ( $authors as $author ) {
			echo "\t<mcms:author>";
			echo '<mcms:author_id>' . intval( $author->ID ) . '</mcms:author_id>';
			echo '<mcms:author_login>' . wxr_cdata( $author->user_login ) . '</mcms:author_login>';
			echo '<mcms:author_email>' . wxr_cdata( $author->user_email ) . '</mcms:author_email>';
			echo '<mcms:author_display_name>' . wxr_cdata( $author->display_name ) . '</mcms:author_display_name>';
			echo '<mcms:author_first_name>' . wxr_cdata( $author->first_name ) . '</mcms:author_first_name>';
			echo '<mcms:author_last_name>' . wxr_cdata( $author->last_name ) . '</mcms:author_last_name>';
			echo "</mcms:author>\n";
		}
	}

	/**
	 * Output all navigation menu terms
	 *
	 * @since 3.1.0
	 */
	function wxr_nav_menu_terms() {
		$nav_menus = mcms_get_nav_menus();
		if ( empty( $nav_menus ) || ! is_array( $nav_menus ) )
			return;

		foreach ( $nav_menus as $menu ) {
			echo "\t<mcms:term>";
			echo '<mcms:term_id>' . intval( $menu->term_id ) . '</mcms:term_id>';
			echo '<mcms:term_taxonomy>nav_menu</mcms:term_taxonomy>';
			echo '<mcms:term_slug>' . wxr_cdata( $menu->slug ) . '</mcms:term_slug>';
			wxr_term_name( $menu );
			echo "</mcms:term>\n";
		}
	}

	/**
	 * Output list of taxonomy terms, in XML tag format, associated with a post
	 *
	 * @since 2.3.0
	 */
	function wxr_post_taxonomy() {
		$post = get_post();

		$taxonomies = get_object_taxonomies( $post->post_type );
		if ( empty( $taxonomies ) )
			return;
		$terms = mcms_get_object_terms( $post->ID, $taxonomies );

		foreach ( (array) $terms as $term ) {
			echo "\t\t<category domain=\"{$term->taxonomy}\" nicename=\"{$term->slug}\">" . wxr_cdata( $term->name ) . "</category>\n";
		}
	}

	/**
	 *
	 * @param bool   $return_me
	 * @param string $meta_key
	 * @return bool
	 */
	function wxr_filter_postmeta( $return_me, $meta_key ) {
		if ( '_edit_lock' == $meta_key )
			$return_me = true;
		return $return_me;
	}
	add_filter( 'wxr_export_skip_postmeta', 'wxr_filter_postmeta', 10, 2 );

	echo '<?xml version="1.0" encoding="' . get_bloginfo('charset') . "\" ?>\n";

	?>
<!-- This is a MandarinCMS eXtended RSS file generated by MandarinCMS as an export of your site. -->
<!-- It contains information about your site's posts, pages, comments, categories, and other content. -->
<!-- You may use this file to transfer that content from one site to another. -->
<!-- This file is not intended to serve as a complete backup of your site. -->

<!-- To import this information into a MandarinCMS site follow these steps: -->
<!-- 1. Log in to that site as an administrator. -->
<!-- 2. Go to Tools: Import in the MandarinCMS admin panel. -->
<!-- 3. Install the "MandarinCMS" importer from the list. -->
<!-- 4. Activate & Run Importer. -->
<!-- 5. Upload this file using the form provided on that page. -->
<!-- 6. You will first be asked to map the authors in this export file to users -->
<!--    on the site. For each author, you may choose to map to an -->
<!--    existing user on the site or to create a new user. -->
<!-- 7. MandarinCMS will then import each of the posts, pages, comments, categories, etc. -->
<!--    contained in this file into your site. -->

<?php the_generator( 'export' ); ?>
<rss version="2.0"
	xmlns:excerpt="http://mandarincms.com/export/<?php echo WXR_VERSION; ?>/excerpt/"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:mcms="http://mandarincms.com/export/<?php echo WXR_VERSION; ?>/"
>

<channel>
	<title><?php bloginfo_rss( 'name' ); ?></title>
	<link><?php bloginfo_rss( 'url' ); ?></link>
	<description><?php bloginfo_rss( 'description' ); ?></description>
	<pubDate><?php echo date( 'D, d M Y H:i:s +0000' ); ?></pubDate>
	<language><?php bloginfo_rss( 'language' ); ?></language>
	<mcms:wxr_version><?php echo WXR_VERSION; ?></mcms:wxr_version>
	<mcms:base_site_url><?php echo wxr_site_url(); ?></mcms:base_site_url>
	<mcms:base_blog_url><?php bloginfo_rss( 'url' ); ?></mcms:base_blog_url>

<?php wxr_authors_list( $post_ids ); ?>

<?php foreach ( $cats as $c ) : ?>
	<mcms:category>
		<mcms:term_id><?php echo intval( $c->term_id ); ?></mcms:term_id>
		<mcms:category_nicename><?php echo wxr_cdata( $c->slug ); ?></mcms:category_nicename>
		<mcms:category_parent><?php echo wxr_cdata( $c->parent ? $cats[$c->parent]->slug : '' ); ?></mcms:category_parent>
		<?php wxr_cat_name( $c );
		wxr_category_description( $c );
		wxr_term_meta( $c ); ?>
	</mcms:category>
<?php endforeach; ?>
<?php foreach ( $tags as $t ) : ?>
	<mcms:tag>
		<mcms:term_id><?php echo intval( $t->term_id ); ?></mcms:term_id>
		<mcms:tag_slug><?php echo wxr_cdata( $t->slug ); ?></mcms:tag_slug>
		<?php wxr_tag_name( $t );
		wxr_tag_description( $t );
		wxr_term_meta( $t ); ?>
	</mcms:tag>
<?php endforeach; ?>
<?php foreach ( $terms as $t ) : ?>
	<mcms:term>
		<mcms:term_id><?php echo wxr_cdata( $t->term_id ); ?></mcms:term_id>
		<mcms:term_taxonomy><?php echo wxr_cdata( $t->taxonomy ); ?></mcms:term_taxonomy>
		<mcms:term_slug><?php echo wxr_cdata( $t->slug ); ?></mcms:term_slug>
		<mcms:term_parent><?php echo wxr_cdata( $t->parent ? $terms[$t->parent]->slug : '' ); ?></mcms:term_parent>
		<?php wxr_term_name( $t );
		wxr_term_description( $t );
		wxr_term_meta( $t ); ?>
	</mcms:term>
<?php endforeach; ?>
<?php if ( 'all' == $args['content'] ) wxr_nav_menu_terms(); ?>

	<?php
	/** This action is documented in mcms-roots/feed-rss2.php */
	do_action( 'rss2_head' );
	?>

<?php if ( $post_ids ) {
	/**
	 * @global MCMS_Query $mcms_query
	 */
	global $mcms_query;

	// Fake being in the loop.
	$mcms_query->in_the_loop = true;

	// Fetch 20 posts at a time rather than loading the entire table into memory.
	while ( $next_posts = array_splice( $post_ids, 0, 20 ) ) {
	$where = 'WHERE ID IN (' . join( ',', $next_posts ) . ')';
	$posts = $mcmsdb->get_results( "SELECT * FROM {$mcmsdb->posts} $where" );

	// Begin Loop.
	foreach ( $posts as $post ) {
		setup_postdata( $post );
		$is_sticky = is_sticky( $post->ID ) ? 1 : 0;
?>
	<item>
		<title><?php
			/** This filter is documented in mcms-roots/feed.php */
			echo apply_filters( 'the_title_rss', $post->post_title );
		?></title>
		<link><?php the_permalink_rss() ?></link>
		<pubDate><?php echo mysql2date( 'D, d M Y H:i:s +0000', get_post_time( 'Y-m-d H:i:s', true ), false ); ?></pubDate>
		<dc:creator><?php echo wxr_cdata( get_the_author_meta( 'login' ) ); ?></dc:creator>
		<guid isPermaLink="false"><?php the_guid(); ?></guid>
		<description></description>
		<content:encoded><?php
			/**
			 * Filters the post content used for WXR exports.
			 *
			 * @since 2.5.0
			 *
			 * @param string $post_content Content of the current post.
			 */
			echo wxr_cdata( apply_filters( 'the_content_export', $post->post_content ) );
		?></content:encoded>
		<excerpt:encoded><?php
			/**
			 * Filters the post excerpt used for WXR exports.
			 *
			 * @since 2.6.0
			 *
			 * @param string $post_excerpt Excerpt for the current post.
			 */
			echo wxr_cdata( apply_filters( 'the_excerpt_export', $post->post_excerpt ) );
		?></excerpt:encoded>
		<mcms:post_id><?php echo intval( $post->ID ); ?></mcms:post_id>
		<mcms:post_date><?php echo wxr_cdata( $post->post_date ); ?></mcms:post_date>
		<mcms:post_date_gmt><?php echo wxr_cdata( $post->post_date_gmt ); ?></mcms:post_date_gmt>
		<mcms:comment_status><?php echo wxr_cdata( $post->comment_status ); ?></mcms:comment_status>
		<mcms:ping_status><?php echo wxr_cdata( $post->ping_status ); ?></mcms:ping_status>
		<mcms:post_name><?php echo wxr_cdata( $post->post_name ); ?></mcms:post_name>
		<mcms:status><?php echo wxr_cdata( $post->post_status ); ?></mcms:status>
		<mcms:post_parent><?php echo intval( $post->post_parent ); ?></mcms:post_parent>
		<mcms:menu_order><?php echo intval( $post->menu_order ); ?></mcms:menu_order>
		<mcms:post_type><?php echo wxr_cdata( $post->post_type ); ?></mcms:post_type>
		<mcms:post_password><?php echo wxr_cdata( $post->post_password ); ?></mcms:post_password>
		<mcms:is_sticky><?php echo intval( $is_sticky ); ?></mcms:is_sticky>
<?php	if ( $post->post_type == 'attachment' ) : ?>
		<mcms:attachment_url><?php echo wxr_cdata( mcms_get_attachment_url( $post->ID ) ); ?></mcms:attachment_url>
<?php 	endif; ?>
<?php 	wxr_post_taxonomy(); ?>
<?php	$postmeta = $mcmsdb->get_results( $mcmsdb->prepare( "SELECT * FROM $mcmsdb->postmeta WHERE post_id = %d", $post->ID ) );
		foreach ( $postmeta as $meta ) :
			/**
			 * Filters whether to selectively skip post meta used for WXR exports.
			 *
			 * Returning a truthy value to the filter will skip the current meta
			 * object from being exported.
			 *
			 * @since 3.3.0
			 *
			 * @param bool   $skip     Whether to skip the current post meta. Default false.
			 * @param string $meta_key Current meta key.
			 * @param object $meta     Current meta object.
			 */
			if ( apply_filters( 'wxr_export_skip_postmeta', false, $meta->meta_key, $meta ) )
				continue;
		?>
		<mcms:postmeta>
			<mcms:meta_key><?php echo wxr_cdata( $meta->meta_key ); ?></mcms:meta_key>
			<mcms:meta_value><?php echo wxr_cdata( $meta->meta_value ); ?></mcms:meta_value>
		</mcms:postmeta>
<?php	endforeach;

		$_comments = $mcmsdb->get_results( $mcmsdb->prepare( "SELECT * FROM $mcmsdb->comments WHERE comment_post_ID = %d AND comment_approved <> 'spam'", $post->ID ) );
		$comments = array_map( 'get_comment', $_comments );
		foreach ( $comments as $c ) : ?>
		<mcms:comment>
			<mcms:comment_id><?php echo intval( $c->comment_ID ); ?></mcms:comment_id>
			<mcms:comment_author><?php echo wxr_cdata( $c->comment_author ); ?></mcms:comment_author>
			<mcms:comment_author_email><?php echo wxr_cdata( $c->comment_author_email ); ?></mcms:comment_author_email>
			<mcms:comment_author_url><?php echo esc_url_raw( $c->comment_author_url ); ?></mcms:comment_author_url>
			<mcms:comment_author_IP><?php echo wxr_cdata( $c->comment_author_IP ); ?></mcms:comment_author_IP>
			<mcms:comment_date><?php echo wxr_cdata( $c->comment_date ); ?></mcms:comment_date>
			<mcms:comment_date_gmt><?php echo wxr_cdata( $c->comment_date_gmt ); ?></mcms:comment_date_gmt>
			<mcms:comment_content><?php echo wxr_cdata( $c->comment_content ) ?></mcms:comment_content>
			<mcms:comment_approved><?php echo wxr_cdata( $c->comment_approved ); ?></mcms:comment_approved>
			<mcms:comment_type><?php echo wxr_cdata( $c->comment_type ); ?></mcms:comment_type>
			<mcms:comment_parent><?php echo intval( $c->comment_parent ); ?></mcms:comment_parent>
			<mcms:comment_user_id><?php echo intval( $c->user_id ); ?></mcms:comment_user_id>
<?php		$c_meta = $mcmsdb->get_results( $mcmsdb->prepare( "SELECT * FROM $mcmsdb->commentmeta WHERE comment_id = %d", $c->comment_ID ) );
			foreach ( $c_meta as $meta ) :
				/**
				 * Filters whether to selectively skip comment meta used for WXR exports.
				 *
				 * Returning a truthy value to the filter will skip the current meta
				 * object from being exported.
				 *
				 * @since 4.0.0
				 *
				 * @param bool   $skip     Whether to skip the current comment meta. Default false.
				 * @param string $meta_key Current meta key.
				 * @param object $meta     Current meta object.
				 */
				if ( apply_filters( 'wxr_export_skip_commentmeta', false, $meta->meta_key, $meta ) ) {
					continue;
				}
			?>
			<mcms:commentmeta>
				<mcms:meta_key><?php echo wxr_cdata( $meta->meta_key ); ?></mcms:meta_key>
				<mcms:meta_value><?php echo wxr_cdata( $meta->meta_value ); ?></mcms:meta_value>
			</mcms:commentmeta>
<?php		endforeach; ?>
		</mcms:comment>
<?php	endforeach; ?>
	</item>
<?php
	}
	}
} ?>
</channel>
</rss>
<?php
}
