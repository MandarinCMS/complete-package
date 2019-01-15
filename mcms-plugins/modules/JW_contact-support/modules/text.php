<?php
/**
** A base module for the following types of tags:
** 	[text] and [text*]		# Single-line text
** 	[email] and [email*]	# Email address
** 	[url] and [url*]		# URL
** 	[tel] and [tel*]		# Telephone number
**/

/* form_tag handler */

add_action( 'mcmscf7_init', 'mcmscf7_add_form_tag_text' );

function mcmscf7_add_form_tag_text() {
	mcmscf7_add_form_tag(
		array( 'text', 'text*', 'email', 'email*', 'url', 'url*', 'tel', 'tel*' ),
		'mcmscf7_text_form_tag_handler', array( 'name-attr' => true ) );
}

function mcmscf7_text_form_tag_handler( $tag ) {
	if ( empty( $tag->name ) ) {
		return '';
	}

	$validation_error = mcmscf7_get_validation_error( $tag->name );

	$class = mcmscf7_form_controls_class( $tag->type, 'mcmscf7-text' );

	if ( in_array( $tag->basetype, array( 'email', 'url', 'tel' ) ) ) {
		$class .= ' mcmscf7-validates-as-' . $tag->basetype;
	}

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

	$atts['autocomplete'] = $tag->get_option( 'autocomplete',
		'[-0-9a-zA-Z]+', true );

	if ( $tag->has_option( 'readonly' ) ) {
		$atts['readonly'] = 'readonly';
	}

	if ( $tag->is_required() ) {
		$atts['aria-required'] = 'true';
	}

	$atts['aria-invalid'] = $validation_error ? 'true' : 'false';

	$value = (string) reset( $tag->values );

	if ( $tag->has_option( 'placeholder' ) || $tag->has_option( 'watermark' ) ) {
		$atts['placeholder'] = $value;
		$value = '';
	}

	$value = $tag->get_default_option( $value );

	$value = mcmscf7_get_hangover( $tag->name, $value );

	$atts['value'] = $value;

	if ( mcmscf7_support_html5() ) {
		$atts['type'] = $tag->basetype;
	} else {
		$atts['type'] = 'text';
	}

	$atts['name'] = $tag->name;

	$atts = mcmscf7_format_atts( $atts );

	$html = sprintf(
		'<span class="mcmscf7-form-control-wrap %1$s"><input %2$s />%3$s</span>',
		sanitize_html_class( $tag->name ), $atts, $validation_error );

	return $html;
}


/* Validation filter */

add_filter( 'mcmscf7_validate_text', 'mcmscf7_text_validation_filter', 10, 2 );
add_filter( 'mcmscf7_validate_text*', 'mcmscf7_text_validation_filter', 10, 2 );
add_filter( 'mcmscf7_validate_email', 'mcmscf7_text_validation_filter', 10, 2 );
add_filter( 'mcmscf7_validate_email*', 'mcmscf7_text_validation_filter', 10, 2 );
add_filter( 'mcmscf7_validate_url', 'mcmscf7_text_validation_filter', 10, 2 );
add_filter( 'mcmscf7_validate_url*', 'mcmscf7_text_validation_filter', 10, 2 );
add_filter( 'mcmscf7_validate_tel', 'mcmscf7_text_validation_filter', 10, 2 );
add_filter( 'mcmscf7_validate_tel*', 'mcmscf7_text_validation_filter', 10, 2 );

