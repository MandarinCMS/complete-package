<?php
/**
 * MySkins administration panel.
 *
 * @package MandarinCMS
 * @subpackage Administration
 */

/** MandarinCMS Administration Bootstrap */
require_once( dirname( __FILE__ ) . '/admin.php' );

if ( ! current_user_can( 'switch_myskins' ) && ! current_user_can( 'edit_myskin_options' ) ) {
	mcms_die(
		'<h1>' . __( 'You need a higher level of permission.' ) . '</h1>' .
		'<p>' . __( 'Sorry, you are not allowed to edit myskin options on this site.' ) . '</p>',
		403
	);
}

if ( current_user_can( 'switch_myskins' ) && isset($_GET['action'] ) ) {
	if ( 'activate' == $_GET['action'] ) {
		check_admin_referer('switch-myskin_' . $_GET['stylesheet']);
		$myskin = mcms_get_myskin( $_GET['stylesheet'] );

		if ( ! $myskin->exists() || ! $myskin->is_allowed() ) {
			mcms_die(
				'<h1>' . __( 'Something went wrong.' ) . '</h1>' .
				'<p>' . __( 'The requested myskin does not exist.' ) . '</p>',
				403
			);
		}

		switch_myskin( $myskin->get_stylesheet() );
		mcms_redirect( admin_url('myskins.php?activated=true') );
		exit;
	} elseif ( 'delete' == $_GET['action'] ) {
		check_admin_referer('delete-myskin_' . $_GET['stylesheet']);
		$myskin = mcms_get_myskin( $_GET['stylesheet'] );

		if ( ! current_user_can( 'delete_myskins' ) ) {
			mcms_die(
				'<h1>' . __( 'You need a higher level of permission.' ) . '</h1>' .
				'<p>' . __( 'Sorry, you are not allowed to delete this item.' ) . '</p>',
				403
			);
		}

		if ( ! $myskin->exists() ) {
			mcms_die(
				'<h1>' . __( 'Something went wrong.' ) . '</h1>' .
				'<p>' . __( 'The requested myskin does not exist.' ) . '</p>',
				403
			);
		}

		$active = mcms_get_myskin();
		if ( $active->get( 'Template' ) == $_GET['stylesheet'] ) {
			mcms_redirect( admin_url( 'myskins.php?delete-active-child=true' ) );
		} else {
			delete_myskin( $_GET['stylesheet'] );
			mcms_redirect( admin_url( 'myskins.php?deleted=true' ) );
		}
		exit;
	}
}

$title = __('Manage MySkins');
$parent_file = 'myskins.php';

// Help tab: Overview
if ( current_user_can( 'switch_myskins' ) ) {
	$help_overview  = '<p>' . __( 'This screen is used for managing your installed myskins. Aside from the default myskin(s) included with your MandarinCMS installation, myskins are designed and developed by third parties.' ) . '</p>' .
		'<p>' . __( 'From this screen you can:' ) . '</p>' .
		'<ul><li>' . __( 'Hover or tap to see Activate and Live Preview buttons' ) . '</li>' .
		'<li>' . __( 'Click on the myskin to see the myskin name, version, author, description, tags, and the Delete link' ) . '</li>' .
		'<li>' . __( 'Click Customize for the current myskin or Live Preview for any other myskin to see a live preview' ) . '</li></ul>' .
		'<p>' . __( 'The current myskin is displayed highlighted as the first myskin.' ) . '</p>' .
		'<p>' . __( 'The search for installed myskins will search for terms in their name, description, author, or tag.' ) . ' <span id="live-search-desc">' . __( 'The search results will be updated as you type.' ) . '</span></p>';

	get_current_screen()->add_help_tab( array(
		'id'      => 'overview',
		'title'   => __( 'Overview' ),
		'content' => $help_overview
	) );
} // switch_myskins

