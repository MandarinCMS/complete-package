<?php

function mcmscf7_welcome_panel() {
	$classes = 'welcome-panel';

	$vers = (array) get_user_meta( get_current_user_id(),
		'mcmscf7_hide_welcome_panel_on', true );

	if ( mcmscf7_version_grep( mcmscf7_version( 'only_major=1' ), $vers ) ) {
		$classes .= ' hidden';
	}

?>
<div id="welcome-panel" class="<?php echo esc_attr( $classes ); ?>">
	<?php mcms_nonce_field( 'mcmscf7-welcome-panel-nonce', 'welcomepanelnonce', false ); ?>
	<a class="welcome-panel-close" href="<?php echo esc_url( menu_page_url( 'mcmscf7', false ) ); ?>"><?php echo esc_html( __( 'Dismiss', 'jw-contact-support' ) ); ?></a>

	<div class="welcome-panel-content">
		<div class="welcome-panel-column-container">

			<div class="welcome-panel-column">
				<h3><span class="dashicons dashicons-shield" aria-hidden="true"></span> <?php echo esc_html( __( "Getting spammed? You have protection.", 'jw-contact-support' ) ); ?></h3>

				<p><?php echo esc_html( __( "Spammers target everything; your contact forms aren&#8217;t an exception. Before you get spammed, protect your contact forms with the powerful anti-spam features JW Contact Supportprovides.", 'jw-contact-support' ) ); ?></p>

				<p><?php /* translators: links labeled 1: 'Akismet', 2: 'reCAPTCHA', 3: 'comment blacklist' */ echo sprintf( esc_html( __( 'JW Contact Supportsupports spam-filtering with %1$s. Intelligent %2$s blocks annoying spambots. Plus, using %3$s, you can block messages containing specified keywords or those sent from specified IP addresses.', 'jw-contact-support' ) ), mcmscf7_link( __( 'https://jiiworks.net/spam-filtering-with-akismet/', 'jw-contact-support' ), __( 'Akismet', 'jw-contact-support' ) ), mcmscf7_link( __( 'https://jiiworks.net/recaptcha/', 'jw-contact-support' ), __( 'reCAPTCHA', 'jw-contact-support' ) ), mcmscf7_link( __( 'https://jiiworks.net/comment-blacklist/', 'jw-contact-support' ), __( 'comment blacklist', 'jw-contact-support' ) ) ); ?></p>
			</div>

<?php if ( defined( 'FLAMINGO_VERSION' ) ) : ?>
			<div class="welcome-panel-column">
				<h3><span class="dashicons dashicons-megaphone" aria-hidden="true"></span> <?php echo esc_html( __( "JW Contact Supportneeds your support.", 'jw-contact-support' ) ); ?></h3>

				<p><?php echo esc_html( __( "It is hard to continue development and support for this module without contributions from users like you.", 'jw-contact-support' ) ); ?></p>

				<p><?php /* translators: %s: link labeled 'making a donation' */ echo sprintf( esc_html( __( 'If you enjoy using JW Contact Supportand find it useful, please consider %s.', 'jw-contact-support' ) ), mcmscf7_link( __( 'https://jiiworks.net/donate/', 'jw-contact-support' ), __( 'making a donation', 'jw-contact-support' ) ) ); ?></p>

				<p><?php echo esc_html( __( "Your donation will help encourage and support the module&#8217;s continued development and better user support.", 'jw-contact-support' ) ); ?></p>
			</div>
<?php else: ?>
			<div class="welcome-panel-column">
				<h3><span class="dashicons dashicons-editor-help" aria-hidden="true"></span> <?php echo esc_html( __( "Before you cry over spilt mail&#8230;", 'jw-contact-support' ) ); ?></h3>

				<p><?php echo esc_html( __( "JW Contact Supportdoesn&#8217;t store submitted messages anywhere. Therefore, you may lose important messages forever if your mail server has issues or you make a mistake in mail configuration.", 'jw-contact-support' ) ); ?></p>

				<p><?php /* translators: %s: link labeled 'Flamingo' */ echo sprintf( esc_html( __( 'Install a message storage module before this happens to you. %s saves all messages through contact forms into the database. Flamingo is a free MandarinCMS module created by the same author as Contact Form 7.', 'jw-contact-support' ) ), mcmscf7_link( __( 'https://jiiworks.net/save-submitted-messages-with-flamingo/', 'jw-contact-support' ), __( 'Flamingo', 'jw-contact-support' ) ) ); ?></p>
			</div>
<?php endif; ?>

		</div>
	</div>
</div>
<?php
}

add_action( 'mcms_ajax_mcmscf7-update-welcome-panel', 'mcmscf7_admin_ajax_welcome_panel' );

function mcmscf7_admin_ajax_welcome_panel() {
	check_ajax_referer( 'mcmscf7-welcome-panel-nonce', 'welcomepanelnonce' );

	$vers = get_user_meta( get_current_user_id(),
		'mcmscf7_hide_welcome_panel_on', true );

	if ( empty( $vers ) || ! is_array( $vers ) ) {
		$vers = array();
	}

	if ( empty( $_POST['visible'] ) ) {
		$vers[] = mcmscf7_version( 'only_major=1' );
	}

	$vers = array_unique( $vers );

	update_user_meta( get_current_user_id(), 'mcmscf7_hide_welcome_panel_on', $vers );

	mcms_die( 1 );
}