function mcmscf7_text_validation_filter( $result, $tag ) {
	$name = $tag->name;

	$value = isset( $_POST[$name] )
		? trim( mcms_unslash( strtr( (string) $_POST[$name], "\n", " " ) ) )
		: '';

	if ( 'text' == $tag->basetype ) {
		if ( $tag->is_required() && '' == $value ) {
			$result->invalidate( $tag, mcmscf7_get_message( 'invalid_required' ) );
		}
	}

	if ( 'email' == $tag->basetype ) {
		if ( $tag->is_required() && '' == $value ) {
			$result->invalidate( $tag, mcmscf7_get_message( 'invalid_required' ) );
		} elseif ( '' != $value && ! mcmscf7_is_email( $value ) ) {
			$result->invalidate( $tag, mcmscf7_get_message( 'invalid_email' ) );
		}
	}

	if ( 'url' == $tag->basetype ) {
		if ( $tag->is_required() && '' == $value ) {
			$result->invalidate( $tag, mcmscf7_get_message( 'invalid_required' ) );
		} elseif ( '' != $value && ! mcmscf7_is_url( $value ) ) {
			$result->invalidate( $tag, mcmscf7_get_message( 'invalid_url' ) );
		}
	}

	if ( 'tel' == $tag->basetype ) {
		if ( $tag->is_required() && '' == $value ) {
			$result->invalidate( $tag, mcmscf7_get_message( 'invalid_required' ) );
		} elseif ( '' != $value && ! mcmscf7_is_tel( $value ) ) {
			$result->invalidate( $tag, mcmscf7_get_message( 'invalid_tel' ) );
		}
	}

	if ( '' !== $value ) {
		$maxlength = $tag->get_maxlength_option();
		$minlength = $tag->get_minlength_option();

		if ( $maxlength && $minlength && $maxlength < $minlength ) {
			$maxlength = $minlength = null;
		}

		$code_units = mcmscf7_count_code_units( stripslashes( $value ) );

		if ( false !== $code_units ) {
			if ( $maxlength && $maxlength < $code_units ) {
				$result->invalidate( $tag, mcmscf7_get_message( 'invalid_too_long' ) );
			} elseif ( $minlength && $code_units < $minlength ) {
				$result->invalidate( $tag, mcmscf7_get_message( 'invalid_too_short' ) );
			}
		}
	}

	return $result;
}


/* Messages */

add_filter( 'mcmscf7_messages', 'mcmscf7_text_messages' );

function mcmscf7_text_messages( $messages ) {
	$messages = array_merge( $messages, array(
		'invalid_email' => array(
			'description' =>
				__( "Email address that the sender entered is invalid", 'jw-contact-support' ),
			'default' =>
				__( "The e-mail address entered is invalid.", 'jw-contact-support' ),
		),

		'invalid_url' => array(
			'description' =>
				__( "URL that the sender entered is invalid", 'jw-contact-support' ),
			'default' =>
				__( "The URL is invalid.", 'jw-contact-support' ),
		),

		'invalid_tel' => array(
			'description' =>
				__( "Telephone number that the sender entered is invalid", 'jw-contact-support' ),
			'default' =>
				__( "The telephone number is invalid.", 'jw-contact-support' ),
		),
	) );

	return $messages;
}


/* Tag generator */

add_action( 'mcmscf7_admin_init', 'mcmscf7_add_tag_generator_text', 15 );

function mcmscf7_add_tag_generator_text() {
	$tag_generator = MCMSCF7_TagGenerator::get_instance();
	$tag_generator->add( 'text', __( 'text', 'jw-contact-support' ),
		'mcmscf7_tag_generator_text' );
	$tag_generator->add( 'email', __( 'email', 'jw-contact-support' ),
		'mcmscf7_tag_generator_text' );
	$tag_generator->add( 'url', __( 'URL', 'jw-contact-support' ),
		'mcmscf7_tag_generator_text' );
	$tag_generator->add( 'tel', __( 'tel', 'jw-contact-support' ),
		'mcmscf7_tag_generator_text' );
}

