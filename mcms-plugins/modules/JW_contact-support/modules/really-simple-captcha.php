<?php
/**
** A base module for [captchac] and [captchar]
**/

/* form_tag handler */

add_action( 'mcmscf7_init', 'mcmscf7_add_form_tag_captcha' );

function mcmscf7_add_form_tag_captcha() {
	// CAPTCHA-Challenge (image)
	mcmscf7_add_form_tag( 'captchac',
		'mcmscf7_captchac_form_tag_handler',
		array(
			'name-attr' => true,
			'zero-controls-container' => true,
			'not-for-mail' => true,
		)
	);

	// CAPTCHA-Response (input)
	mcmscf7_add_form_tag( 'captchar',
		'mcmscf7_captchar_form_tag_handler',
		array(
			'name-attr' => true,
			'do-not-store' => true,
			'not-for-mail' => true,
		)
	);
}

function mcmscf7_captchac_form_tag_handler( $tag ) {
	if ( ! class_exists( 'ReallySimpleCaptcha' ) ) {
		$error = sprintf(
			/* translators: %s: link labeled 'Really Simple CAPTCHA' */
			esc_html( __( "To use CAPTCHA, you need %s module installed.", 'jw-contact-support' ) ),
			mcmscf7_link( 'https://mandarincms.com/modules/really-simple-captcha/', 'Really Simple CAPTCHA' ) );

		return sprintf( '<em>%s</em>', $error );
	}

	if ( empty( $tag->name ) ) {
		return '';
	}

	$class = mcmscf7_form_controls_class( $tag->type );
	$class .= ' mcmscf7-captcha-' . $tag->name;

	$atts = array();
	$atts['class'] = $tag->get_class_option( $class );
	$atts['id'] = $tag->get_id_option();

	$op = array( // Default
		'img_size' => array( 72, 24 ),
		'base' => array( 6, 18 ),
		'font_size' => 14,
		'font_char_width' => 15,
	);

	$op = array_merge( $op, mcmscf7_captchac_options( $tag->options ) );

	if ( ! $filename = mcmscf7_generate_captcha( $op ) ) {
		return '';
	}

	if ( ! empty( $op['img_size'] ) ) {
		if ( isset( $op['img_size'][0] ) ) {
			$atts['width'] = $op['img_size'][0];
		}

		if ( isset( $op['img_size'][1] ) ) {
			$atts['height'] = $op['img_size'][1];
		}
	}

	$atts['alt'] = 'captcha';
	$atts['src'] = mcmscf7_captcha_url( $filename );

	$atts = mcmscf7_format_atts( $atts );

	$prefix = substr( $filename, 0, strrpos( $filename, '.' ) );

	$html = sprintf(
		'<input type="hidden" name="_mcmscf7_captcha_challenge_%1$s" value="%2$s" /><img %3$s />',
		$tag->name, esc_attr( $prefix ), $atts );

	return $html;
}

function mcmscf7_captchar_form_tag_handler( $tag ) {
	if ( empty( $tag->name ) ) {
		return '';
	}

	$validation_error = mcmscf7_get_validation_error( $tag->name );

	$class = mcmscf7_form_controls_class( $tag->type );

	if ( $validation_error ) {
		$class .= ' mcmscf7-not-valid';
	}

	$atts = array();

	$atts['size'] = $tag->get_size_option( '40' );
	$atts['maxlength'] = $tag->get_maxlength_option();
	$atts['minlength'] = $tag->get_minlength_option();

	if ( $atts['maxlength'] && $atts['minlength']
	&& $atts['maxlength'] < $atts['minlength'] ) {
		unset( $atts['maxlength'], $atts['minlength'] );
	}

	$atts['class'] = $tag->get_class_option( $class );
	$atts['id'] = $tag->get_id_option();
	$atts['tabindex'] = $tag->get_option( 'tabindex', 'signed_int', true );
	$atts['autocomplete'] = 'off';
	$atts['aria-invalid'] = $validation_error ? 'true' : 'false';

	$value = (string) reset( $tag->values );

	if ( mcmscf7_is_posted() ) {
		$value = '';
	}

	if ( $tag->has_option( 'placeholder' )
	|| $tag->has_option( 'watermark' ) ) {
		$atts['placeholder'] = $value;
		$value = '';
	}

	$atts['value'] = $value;
	$atts['type'] = 'text';
	$atts['name'] = $tag->name;

	$atts = mcmscf7_format_atts( $atts );

	$html = sprintf(
		'<span class="mcmscf7-form-control-wrap %1$s"><input %2$s />%3$s</span>',
		sanitize_html_class( $tag->name ), $atts, $validation_error );

	return $html;
}


