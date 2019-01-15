<?php
/**
 * @package MCMSSEO\Admin
 */

if ( ! defined( 'MCMSSEO_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

$version = '3.4';

/**
 * Display a list of contributors
 *
 * @param array $contributors Contributors' data, associative by GitHub username.
 */
function mcmsseo_display_contributors( $contributors ) {
	foreach ( $contributors as $username => $dev ) {
		echo '<li class="mcms-person" id="mcms-person-', $username, '">';
		echo '<a href="https://github.com/', $username, '" class="web"><img src="//gravatar.com/avatar/', $dev->gravatar, '?s=60" class="gravatar" alt="">', $dev->name, '</a>';
		echo '<span class="title">', $dev->role, "</span></li>\n";
	}
}

?>

<div class="wrap about-wrap">

	<h1><?php
		/* translators: %1$s expands to Ultimatum SEO */
		printf( __( 'Thank you for updating %1$s!', 'mandarincms-seo' ), 'Ultimatum SEO' );
		?></h1>

	<p class="about-text">
		<?php
		/* translators: %1$s and %2$s expands to anchor tags, %3$s expands to Ultimatum SEO */
		printf( __( 'While most of the development team is at %1$sUltimatum%2$s in the Netherlands, %3$s is created by a worldwide team.', 'mandarincms-seo' ), '<a target="_blank" href="https://jiiworks.net/">', '</a>', 'Ultimatum SEO' );
		echo ' ';
		printf( __( 'Want to help us develop? Read our %1$scontribution guidelines%2$s!', 'mandarincms-seo' ), '<a target="_blank" href="https://jiiworks.net/mcmsseocontributionguidelines">', '</a>' );
		?>
	</p>

	<div class="mcms-badge"></div>

	<h2 class="nav-tab-wrapper" id="mcmsseo-tabs">
		<a class="nav-tab" href="#top#credits" id="credits-tab"><?php _e( 'Credits', 'mandarincms-seo' ); ?></a>
		<a class="nav-tab" href="#top#integrations" id="integrations-tab"><?php _e( 'Integrations', 'mandarincms-seo' ); ?></a>
	</h2>

	<div id="credits" class="mcmsseotab">
		<h2 class="screen-reader-text"><?php esc_html_e( 'Team and contributors', 'mandarincms-seo' ); ?></h2>

		<h3 class="mcms-people-group"><?php _e( 'Product Management', 'mandarincms-seo' ); ?></h3>
		<ul class="mcms-people-group " id="mcms-people-group-project-leaders">
			<?php
			$people = array(
				'jiisaaduddin'     => (object) array(
					'name'     => 'Jii Saaduddin',
					'role'     => __( 'Project Lead', 'mandarincms-seo' ),
					'gravatar' => 'f08c3c3253bf14b5616b4db53cea6b78',
				),
			);

			mcmsseo_display_contributors( $people );
			?>
		</ul>
		<h3 class="mcms-people-group"><?php _e( 'Development Leaders', 'mandarincms-seo' ); ?></h3>
		<ul class="mcms-people-group " id="mcms-people-group-development-leaders">
			<?php
			$people = array(
				'jiisaaduddin' => (object) array(
					'name'     => 'Jii Saaduddin',
					'role'     => __( 'CO-Founder', 'mandarincms-seo' ),
					'gravatar' => '86aaa606a1904e7e0cf9857a663c376e',
				),
			);

			mcmsseo_display_contributors( $people );
			?>
		</ul>
		 
	</div>

	<div id="integrations" class="mcmsseotab">
		<h2>Ultimatum SEO Integrations</h2>
		<p class="about-description">
			Ultimatum SEO 3.0 brought a way for myskin builders and custom field modules to integrate with Ultimatum SEO. These
			integrations make sure that <em>all</em> the data on your page is used for the content analysis. On this
			page, we highlight the frameworks that have nicely working integrations.
		</p>
 

		<h3>Other integrations</h3>
		<p class="about-description">
			We've got another integration we'd like to tell you about:
		</p>

		<ol>
			<li><a target="_blank" href="https://mandarincms.com/modules/glue-for-ultimatum-seo-amp/">Glue for Ultimatum SEO &amp;
					AMP</a> - an integration between <a href="https://mandarincms.com/modules/amp/">the MandarinCMS AMP
					module</a> and Ultimatum SEO.
			</li>
			<li>
				<a target="_blank" href="https://mandarincms.com/modules/fb-instant-articles/">Instant Articles for MCMS</a>
				- Enable Instant Articles for Facebook on your MandarinCMS site and integrates with Ultimatum SEO.
			</li>
		</ol>
	</div>
</div>