function mcmscf7_tag_generator_text( $contact_form, $args = '' ) {
	$args = mcms_parse_args( $args, array() );
	$type = $args['id'];

	if ( ! in_array( $type, array( 'email', 'url', 'tel' ) ) ) {
		$type = 'text';
	}

	if ( 'text' == $type ) {
		$description = __( "Generate a form-tag for a single-line plain text input field. For more details, see %s.", 'jw-contact-support' );
	} elseif ( 'email' == $type ) {
		$description = __( "Generate a form-tag for a single-line email address input field. For more details, see %s.", 'jw-contact-support' );
	} elseif ( 'url' == $type ) {
		$description = __( "Generate a form-tag for a single-line URL input field. For more details, see %s.", 'jw-contact-support' );
	} elseif ( 'tel' == $type ) {
		$description = __( "Generate a form-tag for a single-line telephone number input field. For more details, see %s.", 'jw-contact-support' );
	}

	$desc_link = mcmscf7_link( __( 'https://jiiworks.net/text-fields/', 'jw-contact-support' ), __( 'Text Fields', 'jw-contact-support' ) );

?>
<div class="control-box">
<fieldset>
<legend><?php echo sprintf( esc_html( $description ), $desc_link ); ?></legend>

<table class="form-table">
<tbody>
	<tr>
	<th scope="row"><?php echo esc_html( __( 'Field type', 'jw-contact-support' ) ); ?></th>
	<td>
		<fieldset>
		<legend class="screen-reader-text"><?php echo esc_html( __( 'Field type', 'jw-contact-support' ) ); ?></legend>
		<label><input type="checkbox" name="required" /> <?php echo esc_html( __( 'Required field', 'jw-contact-support' ) ); ?></label>
		</fieldset>
	</td>
	</tr>

	<tr>
	<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-name' ); ?>"><?php echo esc_html( __( 'Name', 'jw-contact-support' ) ); ?></label></th>
	<td><input type="text" name="name" class="tg-name oneline" id="<?php echo esc_attr( $args['content'] . '-name' ); ?>" /></td>
	</tr>

	<tr>
	<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-values' ); ?>"><?php echo esc_html( __( 'Default value', 'jw-contact-support' ) ); ?></label></th>
	<td><input type="text" name="values" class="oneline" id="<?php echo esc_attr( $args['content'] . '-values' ); ?>" /><br />
	<label><input type="checkbox" name="placeholder" class="option" /> <?php echo esc_html( __( 'Use this text as the placeholder of the field', 'jw-contact-support' ) ); ?></label></td>
	</tr>

<?php if ( in_array( $type, array( 'text', 'email', 'url' ) ) ) : ?>
	<tr>
	<th scope="row"><?php echo esc_html( __( 'Akismet', 'jw-contact-support' ) ); ?></th>
	<td>
		<fieldset>
		<legend class="screen-reader-text"><?php echo esc_html( __( 'Akismet', 'jw-contact-support' ) ); ?></legend>

<?php if ( 'text' == $type ) : ?>
		<label>
			<input type="checkbox" name="akismet:author" class="option" />
			<?php echo esc_html( __( "This field requires author's name", 'jw-contact-support' ) ); ?>
		</label>
<?php elseif ( 'email' == $type ) : ?>
		<label>
			<input type="checkbox" name="akismet:author_email" class="option" />
			<?php echo esc_html( __( "This field requires author's email address", 'jw-contact-support' ) ); ?>
		</label>
<?php elseif ( 'url' == $type ) : ?>
		<label>
			<input type="checkbox" name="akismet:author_url" class="option" />
			<?php echo esc_html( __( "This field requires author's URL", 'jw-contact-support' ) ); ?>
		</label>
<?php endif; ?>

		</fieldset>
	</td>
	</tr>
<?php endif; ?>

	<tr>
	<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-id' ); ?>"><?php echo esc_html( __( 'Id attribute', 'jw-contact-support' ) ); ?></label></th>
	<td><input type="text" name="id" class="idvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-id' ); ?>" /></td>
	</tr>

	<tr>
	<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-class' ); ?>"><?php echo esc_html( __( 'Class attribute', 'jw-contact-support' ) ); ?></label></th>
	<td><input type="text" name="class" class="classvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-class' ); ?>" /></td>
	</tr>

</tbody>
</table>
</fieldset>
</div>

<div class="insert-box">
	<input type="text" name="<?php echo $type; ?>" class="tag code" readonly="readonly" onfocus="this.select()" />

	<div class="submitbox">
	<input type="button" class="button button-primary insert-tag" value="<?php echo esc_attr( __( 'Insert Tag', 'jw-contact-support' ) ); ?>" />
	</div>

	<br class="clear" />

	<p class="description mail-tag"><label for="<?php echo esc_attr( $args['content'] . '-mailtag' ); ?>"><?php echo sprintf( esc_html( __( "To use the value input through this field in a mail field, you need to insert the corresponding mail-tag (%s) into the field on the Mail tab.", 'jw-contact-support' ) ), '<strong><span class="mail-tag"></span></strong>' ); ?><input type="text" class="mail-tag code hidden" readonly="readonly" id="<?php echo esc_attr( $args['content'] . '-mailtag' ); ?>" /></label></p>
</div>
<?php
}
