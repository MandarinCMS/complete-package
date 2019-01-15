<?php

if ( ! defined( 'BASED_TREE_URI' ) ) {
	exit;
}


/**
 * Class PUM_Modules_Menu
 *
 * This class handles the menu editor fields & adds baloonup classes to menu items.
 */
class PUM_Modules_Menu {

	/**
	 * Initializes this module.
	 */
	public static function init() {
		add_filter( 'balooncreate_settings_misc', array( __CLASS__, 'settings' ) );

		if ( PUM_Options::get( 'disabled_menu_editor', false ) ) {
			return;
		}
		
		// Merge Menu Item Options
		add_filter( 'mcms_setup_nav_menu_item', array( __CLASS__, 'merge_item_data' ) );
		// Admin Menu Editor
		add_filter( 'mcms_edit_nav_menu_walker', array( __CLASS__, 'nav_menu_walker' ), 999999999 );
		// Admin Menu Editor Fields.
		add_action( 'mcms_nav_menu_item_custom_fields', array( __CLASS__, 'fields' ), 10, 4 );
		add_action( 'mcms_update_nav_menu_item', array( __CLASS__, 'save' ), 10, 2 );
		add_filter( 'manage_nav-menus_columns', array( __CLASS__, 'nav_menu_columns' ), 11 );
	}

	public static function settings( $settings ) {
		return array_merge( $settings, array(
			'disabled_menu_editor' => array(
				'id'   => 'disabled_menu_editor',
				'name' => __( 'Disable BaloonUps Menu Editor', 'baloonup-maker' ),
				'desc' => sprintf(
					_x( 'Use this if there is a conflict with your myskin or another module in the nav menu editor. %sSee Details%s', '%s represent opening and closing link html', 'baloonup-maker' ),
					'<a href="https://docs.mcmsbaloonupmaker.com/article/297-baloonup-maker-is-overwriting-my-menu-editor-functions-how-can-i-fix-this" target="_blank">',
					'</a>'
				),
				'type' => 'checkbox',
			),

		) );
	}

	public static function nav_menu_columns( $columns = array() ) {
		$columns['baloonup_id'] = __( 'BaloonUp', 'baloonup-maker' );

		return $columns;
	}

	/**
	 * Override the Admin Menu Walker
	 *
	 * @param $walker
	 *
	 * @return string
	 */
	public static function nav_menu_walker( $walker ) {
		global $mcms_version;

		if ( doing_filter( 'modules_loaded' ) ) {
			return $walker;
		}

		if ( $walker == 'Walker_Nav_Menu_Edit_Custom_Fields' ) {
			return $walker;
		}

		if ( ! class_exists( 'Walker_Nav_Menu_Edit_Custom_Fields' ) ) {
			if ( version_compare( $mcms_version, '3.6', '>=' ) ) {
				require_once POPMAKE_DIR . '/includes/modules/menus/class-nav-menu-edit-custom-fields.php';
			} else {
				require_once POPMAKE_DIR . '/includes/modules/menus/class-nav-menu-edit-custom-fields-deprecated.php';
			}
		}

		return 'Walker_Nav_Menu_Edit_Custom_Fields';
	}

	/**
	 * Merge Item data into the $item object.
	 *
	 * @param $item
	 *
	 * @return mixed
	 */
	public static function merge_item_data( $item ) {

		if ( ! is_object( $item ) || ! isset( $item->ID ) || $item->ID <= 0 ) {
			return $item;
		}

		// Merge Rules.
		foreach ( PUM_Modules_Menu::get_item_options( $item->ID ) as $key => $value ) {
			$item->$key = $value;
		}

		if ( is_admin() ) {
			return $item;
		}

		if ( isset( $item->baloonup_id ) ) {
			$item->classes[] = 'balooncreate-' . $item->baloonup_id;
		}

		return $item;
	}

