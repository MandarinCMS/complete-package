<?php
/**
 * Module installation and activation for MandarinCMS myskins.
 *
 * Please note that this is a drop-in library for a myskin or module.
 * The authors of this library (Jii Saaduddin) is NOT responsible
 * for the support of your module or myskin. Please contact the module
 * or myskin author for support.
 *
 * @package   TGM-Module-Activation
 * @version   2.6.1 for parent myskin JMD Worldcasts for publication on MandarinCMS.org
 * @link      http://github.com/JiiSaaduddin/
 * @author    Jii Saaduddin
 * @copyright Copyright (c) 2011, Jii Saaduddin
 * @license   GPL-2.0+
 */

/*
	Copyright 2011 Jii Saaduddin (thomasgriffinmedia.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( ! class_exists( 'TGM_Module_Activation' ) ) {

	/**
	 * Automatic module installation and activation library.
	 *
	 * Creates a way to automatically install and activate modules from within myskins.
	 * The modules can be either bundled, downloaded from the MandarinCMS
	 * Module Repository or downloaded from another external source.
	 *
	 * @since 1.0.0
	 *
	 * @package TGM-Module-Activation
	 * @author  Jii Saaduddin
	 * @author  Gary Jones
	 */
	class TGM_Module_Activation {
		/**
		 * TGMPA version number.
		 *
		 * @since 2.5.0
		 *
		 * @const string Version number.
		 */
		const TGMPA_VERSION = '2.6.1';

		/**
		 * Regular expression to test if a URL is a MCMS module repo URL.
		 *
		 * @const string Regex.
		 *
		 * @since 2.5.0
		 */
		const MCMS_REPO_REGEX = '|^http[s]?://mandarincms\.org/(?:extend/)?modules/|';

		/**
		 * Arbitrary regular expression to test if a string starts with a URL.
		 *
		 * @const string Regex.
		 *
		 * @since 2.5.0
		 */
		const IS_URL_REGEX = '|^http[s]?://|';

		/**
		 * Holds a copy of itself, so it can be referenced by the class name.
		 *
		 * @since 1.0.0
		 *
		 * @var TGM_Module_Activation
		 */
		public static $instance;

		/**
		 * Holds arrays of module details.
		 *
		 * @since 1.0.0
		 * @since 2.5.0 the array has the module slug as an associative key.
		 *
		 * @var array
		 */
		public $modules = array();

		/**
		 * Holds arrays of module names to use to sort the modules array.
		 *
		 * @since 2.5.0
		 *
		 * @var array
		 */
		protected $sort_order = array();

		/**
		 * Whether any modules have the 'force_activation' setting set to true.
		 *
		 * @since 2.5.0
		 *
		 * @var bool
		 */
		protected $has_forced_activation = false;

		/**
		 * Whether any modules have the 'force_deactivation' setting set to true.
		 *
		 * @since 2.5.0
		 *
		 * @var bool
		 */
		protected $has_forced_deactivation = false;

		/**
		 * Name of the unique ID to hash notices.
		 *
		 * @since 2.4.0
		 *
		 * @var string
		 */
		public $id = 'tgmpa';

		/**
		 * Name of the query-string argument for the admin page.
		 *
		 * @since 1.0.0
		 *
		 * @var string
		 */
		protected $menu = 'tgmpa-install-modules';

		/**
		 * Parent menu file slug.
		 *
		 * @since 2.5.0
		 *
		 * @var string
		 */
		public $parent_slug = 'myskins.php';

		/**
		 * Capability needed to view the module installation menu item.
		 *
		 * @since 2.5.0
		 *
		 * @var string
		 */
		public $capability = 'edit_myskin_options';

		/**
		 * Default absolute path to folder containing bundled module zip files.
		 *
		 * @since 2.0.0
		 *
		 * @var string Absolute path prefix to zip file location for bundled modules. Default is empty string.
		 */
		public $default_path = '';

		/**
		 * Flag to show admin notices or not.
		 *
		 * @since 2.1.0
		 *
		 * @var boolean
		 */
		public $has_notices = true;

		/**
		 * Flag to determine if the user can dismiss the notice nag.
		 *
		 * @since 2.4.0
		 *
		 * @var boolean
		 */
		public $dismissable = true;

		/**
		 * Message to be output above nag notice if dismissable is false.
		 *
		 * @since 2.4.0
		 *
		 * @var string
		 */
		public $dismiss_msg = '';

		/**
		 * Flag to set automatic activation of modules. Off by default.
		 *
		 * @since 2.2.0
		 *
		 * @var boolean
		 */
		public $is_automatic = false;

		/**
		 * Optional message to display before the modules table.
		 *
		 * @since 2.2.0
		 *
		 * @var string Message filtered by mcms_kses_post(). Default is empty string.
		 */
		public $message = '';

		/**
		 * Holds configurable array of strings.
		 *
		 * Default values are added in the constructor.
		 *
		 * @since 2.0.0
		 *
		 * @var array
		 */
		public $strings = array();

		/**
		 * Holds the version of MandarinCMS.
		 *
		 * @since 2.4.0
		 *
		 * @var int
		 */
		public $mcms_version;

		/**
		 * Holds the hook name for the admin page.
		 *
		 * @since 2.5.0
		 *
		 * @var string
		 */
		public $page_hook;

		/**
		 * Adds a reference of this object to $instance, populates default strings,
		 * does the tgmpa_init action hook, and hooks in the interactions to init.
		 *
		 * {@internal This method should be `protected`, but as too many TGMPA implementations
		 * haven't upgraded beyond v2.3.6 yet, this gives backward compatibility issues.
		 * Reverted back to public for the time being.}}
		 *
		 * @since 1.0.0
		 *
		 * @see TGM_Module_Activation::init()
		 */
		public function __construct() {
			// Set the current MandarinCMS version.
			$this->mcms_version = $GLOBALS['mcms_version'];

			// Announce that the class is ready, and pass the object (for advanced use).
			do_action_ref_array( 'tgmpa_init', array( $this ) );



			// When the rest of MCMS has loaded, kick-start the rest of the class.
			add_action( 'init', array( $this, 'init' ) );
		}

		/**
		 * Magic method to (not) set protected properties from outside of this class.
		 *
		 * {@internal hackedihack... There is a serious bug in v2.3.2 - 2.3.6  where the `menu` property
		 * is being assigned rather than tested in a conditional, effectively rendering it useless.
		 * This 'hack' prevents this from happening.}}
		 *
		 * @see https://github.com/TGMPA/TGM-Module-Activation/blob/2.3.6/tgm-module-activation/class-tgm-module-activation.php#L1593
		 *
		 * @since 2.5.2
		 *
		 * @param string $name  Name of an inaccessible property.
		 * @param mixed  $value Value to assign to the property.
		 * @return void  Silently fail to set the property when this is tried from outside of this class context.
		 *               (Inside this class context, the __set() method if not used as there is direct access.)
		 */
		public function __set( $name, $value ) {
			return;
		}

		/**
		 * Magic method to get the value of a protected property outside of this class context.
		 *
		 * @since 2.5.2
		 *
		 * @param string $name Name of an inaccessible property.
		 * @return mixed The property value.
		 */
		public function __get( $name ) {
			return $this->{$name};
		}

		/**
		 * Initialise the interactions between this class and MandarinCMS.
		 *
		 * Hooks in three new methods for the class: admin_menu, notices and styles.
		 *
		 * @since 2.0.0
		 *
		 * @see TGM_Module_Activation::admin_menu()
		 * @see TGM_Module_Activation::notices()
		 * @see TGM_Module_Activation::styles()
		 */
		public function init() {
			/**
			 * By default TGMPA only loads on the MCMS back-end and not in an Ajax call. Using this filter
			 * you can overrule that behaviour.
			 *
			 * @since 2.5.0
			 *
			 * @param bool $load Whether or not TGMPA should load.
			 *                   Defaults to the return of `is_admin() && ! defined( 'DOING_AJAX' )`.
			 */
			if ( true !== apply_filters( 'tgmpa_load', ( is_admin() && ! defined( 'DOING_AJAX' ) ) ) ) {
				return;
			}

			// Load class strings.
			$this->strings = array(
				'page_title'                      => __( 'Install Required Modules', 'jmd-worldcast' ),
				'menu_title'                      => __( 'Install Modules', 'jmd-worldcast' ),
				/* translators: %s: module name. */
				'installing'                      => __( 'Installing Module: %s', 'jmd-worldcast' ),
				/* translators: %s: module name. */
				'updating'                        => __( 'Updating Module: %s', 'jmd-worldcast' ),
				'oops'                            => __( 'Something went wrong with the module API.', 'jmd-worldcast' ),
				/* translators: 1: module name(s). */
				'notice_can_install_required'     => _n_noop(
					'This myskin requires the following module: %1$s.',
					'This myskin requires the following modules: %1$s.',
					'jmd-worldcast'
				),
				/* translators: 1: module name(s). */
				'notice_can_install_recommended'  => _n_noop(
					'This myskin recommends the following module: %1$s.',
					'This myskin recommends the following modules: %1$s.',
					'jmd-worldcast'
				),
				/* translators: 1: module name(s). */
				'notice_ask_to_update'            => _n_noop(
					'The following module needs to be updated to its latest version to ensure maximum compatibility with this myskin: %1$s.',
					'The following modules need to be updated to their latest version to ensure maximum compatibility with this myskin: %1$s.',
					'jmd-worldcast'
				),
				/* translators: 1: module name(s). */
				'notice_ask_to_update_maybe'      => _n_noop(
					'There is an update available for: %1$s.',
					'There are updates available for the following modules: %1$s.',
					'jmd-worldcast'
				),
				/* translators: 1: module name(s). */
				'notice_can_activate_required'    => _n_noop(
					'The following required module is currently inactive: %1$s.',
					'The following required modules are currently inactive: %1$s.',
					'jmd-worldcast'
				),
				/* translators: 1: module name(s). */
				'notice_can_activate_recommended' => _n_noop(
					'The following recommended module is currently inactive: %1$s.',
					'The following recommended modules are currently inactive: %1$s.',
					'jmd-worldcast'
				),
				'install_link'                    => _n_noop(
					'Begin installing module',
					'Begin installing modules',
					'jmd-worldcast'
				),
				'update_link'                     => _n_noop(
					'Begin updating module',
					'Begin updating modules',
					'jmd-worldcast'
				),
				'activate_link'                   => _n_noop(
					'Begin activating module',
					'Begin activating modules',
					'jmd-worldcast'
				),
				'return'                          => __( 'Return to Required Modules Installer', 'jmd-worldcast' ),
				'dashboard'                       => __( 'Return to the Dashboard', 'jmd-worldcast' ),
				'module_activated'                => __( 'Module activated successfully.', 'jmd-worldcast' ),
				'activated_successfully'          => __( 'The following module was activated successfully:', 'jmd-worldcast' ),
				/* translators: 1: module name. */
				'module_already_active'           => __( 'No action taken. Module %1$s was already active.', 'jmd-worldcast' ),
				/* translators: 1: module name. */
				'module_needs_higher_version'     => __( 'Module not activated. A higher version of %s is needed for this myskin. Please update the module.', 'jmd-worldcast' ),
				/* translators: 1: dashboard link. */
				'complete'                        => __( 'All modules installed and activated successfully. %1$s', 'jmd-worldcast' ),
				'dismiss'                         => __( 'Dismiss this notice', 'jmd-worldcast' ),
				'notice_cannot_install_activate'  => __( 'There are one or more required or recommended modules to install, update or activate.', 'jmd-worldcast' ),
				'contact_admin'                   => __( 'Please contact the administrator of this site for help.', 'jmd-worldcast' ),
			);

			do_action( 'tgmpa_register' );

			/* After this point, the modules should be registered and the configuration set. */

			// Proceed only if we have modules to handle.
			if ( empty( $this->modules ) || ! is_array( $this->modules ) ) {
				return;
			}

			// Set up the menu and notices if we still have outstanding actions.
			if ( true !== $this->is_tgmpa_complete() ) {
				// Sort the modules.
				array_multisort( $this->sort_order, SORT_ASC, $this->modules );

				add_action( 'admin_menu', array( $this, 'admin_menu' ) );
				add_action( 'admin_head', array( $this, 'dismiss' ) );

				// Prevent the normal links from showing underneath a single install/update page.
				add_filter( 'install_module_complete_actions', array( $this, 'actions' ) );
				add_filter( 'update_module_complete_actions', array( $this, 'actions' ) );

				if ( $this->has_notices ) {
					add_action( 'admin_notices', array( $this, 'notices' ) );
					add_action( 'admin_init', array( $this, 'admin_init' ), 1 );
					add_action( 'admin_enqueue_scripts', array( $this, 'thickbox' ) );
				}
			}

			// If needed, filter module action links.
			add_action( 'load-modules.php', array( $this, 'add_module_action_link_filters' ), 1 );

			// Make sure things get reset on switch myskin.
			add_action( 'switch_myskin', array( $this, 'flush_modules_cache' ) );

			if ( $this->has_notices ) {
				add_action( 'switch_myskin', array( $this, 'update_dismiss' ) );
			}

			// Setup the force activation hook.
			if ( true === $this->has_forced_activation ) {
				add_action( 'admin_init', array( $this, 'force_activation' ) );
			}

			// Setup the force deactivation hook.
			if ( true === $this->has_forced_deactivation ) {
				add_action( 'switch_myskin', array( $this, 'force_deactivation' ) );
			}
		}







		/**
		 * Hook in module action link filters for the MCMS native modules page.
		 *
		 * - Prevent activation of modules which don't meet the minimum version requirements.
		 * - Prevent deactivation of force-activated modules.
		 * - Add update notice if update available.
		 *
		 * @since 2.5.0
		 */
		public function add_module_action_link_filters() {
			foreach ( $this->modules as $slug => $module ) {
				if ( false === $this->can_module_activate( $slug ) ) {
					add_filter( 'module_action_links_' . $module['file_path'], array( $this, 'filter_module_action_links_activate' ), 20 );
				}

				if ( true === $module['force_activation'] ) {
					add_filter( 'module_action_links_' . $module['file_path'], array( $this, 'filter_module_action_links_deactivate' ), 20 );
				}

				if ( false !== $this->does_module_require_update( $slug ) ) {
					add_filter( 'module_action_links_' . $module['file_path'], array( $this, 'filter_module_action_links_update' ), 20 );
				}
			}
		}

		/**
		 * Remove the 'Activate' link on the MCMS native modules page if the module does not meet the
		 * minimum version requirements.
		 *
		 * @since 2.5.0
		 *
		 * @param array $actions Action links.
		 * @return array
		 */
		public function filter_module_action_links_activate( $actions ) {
			unset( $actions['activate'] );

			return $actions;
		}

		/**
		 * Remove the 'Deactivate' link on the MCMS native modules page if the module has been set to force activate.
		 *
		 * @since 2.5.0
		 *
		 * @param array $actions Action links.
		 * @return array
		 */
		public function filter_module_action_links_deactivate( $actions ) {
			unset( $actions['deactivate'] );

			return $actions;
		}

		/**
		 * Add a 'Requires update' link on the MCMS native modules page if the module does not meet the
		 * minimum version requirements.
		 *
		 * @since 2.5.0
		 *
		 * @param array $actions Action links.
		 * @return array
		 */
		public function filter_module_action_links_update( $actions ) {
			$actions['update'] = sprintf(
				'<a href="%1$s" title="%2$s" class="edit">%3$s</a>',
				esc_url( $this->get_tgmpa_status_url( 'update' ) ),
				esc_attr__( 'This module needs to be updated to be compatible with your myskin.', 'jmd-worldcast' ),
				esc_html__( 'Update Required', 'jmd-worldcast' )
			);

			return $actions;
		}

		/**
		 * Handles calls to show module information via links in the notices.
		 *
		 * We get the links in the admin notices to point to the TGMPA page, rather
		 * than the typical module-install.php file, so we can prepare everything
		 * beforehand.
		 *
		 * MCMS does not make it easy to show the module information in the thickbox -
		 * here we have to require a file that includes a function that does the
		 * main work of displaying it, enqueue some styles, set up some globals and
		 * finally call that function before exiting.
		 *
		 * Down right easy once you know how...
		 *
		 * Returns early if not the TGMPA page.
		 *
		 * @since 2.1.0
		 *
		 * @global string $tab Used as iframe div class names, helps with styling
		 * @global string $body_id Used as the iframe body ID, helps with styling
		 *
		 * @return null Returns early if not the TGMPA page.
		 */
		public function admin_init() {
			if ( ! $this->is_tgmpa_page() ) {
				return;
			}

			if ( isset( $_REQUEST['tab'] ) && 'module-information' === $_REQUEST['tab'] ) {
				// Needed for install_module_information().
				require_once BASED_TREE_URI . 'mcms-admin/includes/module-install.php';

				mcms_enqueue_style( 'module-install' );

				global $tab, $body_id;
				$body_id = 'module-information';
				// @codingStandardsIgnoreStart
				$tab     = 'module-information';
				// @codingStandardsIgnoreEnd

				install_module_information();

				exit;
			}
		}

		/**
		 * Enqueue thickbox scripts/styles for module info.
		 *
		 * Thickbox is not automatically included on all admin pages, so we must
		 * manually enqueue it for those pages.
		 *
		 * Thickbox is only loaded if the user has not dismissed the admin
		 * notice or if there are any modules left to install and activate.
		 *
		 * @since 2.1.0
		 */
		public function thickbox() {
			if ( ! get_user_meta( get_current_user_id(), 'tgmpa_dismissed_notice_' . $this->id, true ) ) {
				add_thickbox();
			}
		}

		/**
		 * Adds submenu page if there are module actions to take.
		 *
		 * This method adds the submenu page letting users know that a required
		 * module needs to be installed.
		 *
		 * This page disappears once the module has been installed and activated.
		 *
		 * @since 1.0.0
		 *
		 * @see TGM_Module_Activation::init()
		 * @see TGM_Module_Activation::install_modules_page()
		 *
		 * @return null Return early if user lacks capability to install a module.
		 */
		public function admin_menu() {
			// Make sure privileges are correct to see the page.
			if ( ! current_user_can( 'install_modules' ) ) {
				return;
			}

			$args = apply_filters(
				'tgmpa_admin_menu_args',
				array(
					'parent_slug' => $this->parent_slug,                     // Parent Menu slug.
					'page_title'  => $this->strings['page_title'],           // Page title.
					'menu_title'  => $this->strings['menu_title'],           // Menu title.
					'capability'  => $this->capability,                      // Capability.
					'menu_slug'   => $this->menu,                            // Menu slug.
					'function'    => array( $this, 'install_modules_page' ), // Callback.
				)
			);

			$this->add_admin_menu( $args );
		}

		/**
		 * Add the menu item.
		 *
		 * {@internal IMPORTANT! If this function changes, review the regex in the custom TGMPA
		 * generator on the website.}}
		 *
		 * @since 2.5.0
		 *
		 * @param array $args Menu item configuration.
		 */
		protected function add_admin_menu( array $args ) {
			$this->page_hook = add_myskin_page( $args['page_title'], $args['menu_title'], $args['capability'], $args['menu_slug'], $args['function'] );
		}

		/**
		 * Echoes module installation form.
		 *
		 * This method is the callback for the admin_menu method function.
		 * This displays the admin page and form area where the user can select to install and activate the module.
		 * Aborts early if we're processing a module installation action.
		 *
		 * @since 1.0.0
		 *
		 * @return null Aborts early if we're processing a module installation action.
		 */
		public function install_modules_page() {
			// Store new instance of module table in object.
			$module_table = new TGMPA_List_Table;

			// Return early if processing a module installation action.
			if ( ( ( 'tgmpa-bulk-install' === $module_table->current_action() || 'tgmpa-bulk-update' === $module_table->current_action() ) && $module_table->process_bulk_actions() ) || $this->do_module_install() ) {
				return;
			}

			// Force refresh of available module information so we'll know about manual updates/deletes.
			mcms_clean_modules_cache( false );

			?>
			<div class="tgmpa wrap">
				<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
				<?php $module_table->prepare_items(); ?>

				<?php
				if ( ! empty( $this->message ) && is_string( $this->message ) ) {
					echo mcms_kses_post( $this->message );
				}
				?>
				<?php $module_table->views(); ?>

				<form id="tgmpa-modules" action="" method="post">
					<input type="hidden" name="tgmpa-page" value="<?php echo esc_attr( $this->menu ); ?>" />
					<input type="hidden" name="module_status" value="<?php echo esc_attr( $module_table->view_context ); ?>" />
					<?php $module_table->display(); ?>
				</form>
			</div>
			<?php
		}

		/**
		 * Installs, updates or activates a module depending on the action link clicked by the user.
		 *
		 * Checks the $_GET variable to see which actions have been
		 * passed and responds with the appropriate method.
		 *
		 * Uses MCMS_Filesystem to process and handle the module installation
		 * method.
		 *
		 * @since 1.0.0
		 *
		 * @uses MCMS_Filesystem
		 * @uses MCMS_Error
		 * @uses MCMS_Upgrader
		 * @uses Module_Upgrader
		 * @uses Module_Installer_Skin
		 * @uses Module_Upgrader_Skin
		 *
		 * @return boolean True on success, false on failure.
		 */
		protected function do_module_install() {
			if ( empty( $_GET['module'] ) ) {
				return false;
			}

			// All module information will be stored in an array for processing.
			$slug = $this->sanitize_key(urldecode( sanitize_key($_GET['module'] ) ));

			if ( ! isset( $this->modules[ $slug ] ) ) {
				return false;
			}

			// Was an install or upgrade action link clicked?
			if ( ( isset( $_GET['tgmpa-install'] ) && 'install-module' === $_GET['tgmpa-install'] ) || ( isset( $_GET['tgmpa-update'] ) && 'update-module' === $_GET['tgmpa-update'] ) ) {

				$install_type = 'install';
				if ( isset( $_GET['tgmpa-update'] ) && 'update-module' === $_GET['tgmpa-update'] ) {
					$install_type = 'update';
				}

				check_admin_referer( 'tgmpa-' . $install_type, 'tgmpa-nonce' );

				// Pass necessary information via URL if MCMS_Filesystem is needed.
				$url = mcms_nonce_url(
					add_query_arg(
						array(
							'module'                 => urlencode( $slug ),
							'tgmpa-' . $install_type => $install_type . '-module',
						),
						$this->get_tgmpa_url()
					),
					'tgmpa-' . $install_type,
					'tgmpa-nonce'
				);

				$method = ''; // Leave blank so MCMS_Filesystem can populate it as necessary.

				if ( false === ( $creds = request_filesystem_credentials( esc_url_raw( $url ), $method, false, false, array() ) ) ) {
					return true;
				}

				if ( ! MCMS_Filesystem( $creds ) ) {
					request_filesystem_credentials( esc_url_raw( $url ), $method, true, false, array() ); // Setup MCMS_Filesystem.
					return true;
				}

				/* If we arrive here, we have the filesystem. */

				// Prep variables for Module_Installer_Skin class.
				$extra         = array();
				$extra['slug'] = $slug; // Needed for potentially renaming of directory name.
				$source        = $this->get_download_url( $slug );
				$api           = ( 'repo' === $this->modules[ $slug ]['source_type'] ) ? $this->get_modules_api( $slug ) : null;
				$api           = ( false !== $api ) ? $api : null;

				$url = add_query_arg(
					array(
						'action' => $install_type . '-module',
						'module' => urlencode( $slug ),
					),
					'update.php'
				);

				if ( ! class_exists( 'Module_Upgrader', false ) ) {
					require_once BASED_TREE_URI . 'mcms-admin/includes/class-mcms-upgrader.php';
				}

				$title     = ( 'update' === $install_type ) ? $this->strings['updating'] : $this->strings['installing'];
				$skin_args = array(
					'type'   => ( 'bundled' !== $this->modules[ $slug ]['source_type'] ) ? 'web' : 'upload',
					'title'  => sprintf( $title, $this->modules[ $slug ]['name'] ),
					'url'    => esc_url_raw( $url ),
					'nonce'  => $install_type . '-module_' . $slug,
					'module' => '',
					'api'    => $api,
					'extra'  => $extra,
				);
				unset( $title );

				if ( 'update' === $install_type ) {
					$skin_args['module'] = $this->modules[ $slug ]['file_path'];
					$skin                = new Module_Upgrader_Skin( $skin_args );
				} else {
					$skin = new Module_Installer_Skin( $skin_args );
				}

				// Create a new instance of Module_Upgrader.
				$upgrader = new Module_Upgrader( $skin );

				// Perform the action and install the module from the $source urldecode().
				add_filter( 'upgrader_source_selection', array( $this, 'maybe_adjust_source_dir' ), 1, 3 );

				if ( 'update' === $install_type ) {
					// Inject our info into the update transient.
					$to_inject                    = array( $slug => $this->modules[ $slug ] );
					$to_inject[ $slug ]['source'] = $source;
					$this->inject_update_info( $to_inject );

					$upgrader->upgrade( $this->modules[ $slug ]['file_path'] );
				} else {
					$upgrader->install( $source );
				}

				remove_filter( 'upgrader_source_selection', array( $this, 'maybe_adjust_source_dir' ), 1 );

				// Make sure we have the correct file path now the module is installed/updated.
				$this->populate_file_path( $slug );

				// Only activate modules if the config option is set to true and the module isn't
				// already active (upgrade).
				if ( $this->is_automatic && ! $this->is_module_active( $slug ) ) {
					$module_activate = $upgrader->module_info(); // Grab the module info from the Module_Upgrader method.
					if ( false === $this->activate_single_module( $module_activate, $slug, true ) ) {
						return true; // Finish execution of the function early as we encountered an error.
					}
				}

				$this->show_tgmpa_version();

				// Display message based on if all modules are now active or not.
				if ( $this->is_tgmpa_complete() ) {
					echo '<p>', sprintf( esc_html( $this->strings['complete'] ), '<a href="' . esc_url( self_admin_url() ) . '">' . esc_html__( 'Return to the Dashboard', 'jmd-worldcast' ) . '</a>' ), '</p>';
					echo '<style type="text/css">#adminmenu .mcms-submenu li.current { display: none !important; }</style>';
				} else {
					echo '<p><a href="', esc_url( $this->get_tgmpa_url() ), '" target="_parent">', esc_html( $this->strings['return'] ), '</a></p>';
				}

				return true;
			} elseif ( isset( $this->modules[ $slug ]['file_path'], $_GET['tgmpa-activate'] ) && 'activate-module' === $_GET['tgmpa-activate'] ) {
				// Activate action link was clicked.
				check_admin_referer( 'tgmpa-activate', 'tgmpa-nonce' );

				if ( false === $this->activate_single_module( $this->modules[ $slug ]['file_path'], $slug ) ) {
					return true; // Finish execution of the function early as we encountered an error.
				}
			}

			return false;
		}

		/**
		 * Inject information into the 'update_modules' site transient as MCMS checks that before running an update.
		 *
		 * @since 2.5.0
		 *
		 * @param array $modules The module information for the modules which are to be updated.
		 */
		public function inject_update_info( $modules ) {
			$repo_updates = get_site_transient( 'update_modules' );

			if ( ! is_object( $repo_updates ) ) {
				$repo_updates = new stdClass;
			}

			foreach ( $modules as $slug => $module ) {
				$file_path = $module['file_path'];

				if ( empty( $repo_updates->response[ $file_path ] ) ) {
					$repo_updates->response[ $file_path ] = new stdClass;
				}

				// We only really need to set package, but let's do all we can in case MCMS changes something.
				$repo_updates->response[ $file_path ]->slug        = $slug;
				$repo_updates->response[ $file_path ]->module      = $file_path;
				$repo_updates->response[ $file_path ]->new_version = $module['version'];
				$repo_updates->response[ $file_path ]->package     = $module['source'];
				if ( empty( $repo_updates->response[ $file_path ]->url ) && ! empty( $module['external_url'] ) ) {
					$repo_updates->response[ $file_path ]->url = $module['external_url'];
				}
			}

			set_site_transient( 'update_modules', $repo_updates );
		}

		/**
		 * Adjust the module directory name if necessary.
		 *
		 * The final destination directory of a module is based on the subdirectory name found in the
		 * (un)zipped source. In some cases - most notably GitHub repository module downloads -, this
		 * subdirectory name is not the same as the expected slug and the module will not be recognized
		 * as installed. This is fixed by adjusting the temporary unzipped source subdirectory name to
		 * the expected module slug.
		 *
		 * @since 2.5.0
		 *
		 * @param string       $source        Path to upgrade/zip-file-name.tmp/subdirectory/.
		 * @param string       $remote_source Path to upgrade/zip-file-name.tmp.
		 * @param \MCMS_Upgrader $upgrader      Instance of the upgrader which installs the module.
		 * @return string $source
		 */
		public function maybe_adjust_source_dir( $source, $remote_source, $upgrader ) {
			if ( ! $this->is_tgmpa_page() || ! is_object( $GLOBALS['mcms_filesystem'] ) ) {
				return $source;
			}

			// Check for single file modules.
			$source_files = array_keys( $GLOBALS['mcms_filesystem']->dirlist( $remote_source ) );
			if ( 1 === count( $source_files ) && false === $GLOBALS['mcms_filesystem']->is_dir( $source ) ) {
				return $source;
			}

			// Multi-file module, let's see if the directory is correctly named.
			$desired_slug = '';

			// Figure out what the slug is supposed to be.
			if ( false === $upgrader->bulk && ! empty( $upgrader->skin->options['extra']['slug'] ) ) {
				$desired_slug = $upgrader->skin->options['extra']['slug'];
			} else {
				// Bulk installer contains less info, so fall back on the info registered here.
				foreach ( $this->modules as $slug => $module ) {
					if ( ! empty( $upgrader->skin->module_names[ $upgrader->skin->i ] ) && $module['name'] === $upgrader->skin->module_names[ $upgrader->skin->i ] ) {
						$desired_slug = $slug;
						break;
					}
				}
				unset( $slug, $module );
			}

			if ( ! empty( $desired_slug ) ) {
				$subdir_name = untrailingslashit( str_replace( trailingslashit( $remote_source ), '', $source ) );

				if ( ! empty( $subdir_name ) && $subdir_name !== $desired_slug ) {
					$from_path = untrailingslashit( $source );
					$to_path   = trailingslashit( $remote_source ) . $desired_slug;

					if ( true === $GLOBALS['mcms_filesystem']->move( $from_path, $to_path ) ) {
						return trailingslashit( $to_path );
					} else {
						return new MCMS_Error( 'rename_failed', esc_html__( 'The remote module package does not contain a folder with the desired slug and renaming did not work.', 'jmd-worldcast' ) . ' ' . esc_html__( 'Please contact the module provider and ask them to package their module according to the MandarinCMS guidelines.', 'jmd-worldcast' ), array( 'found' => $subdir_name, 'expected' => $desired_slug ) );
					}
				} elseif ( empty( $subdir_name ) ) {
					return new MCMS_Error( 'packaged_wrong', esc_html__( 'The remote module package consists of more than one file, but the files are not packaged in a folder.', 'jmd-worldcast' ) . ' ' . esc_html__( 'Please contact the module provider and ask them to package their module according to the MandarinCMS guidelines.', 'jmd-worldcast' ), array( 'found' => $subdir_name, 'expected' => $desired_slug ) );
				}
			}

			return $source;
		}

		/**
		 * Activate a single module and send feedback about the result to the screen.
		 *
		 * @since 2.5.0
		 *
		 * @param string $file_path Path within mcms-modules/ to main module file.
		 * @param string $slug      Module slug.
		 * @param bool   $automatic Whether this is an automatic activation after an install. Defaults to false.
		 *                          This determines the styling of the output messages.
		 * @return bool False if an error was encountered, true otherwise.
		 */
		protected function activate_single_module( $file_path, $slug, $automatic = false ) {
			if ( $this->can_module_activate( $slug ) ) {
				$activate = activate_module( $file_path );

				if ( is_mcms_error( $activate ) ) {
					echo '<div id="message" class="error"><p>', mcms_kses_post( $activate->get_error_message() ), '</p></div>',
						'<p><a href="', esc_url( $this->get_tgmpa_url() ), '" target="_parent">', esc_html( $this->strings['return'] ), '</a></p>';

					return false; // End it here if there is an error with activation.
				} else {
					if ( ! $automatic ) {
						// Make sure message doesn't display again if bulk activation is performed
						// immediately after a single activation.
						if ( ! isset( $_POST['action'] ) ) { // MCMSCS: CSRF OK.
							echo '<div id="message" class="updated"><p>', esc_html( $this->strings['activated_successfully'] ), ' <strong>', esc_html( $this->modules[ $slug ]['name'] ), '.</strong></p></div>';
						}
					} else {
						// Simpler message layout for use on the module install page.
						echo '<p>', esc_html( $this->strings['module_activated'] ), '</p>';
					}
				}
			} elseif ( $this->is_module_active( $slug ) ) {
				// No simpler message format provided as this message should never be encountered
				// on the module install page.
				echo '<div id="message" class="error"><p>',
					sprintf(
						esc_html( $this->strings['module_already_active'] ),
						'<strong>' . esc_html( $this->modules[ $slug ]['name'] ) . '</strong>'
					),
					'</p></div>';
			} elseif ( $this->does_module_require_update( $slug ) ) {
				if ( ! $automatic ) {
					// Make sure message doesn't display again if bulk activation is performed
					// immediately after a single activation.
					if ( ! isset( $_POST['action'] ) ) { // MCMSCS: CSRF OK.
						echo '<div id="message" class="error"><p>',
							sprintf(
								esc_html( $this->strings['module_needs_higher_version'] ),
								'<strong>' . esc_html( $this->modules[ $slug ]['name'] ) . '</strong>'
							),
							'</p></div>';
					}
				} else {
					// Simpler message layout for use on the module install page.
					echo '<p>', sprintf( esc_html( $this->strings['module_needs_higher_version'] ), esc_html( $this->modules[ $slug ]['name'] ) ), '</p>';
				}
			}

			return true;
		}

		/**
		 * Echoes required module notice.
		 *
		 * Outputs a message telling users that a specific module is required for
		 * their myskin. If appropriate, it includes a link to the form page where
		 * users can install and activate the module.
		 *
		 * Returns early if we're on the Install page.
		 *
		 * @since 1.0.0
		 *
		 * @global object $current_screen
		 *
		 * @return null Returns early if we're on the Install page.
		 */
		public function notices() {
			// Remove nag on the install page / Return early if the nag message has been dismissed or user < author.
			if ( ( $this->is_tgmpa_page() || $this->is_core_update_page() ) || get_user_meta( get_current_user_id(), 'tgmpa_dismissed_notice_' . $this->id, true ) || ! current_user_can( apply_filters( 'tgmpa_show_admin_notice_capability', 'publish_posts' ) ) ) {
				return;
			}

			// Store for the module slugs by message type.
			$message = array();

			// Initialize counters used to determine plurality of action link texts.
			$install_link_count          = 0;
			$update_link_count           = 0;
			$activate_link_count         = 0;
			$total_required_action_count = 0;

			foreach ( $this->modules as $slug => $module ) {
				if ( $this->is_module_active( $slug ) && false === $this->does_module_have_update( $slug ) ) {
					continue;
				}

				if ( ! $this->is_module_installed( $slug ) ) {
					if ( current_user_can( 'install_modules' ) ) {
						$install_link_count++;

						if ( true === $module['required'] ) {
							$message['notice_can_install_required'][] = $slug;
						} else {
							$message['notice_can_install_recommended'][] = $slug;
						}
					}
					if ( true === $module['required'] ) {
						$total_required_action_count++;
					}
				} else {
					if ( ! $this->is_module_active( $slug ) && $this->can_module_activate( $slug ) ) {
						if ( current_user_can( 'activate_modules' ) ) {
							$activate_link_count++;

							if ( true === $module['required'] ) {
								$message['notice_can_activate_required'][] = $slug;
							} else {
								$message['notice_can_activate_recommended'][] = $slug;
							}
						}
						if ( true === $module['required'] ) {
							$total_required_action_count++;
						}
					}

					if ( $this->does_module_require_update( $slug ) || false !== $this->does_module_have_update( $slug ) ) {

						if ( current_user_can( 'update_modules' ) ) {
							$update_link_count++;

							if ( $this->does_module_require_update( $slug ) ) {
								$message['notice_ask_to_update'][] = $slug;
							} elseif ( false !== $this->does_module_have_update( $slug ) ) {
								$message['notice_ask_to_update_maybe'][] = $slug;
							}
						}
						if ( true === $module['required'] ) {
							$total_required_action_count++;
						}
					}
				}
			}
			unset( $slug, $module );

			// If we have notices to display, we move forward.
			if ( ! empty( $message ) || $total_required_action_count > 0 ) {
				krsort( $message ); // Sort messages.
				$rendered = '';

				// As add_settings_error() wraps the final message in a <p> and as the final message can't be
				// filtered, using <p>'s in our html would render invalid html output.
				$line_template = '<span style="display: block; margin: 0.5em 0.5em 0 0; clear: both;">%s</span>' . "\n";

				if ( ! current_user_can( 'activate_modules' ) && ! current_user_can( 'install_modules' ) && ! current_user_can( 'update_modules' ) ) {
					$rendered  = esc_html( $this->strings['notice_cannot_install_activate'] ) . ' ' . esc_html( $this->strings['contact_admin'] );
					$rendered .= $this->create_user_action_links_for_notice( 0, 0, 0, $line_template );
				} else {

					// If dismissable is false and a message is set, output it now.
					if ( ! $this->dismissable && ! empty( $this->dismiss_msg ) ) {
						$rendered .= sprintf( $line_template, mcms_kses_post( $this->dismiss_msg ) );
					}

					// Render the individual message lines for the notice.
					foreach ( $message as $type => $module_group ) {
						$linked_modules = array();

						// Get the external info link for a module if one is available.
						foreach ( $module_group as $module_slug ) {
							$linked_modules[] = $this->get_info_link( $module_slug );
						}
						unset( $module_slug );

						$count          = count( $module_group );
						$linked_modules = array_map( array( 'TGMPA_Utils', 'wrap_in_em' ), $linked_modules );
						$last_module    = array_pop( $linked_modules ); // Pop off last name to prep for readability.
						$imploded       = empty( $linked_modules ) ? $last_module : ( implode( ', ', $linked_modules ) . ' ' . esc_html_x( 'and', 'module A *and* module B', 'jmd-worldcast' ) . ' ' . $last_module );

						$rendered .= sprintf(
							$line_template,
							sprintf(
								translate_nooped_plural( $this->strings[ $type ], $count, 'jmd-worldcast' ),
								$imploded,
								$count
							)
						);

					}
					unset( $type, $module_group, $linked_modules, $count, $last_module, $imploded );

					$rendered .= $this->create_user_action_links_for_notice( $install_link_count, $update_link_count, $activate_link_count, $line_template );
				}

				// Register the nag messages and prepare them to be processed.
				add_settings_error( 'tgmpa', 'tgmpa', $rendered, $this->get_admin_notice_class() );
			}

			// Admin options pages already output settings_errors, so this is to avoid duplication.
			if ( 'options-general' !== $GLOBALS['current_screen']->parent_base ) {
				$this->display_settings_errors();
			}
		}

		/**
		 * Generate the user action links for the admin notice.
		 *
		 * @since 2.6.0
		 *
		 * @param int $install_count  Number of modules to install.
		 * @param int $update_count   Number of modules to update.
		 * @param int $activate_count Number of modules to activate.
		 * @param int $line_template  Template for the HTML tag to output a line.
		 * @return string Action links.
		 */
		protected function create_user_action_links_for_notice( $install_count, $update_count, $activate_count, $line_template ) {
			// Setup action links.
			$action_links = array(
				'install'  => '',
				'update'   => '',
				'activate' => '',
				'dismiss'  => $this->dismissable ? '<a href="' . esc_url( mcms_nonce_url( add_query_arg( 'tgmpa-dismiss', 'dismiss_admin_notices' ), 'tgmpa-dismiss-' . get_current_user_id() ) ) . '" class="dismiss-notice" target="_parent">' . esc_html( $this->strings['dismiss'] ) . '</a>' : '',
			);

			$link_template = '<a href="%2$s">%1$s</a>';

			if ( current_user_can( 'install_modules' ) ) {
				if ( $install_count > 0 ) {
					$action_links['install'] = sprintf(
						$link_template,
						translate_nooped_plural( $this->strings['install_link'], $install_count, 'jmd-worldcast' ),
						esc_url( $this->get_tgmpa_status_url( 'install' ) )
					);
				}
				if ( $update_count > 0 ) {
					$action_links['update'] = sprintf(
						$link_template,
						translate_nooped_plural( $this->strings['update_link'], $update_count, 'jmd-worldcast' ),
						esc_url( $this->get_tgmpa_status_url( 'update' ) )
					);
				}
			}

			if ( current_user_can( 'activate_modules' ) && $activate_count > 0 ) {
				$action_links['activate'] = sprintf(
					$link_template,
					translate_nooped_plural( $this->strings['activate_link'], $activate_count, 'jmd-worldcast' ),
					esc_url( $this->get_tgmpa_status_url( 'activate' ) )
				);
			}

			$action_links = apply_filters( 'tgmpa_notice_action_links', $action_links );

			$action_links = array_filter( (array) $action_links ); // Remove any empty array items.

			if ( ! empty( $action_links ) ) {
				$action_links = sprintf( $line_template, implode( ' | ', $action_links ) );
				return apply_filters( 'tgmpa_notice_rendered_action_links', $action_links );
			} else {
				return '';
			}
		}

		/**
		 * Get admin notice class.
		 *
		 * Work around all the changes to the various admin notice classes between MCMS 4.4 and 3.7
		 * (lowest supported version by TGMPA).
		 *
		 * @since 2.6.0
		 *
		 * @return string
		 */
		protected function get_admin_notice_class() {
			if ( ! empty( $this->strings['nag_type'] ) ) {
				return sanitize_html_class( strtolower( $this->strings['nag_type'] ) );
			} else {
				if ( version_compare( $this->mcms_version, '4.2', '>=' ) ) {
					return 'notice-warning';
				} elseif ( version_compare( $this->mcms_version, '4.1', '>=' ) ) {
					return 'notice';
				} else {
					return 'updated';
				}
			}
		}

		/**
		 * Display settings errors and remove those which have been displayed to avoid duplicate messages showing
		 *
		 * @since 2.5.0
		 */
		protected function display_settings_errors() {
			global $mcms_settings_errors;

			settings_errors( 'tgmpa' );

			foreach ( (array) $mcms_settings_errors as $key => $details ) {
				if ( 'tgmpa' === $details['setting'] ) {
					unset( $mcms_settings_errors[ $key ] );
					break;
				}
			}
		}

		/**
		 * Register dismissal of admin notices.
		 *
		 * Acts on the dismiss link in the admin nag messages.
		 * If clicked, the admin notice disappears and will no longer be visible to this user.
		 *
		 * @since 2.1.0
		 */
		public function dismiss() {
			if ( isset( $_GET['tgmpa-dismiss'] ) && check_admin_referer( 'tgmpa-dismiss-' . get_current_user_id() ) ) {
				update_user_meta( get_current_user_id(), 'tgmpa_dismissed_notice_' . $this->id, 1 );
			}
		}

		/**
		 * Add individual module to our collection of modules.
		 *
		 * If the required keys are not set or the module has already
		 * been registered, the module is not added.
		 *
		 * @since 2.0.0
		 *
		 * @param array|null $module Array of module arguments or null if invalid argument.
		 * @return null Return early if incorrect argument.
		 */
		public function register( $module ) {
			if ( empty( $module['slug'] ) || empty( $module['name'] ) ) {
				return;
			}

			if ( empty( $module['slug'] ) || ! is_string( $module['slug'] ) || isset( $this->modules[ $module['slug'] ] ) ) {
				return;
			}

			$defaults = array(
				'name'               => '',      // String
				'slug'               => '',      // String
				'source'             => 'repo',  // String
				'required'           => false,   // Boolean
				'version'            => '',      // String
				'force_activation'   => false,   // Boolean
				'force_deactivation' => false,   // Boolean
				'external_url'       => '',      // String
				'is_callable'        => '',      // String|Array.
			);

			// Prepare the received data.
			$module = mcms_parse_args( $module, $defaults );

			// Standardize the received slug.
			$module['slug'] = $this->sanitize_key( $module['slug'] );

			// Forgive users for using string versions of booleans or floats for version number.
			$module['version']            = (string) $module['version'];
			$module['source']             = empty( $module['source'] ) ? 'repo' : $module['source'];
			$module['required']           = TGMPA_Utils::validate_bool( $module['required'] );
			$module['force_activation']   = TGMPA_Utils::validate_bool( $module['force_activation'] );
			$module['force_deactivation'] = TGMPA_Utils::validate_bool( $module['force_deactivation'] );

			// Enrich the received data.
			$module['file_path']   = $this->_get_module_basename_from_slug( $module['slug'] );
			$module['source_type'] = $this->get_module_source_type( $module['source'] );

			// Set the class properties.
			$this->modules[ $module['slug'] ]    = $module;
			$this->sort_order[ $module['slug'] ] = $module['name'];

			// Should we add the force activation hook ?
			if ( true === $module['force_activation'] ) {
				$this->has_forced_activation = true;
			}

			// Should we add the force deactivation hook ?
			if ( true === $module['force_deactivation'] ) {
				$this->has_forced_deactivation = true;
			}
		}

		/**
		 * Determine what type of source the module comes from.
		 *
		 * @since 2.5.0
		 *
		 * @param string $source The source of the module as provided, either empty (= MCMS repo), a file path
		 *                       (= bundled) or an external URL.
		 * @return string 'repo', 'external', or 'bundled'
		 */
		protected function get_module_source_type( $source ) {
			if ( 'repo' === $source || preg_match( self::MCMS_REPO_REGEX, $source ) ) {
				return 'repo';
			} elseif ( preg_match( self::IS_URL_REGEX, $source ) ) {
				return 'external';
			} else {
				return 'bundled';
			}
		}

		/**
		 * Sanitizes a string key.
		 *
		 * Near duplicate of MCMS Core `sanitize_key()`. The difference is that uppercase characters *are*
		 * allowed, so as not to break upgrade paths from non-standard bundled modules using uppercase
		 * characters in the module directory path/slug. Silly them.
		 *
		 * @see https://developer.mandarincms.org/reference/hooks/sanitize_key/
		 *
		 * @since 2.5.0
		 *
		 * @param string $key String key.
		 * @return string Sanitized key
		 */
		public function sanitize_key( $key ) {
			$raw_key = $key;
			$key     = preg_replace( '`[^A-Za-z0-9_-]`', '', $key );

			/**
			 * Filter a sanitized key string.
			 *
			 * @since 2.5.0
			 *
			 * @param string $key     Sanitized key.
			 * @param string $raw_key The key prior to sanitization.
			 */
			return apply_filters( 'tgmpa_sanitize_key', $key, $raw_key );
		}

		/**
		 * Amend default configuration settings.
		 *
		 * @since 2.0.0
		 *
		 * @param array $config Array of config options to pass as class properties.
		 */
		public function config( $config ) {
			$keys = array(
				'id',
				'default_path',
				'has_notices',
				'dismissable',
				'dismiss_msg',
				'menu',
				'parent_slug',
				'capability',
				'is_automatic',
				'message',
				'strings',
			);

			foreach ( $keys as $key ) {
				if ( isset( $config[ $key ] ) ) {
					if ( is_array( $config[ $key ] ) ) {
						$this->$key = array_merge( $this->$key, $config[ $key ] );
					} else {
						$this->$key = $config[ $key ];
					}
				}
			}
		}

		/**
		 * Amend action link after module installation.
		 *
		 * @since 2.0.0
		 *
		 * @param array $install_actions Existing array of actions.
		 * @return false|array Amended array of actions.
		 */
		public function actions( $install_actions ) {
			// Remove action links on the TGMPA install page.
			if ( $this->is_tgmpa_page() ) {
				return false;
			}

			return $install_actions;
		}

		/**
		 * Flushes the modules cache on myskin switch to prevent stale entries
		 * from remaining in the module table.
		 *
		 * @since 2.4.0
		 *
		 * @param bool $clear_update_cache Optional. Whether to clear the Module updates cache.
		 *                                 Parameter added in v2.5.0.
		 */
		public function flush_modules_cache( $clear_update_cache = true ) {
			mcms_clean_modules_cache( $clear_update_cache );
		}

		/**
		 * Set file_path key for each installed module.
		 *
		 * @since 2.1.0
		 *
		 * @param string $module_slug Optional. If set, only (re-)populates the file path for that specific module.
		 *                            Parameter added in v2.5.0.
		 */
		public function populate_file_path( $module_slug = '' ) {
			if ( ! empty( $module_slug ) && is_string( $module_slug ) && isset( $this->modules[ $module_slug ] ) ) {
				$this->modules[ $module_slug ]['file_path'] = $this->_get_module_basename_from_slug( $module_slug );
			} else {
				// Add file_path key for all modules.
				foreach ( $this->modules as $slug => $values ) {
					$this->modules[ $slug ]['file_path'] = $this->_get_module_basename_from_slug( $slug );
				}
			}
		}

		/**
		 * Helper function to extract the file path of the module file from the
		 * module slug, if the module is installed.
		 *
		 * @since 2.0.0
		 *
		 * @param string $slug Module slug (typically folder name) as provided by the developer.
		 * @return string Either file path for module if installed, or just the module slug.
		 */
		protected function _get_module_basename_from_slug( $slug ) {
			$keys = array_keys( $this->get_modules() );

			foreach ( $keys as $key ) {
				if ( preg_match( '|^' . $slug . '/|', $key ) ) {
					return $key;
				}
			}

			return $slug;
		}

		/**
		 * Retrieve module data, given the module name.
		 *
		 * Loops through the registered modules looking for $name. If it finds it,
		 * it returns the $data from that module. Otherwise, returns false.
		 *
		 * @since 2.1.0
		 *
		 * @param string $name Name of the module, as it was registered.
		 * @param string $data Optional. Array key of module data to return. Default is slug.
		 * @return string|boolean Module slug if found, false otherwise.
		 */
		public function _get_module_data_from_name( $name, $data = 'slug' ) {
			foreach ( $this->modules as $values ) {
				if ( $name === $values['name'] && isset( $values[ $data ] ) ) {
					return $values[ $data ];
				}
			}

			return false;
		}

		/**
		 * Retrieve the download URL for a package.
		 *
		 * @since 2.5.0
		 *
		 * @param string $slug Module slug.
		 * @return string Module download URL or path to local file or empty string if undetermined.
		 */
		public function get_download_url( $slug ) {
			$dl_source = '';

			switch ( $this->modules[ $slug ]['source_type'] ) {
				case 'repo':
					return $this->get_mcms_repo_download_url( $slug );
				case 'external':
					return $this->modules[ $slug ]['source'];
				case 'bundled':
					return $this->default_path . $this->modules[ $slug ]['source'];
			}

			return $dl_source; // Should never happen.
		}

		/**
		 * Retrieve the download URL for a MCMS repo package.
		 *
		 * @since 2.5.0
		 *
		 * @param string $slug Module slug.
		 * @return string Module download URL.
		 */
		protected function get_mcms_repo_download_url( $slug ) {
			$source = '';
			$api    = $this->get_modules_api( $slug );

			if ( false !== $api && isset( $api->download_link ) ) {
				$source = $api->download_link;
			}

			return $source;
		}

		/**
		 * Try to grab information from MandarinCMS API.
		 *
		 * @since 2.5.0
		 *
		 * @param string $slug Module slug.
		 * @return object Modules_api response object on success, MCMS_Error on failure.
		 */
		protected function get_modules_api( $slug ) {
			static $api = array(); // Cache received responses.

			if ( ! isset( $api[ $slug ] ) ) {
				if ( ! function_exists( 'modules_api' ) ) {
					require_once BASED_TREE_URI . 'mcms-admin/includes/module-install.php';
				}

				$response = modules_api( 'module_information', array( 'slug' => $slug, 'fields' => array( 'sections' => false ) ) );

				$api[ $slug ] = false;

				if ( is_mcms_error( $response ) ) {
					mcms_die( esc_html( $this->strings['oops'] ) );
				} else {
					$api[ $slug ] = $response;
				}
			}

			return $api[ $slug ];
		}

		/**
		 * Retrieve a link to a module information page.
		 *
		 * @since 2.5.0
		 *
		 * @param string $slug Module slug.
		 * @return string Fully formed html link to a module information page if available
		 *                or the module name if not.
		 */
		public function get_info_link( $slug ) {
			if ( ! empty( $this->modules[ $slug ]['external_url'] ) && preg_match( self::IS_URL_REGEX, $this->modules[ $slug ]['external_url'] ) ) {
				$link = sprintf(
					'<a href="%1$s" target="_blank">%2$s</a>',
					esc_url( $this->modules[ $slug ]['external_url'] ),
					esc_html( $this->modules[ $slug ]['name'] )
				);
			} elseif ( 'repo' === $this->modules[ $slug ]['source_type'] ) {
				$url = add_query_arg(
					array(
						'tab'       => 'module-information',
						'module'    => urlencode( $slug ),
						'TB_iframe' => 'true',
						'width'     => '640',
						'height'    => '500',
					),
					self_admin_url( 'module-install.php' )
				);

				$link = sprintf(
					'<a href="%1$s" class="thickbox">%2$s</a>',
					esc_url( $url ),
					esc_html( $this->modules[ $slug ]['name'] )
				);
			} else {
				$link = esc_html( $this->modules[ $slug ]['name'] ); // No hyperlink.
			}

			return $link;
		}

		/**
		 * Determine if we're on the TGMPA Install page.
		 *
		 * @since 2.1.0
		 *
		 * @return boolean True when on the TGMPA page, false otherwise.
		 */
		protected function is_tgmpa_page() {
			return isset( $_GET['page'] ) && $this->menu === $_GET['page'];
		}

		/**
		 * Determine if we're on a MCMS Core installation/upgrade page.
		 *
		 * @since 2.6.0
		 *
		 * @return boolean True when on a MCMS Core installation/upgrade page, false otherwise.
		 */
		protected function is_core_update_page() {
			// Current screen is not always available, most notably on the customizer screen.
			if ( ! function_exists( 'get_current_screen' ) ) {
				return false;
			}

			$screen = get_current_screen();

			if ( 'update-core' === $screen->base ) {
				// Core update screen.
				return true;
			} elseif ( 'modules' === $screen->base && ! empty( $_POST['action'] ) ) { // MCMSCS: CSRF ok.
				// Modules bulk update screen.
				return true;
			} elseif ( 'update' === $screen->base && ! empty( $_POST['action'] ) ) { // MCMSCS: CSRF ok.
				// Individual updates (ajax call).
				return true;
			}

			return false;
		}

		/**
		 * Retrieve the URL to the TGMPA Install page.
		 *
		 * I.e. depending on the config settings passed something along the lines of:
		 * http://example.com/mcms-admin/myskins.php?page=tgmpa-install-modules
		 *
		 * @since 2.5.0
		 *
		 * @return string Properly encoded URL (not escaped).
		 */
		public function get_tgmpa_url() {
			static $url;

			if ( ! isset( $url ) ) {
				$parent = $this->parent_slug;
				if ( false === strpos( $parent, '.php' ) ) {
					$parent = 'admin.php';
				}
				$url = add_query_arg(
					array(
						'page' => urlencode( $this->menu ),
					),
					self_admin_url( $parent )
				);
			}

			return $url;
		}

		/**
		 * Retrieve the URL to the TGMPA Install page for a specific module status (view).
		 *
		 * I.e. depending on the config settings passed something along the lines of:
		 * http://example.com/mcms-admin/myskins.php?page=tgmpa-install-modules&module_status=install
		 *
		 * @since 2.5.0
		 *
		 * @param string $status Module status - either 'install', 'update' or 'activate'.
		 * @return string Properly encoded URL (not escaped).
		 */
		public function get_tgmpa_status_url( $status ) {
			return add_query_arg(
				array(
					'module_status' => urlencode( $status ),
				),
				$this->get_tgmpa_url()
			);
		}

		/**
		 * Determine whether there are open actions for modules registered with TGMPA.
		 *
		 * @since 2.5.0
		 *
		 * @return bool True if complete, i.e. no outstanding actions. False otherwise.
		 */
		public function is_tgmpa_complete() {
			$complete = true;
			foreach ( $this->modules as $slug => $module ) {
				if ( ! $this->is_module_active( $slug ) || false !== $this->does_module_have_update( $slug ) ) {
					$complete = false;
					break;
				}
			}

			return $complete;
		}

		/**
		 * Check if a module is installed. Does not take must-use modules into account.
		 *
		 * @since 2.5.0
		 *
		 * @param string $slug Module slug.
		 * @return bool True if installed, false otherwise.
		 */
		public function is_module_installed( $slug ) {
			$installed_modules = $this->get_modules(); // Retrieve a list of all installed modules (MCMS cached).

			return ( ! empty( $installed_modules[ $this->modules[ $slug ]['file_path'] ] ) );
		}

		/**
		 * Check if a module is active.
		 *
		 * @since 2.5.0
		 *
		 * @param string $slug Module slug.
		 * @return bool True if active, false otherwise.
		 */
		public function is_module_active( $slug ) {
			return ( ( ! empty( $this->modules[ $slug ]['is_callable'] ) && is_callable( $this->modules[ $slug ]['is_callable'] ) ) || is_module_active( $this->modules[ $slug ]['file_path'] ) );
		}

		/**
		 * Check if a module can be updated, i.e. if we have information on the minimum MCMS version required
		 * available, check whether the current install meets them.
		 *
		 * @since 2.5.0
		 *
		 * @param string $slug Module slug.
		 * @return bool True if OK to update, false otherwise.
		 */
		public function can_module_update( $slug ) {
			// We currently can't get reliable info on non-MCMS-repo modules - issue #380.
			if ( 'repo' !== $this->modules[ $slug ]['source_type'] ) {
				return true;
			}

			$api = $this->get_modules_api( $slug );

			if ( false !== $api && isset( $api->requires ) ) {
				return version_compare( $this->mcms_version, $api->requires, '>=' );
			}

			// No usable info received from the modules API, presume we can update.
			return true;
		}

		/**
		 * Check to see if the module is 'updatetable', i.e. installed, with an update available
		 * and no MCMS version requirements blocking it.
		 *
		 * @since 2.6.0
		 *
		 * @param string $slug Module slug.
		 * @return bool True if OK to proceed with update, false otherwise.
		 */
		public function is_module_updatetable( $slug ) {
			if ( ! $this->is_module_installed( $slug ) ) {
				return false;
			} else {
				return ( false !== $this->does_module_have_update( $slug ) && $this->can_module_update( $slug ) );
			}
		}

		/**
		 * Check if a module can be activated, i.e. is not currently active and meets the minimum
		 * module version requirements set in TGMPA (if any).
		 *
		 * @since 2.5.0
		 *
		 * @param string $slug Module slug.
		 * @return bool True if OK to activate, false otherwise.
		 */
		public function can_module_activate( $slug ) {
			return ( ! $this->is_module_active( $slug ) && ! $this->does_module_require_update( $slug ) );
		}

		/**
		 * Retrieve the version number of an installed module.
		 *
		 * @since 2.5.0
		 *
		 * @param string $slug Module slug.
		 * @return string Version number as string or an empty string if the module is not installed
		 *                or version unknown (modules which don't comply with the module header standard).
		 */
		public function get_installed_version( $slug ) {
			$installed_modules = $this->get_modules(); // Retrieve a list of all installed modules (MCMS cached).

			if ( ! empty( $installed_modules[ $this->modules[ $slug ]['file_path'] ]['Version'] ) ) {
				return $installed_modules[ $this->modules[ $slug ]['file_path'] ]['Version'];
			}

			return '';
		}

		/**
		 * Check whether a module complies with the minimum version requirements.
		 *
		 * @since 2.5.0
		 *
		 * @param string $slug Module slug.
		 * @return bool True when a module needs to be updated, otherwise false.
		 */
		public function does_module_require_update( $slug ) {
			$installed_version = $this->get_installed_version( $slug );
			$minimum_version   = $this->modules[ $slug ]['version'];

			return version_compare( $minimum_version, $installed_version, '>' );
		}

		/**
		 * Check whether there is an update available for a module.
		 *
		 * @since 2.5.0
		 *
		 * @param string $slug Module slug.
		 * @return false|string Version number string of the available update or false if no update available.
		 */
		public function does_module_have_update( $slug ) {
			// Presume bundled and external modules will point to a package which meets the minimum required version.
			if ( 'repo' !== $this->modules[ $slug ]['source_type'] ) {
				if ( $this->does_module_require_update( $slug ) ) {
					return $this->modules[ $slug ]['version'];
				}

				return false;
			}

			$repo_updates = get_site_transient( 'update_modules' );

			if ( isset( $repo_updates->response[ $this->modules[ $slug ]['file_path'] ]->new_version ) ) {
				return $repo_updates->response[ $this->modules[ $slug ]['file_path'] ]->new_version;
			}

			return false;
		}

		/**
		 * Retrieve potential upgrade notice for a module.
		 *
		 * @since 2.5.0
		 *
		 * @param string $slug Module slug.
		 * @return string The upgrade notice or an empty string if no message was available or provided.
		 */
		public function get_upgrade_notice( $slug ) {
			// We currently can't get reliable info on non-MCMS-repo modules - issue #380.
			if ( 'repo' !== $this->modules[ $slug ]['source_type'] ) {
				return '';
			}

			$repo_updates = get_site_transient( 'update_modules' );

			if ( ! empty( $repo_updates->response[ $this->modules[ $slug ]['file_path'] ]->upgrade_notice ) ) {
				return $repo_updates->response[ $this->modules[ $slug ]['file_path'] ]->upgrade_notice;
			}

			return '';
		}

		/**
		 * Wrapper around the core MCMS get_modules function, making sure it's actually available.
		 *
		 * @since 2.5.0
		 *
		 * @param string $module_folder Optional. Relative path to single module folder.
		 * @return array Array of installed modules with module information.
		 */
		public function get_modules( $module_folder = '' ) {
			if ( ! function_exists( 'get_modules' ) ) {
				require_once BASED_TREE_URI . 'mcms-admin/includes/module.php';
			}

			return get_modules( $module_folder );
		}

		/**
		 * Delete dismissable nag option when myskin is switched.
		 *
		 * This ensures that the user(s) is/are again reminded via nag of required
		 * and/or recommended modules if they re-activate the myskin.
		 *
		 * @since 2.1.1
		 */
		public function update_dismiss() {
			delete_metadata( 'user', null, 'tgmpa_dismissed_notice_' . $this->id, null, true );
		}

		/**
		 * Forces module activation if the parameter 'force_activation' is
		 * set to true.
		 *
		 * This allows myskin authors to specify certain modules that must be
		 * active at all times while using the current myskin.
		 *
		 * Please take special care when using this parameter as it has the
		 * potential to be harmful if not used correctly. Setting this parameter
		 * to true will not allow the specified module to be deactivated unless
		 * the user switches myskins.
		 *
		 * @since 2.2.0
		 */
		public function force_activation() {
			foreach ( $this->modules as $slug => $module ) {
				if ( true === $module['force_activation'] ) {
					if ( ! $this->is_module_installed( $slug ) ) {
						// Oops, module isn't there so iterate to next condition.
						continue;
					} elseif ( $this->can_module_activate( $slug ) ) {
						// There we go, activate the module.
						activate_module( $module['file_path'] );
					}
				}
			}
		}

		/**
		 * Forces module deactivation if the parameter 'force_deactivation'
		 * is set to true and adds the module to the 'recently active' modules list.
		 *
		 * This allows myskin authors to specify certain modules that must be
		 * deactivated upon switching from the current myskin to another.
		 *
		 * Please take special care when using this parameter as it has the
		 * potential to be harmful if not used correctly.
		 *
		 * @since 2.2.0
		 */
		public function force_deactivation() {
			$deactivated = array();

			foreach ( $this->modules as $slug => $module ) {
				/*
				 * Only proceed forward if the parameter is set to true and module is active
				 * as a 'normal' (not must-use) module.
				 */
				if ( true === $module['force_deactivation'] && is_module_active( $module['file_path'] ) ) {
					deactivate_modules( $module['file_path'] );
					$deactivated[ $module['file_path'] ] = time();
				}
			}

			if ( ! empty( $deactivated ) ) {
				update_option( 'recently_activated', $deactivated + (array) get_option( 'recently_activated' ) );
			}
		}

		/**
		 * Echo the current TGMPA version number to the page.
		 *
		 * @since 2.5.0
		 */
		public function show_tgmpa_version() {
			echo '<p style="float: right; padding: 0em 1.5em 0.5em 0;"><strong><small>',
				esc_html(
					sprintf(
						/* translators: %s: version number */
						__( 'TGMPA v%s', 'jmd-worldcast' ),
						self::TGMPA_VERSION
					)
				),
				'</small></strong></p>';
		}

		/**
		 * Returns the singleton instance of the class.
		 *
		 * @since 2.4.0
		 *
		 * @return \TGM_Module_Activation The TGM_Module_Activation object.
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}
	}

	if ( ! function_exists( 'load_tgm_module_activation' ) ) {
		/**
		 * Ensure only one instance of the class is ever invoked.
		 *
		 * @since 2.5.0
		 */
		function load_tgm_module_activation() {
			$GLOBALS['tgmpa'] = TGM_Module_Activation::get_instance();
		}
	}

	if ( did_action( 'modules_loaded' ) ) {
		load_tgm_module_activation();
	} else {
		add_action( 'modules_loaded', 'load_tgm_module_activation' );
	}
}

