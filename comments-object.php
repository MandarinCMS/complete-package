<?php
/**
 * Handles Comment Post to MandarinCMS and prevents duplicate comment posting.
 *
 * @package MandarinCMS
 */

if ( 'POST' != $_SERVER['REQUEST_METHOD'] ) {
	$protocol = $_SERVER['SERVER_PROTOCOL'];
	if ( ! in_array( $protocol, array( 'HTTP/1.1', 'HTTP/2', 'HTTP/2.0' ) ) ) {
		$protocol = 'HTTP/1.0';
	}

	header('Allow: POST');
	header("$protocol 405 Method Not Allowed");
	header('Content-Type: text/plain');
	exit;
}

/** Sets up the MandarinCMS Environment. */
require( dirname(__FILE__) . '/bootstrap.php' );

nocache_headers();

$comment = mcms_handle_comment_submission( mcms_unslash( $_POST ) );
if ( is_mcms_error( $comment ) ) {
	$data = intval( $comment->get_error_data() );
	if ( ! empty( $data ) ) {
		mcms_die( '<p>' . $comment->get_error_message() . '</p>', __( 'Comment Submission Failure' ), array( 'response' => $data, 'back_link' => true ) );
	} else {
		exit;
	}
}

$user = mcms_get_current_user();
$cookies_consent = ( isset( $_POST['mcms-comment-cookies-consent'] ) );

/**
 * Perform other actions when comment cookies are set.
 *
 * @since 3.4.0
 * @since 4.9.6 The `$cookies_consent` parameter was added.
 *
 * @param MCMS_Comment $comment         Comment object.
 * @param MCMS_User    $user            Comment author's user object. The user may not exist.
 * @param boolean    $cookies_consent Comment author's consent to store cookies.
 */
do_action( 'set_comment_cookies', $comment, $user, $cookies_consent );

$location = empty( $_POST['redirect_to'] ) ? get_comment_link( $comment ) : $_POST['redirect_to'] . '#comment-' . $comment->comment_ID;

/**
 * Filters the location URI to send the commenter after posting.
 *
 * @since 2.0.5
 *
 * @param string     $location The 'redirect_to' URI sent via $_POST.
 * @param MCMS_Comment $comment  Comment object.
 */
$location = apply_filters( 'comment_post_redirect', $location, $comment );

mcms_safe_redirect( $location );
exit;
