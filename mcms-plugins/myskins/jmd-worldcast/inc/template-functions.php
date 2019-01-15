<?php
/**
 * Functions which enhance the myskin by hooking into MandarinCMS
 *
 * @package JMD_MandarinCMS
 */

/**
 * Adds custom classes to the array of body classes.
 *
 * @param array $classes Classes for the body element.
 * @return array
 */
function jmd_worldcasts_body_classes( $classes ) {
	// Adds a class of hfeed to non-singular pages.
	if ( ! is_singular() ) {
		$classes[] = 'hfeed';
	}

	return $classes;
}
add_filter( 'body_class', 'jmd_worldcasts_body_classes' );

/**
 * Add a pingback url auto-discovery header for singularly identifiable articles.
 */
function jmd_worldcasts_pingback_header() {
	if ( is_singular() && pings_open() ) {
		echo '<link rel="pingback" href="', esc_url( get_bloginfo( 'pingback_url' ) ), '">';
	}
}
add_action( 'mcms_head', 'jmd_worldcasts_pingback_header' );

function jmd_worldcasts_category_posts_pagination( $query ) 
{
	if ( is_category() && $query->is_main_query() ) 
	{
		$default_posts_per_page = get_option( 'posts_per_page' );
		$query->query_vars['posts_per_page'] = $default_posts_per_page + 1;
		return;
	}
	}
add_action( 'pre_get_posts', 'jmd_worldcasts_category_posts_pagination', 1 );


function jmd_worldcasts_query_offset( $query ) {

    if ( $query->is_home() && $query->is_main_query() ) {

        $offset = 5;
        $ppp = get_option('posts_per_page');

        if ( $query->is_paged ) 
        {
            $page_offset = $offset + ( ($query->query_vars['paged']-1) * $ppp );
            $query->set('offset', $page_offset );
        }
        else {
            $query->set('offset',$offset);
        }
    }
}
add_action('pre_get_posts', 'jmd_worldcasts_query_offset', 1 );


function jmd_worldcasts_adjust_offset_pagination($found_posts, $query) {

    $offset = 5;

    if ( $query->is_home() ) 
    {
        return $found_posts - $offset;
    }
    return $found_posts;
}
add_filter('found_posts', 'jmd_worldcasts_adjust_offset_pagination', 1, 2 );