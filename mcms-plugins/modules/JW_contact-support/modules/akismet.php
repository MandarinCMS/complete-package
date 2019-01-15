<?php
/**
** Akismet Filter
** Akismet API: http://akismet.com/development/api/
**/

add_filter( 'mcmscf7_spam', 'mcmscf7_akismet' );

function mcmscf7_akismet( $spam ) {
	if ( $spam ) {
		return $spam;
	}

	if ( ! mcmscf7_akismet_is_available() ) {
		return false;
	}

	if ( ! $params = mcmscf7_akismet_submitted_params() ) {
		return false;
	}

	$c = array();

	$c['comment_author'] = $params['author'];
	$c['comment_author_email'] = $params['author_email'];
	$c['comment_author_url'] = $params['author_url'];
	$c['comment_content'] = $params['content'];

	$c['blog'] = get_option( 'home' );
	$c['blog_lang'] = get_locale();
	$c['blog_charset'] = get_option( 'blog_charset' );
	$c['user_ip'] = $_SERVER['REMOTE_ADDR'];
	$c['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
	$c['referrer'] = $_SERVER['HTTP_REFERER'];

	// http://blog.akismet.com/2012/06/19/pro-tip-tell-us-your-comment_type/
	$c['comment_type'] = 'contact-support';

	if ( $permalink = get_permalink() ) {
		$c['permalink'] = $permalink;
	}

	$ignore = array( 'HTTP_COOKIE', 'HTTP_COOKIE2', 'PHP_AUTH_PW' );

	foreach ( $_SERVER as $key => $value ) {
		if ( ! in_array( $key, (array) $ignore ) ) {
			$c["$key"] = $value;
		}
	}

	return mcmscf7_akismet_comment_check( $c );
}

function mcmscf7_akismet_is_available() {
	if ( is_callable( array( 'Akismet', 'get_api_key' ) ) ) {
		return (bool) Akismet::get_api_key();
	}

	return false;
}

function mcmscf7_akismet_submitted_params() {
	$params = array(
		'author' => '',
		'author_email' => '',
		'author_url' => '',
		'content' => '',
	);

	$has_akismet_option = false;

	foreach ( (array) $_POST as $key => $val ) {
		if ( '_mcmscf7' == substr( $key, 0, 6 ) || '_mcmsnonce' == $key ) {
			continue;
		}

		if ( is_array( $val ) ) {
			$val = implode( ', ', mcmscf7_array_flatten( $val ) );
		}

		$val = trim( $val );

		if ( 0 == strlen( $val ) ) {
			continue;
		}

		if ( $tags = mcmscf7_scan_form_tags( array( 'name' => $key ) ) ) {
			$tag = $tags[0];

			$akismet = $tag->get_option( 'akismet',
				'(author|author_email|author_url)', true );

			if ( $akismet ) {
				$has_akismet_option = true;

				if ( 'author' == $akismet ) {
					$params[$akismet] = trim( $params[$akismet] . ' ' . $val );
					continue;
				} elseif ( '' == $params[$akismet] ) {
					$params[$akismet] = $val;
					continue;
				}
			}
		}

		$params['content'] .= "\n\n" . $val;
	}

	if ( ! $has_akismet_option ) {
		return false;
	}

	$params['content'] = trim( $params['content'] );

	return $params;
}

function mcmscf7_akismet_comment_check( $comment ) {
	$spam = false;
	$query_string = mcmscf7_build_query( $comment );

	if ( is_callable( array( 'Akismet', 'http_post' ) ) ) {
		$response = Akismet::http_post( $query_string, 'comment-check' );
	} else {
		return $spam;
	}

	if ( 'true' == $response[1] ) {
		$spam = true;
	}

	if ( $submission = MCMSCF7_Submission::get_instance() ) {
		$submission->akismet = array( 'comment' => $comment, 'spam' => $spam );
	}

	return apply_filters( 'mcmscf7_akismet_comment_check', $spam, $comment );
}
