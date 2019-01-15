<?php
/**
 * @package MCMSSEO\Admin
 */

?>

<h2 id="mandarincms-seo"><?php
	/* translators: %1$s expands to Ultimatum SEO */
	printf( __( '%1$s settings', 'mandarincms-seo' ), 'Ultimatum SEO' );
	?></h2>
<table class="form-table">
	<tr>
		<th scope="row">
			<label
				for="mcmsseo_author_title"><?php _e( 'Title to use for Author page', 'mandarincms-seo' ); ?></label>
		</th>
		<td>
			<input class="regular-text" type="text" id="mcmsseo_author_title" name="mcmsseo_author_title"
		           value="<?php echo esc_attr( get_the_author_meta( 'mcmsseo_title', $user->ID ) ); ?>"/>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<label
				for="mcmsseo_author_metadesc"><?php _e( 'Meta description to use for Author page', 'mandarincms-seo' ); ?></label>
		</th>
		<td>
			<textarea rows="3" cols="30" id="mcmsseo_author_metadesc"
			          name="mcmsseo_author_metadesc"><?php echo esc_textarea( get_the_author_meta( 'mcmsseo_metadesc', $user->ID ) ); ?></textarea>
		</td>
	</tr>
	<?php if ( $options['usemetakeywords'] === true ) { ?>
		<tr>
			<th scope="row">
				<label
					for="mcmsseo_author_metakey"><?php _e( 'Meta keywords to use for Author page', 'mandarincms-seo' ); ?></label>
			</th>
			<td>
				<input class="regular-text" type="text" id="mcmsseo_author_metakey"
				       name="mcmsseo_author_metakey"
				       value="<?php echo esc_attr( get_the_author_meta( 'mcmsseo_metakey', $user->ID ) ); ?>"/>
			</td>
		</tr>
	<?php } ?>
	<tr>
		<td></td>
		<td>
			<input class="checkbox double" type="checkbox" id="mcmsseo_author_exclude"
			       name="mcmsseo_author_exclude"
			       value="on" <?php echo ( get_the_author_meta( 'mcmsseo_excludeauthorsitemap', $user->ID ) === 'on' ) ? 'checked' : ''; ?> />
			<label class="ultimatum-label-strong"
				for="mcmsseo_author_exclude"><?php _e( 'Exclude user from Author-sitemap', 'mandarincms-seo' ); ?></label>
		</td>
	</tr>

	<?php if ( $options['keyword-analysis-active'] === true ) { ?>
		<tr>
			<td></td>
			<td>
				<input class="checkbox double" type="checkbox" id="mcmsseo_keyword_analysis_disable"
				       name="mcmsseo_keyword_analysis_disable" aria-describedby="mcmsseo_keyword_analysis_disable_desc"
				       value="on" <?php echo ( get_the_author_meta( 'mcmsseo_keyword_analysis_disable', $user->ID ) === 'on' ) ? 'checked' : ''; ?> />
				<label class="ultimatum-label-strong"
					for="mcmsseo_keyword_analysis_disable"><?php _e( 'Disable SEO analysis', 'mandarincms-seo' ); ?></label>
				<p class="description" id="mcmsseo_keyword_analysis_disable_desc">
					<?php _e( 'Removes the keyword tab from the metabox and disables all SEO-related suggestions.', 'mandarincms-seo' ); ?>
				</p>
			</td>
		</tr>

	<?php } ?>

	<?php if ( $options['content-analysis-active'] === true ) { ?>
		<tr>
			<td></td>
			<td>
				<input class="checkbox double" type="checkbox" id="mcmsseo_content_analysis_disable"
				       name="mcmsseo_content_analysis_disable" aria-describedby="mcmsseo_content_analysis_disable_desc"
				       value="on" <?php echo ( get_the_author_meta( 'mcmsseo_content_analysis_disable', $user->ID ) === 'on' ) ? 'checked' : ''; ?> />
				<label class="ultimatum-label-strong"
					for="mcmsseo_content_analysis_disable"><?php _e( 'Disable readability analysis', 'mandarincms-seo' ); ?></label>
				<p class="description" id="mcmsseo_content_analysis_disable_desc">
					<?php _e( 'Removes the readability tab from the metabox and disables all readability-related suggestions.', 'mandarincms-seo' ); ?>
				</p>
			</td>
		</tr>
	<?php } ?>
</table>
<br/><br/>
