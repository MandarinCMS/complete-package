<?php

// don't load directly
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

function mcmscf7_admin_save_button( $post_id ) {
	static $button = '';

	if ( ! empty( $button ) ) {
		echo $button;
		return;
	}

	$nonce = mcms_create_nonce( 'mcmscf7-save-contact-support_' . $post_id );

	$onclick = sprintf(
		"this.form._mcmsnonce.value = '%s';"
		. " this.form.action.value = 'save';"
		. " return true;",
		$nonce );

	$button = sprintf(
		'<input type="submit" class="button-primary" name="mcmscf7-save" value="%1$s" onclick="%2$s" />',
		esc_attr( __( 'Save', 'jw-contact-support' ) ),
		$onclick );

	echo $button;
}

?><div class="wrap">

<h1 class="mcms-heading-inline"><?php
	if ( $post->initial() ) {
		echo esc_html( __( 'Add New Contact Form', 'jw-contact-support' ) );
	} else {
		echo esc_html( __( 'Edit Contact Form', 'jw-contact-support' ) );
	}
?></h1>

<?php
	if ( ! $post->initial() && current_user_can( 'mcmscf7_edit_contact_forms' ) ) {
		echo sprintf( '<a href="%1$s" class="add-new-h2">%2$s</a>',
			esc_url( menu_page_url( 'mcmscf7-new', false ) ),
			esc_html( __( 'Add New', 'jw-contact-support' ) ) );
	}
?>

<hr class="mcms-header-end">

<?php do_action( 'mcmscf7_admin_warnings' ); ?>
<?php do_action( 'mcmscf7_admin_notices' ); ?>

<?php
if ( $post ) :

	if ( current_user_can( 'mcmscf7_edit_contact_form', $post_id ) ) {
		$disabled = '';
	} else {
		$disabled = ' disabled="disabled"';
	}
?>

<form method="post" action="<?php echo esc_url( add_query_arg( array( 'post' => $post_id ), menu_page_url( 'mcmscf7', false ) ) ); ?>" id="mcmscf7-admin-form-element"<?php do_action( 'mcmscf7_post_edit_form_tag' ); ?>>
<?php
	if ( current_user_can( 'mcmscf7_edit_contact_form', $post_id ) ) {
		mcms_nonce_field( 'mcmscf7-save-contact-support_' . $post_id );
	}
?>
<input type="hidden" id="post_ID" name="post_ID" value="<?php echo (int) $post_id; ?>" />
<input type="hidden" id="mcmscf7-locale" name="mcmscf7-locale" value="<?php echo esc_attr( $post->locale() ); ?>" />
<input type="hidden" id="hiddenaction" name="action" value="save" />
<input type="hidden" id="active-tab" name="active-tab" value="<?php echo isset( $_GET['active-tab'] ) ? (int) $_GET['active-tab'] : '0'; ?>" />

<div id="poststuff">
<div id="post-body" class="metabox-holder columns-2">
<div id="post-body-content">
<div id="titlediv">
<div id="titlewrap">
	<label class="screen-reader-text" id="title-prompt-text" for="title"><?php echo esc_html( __( 'Enter title here', 'jw-contact-support' ) ); ?></label>
<?php
	$posttitle_atts = array(
		'type' => 'text',
		'name' => 'post_title',
		'size' => 30,
		'value' => $post->initial() ? '' : $post->title(),
		'id' => 'title',
		'spellcheck' => 'true',
		'autocomplete' => 'off',
		'disabled' =>
			current_user_can( 'mcmscf7_edit_contact_form', $post_id ) ? '' : 'disabled',
	);

	echo sprintf( '<input %s />', mcmscf7_format_atts( $posttitle_atts ) );
?>
</div><!-- #titlewrap -->

<div class="inside">
<?php
	if ( ! $post->initial() ) :
?>
	<p class="description">
	<label for="mcmscf7-shortcode"><?php echo esc_html( __( "Copy this shortcode and paste it into your post, page, or text widget content:", 'jw-contact-support' ) ); ?></label>
	<span class="shortcode mcms-ui-highlight"><input type="text" id="mcmscf7-shortcode" onfocus="this.select();" readonly="readonly" class="large-text code" value="<?php echo esc_attr( $post->shortcode() ); ?>" /></span>
	</p>
<?php
		if ( $old_shortcode = $post->shortcode( array( 'use_old_format' => true ) ) ) :
?>
	<p class="description">
	<label for="mcmscf7-shortcode-old"><?php echo esc_html( __( "You can also use this old-style shortcode:", 'jw-contact-support' ) ); ?></label>
	<span class="shortcode old"><input type="text" id="mcmscf7-shortcode-old" onfocus="this.select();" readonly="readonly" class="large-text code" value="<?php echo esc_attr( $old_shortcode ); ?>" /></span>
	</p>
<?php
		endif;
	endif;
?>
</div>
</div><!-- #titlediv -->
</div><!-- #post-body-content -->

<div id="postbox-container-1" class="postbox-container">
<?php if ( current_user_can( 'mcmscf7_edit_contact_form', $post_id ) ) : ?>
<div id="submitdiv" class="postbox">
<h3><?php echo esc_html( __( 'Status', 'jw-contact-support' ) ); ?></h3>
<div class="inside">
<div class="submitbox" id="submitpost">

