<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

require_once( 'class-vc-grids-common.php' );
$mediaGridParams = VcGridsCommon::getMediaCommonAtts();

return array(
	'name' => __( 'Media Grid', 'rl_conductor' ),
	'base' => 'vc_media_grid',
	'icon' => 'vc_icon-vc-media-grid',
	'category' => __( 'Content', 'rl_conductor' ),
	'description' => __( 'Media grid from Media Library', 'rl_conductor' ),
	'params' => $mediaGridParams,
);
