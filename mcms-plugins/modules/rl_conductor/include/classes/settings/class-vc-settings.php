<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

/**
 * Settings page for VC. list of tabs for function composer
 *
 * Settings page for VC creates menu item in admin menu as subpage of Settings section.
 * Settings are build with MCMS settings API and organized as tabs.
 *
 * List of tabs
 * 1. General Settings - set access rules and allowed content types for editors.
 * 2. Design Options - custom color and spacing editor for VC shortcodes elements.
 * 3. Custom CSS - add custom css to your MCMS pages.
 * 4. Product License - license key activation for automatic VC updates.
 * 5. My Shortcodes - automated mapping tool for shortcodes.
 *
 * @link http://codex.mandarincms.com/Settings_API Wordpress settings API
 * @since 3.4
 */
class Vc_Settings {
	public $tabs;
	public $deactivate;
	public $locale;
	/**
	 * @var string
	 */
	protected $option_group = 'mcmsb_rl_conductor_settings';
	/**
	 * @var string
	 */
	protected $page = 'vc_settings';
	/**
	 * @var string
	 */
	protected static $field_prefix = 'mcmsb_js_';
	/**
	 * @var string
	 */
	protected static $notification_name = 'mcmsb_js_notify_user_about_element_class_names';
	/**
	 * @var
	 */
	protected static $color_settings;
	/**
	 * @var
	 */
	protected static $defaults;
	/**
	 * @var
	 */
	protected $composer;

	/**
	 * @var array
	 */
	protected $google_fonts_subsets_default = array( 'latin' );
	/**
	 * @var array
	 */
	protected $google_fonts_subsets = array(
		'latin',
		'vietnamese',
		'cyrillic',
		'latin-ext',
		'greek',
		'cyrillic-ext',
		'greek-ext',
	);

	/**
	 * @var array
	 */
	public $google_fonts_subsets_excluded = array();

	/**
	 * @param string $field_prefix
	 */
	public static function setFieldPrefix( $field_prefix ) {
		self::$field_prefix = $field_prefix;
	}

	/**
	 * @return string
	 */
	public function page() {
		return $this->page;
	}

	/**
	 * @return bool
	 */
	public function isEditorEnabled() {
		global $current_user;
		mcms_get_current_user();

		/** @var $settings - get use group access rules */
		$settings = $this->get( 'groups_access_rules' );

		$show = true;
		foreach ( $current_user->roles as $role ) {
			if ( isset( $settings[ $role ]['show'] ) && 'no' === $settings[ $role ]['show'] ) {
				$show = false;
				break;
			}
		}

		return $show;
	}

	/**
	 *
	 */
	public function setTabs() {
		$this->tabs = array();

		if ( $this->showConfigurationTabs() ) {
			$this->tabs['vc-general'] = __( 'General Settings', 'rl_conductor' );
			if ( vc_is_as_myskin() || apply_filters( 'vc_settings_page_show_design_tabs', false ) ) {
				$this->tabs['vc-color'] = __( 'Design Options', 'rl_conductor' );
				$this->tabs['vc-custom_css'] = __( 'Custom CSS', 'rl_conductor' );
			}
		}
 
		// TODO: may allow to disable automapper
		if ( ! is_network_admin() && ! vc_automapper_is_disabled() ) {
			$this->tabs['vc-automapper'] = vc_automapper()->title();
		}
	}

	public function getTabs() {
		if ( ! isset( $this->tabs ) ) {
			$this->setTabs();
		}

		return apply_filters( 'vc_settings_tabs', $this->tabs );
	}

	/**
	 * @return bool
	 */
	public function showConfigurationTabs() {
		return ! vc_is_network_module() || ! is_network_admin();
	}

	/**
	 * Render
	 *
	 * @param $tab
	 */
	public function renderTab( $tab ) {
		require_once vc_path_dir( 'CORE_DIR', 'class-vc-page.php' );
		mcms_enqueue_style( 'mcms-color-picker' );
		mcms_enqueue_script( 'mcms-color-picker' );
		if ( ( isset( $_GET['build_css'] ) && ( '1' === $_GET['build_css'] || 'true' === $_GET['build_css'] ) ) || ( isset( $_GET['settings-updated'] ) && ( '1' === $_GET['settings-updated'] || 'true' === $_GET['settings-updated'] ) ) ) {
			$this->buildCustomCss(); // TODO: remove this - no needs to re-save always
		}
		$tabs = $this->getTabs();
		foreach ( $tabs as $key => $value ) {
			if ( ! vc_user_access()->part( 'settings' )->can( $key . '-tab' )->get() ) {
				unset( $tabs[ $key ] );
			}
		}
		do_action( 'vc-settings-render-tab-' . $tab );
		$page = new Vc_Page();
		$page->setSlug( $tab )->setTitle( isset( $tabs[ $tab ] ) ? $tabs[ $tab ] : '' )->setTemplatePath( apply_filters( 'vc_settings-render-tab-' . $tab, 'pages/vc-settings/tab.php' ) );
		vc_include_template( 'pages/vc-settings/index.php', array(
			'pages' => $tabs,
			'active_page' => $page,
			'vc_settings' => $this,
		) );
	}

