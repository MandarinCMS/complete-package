<?php
/**
 * Nav Menu API: Template functions
 *
 * @package MandarinCMS
 * @subpackage Nav_Menus
 * @since 3.0.0
 */

/** Walker_Nav_Menu class */
require_once BASED_TREE_URI . MCMSINC . '/class-walker-nav-menu.php';

/**
 * Displays a navigation menu.
 *
 * @since 3.0.0
 * @since 4.7.0 Added the `item_spacing` argument.
 *
 * @staticvar array $menu_id_slugs
 *
 * @param array $args {
 *     Optional. Array of nav menu arguments.
 *
 *     @type int|string|MCMS_Term $menu            Desired menu. Accepts a menu ID, slug, name, or object. Default empty.
 *     @type string             $menu_class      CSS class to use for the ul element which forms the menu. Default 'menu'.
 *     @type string             $menu_id         The ID that is applied to the ul element which forms the menu.
 *                                               Default is the menu slug, incremented.
 *     @type string             $container       Whether to wrap the ul, and what to wrap it with. Default 'div'.
 *     @type string             $container_class Class that is applied to the container. Default 'menu-{menu slug}-container'.
 *     @type string             $container_id    The ID that is applied to the container. Default empty.
 *     @type callable|bool      $fallback_cb     If the menu doesn't exists, a callback function will fire.
 *                                               Default is 'mcms_page_menu'. Set to false for no fallback.
 *     @type string             $before          Text before the link markup. Default empty.
 *     @type string             $after           Text after the link markup. Default empty.
 *     @type string             $link_before     Text before the link text. Default empty.
 *     @type string             $link_after      Text after the link text. Default empty.
 *     @type bool               $echo            Whether to echo the menu or return it. Default true.
 *     @type int                $depth           How many levels of the hierarchy are to be included. 0 means all. Default 0.
 *     @type object             $walker          Instance of a custom walker class. Default empty.
 *     @type string             $myskin_location  MySkin location to be used. Must be registered with register_nav_menu()
 *                                               in order to be selectable by the user.
 *     @type string             $items_wrap      How the list items should be wrapped. Default is a ul with an id and class.
 *                                               Uses printf() format with numbered placeholders.
 *     @type string             $item_spacing    Whether to preserve whitespace within the menu's HTML. Accepts 'preserve' or 'discard'. Default 'preserve'.
 * }
 * @return string|false|void Menu output if $echo is false, false if there are no items or no menu was found.
 */
