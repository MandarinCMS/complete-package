<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.mandarincms.org/myskins/basics/template-files/#template-partials
 *
 * @package JMD_MandarinCMS
 */

?>

			</div><!-- #content -->
		</div>

		<footer id="colophon" class="footer" itemscope itemtype="<?php echo esc_url('http://schema.org/MCMSFooter'); ?>">
			<div class="footer-top">
				<div class="container">
					<div class="row">
						<?php if(!function_exists('dynamic_sidebar') || !dynamic_sidebar('footer-widget')) ?>
					</div>
				</div>
			</div>
			<div class="footer-bot">
				<div class="container">
					<div class="row">
						<div class="col-sm-6">
							<div class="footer-copyright"><?php echo esc_html(get_myskin_mod('footer_copyright')); ?></div>
						</div>
						<div class="col-sm-6">
							<div class="author-credits">
								<?php esc_html_e('Developed by', 'jmd-worldcast'); ?> <a href="<?php echo esc_url('https://github.com/JiiSaaduddin/'); ?>"><?php esc_html_e('Dessign', 'jmd-worldcast'); ?></a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</footer><!-- #colophon -->

</div><!-- #page -->

<?php mcms_footer(); ?>

</body>
</html>