	/**
	 * Init settings page && menu item
	 * vc_filter: vc_settings_tabs - hook to override settings tabs
	 */
	public function initAdmin() {
		$this->setTabs();

		self::$color_settings = array(
			array( 'vc_color' => array( 'title' => __( 'Main accent color', 'rl_conductor' ) ) ),
			array( 'vc_color_hover' => array( 'title' => __( 'Hover color', 'rl_conductor' ) ) ),
			array( 'vc_color_call_to_action_bg' => array( 'title' => __( 'Call to action background color', 'rl_conductor' ) ) ),
			array( 'vc_color_google_maps_bg' => array( 'title' => __( 'Google maps background color', 'rl_conductor' ) ) ),
			array( 'vc_color_post_slider_caption_bg' => array( 'title' => __( 'Post slider caption background color', 'rl_conductor' ) ) ),
			array( 'vc_color_progress_bar_bg' => array( 'title' => __( 'Progress bar background color', 'rl_conductor' ) ) ),
			array( 'vc_color_separator_border' => array( 'title' => __( 'Separator border color', 'rl_conductor' ) ) ),
			array( 'vc_color_tab_bg' => array( 'title' => __( 'Tabs navigation background color', 'rl_conductor' ) ) ),
			array( 'vc_color_tab_bg_active' => array( 'title' => __( 'Active tab background color', 'rl_conductor' ) ) ),
		);
		self::$defaults = array(
			'vc_color' => '#f7f7f7',
			'vc_color_hover' => '#F0F0F0',
			'margin' => '35px',
			'gutter' => '15',
			'responsive_max' => '768',
			'compiled_rl_conductor_less' => '',
		);
		if ( 'restore_color' === vc_post_param( 'vc_action' ) && vc_user_access()
				->check( 'mcms_verify_nonce', vc_post_param( '_mcmsnonce' ), vc_settings()->getOptionGroup() . '_color' . '-options' )// see settings_fields() function
				->validateDie()->mcmsAny( 'manage_options' )->validateDie()->part( 'settings' )->can( 'vc-color-tab' )->validateDie()->get()
		) {
			$this->restoreColor();
		}

		/**
		 * @since 4.5 used to call update file once option is changed
		 */
		add_action( 'update_option_mcmsb_js_compiled_rl_conductor_less', array(
			$this,
			'buildCustomColorCss',
		) );

		/**
		 * @since 4.5 used to call update file once option is changed
		 */
		add_action( 'update_option_mcmsb_js_custom_css', array(
			$this,
			'buildCustomCss',
		) );

		/**
		 * @since 4.5 used to call update file once option is changed
		 */
		add_action( 'add_option_mcmsb_js_compiled_rl_conductor_less', array(
			$this,
			'buildCustomColorCss',
		) );

		/**
		 * @since 4.5 used to call update file once option is changed
		 */
		add_action( 'add_option_mcmsb_js_custom_css', array(
			$this,
			'buildCustomCss',
		) );

		/**
		 * Tab: General Settings
		 */
		$tab = 'general';
		$this->addSection( $tab );

		$this->addField( $tab, __( 'Disable responsive content elements', 'rl_conductor' ), 'not_responsive_css', array(
			$this,
			'sanitize_not_responsive_css_callback',
		), array(
			$this,
			'not_responsive_css_field_callback',
		) );

		$this->addField( $tab, __( 'Google fonts subsets', 'rl_conductor' ), 'google_fonts_subsets', array(
			$this,
			'sanitize_google_fonts_subsets_callback',
		), array(
			$this,
			'google_fonts_subsets_callback',
		) );

		/**
		 * Tab: Design Options
		 */
		$tab = 'color';
		$this->addSection( $tab );

		// Use custom checkbox
		$this->addField( $tab, __( 'Use custom design options', 'rl_conductor' ), 'use_custom', array(
			$this,
			'sanitize_use_custom_callback',
		), array(
			$this,
			'use_custom_callback',
		) );

		foreach ( self::$color_settings as $color_set ) {
			foreach ( $color_set as $key => $data ) {
				$this->addField( $tab, $data['title'], $key, array(
					$this,
					'sanitize_color_callback',
				), array(
					$this,
					'color_callback',
				), array(
					'id' => $key,
				) );
			}
		}

		// Margin
		$this->addField( $tab, __( 'Elements bottom margin', 'rl_conductor' ), 'margin', array(
			$this,
			'sanitize_margin_callback',
		), array(
			$this,
			'margin_callback',
		) );

		// Gutter
		$this->addField( $tab, __( 'Grid gutter width', 'rl_conductor' ), 'gutter', array(
			$this,
			'sanitize_gutter_callback',
		), array(
			$this,
			'gutter_callback',
		) );

		// Responsive max width
		$this->addField( $tab, __( 'Mobile screen width', 'rl_conductor' ), 'responsive_max', array(
			$this,
			'sanitize_responsive_max_callback',
		), array(
			$this,
			'responsive_max_callback',
		) );
		$this->addField( $tab, false, 'compiled_rl_conductor_less', array(
			$this,
			'sanitize_compiled_rl_conductor_less_callback',
		), array(
			$this,
			'compiled_rl_conductor_less_callback',
		) );

		/**
		 * Tab: Custom CSS
		 */
		$tab = 'custom_css';
		$this->addSection( $tab );
		$this->addField( $tab, __( 'Paste your CSS code', 'rl_conductor' ), 'custom_css', array(
			$this,
			'sanitize_custom_css_callback',
		), array(
			$this,
			'custom_css_field_callback',
		) );

		/**
		 * Custom Tabs
		 */
		foreach ( $this->getTabs() as $tab => $title ) {
			do_action( 'vc_settings_tab-' . preg_replace( '/^vc\-/', '', $tab ), $this );
		}

		/**
		 * Tab: Updater
		 */
		$tab = 'updater';
		$this->addSection( $tab );
	}

	/**
	 * Creates new section.
	 *
	 * @param $tab - tab key name as tab section
	 * @param $title - Human title
	 * @param $callback - function to build section header.
	 */
	public function addSection( $tab, $title = null, $callback = null ) {
		add_settings_section( $this->option_group . '_' . $tab, $title, ( null !== $callback ? $callback : array(
			$this,
			'setting_section_callback_function',
		) ), $this->page . '_' . $tab );
	}

	/**
	 * Create field in section.
	 *
	 * @param $tab
	 * @param $title
	 * @param $field_name
	 * @param $sanitize_callback
	 * @param $field_callback
	 * @param array $args
	 *
	 * @return $this
	 */
	public function addField( $tab, $title, $field_name, $sanitize_callback, $field_callback, $args = array() ) {
		register_setting( $this->option_group . '_' . $tab, self::$field_prefix . $field_name, $sanitize_callback );
		add_settings_field( self::$field_prefix . $field_name, $title, $field_callback, $this->page . '_' . $tab, $this->option_group . '_' . $tab, $args );

		return $this; // chaining
	}

