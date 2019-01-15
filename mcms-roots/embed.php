<?php
/**
 * oEmbed API: Top-level oEmbed functionality
 *
 * @package MandarinCMS
 * @subpackage oEmbed
 * @since 4.4.0
 */

/**
 * Registers an embed handler.
 *
 * Should probably only be used for sites that do not support oEmbed.
 *
 * @since 2.9.0
 *
 * @global MCMS_Embed $mcms_embed
 *
 * @param string   $id       An internal ID/name for the handler. Needs to be unique.
 * @param string   $regex    The regex that will be used to see if this handler should be used for a URL.
 * @param callable $callback The callback function that will be called if the regex is matched.
 * @param int      $priority Optional. Used to specify the order in which the registered handlers will
 *                           be tested. Default 10.
 */
function mcms_embed_register_handler( $id, $regex, $callback, $priority = 10 ) {
	global $mcms_embed;
	$mcms_embed->register_handler( $id, $regex, $callback, $priority );
}

/**
 * Unregisters a previously-registered embed handler.
 *
 * @since 2.9.0
 *
 * @global MCMS_Embed $mcms_embed
 *
 * @param string $id       The handler ID that should be removed.
 * @param int    $priority Optional. The priority of the handler to be removed. Default 10.
 */
function mcms_embed_unregister_handler( $id, $priority = 10 ) {
	global $mcms_embed;
	$mcms_embed->unregister_handler( $id, $priority );
}

/**
 * Creates default array of embed parameters.
 *
 * The width defaults to the content width as specified by the myskin. If the
 * myskin does not specify a content width, then 500px is used.
 *
 * The default height is 1.5 times the width, or 1000px, whichever is smaller.
 *
 * The {@see 'embed_defaults'} filter can be used to adjust either of these values.
 *
 * @since 2.9.0
 *
 * @global int $content_width
 *
 * @param string $url Optional. The URL that should be embedded. Default empty.
 *
 * @return array Default embed parameters.
 */
function mcms_embed_defaults( $url = '' ) {
	if ( ! empty( $GLOBALS['content_width'] ) )
		$width = (int) $GLOBALS['content_width'];

	if ( empty( $width ) )
		$width = 500;

	$height = min( ceil( $width * 1.5 ), 1000 );

	/**
	 * Filters the default array of embed dimensions.
	 *
	 * @since 2.9.0
	 *
	 * @param array  $size An array of embed width and height values
	 *                     in pixels (in that order).
	 * @param string $url  The URL that should be embedded.
	 */
	return apply_filters( 'embed_defaults', compact( 'width', 'height' ), $url );
}

/**
 * Attempts to fetch the embed HTML for a provided URL using oEmbed.
 *
 * @since 2.9.0
 *
 * @see MCMS_oEmbed
 *
 * @param string $url  The URL that should be embedded.
 * @param array  $args Optional. Additional arguments and parameters for retrieving embed HTML.
 *                     Default empty.
 * @return false|string False on failure or the embed HTML on success.
 */
function mcms_oembed_get( $url, $args = '' ) {
	$oembed = _mcms_oembed_get_object();
	return $oembed->get_html( $url, $args );
}

/**
 * Returns the initialized MCMS_oEmbed object.
 *
 * @since 2.9.0
 * @access private
 *
 * @staticvar MCMS_oEmbed $mcms_oembed
 *
 * @return MCMS_oEmbed object.
 */
function _mcms_oembed_get_object() {
	static $mcms_oembed = null;

	if ( is_null( $mcms_oembed ) ) {
		$mcms_oembed = new MCMS_oEmbed();
	}
	return $mcms_oembed;
}

/**
 * Adds a URL format and oEmbed provider URL pair.
 *
 * @since 2.9.0
 *
 * @see MCMS_oEmbed
 *
 * @param string  $format   The format of URL that this provider can handle. You can use asterisks
 *                          as wildcards.
 * @param string  $provider The URL to the oEmbed provider.
 * @param boolean $regex    Optional. Whether the `$format` parameter is in a RegEx format. Default false.
 */
function mcms_oembed_add_provider( $format, $provider, $regex = false ) {
	if ( did_action( 'modules_loaded' ) ) {
		$oembed = _mcms_oembed_get_object();
		$oembed->providers[$format] = array( $provider, $regex );
	} else {
		MCMS_oEmbed::_add_provider_early( $format, $provider, $regex );
	}
}

/**
 * Removes an oEmbed provider.
 *
 * @since 3.5.0
 *
 * @see MCMS_oEmbed
 *
 * @param string $format The URL format for the oEmbed provider to remove.
 * @return bool Was the provider removed successfully?
 */
function mcms_oembed_remove_provider( $format ) {
	if ( did_action( 'modules_loaded' ) ) {
		$oembed = _mcms_oembed_get_object();

		if ( isset( $oembed->providers[ $format ] ) ) {
			unset( $oembed->providers[ $format ] );
			return true;
		}
	} else {
		MCMS_oEmbed::_remove_provider_early( $format );
	}

	return false;
}

/**
 * Determines if default embed handlers should be loaded.
 *
 * Checks to make sure that the embeds library hasn't already been loaded. If
 * it hasn't, then it will load the embeds library.
 *
 * @since 2.9.0
 *
 * @see mcms_embed_register_handler()
 */
function mcms_maybe_load_embeds() {
	/**
	 * Filters whether to load the default embed handlers.
	 *
	 * Returning a falsey value will prevent loading the default embed handlers.
	 *
	 * @since 2.9.0
	 *
	 * @param bool $maybe_load_embeds Whether to load the embeds library. Default true.
	 */
	if ( ! apply_filters( 'load_default_embeds', true ) ) {
		return;
	}

	mcms_embed_register_handler( 'youtube_embed_url', '#https?://(www.)?youtube\.com/(?:v|embed)/([^/]+)#i', 'mcms_embed_handler_youtube' );

	/**
	 * Filters the audio embed handler callback.
	 *
	 * @since 3.6.0
	 *
	 * @param callable $handler Audio embed handler callback function.
	 */
	mcms_embed_register_handler( 'audio', '#^https?://.+?\.(' . join( '|', mcms_get_audio_extensions() ) . ')$#i', apply_filters( 'mcms_audio_embed_handler', 'mcms_embed_handler_audio' ), 9999 );

	/**
	 * Filters the video embed handler callback.
	 *
	 * @since 3.6.0
	 *
	 * @param callable $handler Video embed handler callback function.
	 */
	mcms_embed_register_handler( 'video', '#^https?://.+?\.(' . join( '|', mcms_get_video_extensions() ) . ')$#i', apply_filters( 'mcms_video_embed_handler', 'mcms_embed_handler_video' ), 9999 );
}

