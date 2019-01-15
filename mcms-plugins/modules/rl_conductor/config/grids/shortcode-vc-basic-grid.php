<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

require_once( 'class-vc-grids-common.php' );
$gridParams = VcGridsCommon::getBasicAtts();

return array(
	'name' => __( 'Post Grid', 'rl_conductor' ),
	'base' => 'vc_basic_grid',
	'icon' => 'icon-mcmsb-application-icon-large',
	'category' => __( 'Content', 'rl_conductor' ),
	'description' => __( 'Posts, pages or custom posts in grid', 'rl_conductor' ),
	'params' => $gridParams,
);
