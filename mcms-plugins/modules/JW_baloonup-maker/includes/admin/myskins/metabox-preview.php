<?php

// Exit if accessed directly
if ( ! defined( 'BASED_TREE_URI' ) ) {
	exit;
}

add_action( 'balooncreate_example_baloonup_content', 'balooncreate_default_example_baloonup_content', 1 );
function balooncreate_default_example_baloonup_content() {
	echo balooncreate_get_default_example_baloonup_content();
}

function balooncreate_get_default_example_baloonup_content() {
	return '<p>Suspendisse ipsum eros, tincidunt sed commodo ut, viverra vitae ipsum. Etiam non porta neque. Pellentesque nulla elit, aliquam in ullamcorper at, bibendum sed eros. Morbi non sapien tellus, ac vestibulum eros. In hac habitasse platea dictumst. Nulla vestibulum, diam vel porttitor placerat, eros tortor ultrices lectus, eget faucibus arcu justo eget massa. Maecenas id tellus vitae justo posuere hendrerit aliquet ut dolor.</p>';
}