<?php
/**
 * Credits administration panel.
 *
 * @package MandarinCMS
 * @subpackage Administration
 */

/** MandarinCMS Administration Bootstrap */
require_once( dirname( __FILE__ ) . '/admin.php' );
require_once( dirname( __FILE__ ) . '/includes/credits.php' );

$title = __( 'Credits' );

list( $display_version ) = explode( '-', get_bloginfo( 'version' ) );

include( BASED_TREE_URI . 'mcms-admin/admin-header.php' );
?>
<div class="wrap about-wrap full-width-layout">

<h1><?php printf( __( 'Welcome to MandarinCMS %s' ), $display_version ); ?></h1>

<p class="about-text"><?php printf( __( 'Thank you for updating to the latest version! MandarinCMS %s will smooth your design workflow and keep you safe from coding errors.' ), $display_version ); ?></p>

<div class="mcms-badge"><?php printf( __( 'Version %s' ), $display_version ); ?></div>

<h2 class="nav-tab-wrapper mcms-clearfix">
	<a href="about.php" class="nav-tab"><?php _e( 'What&#8217;s New' ); ?></a>
	<a href="credits.php" class="nav-tab nav-tab-active"><?php _e( 'Credits' ); ?></a>
	<a href="freedoms.php" class="nav-tab"><?php _e( 'Freedoms' ); ?></a>
	<a href="freedoms.php?privacy-notice" class="nav-tab"><?php _e( 'Privacy' ); ?></a>
</h2>

<div class="about-wrap-content">
<?php

$credits = mcms_credits();

if ( ! $credits ) {
	echo '<p class="about-description">';
	/* translators: 1: https://mandarincms.com/about/, 2: https://make.mandarincms.com/ */
	printf( __( 'MandarinCMS is created by a <a href="%1$s">worldwide team</a> of passionate individuals. <a href="%2$s">Get involved in MandarinCMS</a>.' ),
		'https://mandarincms.com/about/',
		__( 'https://make.mandarincms.com/' )
	);
	echo '</p>';
	echo '</div>';
	echo '</div>';
	include( BASED_TREE_URI . 'mcms-admin/admin-footer.php' );
	exit;
}

echo '<p class="about-description">' . __( 'MandarinCMS is created by a worldwide team of passionate individuals.' ) . "</p>\n";

echo '<p>' . sprintf(
	/* translators: %s: https://make.mandarincms.com/ */
	__( 'Want to see your name in lights on this page? <a href="%s">Get involved in MandarinCMS</a>.' ),
	__( 'https://make.mandarincms.com/' )
) . '</p>';

foreach ( $credits['groups'] as $group_slug => $group_data ) {
	if ( $group_data['name'] ) {
		if ( 'Translators' == $group_data['name'] ) {
			// Considered a special slug in the API response. (Also, will never be returned for en_US.)
			$title = _x( 'Translators', 'Translate this to be the equivalent of English Translators in your language for the credits page Translators section' );
		} elseif ( isset( $group_data['placeholders'] ) ) {
			$title = vsprintf( translate( $group_data['name'] ), $group_data['placeholders'] );
		} else {
			$title = translate( $group_data['name'] );
		}

		echo '<h3 class="mcms-people-group">' . esc_html( $title ) . "</h3>\n";
	}

	if ( ! empty( $group_data['shuffle'] ) )
		shuffle( $group_data['data'] ); // We were going to sort by ability to pronounce "hierarchical," but that wouldn't be fair to Matt.

	switch ( $group_data['type'] ) {
		case 'list' :
			array_walk( $group_data['data'], '_mcms_credits_add_profile_link', $credits['data']['profiles'] );
			echo '<p class="mcms-credits-list">' . mcms_sprintf( '%l.', $group_data['data'] ) . "</p>\n\n";
			break;
		case 'libraries' :
			array_walk( $group_data['data'], '_mcms_credits_build_object_link' );
			echo '<p class="mcms-credits-list">' . mcms_sprintf( '%l.', $group_data['data'] ) . "</p>\n\n";
			break;
		default:
			$compact = 'compact' == $group_data['type'];
			$classes = 'mcms-people-group ' . ( $compact ? 'compact' : '' );
			echo '<ul class="' . $classes . '" id="mcms-people-group-' . $group_slug . '">' . "\n";
			foreach ( $group_data['data'] as $person_data ) {
				echo '<li class="mcms-person" id="mcms-person-' . esc_attr( $person_data[2] ) . '">' . "\n\t";
				echo '<a href="' . esc_url( sprintf( $credits['data']['profiles'], $person_data[2] ) ) . '" class="web">';
				$size = 'compact' == $group_data['type'] ? 30 : 60;
				$data = get_avatar_data( $person_data[1] . '@md5.gravatar.com', array( 'size' => $size ) );
				$size *= 2;
				$data2x = get_avatar_data( $person_data[1] . '@md5.gravatar.com', array( 'size' => $size ) );
				echo '<img src="' . esc_url( $data['url'] ) . '" srcset="' . esc_url( $data2x['url'] ) . ' 2x" class="gravatar" alt="" />' . "\n";
				echo esc_html( $person_data[0] ) . "</a>\n\t";
				if ( ! $compact )
					echo '<span class="title">' . translate( $person_data[3] ) . "</span>\n";
				echo "</li>\n";
			}
			echo "</ul>\n";
		break;
	}
}

?>
</div>
</div>
<?php

include( BASED_TREE_URI . 'mcms-admin/admin-footer.php' );

return;

// These are strings returned by the API that we want to be translatable
__( 'Project Leaders' );
__( 'Core Feeders to MandarinCMS %s' );
__( 'Noteworthy Feeders' );
__( 'Cofounder, Project Lead' );
__( 'Lead Developer' );
__( 'Release Lead' );
__( 'Release Design Lead' );
__( 'Release Deputy' );
__( 'Core Developer' );
__( 'External Libraries' );