	/**
	 *
	 */
	public function restoreColor() {
		foreach ( self::$color_settings as $color_sett ) {
			foreach ( $color_sett as $key => $value ) {
				delete_option( self::$field_prefix . $key );
			}
		}
		delete_option( self::$field_prefix . 'margin' );
		delete_option( self::$field_prefix . 'gutter' );
		delete_option( self::$field_prefix . 'responsive_max' );
		delete_option( self::$field_prefix . 'use_custom' );
		delete_option( self::$field_prefix . 'compiled_rl_conductor_less' );
		delete_option( self::$field_prefix . 'less_version' );
	}

	/**
	 * @deprecated since 4.4
	 */
	public function removeAllCssClasses() {
		_deprecated_function( '\Vc_Settings::removeAllCssClasses', '4.4 (will be removed in 5.1)' );
		delete_option( self::$field_prefix . 'row_css_class' );
		delete_option( self::$field_prefix . 'column_css_classes' );
	}

	/**
	 * @param $option_name
	 *
	 * @param bool $defaultValue
	 *
	 * @return mixed|void
	 */
	public static function get( $option_name, $defaultValue = false ) {
		return get_option( self::$field_prefix . $option_name, $defaultValue );
	}

	/**
	 * @param $option_name
	 * @param $value
	 *
	 * @return bool
	 */
	public static function set( $option_name, $value ) {
		return update_option( self::$field_prefix . $option_name, $value );
	}

	/**
	 * Set up the enqueue for the CSS & JavaScript files.
	 *
	 */

	function adminLoad() {
		mcms_register_script( 'mcmsb_rl_conductor_settings', vc_asset_url( 'js/dist/settings.min.js' ), array(), MCMSB_VC_VERSION, true );
		mcms_enqueue_style( 'rl_conductor_settings', vc_asset_url( 'css/rl_conductor_settings.min.css' ), false, MCMSB_VC_VERSION );
		mcms_enqueue_script( 'backbone' );
		mcms_enqueue_script( 'shortcode' );
		mcms_enqueue_script( 'underscore' );
		mcms_enqueue_script( 'jquery-ui-accordion' );
		mcms_enqueue_script( 'jquery-ui-sortable' );
		mcms_enqueue_script( 'mcmsb_rl_conductor_settings' );
		$this->locale = array(
			'are_you_sure_reset_css_classes' => __( 'Are you sure you want to reset to defaults?', 'rl_conductor' ),
			'are_you_sure_reset_color' => __( 'Are you sure you want to reset to defaults?', 'rl_conductor' ),
			'saving' => __( 'Saving...', 'rl_conductor' ),
			'save' => __( 'Save Changes', 'rl_conductor' ),
			'saved' => __( 'Design Options successfully saved.', 'rl_conductor' ),
			'save_error' => __( 'Design Options could not be saved', 'rl_conductor' ),
			'form_save_error' => __( 'Problem with AJAX request execution, check internet connection and try again.', 'rl_conductor' ),
			'are_you_sure_delete' => __( 'Are you sure you want to delete this shortcode?', 'rl_conductor' ),
			'are_you_sure_delete_param' => __( "Are you sure you want to delete the shortcode's param?", 'rl_conductor' ),
			'my_shortcodes_category' => __( 'My shortcodes', 'rl_conductor' ),
			'error_shortcode_name_is_required' => __( 'Shortcode name is required.', 'rl_conductor' ),
			'error_enter_valid_shortcode_tag' => __( 'Please enter valid shortcode tag.', 'rl_conductor' ),
			'error_enter_required_fields' => __( 'Please enter all required fields for params.', 'rl_conductor' ),
			'new_shortcode_mapped' => __( 'New shortcode mapped from string!', 'rl_conductor' ),
			'shortcode_updated' => __( 'Shortcode updated!', 'rl_conductor' ),
			'error_content_param_not_manually' => __( 'Content param can not be added manually, please use checkbox.', 'rl_conductor' ),
			'error_param_already_exists' => __( 'Param %s already exists. Param names must be unique.', 'rl_conductor' ),
			'error_wrong_param_name' => __( 'Please use only letters, numbers and underscore for param name', 'rl_conductor' ),
			'error_enter_valid_shortcode' => __( 'Please enter valid shortcode to parse!', 'rl_conductor' ),

		);
		mcms_localize_script( 'mcmsb_rl_conductor_settings', 'vcData', apply_filters( 'vc_global_js_data', array(
			'version' => MCMSB_VC_VERSION,
			'debug' => mcmsb_debug(),
		) ) );
		mcms_localize_script( 'mcmsb_rl_conductor_settings', 'i18nLocaleSettings', $this->locale );
	}