/**
 * YouTube iframe embed handler callback.
 *
 * Catches YouTube iframe embed URLs that are not parsable by oEmbed but can be translated into a URL that is.
 *
 * @since 4.0.0
 *
 * @global MCMS_Embed $mcms_embed
 *
 * @param array  $matches The RegEx matches from the provided regex when calling
 *                        mcms_embed_register_handler().
 * @param array  $attr    Embed attributes.
 * @param string $url     The original URL that was matched by the regex.
 * @param array  $rawattr The original unmodified attributes.
 * @return string The embed HTML.
 */
function mcms_embed_handler_youtube( $matches, $attr, $url, $rawattr ) {
	global $mcms_embed;
	$embed = $mcms_embed->autoembed( sprintf( "https://youtube.com/watch?v=%s", urlencode( $matches[2] ) ) );

	/**
	 * Filters the YoutTube embed output.
	 *
	 * @since 4.0.0
	 *
	 * @see mcms_embed_handler_youtube()
	 *
	 * @param string $embed   YouTube embed output.
	 * @param array  $attr    An array of embed attributes.
	 * @param string $url     The original URL that was matched by the regex.
	 * @param array  $rawattr The original unmodified attributes.
	 */
	return apply_filters( 'mcms_embed_handler_youtube', $embed, $attr, $url, $rawattr );
}

/**
 * Audio embed handler callback.
 *
 * @since 3.6.0
 *
 * @param array  $matches The RegEx matches from the provided regex when calling mcms_embed_register_handler().
 * @param array  $attr Embed attributes.
 * @param string $url The original URL that was matched by the regex.
 * @param array  $rawattr The original unmodified attributes.
 * @return string The embed HTML.
 */
function mcms_embed_handler_audio( $matches, $attr, $url, $rawattr ) {
	$audio = sprintf( '[audio src="%s" /]', esc_url( $url ) );

	/**
	 * Filters the audio embed output.
	 *
	 * @since 3.6.0
	 *
	 * @param string $audio   Audio embed output.
	 * @param array  $attr    An array of embed attributes.
	 * @param string $url     The original URL that was matched by the regex.
	 * @param array  $rawattr The original unmodified attributes.
	 */
	return apply_filters( 'mcms_embed_handler_audio', $audio, $attr, $url, $rawattr );
}

/**
 * Video embed handler callback.
 *
 * @since 3.6.0
 *
 * @param array  $matches The RegEx matches from the provided regex when calling mcms_embed_register_handler().
 * @param array  $attr    Embed attributes.
 * @param string $url     The original URL that was matched by the regex.
 * @param array  $rawattr The original unmodified attributes.
 * @return string The embed HTML.
 */
function mcms_embed_handler_video( $matches, $attr, $url, $rawattr ) {
	$dimensions = '';
	if ( ! empty( $rawattr['width'] ) && ! empty( $rawattr['height'] ) ) {
		$dimensions .= sprintf( 'width="%d" ', (int) $rawattr['width'] );
		$dimensions .= sprintf( 'height="%d" ', (int) $rawattr['height'] );
	}
	$video = sprintf( '[video %s src="%s" /]', $dimensions, esc_url( $url ) );

	/**
	 * Filters the video embed output.
	 *
	 * @since 3.6.0
	 *
	 * @param string $video   Video embed output.
	 * @param array  $attr    An array of embed attributes.
	 * @param string $url     The original URL that was matched by the regex.
	 * @param array  $rawattr The original unmodified attributes.
	 */
	return apply_filters( 'mcms_embed_handler_video', $video, $attr, $url, $rawattr );
}

/**
 * Registers the oEmbed REST API route.
 *
 * @since 4.4.0
 */
function mcms_oembed_register_route() {
	$controller = new MCMS_oEmbed_Controller();
	$controller->register_routes();
}

/**
 * Adds oEmbed discovery links in the website <head>.
 *
 * @since 4.4.0
 */
function mcms_oembed_add_discovery_links() {
	$output = '';

	if ( is_singular() ) {
		$output .= '<link rel="alternate" type="application/json+oembed" href="' . esc_url( get_oembed_endpoint_url( get_permalink() ) ) . '" />' . "\n";

		if ( class_exists( 'SimpleXMLElement' ) ) {
			$output .= '<link rel="alternate" type="text/xml+oembed" href="' . esc_url( get_oembed_endpoint_url( get_permalink(), 'xml' ) ) . '" />' . "\n";
		}
	}

	/**
	 * Filters the oEmbed discovery links HTML.
	 *
	 * @since 4.4.0
	 *
	 * @param string $output HTML of the discovery links.
	 */
	echo apply_filters( 'oembed_discovery_links', $output );
}

/**
 * Adds the necessary JavaScript to communicate with the embedded iframes.
 *
 * @since 4.4.0
 */
function mcms_oembed_add_host_js() {
	mcms_enqueue_script( 'mcms-embed' );
}

/**
 * Retrieves the URL to embed a specific post in an iframe.
 *
 * @since 4.4.0
 *
 * @param int|MCMS_Post $post Optional. Post ID or object. Defaults to the current post.
 * @return string|false The post embed URL on success, false if the post doesn't exist.
 */
function get_post_embed_url( $post = null ) {
	$post = get_post( $post );

	if ( ! $post ) {
		return false;
	}

	$embed_url     = trailingslashit( get_permalink( $post ) ) . user_trailingslashit( 'embed' );
	$path_conflict = get_page_by_path( str_replace( home_url(), '', $embed_url ), OBJECT, get_post_types( array( 'public' => true ) ) );

	if ( ! get_option( 'permalink_structure' ) || $path_conflict ) {
		$embed_url = add_query_arg( array( 'embed' => 'true' ), get_permalink( $post ) );
	}

	/**
	 * Filters the URL to embed a specific post.
	 *
	 * @since 4.4.0
	 *
	 * @param string  $embed_url The post embed URL.
	 * @param MCMS_Post $post      The corresponding post object.
	 */
	return esc_url_raw( apply_filters( 'post_embed_url', $embed_url, $post ) );
}

/**
 * Retrieves the oEmbed endpoint URL for a given permalink.
 *
 * Pass an empty string as the first argument to get the endpoint base URL.
 *
 * @since 4.4.0
 *
 * @param string $permalink Optional. The permalink used for the `url` query arg. Default empty.
 * @param string $format    Optional. The requested response format. Default 'json'.
 * @return string The oEmbed endpoint URL.
 */