<div id="minor-publishing-actions">

<div class="hidden">
	<input type="submit" class="button-primary" name="mcmscf7-save" value="<?php echo esc_attr( __( 'Save', 'jw-contact-support' ) ); ?>" />
</div>

<?php
	if ( ! $post->initial() ) :
		$copy_nonce = mcms_create_nonce( 'mcmscf7-copy-contact-support_' . $post_id );
?>
	<input type="submit" name="mcmscf7-copy" class="copy button" value="<?php echo esc_attr( __( 'Duplicate', 'jw-contact-support' ) ); ?>" <?php echo "onclick=\"this.form._mcmsnonce.value = '$copy_nonce'; this.form.action.value = 'copy'; return true;\""; ?> />
<?php endif; ?>
</div><!-- #minor-publishing-actions -->

<div id="misc-publishing-actions">
<?php do_action( 'mcmscf7_admin_misc_pub_section', $post_id ); ?>
</div><!-- #misc-publishing-actions -->

<div id="major-publishing-actions">

<?php
	if ( ! $post->initial() ) :
		$delete_nonce = mcms_create_nonce( 'mcmscf7-delete-contact-support_' . $post_id );
?>
<div id="delete-action">
	<input type="submit" name="mcmscf7-delete" class="delete submitdelete" value="<?php echo esc_attr( __( 'Delete', 'jw-contact-support' ) ); ?>" <?php echo "onclick=\"if (confirm('" . esc_js( __( "You are about to delete this contact form.\n  'Cancel' to stop, 'OK' to delete.", 'jw-contact-support' ) ) . "')) {this.form._mcmsnonce.value = '$delete_nonce'; this.form.action.value = 'delete'; return true;} return false;\""; ?> />
</div><!-- #delete-action -->
<?php endif; ?>

<div id="publishing-action">
	<span class="spinner"></span>
	<?php mcmscf7_admin_save_button( $post_id ); ?>
</div>
<div class="clear"></div>
</div><!-- #major-publishing-actions -->
</div><!-- #submitpost -->
</div>
</div><!-- #submitdiv -->
<?php endif; ?>
 
</div><!-- #postbox-container-1 -->

<div id="postbox-container-2" class="postbox-container">
<div id="contact-support-editor">
<div class="keyboard-interaction"><?php
	echo sprintf(
		/* translators: 1: ◀ ▶ dashicon, 2: screen reader text for the dashicon */
		esc_html( __( '%1$s %2$s keys switch panels', 'jw-contact-support' ) ),
		'<span class="dashicons dashicons-leftright" aria-hidden="true"></span>',
		sprintf(
			'<span class="screen-reader-text">%s</span>',
			/* translators: screen reader text */
			esc_html( __( '(left and right arrow)', 'jw-contact-support' ) )
		)
	);
?></div>

<?php

	$editor = new MCMSCF7_Editor( $post );
	$panels = array();

	if ( current_user_can( 'mcmscf7_edit_contact_form', $post_id ) ) {
		$panels = array(
			'form-panel' => array(
				'title' => __( 'Form', 'jw-contact-support' ),
				'callback' => 'mcmscf7_editor_panel_form',
			),
			'mail-panel' => array(
				'title' => __( 'Mail', 'jw-contact-support' ),
				'callback' => 'mcmscf7_editor_panel_mail',
			),
			'messages-panel' => array(
				'title' => __( 'Messages', 'jw-contact-support' ),
				'callback' => 'mcmscf7_editor_panel_messages',
			),
		);

		$additional_settings = trim( $post->prop( 'additional_settings' ) );
		$additional_settings = explode( "\n", $additional_settings );
		$additional_settings = array_filter( $additional_settings );
		$additional_settings = count( $additional_settings );

		$panels['additional-settings-panel'] = array(
			'title' => $additional_settings
				/* translators: %d: number of additional settings */
				? sprintf(
					__( 'Additional Settings (%d)', 'jw-contact-support' ),
					$additional_settings )
				: __( 'Additional Settings', 'jw-contact-support' ),
			'callback' => 'mcmscf7_editor_panel_additional_settings',
		);
	}

	$panels = apply_filters( 'mcmscf7_editor_panels', $panels );

	foreach ( $panels as $id => $panel ) {
		$editor->add_panel( $id, $panel['title'], $panel['callback'] );
	}

	$editor->display();
?>
</div><!-- #contact-support-editor -->

<?php if ( current_user_can( 'mcmscf7_edit_contact_form', $post_id ) ) : ?>
<p class="submit"><?php mcmscf7_admin_save_button( $post_id ); ?></p>
<?php endif; ?>

</div><!-- #postbox-container-2 -->

</div><!-- #post-body -->
<br class="clear" />
</div><!-- #poststuff -->
</form>

<?php endif; ?>

</div><!-- .wrap -->

<?php

	$tag_generator = MCMSCF7_TagGenerator::get_instance();
	$tag_generator->print_panels( $post );

	do_action( 'mcmscf7_admin_footer', $post );
