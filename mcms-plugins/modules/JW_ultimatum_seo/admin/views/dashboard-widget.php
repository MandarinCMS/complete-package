<?php
/**
 * @package MCMSSEO\Admin
 *
 * @var array $statistics {
 *      An array of statistics to display
 *
 *      @type string $seo_rank The SEO rank that this item represents
 *      @type string $title The title for this statistic
 *      @type string $class The class for the link
 *      @type int $count The amount of posts that meets the statistic requirements
 * }
 */

?>
<p><?php _e( 'Below are your published posts&#8217; SEO scores. Now is as good a time as any to start improving some of your posts!', 'mandarincms-seo' ); ?></p>
<ul class="mcmsseo-dashboard-overview-scores">
	<?php foreach ( $statistics as $statistic ) :
		if ( current_user_can( 'edit_others_posts' ) === false ) {
			$url = esc_url( admin_url( 'edit.php?post_status=publish&post_type=post&seo_filter=' . $statistic['seo_rank'] . '&author=' . get_current_user_id() ) );
		}
		else {
			$url = esc_url( admin_url( 'edit.php?post_status=publish&post_type=post&seo_filter=' . $statistic['seo_rank'] ) );
		}
	?>
	<li>
		<span class="mcmsseo-dashboard-overview-post-score">
			<span class="mcmsseo-score-icon <?php echo sanitize_html_class( $statistic['icon_class'] ); ?>"></span>
			<a href="<?php echo $url; ?>"
				class="mcmsseo-glance <?php echo esc_attr( $statistic['class'] ); ?>">
				<?php printf( $statistic['title'], intval( $statistic['count'] ) ); ?>
				<span class="screen-reader-text">(<?php echo absint( $statistic['count'] ); ?>)</span>
			</a>
		</span>
		<span class="mcmsseo-dashboard-overview-post-count" aria-hidden="true">
			<?php echo absint( $statistic['count'] ); ?>
		</td>
	</li>
	<?php endforeach; ?>
</ul>
<?php $can_access = is_multisite() ? MCMSSEO_Utils::grant_access() : current_user_can( 'manage_options' );
if ( ! empty( $onpage ) && $can_access ) : ?>
<div class="onpage">
	<h3><?php
		printf(
			/* translators: 1: expands to OnPage.org */
			__( 'Indexability check by %1$s', 'mandarincms-seo' ),
			'OnPage.org'
		);
	?></h3>

	<p>
		<?php
		/**
		 * @var array $onpage Array containing the indexable and can_fetch value.
		 */
		switch ( $onpage['indexable'] ) :
			case MCMSSEO_OnPage_Option::IS_INDEXABLE :
				echo '<span class="mcmsseo-score-icon good"></span>';
				_e( 'Your homepage can be indexed by search engines.', 'mandarincms-seo' );

				break;
			case MCMSSEO_OnPage_Option::IS_NOT_INDEXABLE :
				echo '<span class="mcmsseo-score-icon bad"></span>';
				printf(
					/* translators: 1: opens a link to a related knowledge base article. 2: closes the link */
					__( '%1$sYour homepage cannot be indexed by search engines%2$s. This is very bad for SEO and should be fixed.', 'mandarincms-seo' ),
					'<a href="https://jiiworks.net/onpageindexerror" target="_blank">',
					'</a>'
				);
				break;
			case MCMSSEO_OnPage_Option::CANNOT_FETCH :
				echo '<span class="mcmsseo-score-icon na"></span>';
				printf(
					/* translators: 1: opens a link to a related knowledge base article. 2: closes the link */
					__( '%1$sUltimatum SEO has not been able to fetch your site’s indexability status%2$s from OnPage.org', 'mandarincms-seo' ),
					'<a href="https://jiiworks.net/onpagerequestfailed" target="_blank">',
					'</a>'
				);
				break;
			case MCMSSEO_OnPage_Option::NOT_FETCHED :
				echo '<span class="mcmsseo-score-icon na"></span>';
				esc_html_e( 'Ultimatum SEO has not fetched your site’s indexability status yet from OnPage.org', 'mandarincms-seo' );
				break;
		endswitch;
		?>
	</p>

	<p>
		<?php
		if ( $onpage['indexable'] !== MCMSSEO_OnPage_Option::IS_INDEXABLE && $onpage['can_fetch'] ) :
			echo '<a class="fetch-status button" href="' . esc_attr( add_query_arg( 'mcmsseo-redo-onpage', '1' ) ) . '#mcmsseo-dashboard-overview">' . __( 'Fetch the current status', 'mandarincms-seo' ) . ' </a> ';
		endif;

		echo '<a class="landing-page button" href="https://onpage.org/ultimatum-indexability/" target="_blank">' . __( 'Analyze entire site', 'mandarincms-seo' ) . ' </a>';
		?>
	</p>
</div>
	<?php
endif;