/* Validation filter */

add_filter( 'mcmscf7_validate_captchar', 'mcmscf7_captcha_validation_filter', 10, 2 );

function mcmscf7_captcha_validation_filter( $result, $tag ) {
	$type = $tag->type;
	$name = $tag->name;

	$captchac = '_mcmscf7_captcha_challenge_' . $name;

	$prefix = isset( $_POST[$captchac] ) ? (string) $_POST[$captchac] : '';
	$response = isset( $_POST[$name] ) ? (string) $_POST[$name] : '';
	$response = mcmscf7_canonicalize( $response );

	if ( 0 == strlen( $prefix ) || ! mcmscf7_check_captcha( $prefix, $response ) ) {
		$result->invalidate( $tag, mcmscf7_get_message( 'captcha_not_match' ) );
	}

	if ( 0 != strlen( $prefix ) ) {
		mcmscf7_remove_captcha( $prefix );
	}

	return $result;
}


/* Ajax echo filter */

add_filter( 'mcmscf7_ajax_onload', 'mcmscf7_captcha_ajax_refill' );
add_filter( 'mcmscf7_ajax_json_echo', 'mcmscf7_captcha_ajax_refill' );

function mcmscf7_captcha_ajax_refill( $items ) {
	if ( ! is_array( $items ) ) {
		return $items;
	}

	$tags = mcmscf7_scan_form_tags( array( 'type' => 'captchac' ) );

	if ( empty( $tags ) ) {
		return $items;
	}

	$refill = array();

	foreach ( $tags as $tag ) {
		$name = $tag->name;
		$options = $tag->options;

		if ( empty( $name ) ) {
			continue;
		}

		$op = mcmscf7_captchac_options( $options );

		if ( $filename = mcmscf7_generate_captcha( $op ) ) {
			$captcha_url = mcmscf7_captcha_url( $filename );
			$refill[$name] = $captcha_url;
		}
	}

	if ( ! empty( $refill ) ) {
		$items['captcha'] = $refill;
	}

	return $items;
}


/* Messages */

add_filter( 'mcmscf7_messages', 'mcmscf7_captcha_messages' );

function mcmscf7_captcha_messages( $messages ) {
	$messages = array_merge( $messages, array(
		'captcha_not_match' => array(
			'description' =>
				__( "The code that sender entered does not match the CAPTCHA", 'jw-contact-support' ),
			'default' =>
				__( 'Your entered code is incorrect.', 'jw-contact-support' ),
		),
	) );

	return $messages;
}


/* Tag generator */

add_action( 'mcmscf7_admin_init', 'mcmscf7_add_tag_generator_captcha', 46 );

function mcmscf7_add_tag_generator_captcha() {
	if ( ! mcmscf7_use_really_simple_captcha() ) {
		return;
	}

	$tag_generator = MCMSCF7_TagGenerator::get_instance();
	$tag_generator->add( 'captcha',
		__( 'CAPTCHA (Really Simple CAPTCHA)', 'jw-contact-support' ),
		'mcmscf7_tag_generator_captcha' );
}

