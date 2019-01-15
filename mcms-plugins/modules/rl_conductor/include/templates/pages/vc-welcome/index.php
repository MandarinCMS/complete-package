<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}
preg_match( '/^(\d+)(\.\d+)?/', MCMSB_VC_VERSION, $matches );
?>
<div class="wrap vc-page-welcome about-wrap">
	<h1><?php echo sprintf( __( 'Welcome to RazorLeaf Conductor %s', 'rl_conductor' ), isset( $matches[0] ) ? $matches[0] : MCMSB_VC_VERSION ) ?></h1>

	<div class="about-text">
		<?php _e( 'Congratulations! You are about to use most powerful time saver for MandarinCMS ever - page builder module with Frontend and Backend editors by MCMSBakery.', 'rl_conductor' ) ?>
	</div>
	<div class="mcms-badge vc-page-logo">
		<?php echo sprintf( __( 'Version %s', 'rl_conductor' ), MCMSB_VC_VERSION ) ?>
	</div>
	<p class="vc-page-actions">
		<?php if ( vc_user_access()
				->mcmsAny( 'manage_options' )
				->part( 'settings' )
				->can( 'vc-general-tab' )
				->get() && ( ! is_multisite() || ! is_main_site() )
		) : ?>
			<a href="<?php echo esc_attr( admin_url( 'admin.php?page=vc-general' ) ) ?>"
			class="button button-primary"><?php _e( 'Settings', 'rl_conductor' ) ?></a><?php endif; ?>
		<a href="https://twitter.com/share" class="twitter-share-button"
			data-via="mcmsbakery"
			data-text="Take full control over your #MandarinCMS site with RazorLeaf Conductor page builder"
			data-url="http://jiiworks.net" data-size="large">Tweet</a>
		<script>! function ( d, s, id ) {
				var js, fjs = d.getElementsByTagName( s )[ 0 ], p = /^http:/.test( d.location ) ? 'http' : 'https';
				if ( ! d.getElementById( id ) ) {
					js = d.createElement( s );
					js.id = id;
					js.src = p + '://platform.twitter.com/widgets.js';
					fjs.parentNode.insertBefore( js, fjs );
				}
			}( document, 'script', 'twitter-wjs' );</script>
	</p>
	<?php vc_include_template( '/pages/partials/_tabs.php', array(
			'slug' => $page->getSlug(),
			'active_tab' => $active_page->getSlug(),
			'tabs' => $pages,
		) );
	?>
	<?php echo $active_page->render(); ?>
</div>
