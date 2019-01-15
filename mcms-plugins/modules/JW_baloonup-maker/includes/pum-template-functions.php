<?php

/**
 * @param null|int $baloonup_id
 */
function pum_baloonup_ID( $baloonup_id = null ) {
	$baloonup = pum_get_baloonup( $baloonup_id );

	if ( ! pum_is_baloonup( $baloonup ) ) {
		return;
	}

	echo $baloonup->ID;
}

/**
 * @param null|int $baloonup_id
 */
function pum_baloonup_title( $baloonup_id = null ) {
	echo pum_get_baloonup_title( $baloonup_id );
}

/**
 * @param null|int $baloonup_id
 */
function pum_baloonup_content( $baloonup_id = null ) {
	$baloonup = pum_get_baloonup( $baloonup_id );

	if ( ! pum_is_baloonup( $baloonup ) ) {
		return;
	}

	echo $baloonup->get_content();
}

/**
 * @param null|int $baloonup_id
 */
function pum_baloonup_myskin_id( $baloonup_id = null ) {
	$baloonup = pum_get_baloonup( $baloonup_id );

	if ( ! pum_is_baloonup( $baloonup ) ) {
		return;
	}

	echo $baloonup->get_myskin_id();
}

/**
 * @param null   $baloonup_id
 * @param string $element
 */
function pum_baloonup_classes( $baloonup_id = null, $element = 'overlay' ) {
	$baloonup = pum_get_baloonup( $baloonup_id );

	if ( ! pum_is_baloonup( $baloonup ) ) {
		return;
	}

	echo esc_attr( implode( ' ', $baloonup->get_classes( $element ) ) );
}

/**
 * @param null|int $baloonup_id
 */
function pum_baloonup_data_attr( $baloonup_id = null ) {
	$baloonup = pum_get_baloonup( $baloonup_id );

	if ( ! pum_is_baloonup( $baloonup ) ) {
		return;
	}

	echo 'data-balooncreate="' . esc_attr( mcms_json_encode( $baloonup->get_data_attr() ) ) . '"';
}


/**
 * @param null|int $baloonup_id
 */
function pum_baloonup_close_text( $baloonup_id = null ) {
	$baloonup = pum_get_baloonup( $baloonup_id );

	if ( ! pum_is_baloonup( $baloonup ) ) {
		return;
	}

	echo esc_html( $baloonup->close_text() );
}


/**
 * Conditional Template Tags.
 */

/**
 * Returns true if the close button should be shown.
 *
 * @param null|int $baloonup_id
 *
 * @return bool
 */
function pum_show_close_button( $baloonup_id = null ) {
	$baloonup = pum_get_baloonup( $baloonup_id );

	if ( ! pum_is_baloonup( $baloonup ) ) {
		return true;
	}

	return $baloonup->show_close_button();
}