<?php
/**
 * @package MCMSSEO\Premium\Classes
 */

/**
 * Class MCMSSEO_Redirect_Page
 */
class MCMSSEO_Redirect_Page {

	/**
	 * Constructing redirect module
	 */
	public function __construct() {
		if ( is_admin() ) {
			$this->initialize_admin();
		}

		// Only initialize the ajax for all tabs except settings.
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			$this->initialize_ajax();
		}
	}

	/**
	 * Display the presenter
	 */
	public function display() {
		$redirect_presenter = new MCMSSEO_Redirect_Presenter();
		$redirect_presenter->display( $this->get_current_tab() );
	}

	/**
	 * Catch the redirects search post and redirect it to a search get
	 */
	public function list_table_search() {
		if ( ( $search_string = filter_input( INPUT_POST, 's' ) ) !== null ) {
			$url = ( $search_string !== '' ) ? add_query_arg( 's', $search_string ) : remove_query_arg( 's' );

			// Do the redirect.
			mcms_redirect( $url );
			exit;
		}
	}

	/**
	 * Load the admin redirects scripts
	 */
	public function enqueue_assets() {
		mcms_enqueue_script( 'mcmsseo-premium-ultimatum-overlay', module_dir_url( MCMSSEO_PREMIUM_FILE ) . 'assets/js/dist/mcmsseo-premium-ultimatum-overlay-350' . MCMSSEO_CSSJS_SUFFIX . '.js', array( 'jquery' ), MCMSSEO_VERSION );
		mcms_enqueue_script(
			'mcms-seo-premium-admin-redirects',
			module_dir_url( MCMSSEO_PREMIUM_FILE ) .
			'assets/js/dist/mcms-seo-premium-admin-redirects-370' . MCMSSEO_CSSJS_SUFFIX . '.js',
			array( 'jquery', 'jquery-ui-dialog', 'mcms-util', 'underscore' ),
			MCMSSEO_VERSION
		);
		mcms_localize_script( 'mcms-seo-premium-admin-redirects', 'mcmsseo_premium_strings', MCMSSEO_Premium_Javascript_Strings::strings() );
		mcms_localize_script( 'mcms-seo-premium-admin-redirects', 'mcmsseoSelect2Locale', substr( get_locale(), 0, 2 ) );

		mcms_enqueue_style( 'mcmsseo-premium-redirects', module_dir_url( MCMSSEO_PREMIUM_FILE ) . 'assets/css/dist/premium-redirects-' . '340' . MCMSSEO_CSSJS_SUFFIX . '.css', array(), MCMSSEO_VERSION );

		mcms_enqueue_style( 'mcms-jquery-ui-dialog' );

		add_screen_option( 'per_page', array(
			'label'   => __( 'Redirects per page', 'mandarincms-seo-premium' ),
			'default' => 25,
			'option'  => 'redirects_per_page',
		) );
	}

	/**
	 * Catch redirects_per_page
	 *
	 * @param string $status Unused.
	 * @param string $option The option name where the value is set for.
	 * @param string $value  The new value for the screen option.
	 *
	 * @return string|void
	 */
	public function set_screen_option( $status, $option, $value ) {
		if ( 'redirects_per_page' === $option ) {
			return $value;
		}
	}

	/**
	 * Get the Ultimatum SEO options
	 *
	 * @return array
	 */
	public static function get_options() {
		static $options;

		if ( $options === null ) {
			$options = apply_filters(
				'mcmsseo_premium_redirect_options',
				mcms_parse_args(
					get_option( 'mcmsseo_redirect', array() ),
					array(
						'disable_php_redirect' => 'off',
						'separate_file'        => 'off',
					)
				)
			);
		}

		return $options;
	}

	/**
	 * Hook that runs after the 'mcmsseo_redirect' option is updated
	 *
	 * @param array $old_value Unused.
	 * @param array $value     The new saved values.
	 */
	public function save_redirect_files( $old_value, $value ) {

		$is_php = ( empty( $value['disable_php_redirect'] ) || 'on' !== $value['disable_php_redirect'] );

		$was_separate_file = ( ! empty( $old_value['separate_file'] ) && 'on' === $old_value['separate_file'] );
		$is_separate_file  = ( ! empty( $value['separate_file'] ) && 'on' === $value['separate_file'] );

		// Check if the 'disable_php_redirect' option set to true/on.
		if ( ! $is_php ) {
			// The 'disable_php_redirect' option is set to true(on) so we need to generate a file.
			// The Redirect Manager will figure out what file needs to be created.
			$redirect_manager = new MCMSSEO_Redirect_Manager();
			$redirect_manager->export_redirects();
		}

		// Check if we need to remove the .htaccess redirect entries.
		if ( MCMSSEO_Utils::is_apache() ) {
			if ( $is_php || ( ! $was_separate_file && $is_separate_file ) ) {
				// Remove the apache redirect entries.
				MCMSSEO_Redirect_Htaccess_Util::clear_htaccess_entries();
			}

			if ( $is_php || ( $was_separate_file && ! $is_separate_file ) ) {
				// Remove the apache separate file redirect entries.
				MCMSSEO_Redirect_File_Util::write_file( MCMSSEO_Redirect_File_Util::get_file_path(), '' );
			}
		}

		if ( MCMSSEO_Utils::is_nginx() && $is_php ) {
			// Remove the nginx redirect entries.
			$this->clear_nginx_redirects();
		}

	}

	/**
	 * The server should always be apache. And the php redirects have to be enabled or in case of a separate
	 * file it should be disabled.
	 *
	 * @param boolean $disable_php_redirect Are the php redirects disabled.
	 * @param boolean $separate_file        Value of the separate file.
	 *
	 * @return bool
	 */
	private function remove_htaccess_entries( $disable_php_redirect, $separate_file ) {
		return ( MCMSSEO_Utils::is_apache() && ( ! $disable_php_redirect || ( $disable_php_redirect && $separate_file ) ) );
	}

	/**
	 * Clears the redirects from the nginx config.
	 */
	private function clear_nginx_redirects() {
		$redirect_file = MCMSSEO_Redirect_File_Util::get_file_path();
		if ( is_writable( $redirect_file ) ) {
			MCMSSEO_Redirect_File_Util::write_file( $redirect_file, '' );
		}
	}

	/**
	 * Initialize admin hooks.
	 */
	private function initialize_admin() {
		$this->fetch_bulk_action();

		// Check if we need to save files after updating options.
		add_action( 'update_option_mcmsseo_redirect', array( $this, 'save_redirect_files' ), 10, 2 );

		// Convert post into get on search and loading the page scripts.
		if ( filter_input( INPUT_GET, 'page' ) === 'mcmsseo_redirects' ) {
			$upgrade_manager = new MCMSSEO_Upgrade_Manager();
			$upgrade_manager->retry_upgrade_31();

			add_action( 'admin_init', array( $this, 'list_table_search' ) );

			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
			add_filter( 'set-screen-option', array( $this, 'set_screen_option' ), 11, 3 );
		}
	}

	/**
	 * Initialize the AJAX redirect files
	 */
	private function initialize_ajax() {
		// Normal Redirect AJAX.
		new MCMSSEO_Redirect_Ajax( MCMSSEO_Redirect::FORMAT_PLAIN );

		// Regex Redirect AJAX.
		new MCMSSEO_Redirect_Ajax( MCMSSEO_Redirect::FORMAT_REGEX );
	}

	/**
	 * Getting the current active tab
	 *
	 * @return string
	 */
	private function get_current_tab() {
		static $current_tab;

		if ( $current_tab === null ) {
			$current_tab = filter_input(
				INPUT_GET,
				'tab',
				FILTER_VALIDATE_REGEXP,
				array( 'options' => array( 'default' => 'plain', 'regexp' => '/^(plain|regex|settings)$/' ) )
			);
		}

		return $current_tab;
	}

	/**
	 * Setting redirect manager, based on the current active tab
	 *
	 * @return MCMSSEO_Redirect_Manager
	 */
	private function get_redirect_manager() {
		static $redirect_manager;

		if ( $redirect_manager === null ) {
			$redirects_format = MCMSSEO_Redirect::FORMAT_PLAIN;
			if ( $this->get_current_tab() === MCMSSEO_Redirect::FORMAT_REGEX ) {
				$redirects_format = MCMSSEO_Redirect::FORMAT_REGEX;
			}

			$redirect_manager = new MCMSSEO_Redirect_Manager( $redirects_format );
		}

		return $redirect_manager;
	}

	/**
	 * Fetches the bulk action for removing redirects.
	 */
	private function fetch_bulk_action() {
		if ( mcms_verify_nonce( filter_input( INPUT_POST, 'mcmsseo_redirects_ajax_nonce' ), 'mcmsseo-redirects-ajax-security' ) ) {
			if ( filter_input( INPUT_POST, 'action' ) === 'delete' || filter_input( INPUT_POST, 'action2' ) === 'delete' ) {
				$bulk_delete = filter_input( INPUT_POST, 'mcmsseo_redirects_bulk_delete', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
				$redirects   = array();
				foreach ( $bulk_delete as $origin ) {
					if ( $redirect = $this->get_redirect_manager()->get_redirect( $origin ) ) {
						$redirects[] = $redirect;
					}
				}

				$this->get_redirect_manager()->delete_redirects( $redirects );
			}
		}
	}
}
