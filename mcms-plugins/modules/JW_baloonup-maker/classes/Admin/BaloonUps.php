<?php
/*******************************************************************************
 * Copyright (c) 2017, MCMS BaloonUp Maker
 ******************************************************************************/

if ( ! defined( 'BASED_TREE_URI' ) ) {
	exit;
}

/**
 * Class PUM_Admin_BaloonUps
 *
 * @since 1.7.0
 */
class PUM_Admin_BaloonUps {

	/**
	 * Hook the initialize method to the MCMS init action.
	 */
	public static function init() {
		// Change title to baloonup name.
		add_filter( 'enter_title_here', array( __CLASS__, '_default_title' ) );

		// Add baloonup title field.
		add_action( 'edit_form_advanced', array( __CLASS__, 'title_meta_field' ) );

		// Add Contextual help to post_name field.
		add_action( 'edit_form_before_permalink', array( __CLASS__, 'baloonup_post_title_contextual_message' ) );

		// Regitster Metaboxes
		add_action( 'add_meta_boxes', array( __CLASS__, 'meta_box' ) );

		// Process meta saving.
		add_action( 'save_post', array( __CLASS__, 'save' ), 10, 2 );

		// Set the slug properly on save.
		add_filter( 'mcms_insert_post_data', array( __CLASS__, 'set_slug' ), 99, 2 );

		// Dashboard columns & filters.
		add_filter( 'manage_edit-baloonup_columns', array( __CLASS__, 'dashboard_columns' ) );
		add_action( 'manage_posts_custom_column', array( __CLASS__, 'render_columns' ), 10, 2 );
		add_filter( 'manage_edit-baloonup_sortable_columns', array( __CLASS__, 'sortable_columns' ) );
		add_action( 'load-edit.php', array( __CLASS__, 'load' ), 9999 );
		add_action( 'restrict_manage_posts', array( __CLASS__, 'add_baloonup_filters' ), 100 );
	}

	/**
	 * Change default "Enter title here" input
	 *
	 * @param string $title Default title placeholder text
	 *
	 * @return string $title New placeholder text
	 */
	public static function _default_title( $title ) {

		if ( ! is_admin() ) {
			return $title;
		}

		$screen = get_current_screen();

		if ( 'baloonup_myskin' == $screen->post_type ) {
			$label = $screen->post_type == 'baloonup' ? __( 'BaloonUp', 'baloonup-maker' ) : __( 'BaloonUp mySkin', 'baloonup-maker' );
			$title = sprintf( __( '%s Name', 'baloonup-maker' ), $label );
		}

		if ( 'baloonup' == $screen->post_type ) {
			$title = __( 'BaloonUp Name (appears under "Name" column on "All BaloonUps" screen', 'baloonup-maker' );
		}

		return $title;
	}


	/**
	 * Renders the baloonup title meta field.
	 */
	public static function title_meta_field() {
		global $post, $pagenow, $typenow;

		if ( ! is_admin() ) {
			return;
		}

		if ( 'baloonup' == $typenow && in_array( $pagenow, array( 'post-new.php', 'post.php' ) ) ) { ?>

			<div id="baloonup-titlediv" class="pum-form">
				<div id="baloonup-titlewrap">
					<label class="screen-reader-text" id="baloonup-title-prompt-text" for="baloonup-title">
						<?php _e( 'BaloonUp Title (appears on front end inside the baloonup container)', 'baloonup-maker' ); ?>
					</label>
					<input tabindex="2" name="baloonup_title" size="30" value="<?php esc_attr_e( get_post_meta( $post->ID, 'baloonup_title', true ) ); ?>" id="baloonup-title" autocomplete="off" placeholder="<?php _e( 'BaloonUp Title (appears on front end inside the baloonup container)', 'baloonup-maker' ); ?>" />
					<p class="pum-desc"><?php echo '(' . __( 'Optional', 'baloonup-maker' ) . ') ' . __( 'Display a title inside the baloonup container. May be left empty.', 'baloonup-maker' ); ?></p>
				</div>
				<div class="inside"></div>
			</div>
			<script>jQuery('#baloonup-titlediv').insertAfter('#titlediv');</script>
			<?php
		}
	}

	/**
	 * Renders contextual help for title.
	 */
	public static function baloonup_post_title_contextual_message() {
		global $post, $pagenow, $typenow;

		if ( ! is_admin() ) {
			return;
		}

		if ( 'baloonup' == $typenow && in_array( $pagenow, array( 'post-new.php', 'post.php' ) ) ) { ?>
			<p class="pum-desc"><?php echo '(' . __( 'Required', 'baloonup-maker' ) . ') ' . __( 'Register a baloonup name. The CSS class ‘balooncreate-{baloonup-name}’ can be used to set a trigger to display a baloonup.', 'baloonup-maker' ); ?></p>
			<?php
		}
	}

	/**
	 * Registers baloonup metaboxes.
	 */
	public static function meta_box() {
		add_meta_box( 'pum_baloonup_settings', __( 'BaloonUp Settings', 'baloonup-maker' ), array( __CLASS__, 'render_settings_meta_box' ), 'baloonup', 'normal', 'high' );
		add_meta_box( 'pum_baloonup_analytics', __( 'Analytics', 'baloonup-maker' ), array( __CLASS__, 'render_analytics_meta_box' ), 'baloonup', 'side', 'high' );
	}