// Help tab: Adding MySkins
if ( current_user_can( 'install_myskins' ) ) {
	if ( is_multisite() ) {
		$help_install = '<p>' . __('Installing myskins on Multisite can only be done from the Network Admin section.') . '</p>';
	} else {
		$help_install = '<p>' . sprintf( __('If you would like to see more myskins to choose from, click on the &#8220;Add New&#8221; button and you will be able to browse or search for additional myskins from the <a href="%s">MandarinCMS MySkin Directory</a>. MySkins in the MandarinCMS MySkin Directory are designed and developed by third parties, and are compatible with the license MandarinCMS uses. Oh, and they&#8217;re free!'), __( 'https://mandarincms.com/myskins/' ) ) . '</p>';
	}

	get_current_screen()->add_help_tab( array(
		'id'      => 'adding-myskins',
		'title'   => __('Adding MySkins'),
		'content' => $help_install
	) );
} // install_myskins

// Help tab: Previewing and Customizing
if ( current_user_can( 'edit_myskin_options' ) && current_user_can( 'customize' ) ) {
	$help_customize =
		'<p>' . __( 'Tap or hover on any myskin then click the Live Preview button to see a live preview of that myskin and change myskin options in a separate, full-screen view. You can also find a Live Preview button at the bottom of the myskin details screen. Any installed myskin can be previewed and customized in this way.' ) . '</p>'.
		'<p>' . __( 'The myskin being previewed is fully interactive &mdash; navigate to different pages to see how the myskin handles posts, archives, and other page templates. The settings may differ depending on what myskin features the myskin being previewed supports. To accept the new settings and activate the myskin all in one step, click the Publish &amp; Activate button above the menu.' ) . '</p>' .
		'<p>' . __( 'When previewing on smaller monitors, you can use the collapse icon at the bottom of the left-hand pane. This will hide the pane, giving you more room to preview your site in the new myskin. To bring the pane back, click on the collapse icon again.' ) . '</p>';

	get_current_screen()->add_help_tab( array(
		'id'		=> 'customize-preview-myskins',
		'title'		=> __( 'Previewing and Customizing' ),
		'content'	=> $help_customize
	) );
} // edit_myskin_options && customize

get_current_screen()->set_help_sidebar(
	'<p><strong>' . __( 'For more information:' ) . '</strong></p>' .
	'<p>' . __( '<a href="https://dev.mandarincms.com/Using_MySkins">Documentation on Using MySkins</a>' ) . '</p>' .
	'<p>' . __( '<a href="https://mandarincms.com/support/">Support Forums</a>' ) . '</p>'
);

if ( current_user_can( 'switch_myskins' ) ) {
	$myskins = mcms_prepare_myskins_for_js();
} else {
	$myskins = mcms_prepare_myskins_for_js( array( mcms_get_myskin() ) );
}
mcms_reset_vars( array( 'myskin', 'search' ) );

add_thickbox();
mcms_enqueue_script( 'myskin' );
mcms_enqueue_script( 'updates' );

require_once( BASED_TREE_URI . 'mcms-admin/admin-header.php' );
?>

<div class="wrap">
	<hr class="mcms-header-end">
<?php
if ( ! validate_current_myskin() || isset( $_GET['broken'] ) ) : ?>
<div id="message1" class="updated notice is-dismissible"><p><?php _e('The active skin is maybe damaged. Reverting to the default skin.'); ?></p></div>
<?php elseif ( isset($_GET['activated']) ) :
		if ( isset( $_GET['previewed'] ) ) { ?>
		<div id="message2" class="updated notice is-dismissible"><p><?php _e( 'Settings saved and skin activated.' ); ?> <a href="<?php echo home_url( '/' ); ?>"><?php _e( 'Visit site' ); ?></a></p></div>
		<?php } else { ?>
<div id="message2" class="updated notice is-dismissible"><p><?php _e( 'New myskin activated.' ); ?> <a href="<?php echo home_url( '/' ); ?>"><?php _e( 'Visit site' ); ?></a></p></div><?php
		}
	elseif ( isset($_GET['deleted']) ) : ?>
