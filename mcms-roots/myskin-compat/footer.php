<?php
/**
 * @package MandarinCMS
 * @subpackage MySkin_Compat
 * @deprecated 3.0.0
 *
 * This file is here for backward compatibility with old myskins and will be removed in a future version
 */
_deprecated_file(
	/* translators: %s: template name */
	sprintf( __( 'MySkin without %s' ), basename( __FILE__ ) ),
	'3.0.0',
	null,
	/* translators: %s: template name */
	sprintf( __( 'Please include a %s template in your myskin.' ), basename( __FILE__ ) )
);
?>

<hr />
<div id="footer" role="contentinfo">
<!-- If you'd like to support MandarinCMS, having the "powered by" link somewhere on your blog is the best way; it's our only promotion or advertising. -->
	<p>
		<?php
		printf(
			/* translators: 1: blog name, 2: MandarinCMS */
			__( '%1$s is proudly powered by %2$s' ),
			get_bloginfo('name'),
			'<a href="https://mandarincms.com/">MandarinCMS</a>'
		);
		?>
	</p>
</div>
</div>

<!-- Gorgeous design by Michael Heilemann - http://binarybonsai.com/kubrick/ -->
<?php /* "Just what do you think you're doing Dave?" */ ?>

		<?php mcms_footer(); ?>
</body>
</html>