	/**
	 * Render the settings meta box wrapper and JS vars.
	 */
	public static function render_settings_meta_box() {
		global $post;

		$baloonup = pum_get_baloonup( $post->ID, true );

		// Get the meta directly rather than from cached object.
		$settings = $baloonup->get_settings( true );

		if ( empty( $settings ) ) {
			$settings = self::defaults();
		}

		// Do settings migration on the fly and then self clean for a passive migration?

		// $settings['conditions'] = get_post_meta( $post->ID, 'baloonup_conditions', true );
		//$settings['triggers'] = get_post_meta( $post->ID, 'baloonup_triggers', true );

		mcms_nonce_field( basename( __FILE__ ), 'pum_baloonup_settings_nonce' );
		mcms_enqueue_script( 'baloonup-maker-admin' );
		?>
		<script type="text/javascript">
            window.pum_baloonup_settings_editor = <?php echo PUM_Utils_Array::safe_json_encode( apply_filters( 'pum_baloonup_settings_editor_var', array(
				'form_args'             => array(
					'id'       => 'pum-baloonup-settings',
					'tabs'     => self::tabs(),
					'sections' => self::sections(),
					'fields'   => self::fields(),
				),
				'conditions'            => PUM_Conditions::instance()->get_conditions(),
				'conditions_selectlist' => PUM_Conditions::instance()->dropdown_list(),
				'triggers'              => PUM_Triggers::instance()->get_triggers(),
				'cookies'               => PUM_Cookies::instance()->get_cookies(),
				'current_values'        => self::parse_values( $settings ),
			) ) ); ?>;
		</script>

		<div id="pum-baloonup-settings-container" class="pum-baloonup-settings-container">
			<div class="pum-no-js" style="padding: 0 12px;">
				<p><?php printf( __( 'If you are seeing this, the page is still loading or there are Javascript errors on this page. %sView troubleshooting guide%s', 'baloonup-maker' ), '<a href="https://docs.mcmsbaloonupmaker.com/article/373-checking-for-javascript-errors" target="_blank">', '</a>' ); ?></p>
			</div>
		</div>
		<?php
	}

	/**
	 * Used to get deprecated fields for metabox saving of old extensions.
	 *
	 * @deprecated 1.7.0
	 *
	 * @return mixed
	 */
	public static function deprecated_meta_fields() {
		$fields = array();
		foreach ( self::deprecated_meta_field_groups() as $group ) {
			foreach ( apply_filters( 'balooncreate_baloonup_meta_field_group_' . $group, array() ) as $field ) {
				$fields[] = 'baloonup_' . $group . '_' . $field;
			}
		}

		return apply_filters( 'balooncreate_baloonup_meta_fields', $fields );
	}

	/**
	 * Used to get field groups from extensions.
	 *
	 * @deprecated 1.7.0
	 *
	 * @return mixed
	 */
	public static function deprecated_meta_field_groups() {
		return apply_filters( 'balooncreate_baloonup_meta_field_groups', array( 'display', 'close' ) );
	}

	/**
	 * @param $post_id
	 * @param $post
	 */
	public static function save( $post_id, $post ) {

		if ( isset( $post->post_type ) && 'baloonup' != $post->post_type ) {
			return;
		}

		if ( ! isset( $_POST['pum_baloonup_settings_nonce'] ) || ! mcms_verify_nonce( $_POST['pum_baloonup_settings_nonce'], basename( __FILE__ ) ) ) {
			return;
		}

		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || isset( $_REQUEST['bulk_edit'] ) ) {
			return;
		}

		if ( isset( $post->post_type ) && 'revision' == $post->post_type ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$baloonup = pum_get_baloonup( $post_id );

		if ( isset( $_POST['baloonup_reset_counts'] ) ) {
			/**
			 * Reset baloonup open count, per user request.
			 */
			$baloonup->reset_counts();
		}


		$title = ! empty ( $_POST['baloonup_title'] ) ? trim( sanitize_text_field( $_POST['baloonup_title'] ) ) : '';
		$baloonup->update_meta( 'baloonup_title', $title );

		$settings = ! empty( $_POST['baloonup_settings'] ) ? $_POST['baloonup_settings'] : array();

		$settings = mcms_parse_args( $settings, self::defaults() );

		// Sanitize JSON values.
		$settings['conditions'] = isset( $settings['conditions'] ) ? self::sanitize_meta( $settings['conditions'] ) : array();
		$settings['triggers']   = isset( $settings['triggers'] ) ? self::sanitize_meta( $settings['triggers'] ) : array();
		$settings['cookies']    = isset( $settings['cookies'] ) ? self::sanitize_meta( $settings['cookies'] ) : array();

		$settings = apply_filters( 'pum_baloonup_setting_pre_save', $settings, $post->ID );

		$settings = self::sanitize_settings( $settings );

		$baloonup->update_meta( 'baloonup_settings', $settings );

		// TODO Remove this and all other code here. This should be clean and all code more compartmentalized.
		foreach ( self::deprecated_meta_fields() as $field ) {
			if ( isset( $_POST[ $field ] ) ) {
				$new = apply_filters( 'balooncreate_metabox_save_' . $field, $_POST[ $field ] );
				update_post_meta( $post_id, $field, $new );
			} else {
				delete_post_meta( $post_id, $field );
			}
		}

		do_action( 'pum_save_baloonup', $post_id, $post );
	}

	public static function parse_values( $settings ) {

		foreach ( $settings as $key => $value ) {
			$field = self::get_field( $key );


			if ( $field ) {
				switch ( $field['type'] ) {
					case 'measure':
						break;
				}
			}
		}

		return $settings;
	}