function mcms_nav_menu( $args = array() ) {
	static $menu_id_slugs = array();

	$defaults = array( 'menu' => '', 'container' => 'div', 'container_class' => '', 'container_id' => '', 'menu_class' => 'menu', 'menu_id' => '',
	'echo' => true, 'fallback_cb' => 'mcms_page_menu', 'before' => '', 'after' => '', 'link_before' => '', 'link_after' => '', 'items_wrap' => '<ul id="%1$s" class="%2$s">%3$s</ul>', 'item_spacing' => 'preserve',
	'depth' => 0, 'walker' => '', 'myskin_location' => '' );

	$args = mcms_parse_args( $args, $defaults );

	if ( ! in_array( $args['item_spacing'], array( 'preserve', 'discard' ), true ) ) {
		// invalid value, fall back to default.
		$args['item_spacing'] = $defaults['item_spacing'];
	}

	/**
	 * Filters the arguments used to display a navigation menu.
	 *
	 * @since 3.0.0
	 *
	 * @see mcms_nav_menu()
	 *
	 * @param array $args Array of mcms_nav_menu() arguments.
	 */
	$args = apply_filters( 'mcms_nav_menu_args', $args );
	$args = (object) $args;

	/**
	 * Filters whether to short-circuit the mcms_nav_menu() output.
	 *
	 * Returning a non-null value to the filter will short-circuit
	 * mcms_nav_menu(), echoing that value if $args->echo is true,
	 * returning that value otherwise.
	 *
	 * @since 3.9.0
	 *
	 * @see mcms_nav_menu()
	 *
	 * @param string|null $output Nav menu output to short-circuit with. Default null.
	 * @param stdClass    $args   An object containing mcms_nav_menu() arguments.
	 */
	$nav_menu = apply_filters( 'pre_mcms_nav_menu', null, $args );

	if ( null !== $nav_menu ) {
		if ( $args->echo ) {
			echo $nav_menu;
			return;
		}

		return $nav_menu;
	}

	// Get the nav menu based on the requested menu
	$menu = mcms_get_nav_menu_object( $args->menu );

	// Get the nav menu based on the myskin_location
	if ( ! $menu && $args->myskin_location && ( $locations = get_nav_menu_locations() ) && isset( $locations[ $args->myskin_location ] ) )
		$menu = mcms_get_nav_menu_object( $locations[ $args->myskin_location ] );

	// get the first menu that has items if we still can't find a menu
	if ( ! $menu && !$args->myskin_location ) {
		$menus = mcms_get_nav_menus();
		foreach ( $menus as $menu_maybe ) {
			if ( $menu_items = mcms_get_nav_menu_items( $menu_maybe->term_id, array( 'update_post_term_cache' => false ) ) ) {
				$menu = $menu_maybe;
				break;
			}
		}
	}

	if ( empty( $args->menu ) ) {
		$args->menu = $menu;
	}

	// If the menu exists, get its items.
	if ( $menu && ! is_mcms_error($menu) && !isset($menu_items) )
		$menu_items = mcms_get_nav_menu_items( $menu->term_id, array( 'update_post_term_cache' => false ) );

	/*
	 * If no menu was found:
	 *  - Fall back (if one was specified), or bail.
	 *
	 * If no menu items were found:
	 *  - Fall back, but only if no myskin location was specified.
	 *  - Otherwise, bail.
	 */
	if ( ( !$menu || is_mcms_error($menu) || ( isset($menu_items) && empty($menu_items) && !$args->myskin_location ) )
		&& isset( $args->fallback_cb ) && $args->fallback_cb && is_callable( $args->fallback_cb ) )
			return call_user_func( $args->fallback_cb, (array) $args );

	if ( ! $menu || is_mcms_error( $menu ) )
		return false;

	$nav_menu = $items = '';

	$show_container = false;
	if ( $args->container ) {
		/**
		 * Filters the list of HTML tags that are valid for use as menu containers.
		 *
		 * @since 3.0.0
		 *
		 * @param array $tags The acceptable HTML tags for use as menu containers.
		 *                    Default is array containing 'div' and 'nav'.
		 */
		$allowed_tags = apply_filters( 'mcms_nav_menu_container_allowedtags', array( 'div', 'nav' ) );
		if ( is_string( $args->container ) && in_array( $args->container, $allowed_tags ) ) {
			$show_container = true;
			$class = $args->container_class ? ' class="' . esc_attr( $args->container_class ) . '"' : ' class="menu-'. $menu->slug .'-container"';
			$id = $args->container_id ? ' id="' . esc_attr( $args->container_id ) . '"' : '';
			$nav_menu .= '<'. $args->container . $id . $class . '>';
		}
	}

	// Set up the $menu_item variables
	_mcms_menu_item_classes_by_context( $menu_items );

	$sorted_menu_items = $menu_items_with_children = array();
	foreach ( (array) $menu_items as $menu_item ) {
		$sorted_menu_items[ $menu_item->menu_order ] = $menu_item;
		if ( $menu_item->menu_item_parent )
			$menu_items_with_children[ $menu_item->menu_item_parent ] = true;
	}

	// Add the menu-item-has-children class where applicable
	if ( $menu_items_with_children ) {
		foreach ( $sorted_menu_items as &$menu_item ) {
			if ( isset( $menu_items_with_children[ $menu_item->ID ] ) )
				$menu_item->classes[] = 'menu-item-has-children';
		}
	}

	unset( $menu_items, $menu_item );

	/**
	 * Filters the sorted list of menu item objects before generating the menu's HTML.
	 *
	 * @since 3.1.0
	 *
	 * @param array    $sorted_menu_items The menu items, sorted by each menu item's menu order.
	 * @param stdClass $args              An object containing mcms_nav_menu() arguments.
	 */
	$sorted_menu_items = apply_filters( 'mcms_nav_menu_objects', $sorted_menu_items, $args );

	$items .= walk_nav_menu_tree( $sorted_menu_items, $args->depth, $args );
	unset($sorted_menu_items);

	// Attributes
	if ( ! empty( $args->menu_id ) ) {
		$wrap_id = $args->menu_id;
	} else {
		$wrap_id = 'menu-' . $menu->slug;
		while ( in_array( $wrap_id, $menu_id_slugs ) ) {
			if ( preg_match( '#-(\d+)$#', $wrap_id, $matches ) )
				$wrap_id = preg_replace('#-(\d+)$#', '-' . ++$matches[1], $wrap_id );
			else
				$wrap_id = $wrap_id . '-1';
		}
	}
	$menu_id_slugs[] = $wrap_id;

	$wrap_class = $args->menu_class ? $args->menu_class : '';

	/**
	 * Filters the HTML list content for navigation menus.
	 *
	 * @since 3.0.0
	 *
	 * @see mcms_nav_menu()
	 *
	 * @param string   $items The HTML list content for the menu items.
	 * @param stdClass $args  An object containing mcms_nav_menu() arguments.
	 */
	$items = apply_filters( 'mcms_nav_menu_items', $items, $args );
	/**
	 * Filters the HTML list content for a specific navigation menu.
	 *
	 * @since 3.0.0
	 *
	 * @see mcms_nav_menu()
	 *
	 * @param string   $items The HTML list content for the menu items.
	 * @param stdClass $args  An object containing mcms_nav_menu() arguments.
	 */
	$items = apply_filters( "mcms_nav_menu_{$menu->slug}_items", $items, $args );

	// Don't print any markup if there are no items at this point.
	if ( empty( $items ) )
		return false;

	$nav_menu .= sprintf( $args->items_wrap, esc_attr( $wrap_id ), esc_attr( $wrap_class ), $items );
	unset( $items );

	if ( $show_container )
		$nav_menu .= '</' . $args->container . '>';

	/**
	 * Filters the HTML content for navigation menus.
	 *
	 * @since 3.0.0
	 *
	 * @see mcms_nav_menu()
	 *
	 * @param string   $nav_menu The HTML content for the navigation menu.
	 * @param stdClass $args     An object containing mcms_nav_menu() arguments.
	 */
	$nav_menu = apply_filters( 'mcms_nav_menu', $nav_menu, $args );

	if ( $args->echo )
		echo $nav_menu;
	else
		return $nav_menu;
}