function mcmscf7_tag_generator_captcha( $contact_form, $args = '' ) {
	$args = mcms_parse_args( $args, array() );

	if ( ! class_exists( 'ReallySimpleCaptcha' ) ) {
?>
<div class="control-box">
<fieldset>
<legend><?php
	echo sprintf(
		/* translators: %s: link labeled 'Really Simple CAPTCHA' */
		esc_html( __( "To use CAPTCHA, you first need to install and activate %s module.", 'jw-contact-support' ) ),
		mcmscf7_link( 'https://mandarincms.com/modules/really-simple-captcha/', 'Really Simple CAPTCHA' )
	);
?></legend>
</fieldset>
</div>
<?php

		return;
	}

	$description = __( "Generate form-tags for a CAPTCHA image and corresponding response input field. For more details, see %s.", 'jw-contact-support' );

	$desc_link = mcmscf7_link( __( 'https://jiiworks.net/captcha/', 'jw-contact-support' ), __( 'CAPTCHA', 'jw-contact-support' ) );

?>
<div class="control-box">
<fieldset>
<legend><?php echo sprintf( esc_html( $description ), $desc_link ); ?></legend>

<table class="form-table">
<tbody>
	<tr>
	<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-name' ); ?>"><?php echo esc_html( __( 'Name', 'jw-contact-support' ) ); ?></label></th>
	<td><input type="text" name="name" class="tg-name oneline" id="<?php echo esc_attr( $args['content'] . '-name' ); ?>" /></td>
	</tr>
</tbody>
</table>

<table class="form-table scope captchac">
<caption><?php echo esc_html( __( "Image settings", 'jw-contact-support' ) ); ?></caption>
<tbody>
	<tr>
	<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-captchac-id' ); ?>"><?php echo esc_html( __( 'Id attribute', 'jw-contact-support' ) ); ?></label></th>
	<td><input type="text" name="id" class="idvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-captchac-id' ); ?>" /></td>
	</tr>

	<tr>
	<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-captchac-class' ); ?>"><?php echo esc_html( __( 'Class attribute', 'jw-contact-support' ) ); ?></label></th>
	<td><input type="text" name="class" class="classvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-captchac-class' ); ?>" /></td>
	</tr>
</tbody>
</table>

<table class="form-table scope captchar">
<caption><?php echo esc_html( __( "Input field settings", 'jw-contact-support' ) ); ?></caption>
<tbody>
	<tr>
	<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-captchar-id' ); ?>"><?php echo esc_html( __( 'Id attribute', 'jw-contact-support' ) ); ?></label></th>
	<td><input type="text" name="id" class="idvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-captchar-id' ); ?>" /></td>
	</tr>

	<tr>
	<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-captchar-class' ); ?>"><?php echo esc_html( __( 'Class attribute', 'jw-contact-support' ) ); ?></label></th>
	<td><input type="text" name="class" class="classvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-captchar-class' ); ?>" /></td>
	</tr>
</tbody>
</table>
</fieldset>
</div>

<div class="insert-box">
	<input type="text" name="captcha" class="tag code" readonly="readonly" onfocus="this.select()" />

	<div class="submitbox">
	<input type="button" class="button button-primary insert-tag" value="<?php echo esc_attr( __( 'Insert Tag', 'jw-contact-support' ) ); ?>" />
	</div>
</div>
<?php
}


/* Warning message */

add_action( 'mcmscf7_admin_warnings', 'mcmscf7_captcha_display_warning_message' );

function mcmscf7_captcha_display_warning_message() {
	if ( ! $contact_form = mcmscf7_get_current_contact_form() ) {
		return;
	}

	$has_tags = (bool) $contact_form->scan_form_tags(
		array( 'type' => array( 'captchac' ) ) );

	if ( ! $has_tags ) {
		return;
	}

	if ( ! class_exists( 'ReallySimpleCaptcha' ) ) {
		return;
	}

	$uploads_dir = mcmscf7_captcha_tmp_dir();
	mcmscf7_init_captcha();

	if ( ! is_dir( $uploads_dir ) || ! mcms_is_writable( $uploads_dir ) ) {
		$message = sprintf( __( 'This contact form contains CAPTCHA fields, but the temporary folder for the files (%s) does not exist or is not writable. You can create the folder or change its permission manually.', 'jw-contact-support' ), $uploads_dir );

		echo '<div class="notice notice-warning"><p>' . esc_html( $message ) . '</p></div>';
	}

	if ( ! function_exists( 'imagecreatetruecolor' )
	|| ! function_exists( 'imagettftext' ) ) {
		$message = __( "This contact form contains CAPTCHA fields, but the necessary libraries (GD and FreeType) are not available on your server.", 'jw-contact-support' );

		echo '<div class="notice notice-warning"><p>' . esc_html( $message ) . '</p></div>';
	}
}


/* CAPTCHA functions */