	/**
	 * List of tabs & labels for the settings panel.
	 *
	 * @return array
	 */
	public static function tabs() {
		return apply_filters( 'pum_baloonup_settings_tabs', array(
			'general'   => __( 'General', 'baloonup-maker' ),
			'display'   => __( 'Display', 'baloonup-maker' ),
			'close'     => __( 'Close', 'baloonup-maker' ),
			'triggers'  => __( 'Triggers', 'baloonup-maker' ),
			'targeting' => __( 'Targeting', 'baloonup-maker' ),
		) );
	}

	/**
	 * List of tabs & labels for the settings panel.
	 *
	 * @return array
	 */
	public static function sections() {
		return apply_filters( 'pum_baloonup_settings_sections', array(
			'general'   => array(
				'main' => __( 'General Settings', 'baloonup-maker' ),
			),
			'triggers'  => array(
				'main' => __( 'Triggers & Cookies', 'baloonup-maker' ),
				'advanced'  => __( 'Advanced', 'baloonup-maker' ),
			),
			'targeting' => array(
				'main' => __( 'Conditions', 'baloonup-maker' ),
			),
			'display'   => array(
				'main'      => __( 'Appearance', 'baloonup-maker' ),
				'size'      => __( 'Size', 'baloonup-maker' ),
				'animation' => __( 'Animation', 'baloonup-maker' ),
				'position'  => __( 'Position', 'baloonup-maker' ),
				'advanced'  => __( 'Advanced', 'baloonup-maker' ),
			),
			'close'     => array(
				'button'            => __( 'Button', 'baloonup-maker' ),
				'alternate_methods' => __( 'Alternate Methods', 'baloonup-maker' ),
			),
		) );
	}

