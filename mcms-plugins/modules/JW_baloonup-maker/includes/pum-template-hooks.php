<?php

/**
 * @since 1.4 hooks & filters
 */
add_filter( 'pum_baloonup_content', array( $GLOBALS['mcms_embed'], 'run_shortcode' ), 8 );
add_filter( 'pum_baloonup_content', array( $GLOBALS['mcms_embed'], 'autoembed' ), 8 );
add_filter( 'pum_baloonup_content', 'mcmstexturize', 10 );
add_filter( 'pum_baloonup_content', 'convert_smilies', 10 );
add_filter( 'pum_baloonup_content', 'convert_chars', 10 );
add_filter( 'pum_baloonup_content', 'mcmsautop', 10 );
add_filter( 'pum_baloonup_content', 'shortcode_unautop', 10 );
add_filter( 'pum_baloonup_content', 'prepend_attachment', 10 );
add_filter( 'pum_baloonup_content', 'force_balance_tags', 10 );
add_filter( 'pum_baloonup_content', 'do_shortcode', 11 );
add_filter( 'pum_baloonup_content', 'capital_P_dangit', 11 );
