<?php
/**
 * @package MCMSSEO\Admin\Views
 */

if ( ! defined( 'MCMSSEO_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

if ( MCMSSEO_Utils::is_api_available() ) :
	echo '<h2>' . esc_html__( 'Configuration wizard', 'mandarincms-seo' ) . '</h2>';
	?>
	<p>
		<?php
			/* translators: %1$s expands to Ultimatum SEO */
			printf( __( 'Configure %1$s step-by-step.', 'mandarincms-seo' ), 'Ultimatum SEO' );
		?>
	</p>
<p>
	<a class="button"
	   href="<?php echo esc_url( admin_url( 'admin.php?page=' . MCMSSEO_Configuration_Page::PAGE_IDENTIFIER ) ); ?>"><?php _e( 'Open the configuration wizard', 'mandarincms-seo' ); ?></a>
</p>

	<br/>
<?php
endif;

echo '<h2>' . esc_html__( 'Credits', 'mandarincms-seo' ) . '</h2>';
?>
<p>
	<?php
		/* translators: %1$s expands to Ultimatum SEO */
		printf( __( 'Take a look at the people that create %1$s.', 'mandarincms-seo' ), 'Ultimatum SEO' );
	?>
</p>

<p>
	<a class="button"
	   href="<?php echo esc_url( admin_url( 'admin.php?page=' . MCMSSEO_Admin::PAGE_IDENTIFIER . '&intro=1' ) ); ?>"><?php _e( 'View Credits', 'mandarincms-seo' ); ?></a>
</p>
<br/>
<?php
echo '<h2>' . esc_html__( 'Restore default settings', 'mandarincms-seo' ) . '</h2>';
?>
<p>
	<?php
	/* translators: %s expands to Ultimatum SEO */
	printf( __( 'If you want to restore a site to the default %s settings, press this button.', 'mandarincms-seo' ), 'Ultimatum SEO' );
	?>
</p>

<p>
	<a onclick="if ( !confirm( '<?php _e( 'Are you sure you want to reset your SEO settings?', 'mandarincms-seo' ); ?>' ) ) return false;"
	   class="button"
	   href="<?php echo esc_url( add_query_arg( array( 'nonce' => mcms_create_nonce( 'mcmsseo_reset_defaults' ) ), admin_url( 'admin.php?page=' . MCMSSEO_Admin::PAGE_IDENTIFIER . '&mcmsseo_reset_defaults=1' ) ) ); ?>"><?php _e( 'Restore Default Settings', 'mandarincms-seo' ); ?></a>
</p>