	/**
	 * Returns array of baloonup settings fields.
	 *
	 * @return mixed
	 */
	public static function fields() {

		static $tabs;

		if ( ! isset( $tabs ) ) {
			$tabs = apply_filters( 'pum_baloonup_settings_fields', array(
				'general'   => apply_filters( 'pum_baloonup_general_settings_fields', array(
					'main' => array(),
				) ),
				'triggers'  => apply_filters( 'pum_baloonup_triggers_settings_fields', array(
					'main' => array(
						'triggers'   => array(
							'type'     => 'triggers',
							'std'      => array(),
							'priority' => 10,
						),
						'separator1' => array(
							'type'    => 'separator',
							'private' => true,
						),
						'cookies'    => array(
							'type'     => 'cookies',
							'std'      => array(),
							'priority' => 20,
						),
					),
					'advanced' => array(
						'disable_form_reopen' => array(
							'label'    => __( 'Disable automatic re-triggering of baloonup after non-ajax form submission.', 'baloonup-maker' ),
							'type'     => 'checkbox',
							'priority' => 10,
						),
					),
				) ),
				'targeting' => apply_filters( 'pum_baloonup_targeting_settings_fields', array(
					'main' => array(
						'conditions'        => array(
							'type'     => 'conditions',
							'std'      => array(),
							'priority' => 10,
							'private'  => true,
						),
						'disable_on_mobile' => array(
							'label'    => __( 'Disable this baloonup on mobile devices.', 'baloonup-maker' ),
							'type'     => 'checkbox',
							'priority' => 20,
						),
						'disable_on_tablet' => array(
							'label'    => __( 'Disable this baloonup on tablet devices.', 'baloonup-maker' ),
							'type'     => 'checkbox',
							'priority' => 20,
						),
					),
				) ),
				'display'   => apply_filters( 'pum_baloonup_display_settings_fields', array(
					'main'      => array(
						'myskin_id' => array(
							'label'        => __( 'BaloonUp mySkin', 'baloonup-maker' ),
							'dynamic_desc' => sprintf( '%1$s<br/><a id="edit_myskin_link" href="%3$s">%2$s</a>', __( 'Choose a myskin for this baloonup.', 'baloonup-maker' ), __( 'Customize This mySkin', 'baloonup-maker' ), admin_url( "post.php?action=edit&post={{data.value}}" ) ),
							'type'         => 'select',
							'options'      => PUM_Helpers::baloonup_myskin_selectlist(),
							'std'          => balooncreate_get_default_baloonup_myskin(),
						),
					),
					'size'      => array(
						'size'                 => array(
							'label'    => __( 'Size', 'baloonup-maker' ),
							'desc'     => __( 'Select the size of the baloonup.', 'baloonup-maker' ),
							'type'     => 'select',
							'std'      => 'medium',
							'priority' => 10,
							'options'  => array(
								__( 'Responsive Sizes', 'baloonup-maker' ) => array(
									'nano'   => __( 'Nano - 10%', 'baloonup-maker' ),
									'micro'  => __( 'Micro - 20%', 'baloonup-maker' ),
									'tiny'   => __( 'Tiny - 30%', 'baloonup-maker' ),
									'small'  => __( 'Small - 40%', 'baloonup-maker' ),
									'medium' => __( 'Medium - 60%', 'baloonup-maker' ),
									'normal' => __( 'Normal - 70%', 'baloonup-maker' ),
									'large'  => __( 'Large - 80%', 'baloonup-maker' ),
									'xlarge' => __( 'X Large - 95%', 'baloonup-maker' ),
								),
								__( 'Other Sizes', 'baloonup-maker' )      => array(
									'auto'   => __( 'Auto', 'baloonup-maker' ),
									'custom' => __( 'Custom', 'baloonup-maker' ),
								),
							),
						),
						'responsive_min_width' => array(
							'label'        => __( 'Min Width', 'baloonup-maker' ),
							'desc'         => __( 'Set a minimum width for the baloonup.', 'baloonup-maker' ),
							'type'         => 'measure',
							'std'          => '0%',
							'priority'     => 20,
							'dependencies' => array(
								'size' => array( 'nano', 'micro', 'tiny', 'small', 'medium', 'normal', 'large', 'xlarge' ),
							),
						),
						'responsive_max_width' => array(
							'label'        => __( 'Max Width', 'baloonup-maker' ),
							'desc'         => __( 'Set a maximum width for the baloonup.', 'baloonup-maker' ),
							'type'         => 'measure',
							'std'          => '100%',
							'priority'     => 30,
							'dependencies' => array(
								'size' => array( 'nano', 'micro', 'tiny', 'small', 'medium', 'normal', 'large', 'xlarge' ),
							),
						),
						'custom_width'         => array(
							'label'        => __( 'Width', 'baloonup-maker' ),
							'desc'         => __( 'Set a custom width for the baloonup.', 'baloonup-maker' ),
							'type'         => 'measure',
							'std'          => '640px',
							'priority'     => 40,
							'dependencies' => array(
								'size' => 'custom',
							),
						),
						'custom_height_auto'   => array(
							'label'        => __( 'Auto Adjusted Height', 'baloonup-maker' ),
							'desc'         => __( 'Checking this option will set height to fit the content.', 'baloonup-maker' ),
							'type'         => 'checkbox',
							'priority'     => 50,
							'dependencies' => array(
								'size' => 'custom',
							),
						),
						'custom_height'        => array(
							'label'        => __( 'Height', 'baloonup-maker' ),
							'desc'         => __( 'Set a custom height for the baloonup.', 'baloonup-maker' ),
							'type'         => 'measure',
							'std'          => '380px',
							'priority'     => 60,
							'dependencies' => array(
								'size'               => 'custom',
								'custom_height_auto' => false,
							),
						),
						'scrollable_content'   => array(
							'label'        => __( 'Scrollable Content', 'baloonup-maker' ),
							'desc'         => __( 'Checking this option will add a scroll bar to your content.', 'baloonup-maker' ),
							'type'         => 'checkbox',
							'std'          => false,
							'priority'     => 70,
							'dependencies' => array(
								'size'               => 'custom',
								'custom_height_auto' => false,
							),
						),
					),
					'animation' => array(
						'animation_type'   => array(
							'label'    => __( 'Animation Type', 'baloonup-maker' ),
							'desc'     => __( 'Select an animation type for your baloonup.', 'baloonup-maker' ),
							'type'     => 'select',
							'std'      => 'fade',
							'priority' => 10,
							'options'  => array(
								'none'         => __( 'None', 'baloonup-maker' ),
								'slide'        => __( 'Slide', 'baloonup-maker' ),
								'fade'         => __( 'Fade', 'baloonup-maker' ),
								'fadeAndSlide' => __( 'Fade and Slide', 'baloonup-maker' ),
								// 'grow'         => __( 'Grow', 'baloonup-maker' ),
								// 'growAndSlide' => __( 'Grow and Slide', 'baloonup-maker' ),
							),
						),
						'animation_speed'  => array(
							'label'        => __( 'Animation Speed', 'baloonup-maker' ),
							'desc'         => __( 'Set the animation speed for the baloonup.', 'baloonup-maker' ),
							'type'         => 'rangeslider',
							'std'          => 350,
							'step'         => 10,
							'min'          => 50,
							'max'          => 1000,
							'unit'         => __( 'ms', 'baloonup-maker' ),
							'priority'     => 20,
							'dependencies' => array(
								'animation_type' => array( 'slide', 'fade', 'fadeAndSlide', 'grow', 'growAndSlide' ),
							),
						),
						'animation_origin' => array(
							'label'        => __( 'Animation Origin', 'baloonup-maker' ),
							'desc'         => __( 'Choose where the animation will begin.', 'baloonup-maker' ),
							'type'         => 'select',
							'std'          => 'center top',
							'options'      => array(
								'top'           => __( 'Top', 'baloonup-maker' ),
								'left'          => __( 'Left', 'baloonup-maker' ),
								'bottom'        => __( 'Bottom', 'baloonup-maker' ),
								'right'         => __( 'Right', 'baloonup-maker' ),
								'left top'      => __( 'Top Left', 'baloonup-maker' ),
								'center top'    => __( 'Top Center', 'baloonup-maker' ),
								'right top'     => __( 'Top Right', 'baloonup-maker' ),
								'left center'   => __( 'Middle Left', 'baloonup-maker' ),
								'center center' => __( 'Middle Center', 'baloonup-maker' ),
								'right center'  => __( 'Middle Right', 'baloonup-maker' ),
								'left bottom'   => __( 'Bottom Left', 'baloonup-maker' ),
								'center bottom' => __( 'Bottom Center', 'baloonup-maker' ),
								'right bottom'  => __( 'Bottom Right', 'baloonup-maker' ),
							),
							'priority'     => 30,
							'dependencies' => array(
								'animation_type' => array( 'slide', 'fadeAndSlide', 'grow', 'growAndSlide' ),
							),
						),
					),
					'position'  => array(
						'location'              => array(
							'label'    => __( 'Location', 'baloonup-maker' ),
							'desc'     => __( 'Choose where the baloonup will be displayed.', 'baloonup-maker' ),
							'type'     => 'select',
							'std'      => 'center top',
							'priority' => 10,
							'options'  => array(
								'left top'      => __( 'Top Left', 'baloonup-maker' ),
								'center top'    => __( 'Top Center', 'baloonup-maker' ),
								'right top'     => __( 'Top Right', 'baloonup-maker' ),
								'left center'   => __( 'Middle Left', 'baloonup-maker' ),
								'center'        => __( 'Middle Center', 'baloonup-maker' ),
								'right center'  => __( 'Middle Right', 'baloonup-maker' ),
								'left bottom'   => __( 'Bottom Left', 'baloonup-maker' ),
								'center bottom' => __( 'Bottom Center', 'baloonup-maker' ),
								'right bottom'  => __( 'Bottom Right', 'baloonup-maker' ),
							),
						),
						'position_top'          => array(
							'label'        => __( 'Top', 'baloonup-maker' ),
							'desc'         => sprintf( _x( 'Distance from the %s edge of the screen.', 'Screen Edge: top, bottom', 'baloonup-maker' ), strtolower( __( 'Top', 'baloonup-maker' ) ) ),
							'type'         => 'rangeslider',
							'std'          => 100,
							'step'         => 1,
							'min'          => 0,
							'max'          => 500,
							'unit'         => 'px',
							'priority'     => 20,
							'dependencies' => array(
								'location' => array( 'left top', 'center top', 'right top' ),
							),
						),
						'position_bottom'       => array(
							'label'        => __( 'Bottom', 'baloonup-maker' ),
							'desc'         => sprintf( _x( 'Distance from the %s edge of the screen.', 'Screen Edge: top, bottom', 'baloonup-maker' ), strtolower( __( 'Bottom', 'baloonup-maker' ) ) ),
							'type'         => 'rangeslider',
							'std'          => 0,
							'step'         => 1,
							'min'          => 0,
							'max'          => 500,
							'unit'         => 'px',
							'priority'     => 20,
							'dependencies' => array(
								'location' => array( 'left bottom', 'center bottom', 'right bottom' ),
							),
						),
						'position_left'         => array(
							'label'        => __( 'Left', 'baloonup-maker' ),
							'desc'         => sprintf( _x( 'Distance from the %s edge of the screen.', 'Screen Edge: top, bottom', 'baloonup-maker' ), strtolower( __( 'Left', 'baloonup-maker' ) ) ),
							'type'         => 'rangeslider',
							'std'          => 0,
							'step'         => 1,
							'min'          => 0,
							'max'          => 500,
							'unit'         => 'px',
							'priority'     => 30,
							'dependencies' => array(
								'location' => array( 'left top', 'left center', 'left bottom' ),
							),
						),
						'position_right'        => array(
							'label'        => __( 'Right', 'baloonup-maker' ),
							'desc'         => sprintf( _x( 'Distance from the %s edge of the screen.', 'Screen Edge: top, bottom', 'baloonup-maker' ), strtolower( __( 'Right', 'baloonup-maker' ) ) ),
							'type'         => 'rangeslider',
							'std'          => 0,
							'step'         => 1,
							'min'          => 0,
							'max'          => 500,
							'unit'         => 'px',
							'priority'     => 30,
							'dependencies' => array(
								'location' => array( 'right top', 'right center', 'right bottom' ),
							),
						),
						'position_from_trigger' => array(
							'label'    => __( 'Position from Trigger', 'baloonup-maker' ),
							'desc'     => sprintf( __( 'This will position the baloonup in relation to the %sClick Trigger%s.', 'baloonup-maker' ), '<a target="_blank" href="https://docs.mcmsbaloonupmaker.com/article/144-trigger-click-open?utm_medium=inline-doclink&utm_campaign=ContextualHelp&utm_source=module-baloonup-editor&utm_content=position-from-trigger">', '</a>' ),
							'type'     => 'checkbox',
							'std'      => false,
							'priority' => 40,
						),
						'position_fixed'        => array(
							'label'    => __( 'Fixed Postioning', 'baloonup-maker' ),
							'desc'     => __( 'Checking this sets the positioning of the baloonup to fixed.', 'baloonup-maker' ),
							'type'     => 'checkbox',
							'priority' => 50,
						),
					),
					'advanced'  => array(
						'overlay_disabled'   => array(
							'label'    => __( 'Disable Overlay', 'baloonup-maker' ),
							'desc'     => __( 'Checking this will disable and hide the overlay for this baloonup.', 'baloonup-maker' ),
							'type'     => 'checkbox',
							'priority' => 10,
						),
						'stackable'          => array(
							'label'    => __( 'Stackable', 'baloonup-maker' ),
							'desc'     => __( 'This enables other baloonups to remain open.', 'baloonup-maker' ),
							'type'     => 'checkbox',
							'priority' => 20,
						),
						'disable_reposition' => array(
							'label'    => __( 'Disable Repositioning', 'baloonup-maker' ),
							'desc'     => __( 'This will disable automatic repositioning of the baloonup on window resizing.', 'baloonup-maker' ),
							'type'     => 'checkbox',
							'priority' => 30,
						),
						'zindex'             => array(
							'label'    => __( 'BaloonUp Z-Index', 'baloonup-maker' ),
							'desc'     => __( 'Change the z-index layer level for the baloonup.', 'baloonup-maker' ),
							'type'     => 'number',
							'min'      => 999,
							'max'      => 2147483647,
							'std'      => 1999999999,
							'priority' => 40,
						),
					),
				) ),
				'close'     => apply_filters( 'pum_baloonup_close_settings_fields', array(
					'button'            => array(
						'close_text'         => array(
							'label'       => __( 'Close Text', 'baloonup-maker' ),
							'placeholder' => __( 'Close', 'baloonup-maker' ),
							'desc'        => __( 'Override the default close text.', 'baloonup-maker' ),
							'priority'    => 10,
							'private'     => true,
						),
						'close_button_delay' => array(
							'label'    => __( 'Close Button Delay', 'baloonup-maker' ),
							'desc'     => __( 'This delays the display of the close button.', 'baloonup-maker' ),
							'type'     => 'rangeslider',
							'std'      => 0,
							'step'     => 100,
							'min'      => 0,
							'max'      => 3000,
							'unit'     => __( 'ms', 'baloonup-maker' ),
							'priority' => 20,
						),
					),
					'alternate_methods' => array(
						'close_on_overlay_click' => array(
							'label'    => __( 'Click Overlay to Close', 'baloonup-maker' ),
							'desc'     => __( 'Checking this will cause baloonup to close when user clicks on overlay.', 'baloonup-maker' ),
							'type'     => 'checkbox',
							'priority' => 10,
						),
						'close_on_esc_press'     => array(
							'label'    => __( 'Press ESC to Close', 'baloonup-maker' ),
							'desc'     => __( 'Checking this will cause baloonup to close when user presses ESC key.', 'baloonup-maker' ),
							'type'     => 'checkbox',
							'priority' => 20,
						),
						'close_on_f4_press'      => array(
							'label'    => __( 'Press F4 to Close', 'baloonup-maker' ),
							'desc'     => __( 'Checking this will cause baloonup to close when user presses F4 key.', 'baloonup-maker' ),
							'type'     => 'checkbox',
							'priority' => 30,
						),
					),
				) ),
			) );

			foreach ( $tabs as $tab_id => $sections ) {

				foreach ( $sections as $section_id => $fields ) {

					if ( PUM_Admin_Helpers::is_field( $fields ) ) {
						// Allow for flat tabs with no sections.
						$section_id = 'main';
						$fields     = array(
							$section_id => $fields,
						);
					}

					foreach ( $fields as $field_id => $field ) {
						if ( ! is_array( $field ) || ! PUM_Admin_Helpers::is_field( $field ) ) {
							continue;
						}

						if ( empty( $field['id'] ) ) {
							$field['id'] = $field_id;
						}
						if ( empty( $field['name'] ) ) {
							$field['name'] = 'baloonup_settings[' . $field_id . ']';
						}

						$tabs[ $tab_id ][ $section_id ][ $field_id ] = mcms_parse_args( $field, array(
							'section'      => 'main',
							'type'         => 'text',
							'id'           => null,
							'label'        => '',
							'desc'         => '',
							'name'         => null,
							'templ_name'   => null,
							'size'         => 'regular',
							'options'      => array(),
							'std'          => null,
							'rows'         => 5,
							'cols'         => 50,
							'min'          => 0,
							'max'          => 50,
							'force_minmax' => false,
							'step'         => 1,
							'select2'      => null,
							'object_type'  => 'post_type',
							'object_key'   => 'post',
							'post_type'    => null,
							'taxonomy'     => null,
							'multiple'     => null,
							'as_array'     => false,
							'placeholder'  => null,
							'checkbox_val' => 1,
							'allow_blank'  => true,
							'readonly'     => false,
							'required'     => false,
							'disabled'     => false,
							'hook'         => null,
							'unit'         => __( 'ms', 'baloonup-maker' ),
							'units'        => array(
								'px'  => 'px',
								'%'   => '%',
								'em'  => 'em',
								'rem' => 'rem',
							),
							'priority'     => null,
							'doclink'      => '',
							'button_type'  => 'submit',
							'class'        => '',
							'private'      => false,
						) );

					}
				}
			}
		}


		return $tabs;
	}