if ( ! function_exists( 'tgmpa' ) ) {
	/**
	 * Helper function to register a collection of required modules.
	 *
	 * @since 2.0.0
	 * @api
	 *
	 * @param array $modules An array of module arrays.
	 * @param array $config  Optional. An array of configuration values.
	 */
	function tgmpa( $modules, $config = array() ) {
		$instance = call_user_func( array( get_class( $GLOBALS['tgmpa'] ), 'get_instance' ) );

		foreach ( $modules as $module ) {
			call_user_func( array( $instance, 'register' ), $module );
		}

		if ( ! empty( $config ) && is_array( $config ) ) {
			// Send out notices for deprecated arguments passed.
			if ( isset( $config['notices'] ) ) {
				_deprecated_argument( __FUNCTION__, '2.2.0', 'The `notices` config parameter was renamed to `has_notices` in TGMPA 2.2.0. Please adjust your configuration.' );
				if ( ! isset( $config['has_notices'] ) ) {
					$config['has_notices'] = $config['notices'];
				}
			}

			if ( isset( $config['parent_menu_slug'] ) ) {
				_deprecated_argument( __FUNCTION__, '2.4.0', 'The `parent_menu_slug` config parameter was removed in TGMPA 2.4.0. In TGMPA 2.5.0 an alternative was (re-)introduced. Please adjust your configuration. For more information visit the website: http://github.com/JiiSaaduddin/configuration/#h-configuration-options.' );
			}
			if ( isset( $config['parent_url_slug'] ) ) {
				_deprecated_argument( __FUNCTION__, '2.4.0', 'The `parent_url_slug` config parameter was removed in TGMPA 2.4.0. In TGMPA 2.5.0 an alternative was (re-)introduced. Please adjust your configuration. For more information visit the website: http://github.com/JiiSaaduddin/configuration/#h-configuration-options.' );
			}

			call_user_func( array( $instance, 'config' ), $config );
		}
	}
}