<div id="message3" class="updated notice is-dismissible"><p><?php _e('Skin deleted.') ?></p></div>
<?php elseif ( isset( $_GET['delete-active-child'] ) ) : ?>
	<div id="message4" class="error"><p><?php _e( 'You cannot delete a skin while it has an active child myskin.' ); ?></p></div>
<?php
endif;

$ct = mcms_get_myskin();

if ( $ct->errors() && ( ! is_multisite() || current_user_can( 'manage_network_myskins' ) ) ) {
	echo '<div class="error"><p>' . __( 'ERROR:' ) . ' ' . $ct->errors()->get_error_message() . '</p></div>';
}

/*
// Certain error codes are less fatal than others. We can still display myskin information in most cases.
if ( ! $ct->errors() || ( 1 == count( $ct->errors()->get_error_codes() )
	&& in_array( $ct->errors()->get_error_code(), array( 'myskin_no_parent', 'myskin_parent_invalid', 'myskin_no_index' ) ) ) ) : ?>
*/

	// Pretend you didn't see this.
	$current_myskin_actions = array();
	if ( is_array( $submenu ) && isset( $submenu['myskins.php'] ) ) {
		foreach ( (array) $submenu['myskins.php'] as $item) {
			$class = '';
			if ( 'myskins.php' == $item[2] || 'myskin-editor.php' == $item[2] || 0 === strpos( $item[2], 'customize.php' ) )
				continue;
			// 0 = name, 1 = capability, 2 = file
			if ( ( strcmp($self, $item[2]) == 0 && empty($parent_file)) || ($parent_file && ($item[2] == $parent_file)) )
				$class = ' current';
			if ( !empty($submenu[$item[2]]) ) {
				$submenu[$item[2]] = array_values($submenu[$item[2]]); // Re-index.
				$menu_hook = get_module_page_hook($submenu[$item[2]][0][2], $item[2]);
				if ( file_exists(MCMS_PLUGIN_DIR . "/{$submenu[$item[2]][0][2]}") || !empty($menu_hook))
					$current_myskin_actions[] = "<a class='button$class' href='admin.php?page={$submenu[$item[2]][0][2]}'>{$item[0]}</a>";
				else
					$current_myskin_actions[] = "<a class='button$class' href='{$submenu[$item[2]][0][2]}'>{$item[0]}</a>";
			} elseif ( ! empty( $item[2] ) && current_user_can( $item[1] ) ) {
				$menu_file = $item[2];

				if ( current_user_can( 'customize' ) ) {
					if ( 'custom-header' === $menu_file ) {
						$current_myskin_actions[] = "<a class='button hide-if-no-customize$class' href='customize.php?autofocus[control]=header_image'>{$item[0]}</a>";
					} elseif ( 'custom-background' === $menu_file ) {
						$current_myskin_actions[] = "<a class='button hide-if-no-customize$class' href='customize.php?autofocus[control]=background_image'>{$item[0]}</a>";
					}
				}

				if ( false !== ( $pos = strpos( $menu_file, '?' ) ) ) {
					$menu_file = substr( $menu_file, 0, $pos );
				}

				if ( file_exists( BASED_TREE_URI . "mcms-admin/$menu_file" ) ) {
					$current_myskin_actions[] = "<a class='button$class' href='{$item[2]}'>{$item[0]}</a>";
				} else {
					$current_myskin_actions[] = "<a class='button$class' href='myskins.php?page={$item[2]}'>{$item[0]}</a>";
				}
			}
		}
	}

?>

<?php
$class_name = 'myskin-browser';
if ( ! empty( $_GET['search'] ) ) {
	$class_name .= ' search-loading';
}
?>
<div class="<?php echo esc_attr( $class_name ); ?>">
	<div class="myskins mcms-clearfix">

<?php
/*
 * This PHP is synchronized with the tmpl-myskin template below!
 */

foreach ( $myskins as $myskin ) :
	$aria_action = esc_attr( $myskin['id'] . '-action' );
	$aria_name   = esc_attr( $myskin['id'] . '-name' );
	?>