	public static function get_field( $id ) {
		$tabs = self::fields();

		foreach ( $tabs as $tab => $sections ) {

			if ( PUM_Admin_Helpers::is_field( $sections ) ) {
				$sections = array(
					'main' => array(
						$tab => $sections,
					),
				);
			}

			foreach ( $sections as $section => $fields ) {

				foreach ( $fields as $key => $args ) {
					if ( $key == $id ) {
						return $args;
					}
				}
			}
		}

		return false;
	}

	public static function sanitize_settings( $settings = array() ) {


		foreach ( $settings as $key => $value ) {
			$field = self::get_field( $key );

			if ( is_string( $value ) ) {
				$settings[ $key ] = sanitize_text_field( $value );
			}

			if ( $field ) {
				switch ( $field['type'] ) {
					case 'measure':
						$settings[ $key ] .= $settings[ $key . '_unit' ];
						break;
				}
			} else {
				unset( $settings[ $key ] );
			}


		}

		return $settings;
	}

	/**
	 * @return array
	 */
	public static function defaults() {
		$tabs = self::fields();

		$defaults = array();

		foreach ( $tabs as $tab_id => $sections ) {
			foreach ( $sections as $section_id => $fields ) {
				foreach ( $fields as $key => $field ) {
					if ( $field['type'] == 'checkbox' ) {
						$defaults[ $key ] = isset( $field['std'] ) ? $field['std'] : ( $field['type'] == 'checkbox' ? false : null );
					}
				}
			}
		}

		return $defaults;
	}

