<?php
/**
 * Metabox Functions
 *
 * @package     POPMAKE
 * @subpackage  Admin/mySkins
 * @copyright   Copyright (c) 2014, Wizard Internet Solutions
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 1.0
 */

// Exit if accessed directly
if ( ! defined( 'BASED_TREE_URI' ) ) {
	exit;
}

/** All mySkins *****************************************************************/

/**
 * Register all the meta boxes for the mySkin custom post type
 *
 * @since 1.0
 * @return void
 */
function balooncreate_add_baloonup_myskin_meta_box() {

	/** Preview Window **/
	add_meta_box( 'balooncreate_baloonup_myskin_preview', __( 'mySkin Preview', 'baloonup-maker' ), 'balooncreate_render_baloonup_myskin_preview_meta_box', 'baloonup_myskin', 'side', 'low' );

	/** Overlay Meta **/
	add_meta_box( 'balooncreate_baloonup_myskin_overlay', __( 'Overlay Settings', 'baloonup-maker' ), 'balooncreate_render_baloonup_myskin_overlay_meta_box', 'baloonup_myskin', 'normal', 'high' );

	/** Container Meta **/
	add_meta_box( 'balooncreate_baloonup_myskin_container', __( 'Container Settings', 'baloonup-maker' ), 'balooncreate_render_baloonup_myskin_container_meta_box', 'baloonup_myskin', 'normal', 'high' );

	/** Title Meta **/
	add_meta_box( 'balooncreate_baloonup_myskin_title', __( 'Title Settings', 'baloonup-maker' ), 'balooncreate_render_baloonup_myskin_title_meta_box', 'baloonup_myskin', 'normal', 'high' );

	/** Content Meta **/
	add_meta_box( 'balooncreate_baloonup_myskin_content', __( 'Content Settings', 'baloonup-maker' ), 'balooncreate_render_baloonup_myskin_content_meta_box', 'baloonup_myskin', 'normal', 'high' );

	/** Close Meta **/
	add_meta_box( 'balooncreate_baloonup_myskin_close', __( 'Close Settings', 'baloonup-maker' ), 'balooncreate_render_baloonup_myskin_close_meta_box', 'baloonup_myskin', 'normal', 'high' );

}

add_action( 'add_meta_boxes', 'balooncreate_add_baloonup_myskin_meta_box' );


function balooncreate_baloonup_myskin_meta_fields() {
	$fields = array(
		'baloonup_myskin_defaults_set',
	);
	foreach ( balooncreate_baloonup_myskin_meta_field_groups() as $group ) {
		foreach ( apply_filters( 'balooncreate_baloonup_myskin_meta_field_group_' . $group, array() ) as $field ) {
			$fields[] = 'baloonup_myskin_' . $group . '_' . $field;
		}
	}

	return apply_filters( 'balooncreate_baloonup_myskin_meta_fields', $fields );
}


function balooncreate_baloonup_myskin_meta_field_groups() {
	return apply_filters( 'balooncreate_baloonup_myskin_meta_field_groups', array() );
}


/**
 * Save post meta when the save_post action is called
 *
 * @since 1.0
 *
 * @param int $post_id mySkin (Post) ID
 *
 * @global array $post All the data of the the current post
 * @return void
 */