<div class="myskin<?php if ( $myskin['active'] ) echo ' active'; ?>" tabindex="0" aria-describedby="<?php echo $aria_action . ' ' . $aria_name; ?>">
	<?php if ( ! empty( $myskin['screenshot'][0] ) ) { ?>
		<div class="myskin-screenshot">
			<img src="<?php echo $myskin['screenshot'][0]; ?>" alt="" />
		</div>
	<?php } else { ?>
		<div class="myskin-screenshot blank"></div>
	<?php } ?>

	<?php if ( $myskin['hasUpdate'] ) : ?>
		<div class="update-message notice inline notice-warning notice-alt">
		<?php if ( $myskin['hasPackage'] ) : ?>
			<p><?php _e( 'New version available. <button class="button-link" type="button">Update now</button>' ); ?></p>
		<?php else : ?>
			<p><?php _e( 'New version available.' ); ?></p>
		<?php endif; ?>
		</div>
	<?php endif; ?>

	<span class="more-details" id="<?php echo $aria_action; ?>"><?php _e( 'MySkin Details' ); ?></span>
	<div class="myskin-author"><?php printf( __( 'By %s' ), $myskin['author'] ); ?></div>

	<div class="myskin-id-container">
		<?php if ( $myskin['active'] ) { ?>
			<h2 class="myskin-name" id="<?php echo $aria_name; ?>">
				<?php
				/* translators: %s: myskin name */
				printf( __( '<span>Active:</span> %s' ), $myskin['name'] );
				?>
			</h2>
		<?php } else { ?>
			<h2 class="myskin-name" id="<?php echo $aria_name; ?>"><?php echo $myskin['name']; ?></h2>
		<?php } ?>

		<div class="myskin-actions">
		<?php if ( $myskin['active'] ) { ?>
			<?php if ( $myskin['actions']['customize'] && current_user_can( 'edit_myskin_options' ) && current_user_can( 'customize' ) ) { ?>
				<a class="button button-primary customize load-customize hide-if-no-customize" href="<?php echo $myskin['actions']['customize']; ?>"><?php _e( 'Customize' ); ?></a>
			<?php } ?>
		<?php } else { ?>
			<?php
			/* translators: %s: MySkin name */
			$aria_label = sprintf( _x( 'Activate %s', 'myskin' ), '{{ data.name }}' );
			?>
			<a class="button activate" href="<?php echo $myskin['actions']['activate']; ?>" aria-label="<?php echo esc_attr( $aria_label ); ?>"><?php _e( 'Activate' ); ?></a>
			<?php if ( current_user_can( 'edit_myskin_options' ) && current_user_can( 'customize' ) ) { ?>
				<a class="button button-primary load-customize hide-if-no-customize" href="<?php echo $myskin['actions']['customize']; ?>"><?php _e( 'Live Preview' ); ?></a>
			<?php } ?>
		<?php } ?>

		</div>
	</div>
</div>
<?php endforeach; ?>
	</div>
</div>
<div class="myskin-overlay" tabindex="0" role="dialog" aria-label="<?php esc_attr_e( 'MySkin Details' ); ?>"></div>

<p class="no-myskins"><?php _e( 'No myskins found. Try a different search.' ); ?></p>

