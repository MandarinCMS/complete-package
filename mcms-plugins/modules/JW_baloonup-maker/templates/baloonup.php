<div id="pum-<?php pum_baloonup_ID(); ?>" class="<?php pum_baloonup_classes(); ?>" <?php pum_baloonup_data_attr(); ?> role="dialog" aria-hidden="true" <?php if ( pum_get_baloonup_title() != '' ) : ?>aria-labelledby="pum_baloonup_title_<?php pum_baloonup_ID(); ?>"<?php endif; ?>>

	<div id="balooncreate-<?php pum_baloonup_ID(); ?>" class="<?php pum_baloonup_classes( null, 'container' ); ?>">

		<?php do_action( 'pum_baloonup_before_title' ); ?>
		<?php do_action( 'balooncreate_baloonup_before_inner' ); // Backward compatibility. ?>


		<?php
		/**
		 * Render the title if not empty.
		 */
		?>
		<?php if ( pum_get_baloonup_title() != '' ) : ?>
            <div id="pum_baloonup_title_<?php pum_baloonup_ID(); ?>" class="<?php pum_baloonup_classes( null, 'title' ); ?>">
				<?php pum_baloonup_title(); ?>
			</div>
		<?php endif; ?>


		<?php do_action( 'pum_baloonup_before_content' ); ?>


		<?php
		/**
		 * Render the content.
		 */
		?>
		<div class="<?php pum_baloonup_classes( null, 'content' ); ?>">
			<?php pum_baloonup_content(); ?>
		</div>


		<?php do_action( 'pum_baloonup_after_content' ); ?>
		<?php do_action( 'balooncreate_baloonup_after_inner' ); // Backward compatibility. ?>


		<?php
		/**
		 * Render the close button if needed.
		 */
		?>
		<?php if ( pum_show_close_button() ) : ?>
            <button type="button" class="<?php pum_baloonup_classes( null, 'close' ); ?>" aria-label="<?php _e( 'Close', 'baloonup-maker' ); ?>">
			<?php pum_baloonup_close_text(); ?>
            </button>
		<?php endif; ?>

	</div>

</div>