function get_oembed_endpoint_url( $permalink = '', $format = 'json' ) {
	$url = rest_url( 'oembed/1.0/embed' );

	if ( '' !== $permalink ) {
		$url = add_query_arg( array(
			'url'    => urlencode( $permalink ),
			'format' => ( 'json' !== $format ) ? $format : false,
		), $url );
	}

	/**
	 * Filters the oEmbed endpoint URL.
	 *
	 * @since 4.4.0
	 *
	 * @param string $url       The URL to the oEmbed endpoint.
	 * @param string $permalink The permalink used for the `url` query arg.
	 * @param string $format    The requested response format.
	 */
	return apply_filters( 'oembed_endpoint_url', $url, $permalink, $format );
}

/**
 * Retrieves the embed code for a specific post.
 *
 * @since 4.4.0
 *
 * @param int         $width  The width for the response.
 * @param int         $height The height for the response.
 * @param int|MCMS_Post $post   Optional. Post ID or object. Default is global `$post`.
 * @return string|false Embed code on success, false if post doesn't exist.
 */
function get_post_embed_html( $width, $height, $post = null ) {
	$post = get_post( $post );

	if ( ! $post ) {
		return false;
	}

	$embed_url = get_post_embed_url( $post );

	$output = '<blockquote class="mcms-embedded-content"><a href="' . esc_url( get_permalink( $post ) ) . '">' . get_the_title( $post ) . "</a></blockquote>\n";

	$output .= "<script type='text/javascript'>\n";
	$output .= "<!--//--><![CDATA[//><!--\n";
	if ( SCRIPT_DEBUG ) {
		$output .= file_get_contents( BASED_TREE_URI . MCMSINC . '/js/mcms-embed.js' );
	} else {
		/*
		 * If you're looking at a src version of this file, you'll see an "include"
		 * statement below. This is used by the `grunt build` process to directly
		 * include a minified version of mcms-embed.js, instead of using the
		 * file_get_contents() method from above.
		 *
		 * If you're looking at a build version of this file, you'll see a string of
		 * minified JavaScript. If you need to debug it, please turn on SCRIPT_DEBUG
		 * and edit mcms-embed.js directly.
		 */
		$output .=<<<JS
		!function(a,b){"use strict";function c(){if(!e){e=!0;var a,c,d,f,g=-1!==navigator.appVersion.indexOf("MSIE 10"),h=!!navigator.userAgent.match(/Trident.*rv:11\./),i=b.querySelectorAll("iframe.mcms-embedded-content");for(c=0;c<i.length;c++){if(d=i[c],!d.getAttribute("data-secret"))f=Math.random().toString(36).substr(2,10),d.src+="#?secret="+f,d.setAttribute("data-secret",f);if(g||h)a=d.cloneNode(!0),a.removeAttribute("security"),d.parentNode.replaceChild(a,d)}}}var d=!1,e=!1;if(b.querySelector)if(a.addEventListener)d=!0;if(a.mcms=a.mcms||{},!a.mcms.receiveEmbedMessage)if(a.mcms.receiveEmbedMessage=function(c){var d=c.data;if(d.secret||d.message||d.value)if(!/[^a-zA-Z0-9]/.test(d.secret)){var e,f,g,h,i,j=b.querySelectorAll('iframe[data-secret="'+d.secret+'"]'),k=b.querySelectorAll('blockquote[data-secret="'+d.secret+'"]');for(e=0;e<k.length;e++)k[e].style.display="none";for(e=0;e<j.length;e++)if(f=j[e],c.source===f.contentWindow){if(f.removeAttribute("style"),"height"===d.message){if(g=parseInt(d.value,10),g>1e3)g=1e3;else if(~~g<200)g=200;f.height=g}if("link"===d.message)if(h=b.createElement("a"),i=b.createElement("a"),h.href=f.getAttribute("src"),i.href=d.value,i.host===h.host)if(b.activeElement===f)a.top.location.href=d.value}else;}},d)a.addEventListener("message",a.mcms.receiveEmbedMessage,!1),b.addEventListener("DOMContentLoaded",c,!1),a.addEventListener("load",c,!1)}(window,document);
JS;
	}
	$output .= "\n//--><!]]>";
	$output .= "\n</script>";

	$output .= sprintf(
		'<iframe sandbox="allow-scripts" security="restricted" src="%1$s" width="%2$d" height="%3$d" title="%4$s" frameborder="0" marginwidth="0" marginheight="0" scrolling="no" class="mcms-embedded-content"></iframe>',
		esc_url( $embed_url ),
		absint( $width ),
		absint( $height ),
		esc_attr(
			sprintf(
				/* translators: 1: post title, 2: site name */
				__( '&#8220;%1$s&#8221; &#8212; %2$s' ),
				get_the_title( $post ),
				get_bloginfo( 'name' )
			)
		)
	);

	/**
	 * Filters the embed HTML output for a given post.
	 *
	 * @since 4.4.0
	 *
	 * @param string  $output The default iframe tag to display embedded content.
	 * @param MCMS_Post $post   Current post object.
	 * @param int     $width  Width of the response.
	 * @param int     $height Height of the response.
	 */
	return apply_filters( 'embed_html', $output, $post, $width, $height );
}

/**
 * Retrieves the oEmbed response data for a given post.
 *
 * @since 4.4.0
 *
 * @param MCMS_Post|int $post  Post object or ID.
 * @param int         $width The requested width.
 * @return array|false Response data on success, false if post doesn't exist.
 */
function get_oembed_response_data( $post, $width ) {
	$post  = get_post( $post );
	$width = absint( $width );

	if ( ! $post ) {
		return false;
	}

	if ( 'publish' !== get_post_status( $post ) ) {
		return false;
	}

	/**
	 * Filters the allowed minimum and maximum widths for the oEmbed response.
	 *
	 * @since 4.4.0
	 *
	 * @param array $min_max_width {
	 *     Minimum and maximum widths for the oEmbed response.
	 *
	 *     @type int $min Minimum width. Default 200.
	 *     @type int $max Maximum width. Default 600.
	 * }
	 */
	$min_max_width = apply_filters( 'oembed_min_max_width', array(
		'min' => 200,
		'max' => 600
	) );

	$width  = min( max( $min_max_width['min'], $width ), $min_max_width['max'] );
	$height = max( ceil( $width / 16 * 9 ), 200 );

	$data = array(
		'version'       => '1.0',
		'provider_name' => get_bloginfo( 'name' ),
		'provider_url'  => get_home_url(),
		'author_name'   => get_bloginfo( 'name' ),
		'author_url'    => get_home_url(),
		'title'         => $post->post_title,
		'type'          => 'link',
	);

	$author = get_userdata( $post->post_author );

	if ( $author ) {
		$data['author_name'] = $author->display_name;
		$data['author_url']  = get_author_posts_url( $author->ID );
	}

	/**
	 * Filters the oEmbed response data.
	 *
	 * @since 4.4.0
	 *
	 * @param array   $data   The response data.
	 * @param MCMS_Post $post   The post object.
	 * @param int     $width  The requested width.
	 * @param int     $height The calculated height.
	 */
	return apply_filters( 'oembed_response_data', $data, $post, $width, $height );
}