function mcmscf7_init_captcha() {
	static $captcha = null;

	if ( $captcha ) {
		return $captcha;
	}

	if ( class_exists( 'ReallySimpleCaptcha' ) ) {
		$captcha = new ReallySimpleCaptcha();
	} else {
		return false;
	}

	$dir = trailingslashit( mcmscf7_captcha_tmp_dir() );

	$captcha->tmp_dir = $dir;

	if ( is_callable( array( $captcha, 'make_tmp_dir' ) ) ) {
		$result = $captcha->make_tmp_dir();

		if ( ! $result ) {
			return false;
		}

		return $captcha;
	}

	if ( mcms_mkdir_p( $dir ) ) {
		$htaccess_file = path_join( $dir, '.htaccess' );

		if ( file_exists( $htaccess_file ) ) {
			return $captcha;
		}

		if ( $handle = fopen( $htaccess_file, 'w' ) ) {
			fwrite( $handle, 'Order deny,allow' . "\n" );
			fwrite( $handle, 'Deny from all' . "\n" );
			fwrite( $handle, '<Files ~ "^[0-9A-Za-z]+\\.(jpeg|gif|png)$">' . "\n" );
			fwrite( $handle, '    Allow from all' . "\n" );
			fwrite( $handle, '</Files>' . "\n" );
			fclose( $handle );
		}
	} else {
		return false;
	}

	return $captcha;
}

function mcmscf7_captcha_tmp_dir() {
	if ( defined( 'MCMSCF7_CAPTCHA_TMP_DIR' ) ) {
		return MCMSCF7_CAPTCHA_TMP_DIR;
	} else {
		return path_join( mcmscf7_upload_dir( 'dir' ), 'mcmscf7_captcha' );
	}
}

function mcmscf7_captcha_tmp_url() {
	if ( defined( 'MCMSCF7_CAPTCHA_TMP_URL' ) ) {
		return MCMSCF7_CAPTCHA_TMP_URL;
	} else {
		return path_join( mcmscf7_upload_dir( 'url' ), 'mcmscf7_captcha' );
	}
}

function mcmscf7_captcha_url( $filename ) {
	$url = path_join( mcmscf7_captcha_tmp_url(), $filename );

	if ( is_ssl() && 'http:' == substr( $url, 0, 5 ) ) {
		$url = 'https:' . substr( $url, 5 );
	}

	return apply_filters( 'mcmscf7_captcha_url', esc_url_raw( $url ) );
}

function mcmscf7_generate_captcha( $options = null ) {
	if ( ! $captcha = mcmscf7_init_captcha() ) {
		return false;
	}

	if ( ! is_dir( $captcha->tmp_dir )
	|| ! mcms_is_writable( $captcha->tmp_dir ) ) {
		return false;
	}

	$img_type = imagetypes();

	if ( $img_type & IMG_PNG ) {
		$captcha->img_type = 'png';
	} elseif ( $img_type & IMG_GIF ) {
		$captcha->img_type = 'gif';
	} elseif ( $img_type & IMG_JPG ) {
		$captcha->img_type = 'jpeg';
	} else {
		return false;
	}

	if ( is_array( $options ) ) {
		if ( isset( $options['img_size'] ) ) {
			$captcha->img_size = $options['img_size'];
		}

		if ( isset( $options['base'] ) ) {
			$captcha->base = $options['base'];
		}

		if ( isset( $options['font_size'] ) ) {
			$captcha->font_size = $options['font_size'];
		}

		if ( isset( $options['font_char_width'] ) ) {
			$captcha->font_char_width = $options['font_char_width'];
		}

		if ( isset( $options['fg'] ) ) {
			$captcha->fg = $options['fg'];
		}

		if ( isset( $options['bg'] ) ) {
			$captcha->bg = $options['bg'];
		}
	}

	$prefix = mcms_rand();
	$captcha_word = $captcha->generate_random_word();
	return $captcha->generate_image( $prefix, $captcha_word );
}

function mcmscf7_check_captcha( $prefix, $response ) {
	if ( ! $captcha = mcmscf7_init_captcha() ) {
		return false;
	}

	return $captcha->check( $prefix, $response );
}

function mcmscf7_remove_captcha( $prefix ) {
	if ( ! $captcha = mcmscf7_init_captcha() ) {
		return false;
	}

	// JW Contact Supportgenerates $prefix with mcms_rand()
	if ( preg_match( '/[^0-9]/', $prefix ) ) {
		return false;
	}

	$captcha->remove( $prefix );
}

