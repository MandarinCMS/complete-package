<?php
/**
 * MySkin editor administration panel.
 *
 * @package MandarinCMS
 * @subpackage Administration
 */

/** MandarinCMS Administration Bootstrap */
require_once( dirname( __FILE__ ) . '/admin.php' );

if ( is_multisite() && ! is_network_admin() ) {
	mcms_redirect( network_admin_url( 'myskin-editor.php' ) );
	exit();
}

if ( !current_user_can('edit_myskins') )
	mcms_die('<p>'.__('Sorry, you are not allowed to edit templates for this site.').'</p>');

#$title = __("Edit MySkins");
$parent_file = 'myskins.php';

get_current_screen()->add_help_tab( array(
'id'		=> 'overview',
'title'		=> __('Overview'),
'content'	=>
	'<p>' . __( 'You can use the MySkin Editor to edit the individual CSS and PHP files which make up your myskin.' ) . '</p>' .
	'<p>' . __( 'Begin by choosing a myskin to edit from the dropdown menu and clicking the Select button. A list then appears of the myskin&#8217;s template files. Clicking once on any file name causes the file to appear in the large Editor box.' ) . '</p>' .
	'<p>' . __( 'For PHP files, you can use the Documentation dropdown to select from functions recognized in that file. Look Up takes you to a web page with reference material about that particular function.' ) . '</p>' .
	'<p id="editor-keyboard-trap-help-1">' . __( 'When using a keyboard to navigate:' ) . '</p>' .
	'<ul>' .
	'<li id="editor-keyboard-trap-help-2">' . __( 'In the editing area, the Tab key enters a tab character.' ) . '</li>' .
	'<li id="editor-keyboard-trap-help-3">' . __( 'To move away from this area, press the Esc key followed by the Tab key.' ) . '</li>' .
	'<li id="editor-keyboard-trap-help-4">' . __( 'Screen reader users: when in forms mode, you may need to press the Esc key twice.' ) . '</li>' .
	'</ul>' .
	'<p>' . __( 'After typing in your edits, click Save & Compile.' ) . '</p>' .
	'<p>' . __( '<strong>Advice:</strong> think very carefully about your site crashing if you are live-editing the myskin currently in use.' ) . '</p>' .
	/* translators: %s: link to dev article about child myskins */
	'<p>' . sprintf( __( 'Upgrading to a newer version of the same myskin will override changes made here. To avoid this, consider creating a <a href="%s">child myskin</a> instead.' ), __( 'https://dev.mandarincms.com/Child_MySkins' ) ) . '</p>' .
	( is_network_admin() ? '<p>' . __( 'Any edits to files from this screen will be reflected on all sites in the network.' ) . '</p>' : '' ),
) );

get_current_screen()->set_help_sidebar(
	'<p><strong>' . __('For more information:') . '</strong></p>' .
	'<p>' . __('<a href="https://dev.mandarincms.com/MySkin_Development">Documentation on MySkin Development</a>') . '</p>' .
	'<p>' . __('<a href="https://dev.mandarincms.com/Using_MySkins">Documentation on Using MySkins</a>') . '</p>' .
	'<p>' . __('<a href="https://dev.mandarincms.com/Editing_Files">Documentation on Editing Files</a>') . '</p>' .
	'<p>' . __('<a href="https://dev.mandarincms.com/Template_Tags">Documentation on Template Tags</a>') . '</p>' .
	'<p>' . __('<a href="https://mandarincms.com/support/">Support Forums</a>') . '</p>'
);

mcms_reset_vars( array( 'action', 'error', 'file', 'myskin' ) );

if ( $myskin ) {
	$stylesheet = $myskin;
} else {
	$stylesheet = get_stylesheet();
}

$myskin = mcms_get_myskin( $stylesheet );

if ( ! $myskin->exists() ) {
	mcms_die( __( 'The requested myskin does not exist.' ) );
}

if ( $myskin->errors() && 'myskin_no_stylesheet' == $myskin->errors()->get_error_code() ) {
	mcms_die( __( 'The requested myskin does not exist.' ) . ' ' . $myskin->errors()->get_error_message() );
}