function balooncreate_baloonup_myskin_meta_box_save( $post_id, $post ) {

	if ( isset( $post->post_type ) && 'baloonup_myskin' != $post->post_type ) {
		return;
	}

	if ( ! isset( $_POST['balooncreate_baloonup_myskin_meta_box_nonce'] ) || ! mcms_verify_nonce( $_POST['balooncreate_baloonup_myskin_meta_box_nonce'], basename( __FILE__ ) ) ) {
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

	$field_prefix = Popmake_BaloonUp_mySkin_Fields::instance()->field_prefix;

	foreach ( Popmake_BaloonUp_mySkin_Fields::instance()->get_all_fields() as $section => $fields ) {

		$section_prefix = "{$field_prefix}{$section}";

		$meta_values = array();

		foreach ( $fields as $field => $args ) {

			$field_name = "{$section_prefix}_{$field}";

			if ( isset( $_POST[ $field_name ] ) ) {
				$meta_values[ $field ] = apply_filters( 'balooncreate_metabox_save_' . $field_name, $_POST[ $field_name ] );
			}

		}

		update_post_meta( $post_id, "baloonup_myskin_{$section}", $meta_values );

	}


	foreach ( balooncreate_baloonup_myskin_meta_fields() as $field ) {
		if ( isset( $_POST[ $field ] ) ) {
			$new = apply_filters( 'balooncreate_metabox_save_' . $field, $_POST[ $field ] );
			update_post_meta( $post_id, $field, $new );
		} else {
			delete_post_meta( $post_id, $field );
		}
	}

	// If this is a built in myskin and the user has modified it set a key so that we know not to make automatic upgrades to it in the future.
	if ( get_post_meta( $post_id, '_pum_built_in', true ) !== false ) {
		update_post_meta( $post_id, '_pum_user_modified', true );
	}

	pum_force_myskin_css_refresh();

	do_action( 'balooncreate_save_baloonup_myskin', $post_id, $post );
}

add_action( 'save_post', 'balooncreate_baloonup_myskin_meta_box_save', 10, 2 );


/** mySkin Configuration *****************************************************************/

/**
 * mySkin Preview Metabox
 *
 * Extensions (as well as the core module) can add items to the myskin preview
 * configuration metabox via the `balooncreate_baloonup_myskin_preview_meta_box_fields` action.
 *
 * @since 1.0
 * @return void
 */
function balooncreate_render_baloonup_myskin_preview_meta_box() { ?>
	<div class="empreview">
		<div id="PopMake-Preview">
			<div class="example-baloonup-overlay"></div>
			<div class="example-baloonup">
				<div class="title"><?php _e( 'Title Text', 'baloonup-maker' ); ?></div>
				<div class="content">
					<?php do_action( 'balooncreate_example_baloonup_content' ); ?>
				</div>
				<a class="close-baloonup">&#215;</a>
			</div>
			<p class="pum-desc"><?php
				$tips = array(
					__( 'If you move this myskin preview to the bottom of your sidebar here it will follow you down the page?', 'baloonup-maker' ),
					__( 'Clicking on an element in this myskin preview will take you to its relevant settings in the editor?', 'baloonup-maker' ),
				);
				$key  = array_rand( $tips, 1 ); ?>
				<i class="dashicons dashicons-info"></i> <?php echo '<strong>' . __( 'Did you know:', 'baloonup-maker' ) . '</strong>  ' . $tips[ $key ]; ?>
			</p>
		</div>
	</div>

	<?php
}


/**
 * mySkin Overlay Metabox
 *
 * Extensions (as well as the core module) can add items to the myskin overlay
 * configuration metabox via the `balooncreate_baloonup_myskin_overlay_meta_box_fields` action.
 *
 * @since 1.0
 * @return void
 */
function balooncreate_render_baloonup_myskin_overlay_meta_box() {
	global $post, $balooncreate_options;
	mcms_nonce_field( basename( __FILE__ ), 'balooncreate_baloonup_myskin_meta_box_nonce' ); ?>
	<input type="hidden" name="baloonup_myskin_defaults_set" value="true"/>
	<div id="balooncreate_baloonup_myskin_overlay_fields" class="balooncreate_meta_table_wrap">
	<table class="form-table">
		<tbody>
		<?php do_action( 'balooncreate_baloonup_myskin_overlay_meta_box_fields', $post->ID ); ?>
		</tbody>
	</table>
	</div><?php
}


/**
 * mySkin Container Metabox
 *
 * Extensions (as well as the core module) can add items to the myskin container
 * configuration metabox via the `balooncreate_baloonup_myskin_container_meta_box_fields` action.
 *
 * @since 1.0
 * @return void
 */
function balooncreate_render_baloonup_myskin_container_meta_box() {
	global $post, $balooncreate_options; ?>
	<div id="balooncreate_baloonup_myskin_container_fields" class="balooncreate_meta_table_wrap">
	<table class="form-table">
		<tbody>
		<?php do_action( 'balooncreate_baloonup_myskin_container_meta_box_fields', $post->ID ); ?>
		</tbody>
	</table>
	</div><?php
}


/**
 * mySkin Title Metabox
 *
 * Extensions (as well as the core module) can add items to the myskin title
 * configuration metabox via the `balooncreate_baloonup_myskin_title_meta_box_fields` action.
 *
 * @since 1.0
 * @return void
 */
function balooncreate_render_baloonup_myskin_title_meta_box() {
	global $post, $balooncreate_options; ?>
	<div id="balooncreate_baloonup_myskin_title_fields" class="balooncreate_meta_table_wrap">
	<table class="form-table">
		<tbody>
		<?php do_action( 'balooncreate_baloonup_myskin_title_meta_box_fields', $post->ID ); ?>
		</tbody>
	</table>
	</div><?php
}


/**
 * mySkin Content Metabox
 *
 * Extensions (as well as the core module) can add items to the myskin content
 * configuration metabox via the `balooncreate_baloonup_myskin_content_meta_box_fields` action.
 *
 * @since 1.0
 * @return void
 */
function balooncreate_render_baloonup_myskin_content_meta_box() {
	global $post, $balooncreate_options; ?>
	<div id="balooncreate_baloonup_myskin_content_fields" class="balooncreate_meta_table_wrap">
	<table class="form-table">
		<tbody>
		<?php do_action( 'balooncreate_baloonup_myskin_content_meta_box_fields', $post->ID ); ?>
		</tbody>
	</table>
	</div><?php
}


/**
 * mySkin Close Metabox
 *
 * Extensions (as well as the core module) can add items to the baloonup close
 * configuration metabox via the `balooncreate_baloonup_myskin_close_meta_box_fields` action.
 *
 * @since 1.0
 * @return void
 */
function balooncreate_render_baloonup_myskin_close_meta_box() {
	global $post, $balooncreate_options; ?>
	<div id="balooncreate_baloonup_myskin_close_fields" class="balooncreate_meta_table_wrap">
	<table class="form-table">
		<tbody>
		<?php do_action( 'balooncreate_baloonup_myskin_close_meta_box_fields', $post->ID ); ?>
		</tbody>
	</table>
	</div><?php
}