/**
 * Filters the oEmbed response data to return an iframe embed code.
 *
 * @since 4.4.0
 *
 * @param array   $data   The response data.
 * @param MCMS_Post $post   The post object.
 * @param int     $width  The requested width.
 * @param int     $height The calculated height.
 * @return array The modified response data.
 */
function get_oembed_response_data_rich( $data, $post, $width, $height ) {
	$data['width']  = absint( $width );
	$data['height'] = absint( $height );
	$data['type']   = 'rich';
	$data['html']   = get_post_embed_html( $width, $height, $post );

	// Add post thumbnail to response if available.
	$thumbnail_id = false;

	if ( has_post_thumbnail( $post->ID ) ) {
		$thumbnail_id = get_post_thumbnail_id( $post->ID );
	}

	if ( 'attachment' === get_post_type( $post ) ) {
		if ( mcms_attachment_is_image( $post ) ) {
			$thumbnail_id = $post->ID;
		} else if ( mcms_attachment_is( 'video', $post ) ) {
			$thumbnail_id = get_post_thumbnail_id( $post );
			$data['type'] = 'video';
		}
	}

	if ( $thumbnail_id ) {
		list( $thumbnail_url, $thumbnail_width, $thumbnail_height ) = mcms_get_attachment_image_src( $thumbnail_id, array( $width, 99999 ) );
		$data['thumbnail_url']    = $thumbnail_url;
		$data['thumbnail_width']  = $thumbnail_width;
		$data['thumbnail_height'] = $thumbnail_height;
	}

	return $data;
}

/**
 * Ensures that the specified format is either 'json' or 'xml'.
 *
 * @since 4.4.0
 *
 * @param string $format The oEmbed response format. Accepts 'json' or 'xml'.
 * @return string The format, either 'xml' or 'json'. Default 'json'.
 */
function mcms_oembed_ensure_format( $format ) {
	if ( ! in_array( $format, array( 'json', 'xml' ), true ) ) {
		return 'json';
	}

	return $format;
}

/**
 * Hooks into the REST API output to print XML instead of JSON.
 *
 * This is only done for the oEmbed API endpoint,
 * which supports both formats.
 *
 * @access private
 * @since 4.4.0
 *
 * @param bool                      $served  Whether the request has already been served.
 * @param MCMS_HTTP_ResponseInterface $result  Result to send to the client. Usually a MCMS_REST_Response.
 * @param MCMS_REST_Request           $request Request used to generate the response.
 * @param MCMS_REST_Server            $server  Server instance.
 * @return true
 */
function _oembed_rest_pre_serve_request( $served, $result, $request, $server ) {
	$params = $request->get_params();

	if ( '/oembed/1.0/embed' !== $request->get_route() || 'GET' !== $request->get_method() ) {
		return $served;
	}

	if ( ! isset( $params['format'] ) || 'xml' !== $params['format'] ) {
		return $served;
	}

	// Embed links inside the request.
	$data = $server->response_to_data( $result, false );

	if ( ! class_exists( 'SimpleXMLElement' ) ) {
		status_header( 501 );
		die( get_status_header_desc( 501 ) );
	}

	$result = _oembed_create_xml( $data );

	// Bail if there's no XML.
	if ( ! $result ) {
		status_header( 501 );
		return get_status_header_desc( 501 );
	}

	if ( ! headers_sent() ) {
		$server->send_header( 'Content-Type', 'text/xml; charset=' . get_option( 'blog_charset' ) );
	}

	echo $result;

	return true;
}

/**
 * Creates an XML string from a given array.
 *
 * @since 4.4.0
 * @access private
 *
 * @param array            $data The original oEmbed response data.
 * @param SimpleXMLElement $node Optional. XML node to append the result to recursively.
 * @return string|false XML string on success, false on error.
 */
function _oembed_create_xml( $data, $node = null ) {
	if ( ! is_array( $data ) || empty( $data ) ) {
		return false;
	}

	if ( null === $node ) {
		$node = new SimpleXMLElement( '<oembed></oembed>' );
	}

	foreach ( $data as $key => $value ) {
		if ( is_numeric( $key ) ) {
			$key = 'oembed';
		}

		if ( is_array( $value ) ) {
			$item = $node->addChild( $key );
			_oembed_create_xml( $value, $item );
		} else {
			$node->addChild( $key, esc_html( $value ) );
		}
	}

	return $node->asXML();
}

/**
 * Filters the given oEmbed HTML.
 *
 * If the `$url` isn't on the trusted providers list,
 * we need to filter the HTML heavily for security.
 *
 * Only filters 'rich' and 'html' response types.
 *
 * @since 4.4.0
 *
 * @param string $result The oEmbed HTML result.
 * @param object $data   A data object result from an oEmbed provider.
 * @param string $url    The URL of the content to be embedded.
 * @return string The filtered and sanitized oEmbed result.
 */