	/**
	 * Display analytics metabox
	 *
	 * @return void
	 */
	public static function render_analytics_meta_box() {
		global $post;

		$baloonup = pum_get_baloonup( $post->ID ); ?>
		<div id="pum-baloonup-analytics" class="pum-meta-box">

			<?php do_action( 'pum_baloonup_analytics_metabox_before', $post->ID ); ?>

			<?php
			$opens           = $baloonup->get_event_count( 'open', 'current' );
			//$conversions     = $baloonup->get_event_count( 'conversion', 'current' );
			//$conversion_rate = $opens > 0 && $opens >= $conversions ? $conversions / $opens * 100 : false;
			?>

			<div id="pum-baloonup-analytics" class="pum-baloonup-analytics">

				<table class="form-table">
					<tbody>
					<tr>
						<td><?php _e( 'Opens', 'baloonup-maker' ); ?></td>
						<td><?php echo $opens; ?></td>
					</tr>
					<?php /* if ( $conversion_rate > 0 ) : ?>
						<tr>
							<td><?php _e( 'Conversions', 'baloonup-maker' ); ?></td>
							<td><?php echo $conversions; ?></td>
						</tr>
					<?php endif; */ ?>
					<?php /* if ( $conversion_rate > 0 ) : ?>
						<tr>
							<td><?php _e( 'Conversion Rate', 'baloonup-maker' ); ?></td>
							<td><?php echo round( $conversion_rate, 2 ); ?>%</td>
						</tr>
					<?php endif; */ ?>
					<tr class="separator">
						<td colspan="2">
							<label> <input type="checkbox" name="baloonup_reset_counts" id="baloonup_reset_counts" value="1" />
								<?php _e( 'Reset Counts', 'baloonup-maker' ); ?>
							</label>
							<?php if ( ( $reset = $baloonup->get_last_count_reset() ) ) : ?><br />
								<small>
									<strong><?php _e( 'Last Reset', 'baloonup-maker' ); ?>:</strong> <?php echo date( 'm-d-Y H:i', $reset['timestamp'] ); ?>
									<br /> <strong><?php _e( 'Previous Opens', 'baloonup-maker' ); ?>:</strong> <?php echo $reset['opens']; ?>

									<?php /* if ( $reset['conversions'] > 0 ) : ?>
										<br />
										<strong><?php _e( 'Previous Conversions', 'baloonup-maker' ); ?>:</strong> <?php echo $reset['conversions']; ?>
									<?php endif; */ ?>

									<br /> <strong><?php _e( 'Lifetime Opens', 'baloonup-maker' ); ?>:</strong> <?php echo $baloonup->get_event_count( 'open', 'total' ); ?>

									<?php /* if ( $baloonup->get_event_count( 'conversion', 'total' ) > 0 ) : ?>
										<br />
										<strong><?php _e( 'Lifetime Conversions', 'baloonup-maker' ); ?>:</strong> <?php echo $baloonup->get_event_count( 'conversion', 'total' ); ?>
									<?php endif; */  ?>
								</small>
							<?php endif; ?>
						</td>
					</tr>
					</tbody>
				</table>
			</div>

			<?php do_action( 'pum_baloonup_analytics_metabox_after', $post->ID ); ?>

		</div>

		<?php
	}