$allowed_files = $style_files = array();
$has_templates = false;

$file_types = mcms_get_myskin_file_editable_extensions( $myskin );

foreach ( $file_types as $type ) {
	switch ( $type ) {
		case 'php':
			$allowed_files += $myskin->get_files( 'php', -1 );
			$has_templates = ! empty( $allowed_files );
			break;
		case 'css':
			$style_files = $myskin->get_files( 'css', -1 );
			$allowed_files['style.css'] = $style_files['style.css'];
			$allowed_files += $style_files;
			break;
		default:
			$allowed_files += $myskin->get_files( $type, -1 );
			break;
	}
}

// Move functions.php and style.css to the top.
if ( isset( $allowed_files['functions.php'] ) ) {
	$allowed_files = array( 'functions.php' => $allowed_files['functions.php'] ) + $allowed_files;
}
if ( isset( $allowed_files['style.css'] ) ) {
	$allowed_files = array( 'style.css' => $allowed_files['style.css'] ) + $allowed_files;
}

if ( empty( $file ) ) {
	$relative_file = 'style.css';
	$file = $allowed_files['style.css'];
} else {
	$relative_file = mcms_unslash( $file );
	$file = $myskin->get_stylesheet_directory() . '/' . $relative_file;
}

validate_file_to_edit( $file, $allowed_files );

// Handle fallback editing of file when JavaScript is not available.
$edit_error = null;
$posted_content = null;
if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) {
	$r = mcms_edit_myskin_module_file( mcms_unslash( $_POST ) );
	if ( is_mcms_error( $r ) ) {
		$edit_error = $r;
		if ( check_ajax_referer( 'edit-myskin_' . $file . $stylesheet, 'nonce', false ) && isset( $_POST['newcontent'] ) ) {
			$posted_content = mcms_unslash( $_POST['newcontent'] );
		}
	} else {
		mcms_redirect( add_query_arg(
			array(
				'a' => 1, // This means "success" for some reason.
				'myskin' => $stylesheet,
				'file' => $relative_file,
			),
			admin_url( 'myskin-editor.php' )
		) );
		exit;
	}
}

	$settings = array(
		'codeEditor' => mcms_enqueue_code_editor( compact( 'file' ) ),
	);
	mcms_enqueue_script( 'mcms-myskin-module-editor' );
	mcms_add_inline_script( 'mcms-myskin-module-editor', sprintf( 'jQuery( function( $ ) { mcms.myskinModuleEditor.init( $( "#template" ), %s ); } )', mcms_json_encode( $settings ) ) );
	mcms_add_inline_script( 'mcms-myskin-module-editor', 'mcms.myskinModuleEditor.myskinOrModule = "myskin";' );

	require_once( BASED_TREE_URI . 'mcms-admin/admin-header.php' );

	update_recently_edited( $file );

	if ( ! is_file( $file ) )
		$error = true;

	$content = '';
	if ( ! empty( $posted_content ) ) {
		$content = $posted_content;
	} elseif ( ! $error && filesize( $file ) > 0 ) {
		$f = fopen($file, 'r');
		$content = fread($f, filesize($file));

		if ( '.php' == substr( $file, strrpos( $file, '.' ) ) ) {
			$functions = mcms_doc_link_parse( $content );

			$docs_select = '<select name="docs-list" id="docs-list">';
			$docs_select .= '<option value="">' . esc_attr__( 'Function Name&hellip;' ) . '</option>';
			foreach ( $functions as $function ) {
				$docs_select .= '<option value="' . esc_attr( urlencode( $function ) ) . '">' . htmlspecialchars( $function ) . '()</option>';
			}
			$docs_select .= '</select>';
		}

		$content = esc_textarea( $content );
	}

$file_description = get_file_description( $relative_file );
$file_show = array_search( $file, array_filter( $allowed_files ) );
$description = esc_html( $file_description );
if ( $file_description != $file_show ) {
	$description .= ' <span>(' . esc_html( $file_show ) . ')</span>';
}
?>
<div class="wrap">
<h1><?php echo esc_html( $title ); ?></h1>