	/**
	 * Access groups
	 * @deprecated 4.8
	 */
	public function groups_access_rules_callback() {
		_deprecated_function( '\Vc_Settings::groups_access_rules_callback', '4.8 (will be removed in 5.1)' );
		global $mcms_roles;
		$groups = is_object( $mcms_roles ) ? $mcms_roles->roles : array();

		$settings = ( $settings = get_option( self::$field_prefix . 'groups_access_rules' ) ) ? $settings : array();
		$show_types = array(
			'all' => __( 'Show RazorLeaf Conductor & default editor', 'rl_conductor' ),
			'only' => __( 'Show only RazorLeaf Conductor', 'rl_conductor' ),
			'no' => __( "Don't allow to use RazorLeaf Conductor", 'rl_conductor' ),
		);
		$shortcodes = MCMSBMap::getShortCodes();
		$size_line = ceil( count( array_keys( $shortcodes ) ) / 3 );
		?>
		<div class="mcmsb_settings_accordion" id="mcmsb_js_settings_access_groups" xmlns="http://www.w3.org/1999/html">
		<?php
		if ( is_array( $groups ) ) :
			foreach ( $groups as $key => $params ) :
				if ( ( isset( $params['capabilities']['edit_posts'] ) && true === $params['capabilities']['edit_posts'] ) || ( isset( $params['capabilities']['edit_pages'] ) && true === $params['capabilities']['edit_pages'] ) ) :
					$allowed_setting = isset( $settings[ $key ]['show'] ) ? $settings[ $key ]['show'] : 'all';
					$shortcode_settings = isset( $settings[ $key ]['shortcodes'] ) ? $settings[ $key ]['shortcodes'] : array();
					?>
					<h3 id="mcmsb-settings-group-<?php echo $key ?>-header">
						<a href="#mcmsb-settings-group-<?php echo $key ?>">
							<?php echo $params['name'] ?>
						</a>
					</h3>
					<div id="mcmsb-settings-group-<?php echo $key ?>" class="accordion-body">
						<div class="visibility settings-block">
							<label
								for="mcmsb_composer_access_<?php echo $key ?>"><b><?php _e( 'RazorLeaf Conductor access', 'rl_conductor' ) ?></b></label>
							<select id="mcmsb_composer_access_<?php echo $key ?>"
								name="<?php echo self::$field_prefix . 'groups_access_rules[' . $key . '][show]' ?>">
								<?php foreach ( $show_types as $i_key => $name ) : ?>
									<option
										value="<?php echo $i_key ?>"<?php echo $allowed_setting == $i_key ? ' selected="true"' : '' ?>><?php echo $name ?></option>
								<?php endforeach ?>
							</select>
						</div>
						<div class="shortcodes settings-block">
							<div class="title"><b><?php _e( 'Enabled shortcodes', 'rl_conductor' ); ?></b></div>
							<?php $z = 1;
							foreach ( $shortcodes as $sc_base => $el ) : ?>
								<?php if ( ! in_array( $el['base'], array(
									'vc_column',
									'vc_row',
									'vc_row_inner',
									'vc_column_inner',
								) )
								) : ?>
									<?php if ( 1 === $z ) : ?><div class="pull-left"><?php endif ?>
									<label>
										<input
											type="checkbox"
											<?php if ( isset( $shortcode_settings[ $sc_base ] ) && 1 === (int) $shortcode_settings[ $sc_base ] ) : ?>checked="true"
											<?php endif ?>name="<?php echo self::$field_prefix . 'groups_access_rules[' . $key . '][shortcodes][' . $sc_base . ']' ?>"
											value="1"/>
										<?php
										echo $el['name'];
										if ( isset( $el['deprecated'] ) && false !== $el['deprecated'] ) {
											echo ' <i>' . sprintf( __( '(deprecated since v%s)', 'rl_conductor' ), $el['deprecated'] ) . '</i>';
										}
										?>
									</label>
									<?php if ( $z == $size_line ) : ?></div><?php $z = 0; endif;
									$z += 1; ?>
								<?php endif ?>
							<?php endforeach ?>
							<?php if ( 1 !== $z ) : ?></div><?php endif ?>
						<div class="vc_clearfix"></div>
						<div class="select-all">
							<a href="#"
								class="mcmsb-settings-select-all-shortcodes"><?php echo __( 'Select All', 'rl_conductor' ) ?></a>
							| <a href="#"
								class="mcmsb-settings-select-none-shortcodes"><?php echo __( 'Select none', 'rl_conductor' ) ?></a>
						</div>
					</div>
					</div>
					<?php
				endif;
			endforeach;
		endif;
		?>
		</div>
		<p class="description"><?php _e( 'Define access rules for different user groups.', 'rl_conductor' ); ?></p>
		<?php
	}

	/**
	 * Content types checkboxes list callback function
	 * @deprecated 4.8
	 */
	public function content_types_field_callback() {
		_deprecated_function( '\Vc_Settings::content_types_field_callback', '4.8 (will be removed in 5.1)' );
		$pt_array = ( $pt_array = get_option( 'mcmsb_js_content_types' ) ) ? ( $pt_array ) : vc_default_editor_post_types();
		foreach ( $this->getPostTypes() as $pt ) {
			if ( ! in_array( $pt, $this->getExcluded() ) ) {
				$checked = ( in_array( $pt, $pt_array ) ) ? ' checked' : '';
				?>
				<label>
					<input type="checkbox"<?php echo $checked; ?> value="<?php echo $pt; ?>"
						id="mcmsb_js_post_types_<?php echo $pt; ?>"
						name="<?php echo self::$field_prefix . 'content_types' ?>[]">
					<?php echo $pt; ?>
				</label><br>
				<?php
			}
		}
		?>
		<p
			class="description indicator-hint"><?php _e( 'Select content types available to RazorLeaf Conductor.', 'rl_conductor' ); ?></p>
		<?php
	}

	/**
	 * MySkins Content types checkboxes list callback function
	 * @deprecated 4.8
	 */
	public function myskin_content_types_field_callback() {
		_deprecated_function( '\Vc_Settings::myskin_content_types_field_callback', '4.8 (will be removed in 5.1)' );
		$pt_array = ( $pt_array = get_option( 'mcmsb_js_myskin_content_types' ) ) ? $pt_array : vc_manager()->editorPostTypes();
		foreach ( $this->getPostTypes() as $pt ) {
			if ( ! in_array( $pt, $this->getExcluded() ) ) {
				$checked = ( in_array( $pt, $pt_array ) ) ? ' checked' : '';
				?>
				<label>
					<input type="checkbox"<?php echo $checked; ?> value="<?php echo $pt; ?>"
						id="mcmsb_js_post_types_<?php echo $pt; ?>"
						name="<?php echo self::$field_prefix . 'myskin_content_types' ?>[]">
					<?php echo $pt; ?>
				</label><br>
				<?php
			}
		}
		?>
		<p
			class="description indicator-hint"><?php _e( 'Select content types available to RazorLeaf Conductor.', 'rl_conductor' ); ?></p>
		<?php
	}

