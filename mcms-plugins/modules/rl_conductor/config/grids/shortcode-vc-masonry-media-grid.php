<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

require_once( 'class-vc-grids-common.php' );
$masonryMediaGridParams = VcGridsCommon::getMasonryMediaCommonAtts();

return array(
	'name' => __( 'Masonry Media Grid', 'rl_conductor' ),
	'base' => 'vc_masonry_media_grid',
	'icon' => 'vc_icon-vc-masonry-media-grid',
	'category' => __( 'Content', 'rl_conductor' ),
	'description' => __( 'Masonry media grid from Media Library', 'rl_conductor' ),
	'params' => $masonryMediaGridParams,
);
