<?php
/**
 * @package MCMSSEO\Admin
 */

if ( ! defined( 'MCMSSEO_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}
?>

<script type="text/html" id="tmpl-primary-term-input">
	<input type="hidden" class="ultimatum-mcmsseo-primary-term"
	       id="ultimatum-mcmsseo-primary-{{data.taxonomy.name}}"
	       name="<?php echo MCMSSEO_Meta::$form_prefix; ?>primary_{{data.taxonomy.name}}_term"
	       value="{{data.taxonomy.primary}}">

	<?php mcms_nonce_field( 'save-primary-term', MCMSSEO_Meta::$form_prefix . 'primary_{{data.taxonomy.name}}_nonce' ); ?>
</script>

<script type="text/html" id="tmpl-primary-term-ui">
	<?php
		printf(
			'<button type="button" class="mcmsseo-make-primary-term" aria-label="%1$s">%2$s</button>',
			esc_attr( sprintf(
				/* translators: accessibility text. %1$s expands to the term title, %2$s to the taxonomy title. */
				__( 'Make %1$s primary %2$s', 'mandarincms-seo' ),
				'{{data.term}}',
				'{{data.taxonomy.title}}'
			) ),
			__( 'Make primary', 'mandarincms-seo' )
		);
		?>

	<span class="mcmsseo-is-primary-term" aria-hidden="true"><?php _e( 'Primary', 'mandarincms-seo' ); ?></span>
</script>

<script type="text/html" id="tmpl-primary-term-screen-reader">
	<span class="screen-reader-text mcmsseo-primary-category-label"><?php
		printf(
			/* translators: %s is the taxonomy title. This will be shown to screenreaders */
			'(' . __( 'Primary %s', 'mandarincms-seo' ) . ')',
			'{{data.taxonomy.title}}'
		);
		?></span>
</script>