	/**
	 *
	 */
	public function custom_css_field_callback() {
		$value = ( $value = get_option( self::$field_prefix . 'custom_css' ) ) ? $value : '';
		echo '<textarea name="' . self::$field_prefix . 'custom_css' . '" class="mcmsb_csseditor custom_css" style="display:none">' . $value . '</textarea>';
		echo '<pre id="mcmsb_csseditor" class="mcmsb_content_element custom_css" >' . $value . '</pre>';
		echo '<p class="description indicator-hint">' . __( 'Add custom CSS code to the module without modifying files.', 'rl_conductor' ) . '</p>';
	}

	/**
	 * Not responsive checkbox callback function
	 */
	public function not_responsive_css_field_callback() {
		$checked = ( $checked = get_option( self::$field_prefix . 'not_responsive_css' ) ) ? $checked : false;
		?>
		<label>
			<input type="checkbox"<?php echo( $checked ? ' checked' : '' ) ?> value="1"
				id="mcmsb_js_not_responsive_css" name="<?php echo self::$field_prefix . 'not_responsive_css' ?>">
			<?php _e( 'Disable', 'rl_conductor' ) ?>
		</label><br/>
		<p
			class="description indicator-hint"><?php _e( 'Disable content elements from "stacking" one on top other on small media screens (Example: mobile devices).', 'rl_conductor' ); ?></p>
		<?php
	}

	/**
	 * Google fonts subsets callback
	 */
	public function google_fonts_subsets_callback() {
		$pt_array = ( $pt_array = get_option( self::$field_prefix . 'google_fonts_subsets' ) ) ? $pt_array : $this->googleFontsSubsets();
		foreach ( $this->getGoogleFontsSubsets() as $pt ) {
			if ( ! in_array( $pt, $this->getGoogleFontsSubsetsExcluded() ) ) {
				$checked = ( in_array( $pt, $pt_array ) ) ? ' checked' : '';
				?>
				<label>
					<input type="checkbox"<?php echo $checked; ?> value="<?php echo $pt; ?>"
						id="mcmsb_js_gf_subsets_<?php echo $pt; ?>"
						name="<?php echo self::$field_prefix . 'google_fonts_subsets' ?>[]">
					<?php echo $pt; ?>
				</label><br>
				<?php
			}
		}
		?>
		<p
			class="description indicator-hint"><?php _e( 'Select subsets for Google Fonts available to content elements.', 'rl_conductor' ); ?></p>
		<?php
	}

	/**
	 * Get subsets for google fonts.
	 *
	 * @since  4.3
	 * @access public
	 * @return array
	 */
	public function googleFontsSubsets() {
		if ( ! isset( $this->google_fonts_subsets_settings ) ) {
			$pt_array = vc_settings()->get( 'google_fonts_subsets' );
			$this->google_fonts_subsets_settings = $pt_array ? $pt_array : $this->googleFontsSubsetsDefault();
		}

		return $this->google_fonts_subsets_settings;
	}

	/**
	 * @return array
	 */
	public function googleFontsSubsetsDefault() {
		return $this->google_fonts_subsets_default;
	}

	/**
	 * @return array
	 */
	public function getGoogleFontsSubsets() {
		return $this->google_fonts_subsets;
	}

	/**
	 * @param $subsets
	 *
	 * @return bool
	 */
	public function setGoogleFontsSubsets( $subsets ) {
		if ( is_array( $subsets ) ) {
			$this->google_fonts_subsets = $subsets;

			return true;
		}

		return false;
	}

	/**
	 * @return array
	 */
	public function getGoogleFontsSubsetsExcluded() {
		return $this->google_fonts_subsets_excluded;
	}

	/**
	 * @param $excluded
	 *
	 * @return bool
	 */
	public function setGoogleFontsSubsetsExcluded( $excluded ) {
		if ( is_array( $excluded ) ) {
			$this->google_fonts_subsets_excluded = $excluded;

			return true;
		}

		return false;
	}

	/**
	 * Row css class callback
	 */
	public function row_css_class_callback() {
		_deprecated_function( '\Vc_Settings::row_css_class_callback', '4.4 (will be removed in 5.1)' );
		$value = ( $value = get_option( self::$field_prefix . 'row_css_class' ) ) ? $value : '';
		echo ! empty( $value ) ? $value : '<i>' . __( 'Empty value', 'rl_conductor' ) . '</i>';
	}

	/**
	 * Not responsive checkbox callback function
	 *
	 */
	public function use_custom_callback() {
		$field = 'use_custom';
		$checked = ( $checked = get_option( self::$field_prefix . $field ) ) ? $checked : false;
		?>
		<label>
			<input type="checkbox"<?php echo( $checked ? ' checked' : '' ) ?> value="1"
				id="mcmsb_js_<?php echo $field; ?>" name="<?php echo self::$field_prefix . $field ?>">
			<?php _e( 'Enable', 'rl_conductor' ) ?>
		</label><br/>
		<p
			class="description indicator-hint"><?php _e( 'Enable the use of custom design options (Note: when checked - custom css file will be used).', 'rl_conductor' ); ?></p>
		<?php
	}

	/**
	 * @param $args
	 */
	public function color_callback( $args ) {
		$field = $args['id'];
		$value = ( $value = get_option( self::$field_prefix . $field ) ) ? $value : $this->getDefault( $field );
		echo '<input type="text" name="' . self::$field_prefix . $field . '" value="' . $value . '" class="color-control css-control">';
	}

	/**
	 *
	 */
	public function margin_callback() {
		$field = 'margin';
		$value = ( $value = get_option( self::$field_prefix . $field ) ) ? $value : $this->getDefault( $field );
		echo '<input type="text" name="' . self::$field_prefix . $field . '" value="' . $value . '" class="css-control">';
		echo '<p class="description indicator-hint css-control">' . __( 'Change default vertical spacing between content elements (Example: 20px).', 'rl_conductor' ) . '</p>';
	}

