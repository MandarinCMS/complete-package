<?php
/**
** A base module for [submit]
**/

/* form_tag handler */

add_action( 'mcmscf7_init', 'mcmscf7_add_form_tag_submit' );

function mcmscf7_add_form_tag_submit() {
	mcmscf7_add_form_tag( 'submit', 'mcmscf7_submit_form_tag_handler' );
}

function mcmscf7_submit_form_tag_handler( $tag ) {
	$class = mcmscf7_form_controls_class( $tag->type );

	$atts = array();

	$atts['class'] = $tag->get_class_option( $class );
	$atts['id'] = $tag->get_id_option();
	$atts['tabindex'] = $tag->get_option( 'tabindex', 'signed_int', true );

	$value = isset( $tag->values[0] ) ? $tag->values[0] : '';

	if ( empty( $value ) ) {
		$value = __( 'Send', 'jw-contact-support' );
	}

	$atts['type'] = 'submit';
	$atts['value'] = $value;

	$atts = mcmscf7_format_atts( $atts );

	$html = sprintf( '<input %1$s />', $atts );

	return $html;
}


/* Tag generator */

add_action( 'mcmscf7_admin_init', 'mcmscf7_add_tag_generator_submit', 55 );

function mcmscf7_add_tag_generator_submit() {
	$tag_generator = MCMSCF7_TagGenerator::get_instance();
	$tag_generator->add( 'submit', __( 'submit', 'jw-contact-support' ),
		'mcmscf7_tag_generator_submit', array( 'nameless' => 1 ) );
}

function mcmscf7_tag_generator_submit( $contact_form, $args = '' ) {
	$args = mcms_parse_args( $args, array() );

	$description = __( "Generate a form-tag for a submit button. For more details, see %s.", 'jw-contact-support' );

	$desc_link = mcmscf7_link( __( 'https://jiiworks.net/submit-button/', 'jw-contact-support' ), __( 'Submit Button', 'jw-contact-support' ) );

?>
<div class="control-box">
<fieldset>
<legend><?php echo sprintf( esc_html( $description ), $desc_link ); ?></legend>

<table class="form-table">
<tbody>
	<tr>
	<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-values' ); ?>"><?php echo esc_html( __( 'Label', 'jw-contact-support' ) ); ?></label></th>
	<td><input type="text" name="values" class="oneline" id="<?php echo esc_attr( $args['content'] . '-values' ); ?>" /></td>
	</tr>

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
	<input type="text" name="submit" class="tag code" readonly="readonly" onfocus="this.select()" />

	<div class="submitbox">
	<input type="button" class="button button-primary insert-tag" value="<?php echo esc_attr( __( 'Insert Tag', 'jw-contact-support' ) ); ?>" />
	</div>
</div>
<?php
}