/**
 * Add the class property classes for the current context, if applicable.
 *
 * @access private
 * @since 3.0.0
 *
 * @global MCMS_Query   $mcms_query
 * @global MCMS_Rewrite $mcms_rewrite
 *
 * @param array $menu_items The current menu item objects to which to add the class property information.
 */
function _mcms_menu_item_classes_by_context( &$menu_items ) {
	global $mcms_query, $mcms_rewrite;

	$queried_object = $mcms_query->get_queried_object();
	$queried_object_id = (int) $mcms_query->queried_object_id;

	$active_object = '';
	$active_ancestor_item_ids = array();
	$active_parent_item_ids = array();
	$active_parent_object_ids = array();
	$possible_taxonomy_ancestors = array();
	$possible_object_parents = array();
	$home_page_id = (int) get_option( 'page_for_posts' );

	if ( $mcms_query->is_singular && ! empty( $queried_object->post_type ) && ! is_post_type_hierarchical( $queried_object->post_type ) ) {
		foreach ( (array) get_object_taxonomies( $queried_object->post_type ) as $taxonomy ) {
			if ( is_taxonomy_hierarchical( $taxonomy ) ) {
				$term_hierarchy = _get_term_hierarchy( $taxonomy );
				$terms = mcms_get_object_terms( $queried_object_id, $taxonomy, array( 'fields' => 'ids' ) );
				if ( is_array( $terms ) ) {
					$possible_object_parents = array_merge( $possible_object_parents, $terms );
					$term_to_ancestor = array();
					foreach ( (array) $term_hierarchy as $anc => $descs ) {
						foreach ( (array) $descs as $desc )
							$term_to_ancestor[ $desc ] = $anc;
					}

					foreach ( $terms as $desc ) {
						do {
							$possible_taxonomy_ancestors[ $taxonomy ][] = $desc;
							if ( isset( $term_to_ancestor[ $desc ] ) ) {
								$_desc = $term_to_ancestor[ $desc ];
								unset( $term_to_ancestor[ $desc ] );
								$desc = $_desc;
							} else {
								$desc = 0;
							}
						} while ( ! empty( $desc ) );
					}
				}
			}
		}
	} elseif ( ! empty( $queried_object->taxonomy ) && is_taxonomy_hierarchical( $queried_object->taxonomy ) ) {
		$term_hierarchy = _get_term_hierarchy( $queried_object->taxonomy );
		$term_to_ancestor = array();
		foreach ( (array) $term_hierarchy as $anc => $descs ) {
			foreach ( (array) $descs as $desc )
				$term_to_ancestor[ $desc ] = $anc;
		}
		$desc = $queried_object->term_id;
		do {
			$possible_taxonomy_ancestors[ $queried_object->taxonomy ][] = $desc;
			if ( isset( $term_to_ancestor[ $desc ] ) ) {
				$_desc = $term_to_ancestor[ $desc ];
				unset( $term_to_ancestor[ $desc ] );
				$desc = $_desc;
			} else {
				$desc = 0;
			}
		} while ( ! empty( $desc ) );
	}

	$possible_object_parents = array_filter( $possible_object_parents );

	$front_page_url = home_url();
	$front_page_id  = (int) get_option( 'page_on_front' );

	foreach ( (array) $menu_items as $key => $menu_item ) {

		$menu_items[$key]->current = false;

		$classes = (array) $menu_item->classes;
		$classes[] = 'menu-item';
		$classes[] = 'menu-item-type-' . $menu_item->type;
		$classes[] = 'menu-item-object-' . $menu_item->object;

		// This menu item is set as the 'Front Page'.
		if ( 'post_type' === $menu_item->type && $front_page_id === (int) $menu_item->object_id ) {
			$classes[] = 'menu-item-home';
		}

		// if the menu item corresponds to a taxonomy term for the currently-queried non-hierarchical post object
		if ( $mcms_query->is_singular && 'taxonomy' == $menu_item->type && in_array( $menu_item->object_id, $possible_object_parents ) ) {
			$active_parent_object_ids[] = (int) $menu_item->object_id;
			$active_parent_item_ids[] = (int) $menu_item->db_id;
			$active_object = $queried_object->post_type;

		// if the menu item corresponds to the currently-queried post or taxonomy object
		} elseif (
			$menu_item->object_id == $queried_object_id &&
			(
				( ! empty( $home_page_id ) && 'post_type' == $menu_item->type && $mcms_query->is_home && $home_page_id == $menu_item->object_id ) ||
				( 'post_type' == $menu_item->type && $mcms_query->is_singular ) ||
				( 'taxonomy' == $menu_item->type && ( $mcms_query->is_category || $mcms_query->is_tag || $mcms_query->is_tax ) && $queried_object->taxonomy == $menu_item->object )
			)
		) {
			$classes[] = 'current-menu-item';
			$menu_items[$key]->current = true;
			$_anc_id = (int) $menu_item->db_id;

			while(
				( $_anc_id = get_post_meta( $_anc_id, '_menu_item_menu_item_parent', true ) ) &&
				! in_array( $_anc_id, $active_ancestor_item_ids )
			) {
				$active_ancestor_item_ids[] = $_anc_id;
			}

			if ( 'post_type' == $menu_item->type && 'page' == $menu_item->object ) {
				// Back compat classes for pages to match mcms_page_menu()
				$classes[] = 'page_item';
				$classes[] = 'page-item-' . $menu_item->object_id;
				$classes[] = 'current_page_item';
			}

			$active_parent_item_ids[] = (int) $menu_item->menu_item_parent;
			$active_parent_object_ids[] = (int) $menu_item->post_parent;
			$active_object = $menu_item->object;

		// if the menu item corresponds to the currently-queried post type archive
		} elseif (
			'post_type_archive' == $menu_item->type &&
			is_post_type_archive( array( $menu_item->object ) )
		) {
			$classes[] = 'current-menu-item';
			$menu_items[$key]->current = true;
			$_anc_id = (int) $menu_item->db_id;

			while(
				( $_anc_id = get_post_meta( $_anc_id, '_menu_item_menu_item_parent', true ) ) &&
				! in_array( $_anc_id, $active_ancestor_item_ids )
			) {
				$active_ancestor_item_ids[] = $_anc_id;
			}

			$active_parent_item_ids[] = (int) $menu_item->menu_item_parent;

		// if the menu item corresponds to the currently-requested URL
		} elseif ( 'custom' == $menu_item->object && isset( $_SERVER['HTTP_HOST'] ) ) {
			$_root_relative_current = untrailingslashit( $_SERVER['REQUEST_URI'] );

			//if it is the customize page then it will strips the query var off the url before entering the comparison block.
			if ( is_customize_preview() ) {
				$_root_relative_current = strtok( untrailingslashit( $_SERVER['REQUEST_URI'] ), '?' );
			}
			$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_root_relative_current );
			$raw_item_url = strpos( $menu_item->url, '#' ) ? substr( $menu_item->url, 0, strpos( $menu_item->url, '#' ) ) : $menu_item->url;
			$item_url = set_url_scheme( untrailingslashit( $raw_item_url ) );
			$_indexless_current = untrailingslashit( preg_replace( '/' . preg_quote( $mcms_rewrite->index, '/' ) . '$/', '', $current_url ) );

			if ( $raw_item_url && in_array( $item_url, array( $current_url, $_indexless_current, $_root_relative_current ) ) ) {
				$classes[] = 'current-menu-item';
				$menu_items[$key]->current = true;
				$_anc_id = (int) $menu_item->db_id;

				while(
					( $_anc_id = get_post_meta( $_anc_id, '_menu_item_menu_item_parent', true ) ) &&
					! in_array( $_anc_id, $active_ancestor_item_ids )
				) {
					$active_ancestor_item_ids[] = $_anc_id;
				}

				if ( in_array( home_url(), array( untrailingslashit( $current_url ), untrailingslashit( $_indexless_current ) ) ) ) {
					// Back compat for home link to match mcms_page_menu()
					$classes[] = 'current_page_item';
				}
				$active_parent_item_ids[] = (int) $menu_item->menu_item_parent;
				$active_parent_object_ids[] = (int) $menu_item->post_parent;
				$active_object = $menu_item->object;

			// give front page item current-menu-item class when extra query arguments involved
			} elseif ( $item_url == $front_page_url && is_front_page() ) {
				$classes[] = 'current-menu-item';
			}

			if ( untrailingslashit($item_url) == home_url() )
				$classes[] = 'menu-item-home';
		}

		// back-compat with mcms_page_menu: add "current_page_parent" to static home page link for any non-page query
		if ( ! empty( $home_page_id ) && 'post_type' == $menu_item->type && empty( $mcms_query->is_page ) && $home_page_id == $menu_item->object_id )
			$classes[] = 'current_page_parent';

		$menu_items[$key]->classes = array_unique( $classes );
	}
	$active_ancestor_item_ids = array_filter( array_unique( $active_ancestor_item_ids ) );
	$active_parent_item_ids = array_filter( array_unique( $active_parent_item_ids ) );
	$active_parent_object_ids = array_filter( array_unique( $active_parent_object_ids ) );

	// set parent's class
	foreach ( (array) $menu_items as $key => $parent_item ) {
		$classes = (array) $parent_item->classes;
		$menu_items[$key]->current_item_ancestor = false;
		$menu_items[$key]->current_item_parent = false;

		if (
			isset( $parent_item->type ) &&
			(
				// ancestral post object
				(
					'post_type' == $parent_item->type &&
					! empty( $queried_object->post_type ) &&
					is_post_type_hierarchical( $queried_object->post_type ) &&
					in_array( $parent_item->object_id, $queried_object->ancestors ) &&
					$parent_item->object != $queried_object->ID
				) ||

				// ancestral term
				(
					'taxonomy' == $parent_item->type &&
					isset( $possible_taxonomy_ancestors[ $parent_item->object ] ) &&
					in_array( $parent_item->object_id, $possible_taxonomy_ancestors[ $parent_item->object ] ) &&
					(
						! isset( $queried_object->term_id ) ||
						$parent_item->object_id != $queried_object->term_id
					)
				)
			)
		) {
			$classes[] = empty( $queried_object->taxonomy ) ? 'current-' . $queried_object->post_type . '-ancestor' : 'current-' . $queried_object->taxonomy . '-ancestor';
		}

		if ( in_array(  intval( $parent_item->db_id ), $active_ancestor_item_ids ) ) {
			$classes[] = 'current-menu-ancestor';
			$menu_items[$key]->current_item_ancestor = true;
		}
		if ( in_array( $parent_item->db_id, $active_parent_item_ids ) ) {
			$classes[] = 'current-menu-parent';
			$menu_items[$key]->current_item_parent = true;
		}
		if ( in_array( $parent_item->object_id, $active_parent_object_ids ) )
			$classes[] = 'current-' . $active_object . '-parent';

		if ( 'post_type' == $parent_item->type && 'page' == $parent_item->object ) {
			// Back compat classes for pages to match mcms_page_menu()
			if ( in_array('current-menu-parent', $classes) )
				$classes[] = 'current_page_parent';
			if ( in_array('current-menu-ancestor', $classes) )
				$classes[] = 'current_page_ancestor';
		}

		$menu_items[$key]->classes = array_unique( $classes );
	}
}

/**
 * Retrieve the HTML list content for nav menu items.
 *
 * @uses Walker_Nav_Menu to create HTML list content.
 * @since 3.0.0
 *
 * @param array    $items The menu items, sorted by each menu item's menu order.
 * @param int      $depth Depth of the item in reference to parents.
 * @param stdClass $r     An object containing mcms_nav_menu() arguments.
 * @return string The HTML list content for the menu items.
 */
function walk_nav_menu_tree( $items, $depth, $r ) {
	$walker = ( empty($r->walker) ) ? new Walker_Nav_Menu : $r->walker;
	$args = array( $items, $depth, $r );

	return call_user_func_array( array( $walker, 'walk' ), $args );
}

/**
 * Prevents a menu item ID from being used more than once.
 *
 * @since 3.0.1
 * @access private
 *
 * @staticvar array $used_ids
 * @param string $id
 * @param object $item
 * @return string
 */
function _nav_menu_item_id_use_once( $id, $item ) {
	static $_used_ids = array();
	if ( in_array( $item->ID, $_used_ids ) ) {
		return '';
	}
	$_used_ids[] = $item->ID;
	return $id;
}
