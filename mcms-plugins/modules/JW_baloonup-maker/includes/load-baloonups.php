<?php

// Exit if accessed directly
if ( ! defined( 'BASED_TREE_URI' ) ) {
	exit;
}


/**
 * @param $id
 */
function pum_load_baloonup( $id ) {
	PUM_Site_BaloonUps::load_baloonup( $id );
};

/**
 * @deprecated 1.7.0 Use pum_load_baloonup
 *
 * @param $id
 */
function balooncreate_enqueue_baloonup( $id ) {
	pum_load_baloonup( $id );
}