	/**
	 * @param array $meta
	 *
	 * @return array
	 */
	public static function sanitize_meta( $meta = array() ) {
		if ( ! empty( $meta ) ) {

			foreach ( $meta as $key => $value ) {

				if ( is_array( $value ) ) {
					$meta[ $key ] = self::sanitize_meta( $value );
				} else if ( is_string( $value ) ) {
					try {
						$value = json_decode( stripslashes( $value ) );
						if ( is_object( $value ) || is_array( $value ) ) {
							$meta[ $key ] = PUM_Admin_Helpers::object_to_array( $value );
						}
					} catch ( \Exception $e ) {
					};
				}

			}
		}

		return $meta;
	}


	/**
	 * Ensures that the baloonups have unique slugs.
	 *
	 * @param $data
	 * @param $postarr
	 *
	 * @return mixed
	 */
	public static function set_slug( $data, $postarr ) {
		if ( $data['post_type'] == 'baloonup' ) {
			$data['post_name'] = mcms_unique_post_slug( sanitize_title( balooncreate_post( 'baloonup_name' ) ), $postarr['ID'], $data['post_status'], $data['post_type'], $data['post_parent'] );
		}

		return $data;
	}


	/**
	 * Defines the custom columns and their order
	 *
	 * @param array $_columns Array of baloonup columns
	 *
	 * @return array $columns Updated array of baloonup columns for
	 *  Post Type List Table
	 */
	public static function dashboard_columns( $_columns ) {
		$columns = array(
			'cb'              => '<input type="checkbox"/>',
			'title'           => __( 'Name', 'baloonup-maker' ),
			'baloonup_title'     => __( 'Title', 'baloonup-maker' ),
			'class'           => __( 'CSS Classes', 'baloonup-maker' ),
			'opens'           => __( 'Opens', 'baloonup-maker' ),
			//'conversions'     => __( 'Conversions', 'baloonup-maker' ),
			//'conversion_rate' => __( 'Conversion Rate', 'baloonup-maker' ),
		);

		// Add the date column preventing our own translation
		if ( ! empty( $_columns['date'] ) ) {
			$columns['date'] = $_columns['date'];
		}

		if ( get_taxonomy( 'baloonup_tag' ) ) {
			$columns['baloonup_tag'] = __( 'Tags', 'baloonup-maker' );
		}

		if ( get_taxonomy( 'baloonup_category' ) ) {
			$columns['baloonup_category'] = __( 'Categories', 'baloonup-maker' );
		}

		// Deprecated filter.
		$columns = apply_filters( 'balooncreate_baloonup_columns', $columns );

		return apply_filters( 'pum_baloonup_columns', $columns );
	}