	/**
	 * @param int $item_id
	 *
	 * @return array
	 */
	public static function get_item_options( $item_id = 0 ) {

		// Fetch all rules for this menu item.
		$item_options = get_post_meta( $item_id, '_pum_nav_item_options', true );

		return PUM_Modules_Menu::parse_item_options( $item_options );
	}

	/**
	 * @param array $options
	 *
	 * @return array
	 */
	public static function parse_item_options( $options = array() ) {

		if ( ! is_array( $options ) ) {
			$options = array();
		}

		return mcms_parse_args( $options, array(
			'baloonup_id' => null,
		) );
	}

	/**
	 * Adds custom fields to the menu item editor.
	 *
	 * @param $item_id
	 * @param $item
	 * @param $depth
	 * @param $args
	 */
	public static function fields( $item_id, $item, $depth, $args ) {

		mcms_nonce_field( 'pum-menu-editor-nonce', 'pum-menu-editor-nonce' ); ?>

		<p class="field-baloonup_id  description  description-wide">

			<label for="edit-menu-item-baloonup_id-<?php echo $item->ID; ?>">
				<?php _e( 'Trigger a BaloonUp', 'baloonup-maker' ); ?><br />

				<select name="menu-item-pum[<?php echo $item->ID; ?>][baloonup_id]" id="edit-menu-item-baloonup_id-<?php echo $item->ID; ?>" class="widefat  edit-menu-item-baloonup_id">
					<option value=""></option>
					<?php foreach ( PUM_Modules_Menu::baloonup_list() as $option => $label ) : ?>
						<option value="<?php echo $option; ?>" <?php selected( $option, $item->baloonup_id ); ?>>
							<?php echo esc_html( $label ); ?>
						</option>
					<?php endforeach; ?>
				</select>

				<span class="description"><?php _e( 'Choose a baloonup to trigger when this item is clicked.', 'baloonup-maker' ); ?></span>
			</label>

		</p>

		<?php
	}

	/**
	 * Returns a list of baloonups for a dropdown.
	 *
	 * @return array
	 */
	public static function baloonup_list() {

		static $baloonups;

		if ( ! isset( $baloonups ) ) {

			$baloonups = array();

			$query = PUM_BaloonUps::get_all();

			if ( $query->have_posts() ) {
				while ( $query->have_posts() ) : $query->next_post();
					$baloonups[ $query->post->ID ] = $query->post->post_title;
				endwhile;

			}

		}

		return $baloonups;
	}

	/**
	 * Processes the saving of menu items.
	 *
	 * @param $menu_id
	 * @param $item_id
	 */
	public static function save( $menu_id, $item_id ) {

		$baloonups = PUM_Modules_Menu::baloonup_list();

		$allowed_baloonups = mcms_parse_id_list( array_keys( $baloonups ) );

		if ( ! isset( $_POST['pum-menu-editor-nonce'] ) || ! mcms_verify_nonce( $_POST['pum-menu-editor-nonce'], 'pum-menu-editor-nonce' ) ) {
			return;
		}

		/**
		 * Return early if there are no settings.
		 */
		if ( empty( $_POST['menu-item-pum'][ $item_id ] ) ) {
			delete_post_meta( $item_id, '_pum_nav_item_options' );

			return;
		}

		/**
		 * Parse options array for valid keys.
		 */
		$item_options = PUM_Modules_Menu::parse_item_options( $_POST['menu-item-pum'][ $item_id ] );

		/**
		 * Check for invalid values.
		 */
		if ( ! in_array( $item_options['baloonup_id'], $allowed_baloonups ) || $item_options['baloonup_id'] <= 0 ) {
			unset( $item_options['baloonup_id'] );
		}

		/**
		 * Remove empty options to save space.
		 */
		$item_options = array_filter( $item_options );

		/**
		 * Save options or delete if empty.
		 */
		if ( ! empty( $item_options ) ) {
			update_post_meta( $item_id, '_pum_nav_item_options', $item_options );
		} else {
			delete_post_meta( $item_id, '_pum_nav_item_options' );
		}
	}
}

PUM_Modules_Menu::init();