<?php
// List broken myskins, if any.
if ( ! is_multisite() && current_user_can('edit_myskins') && $broken_myskins = mcms_get_myskins( array( 'errors' => true ) ) ) {
?>

<div class="broken-myskins">
<h3><?php _e('Broken MySkins'); ?></h3>
<p><?php _e( 'The following myskins are installed but incomplete.' ); ?></p>

<?php
$can_delete = current_user_can( 'delete_myskins' );
$can_install = current_user_can( 'install_myskins' );
?>
<table>
	<tr>
		<th><?php _ex('Name', 'myskin name'); ?></th>
		<th><?php _e('Description'); ?></th>
		<?php if ( $can_delete ) { ?>
			<td></td>
		<?php } ?>
		<?php if ( $can_install ) { ?>
			<td></td>
		<?php } ?>
	</tr>
	<?php foreach ( $broken_myskins as $broken_myskin ) : ?>
		<tr>
			<td><?php echo $broken_myskin->get( 'Name' ) ? $broken_myskin->display( 'Name' ) : $broken_myskin->get_stylesheet(); ?></td>
			<td><?php echo $broken_myskin->errors()->get_error_message(); ?></td>
			<?php
			if ( $can_delete ) {
				$stylesheet = $broken_myskin->get_stylesheet();
				$delete_url = add_query_arg( array(
					'action'     => 'delete',
					'stylesheet' => urlencode( $stylesheet ),
				), admin_url( 'myskins.php' ) );
				$delete_url = mcms_nonce_url( $delete_url, 'delete-myskin_' . $stylesheet );
				?>
				<td><a href="<?php echo esc_url( $delete_url ); ?>" class="button delete-myskin"><?php _e( 'Delete' ); ?></a></td>
				<?php
			}

			if ( $can_install && 'myskin_no_parent' === $broken_myskin->errors()->get_error_code() ) {
				$parent_myskin_name = $broken_myskin->get( 'Template' );
				$parent_myskin = myskins_api( 'myskin_information', array( 'slug' => urlencode( $parent_myskin_name ) ) );

				if ( ! is_mcms_error( $parent_myskin ) ) {
					$install_url = add_query_arg( array(
						'action' => 'install-myskin',
						'myskin'  => urlencode( $parent_myskin_name ),
					), admin_url( 'update.php' ) );
					$install_url = mcms_nonce_url( $install_url, 'install-myskin_' . $parent_myskin_name );
					?>
					<td><a href="<?php echo esc_url( $install_url ); ?>" class="button install-myskin"><?php _e( 'Install Parent MySkin' ); ?></a></td>
					<?php
				}
			}
			?>
		</tr>
	<?php endforeach; ?>
</table>
</div>

<?php
}
?>
</div><!-- .wrap -->

<?php
/*
 * The tmpl-myskin template is synchronized with PHP above!
 */
?>
<script id="tmpl-myskin" type="text/template">
	<# if ( data.screenshot[0] ) { #>
		<div class="myskin-screenshot">
			<img src="{{ data.screenshot[0] }}" alt="" />
		</div>
	<# } else { #>
		<div class="myskin-screenshot blank"></div>
	<# } #>

	<# if ( data.hasUpdate ) { #>
		<# if ( data.hasPackage ) { #>
			<div class="update-message notice inline notice-warning notice-alt"><p><?php _e( 'New version available. <button class="button-link" type="button">Update now</button>' ); ?></p></div>
		<# } else { #>
			<div class="update-message notice inline notice-warning notice-alt"><p><?php _e( 'New version available.' ); ?></p></div>
		<# } #>
	<# } #>

	<span class="more-details" id="{{ data.id }}-action"><?php _e( 'MySkin Details' ); ?></span>
	<div class="myskin-author">
		<?php
		/* translators: %s: MySkin author name */
		printf( __( 'By %s' ), '{{{ data.author }}}' );
		?>
	</div>

	<div class="myskin-id-container">
		<# if ( data.active ) { #>
			<h2 class="myskin-name" id="{{ data.id }}-name">
				<?php
				/* translators: %s: MySkin name */
				printf( __( '<span>Active:</span> %s' ), '{{{ data.name }}}' );
				?>
			</h2>
		<# } else { #>
			<h2 class="myskin-name" id="{{ data.id }}-name">{{{ data.name }}}</h2>
		<# } #>

		<div class="myskin-actions">
			<# if ( data.active ) { #>
				<# if ( data.actions.customize ) { #>
					<a class="button button-primary customize load-customize hide-if-no-customize" href="{{{ data.actions.customize }}}"><?php _e( 'Customize' ); ?></a>
				<# } #>
			<# } else { #>
				<?php
				/* translators: %s: MySkin name */
				$aria_label = sprintf( _x( 'Activate %s', 'myskin' ), '{{ data.name }}' );
				?>
				<a class="button activate" href="{{{ data.actions.activate }}}" aria-label="<?php echo $aria_label; ?>"><?php _e( 'Activate' ); ?></a>
				<a class="button button-primary load-customize hide-if-no-customize" href="{{{ data.actions.customize }}}"><?php _e( 'Live Preview' ); ?></a>
			<# } #>
		</div>
	</div>