/**
 * MCMS_List_Table isn't always available. If it isn't available,
 * we load it here.
 *
 * @since 2.2.0
 */
if ( ! class_exists( 'MCMS_List_Table' ) ) {
	require_once BASED_TREE_URI . 'mcms-admin/includes/class-mcms-list-table.php';
}

if ( ! class_exists( 'TGMPA_List_Table' ) ) {

	/**
	 * List table class for handling modules.
	 *
	 * Extends the MCMS_List_Table class to provide a future-compatible
	 * way of listing out all required/recommended modules.
	 *
	 * Gives users an interface similar to the Module Administration
	 * area with similar (albeit stripped down) capabilities.
	 *
	 * This class also allows for the bulk install of modules.
	 *
	 * @since 2.2.0
	 *
	 * @package TGM-Module-Activation
	 * @author  Jii Saaduddin
	 * @author  Gary Jones
	 */
	class TGMPA_List_Table extends MCMS_List_Table {
		/**
		 * TGMPA instance.
		 *
		 * @since 2.5.0
		 *
		 * @var object
		 */
		protected $tgmpa;

		/**
		 * The currently chosen view.
		 *
		 * @since 2.5.0
		 *
		 * @var string One of: 'all', 'install', 'update', 'activate'
		 */
		public $view_context = 'all';

		/**
		 * The module counts for the various views.
		 *
		 * @since 2.5.0
		 *
		 * @var array
		 */
		protected $view_totals = array(
			'all'      => 0,
			'install'  => 0,
			'update'   => 0,
			'activate' => 0,
		);

		/**
		 * References parent constructor and sets defaults for class.
		 *
		 * @since 2.2.0
		 */
		public function __construct() {
			$this->tgmpa = call_user_func( array( get_class( $GLOBALS['tgmpa'] ), 'get_instance' ) );

			parent::__construct(
				array(
					'singular' => 'module',
					'plural'   => 'modules',
					'ajax'     => false,
				)
			);

			if ( isset( $_REQUEST['module_status'] ) && in_array( sanitize_key($_REQUEST['module_status']), array( 'install', 'update', 'activate' ), true ) ) {
				$this->view_context = sanitize_key( $_REQUEST['module_status'] );
			}

			add_filter( 'tgmpa_table_data_items', array( $this, 'sort_table_items' ) );
		}

		/**
		 * Get a list of CSS classes for the <table> tag.
		 *
		 * Overruled to prevent the 'plural' argument from being added.
		 *
		 * @since 2.5.0
		 *
		 * @return array CSS classnames.
		 */
		public function get_table_classes() {
			return array( 'widefat', 'fixed' );
		}

		/**
		 * Gathers and renames all of our module information to be used by MCMS_List_Table to create our table.
		 *
		 * @since 2.2.0
		 *
		 * @return array $table_data Information for use in table.
		 */
		protected function _gather_module_data() {
			// Load thickbox for module links.
			$this->tgmpa->admin_init();
			$this->tgmpa->thickbox();

			// Categorize the modules which have open actions.
			$modules = $this->categorize_modules_to_views();

			// Set the counts for the view links.
			$this->set_view_totals( $modules );

			// Prep variables for use and grab list of all installed modules.
			$table_data = array();
			$i          = 0;

			// Redirect to the 'all' view if no modules were found for the selected view context.
			if ( empty( $modules[ $this->view_context ] ) ) {
				$this->view_context = 'all';
			}

			foreach ( $modules[ $this->view_context ] as $slug => $module ) {
				$table_data[ $i ]['sanitized_module']  = $module['name'];
				$table_data[ $i ]['slug']              = $slug;
				$table_data[ $i ]['module']            = '<strong>' . $this->tgmpa->get_info_link( $slug ) . '</strong>';
				$table_data[ $i ]['source']            = $this->get_module_source_type_text( $module['source_type'] );
				$table_data[ $i ]['type']              = $this->get_module_advise_type_text( $module['required'] );
				$table_data[ $i ]['status']            = $this->get_module_status_text( $slug );
				$table_data[ $i ]['installed_version'] = $this->tgmpa->get_installed_version( $slug );
				$table_data[ $i ]['minimum_version']   = $module['version'];
				$table_data[ $i ]['available_version'] = $this->tgmpa->does_module_have_update( $slug );

				// Prep the upgrade notice info.
				$upgrade_notice = $this->tgmpa->get_upgrade_notice( $slug );
				if ( ! empty( $upgrade_notice ) ) {
					$table_data[ $i ]['upgrade_notice'] = $upgrade_notice;

					add_action( "tgmpa_after_module_row_{$slug}", array( $this, 'mcms_module_update_row' ), 10, 2 );
				}

				$table_data[ $i ] = apply_filters( 'tgmpa_table_data_item', $table_data[ $i ], $module );

				$i++;
			}

			return $table_data;
		}

		/**
		 * Categorize the modules which have open actions into views for the TGMPA page.
		 *
		 * @since 2.5.0
		 */
		protected function categorize_modules_to_views() {
			$modules = array(
				'all'      => array(), // Meaning: all modules which still have open actions.
				'install'  => array(),
				'update'   => array(),
				'activate' => array(),
			);

			foreach ( $this->tgmpa->modules as $slug => $module ) {
				if ( $this->tgmpa->is_module_active( $slug ) && false === $this->tgmpa->does_module_have_update( $slug ) ) {
					// No need to display modules if they are installed, up-to-date and active.
					continue;
				} else {
					$modules['all'][ $slug ] = $module;

					if ( ! $this->tgmpa->is_module_installed( $slug ) ) {
						$modules['install'][ $slug ] = $module;
					} else {
						if ( false !== $this->tgmpa->does_module_have_update( $slug ) ) {
							$modules['update'][ $slug ] = $module;
						}

						if ( $this->tgmpa->can_module_activate( $slug ) ) {
							$modules['activate'][ $slug ] = $module;
						}
					}
				}
			}

			return $modules;
		}

		/**
		 * Set the counts for the view links.
		 *
		 * @since 2.5.0
		 *
		 * @param array $modules Modules order by view.
		 */
		protected function set_view_totals( $modules ) {
			foreach ( $modules as $type => $list ) {
				$this->view_totals[ $type ] = count( $list );
			}
		}

		/**
		 * Get the module required/recommended text string.
		 *
		 * @since 2.5.0
		 *
		 * @param string $required Module required setting.
		 * @return string
		 */
		protected function get_module_advise_type_text( $required ) {
			if ( true === $required ) {
				return __( 'Required', 'jmd-worldcast' );
			}

			return __( 'Recommended', 'jmd-worldcast' );
		}

		/**
		 * Get the module source type text string.
		 *
		 * @since 2.5.0
		 *
		 * @param string $type Module type.
		 * @return string
		 */
		protected function get_module_source_type_text( $type ) {
			$string = '';

			switch ( $type ) {
				case 'repo':
					$string = __( 'MandarinCMS Repository', 'jmd-worldcast' );
					break;
				case 'external':
					$string = __( 'External Source', 'jmd-worldcast' );
					break;
				case 'bundled':
					$string = __( 'Pre-Packaged', 'jmd-worldcast' );
					break;
			}

			return $string;
		}

		/**
		 * Determine the module status message.
		 *
		 * @since 2.5.0
		 *
		 * @param string $slug Module slug.
		 * @return string
		 */
		protected function get_module_status_text( $slug ) {
			if ( ! $this->tgmpa->is_module_installed( $slug ) ) {
				return __( 'Not Installed', 'jmd-worldcast' );
			}

			if ( ! $this->tgmpa->is_module_active( $slug ) ) {
				$install_status = __( 'Installed But Not Activated', 'jmd-worldcast' );
			} else {
				$install_status = __( 'Active', 'jmd-worldcast' );
			}

			$update_status = '';

			if ( $this->tgmpa->does_module_require_update( $slug ) && false === $this->tgmpa->does_module_have_update( $slug ) ) {
				$update_status = __( 'Required Update not Available', 'jmd-worldcast' );

			} elseif ( $this->tgmpa->does_module_require_update( $slug ) ) {
				$update_status = __( 'Requires Update', 'jmd-worldcast' );

			} elseif ( false !== $this->tgmpa->does_module_have_update( $slug ) ) {
				$update_status = __( 'Update recommended', 'jmd-worldcast' );
			}

			if ( '' === $update_status ) {
				return $install_status;
			}

			return sprintf(
				/* translators: 1: install status, 2: update status */
				_x( '%1$s, %2$s', 'Install/Update Status', 'jmd-worldcast' ),
				$install_status,
				$update_status
			);
		}

		/**
		 * Sort modules by Required/Recommended type and by alphabetical module name within each type.
		 *
		 * @since 2.5.0
		 *
		 * @param array $items Prepared table items.
		 * @return array Sorted table items.
		 */
		public function sort_table_items( $items ) {
			$type = array();
			$name = array();

			foreach ( $items as $i => $module ) {
				$type[ $i ] = $module['type']; // Required / recommended.
				$name[ $i ] = $module['sanitized_module'];
			}

			array_multisort( $type, SORT_DESC, $name, SORT_ASC, $items );

			return $items;
		}

		/**
		 * Get an associative array ( id => link ) of the views available on this table.
		 *
		 * @since 2.5.0
		 *
		 * @return array
		 */
		public function get_views() {
			$status_links = array();

			foreach ( $this->view_totals as $type => $count ) {
				if ( $count < 1 ) {
					continue;
				}

				switch ( $type ) {
					case 'all':
						/* translators: 1: number of modules. */
						$text = _nx( 'All <span class="count">(%s)</span>', 'All <span class="count">(%s)</span>', $count, 'modules', 'jmd-worldcast' );
						break;
					case 'install':
						/* translators: 1: number of modules. */
						$text = _n( 'To Install <span class="count">(%s)</span>', 'To Install <span class="count">(%s)</span>', $count, 'jmd-worldcast' );
						break;
					case 'update':
						/* translators: 1: number of modules. */
						$text = _n( 'Update Available <span class="count">(%s)</span>', 'Update Available <span class="count">(%s)</span>', $count, 'jmd-worldcast' );
						break;
					case 'activate':
						/* translators: 1: number of modules. */
						$text = _n( 'To Activate <span class="count">(%s)</span>', 'To Activate <span class="count">(%s)</span>', $count, 'jmd-worldcast' );
						break;
					default:
						$text = '';
						break;
				}

				if ( ! empty( $text ) ) {

					$status_links[ $type ] = sprintf(
						'<a href="%s"%s>%s</a>',
						esc_url( $this->tgmpa->get_tgmpa_status_url( $type ) ),
						( $type === $this->view_context ) ? ' class="current"' : '',
						sprintf( $text, number_format_i18n( $count ) )
					);
				}
			}

			return $status_links;
		}

		/**
		 * Create default columns to display important module information
		 * like type, action and status.
		 *
		 * @since 2.2.0
		 *
		 * @param array  $item        Array of item data.
		 * @param string $column_name The name of the column.
		 * @return string
		 */
		public function column_default( $item, $column_name ) {
			return $item[ $column_name ];
		}

		/**
		 * Required for bulk installing.
		 *
		 * Adds a checkbox for each module.
		 *
		 * @since 2.2.0
		 *
		 * @param array $item Array of item data.
		 * @return string The input checkbox with all necessary info.
		 */
		public function column_cb( $item ) {
			return sprintf(
				'<input type="checkbox" name="%1$s[]" value="%2$s" id="%3$s" />',
				esc_attr( $this->_args['singular'] ),
				esc_attr( $item['slug'] ),
				esc_attr( $item['sanitized_module'] )
			);
		}

		/**
		 * Create default title column along with the action links.
		 *
		 * @since 2.2.0
		 *
		 * @param array $item Array of item data.
		 * @return string The module name and action links.
		 */
		public function column_module( $item ) {
			return sprintf(
				'%1$s %2$s',
				$item['module'],
				$this->row_actions( $this->get_row_actions( $item ), true )
			);
		}

		/**
		 * Create version information column.
		 *
		 * @since 2.5.0
		 *
		 * @param array $item Array of item data.
		 * @return string HTML-formatted version information.
		 */
		public function column_version( $item ) {
			$output = array();

			if ( $this->tgmpa->is_module_installed( $item['slug'] ) ) {
				$installed = ! empty( $item['installed_version'] ) ? $item['installed_version'] : _x( 'unknown', 'as in: "version nr unknown"', 'jmd-worldcast' );

				$color = '';
				if ( ! empty( $item['minimum_version'] ) && $this->tgmpa->does_module_require_update( $item['slug'] ) ) {
					$color = ' color: #ff0000; font-weight: bold;';
				}

				$output[] = sprintf(
					'<p><span style="min-width: 32px; text-align: right; float: right;%1$s">%2$s</span>' . __( 'Installed version:', 'jmd-worldcast' ) . '</p>',
					$color,
					$installed
				);
			}

			if ( ! empty( $item['minimum_version'] ) ) {
				$output[] = sprintf(
					'<p><span style="min-width: 32px; text-align: right; float: right;">%1$s</span>' . __( 'Minimum required version:', 'jmd-worldcast' ) . '</p>',
					$item['minimum_version']
				);
			}

			if ( ! empty( $item['available_version'] ) ) {
				$color = '';
				if ( ! empty( $item['minimum_version'] ) && version_compare( $item['available_version'], $item['minimum_version'], '>=' ) ) {
					$color = ' color: #71C671; font-weight: bold;';
				}

				$output[] = sprintf(
					'<p><span style="min-width: 32px; text-align: right; float: right;%1$s">%2$s</span>' . __( 'Available version:', 'jmd-worldcast' ) . '</p>',
					$color,
					$item['available_version']
				);
			}

			if ( empty( $output ) ) {
				return '&nbsp;'; // Let's not break the table layout.
			} else {
				return implode( "\n", $output );
			}
		}

		/**
		 * Sets default message within the modules table if no modules
		 * are left for interaction.
		 *
		 * Hides the menu item to prevent the user from clicking and
		 * getting a permissions error.
		 *
		 * @since 2.2.0
		 */
		public function no_items() {
			echo esc_html__( 'No modules to install, update or activate.', 'jmd-worldcast' ) . ' <a href="' . esc_url( self_admin_url() ) . '"> ' . esc_html__( 'Return to the Dashboard', 'jmd-worldcast' ) . '</a>';
			echo '<style type="text/css">#adminmenu .mcms-submenu li.current { display: none !important; }</style>';
		}

		/**
		 * Output all the column information within the table.
		 *
		 * @since 2.2.0
		 *
		 * @return array $columns The column names.
		 */
		public function get_columns() {
			$columns = array(
				'cb'     => '<input type="checkbox" />',
				'module' => __( 'Module', 'jmd-worldcast' ),
				'source' => __( 'Source', 'jmd-worldcast' ),
				'type'   => __( 'Type', 'jmd-worldcast' ),
			);

			if ( 'all' === $this->view_context || 'update' === $this->view_context ) {
				$columns['version'] = __( 'Version', 'jmd-worldcast' );
				$columns['status']  = __( 'Status', 'jmd-worldcast' );
			}

			return apply_filters( 'tgmpa_table_columns', $columns );
		}

		/**
		 * Get name of default primary column
		 *
		 * @since 2.5.0 / MCMS 4.3+ compatibility
		 * @access protected
		 *
		 * @return string
		 */
		protected function get_default_primary_column_name() {
			return 'module';
		}

		/**
		 * Get the name of the primary column.
		 *
		 * @since 2.5.0 / MCMS 4.3+ compatibility
		 * @access protected
		 *
		 * @return string The name of the primary column.
		 */
		protected function get_primary_column_name() {
			if ( method_exists( 'MCMS_List_Table', 'get_primary_column_name' ) ) {
				return parent::get_primary_column_name();
			} else {
				return $this->get_default_primary_column_name();
			}
		}

		/**
		 * Get the actions which are relevant for a specific module row.
		 *
		 * @since 2.5.0
		 *
		 * @param array $item Array of item data.
		 * @return array Array with relevant action links.
		 */
		protected function get_row_actions( $item ) {
			$actions      = array();
			$action_links = array();

			// Display the 'Install' action link if the module is not yet available.
			if ( ! $this->tgmpa->is_module_installed( $item['slug'] ) ) {
				/* translators: %2$s: module name in screen reader markup */
				$actions['install'] = __( 'Install %2$s', 'jmd-worldcast' );
			} else {
				// Display the 'Update' action link if an update is available and MCMS complies with module minimum.
				if ( false !== $this->tgmpa->does_module_have_update( $item['slug'] ) && $this->tgmpa->can_module_update( $item['slug'] ) ) {
					/* translators: %2$s: module name in screen reader markup */
					$actions['update'] = __( 'Update %2$s', 'jmd-worldcast' );
				}

				// Display the 'Activate' action link, but only if the module meets the minimum version.
				if ( $this->tgmpa->can_module_activate( $item['slug'] ) ) {
					/* translators: %2$s: module name in screen reader markup */
					$actions['activate'] = __( 'Activate %2$s', 'jmd-worldcast' );
				}
			}

			// Create the actual links.
			foreach ( $actions as $action => $text ) {
				$nonce_url = mcms_nonce_url(
					add_query_arg(
						array(
							'module'           => urlencode( $item['slug'] ),
							'tgmpa-' . $action => $action . '-module',
						),
						$this->tgmpa->get_tgmpa_url()
					),
					'tgmpa-' . $action,
					'tgmpa-nonce'
				);

				$action_links[ $action ] = sprintf(
					'<a href="%1$s">' . esc_html( $text ) . '</a>', // $text contains the second placeholder.
					esc_url( $nonce_url ),
					'<span class="screen-reader-text">' . esc_html( $item['sanitized_module'] ) . '</span>'
				);
			}

			$prefix = ( defined( 'MCMS_NETWORK_ADMIN' ) && MCMS_NETWORK_ADMIN ) ? 'network_admin_' : '';
			return apply_filters( "tgmpa_{$prefix}module_action_links", array_filter( $action_links ), $item['slug'], $item, $this->view_context );
		}

		/**
		 * Generates content for a single row of the table.
		 *
		 * @since 2.5.0
		 *
		 * @param object $item The current item.
		 */
		public function single_row( $item ) {
			parent::single_row( $item );

			/**
			 * Fires after each specific row in the TGMPA Modules list table.
			 *
			 * The dynamic portion of the hook name, `$item['slug']`, refers to the slug
			 * for the module.
			 *
			 * @since 2.5.0
			 */
			do_action( "tgmpa_after_module_row_{$item['slug']}", $item['slug'], $item, $this->view_context );
		}

		/**
		 * Show the upgrade notice below a module row if there is one.
		 *
		 * @since 2.5.0
		 *
		 * @see /mcms-admin/includes/update.php
		 *
		 * @param string $slug Module slug.
		 * @param array  $item The information available in this table row.
		 * @return null Return early if upgrade notice is empty.
		 */
		public function mcms_module_update_row( $slug, $item ) {
			if ( empty( $item['upgrade_notice'] ) ) {
				return;
			}

			echo '
				<tr class="module-update-tr">
					<td colspan="', absint( $this->get_column_count() ), '" class="module-update colspanchange">
						<div class="update-message">',
							esc_html__( 'Upgrade message from the module author:', 'jmd-worldcast' ),
							' <strong>', mcms_kses_data( $item['upgrade_notice'] ), '</strong>
						</div>
					</td>
				</tr>';
		}

		/**
		 * Extra controls to be displayed between bulk actions and pagination.
		 *
		 * @since 2.5.0
		 *
		 * @param string $which 'top' or 'bottom' table navigation.
		 */
		public function extra_tablenav( $which ) {
			if ( 'bottom' === $which ) {
				$this->tgmpa->show_tgmpa_version();
			}
		}

		/**
		 * Defines the bulk actions for handling registered modules.
		 *
		 * @since 2.2.0
		 *
		 * @return array $actions The bulk actions for the module install table.
		 */
		public function get_bulk_actions() {

			$actions = array();

			if ( 'update' !== $this->view_context && 'activate' !== $this->view_context ) {
				if ( current_user_can( 'install_modules' ) ) {
					$actions['tgmpa-bulk-install'] = __( 'Install', 'jmd-worldcast' );
				}
			}

			if ( 'install' !== $this->view_context ) {
				if ( current_user_can( 'update_modules' ) ) {
					$actions['tgmpa-bulk-update'] = __( 'Update', 'jmd-worldcast' );
				}
				if ( current_user_can( 'activate_modules' ) ) {
					$actions['tgmpa-bulk-activate'] = __( 'Activate', 'jmd-worldcast' );
				}
			}

			return $actions;
		}

		/**
		 * Processes bulk installation and activation actions.
		 *
		 * The bulk installation process looks for the $_POST information and passes that
		 * through if a user has to use MCMS_Filesystem to enter their credentials.
		 *
		 * @since 2.2.0
		 */
		public function process_bulk_actions() {
			// Bulk installation process.
			if ( 'tgmpa-bulk-install' === $this->current_action() || 'tgmpa-bulk-update' === $this->current_action() ) {

				check_admin_referer( 'bulk-' . $this->_args['plural'] );

				$install_type = 'install';
				if ( 'tgmpa-bulk-update' === $this->current_action() ) {
					$install_type = 'update';
				}

				$modules_to_install = array();

				// Did user actually select any modules to install/update ?
				if ( empty( $_POST['module'] ) ) {
					if ( 'install' === $install_type ) {
						$message = __( 'No modules were selected to be installed. No action taken.', 'jmd-worldcast' );
					} else {
						$message = __( 'No modules were selected to be updated. No action taken.', 'jmd-worldcast' );
					}

					echo '<div id="message" class="error"><p>', esc_html( $message ), '</p></div>';

					return false;
				}

				if ( is_array( $_POST['module'] ) ) {
					$modules_to_install = (array) sanitize_key($_POST['module']);
				} elseif ( is_string( sanitize_key($_POST['module']) ) ) {
					// Received via Filesystem page - un-flatten array (MCMS bug #19643).
					$modules_to_install = explode( ',', sanitize_key($_POST['module']) );
				}

				// Sanitize the received input.
				$modules_to_install = array_map( 'urldecode', $modules_to_install );
				$modules_to_install = array_map( array( $this->tgmpa, 'sanitize_key' ), $modules_to_install );

				// Validate the received input.
				foreach ( $modules_to_install as $key => $slug ) {
					// Check if the module was registered with TGMPA and remove if not.
					if ( ! isset( $this->tgmpa->modules[ $slug ] ) ) {
						unset( $modules_to_install[ $key ] );
						continue;
					}

					// For install: make sure this is a module we *can* install and not one already installed.
					if ( 'install' === $install_type && true === $this->tgmpa->is_module_installed( $slug ) ) {
						unset( $modules_to_install[ $key ] );
					}

					// For updates: make sure this is a module we *can* update (update available and MCMS version ok).
					if ( 'update' === $install_type && false === $this->tgmpa->is_module_updatetable( $slug ) ) {
						unset( $modules_to_install[ $key ] );
					}
				}

				// No need to proceed further if we have no modules to handle.
				if ( empty( $modules_to_install ) ) {
					if ( 'install' === $install_type ) {
						$message = __( 'No modules are available to be installed at this time.', 'jmd-worldcast' );
					} else {
						$message = __( 'No modules are available to be updated at this time.', 'jmd-worldcast' );
					}

					echo '<div id="message" class="error"><p>', esc_html( $message ), '</p></div>';

					return false;
				}

				// Pass all necessary information if MCMS_Filesystem is needed.
				$url = mcms_nonce_url(
					$this->tgmpa->get_tgmpa_url(),
					'bulk-' . $this->_args['plural']
				);

				// Give validated data back to $_POST which is the only place the filesystem looks for extra fields.
				$_POST['module'] = implode( ',', $modules_to_install ); // Work around for MCMS bug #19643.

				$method = ''; // Leave blank so MCMS_Filesystem can populate it as necessary.
				$fields = array_keys( $_POST ); // Extra fields to pass to MCMS_Filesystem.

				if ( false === ( $creds = request_filesystem_credentials( esc_url_raw( $url ), $method, false, false, $fields ) ) ) {
					return true; // Stop the normal page form from displaying, credential request form will be shown.
				}

				// Now we have some credentials, setup MCMS_Filesystem.
				if ( ! MCMS_Filesystem( $creds ) ) {
					// Our credentials were no good, ask the user for them again.
					request_filesystem_credentials( esc_url_raw( $url ), $method, true, false, $fields );

					return true;
				}

				/* If we arrive here, we have the filesystem */

				// Store all information in arrays since we are processing a bulk installation.
				$names      = array();
				$sources    = array(); // Needed for installs.
				$file_paths = array(); // Needed for upgrades.
				$to_inject  = array(); // Information to inject into the update_modules transient.

				// Prepare the data for validated modules for the install/upgrade.
				foreach ( $modules_to_install as $slug ) {
					$name   = $this->tgmpa->modules[ $slug ]['name'];
					$source = $this->tgmpa->get_download_url( $slug );

					if ( ! empty( $name ) && ! empty( $source ) ) {
						$names[] = $name;

						switch ( $install_type ) {

							case 'install':
								$sources[] = $source;
								break;

							case 'update':
								$file_paths[]                 = $this->tgmpa->modules[ $slug ]['file_path'];
								$to_inject[ $slug ]           = $this->tgmpa->modules[ $slug ];
								$to_inject[ $slug ]['source'] = $source;
								break;
						}
					}
				}
				unset( $slug, $name, $source );

				// Create a new instance of TGMPA_Bulk_Installer.
				$installer = new TGMPA_Bulk_Installer(
					new TGMPA_Bulk_Installer_Skin(
						array(
							'url'          => esc_url_raw( $this->tgmpa->get_tgmpa_url() ),
							'nonce'        => 'bulk-' . $this->_args['plural'],
							'names'        => $names,
							'install_type' => $install_type,
						)
					)
				);

				// Wrap the install process with the appropriate HTML.
				echo '<div class="tgmpa">',
					'<h2 style="font-size: 23px; font-weight: 400; line-height: 29px; margin: 0; padding: 9px 15px 4px 0;">', esc_html( get_admin_page_title() ), '</h2>
					<div class="update-php" style="width: 100%; height: 98%; min-height: 850px; padding-top: 1px;">';

				// Process the bulk installation submissions.
				add_filter( 'upgrader_source_selection', array( $this->tgmpa, 'maybe_adjust_source_dir' ), 1, 3 );

				if ( 'tgmpa-bulk-update' === $this->current_action() ) {
					// Inject our info into the update transient.
					$this->tgmpa->inject_update_info( $to_inject );

					$installer->bulk_upgrade( $file_paths );
				} else {
					$installer->bulk_install( $sources );
				}

				remove_filter( 'upgrader_source_selection', array( $this->tgmpa, 'maybe_adjust_source_dir' ), 1 );

				echo '</div></div>';

				return true;
			}

			// Bulk activation process.
			if ( 'tgmpa-bulk-activate' === $this->current_action() ) {
				check_admin_referer( 'bulk-' . $this->_args['plural'] );

				// Did user actually select any modules to activate ?
				if ( empty( $_POST['module'] ) ) {
					echo '<div id="message" class="error"><p>', esc_html__( 'No modules were selected to be activated. No action taken.', 'jmd-worldcast' ), '</p></div>';

					return false;
				}

				// Grab module data from $_POST.
				$modules = array();
				if ( isset( $_POST['module'] ) ) {
					$modules = array_map( 'urldecode', (array) sanitize_key($_POST['module']) );
					$modules = array_map( array( $this->tgmpa, 'sanitize_key' ), $modules );
				}

				$modules_to_activate = array();
				$module_names        = array();

				// Grab the file paths for the selected & inactive modules from the registration array.
				foreach ( $modules as $slug ) {
					if ( $this->tgmpa->can_module_activate( $slug ) ) {
						$modules_to_activate[] = $this->tgmpa->modules[ $slug ]['file_path'];
						$module_names[]        = $this->tgmpa->modules[ $slug ]['name'];
					}
				}
				unset( $slug );

				// Return early if there are no modules to activate.
				if ( empty( $modules_to_activate ) ) {
					echo '<div id="message" class="error"><p>', esc_html__( 'No modules are available to be activated at this time.', 'jmd-worldcast' ), '</p></div>';

					return false;
				}

				// Now we are good to go - let's start activating modules.
				$activate = activate_modules( $modules_to_activate );

				if ( is_mcms_error( $activate ) ) {
					echo '<div id="message" class="error"><p>', mcms_kses_post( $activate->get_error_message() ), '</p></div>';
				} else {
					$count        = count( $module_names ); // Count so we can use _n function.
					$module_names = array_map( array( 'TGMPA_Utils', 'wrap_in_strong' ), $module_names );
					$last_module  = array_pop( $module_names ); // Pop off last name to prep for readability.
					$imploded     = empty( $module_names ) ? $last_module : ( implode( ', ', $module_names ) . ' ' . esc_html_x( 'and', 'module A *and* module B', 'jmd-worldcast' ) . ' ' . $last_module );

					printf( // MCMSCS: xss ok.
						'<div id="message" class="updated"><p>%1$s %2$s.</p></div>',
						esc_html( _n( 'The following module was activated successfully:', 'The following modules were activated successfully:', $count, 'jmd-worldcast' ) ),
						$imploded
					);

					// Update recently activated modules option.
					$recent = (array) get_option( 'recently_activated' );
					foreach ( $modules_to_activate as $module => $time ) {
						if ( isset( $recent[ $module ] ) ) {
							unset( $recent[ $module ] );
						}
					}
					update_option( 'recently_activated', $recent );
				}

				unset( $_POST ); // Reset the $_POST variable in case user wants to perform one action after another.

				return true;
			}

			return false;
		}

		/**
		 * Prepares all of our information to be outputted into a usable table.
		 *
		 * @since 2.2.0
		 */
		public function prepare_items() {
			$columns               = $this->get_columns(); // Get all necessary column information.
			$hidden                = array(); // No columns to hide, but we must set as an array.
			$sortable              = array(); // No reason to make sortable columns.
			$primary               = $this->get_primary_column_name(); // Column which has the row actions.
			$this->_column_headers = array( $columns, $hidden, $sortable, $primary ); // Get all necessary column headers.

			// Process our bulk activations here.
			if ( 'tgmpa-bulk-activate' === $this->current_action() ) {
				$this->process_bulk_actions();
			}

			// Store all of our module data into $items array so MCMS_List_Table can use it.
			$this->items = apply_filters( 'tgmpa_table_data_items', $this->_gather_module_data() );
		}

		/* *********** DEPRECATED METHODS *********** */

		/**
		 * Retrieve module data, given the module name.
		 *
		 * @since      2.2.0
		 * @deprecated 2.5.0 use {@see TGM_Module_Activation::_get_module_data_from_name()} instead.
		 * @see        TGM_Module_Activation::_get_module_data_from_name()
		 *
		 * @param string $name Name of the module, as it was registered.
		 * @param string $data Optional. Array key of module data to return. Default is slug.
		 * @return string|boolean Module slug if found, false otherwise.
		 */
		protected function _get_module_data_from_name( $name, $data = 'slug' ) {
			_deprecated_function( __FUNCTION__, 'TGMPA 2.5.0', 'TGM_Module_Activation::_get_module_data_from_name()' );

			return $this->tgmpa->_get_module_data_from_name( $name, $data );
		}
	}
}