<?php if ( isset( $_GET['a'] ) ) : ?>
	<div id="message" class="updated notice is-dismissible">
		<p><?php _e( 'File edited successfully.' ); ?></p>
	</div>
<?php elseif ( is_mcms_error( $edit_error ) ) : ?>
	<div id="message" class="notice notice-error">
		<p><?php _e( 'There was an error while trying to update the file. You may need to fix something and try updating again.' ); ?></p>
		<pre><?php echo esc_html( $edit_error->get_error_message() ? $edit_error->get_error_message() : $edit_error->get_error_code() ); ?></pre>
	</div>
<?php endif; ?>
<?php if ( preg_match( '/\.css$/', $file ) ) : ?>
	<div id="message" class="notice-info notice">
		<p><strong><?php _e( 'Did you know?' ); ?></strong></p>
		<p>
			<?php
			echo sprintf(
				/* translators: %s: link to Custom CSS section in the Customizer */
				__( 'There&#8217;s no need to change your CSS here &mdash; you can edit and live preview CSS changes in the <a href="%s">built-in CSS editor</a>.' ),
				esc_url( add_query_arg( 'autofocus[section]', 'custom_css', admin_url( 'customize.php' ) ) )
			);
			?>
		</p>
	</div>
<?php endif; ?>

<div class="fileedit-sub">
<div class="alignleft">
<h2><?php echo $myskin->display( 'Name' ); if ( $description ) echo ': ' . $description; ?></h2>
</div>
<div class="alignright">
	<form action="myskin-editor.php" method="get">
		<strong><label for="myskin"><?php _e('Folder'); ?> </label></strong>
		<select name="myskin" id="myskin">
<?php
foreach ( mcms_get_myskins( array( 'errors' => null ) ) as $a_stylesheet => $a_myskin ) {
	if ( $a_myskin->errors() && 'myskin_no_stylesheet' == $a_myskin->errors()->get_error_code() )
		continue;

	$selected = $a_stylesheet == $stylesheet ? ' selected="selected"' : '';
	echo "\n\t" . '<option value="' . esc_attr( $a_stylesheet ) . '"' . $selected . '>' . $a_myskin->display('Name') . '</option>';
}
?>
		</select>
		<?php submit_button( __( 'Select' ), '', 'Submit', false ); ?>
	</form>
</div>
<br class="clear" />
</div>
<?php
if ( $myskin->errors() )
	echo '<div class="error"><p><strong>' . __( 'This myskin is broken.' ) . '</strong> ' . $myskin->errors()->get_error_message() . '</p></div>';
?>
<div id="templateside">
	<h2 id="myskin-files-label"><?php _e( 'File Explorer' ); ?></h2>
	<ul role="tree" aria-labelledby="myskin-files-label">
		<?php if ( ( $has_templates || $myskin->parent() ) && $myskin->parent() ) : ?>
			<li class="howto">
				<?php
				/* translators: %s: link to edit parent myskin */
				echo sprintf( __( 'This child myskin inherits templates from a parent myskin, %s.' ),
					sprintf( '<a href="%s">%s</a>',
						self_admin_url( 'myskin-editor.php?myskin=' . urlencode( $myskin->get_template() ) ),
						$myskin->parent()->display( 'Name' )
					)
				);
				?>
			</li>
		<?php endif; ?>
		<li role="treeitem" tabindex="-1" aria-expanded="true" aria-level="1" aria-posinset="1" aria-setsize="1">
			<ul role="group">
				<?php mcms_print_myskin_file_tree( mcms_make_myskin_file_tree( $allowed_files ) ); ?>
			</ul>
		</li>
	</ul>
</div>

<?php if ( $error ) :
	echo '<div class="error"><p>' . __('Oops, no such file exists! Double check the name and try again, merci.') . '</p></div>';