add_action( 'template_redirect', 'mcmscf7_cleanup_captcha_files', 20 );

function mcmscf7_cleanup_captcha_files() {
	if ( ! $captcha = mcmscf7_init_captcha() ) {
		return false;
	}

	if ( is_callable( array( $captcha, 'cleanup' ) ) ) {
		return $captcha->cleanup();
	}

	$dir = trailingslashit( mcmscf7_captcha_tmp_dir() );

	if ( ! is_dir( $dir ) || ! is_readable( $dir ) || ! mcms_is_writable( $dir ) ) {
		return false;
	}

	if ( $handle = opendir( $dir ) ) {
		while ( false !== ( $file = readdir( $handle ) ) ) {
			if ( ! preg_match( '/^[0-9]+\.(php|txt|png|gif|jpeg)$/', $file ) ) {
				continue;
			}

			$stat = stat( path_join( $dir, $file ) );

			if ( $stat['mtime'] + 3600 < time() ) { // 3600 secs == 1 hour
				unlink( path_join( $dir, $file ) );
			}
		}

		closedir( $handle );
	}
}

function mcmscf7_captchac_options( $options ) {
	if ( ! is_array( $options ) ) {
		return array();
	}

	$op = array();
	$image_size_array = preg_grep( '%^size:[smlSML]$%', $options );

	if ( $image_size = array_shift( $image_size_array ) ) {
		preg_match( '%^size:([smlSML])$%', $image_size, $is_matches );

		switch ( strtolower( $is_matches[1] ) ) {
			case 's':
				$op['img_size'] = array( 60, 20 );
				$op['base'] = array( 6, 15 );
				$op['font_size'] = 11;
				$op['font_char_width'] = 13;
				break;
			case 'l':
				$op['img_size'] = array( 84, 28 );
				$op['base'] = array( 6, 20 );
				$op['font_size'] = 17;
				$op['font_char_width'] = 19;
				break;
			case 'm':
			default:
				$op['img_size'] = array( 72, 24 );
				$op['base'] = array( 6, 18 );
				$op['font_size'] = 14;
				$op['font_char_width'] = 15;
		}
	}

	$fg_color_array = preg_grep(
		'%^fg:#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$%', $options );

	if ( $fg_color = array_shift( $fg_color_array ) ) {
		preg_match( '%^fg:#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$%',
			$fg_color, $fc_matches );

		if ( 3 == strlen( $fc_matches[1] ) ) {
			$r = substr( $fc_matches[1], 0, 1 );
			$g = substr( $fc_matches[1], 1, 1 );
			$b = substr( $fc_matches[1], 2, 1 );

			$op['fg'] = array(
				hexdec( $r . $r ),
				hexdec( $g . $g ),
				hexdec( $b . $b ),
			);
		} elseif ( 6 == strlen( $fc_matches[1] ) ) {
			$r = substr( $fc_matches[1], 0, 2 );
			$g = substr( $fc_matches[1], 2, 2 );
			$b = substr( $fc_matches[1], 4, 2 );

			$op['fg'] = array(
				hexdec( $r ),
				hexdec( $g ),
				hexdec( $b ),
			);
		}
	}

	$bg_color_array = preg_grep(
		'%^bg:#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$%', $options );

	if ( $bg_color = array_shift( $bg_color_array ) ) {
		preg_match( '%^bg:#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$%',
			$bg_color, $bc_matches );

		if ( 3 == strlen( $bc_matches[1] ) ) {
			$r = substr( $bc_matches[1], 0, 1 );
			$g = substr( $bc_matches[1], 1, 1 );
			$b = substr( $bc_matches[1], 2, 1 );

			$op['bg'] = array(
				hexdec( $r . $r ),
				hexdec( $g . $g ),
				hexdec( $b . $b ),
			);
		} elseif ( 6 == strlen( $bc_matches[1] ) ) {
			$r = substr( $bc_matches[1], 0, 2 );
			$g = substr( $bc_matches[1], 2, 2 );
			$b = substr( $bc_matches[1], 4, 2 );

			$op['bg'] = array(
				hexdec( $r ),
				hexdec( $g ),
				hexdec( $b ),
			);
		}
	}

	return $op;
}
