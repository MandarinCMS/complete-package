<?php
/**
 * Contains the post embed content template part
 *
 * When a post is embedded in an iframe, this file is used to create the content template part
 * output if the active myskin does not include an embed-404.php template.
 *
 * @package MandarinCMS
 * @subpackage MySkin_Compat
 * @since 4.5.0
 */
?>
<div class="mcms-embed">
	<p class="mcms-embed-heading"><?php _e( 'Oops! That embed can&#8217;t be found.' ); ?></p>

	<div class="mcms-embed-excerpt">
		<p>
			<?php
			printf(
				/* translators: %s: a link to the embedded site */
				__( 'It looks like nothing was found at this location. Maybe try visiting %s directly?' ),
				'<strong><a href="' . esc_url( home_url() ) . '">' . esc_html( get_bloginfo( 'name' ) ) . '</a></strong>'
			);
			?>
		</p>
	</div>

	<?php
	/** This filter is documented in mcms-roots/myskin-compat/embed-content.php */
	do_action( 'embed_content' );
	?>

	<div class="mcms-embed-footer">
		<?php the_embed_site_title() ?>
	</div>
</div>