	/**
	 * Render Columns
	 *
	 * @param string $column_name Column name
	 * @param int    $post_id     (Post) ID
	 */
	public static function render_columns( $column_name, $post_id ) {
		if ( get_post_type( $post_id ) == 'baloonup' ) {

			$baloonup = pum_get_baloonup( $post_id );
			//setup_postdata( $baloonup );

			/**
			 * Uncomment if need to check for permissions on certain columns.
			 *          *
			 * $post_type_object = get_post_type_object( $baloonup->post_type );
			 * $can_edit_post    = current_user_can( $post_type_object->cap->edit_post, $baloonup->ID );
			 */

			switch ( $column_name ) {
				case 'baloonup_title':
					echo esc_html( $baloonup->get_title() );
					break;
				case 'baloonup_category':
					echo get_the_term_list( $post_id, 'baloonup_category', '', ', ', '' );
					break;
				case 'baloonup_tag':
					echo get_the_term_list( $post_id, 'baloonup_tag', '', ', ', '' );
					break;
				case 'class':
					echo '<pre style="display:inline-block;margin:0;"><code>balooncreate-' . absint( $post_id ) . '</code></pre>';
					if ( $baloonup->post_name != $baloonup->ID ) {
						echo '|';
						echo '<pre style="display:inline-block;margin:0;"><code>balooncreate-' . $baloonup->post_name . '</code></pre>';
					}
					break;
				case 'opens':
					if ( ! pum_extension_enabled( 'baloonup-analytics' ) ) {
						echo $baloonup->get_event_count( 'open' );
					}
					break;

				/*
				 case 'conversions':
					if ( ! pum_extension_enabled( 'baloonup-analytics' ) ) {
						echo $baloonup->get_event_count( 'conversion' );
					}
					break;
				case 'conversion_rate':
					$views       = $baloonup->get_event_count( 'view', 'current' );
					$conversions = $baloonup->get_event_count( 'conversion', 'current' );

					$conversion_rate = $views > 0 && $views >= $conversions ? $conversions / $views * 100 : __( 'N/A', 'baloonup-maker' );
					echo round( $conversion_rate, 2 ) . '%';
					break;
				*/
			}
		}
	}

	/**
	 * Registers the sortable columns in the list table
	 *
	 * @param array $columns Array of the columns
	 *
	 * @return array $columns Array of sortable columns
	 */
	public static function sortable_columns( $columns ) {
		$columns['baloonup_title'] = 'baloonup_title';
		$columns['opens']       = 'opens';
		// $columns['conversions'] = 'conversions';

		return $columns;
	}

	/**
	 * Sorts Columns in the List Table
	 *
	 * @param array $vars Array of all the sort variables
	 *
	 * @return array $vars Array of all the sort variables
	 */
	public static function sort_columns( $vars ) {
		// Check if we're viewing the "baloonup" post type
		if ( isset( $vars['post_type'] ) && 'baloonup' == $vars['post_type'] ) {
			// Check if 'orderby' is set to "name"
			if ( isset( $vars['orderby'] ) ) {
				switch ( $vars['orderby'] ) {
					case 'baloonup_title':
						$vars = array_merge( $vars, array(
							'meta_key' => 'baloonup_title',
							'orderby'  => 'meta_value',
						) );
						break;
					case 'opens':
						if ( ! pum_extension_enabled( 'baloonup-analytics' ) ) {
							$vars = array_merge( $vars, array(
								'meta_key' => 'baloonup_open_count',
								'orderby'  => 'meta_value_num',
							) );
						}
						break;
					/*
					case 'conversions':
						if ( ! pum_extension_enabled( 'baloonup-analytics' ) ) {
							$vars = array_merge( $vars, array(
								'meta_key' => 'baloonup_conversion_count',
								'orderby'  => 'meta_value_num',
							) );
						}
						break;
					*/
				}
			}
		}

		return $vars;
	}

	/**
	 * Initialize sorting
	 */
	public static function load() {
		add_filter( 'request', array( __CLASS__, 'sort_columns' ) );
	}

	/**
	 * Add BaloonUp Filters
	 *
	 * Adds taxonomy drop down filters for baloonups.
	 */
	public static function add_baloonup_filters() {
		global $typenow;

		// Checks if the current post type is 'baloonup'
		if ( $typenow == 'baloonup' ) {

			if ( get_taxonomy( 'baloonup_category' ) ) {
				$terms = get_terms( 'baloonup_category' );
				if ( count( $terms ) > 0 ) {
					echo "<select name='baloonup_category' id='baloonup_category' class='postform'>";
					echo "<option value=''>" . __( 'Show all categories', 'baloonup-maker' ) . "</option>";
					foreach ( $terms as $term ) {
						$selected = isset( $_GET['baloonup_category'] ) && $_GET['baloonup_category'] == $term->slug ? 'selected="selected"' : '';
						echo '<option value="' . esc_attr( $term->slug ) . '" ' . $selected . '>' . esc_html( $term->name ) . ' (' . $term->count . ')</option>';
					}
					echo "</select>";
				}
			}

			if ( get_taxonomy( 'baloonup_tag' ) ) {
				$terms = get_terms( 'baloonup_tag' );
				if ( count( $terms ) > 0 ) {
					echo "<select name='baloonup_tag' id='baloonup_tag' class='postform'>";
					echo "<option value=''>" . __( 'Show all tags', 'baloonup-maker' ) . "</option>";
					foreach ( $terms as $term ) {
						$selected = isset( $_GET['baloonup_tag'] ) && $_GET['baloonup_tag'] == $term->slug ? 'selected="selected"' : '';
						echo '<option value="' . esc_attr( $term->slug ) . '" ' . $selected . '>' . esc_html( $term->name ) . ' (' . $term->count . ')</option>';
					}
					echo "</select>";
				}
			}
		}

	}

}