if ( ! class_exists( 'TGM_Bulk_Installer' ) ) {

	/**
	 * Hack: Prevent TGMPA v2.4.1- bulk installer class from being loaded if 2.4.1- is loaded after 2.5+.
	 *
	 * @since 2.5.2
	 *
	 * {@internal The TGMPA_Bulk_Installer class was originally called TGM_Bulk_Installer.
	 *            For more information, see that class.}}
	 */
	class TGM_Bulk_Installer {
	}
}
if ( ! class_exists( 'TGM_Bulk_Installer_Skin' ) ) {

	/**
	 * Hack: Prevent TGMPA v2.4.1- bulk installer skin class from being loaded if 2.4.1- is loaded after 2.5+.
	 *
	 * @since 2.5.2
	 *
	 * {@internal The TGMPA_Bulk_Installer_Skin class was originally called TGM_Bulk_Installer_Skin.
	 *            For more information, see that class.}}
	 */
	class TGM_Bulk_Installer_Skin {
	}
}

/**
 * The MCMS_Upgrader file isn't always available. If it isn't available,
 * we load it here.
 *
 * We check to make sure no action or activation keys are set so that MandarinCMS
 * does not try to re-include the class when processing upgrades or installs outside
 * of the class.
 *
 * @since 2.2.0
 */