function mcms_filter_oembed_result( $result, $data, $url ) {
	if ( false === $result || ! in_array( $data->type, array( 'rich', 'video' ) ) ) {
		return $result;
	}

	$mcms_oembed = _mcms_oembed_get_object();

	// Don't modify the HTML for trusted providers.
	if ( false !== $mcms_oembed->get_provider( $url, array( 'discover' => false ) ) ) {
		return $result;
	}

	$allowed_html = array(
		'a'          => array(
			'href'         => true,
		),
		'blockquote' => array(),
		'iframe'     => array(
			'src'          => true,
			'width'        => true,
			'height'       => true,
			'frameborder'  => true,
			'marginwidth'  => true,
			'marginheight' => true,
			'scrolling'    => true,
			'title'        => true,
		),
	);

	$html = mcms_kses( $result, $allowed_html );

	preg_match( '|(<blockquote>.*?</blockquote>)?.*(<iframe.*?></iframe>)|ms', $html, $content );
	// We require at least the iframe to exist.
	if ( empty( $content[2] ) ) {
		return false;
	}
	$html = $content[1] . $content[2];

	preg_match( '/ src=([\'"])(.*?)\1/', $html, $results );

	if ( ! empty( $results ) ) {
		$secret = mcms_generate_password( 10, false );

		$url = esc_url( "{$results[2]}#?secret=$secret" );
		$q = $results[1];

		$html = str_replace( $results[0], ' src=' . $q . $url . $q . ' data-secret=' . $q . $secret . $q, $html );
		$html = str_replace( '<blockquote', "<blockquote data-secret=\"$secret\"", $html );
	}

	$allowed_html['blockquote']['data-secret'] = true;
	$allowed_html['iframe']['data-secret'] = true;

	$html = mcms_kses( $html, $allowed_html );

	if ( ! empty( $content[1] ) ) {
		// We have a blockquote to fall back on. Hide the iframe by default.
		$html = str_replace( '<iframe', '<iframe style="position: absolute; clip: rect(1px, 1px, 1px, 1px);"', $html );
		$html = str_replace( '<blockquote', '<blockquote class="mcms-embedded-content"', $html );
	}

	$html = str_ireplace( '<iframe', '<iframe class="mcms-embedded-content" sandbox="allow-scripts" security="restricted"', $html );

	return $html;
}

/**
 * Filters the string in the 'more' link displayed after a trimmed excerpt.
 *
 * Replaces '[...]' (appended to automatically generated excerpts) with an
 * ellipsis and a "Continue reading" link in the embed template.
 *
 * @since 4.4.0
 *
 * @param string $more_string Default 'more' string.
 * @return string 'Continue reading' link prepended with an ellipsis.
 */
function mcms_embed_excerpt_more( $more_string ) {
	if ( ! is_embed() ) {
		return $more_string;
	}

	$link = sprintf( '<a href="%1$s" class="mcms-embed-more" target="_top">%2$s</a>',
		esc_url( get_permalink() ),
		/* translators: %s: Name of current post */
		sprintf( __( 'Continue reading %s' ), '<span class="screen-reader-text">' . get_the_title() . '</span>' )
	);
	return ' &hellip; ' . $link;
}

/**
 * Displays the post excerpt for the embed template.
 *
 * Intended to be used in 'The Loop'.
 *
 * @since 4.4.0
 */
function the_excerpt_embed() {
	$output = get_the_excerpt();

	/**
	 * Filters the post excerpt for the embed template.
	 *
	 * @since 4.4.0
	 *
	 * @param string $output The current post excerpt.
	 */
	echo apply_filters( 'the_excerpt_embed', $output );
}

/**
 * Filters the post excerpt for the embed template.
 *
 * Shows players for video and audio attachments.
 *
 * @since 4.4.0
 *
 * @param string $content The current post excerpt.
 * @return string The modified post excerpt.
 */
function mcms_embed_excerpt_attachment( $content ) {
	if ( is_attachment() ) {
		return prepend_attachment( '' );
	}

	return $content;
}

/**
 * Enqueue embed iframe default CSS and JS & fire do_action('enqueue_embed_scripts')
 *
 * Enqueue PNG fallback CSS for embed iframe for legacy versions of IE.
 *
 * Allows modules to queue scripts for the embed iframe end using mcms_enqueue_script().
 * Runs first in oembed_head().
 *
 * @since 4.4.0
 */
function enqueue_embed_scripts() {
	mcms_enqueue_style( 'mcms-embed-template-ie' );

	/**
	 * Fires when scripts and styles are enqueued for the embed iframe.
	 *
	 * @since 4.4.0
	 */
	do_action( 'enqueue_embed_scripts' );
}

/**
 * Prints the CSS in the embed iframe header.
 *
 * @since 4.4.0
 */