</script>

<script id="tmpl-myskin-single" type="text/template">
	<div class="myskin-backdrop"></div>
	<div class="myskin-wrap mcms-clearfix" role="document">
		<div class="myskin-header">
			<button class="left dashicons dashicons-no"><span class="screen-reader-text"><?php _e( 'Show previous myskin' ); ?></span></button>
			<button class="right dashicons dashicons-no"><span class="screen-reader-text"><?php _e( 'Show next myskin' ); ?></span></button>
			<button class="close dashicons dashicons-no"><span class="screen-reader-text"><?php _e( 'Close details dialog' ); ?></span></button>
		</div>
		<div class="myskin-about mcms-clearfix">
			<div class="myskin-screenshots">
			<# if ( data.screenshot[0] ) { #>
				<div class="screenshot"><img src="{{ data.screenshot[0] }}" alt="" /></div>
			<# } else { #>
				<div class="screenshot blank"></div>
			<# } #>
			</div>

			<div class="myskin-info">
				<# if ( data.active ) { #>
					<span class="current-label"><?php _e( 'Current MySkin' ); ?></span>
				<# } #>
				<h2 class="myskin-name">{{{ data.name }}}<span class="myskin-version"><?php printf( __( 'Version: %s' ), '{{ data.version }}' ); ?></span></h2>
				<p class="myskin-author"><?php printf( __( 'By %s' ), '{{{ data.authorAndUri }}}' ); ?></p>

				<# if ( data.hasUpdate ) { #>
				<div class="notice notice-warning notice-alt notice-large">
					<h3 class="notice-title"><?php _e( 'Update Available' ); ?></h3>
					{{{ data.update }}}
				</div>
				<# } #>
				<p class="myskin-description">{{{ data.description }}}</p>

				<# if ( data.parent ) { #>
					<p class="parent-myskin"><?php printf( __( 'This is a child myskin of %s.' ), '<strong>{{{ data.parent }}}</strong>' ); ?></p>
				<# } #>

				<# if ( data.tags ) { #>
					<p class="myskin-tags"><span><?php _e( 'Tags:' ); ?></span> {{{ data.tags }}}</p>
				<# } #>
			</div>
		</div>

		<div class="myskin-actions">
			<div class="active-myskin">
				<a href="{{{ data.actions.customize }}}" class="button button-primary customize load-customize hide-if-no-customize"><?php _e( 'Customize' ); ?></a>
				<?php echo implode( ' ', $current_myskin_actions ); ?>
			</div>
			<div class="inactive-myskin">
				<?php
				/* translators: %s: MySkin name */
				$aria_label = sprintf( _x( 'Activate %s', 'myskin' ), '{{ data.name }}' );
				?>
				<# if ( data.actions.activate ) { #>
					<a href="{{{ data.actions.activate }}}" class="button activate" aria-label="<?php echo $aria_label; ?>"><?php _e( 'Activate' ); ?></a>
				<# } #>
				<a href="{{{ data.actions.customize }}}" class="button button-primary load-customize hide-if-no-customize"><?php _e( 'Live Preview' ); ?></a>
			</div>

			<# if ( ! data.active && data.actions['delete'] ) { #>
				<a href="{{{ data.actions['delete'] }}}" class="button delete-myskin"><?php _e( 'Delete' ); ?></a>
			<# } #>
		</div>
	</div>
</script>

<?php
mcms_print_request_filesystem_credentials_modal();
mcms_print_admin_notice_templates();
mcms_print_update_row_templates();

mcms_localize_script( 'updates', '_mcmsUpdatesItemCounts', array(
	'totals'  => mcms_get_update_data(),
) );

require( BASED_TREE_URI . 'mcms-admin/admin-footer.php' );