add_action( 'admin_init', 'tgmpa_load_bulk_installer' );
if ( ! function_exists( 'tgmpa_load_bulk_installer' ) ) {
	/**
	 * Load bulk installer
	 */
	function tgmpa_load_bulk_installer() {
		// Silently fail if 2.5+ is loaded *after* an older version.
		if ( ! isset( $GLOBALS['tgmpa'] ) ) {
			return;
		}

		// Get TGMPA class instance.
		$tgmpa_instance = call_user_func( array( get_class( $GLOBALS['tgmpa'] ), 'get_instance' ) );

		if ( isset( $_GET['page'] ) && $tgmpa_instance->menu === $_GET['page'] ) {
			if ( ! class_exists( 'Module_Upgrader', false ) ) {
				require_once BASED_TREE_URI . 'mcms-admin/includes/class-mcms-upgrader.php';
			}

			if ( ! class_exists( 'TGMPA_Bulk_Installer' ) ) {

				/**
				 * Installer class to handle bulk module installations.
				 *
				 * Extends MCMS_Upgrader and customizes to suit the installation of multiple
				 * modules.
				 *
				 * @since 2.2.0
				 *
				 * {@internal Since 2.5.0 the class is an extension of Module_Upgrader rather than MCMS_Upgrader.}}
				 * {@internal Since 2.5.2 the class has been renamed from TGM_Bulk_Installer to TGMPA_Bulk_Installer.
				 *            This was done to prevent backward compatibility issues with v2.3.6.}}
				 *
				 * @package TGM-Module-Activation
				 * @author  Jii Saaduddin
				 * @author  Gary Jones
				 */
				class TGMPA_Bulk_Installer extends Module_Upgrader {
					/**
					 * Holds result of bulk module installation.
					 *
					 * @since 2.2.0
					 *
					 * @var string
					 */
					public $result;

					/**
					 * Flag to check if bulk installation is occurring or not.
					 *
					 * @since 2.2.0
					 *
					 * @var boolean
					 */
					public $bulk = false;

					/**
					 * TGMPA instance
					 *
					 * @since 2.5.0
					 *
					 * @var object
					 */
					protected $tgmpa;

					/**
					 * Whether or not the destination directory needs to be cleared ( = on update).
					 *
					 * @since 2.5.0
					 *
					 * @var bool
					 */
					protected $clear_destination = false;

					/**
					 * References parent constructor and sets defaults for class.
					 *
					 * @since 2.2.0
					 *
					 * @param \Bulk_Upgrader_Skin|null $skin Installer skin.
					 */
					public function __construct( $skin = null ) {
						// Get TGMPA class instance.
						$this->tgmpa = call_user_func( array( get_class( $GLOBALS['tgmpa'] ), 'get_instance' ) );

						parent::__construct( $skin );

						if ( isset( $this->skin->options['install_type'] ) && 'update' === $this->skin->options['install_type'] ) {
							$this->clear_destination = true;
						}

						if ( $this->tgmpa->is_automatic ) {
							$this->activate_strings();
						}

						add_action( 'upgrader_process_complete', array( $this->tgmpa, 'populate_file_path' ) );
					}

					/**
					 * Sets the correct activation strings for the installer skin to use.
					 *
					 * @since 2.2.0
					 */
					public function activate_strings() {
						$this->strings['activation_failed']  = __( 'Module activation failed.', 'jmd-worldcast' );
						$this->strings['activation_success'] = __( 'Module activated successfully.', 'jmd-worldcast' );
					}

					/**
					 * Performs the actual installation of each module.
					 *
					 * @since 2.2.0
					 *
					 * @see MCMS_Upgrader::run()
					 *
					 * @param array $options The installation config options.
					 * @return null|array Return early if error, array of installation data on success.
					 */
					public function run( $options ) {
						$result = parent::run( $options );

						// Reset the strings in case we changed one during automatic activation.
						if ( $this->tgmpa->is_automatic ) {
							if ( 'update' === $this->skin->options['install_type'] ) {
								$this->upgrade_strings();
							} else {
								$this->install_strings();
							}
						}

						return $result;
					}

					/**
					 * Processes the bulk installation of modules.
					 *
					 * @since 2.2.0
					 *
					 * {@internal This is basically a near identical copy of the MCMS Core
					 * Module_Upgrader::bulk_upgrade() method, with minor adjustments to deal with
					 * new installs instead of upgrades.
					 * For ease of future synchronizations, the adjustments are clearly commented, but no other
					 * comments are added. Code style has been made to comply.}}
					 *
					 * @see Module_Upgrader::bulk_upgrade()
					 * @see https://core.trac.mandarincms.org/browser/tags/4.2.1/src/mcms-admin/includes/class-mcms-upgrader.php#L838
					 * (@internal Last synced: Dec 31st 2015 against https://core.trac.mandarincms.org/browser/trunk?rev=36134}}
					 *
					 * @param array $modules The module sources needed for installation.
					 * @param array $args    Arbitrary passed extra arguments.
					 * @return array|false   Install confirmation messages on success, false on failure.
					 */
					public function bulk_install( $modules, $args = array() ) {
						// [TGMPA + ] Hook auto-activation in.
						add_filter( 'upgrader_post_install', array( $this, 'auto_activate' ), 10 );

						$defaults    = array(
							'clear_update_cache' => true,
						);
						$parsed_args = mcms_parse_args( $args, $defaults );

						$this->init();
						$this->bulk = true;

						$this->install_strings(); // [TGMPA + ] adjusted.

						/* [TGMPA - ] $current = get_site_transient( 'update_modules' ); */

						/* [TGMPA - ] add_filter('upgrader_clear_destination', array($this, 'delete_old_module'), 10, 4); */

						$this->skin->header();

						// Connect to the Filesystem first.
						$res = $this->fs_connect( array( MCMS_CONTENT_DIR, MCMS_PLUGIN_DIR ) );
						if ( ! $res ) {
							$this->skin->footer();
							return false;
						}

						$this->skin->bulk_header();

						/*
						 * Only start maintenance mode if:
						 * - running Multisite and there are one or more modules specified, OR
						 * - a module with an update available is currently active.
						 * @TODO: For multisite, maintenance mode should only kick in for individual sites if at all possible.
						 */
						$maintenance = ( is_multisite() && ! empty( $modules ) );

						/*
						[TGMPA - ]
						foreach ( $modules as $module )
							$maintenance = $maintenance || ( is_module_active( $module ) && isset( $current->response[ $module] ) );
						*/
						if ( $maintenance ) {
							$this->maintenance_mode( true );
						}

						$results = array();

						$this->update_count   = count( $modules );
						$this->update_current = 0;
						foreach ( $modules as $module ) {
							$this->update_current++;

							/*
							[TGMPA - ]
							$this->skin->module_info = get_module_data( MCMS_PLUGIN_DIR . '/' . $module, false, true);

							if ( !isset( $current->response[ $module ] ) ) {
								$this->skin->set_result('up_to_date');
								$this->skin->before();
								$this->skin->feedback('up_to_date');
								$this->skin->after();
								$results[$module] = true;
								continue;
							}

							// Get the URL to the zip file.
							$r = $current->response[ $module ];

							$this->skin->module_active = is_module_active($module);
							*/

							$result = $this->run(
								array(
									'package'           => $module, // [TGMPA + ] adjusted.
									'destination'       => MCMS_PLUGIN_DIR,
									'clear_destination' => false, // [TGMPA + ] adjusted.
									'clear_working'     => true,
									'is_multi'          => true,
									'hook_extra'        => array(
										'module' => $module,
									),
								)
							);

							$results[ $module ] = $this->result;

							// Prevent credentials auth screen from displaying multiple times.
							if ( false === $result ) {
								break;
							}
						} //end foreach $modules

						$this->maintenance_mode( false );

						/**
						 * Fires when the bulk upgrader process is complete.
						 *
						 * @since MCMS 3.6.0 / TGMPA 2.5.0
						 *
						 * @param Module_Upgrader $this Module_Upgrader instance. In other contexts, $this, might
						 *                              be a Theme_Upgrader or Core_Upgrade instance.
						 * @param array           $data {
						 *     Array of bulk item update data.
						 *
						 *     @type string $action   Type of action. Default 'update'.
						 *     @type string $type     Type of update process. Accepts 'module', 'myskin', or 'core'.
						 *     @type bool   $bulk     Whether the update process is a bulk update. Default true.
						 *     @type array  $packages Array of module, myskin, or core packages to update.
						 * }
						 */
						do_action( 'upgrader_process_complete', $this, array(
							'action'  => 'install', // [TGMPA + ] adjusted.
							'type'    => 'module',
							'bulk'    => true,
							'modules' => $modules,
						) );

						$this->skin->bulk_footer();

						$this->skin->footer();

						// Cleanup our hooks, in case something else does a upgrade on this connection.
						/* [TGMPA - ] remove_filter('upgrader_clear_destination', array($this, 'delete_old_module')); */

						// [TGMPA + ] Remove our auto-activation hook.
						remove_filter( 'upgrader_post_install', array( $this, 'auto_activate' ), 10 );

						// Force refresh of module update information.
						mcms_clean_modules_cache( $parsed_args['clear_update_cache'] );

						return $results;
					}

					/**
					 * Handle a bulk upgrade request.
					 *
					 * @since 2.5.0
					 *
					 * @see Module_Upgrader::bulk_upgrade()
					 *
					 * @param array $modules The local MCMS file_path's of the modules which should be upgraded.
					 * @param array $args    Arbitrary passed extra arguments.
					 * @return string|bool Install confirmation messages on success, false on failure.
					 */
					public function bulk_upgrade( $modules, $args = array() ) {

						add_filter( 'upgrader_post_install', array( $this, 'auto_activate' ), 10 );

						$result = parent::bulk_upgrade( $modules, $args );

						remove_filter( 'upgrader_post_install', array( $this, 'auto_activate' ), 10 );

						return $result;
					}

					/**
					 * Abuse a filter to auto-activate modules after installation.
					 *
					 * Hooked into the 'upgrader_post_install' filter hook.
					 *
					 * @since 2.5.0
					 *
					 * @param bool $bool The value we need to give back (true).
					 * @return bool
					 */
					public function auto_activate( $bool ) {
						// Only process the activation of installed modules if the automatic flag is set to true.
						if ( $this->tgmpa->is_automatic ) {
							// Flush modules cache so the headers of the newly installed modules will be read correctly.
							mcms_clean_modules_cache();

							// Get the installed module file.
							$module_info = $this->module_info();

							// Don't try to activate on upgrade of active module as MCMS will do this already.
							if ( ! is_module_active( $module_info ) ) {
								$activate = activate_module( $module_info );

								// Adjust the success string based on the activation result.
								$this->strings['process_success'] = $this->strings['process_success'] . "<br />\n";

								if ( is_mcms_error( $activate ) ) {
									$this->skin->error( $activate );
									$this->strings['process_success'] .= $this->strings['activation_failed'];
								} else {
									$this->strings['process_success'] .= $this->strings['activation_success'];
								}
							}
						}

						return $bool;
					}
				}
			}

			if ( ! class_exists( 'TGMPA_Bulk_Installer_Skin' ) ) {

				/**
				 * Installer skin to set strings for the bulk module installations..
				 *
				 * Extends Bulk_Upgrader_Skin and customizes to suit the installation of multiple
				 * modules.
				 *
				 * @since 2.2.0
				 *
				 * {@internal Since 2.5.2 the class has been renamed from TGM_Bulk_Installer_Skin to
				 *            TGMPA_Bulk_Installer_Skin.
				 *            This was done to prevent backward compatibility issues with v2.3.6.}}
				 *
				 * @see https://core.trac.mandarincms.org/browser/trunk/src/mcms-admin/includes/class-mcms-upgrader-skins.php
				 *
				 * @package TGM-Module-Activation
				 * @author  Jii Saaduddin
				 * @author  Gary Jones
				 */
				class TGMPA_Bulk_Installer_Skin extends Bulk_Upgrader_Skin {
					/**
					 * Holds module info for each individual module installation.
					 *
					 * @since 2.2.0
					 *
					 * @var array
					 */
					public $module_info = array();

					/**
					 * Holds names of modules that are undergoing bulk installations.
					 *
					 * @since 2.2.0
					 *
					 * @var array
					 */
					public $module_names = array();

					/**
					 * Integer to use for iteration through each module installation.
					 *
					 * @since 2.2.0
					 *
					 * @var integer
					 */
					public $i = 0;

					/**
					 * TGMPA instance
					 *
					 * @since 2.5.0
					 *
					 * @var object
					 */
					protected $tgmpa;

					/**
					 * Constructor. Parses default args with new ones and extracts them for use.
					 *
					 * @since 2.2.0
					 *
					 * @param array $args Arguments to pass for use within the class.
					 */
					public function __construct( $args = array() ) {
						// Get TGMPA class instance.
						$this->tgmpa = call_user_func( array( get_class( $GLOBALS['tgmpa'] ), 'get_instance' ) );

						// Parse default and new args.
						$defaults = array(
							'url'          => '',
							'nonce'        => '',
							'names'        => array(),
							'install_type' => 'install',
						);
						$args     = mcms_parse_args( $args, $defaults );

						// Set module names to $this->module_names property.
						$this->module_names = $args['names'];

						// Extract the new args.
						parent::__construct( $args );
					}

					/**
					 * Sets install skin strings for each individual module.
					 *
					 * Checks to see if the automatic activation flag is set and uses the
					 * the proper strings accordingly.
					 *
					 * @since 2.2.0
					 */
					public function add_strings() {
						if ( 'update' === $this->options['install_type'] ) {
							parent::add_strings();
							/* translators: 1: module name, 2: action number 3: total number of actions. */
							$this->upgrader->strings['skin_before_update_header'] = __( 'Updating Module %1$s (%2$d/%3$d)', 'jmd-worldcast' );
						} else {
							/* translators: 1: module name, 2: error message. */
							$this->upgrader->strings['skin_update_failed_error'] = __( 'An error occurred while installing %1$s: <strong>%2$s</strong>.', 'jmd-worldcast' );
							/* translators: 1: module name. */
							$this->upgrader->strings['skin_update_failed'] = __( 'The installation of %1$s failed.', 'jmd-worldcast' );

							if ( $this->tgmpa->is_automatic ) {
								// Automatic activation strings.
								$this->upgrader->strings['skin_upgrade_start'] = __( 'The installation and activation process is starting. This process may take a while on some hosts, so please be patient.', 'jmd-worldcast' );
								/* translators: 1: module name. */
								$this->upgrader->strings['skin_update_successful'] = __( '%1$s installed and activated successfully.', 'jmd-worldcast' ) . ' <a href="#" class="hide-if-no-js" onclick="%2$s"><span>' . esc_html__( 'Show Details', 'jmd-worldcast' ) . '</span><span class="hidden">' . esc_html__( 'Hide Details', 'jmd-worldcast' ) . '</span>.</a>';
								$this->upgrader->strings['skin_upgrade_end']       = __( 'All installations and activations have been completed.', 'jmd-worldcast' );
								/* translators: 1: module name, 2: action number 3: total number of actions. */
								$this->upgrader->strings['skin_before_update_header'] = __( 'Installing and Activating Module %1$s (%2$d/%3$d)', 'jmd-worldcast' );
							} else {
								// Default installation strings.
								$this->upgrader->strings['skin_upgrade_start'] = __( 'The installation process is starting. This process may take a while on some hosts, so please be patient.', 'jmd-worldcast' );
								/* translators: 1: module name. */
								$this->upgrader->strings['skin_update_successful'] = esc_html__( '%1$s installed successfully.', 'jmd-worldcast' ) . ' <a href="#" class="hide-if-no-js" onclick="%2$s"><span>' . esc_html__( 'Show Details', 'jmd-worldcast' ) . '</span><span class="hidden">' . esc_html__( 'Hide Details', 'jmd-worldcast' ) . '</span>.</a>';
								$this->upgrader->strings['skin_upgrade_end']       = __( 'All installations have been completed.', 'jmd-worldcast' );
								/* translators: 1: module name, 2: action number 3: total number of actions. */
								$this->upgrader->strings['skin_before_update_header'] = __( 'Installing Module %1$s (%2$d/%3$d)', 'jmd-worldcast' );
							}
						}
					}

					/**
					 * Outputs the header strings and necessary JS before each module installation.
					 *
					 * @since 2.2.0
					 *
					 * @param string $title Unused in this implementation.
					 */
					public function before( $title = '' ) {
						if ( empty( $title ) ) {
							$title = esc_html( $this->module_names[ $this->i ] );
						}
						parent::before( $title );
					}

					/**
					 * Outputs the footer strings and necessary JS after each module installation.
					 *
					 * Checks for any errors and outputs them if they exist, else output
					 * success strings.
					 *
					 * @since 2.2.0
					 *
					 * @param string $title Unused in this implementation.
					 */
					public function after( $title = '' ) {
						if ( empty( $title ) ) {
							$title = esc_html( $this->module_names[ $this->i ] );
						}
						parent::after( $title );

						$this->i++;
					}

					/**
					 * Outputs links after bulk module installation is complete.
					 *
					 * @since 2.2.0
					 */
					public function bulk_footer() {
						// Serve up the string to say installations (and possibly activations) are complete.
						parent::bulk_footer();

						// Flush modules cache so we can make sure that the installed modules list is always up to date.
						mcms_clean_modules_cache();

						$this->tgmpa->show_tgmpa_version();

						// Display message based on if all modules are now active or not.
						$update_actions = array();

						if ( $this->tgmpa->is_tgmpa_complete() ) {
							// All modules are active, so we display the complete string and hide the menu to protect users.
							echo '<style type="text/css">#adminmenu .mcms-submenu li.current { display: none !important; }</style>';
							$update_actions['dashboard'] = sprintf(
								esc_html( $this->tgmpa->strings['complete'] ),
								'<a href="' . esc_url( self_admin_url() ) . '">' . esc_html__( 'Return to the Dashboard', 'jmd-worldcast' ) . '</a>'
							);
						} else {
							$update_actions['tgmpa_page'] = '<a href="' . esc_url( $this->tgmpa->get_tgmpa_url() ) . '" target="_parent">' . esc_html( $this->tgmpa->strings['return'] ) . '</a>';
						}

						/**
						 * Filter the list of action links available following bulk module installs/updates.
						 *
						 * @since 2.5.0
						 *
						 * @param array $update_actions Array of module action links.
						 * @param array $module_info    Array of information for the last-handled module.
						 */
						$update_actions = apply_filters( 'tgmpa_update_bulk_modules_complete_actions', $update_actions, $this->module_info );

						if ( ! empty( $update_actions ) ) {
							$this->feedback( implode( ' | ', (array) $update_actions ) );
						}
					}

					/* *********** DEPRECATED METHODS *********** */

					/**
					 * Flush header output buffer.
					 *
					 * @since      2.2.0
					 * @deprecated 2.5.0 use {@see Bulk_Upgrader_Skin::flush_output()} instead
					 * @see        Bulk_Upgrader_Skin::flush_output()
					 */
					public function before_flush_output() {
						_deprecated_function( __FUNCTION__, 'TGMPA 2.5.0', 'Bulk_Upgrader_Skin::flush_output()' );
						$this->flush_output();
					}

					/**
					 * Flush footer output buffer and iterate $this->i to make sure the
					 * installation strings reference the correct module.
					 *
					 * @since      2.2.0
					 * @deprecated 2.5.0 use {@see Bulk_Upgrader_Skin::flush_output()} instead
					 * @see        Bulk_Upgrader_Skin::flush_output()
					 */
					public function after_flush_output() {
						_deprecated_function( __FUNCTION__, 'TGMPA 2.5.0', 'Bulk_Upgrader_Skin::flush_output()' );
						$this->flush_output();
						$this->i++;
					}
				}
			}
		}
	}
}