	/**
	 *
	 */
	public function gutter_callback() {
		$field = 'gutter';
		$value = ( $value = get_option( self::$field_prefix . $field ) ) ? $value : $this->getDefault( $field );
		echo '<input type="text" name="' . self::$field_prefix . $field . '" value="' . $value . '" class="css-control"> px';
		echo '<p class="description indicator-hint css-control">' . __( 'Change default horizontal spacing between columns, enter new value in pixels.', 'rl_conductor' ) . '</p>';
	}

	/**
	 *
	 */
	public function responsive_max_callback() {
		$field = 'responsive_max';
		$value = ( $value = get_option( self::$field_prefix . $field ) ) ? $value : $this->getDefault( $field );
		echo '<input type="text" name="' . self::$field_prefix . $field . '" value="' . $value . '" class="css-control"> px';
		echo '<p class="description indicator-hint css-control">' . __( 'By default content elements "stack" one on top other when screen size is smaller than 768px. Change the value to change "stacking" size.', 'rl_conductor' ) . '</p>';
	}

	/**
	 *
	 */
	public function compiled_rl_conductor_less_callback() {
		$field = 'compiled_rl_conductor_less';
		echo '<input type="hidden" name="' . self::$field_prefix . $field . '" value="">'; // VALUE must be empty
	}

	/**
	 * @param $key
	 *
	 * @return string
	 */
	public function getDefault( $key ) {
		return ! empty( self::$defaults[ $key ] ) ? self::$defaults[ $key ] : '';
	}

	/**
	 * @deprecated 4.8 Remove after 2015-12-01
	 *
	 * @return string
	 */
	public function disableIfActivated() {
		_deprecated_function( '\Vc_Settings::disableIfActivated', '4.8 (will be removed in 5.1)' );
		if ( ! isset( $this->deactivate_license ) ) {
			$this->deactivate_license = vc_license()->deactivation();
		}

		return empty( $this->deactivate_license ) ? '' : ' disabled="true" class="vc_updater-passive"';
	}

	/**
	 * Callback function for settings section
	 *
	 * @param $tab
	 */
	public function setting_section_callback_function( $tab ) {
		if ( 'mcmsb_rl_conductor_settings_color' === $tab['id'] ) : ?>
			<div class="tab_intro">
				<p>
					<?php _e( 'Here you can tweak default RazorLeaf Conductor content elements visual appearance. By default RazorLeaf Conductor is using neutral light-grey myskin. Changing "Main accent color" will affect all content elements if no specific "content block" related color is set.', 'rl_conductor' ) ?>
				</p>
			</div>
		<?php endif;
	}

	/**
	 * @return array
	 * @deprecated 4.8
	 */
	protected function getExcluded() {
		_deprecated_function( '\Vc_Settings::getExcluded', '4.8 (will be removed in 5.1)' );
		if ( ! isset( $this->vc_excluded_post_types ) ) {
			$this->vc_excluded_post_types = apply_filters( 'vc_settings_exclude_post_type', array(
				'attachment',
				'revision',
				'nav_menu_item',
				'mediapage',
			) );
		}

		return $this->vc_excluded_post_types;
	}

	/**
	 * @return array
	 * @deprecated 4.8
	 */
	protected function getPostTypes() {
		_deprecated_function( '\Vc_Settings::getPostTypes', '4.8 (will be removed in 5.1)' );

		return get_post_types( array( 'public' => true ) );
	}

	/**
	 * Access rules for user's groups
	 *
	 * @param $rules - Array of selected rules for each user's group
	 *
	 * @deprecated 4.8
	 *
	 * @return array
	 */
	public function sanitize_group_access_rules_callback( $rules ) {
		_deprecated_function( '\Vc_Settings::sanitize_group_access_rules_callback', '4.8 (will be removed in 5.1)' );
		$sanitize_rules = array();
		$groups = get_editable_roles();
		foreach ( $groups as $key => $params ) {
			if ( isset( $rules[ $key ] ) ) {
				$sanitize_rules[ $key ] = $rules[ $key ];
			}
		}

		return $sanitize_rules;
	}

	/**
	 * @param $rules
	 *
	 * @return mixed
	 */
	public function sanitize_not_responsive_css_callback( $rules ) {
		return (bool) $rules;
	}

	/**
	 * @param $value
	 *
	 * @return mixed
	 */
	public function sanitize_row_css_class_callback( $value ) {
		_deprecated_function( '\Vc_Settings::row_css_class_callback', '4.4 (will be removed in 5.1)' );

		return $value;
	}

	/**
	 * Post types fields sanitize
	 *
	 * @param $post_types - Post types array selected by user
	 *
	 * @deprecated 4.8
	 * @return array
	 */
	public function sanitize_post_types_callback( $post_types ) {
		_deprecated_function( '\Vc_Settings::sanitize_post_types_callback', '4.8 (will be removed in 5.1)' );
		$pt_array = array();
		if ( isset( $post_types ) && is_array( $post_types ) ) {
			foreach ( $post_types as $pt ) {
				if ( ! in_array( $pt, $this->getExcluded() ) && in_array( $pt, $this->getPostTypes() ) ) {
					$pt_array[] = $pt;
				}
			}
		}

		return $pt_array;
	}

	/**
	 * @param $subsets
	 *
	 * @return array
	 */
	public function sanitize_google_fonts_subsets_callback( $subsets ) {
		$pt_array = array();
		if ( isset( $subsets ) && is_array( $subsets ) ) {
			foreach ( $subsets as $pt ) {
				if ( ! in_array( $pt, $this->getGoogleFontsSubsetsExcluded() ) && in_array( $pt, $this->getGoogleFontsSubsets() ) ) {
					$pt_array[] = $pt;
				}
			}
		}

		return $pt_array;
	}

