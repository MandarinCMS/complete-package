<?php
/**
 * @package MCMSSEO\Admin
 */

if ( ! defined( 'MCMSSEO_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

$yform                = Ultimatum_Form::get_instance();
$yform->currentoption = 'mcmsseo_internallinks';

echo '<h2>' . __( 'Breadcrumbs settings', 'mandarincms-seo' ) . '</h2>';

if ( ! current_myskin_supports( 'ultimatum-seo-breadcrumbs' ) ) {
	$yform->light_switch( 'breadcrumbs-enable', __( 'Enable Breadcrumbs', 'mandarincms-seo' ) );
	echo '<br/>';
}
echo '<div id="breadcrumbsinfo">';
$yform->textinput( 'breadcrumbs-sep', __( 'Separator between breadcrumbs', 'mandarincms-seo' ) );
$yform->textinput( 'breadcrumbs-home', __( 'Anchor text for the Homepage', 'mandarincms-seo' ) );
$yform->textinput( 'breadcrumbs-prefix', __( 'Prefix for the breadcrumb path', 'mandarincms-seo' ) );
$yform->textinput( 'breadcrumbs-archiveprefix', __( 'Prefix for Archive breadcrumbs', 'mandarincms-seo' ) );
$yform->textinput( 'breadcrumbs-searchprefix', __( 'Prefix for Search Page breadcrumbs', 'mandarincms-seo' ) );
$yform->textinput( 'breadcrumbs-404crumb', __( 'Breadcrumb for 404 Page', 'mandarincms-seo' ) );
echo '<br/>';
if ( get_option( 'show_on_front' ) == 'page' && get_option( 'page_for_posts' ) > 0 ) {
	$yform->toggle_switch( 'breadcrumbs-blog-remove', array(
		'off' => __( 'Show', 'mandarincms-seo' ),
		'on'  => __( 'Hide', 'mandarincms-seo' ),
	), __( 'Show Blog page', 'mandarincms-seo' ) );
}
$yform->toggle_switch( 'breadcrumbs-boldlast', array(
	'on'  => __( 'Bold', 'mandarincms-seo' ),
	'off' => __( 'Regular', 'mandarincms-seo' ),
), __( 'Bold the last page', 'mandarincms-seo' ) );
echo '<br/><br/>';

$post_types = get_post_types( array( 'public' => true ), 'objects' );
if ( is_array( $post_types ) && $post_types !== array() ) {
	echo '<h2>' . __( 'Taxonomy to show in breadcrumbs for post types', 'mandarincms-seo' ) . '</h2>';
	foreach ( $post_types as $pt ) {
		$taxonomies = get_object_taxonomies( $pt->name, 'objects' );
		if ( is_array( $taxonomies ) && $taxonomies !== array() ) {
			$values = array( 0 => __( 'None', 'mandarincms-seo' ) );
			foreach ( $taxonomies as $tax ) {
				$values[ $tax->name ] = $tax->labels->singular_name;
			}
			$yform->select( 'post_types-' . $pt->name . '-maintax', $pt->labels->name, $values );
			unset( $values, $tax );
		}
		unset( $taxonomies );
	}
	unset( $pt );
}
echo '<br/>';

$taxonomies = get_taxonomies( array( 'public' => true, '_builtin' => false ), 'objects' );
if ( is_array( $taxonomies ) && $taxonomies !== array() ) {
	echo '<h2>' . __( 'Post type archive to show in breadcrumbs for taxonomies', 'mandarincms-seo' ) . '</h2>';
	foreach ( $taxonomies as $tax ) {
		$values = array( 0 => __( 'None', 'mandarincms-seo' ) );
		if ( get_option( 'show_on_front' ) == 'page' && get_option( 'page_for_posts' ) > 0 ) {
			$values['post'] = __( 'Blog', 'mandarincms-seo' );
		}

		if ( is_array( $post_types ) && $post_types !== array() ) {
			foreach ( $post_types as $pt ) {
				if ( $pt->has_archive ) {
					$values[ $pt->name ] = $pt->labels->name;
				}
			}
			unset( $pt );
		}
		$yform->select( 'taxonomy-' . $tax->name . '-ptparent', $tax->labels->singular_name, $values );
		unset( $values, $tax );
	}
}
unset( $taxonomies, $post_types );

?>
<br class="clear"/>
</div>
<h2><?php _e( 'How to insert breadcrumbs in your myskin', 'mandarincms-seo' ); ?></h2>
<p>
	<?php
	/* translators: %1$s / %2$s: links to the breadcrumbs implementation page on the Ultimatum knowledgebase */
	printf( __( 'Usage of this breadcrumbs feature is explained in %1$sour knowledge-base article on breadcrumbs implementation%2$s.', 'mandarincms-seo' ), '<a href="http://jiiworks.net/breadcrumbs" target="_blank">', '</a>' );
	?>
</p>