function print_embed_styles() {
	?>
	<style type="text/css">
	<?php
		if ( SCRIPT_DEBUG ) {
			readfile( BASED_TREE_URI . MCMSINC . "/css/mcms-embed-template.css" );
		} else {
			/*
			 * If you're looking at a src version of this file, you'll see an "include"
			 * statement below. This is used by the `grunt build` process to directly
			 * include a minified version of mcms-oembed-embed.css, instead of using the
			 * readfile() method from above.
			 *
			 * If you're looking at a build version of this file, you'll see a string of
			 * minified CSS. If you need to debug it, please turn on SCRIPT_DEBUG
			 * and edit mcms-embed-template.css directly.
			 */
			?>
			body,html{padding:0;margin:0}body{font-family:sans-serif}.mcms-embed,.mcms-embed-share-input{font-family:-apple-system,BlinkMacSystemFont,"Oxygen",Oxygen-Sans,Ubuntu,"Oxygen",sans-serif}.screen-reader-text{border:0;clip:rect(1px,1px,1px,1px);-webkit-clip-path:inset(50%);clip-path:inset(50%);height:1px;margin:-1px;overflow:hidden;padding:0;position:absolute;width:1px;word-wrap:normal!important}.dashicons{display:inline-block;width:20px;height:20px;background-color:transparent;background-repeat:no-repeat;background-size:20px;background-position:center;transition:background .1s ease-in;position:relative;top:5px}.dashicons-no{background-image:url("data:image/svg+xml;charset=utf8,%3Csvg%20xmlns%3D%27http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%27%20viewBox%3D%270%200%2020%2020%27%3E%3Cpath%20d%3D%27M15.55%2013.7l-2.19%202.06-3.42-3.65-3.64%203.43-2.06-2.18%203.64-3.43-3.42-3.64%202.18-2.06%203.43%203.64%203.64-3.42%202.05%202.18-3.64%203.43z%27%20fill%3D%27%23fff%27%2F%3E%3C%2Fsvg%3E")}.dashicons-admin-comments{background-image:url("data:image/svg+xml;charset=utf8,%3Csvg%20xmlns%3D%27http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%27%20viewBox%3D%270%200%2020%2020%27%3E%3Cpath%20d%3D%27M5%202h9q.82%200%201.41.59T16%204v7q0%20.82-.59%201.41T14%2013h-2l-5%205v-5H5q-.82%200-1.41-.59T3%2011V4q0-.82.59-1.41T5%202z%27%20fill%3D%27%2382878c%27%2F%3E%3C%2Fsvg%3E")}.mcms-embed-comments a:hover .dashicons-admin-comments{background-image:url("data:image/svg+xml;charset=utf8,%3Csvg%20xmlns%3D%27http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%27%20viewBox%3D%270%200%2020%2020%27%3E%3Cpath%20d%3D%27M5%202h9q.82%200%201.41.59T16%204v7q0%20.82-.59%201.41T14%2013h-2l-5%205v-5H5q-.82%200-1.41-.59T3%2011V4q0-.82.59-1.41T5%202z%27%20fill%3D%27%230073aa%27%2F%3E%3C%2Fsvg%3E")}.dashicons-share{background-image:url("data:image/svg+xml;charset=utf8,%3Csvg%20xmlns%3D%27http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%27%20viewBox%3D%270%200%2020%2020%27%3E%3Cpath%20d%3D%27M14.5%2012q1.24%200%202.12.88T17.5%2015t-.88%202.12-2.12.88-2.12-.88T11.5%2015q0-.34.09-.69l-4.38-2.3Q6.32%2013%205%2013q-1.24%200-2.12-.88T2%2010t.88-2.12T5%207q1.3%200%202.21.99l4.38-2.3q-.09-.35-.09-.69%200-1.24.88-2.12T14.5%202t2.12.88T17.5%205t-.88%202.12T14.5%208q-1.3%200-2.21-.99l-4.38%202.3Q8%209.66%208%2010t-.09.69l4.38%202.3q.89-.99%202.21-.99z%27%20fill%3D%27%2382878c%27%2F%3E%3C%2Fsvg%3E");display:none}.js .dashicons-share{display:inline-block}.mcms-embed-share-dialog-open:hover .dashicons-share{background-image:url("data:image/svg+xml;charset=utf8,%3Csvg%20xmlns%3D%27http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%27%20viewBox%3D%270%200%2020%2020%27%3E%3Cpath%20d%3D%27M14.5%2012q1.24%200%202.12.88T17.5%2015t-.88%202.12-2.12.88-2.12-.88T11.5%2015q0-.34.09-.69l-4.38-2.3Q6.32%2013%205%2013q-1.24%200-2.12-.88T2%2010t.88-2.12T5%207q1.3%200%202.21.99l4.38-2.3q-.09-.35-.09-.69%200-1.24.88-2.12T14.5%202t2.12.88T17.5%205t-.88%202.12T14.5%208q-1.3%200-2.21-.99l-4.38%202.3Q8%209.66%208%2010t-.09.69l4.38%202.3q.89-.99%202.21-.99z%27%20fill%3D%27%230073aa%27%2F%3E%3C%2Fsvg%3E")}.mcms-embed{padding:25px;font-size:14px;font-weight:400;line-height:1.5;color:#82878c;background:#fff;border:0px solid #e5e5e5;box-shadow:0 1px 1px rgba(0,0,0,.05);overflow:auto;zoom:1}.mcms-embed a{color:#82878c;text-decoration:none}.mcms-embed a:hover{text-decoration:underline}.mcms-embed-featured-image{margin-bottom:20px}.mcms-embed-featured-image img{width:100%;height:auto;border:none}.mcms-embed-featured-image.square{float:left;max-width:160px;margin-right:20px}.mcms-embed p{margin:0}p.mcms-embed-heading{margin:0 0 15px;font-weight:600;font-size:22px;line-height:1.3}.mcms-embed-heading a{color:#32373c}.mcms-embed .mcms-embed-more{color:#b4b9be}.mcms-embed-footer{display:table;width:100%;margin-top:30px}.mcms-embed-site-icon{position:absolute;top:50%;left:0;-webkit-transform:translateY(-50%);transform:translateY(-50%);height:25px;width:25px;border:0}.mcms-embed-site-title{font-weight:600;line-height:25px}.mcms-embed-site-title a{position:relative;display:inline-block;padding-left:35px}.mcms-embed-meta,.mcms-embed-site-title{display:table-cell}.mcms-embed-meta{text-align:right;white-space:nowrap;vertical-align:middle}.mcms-embed-comments,.mcms-embed-share{display:inline}.mcms-embed-comments a,.mcms-embed-share-tab-button{display:inline-block}.mcms-embed-meta a:hover{text-decoration:none;color:#000000}.mcms-embed-comments a{line-height:25px}.mcms-embed-comments+.mcms-embed-share{margin-left:10px}.mcms-embed-share-dialog{position:absolute;top:0;left:0;right:0;bottom:0;background-color:#222;background-color:rgba(10,10,10,.9);color:#fff;opacity:1;transition:opacity .25s ease-in-out}.mcms-embed-share-dialog.hidden{opacity:0;visibility:hidden}.mcms-embed-share-dialog-close,.mcms-embed-share-dialog-open{margin:-8px 0 0;padding:0;background:0 0;border:none;cursor:pointer;outline:0}.mcms-embed-share-dialog-close .dashicons,.mcms-embed-share-dialog-open .dashicons{padding:4px}.mcms-embed-share-dialog-open .dashicons{top:8px}.mcms-embed-share-dialog-close:focus .dashicons,.mcms-embed-share-dialog-open:focus .dashicons{box-shadow:0 0 0 1px #5b9dd9,0 0 2px 1px rgba(30,140,190,.8);border-radius:100%}.mcms-embed-share-dialog-close{position:absolute;top:20px;right:20px;font-size:22px}.mcms-embed-share-dialog-close:hover{text-decoration:none}.mcms-embed-share-dialog-close .dashicons{height:24px;width:24px;background-size:24px}.mcms-embed-share-dialog-content{height:100%;-webkit-transform-style:preserve-3d;transform-style:preserve-3d;overflow:hidden}.mcms-embed-share-dialog-text{margin-top:25px;padding:20px}.mcms-embed-share-tabs{margin:0 0 20px;padding:0;list-style:none}.mcms-embed-share-tab-button button{margin:0;padding:0;border:none;background:0 0;font-size:16px;line-height:1.3;color:#aaa;cursor:pointer;transition:color .1s ease-in}.mcms-embed-share-tab-button [aria-selected=true],.mcms-embed-share-tab-button button:hover{color:#fff}.mcms-embed-share-tab-button+.mcms-embed-share-tab-button{margin:0 0 0 10px;padding:0 0 0 11px;border-left:1px solid #aaa}.mcms-embed-share-tab[aria-hidden=true]{display:none}p.mcms-embed-share-description{margin:0;font-size:14px;line-height:1;font-style:italic;color:#aaa}.mcms-embed-share-input{box-sizing:border-box;width:100%;border:none;height:28px;margin:0 0 10px;padding:0 5px;font-size:14px;font-weight:400;line-height:1.5;resize:none;cursor:text}textarea.mcms-embed-share-input{height:72px}html[dir=rtl] .mcms-embed-featured-image.square{float:right;margin-right:0;margin-left:20px}html[dir=rtl] .mcms-embed-site-title a{padding-left:0;padding-right:35px}html[dir=rtl] .mcms-embed-site-icon{margin-right:0;margin-left:10px;left:auto;right:0}html[dir=rtl] .mcms-embed-meta{text-align:left}html[dir=rtl] .mcms-embed-share{margin-left:0;margin-right:10px}html[dir=rtl] .mcms-embed-share-dialog-close{right:auto;left:20px}html[dir=rtl] .mcms-embed-share-tab-button+.mcms-embed-share-tab-button{margin:0 10px 0 0;padding:0 11px 0 0;border-left:none;border-right:1px solid #aaa}
			<?php
		}
	?>
	</style>
	<?php
}

/**
 * Prints the JavaScript in the embed iframe header.
 *
 * @since 4.4.0
 */
function print_embed_scripts() {
	?>
	<script type="text/javascript">
	<?php
		if ( SCRIPT_DEBUG ) {
			readfile( BASED_TREE_URI . MCMSINC . "/js/mcms-embed-template.js" );
		} else {
			/*
			 * If you're looking at a src version of this file, you'll see an "include"
			 * statement below. This is used by the `grunt build` process to directly
			 * include a minified version of mcms-embed-template.js, instead of using the
			 * readfile() method from above.
			 *
			 * If you're looking at a build version of this file, you'll see a string of
			 * minified JavaScript. If you need to debug it, please turn on SCRIPT_DEBUG
			 * and edit mcms-embed-template.js directly.
			 */
			?>
			!function(a,b){"use strict";function c(b,c){a.parent.postMessage({message:b,value:c,secret:g},"*")}function d(){function d(){l.className=l.className.replace("hidden",""),b.querySelector('.mcms-embed-share-tab-button [aria-selected="true"]').focus()}function e(){l.className+=" hidden",b.querySelector(".mcms-embed-share-dialog-open").focus()}function f(a){var c=b.querySelector('.mcms-embed-share-tab-button [aria-selected="true"]');c.setAttribute("aria-selected","false"),b.querySelector("#"+c.getAttribute("aria-controls")).setAttribute("aria-hidden","true"),a.target.setAttribute("aria-selected","true"),b.querySelector("#"+a.target.getAttribute("aria-controls")).setAttribute("aria-hidden","false")}function g(a){var c,d,e=a.target,f=e.parentElement.previousElementSibling,g=e.parentElement.nextElementSibling;if(37===a.keyCode)c=f;else{if(39!==a.keyCode)return!1;c=g}"rtl"===b.documentElement.getAttribute("dir")&&(c=c===f?g:f),c&&(d=c.firstElementChild,e.setAttribute("tabindex","-1"),e.setAttribute("aria-selected",!1),b.querySelector("#"+e.getAttribute("aria-controls")).setAttribute("aria-hidden","true"),d.setAttribute("tabindex","0"),d.setAttribute("aria-selected","true"),d.focus(),b.querySelector("#"+d.getAttribute("aria-controls")).setAttribute("aria-hidden","false"))}function h(a){var c=b.querySelector('.mcms-embed-share-tab-button [aria-selected="true"]');n!==a.target||a.shiftKey?c===a.target&&a.shiftKey&&(n.focus(),a.preventDefault()):(c.focus(),a.preventDefault())}function i(a){var b,d=a.target;b=d.hasAttribute("href")?d.getAttribute("href"):d.parentElement.getAttribute("href"),b&&(c("link",b),a.preventDefault())}if(!k){k=!0;var j,l=b.querySelector(".mcms-embed-share-dialog"),m=b.querySelector(".mcms-embed-share-dialog-open"),n=b.querySelector(".mcms-embed-share-dialog-close"),o=b.querySelectorAll(".mcms-embed-share-input"),p=b.querySelectorAll(".mcms-embed-share-tab-button button"),q=b.querySelector(".mcms-embed-featured-image img");if(o)for(j=0;j<o.length;j++)o[j].addEventListener("click",function(a){a.target.select()});if(m&&m.addEventListener("click",function(){d()}),n&&n.addEventListener("click",function(){e()}),p)for(j=0;j<p.length;j++)p[j].addEventListener("click",f),p[j].addEventListener("keydown",g);b.addEventListener("keydown",function(a){27===a.keyCode&&-1===l.className.indexOf("hidden")?e():9===a.keyCode&&h(a)},!1),a.self!==a.top&&(c("height",Math.ceil(b.body.getBoundingClientRect().height)),q&&q.addEventListener("load",function(){c("height",Math.ceil(b.body.getBoundingClientRect().height))}),b.addEventListener("click",i))}}function e(){a.self!==a.top&&(clearTimeout(i),i=setTimeout(function(){c("height",Math.ceil(b.body.getBoundingClientRect().height))},100))}function f(){a.self===a.top||g||(g=a.location.hash.replace(/.*secret=([\d\w]{10}).*/,"$1"),clearTimeout(h),h=setTimeout(function(){f()},100))}var g,h,i,j=b.querySelector&&a.addEventListener,k=!1;j&&(f(),b.documentElement.className=b.documentElement.className.replace(/\bno-js\b/,"")+" js",b.addEventListener("DOMContentLoaded",d,!1),a.addEventListener("load",d,!1),a.addEventListener("resize",e,!1))}(window,document);
			<?php
		}
	?>
	</script>
	<?php
}

/**
 * Prepare the oembed HTML to be displayed in an RSS feed.
 *
 * @since 4.4.0
 * @access private
 *
 * @param string $content The content to filter.
 * @return string The filtered content.
 */
function _oembed_filter_feed_content( $content ) {
	return str_replace( '<iframe class="mcms-embedded-content" sandbox="allow-scripts" security="restricted" style="position: absolute; clip: rect(1px, 1px, 1px, 1px);"', '<iframe class="mcms-embedded-content" sandbox="allow-scripts" security="restricted"', $content );
}

/**
 * Prints the necessary markup for the embed comments button.
 *
 * @since 4.4.0
 */
function print_embed_comments_button() {
	if ( is_404() || ! ( get_comments_number() || comments_open() ) ) {
		return;
	}
	?>
	<div class="mcms-embed-comments">
		<a href="<?php comments_link(); ?>" target="_top">
			<span class="dashicons dashicons-admin-comments"></span>
			<?php
			printf(
				_n(
					'%s <span class="screen-reader-text">Comment</span>',
					'%s <span class="screen-reader-text">Comments</span>',
					get_comments_number()
				),
				number_format_i18n( get_comments_number() )
			);
			?>
		</a>
	</div>
	<?php
}

/**
 * Prints the necessary markup for the embed sharing button.
 *
 * @since 4.4.0
 */
function print_embed_sharing_button() {
	if ( is_404() ) {
		return;
	}
	?>
	<div class="mcms-embed-share">
		<button type="button" class="mcms-embed-share-dialog-open" aria-label="<?php esc_attr_e( 'Open sharing dialog' ); ?>">
			<span class="dashicons dashicons-share"></span>
		</button>
	</div>
	<?php
}

/**
 * Prints the necessary markup for the embed sharing dialog.
 *
 * @since 4.4.0
 */
function print_embed_sharing_dialog() {
	if ( is_404() ) {
		return;
	}
	?>
	<div class="mcms-embed-share-dialog hidden" role="dialog" aria-label="<?php esc_attr_e( 'Sharing options' ); ?>">
		<div class="mcms-embed-share-dialog-content">
			<div class="mcms-embed-share-dialog-text">
				<ul class="mcms-embed-share-tabs" role="tablist">
					<li class="mcms-embed-share-tab-button mcms-embed-share-tab-button-mandarincms" role="presentation">
						<button type="button" role="tab" aria-controls="mcms-embed-share-tab-mandarincms" aria-selected="true" tabindex="0"><?php esc_html_e( 'MandarinCMS Embed' ); ?></button>
					</li>
					<li class="mcms-embed-share-tab-button mcms-embed-share-tab-button-html" role="presentation">
						<button type="button" role="tab" aria-controls="mcms-embed-share-tab-html" aria-selected="false" tabindex="-1"><?php esc_html_e( 'HTML Embed' ); ?></button>
					</li>
				</ul>
				<div id="mcms-embed-share-tab-mandarincms" class="mcms-embed-share-tab" role="tabpanel" aria-hidden="false">
					<input type="text" value="<?php the_permalink(); ?>" class="mcms-embed-share-input" aria-describedby="mcms-embed-share-description-mandarincms" tabindex="0" readonly/>

					<p class="mcms-embed-share-description" id="mcms-embed-share-description-mandarincms">
						<?php _e( 'Copy and paste this URL into your MandarinCMS site to embed' ); ?>
					</p>
				</div>
				<div id="mcms-embed-share-tab-html" class="mcms-embed-share-tab" role="tabpanel" aria-hidden="true">
					<textarea class="mcms-embed-share-input" aria-describedby="mcms-embed-share-description-html" tabindex="0" readonly><?php echo esc_textarea( get_post_embed_html( 600, 400 ) ); ?></textarea>

					<p class="mcms-embed-share-description" id="mcms-embed-share-description-html">
						<?php _e( 'Copy and paste this code into your site to embed' ); ?>
					</p>
				</div>
			</div>

			<button type="button" class="mcms-embed-share-dialog-close" aria-label="<?php esc_attr_e( 'Close sharing dialog' ); ?>">
				<span class="dashicons dashicons-no"></span>
			</button>
		</div>
	</div>
	<?php
}

/**
 * Prints the necessary markup for the site title in an embed template.
 *
 * @since 4.5.0
 */
function the_embed_site_title() {
	$site_title = sprintf(
		'<a href="%s" target="_top"><img src="%s" srcset="%s 2x" width="32" height="32" alt="" class="mcms-embed-site-icon"/><span>%s</span></a>',
		esc_url( home_url() ),
		esc_url( get_site_icon_url( 32, admin_url( 'images/w-logo-blue.png' ) ) ),
		esc_url( get_site_icon_url( 64, admin_url( 'images/w-logo-blue.png' ) ) ),
		esc_html( get_bloginfo( 'name' ) )
	);

	$site_title = '<div class="mcms-embed-site-title">' . $site_title . '</div>';

	/**
	 * Filters the site title HTML in the embed footer.
	 *
	 * @since 4.4.0
	 *
	 * @param string $site_title The site title HTML.
	 */
	echo apply_filters( 'embed_site_title_html', $site_title );
}

/**
 * Filters the oEmbed result before any HTTP requests are made.
 *
 * If the URL belongs to the current site, the result is fetched directly instead of
 * going through the oEmbed discovery process.
 *
 * @since 4.5.3
 *
 * @param null|string $result The UNSANITIZED (and potentially unsafe) HTML that should be used to embed. Default null.
 * @param string      $url    The URL that should be inspected for discovery `<link>` tags.
 * @param array       $args   oEmbed remote get arguments.
 * @return null|string The UNSANITIZED (and potentially unsafe) HTML that should be used to embed.
 *                     Null if the URL does not belong to the current site.
 */
function mcms_filter_pre_oembed_result( $result, $url, $args ) {
	$switched_blog = false;

	if ( is_multisite() ) {
		$url_parts = mcms_parse_args( mcms_parse_url( $url ), array(
			'host'   => '',
			'path'   => '/',
		) );

		$qv = array( 'domain' => $url_parts['host'], 'path' => '/' );

		// In case of subdirectory configs, set the path.
		if ( ! is_subdomain_install() ) {
			$path = explode( '/', ltrim( $url_parts['path'], '/' ) );
			$path = reset( $path );

			if ( $path ) {
				$qv['path'] = get_network()->path . $path . '/';
			}
		}

		$sites = get_sites( $qv );
		$site  = reset( $sites );

		if ( $site && (int) $site->blog_id !== get_current_blog_id() ) {
			switch_to_blog( $site->blog_id );
			$switched_blog = true;
		}
	}

	$post_id = url_to_postid( $url );

	/** This filter is documented in mcms-roots/class-mcms-oembed-controller.php */
	$post_id = apply_filters( 'oembed_request_post_id', $post_id, $url );

	if ( ! $post_id ) {
		if ( $switched_blog ) {
			restore_current_blog();
		}

		return $result;
	}

	$width = isset( $args['width'] ) ? $args['width'] : 0;

	$data = get_oembed_response_data( $post_id, $width );
	$data = _mcms_oembed_get_object()->data2html( (object) $data, $url );

	if ( $switched_blog ) {
		restore_current_blog();
	}

	if ( ! $data ) {
		return $result;
	}

	return $data;
}
