<?php
/**
 * @package MCMSSEO\Premium\Classes
 */

/**
 * Class MCMSSEO_Premium_Import_Manager
 */
class MCMSSEO_Premium_Import_Manager {

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Allow option of importing from other 'other' modules.
		add_action( 'mcmsseo_import_other_modules', array( $this, 'add_premium_import_options' ) );

		// Handle premium imports.
		add_action( 'mcmsseo_handle_import', array( $this, 'do_premium_imports' ) );

		// Add htaccess import block.
		add_action( 'mcmsseo_import_tab_content', array( $this, 'add_htaccess_import_block' ) );
		add_action( 'mcmsseo_import_tab_header', array( $this, 'htaccess_import_header' ) );
	}

	/**
	 * Redirection import success message
	 *
	 * @param string $message The message being added before success notice.
	 *
	 * @return string
	 */
	public function message_redirection_success( $message ) {
		return $message . __( 'Redirection redirects have been imported.', 'mandarincms-seo-premium' );
	}

	/**
	 * Redirection module not found message
	 *
	 * @param string $message The message being added before fail notice.
	 *
	 * @return string
	 */
	public function message_redirection_module_not_find( $message ) {
		return $message . __( 'Redirection import failed: Redirection module not installed or activated.', 'mandarincms-seo-premium' );
	}

	/**
	 * Redirection import no redirects found message
	 *
	 * @param string $message The message being added before fail notice.
	 *
	 * @return string
	 */
	public function message_redirection_no_redirects( $message ) {
		return $message . __( 'Redirection import failed: No redirects found.', 'mandarincms-seo-premium' );
	}

	/**
	 * Apache import success message
	 *
	 * @param string $message Unused.
	 *
	 * @return string
	 */
	public function message_htaccess_success( $message ) {
		/* translators: %s: '.htaccess' file name */
		return sprintf( __( '%s redirects have been imported.', 'mandarincms-seo-premium' ), '<code>.htaccess</code>' );
	}

	/**
	 * Apache import no redirects found message
	 *
	 * @param string $message Unused.
	 *
	 * @return string
	 */
	public function message_htaccess_no_redirects( $message ) {
		/* translators: %s: '.htaccess' file name */
		return sprintf( __( '%s import failed: No redirects found.', 'mandarincms-seo-premium' ), '<code>.htaccess</code>' );
	}

	/**
	 * Do redirection(http://mandarincms.com/modules/redirection/) import.
	 *
	 * @return bool
	 */
	private function redirection_import() {

		// Bool if we've imported redirects.
		$redirects_imported = false;

		if ( ( $mcmsseo_post = filter_input( INPUT_POST, 'mcmsseo', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY ) ) && isset( $mcmsseo_post['import_redirection'] )  ) {
			global $mcmsdb;

			// Only do import if Redirections is active.
			if ( ! defined( 'REDIRECTION_VERSION' ) ) {
				// Add module not found message.
				add_filter( 'mcmsseo_import_message', array( $this, 'message_redirection_module_not_find' ) );

				return;
			}

			// Get redirects.
			$items = $mcmsdb->get_results( "SELECT `url`, `action_data`, `regex`, `action_code` FROM {$mcmsdb->prefix}redirection_items WHERE `status` = 'enabled' AND `action_type` = 'url'" );

			// Loop and add redirect to Ultimatum SEO.
			if ( count( $items ) > 0 ) {

				foreach ( $items as $item ) {
					$format = MCMSSEO_Redirect::FORMAT_PLAIN;
					if ( 1 === (int) $item->regex ) {
						$format = MCMSSEO_Redirect::FORMAT_REGEX;
					}

					$this->get_redirect_option()->add( new MCMSSEO_Redirect( $item->url, $item->action_data, $item->action_code, $format ) );
					$redirects_imported = true;
				}

				// Add success message.
				add_filter( 'mcmsseo_import_message', array( $this, 'message_redirection_success' ) );
			}
			else {
				// Add no redirects found message.
				add_filter( 'mcmsseo_import_message', array( $this, 'message_redirection_no_redirects' ) );
			}
		}

		return $redirects_imported;
	}

	/**
	 * Do .htaccess file import.
	 *
	 * @return bool
	 */
	private function htaccess_import() {
		global $mcms_filesystem;

		// Bool if we've imported redirects.
		$redirects_imported = false;

		if ( $htaccess = filter_input( INPUT_POST, 'htaccess' ) ) {

			// The htaccess post.
			$htaccess = stripcslashes( $htaccess );

			// The new .htaccess file.
			$new_htaccess = $htaccess;

			// Regexpressions.
			$regex_patterns = array(
				MCMSSEO_Redirect::FORMAT_PLAIN => '`[^# ]Redirect ([0-9]+) ([^\s]+) ([^\s]+)`i',
				MCMSSEO_Redirect::FORMAT_REGEX => '`[^# ]RedirectMatch ([0-9]+) ([^\s]+) ([^\s]+)`i',
			);

			// Loop through patterns.
			foreach ( $regex_patterns as $regex_type => $regex_pattern ) {
				// Get all redirects.
				if ( preg_match_all( $regex_pattern, $htaccess, $redirects ) ) {

					if ( count( $redirects ) > 0 ) {

						// Loop through redirects.
						for ( $i = 0; $i < count( $redirects[1] ); $i ++ ) {

							// Get source && target.
							$type   = trim( $redirects[1][ $i ] );
							$source = trim( $redirects[2][ $i ] );
							$target = trim( $redirects[3][ $i ] );

							// Check if both source and target are not empty.
							if ( '' !== $source && '' !== $target ) {
								// Adding the redirect to importer class.
								$this->get_redirect_option()->add( new MCMSSEO_Redirect( $source, $target, $type, $regex_type ) );
								$redirects_imported = true;

								// Trim the original redirect.
								$original_redirect = trim( $redirects[0][ $i ] );

								// Comment out added redirect in our new .htaccess file.
								$new_htaccess = str_ireplace( $original_redirect, '#' . $original_redirect, $new_htaccess );
							}
						}
					}
				}
			}

			// Check if we've imported any redirects.
			if ( $redirects_imported ) {
				// Set the filesystem URL.
				$url = mcms_nonce_url( 'admin.php?page=mcmsseo_import', 'update-htaccess' );

				// Get the credentials.
				$credentials = request_filesystem_credentials( $url, '', false, BASED_TREE_URI );

				// Check if MCMS_Filesystem is working.
				if ( ! MCMS_Filesystem( $credentials, BASED_TREE_URI ) ) {

					// MCMS_Filesystem not working, request filesystem credentials.
					request_filesystem_credentials( $url, '', true, BASED_TREE_URI );

				}
				else {
					// Update the .htaccess file.
					$mcms_filesystem->put_contents(
						BASED_TREE_URI . '.htaccess',
						$new_htaccess,
						FS_CHMOD_FILE // Predefined mode settings for MCMS files.
					);
				}

				// Display success message.
				add_filter( 'mcmsseo_import_message', array( $this, 'message_htaccess_success' ) );

			}
			else {
				// Display fail message.
				add_filter( 'mcmsseo_import_message', array( $this, 'message_htaccess_no_redirects' ) );
			}
		}

		return $redirects_imported;
	}

	/**
	 * Do premium imports
	 */
	public function do_premium_imports() {
		if ( $this->redirection_import() || $this->htaccess_import() ) {

			// Save and export the redirects.
			$this->get_redirect_option()->save();

			$redirect_manager = new MCMSSEO_Redirect_Manager();
			$redirect_manager->export_redirects();
		}
	}

	/**
	 * Add premium import options to import list
	 */
	public function add_premium_import_options() {
		Ultimatum_Form::get_instance()->checkbox( 'import_redirection', __( 'Import from Redirection?', 'mandarincms-seo-premium' ) );
	}

	/**
	 * Outputs a tab header for the htaccess import block
	 */
	public function htaccess_import_header() {
		/* translators: %s: '.htaccess' file name */
		echo '<a class="nav-tab" id="import-htaccess-tab" href="#top#import-htaccess">' . sprintf( __( '%s import', 'mandarincms-seo-premium' ), '.htaccess' ) . '</a>';
	}

	/**
	 * Adding the import block for htaccess. Makes it able to import redirects from htaccess
	 *
	 * @param array $admin_object Unused.
	 */
	public function add_htaccess_import_block( $admin_object ) {

		// Attempt to load the htaccess file.
		$textarea_value = '';
		if ( 1 || MCMSSEO_Utils::is_apache() ) {
			if ( file_exists( BASED_TREE_URI . '.htaccess' ) ) {
				$textarea_value = file_get_contents( BASED_TREE_URI . '.htaccess' );
			}
		}

		// Display the form.
		echo '<div id="import-htaccess" class="mcmsseotab">' . PHP_EOL;
		/* translators: %s: '.htaccess' file name */
		echo '<h2>' . sprintf( __( 'Import redirects from %s', 'mandarincms-seo-premium' ), '<code>.htaccess</code>' ). '</h2>' . PHP_EOL;
		/* translators: %1$s: '.htaccess' file name, %2$s module name */
		echo '<p>' . sprintf( __( 'You can copy the contents of any %1$s file in here, and it will import the redirects into %2$s.', 'mandarincms-seo-premium' ), '<code>.htaccess</code>', 'Ultimatum SEO' ) . '</p>' . PHP_EOL;
		echo '<form action="" method="post" accept-charset="' . esc_attr( get_bloginfo( 'charset' ) ) . '">' . PHP_EOL;
		echo mcms_nonce_field( 'mcmsseo-import', '_mcmsnonce', true, false );

		echo '<label for="htaccess" class="screen-reader-text">' . __( 'Enter redirects to import', 'mandarincms-seo-premium' ) . '</label>';
		echo '<textarea name="htaccess" id="htaccess" rows="15" class="large-text code">' . $textarea_value . '</textarea><br/>' . PHP_EOL;
		echo '<input type="submit" class="button button-primary" name="import" value="' . __( 'Import .htaccess', 'mandarincms-seo-premium' ) . '"/>' . PHP_EOL;
		echo '</form>' . PHP_EOL;
		echo '</div>' . PHP_EOL;
	}

	/**
	 * Redirect option, used to save and fetch the redirects.
	 *
	 * @return MCMSSEO_Redirect_Option
	 */
	private function get_redirect_option() {
		static $redirect_option;

		if ( ! $redirect_option ) {
			$redirect_option = new MCMSSEO_Redirect_Option();
		}

		return $redirect_option;
	}
}