	/**
	 * @param $rules
	 *
	 * @return mixed
	 */
	public function sanitize_use_custom_callback( $rules ) {
		return (bool) $rules;
	}

	/**
	 * @param $css
	 *
	 * @return mixed
	 */
	public function sanitize_custom_css_callback( $css ) {
		return strip_tags( $css );
	}

	/**
	 * @param $css
	 *
	 * @return mixed
	 */
	public function sanitize_compiled_rl_conductor_less_callback( $css ) {
		return $css;
	}

	/**
	 * @param $color
	 *
	 * @return mixed
	 */
	public function sanitize_color_callback( $color ) {
		return $color;
	}

	/**
	 * @param $margin
	 *
	 * @return mixed
	 */
	public function sanitize_margin_callback( $margin ) {
		$margin = preg_replace( '/\s/', '', $margin );
		if ( ! preg_match( '/^\d+(px|%|em|pt){0,1}$/', $margin ) ) {
			add_settings_error( self::$field_prefix . 'margin', 1, __( 'Invalid Margin value.', 'rl_conductor' ), 'error' );
		}

		return $margin;
	}

	/**
	 * @param $gutter
	 *
	 * @return mixed
	 */
	public function sanitize_gutter_callback( $gutter ) {
		$gutter = preg_replace( '/[^\d]/', '', $gutter );
		if ( ! $this->_isGutterValid( $gutter ) ) {
			add_settings_error( self::$field_prefix . 'gutter', 1, __( 'Invalid Gutter value.', 'rl_conductor' ), 'error' );
		}

		return $gutter;
	}

	/**
	 * @param $responsive_max
	 *
	 * @return mixed
	 */
	public function sanitize_responsive_max_callback( $responsive_max ) {
		if ( ! $this->_isNumberValid( $responsive_max ) ) {
			add_settings_error( self::$field_prefix . 'responsive_max', 1, __( 'Invalid "Responsive max" value.', 'rl_conductor' ), 'error' );
		}

		return $responsive_max;
	}

	/**
	 * @param $number
	 *
	 * @return int
	 */
	public static function _isNumberValid( $number ) {
		return preg_match( '/^[\d]+(\.\d+){0,1}$/', $number );
	}

	/**
	 * @param $gutter
	 *
	 * @return int
	 */
	public static function _isGutterValid( $gutter ) {
		return self::_isNumberValid( $gutter );
	}

	/**
	 * @deprecated 4.4
	 * @return bool
	 */
	public static function requireNotification() {
		_deprecated_function( '\Vc_Settings::requireNotification', '4.4 (will be removed in 5.1)' );
		$row_css_class = ( $value = get_option( self::$field_prefix . 'row_css_class' ) ) ? $value : '';
		$column_css_classes = ( $value = get_option( self::$field_prefix . 'column_css_classes' ) ) ? $value : '';

		$notification = get_option( self::$notification_name );
		if ( 'false' !== $notification && ( ! empty( $row_css_class ) || strlen( implode( '', array_values( $column_css_classes ) ) ) > 0 ) ) {
			update_option( self::$notification_name, 'true' );

			return true;
		}

		return false;
	}

	public function useCustomCss() {
		$use_custom = get_option( self::$field_prefix . 'use_custom', false );

		return $use_custom;
	}

	public function getCustomCssVersion() {
		$less_version = get_option( self::$field_prefix . 'less_version', false );

		return $less_version;
	}

	/**
	 *
	 */
	public function rebuild() {
		/** MandarinCMS Template Administration API */
		require_once( BASED_TREE_URI . 'mcms-admin/includes/template.php' );
		/** MandarinCMS Administration File API */
		require_once( BASED_TREE_URI . 'mcms-admin/includes/file.php' );
		delete_option( self::$field_prefix . 'compiled_rl_conductor_less' );
		$this->initAdmin();
		$this->buildCustomCss(); // TODO: remove this - no needs to re-save always
	}

	/**
	 *
	 */
	public static function buildCustomColorCss() {
		/**
		 * Filesystem API init.
		 * */
		$url = mcms_nonce_url( 'admin.php?page=vc-color&build_css=1', 'mcmsb_js_settings_save_action' );
		self::getFileSystem( $url );
		global $mcms_filesystem;
		/**
		 *
		 * Building css file.
		 *
		 */
		if ( false === ( $rl_conductor_upload_dir = self::checkCreateUploadDir( $mcms_filesystem, 'use_custom', 'rl_conductor_front_custom.css' ) ) ) {
			return;
		}

		$filename = $rl_conductor_upload_dir . '/rl_conductor_front_custom.css';
		$use_custom = get_option( self::$field_prefix . 'use_custom' );
		if ( ! $use_custom ) {
			$mcms_filesystem->put_contents( $filename, '', FS_CHMOD_FILE );

			return;
		}
		$css_string = get_option( self::$field_prefix . 'compiled_rl_conductor_less' );
		if ( strlen( trim( $css_string ) ) > 0 ) {
			update_option( self::$field_prefix . 'less_version', MCMSB_VC_VERSION );
			delete_option( self::$field_prefix . 'compiled_rl_conductor_less' );
			$css_string = strip_tags( $css_string );
			// HERE goes the magic
			if ( ! $mcms_filesystem->put_contents( $filename, $css_string, FS_CHMOD_FILE ) ) {
				if ( is_mcms_error( $mcms_filesystem->errors ) && $mcms_filesystem->errors->get_error_code() ) {
					add_settings_error( self::$field_prefix . 'main_color', $mcms_filesystem->errors->get_error_code(), __( 'Something went wrong: rl_conductor_front_custom.css could not be created.', 'rl_conductor' ) . ' ' . $mcms_filesystem->errors->get_error_message(), 'error' );
				} elseif ( ! $mcms_filesystem->connect() ) {
					add_settings_error( self::$field_prefix . 'main_color', $mcms_filesystem->errors->get_error_code(), __( 'rl_conductor_front_custom.css could not be created. Connection error.', 'rl_conductor' ), 'error' );
				} elseif ( ! $mcms_filesystem->is_writable( $filename ) ) {
					add_settings_error( self::$field_prefix . 'main_color', $mcms_filesystem->errors->get_error_code(), sprintf( __( 'rl_conductor_front_custom.css could not be created. Cannot write custom css to "%s".', 'rl_conductor' ), $filename ), 'error' );
				} else {
					add_settings_error( self::$field_prefix . 'main_color', $mcms_filesystem->errors->get_error_code(), __( 'rl_conductor_front_custom.css could not be created. Problem with access.', 'rl_conductor' ), 'error' );
				}
				delete_option( self::$field_prefix . 'use_custom' );
				delete_option( self::$field_prefix . 'less_version' );
			}
		}
	}