else : ?>
	<form name="template" id="template" action="myskin-editor.php" method="post">
		<?php mcms_nonce_field( 'edit-myskin_' . $file . $stylesheet, 'nonce' ); ?>
		<div>
			<label for="newcontent" id="myskin-module-editor-label"><?php _e( 'Current workspace' ); ?></label>
			<textarea cols="70" rows="30" name="newcontent" id="newcontent" aria-describedby="editor-keyboard-trap-help-1 editor-keyboard-trap-help-2 editor-keyboard-trap-help-3 editor-keyboard-trap-help-4"><?php echo $content; ?></textarea>
			<input type="hidden" name="action" value="update" />
			<input type="hidden" name="file" value="<?php echo esc_attr( $relative_file ); ?>" />
			<input type="hidden" name="myskin" value="<?php echo esc_attr( $myskin->get_stylesheet() ); ?>" />
		</div>
	
	<div>
		<div class="editor-notices">
			<?php if ( is_child_myskin() && $myskin->get_stylesheet() == get_template() ) : ?>
				<div class="notice notice-warning inline">
					<p>
						<?php if ( is_writeable( $file ) ) { ?><strong><?php _e( 'Caution:' ); ?></strong><?php } ?>
						<?php _e( 'This is a file in your current parent myskin.' ); ?>
					</p>
				</div>
			<?php endif; ?>
		</div>
	<?php if ( is_writeable( $file ) ) : ?>
		<p class="submit">
			<?php submit_button( __( 'Save & Compile' ), 'primary', 'submit', false ); ?>
			<span class="spinner"></span>
		</p>
	<?php else : ?>
		<p><em><?php _e('You need to make this file writable before you can save your changes. See <a href="https://dev.mandarincms.com/Changing_File_Permissions">the Codex</a> for more information.'); ?></em></p>
	<?php endif; ?>
	</div>
	<?php mcms_print_file_editor_templates(); ?>
	</form>
<?php
endif; // $error
?>
<br class="clear" />
</div>
<?php
$dismissed_pointers = explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_mcms_pointers', true ) );
if ( ! in_array( 'myskin_editor_notice', $dismissed_pointers, true ) ) :
	// Get a back URL
	$referer = mcms_get_referer();
	$excluded_referer_basenames = array( 'myskin-editor.php', 'signin.php' );

	if ( $referer && ! in_array( basename( parse_url( $referer, PHP_URL_PATH ) ), $excluded_referer_basenames, true ) ) {
		$return_url = $referer;
	} else {
		$return_url = admin_url( '/' );
	}
?>
<div id="file-editor-warning" class="notification-dialog-wrap file-editor-warning hide-if-no-js hidden">
	<div class="notification-dialog-background"></div>
	<div class="notification-dialog">
		<div class="file-editor-warning-content">
			<div class="file-editor-warning-message">
				<h1><?php _e( 'Heads up!' ); ?></h1>
				<p>
					<?php
					echo sprintf(
						/* translators: %s: Codex URL */
						__( 'You appear to be making direct edits to your myskin in the MandarinCMS dashboard. We recommend that you don&#8217;t! Editing your myskin directly could break your site and your changes may be lost in future updates. If you need to tweak more than your myskin&#8217;s CSS, you might want to try <a href="%s">making a child myskin</a>.' ),
						esc_url( __( 'https://dev.mandarincms.com/Child_MySkins' ) )
					);
					?>
				</p>
				<p><?php _e( 'If you decide to go ahead with direct edits anyway, use a file manager to create a copy with a new name and hang on to the original. That way, you can re-enable a functional version if something goes wrong.' ); ?></p>
				
			</div>
			<p>
				<a class="button file-editor-warning-go-back" href="<?php echo esc_url( $return_url ); ?>"><?php _e( 'Go back' ); ?></a>
				<button type="button" class="file-editor-warning-dismiss button button-primary"><?php _e( 'I understand' ); ?></button>
			</p>
		</div>
	</div>
</div>
<?php
endif; // editor warning notice

include(BASED_TREE_URI . 'mcms-admin/admin-footer.php' );