if ( ! class_exists( 'TGMPA_Utils' ) ) {

	/**
	 * Generic utilities for TGMPA.
	 *
	 * All methods are static, poor-dev name-spacing class wrapper.
	 *
	 * Class was called TGM_Utils in 2.5.0 but renamed TGMPA_Utils in 2.5.1 as this was conflicting with Soliloquy.
	 *
	 * @since 2.5.0
	 *
	 * @package TGM-Module-Activation
	 * @author  Juliette Reinders Folmer
	 */
	class TGMPA_Utils {
		/**
		 * Whether the PHP filter extension is enabled.
		 *
		 * @see http://php.net/book.filter
		 *
		 * @since 2.5.0
		 *
		 * @static
		 *
		 * @var bool $has_filters True is the extension is enabled.
		 */
		public static $has_filters;

		/**
		 * Wrap an arbitrary string in <em> tags. Meant to be used in combination with array_map().
		 *
		 * @since 2.5.0
		 *
		 * @static
		 *
		 * @param string $string Text to be wrapped.
		 * @return string
		 */
		public static function wrap_in_em( $string ) {
			return '<em>' . mcms_kses_post( $string ) . '</em>';
		}

		/**
		 * Wrap an arbitrary string in <strong> tags. Meant to be used in combination with array_map().
		 *
		 * @since 2.5.0
		 *
		 * @static
		 *
		 * @param string $string Text to be wrapped.
		 * @return string
		 */
		public static function wrap_in_strong( $string ) {
			return '<strong>' . mcms_kses_post( $string ) . '</strong>';
		}

		/**
		 * Helper function: Validate a value as boolean
		 *
		 * @since 2.5.0
		 *
		 * @static
		 *
		 * @param mixed $value Arbitrary value.
		 * @return bool
		 */
		public static function validate_bool( $value ) {
			if ( ! isset( self::$has_filters ) ) {
				self::$has_filters = extension_loaded( 'filter' );
			}

			if ( self::$has_filters ) {
				return filter_var( $value, FILTER_VALIDATE_BOOLEAN );
			} else {
				return self::emulate_filter_bool( $value );
			}
		}

		/**
		 * Helper function: Cast a value to bool
		 *
		 * @since 2.5.0
		 *
		 * @static
		 *
		 * @param mixed $value Value to cast.
		 * @return bool
		 */
		protected static function emulate_filter_bool( $value ) {
			// @codingStandardsIgnoreStart
			static $true  = array(
				'1',
				'true', 'True', 'TRUE',
				'y', 'Y',
				'yes', 'Yes', 'YES',
				'on', 'On', 'ON',
			);
			static $false = array(
				'0',
				'false', 'False', 'FALSE',
				'n', 'N',
				'no', 'No', 'NO',
				'off', 'Off', 'OFF',
			);
			// @codingStandardsIgnoreEnd

			if ( is_bool( $value ) ) {
				return $value;
			} elseif ( is_int( $value ) && ( 0 === $value || 1 === $value ) ) {
				return (bool) $value;
			} elseif ( ( is_float( $value ) && ! is_nan( $value ) ) && ( (float) 0 === $value || (float) 1 === $value ) ) {
				return (bool) $value;
			} elseif ( is_string( $value ) ) {
				$value = trim( $value );
				if ( in_array( $value, $true, true ) ) {
					return true;
				} elseif ( in_array( $value, $false, true ) ) {
					return false;
				} else {
					return false;
				}
			}

			return false;
		}
	} // End of class TGMPA_Utils
} // End of class_exists wrapper