	/**
	 * Builds custom css file using css options from vc settings.
	 *
	 * @return bool
	 */
	public static function buildCustomCss() {
		/**
		 * Filesystem API init.
		 * */
		$url = mcms_nonce_url( 'admin.php?page=vc-color&build_css=1', 'mcmsb_js_settings_save_action' );
		self::getFileSystem( $url );
		global $mcms_filesystem;
		/**
		 * Building css file.
		 */
		if ( false === ( $rl_conductor_upload_dir = self::checkCreateUploadDir( $mcms_filesystem, 'custom_css', 'custom.css' ) ) ) {
			return true;
		}

		$filename = $rl_conductor_upload_dir . '/custom.css';
		$css_string = '';
		$custom_css_string = get_option( self::$field_prefix . 'custom_css' );
		if ( ! empty( $custom_css_string ) ) {
			$assets_url = vc_asset_url( '' );
			$css_string .= preg_replace( '/(url\(\.\.\/(?!\.))/', 'url(' . $assets_url, $custom_css_string );
			$css_string = strip_tags( $css_string );
		}

		if ( ! $mcms_filesystem->put_contents( $filename, $css_string, FS_CHMOD_FILE ) ) {
			if ( is_mcms_error( $mcms_filesystem->errors ) && $mcms_filesystem->errors->get_error_code() ) {
				add_settings_error( self::$field_prefix . 'custom_css', $mcms_filesystem->errors->get_error_code(), __( 'Something went wrong: custom.css could not be created.', 'rl_conductor' ) . $mcms_filesystem->errors->get_error_message(), 'error' );
			} elseif ( ! $mcms_filesystem->connect() ) {
				add_settings_error( self::$field_prefix . 'custom_css', $mcms_filesystem->errors->get_error_code(), __( 'custom.css could not be created. Connection error.', 'rl_conductor' ), 'error' );
			} elseif ( ! $mcms_filesystem->is_writable( $filename ) ) {
				add_settings_error( self::$field_prefix . 'custom_css', $mcms_filesystem->errors->get_error_code(), __( 'custom.css could not be created. Cannot write custom css to "' . $filename . '".', 'rl_conductor' ), 'error' );
			} else {
				add_settings_error( self::$field_prefix . 'custom_css', $mcms_filesystem->errors->get_error_code(), __( 'custom.css could not be created. Problem with access.', 'rl_conductor' ), 'error' );
			}

			return false;
		}

		return true;

	}

	/**
	 * @param $mcms_filesystem
	 * @param $option
	 * @param $filename
	 *
	 * @return bool|string
	 */
	public static function checkCreateUploadDir( $mcms_filesystem, $option, $filename ) {
		$rl_conductor_upload_dir = self::uploadDir();
		if ( ! $mcms_filesystem->is_dir( $rl_conductor_upload_dir ) ) {
			if ( ! $mcms_filesystem->mkdir( $rl_conductor_upload_dir, 0777 ) ) {
				add_settings_error( self::$field_prefix . $option, $mcms_filesystem->errors->get_error_code(), __( sprintf( '%s could not be created. Not available to create rl_conductor directory in uploads directory (' . $rl_conductor_upload_dir . ').', $filename ), 'rl_conductor' ), 'error' );

				return false;
			}
		}

		return $rl_conductor_upload_dir;
	}

	/**
	 * @return string
	 */
	public static function uploadDir() {
		$upload_dir = mcms_upload_dir();
		global $mcms_filesystem;

		return $mcms_filesystem->find_folder( $upload_dir['basedir'] ) . vc_upload_dir();
	}

	/**
	 * @return string
	 */
	public static function uploadURL() {
		$upload_dir = mcms_upload_dir();

		return $upload_dir['baseurl'] . vc_upload_dir();
	}

	/**
	 * @return string
	 */
	public static function getFieldPrefix() {
		return self::$field_prefix;
	}

	/**
	 * @param string $url
	 */
	protected static function getFileSystem( $url = '' ) {
		if ( empty( $url ) ) {
			$url = mcms_nonce_url( 'admin.php?page=vc-general', 'mcmsb_js_settings_save_action' );
		}
		if ( false === ( $creds = request_filesystem_credentials( $url, '', false, false, null ) ) ) {
			_e( 'This is required to enable file writing for rl_conductor', 'rl_conductor' );
			exit(); // stop processing here
		}
		$upload_dir = mcms_upload_dir();
		if ( ! MCMS_Filesystem( $creds, $upload_dir['basedir'] ) ) {
			request_filesystem_credentials( $url, '', true, false, null );
			_e( 'This is required to enable file writing for rl_conductor', 'rl_conductor' );
			exit();
		}
	}

	/**
	 * @return string
	 */
	public function getOptionGroup() {
		return $this->option_group;
	}
}

/**
 * Backward capability for third-party-modules
 */
class MCMSBakeryVisualComposerSettings extends Vc_Settings {
	/**
	 * @deprecated 5.0
	 * MCMSBakeryVisualComposerSettings constructor.
	 */
	public function __construct() {
		_deprecated_function( '\MCMSBakeryVisualComposerSettings::__construct', '4.8 (will be removed in 5.1)' );
	}
}
