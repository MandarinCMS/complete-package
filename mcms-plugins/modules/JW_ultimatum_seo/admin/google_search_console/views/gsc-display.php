<?php
/**
 * @package MCMSSEO\Admin|Google_Search_Console
 */

	// Admin header.
	Ultimatum_Form::get_instance()->admin_header( false, 'mcmsseo-gsc', false, 'ultimatum_mcmsseo_gsc_options' );
?>
	<h2 class="nav-tab-wrapper" id="mcmsseo-tabs">
<?php
if ( defined( 'MCMS_DEBUG' ) && MCMS_DEBUG && MCMSSEO_GSC_Settings::get_profile() !== '' ) {
	?>
		<form action="" method="post">
			<input type='hidden' name='reload-crawl-issues-nonce' value='<?php echo mcms_create_nonce( 'reload-crawl-issues' ); ?>' />
			<input type="submit" name="reload-crawl-issues" id="reload-crawl-issue" class="button button-primary alignright"
				   value="<?php _e( 'Reload crawl issues', 'mandarincms-seo' ); ?>">
		</form>
<?php } ?>
		<?php echo $platform_tabs = new MCMSSEO_GSC_Platform_Tabs; ?>
	</h2>

<?php

// Video explains about the options when connected only.
if ( null !== $this->service->get_client()->getAccessToken() ) {
	$video_url = 'https://jiiworks.net/screencast-search-console';
}
else {
	$video_url = 'https://jiiworks.net/screencast-connect-search-console';
}

$tab = new MCMSSEO_Option_Tab( 'GSC', __( 'Google Search Console' ), array( 'video_url' => $video_url ) );
$GSCHelpCenter = new MCMSSEO_Help_Center( 'google-search-console', $tab );
$GSCHelpCenter->output_help_center();

switch ( $platform_tabs->current_tab() ) {
	case 'settings' :
		// Check if there is an access token.
		if ( null === $this->service->get_client()->getAccessToken() ) {
			// Print auth screen.
			echo '<p>';
			/* Translators: %1$s: expands to 'Ultimatum SEO', %2$s expands to Google Search Console. */
			echo sprintf( __( 'To allow %1$s to fetch your %2$s information, please enter your Google Authorization Code. Clicking the button below will open a new window.', 'mandarincms-seo' ), 'Ultimatum SEO', 'Google Search Console' );
			echo "</p>\n";
			echo '<input type="hidden" id="gsc_auth_url" value="', $this->service->get_client()->createAuthUrl() , '" />';
			echo "<button type='button' id='gsc_auth_code' class='button'>" , __( 'Get Google Authorization Code', 'mandarincms-seo' ) ,"</button>\n";

			echo '<p id="gsc-enter-code-label">' . __( 'Enter your Google Authorization Code and press the Authenticate button.', 'mandarincms-seo' ) . "</p>\n";
			echo "<form action='" . admin_url( 'admin.php?page=mcmsseo_search_console&tab=settings' ) . "' method='post'>\n";
			echo "<input type='text' name='gsc[authorization_code]' value='' class='textinput' aria-labelledby='gsc-enter-code-label' />";
			echo "<input type='hidden' name='gsc[gsc_nonce]' value='" . mcms_create_nonce( 'mcmsseo-gsc_nonce' ) . "' />";
			echo "<input type='submit' name='gsc[Submit]' value='" . __( 'Authenticate', 'mandarincms-seo' ) . "' class='button button-primary' />";
			echo "</form>\n";
		}
		else {
			$reset_button = '<a class="button" href="' . add_query_arg( 'gsc_reset', 1 ) . '">' . __( 'Reauthenticate with Google ', 'mandarincms-seo' ) . '</a>';
			echo '<h3>',  __( 'Current profile', 'mandarincms-seo' ), '</h3>';
			if ( ($profile = MCMSSEO_GSC_Settings::get_profile() ) !== '' ) {
				echo '<p>';
				echo $profile;
				echo '</p>';

				echo '<p>';
				echo $reset_button;
				echo '</p>';

			}
			else {
				echo "<form action='" . admin_url( 'options.php' ) . "' method='post'>";

				settings_fields( 'ultimatum_mcmsseo_gsc_options' );
				Ultimatum_Form::get_instance()->set_option( 'mcmsseo-gsc' );

				echo '<p>';
				if ( $profiles = $this->service->get_sites() ) {
					$show_save = true;
					echo Ultimatum_Form::get_instance()->select( 'profile', __( 'Profile', 'mandarincms-seo' ), $profiles );
				}
				else {
					$show_save = false;
					echo __( 'There were no profiles found', 'mandarincms-seo' );
				}
				echo '</p>';

				echo '<p>';

				if ( $show_save ) {
					echo '<input type="submit" name="submit" id="submit" class="button button-primary mcmsseo-gsc-save-profile" value="' . __( 'Save Profile', 'mandarincms-seo' ) . '" /> ' . __( 'or', 'mandarincms-seo' ) , ' ';
				}
				echo $reset_button;
				echo '</p>';
				echo '</form>';
			}
		}
		break;

	default :
		$form_action_url = add_query_arg( 'page', esc_attr( filter_input( INPUT_GET, 'page' ) ) );

		get_current_screen()->set_screen_reader_content( array(
			// There are no views links in this screen, so no need for the views heading.
			'heading_views'      => null,
			'heading_pagination' => __( 'Crawl issues list navigation', 'mandarincms-seo' ),
			'heading_list'       => __( 'Crawl issues list', 'mandarincms-seo' ),
		) );

		// Open <form>.
		echo "<form id='mcmsseo-crawl-issues-table-form' action='" . $form_action_url . "' method='post'>\n";

		// AJAX nonce.
		echo "<input type='hidden' class='mcmsseo-gsc-ajax-security' value='" . mcms_create_nonce( 'mcmsseo-gsc-ajax-security' ) . "' />\n";

		$this->display_table();

		// Close <form>.
		echo "</form>\n";

		break;
}
?>
	<br class="clear" />
<?php

// Admin footer.
Ultimatum_Form::get_instance()->admin_footer( false );
