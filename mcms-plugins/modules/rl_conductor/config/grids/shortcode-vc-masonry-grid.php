<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

require_once( 'class-vc-grids-common.php' );
$masonryGridParams = VcGridsCommon::getMasonryCommonAtts();

return array(
	'name' => __( 'Post Masonry Grid', 'rl_conductor' ),
	'base' => 'vc_masonry_grid',
	'icon' => 'vc_icon-vc-masonry-grid',
	'category' => __( 'Content', 'rl_conductor' ),
	'description' => __( 'Posts, pages or custom posts in masonry grid', 'rl_conductor' ),
	'params' => $masonryGridParams,
);
