<?php
/**
 * Edit module editor administration panel.
 *
 * @package MandarinCMS
 * @subpackage Administration
 */

/** MandarinCMS Administration Bootstrap */
require_once( dirname( __FILE__ ) . '/admin.php' );

if ( is_multisite() && ! is_network_admin() ) {
	mcms_redirect( network_admin_url( 'module-editor.php' ) );
	exit();
}

if ( !current_user_can('edit_modules') )
	mcms_die( __('Sorry, you are not allowed to edit modules for this site.') );

$title = __("Edit Modules");
$parent_file = 'modules.php';

$modules = get_modules();

if ( empty( $modules ) ) {
	include( BASED_TREE_URI . 'mcms-admin/admin-header.php' );
	?>
	<div class="wrap">
		<h1><?php echo esc_html( $title ); ?></h1>
		<div id="message" class="error"><p><?php _e( 'You do not appear to have any modules available at this time.' ); ?></p></div>
	</div>
	<?php
	include( BASED_TREE_URI . 'mcms-admin/admin-footer.php' );
	exit;
}

$file = '';
$module = '';
if ( isset( $_REQUEST['file'] ) ) {
	$file = mcms_unslash( $_REQUEST['file'] );
}

if ( isset( $_REQUEST['module'] ) ) {
	$module = mcms_unslash( $_REQUEST['module'] );
}

if ( empty( $module ) ) {
	if ( $file ) {

		// Locate the module for a given module file being edited.
		$file_dirname = dirname( $file );
		foreach ( array_keys( $modules ) as $module_candidate ) {
			if ( $module_candidate === $file || ( '.' !== $file_dirname && dirname( $module_candidate ) === $file_dirname ) ) {
				$module = $module_candidate;
				break;
			}
		}

		// Fallback to the file as the module.
		if ( empty( $module ) ) {
			$module = $file;
		}
	} else {
		$module = array_keys( $modules );
		$module = $module[0];
	}
}

$module_files = get_module_files($module);

if ( empty( $file ) ) {
	$file = $module_files[0];
}

$file = validate_file_to_edit($file, $module_files);
$real_file = MCMS_PLUGIN_DIR . '/' . $file;

// Handle fallback editing of file when JavaScript is not available.
$edit_error = null;
$posted_content = null;
if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) {
	$r = mcms_edit_myskin_module_file( mcms_unslash( $_POST ) );
	if ( is_mcms_error( $r ) ) {
		$edit_error = $r;
		if ( check_ajax_referer( 'edit-module_' . $file, 'nonce', false ) && isset( $_POST['newcontent'] ) ) {
			$posted_content = mcms_unslash( $_POST['newcontent'] );
		}
	} else {
		mcms_redirect( add_query_arg(
			array(
				'a' => 1, // This means "success" for some reason.
				'module' => $module,
				'file' => $file,
			),
			admin_url( 'module-editor.php' )
		) );
		exit;
	}
}

	// List of allowable extensions
	$editable_extensions = mcms_get_module_file_editable_extensions( $module );

	if ( ! is_file($real_file) ) {
		mcms_die(sprintf('<p>%s</p>', __('No such file exists! Double check the name and try again.')));
	} else {
		// Get the extension of the file
		if ( preg_match('/\.([^.]+)$/', $real_file, $matches) ) {
			$ext = strtolower($matches[1]);
			// If extension is not in the acceptable list, skip it
			if ( !in_array( $ext, $editable_extensions) )
				mcms_die(sprintf('<p>%s</p>', __('Files of this type are not editable.')));
		}
	}

	get_current_screen()->add_help_tab( array(
	'id'		=> 'overview',
	'title'		=> __('Overview'),
	'content'	=>
		'<p>' . __('You can use the editor to make changes to any of your modules&#8217; individual PHP files. Be aware that if you make changes, modules updates will overwrite your customizations.') . '</p>' .
		'<p>' . __('Choose a module to edit from the dropdown menu and click the Select button. Click once on any file name to load it in the editor, and make your changes. Don&#8217;t forget to save your changes (Update File) when you&#8217;re finished.') . '</p>' .
		'<p>' . __('The Documentation menu below the editor lists the PHP functions recognized in the module file. Clicking Look Up takes you to a web page about that particular function.') . '</p>' .
		'<p id="editor-keyboard-trap-help-1">' . __( 'When using a keyboard to navigate:' ) . '</p>' .
		'<ul>' .
		'<li id="editor-keyboard-trap-help-2">' . __( 'In the editing area, the Tab key enters a tab character.' ) . '</li>' .
		'<li id="editor-keyboard-trap-help-3">' . __( 'To move away from this area, press the Esc key followed by the Tab key.' ) . '</li>' .
		'<li id="editor-keyboard-trap-help-4">' . __( 'Screen reader users: when in forms mode, you may need to press the Esc key twice.' ) . '</li>' .
		'</ul>' .
		'<p>' . __('If you want to make changes but don&#8217;t want them to be overwritten when the module is updated, you may be ready to think about writing your own module. For information on how to edit modules, write your own from scratch, or just better understand their anatomy, check out the links below.') . '</p>' .
		( is_network_admin() ? '<p>' . __('Any edits to files from this screen will be reflected on all sites in the network.') . '</p>' : '' )
	) );

	get_current_screen()->set_help_sidebar(
		'<p><strong>' . __('For more information:') . '</strong></p>' .
		'<p>' . __('<a href="https://dev.mandarincms.com/Modules_Editor_Screen">Documentation on Editing Modules</a>') . '</p>' .
		'<p>' . __('<a href="https://dev.mandarincms.com/Writing_a_Module">Documentation on Writing Modules</a>') . '</p>' .
		'<p>' . __('<a href="https://mandarincms.com/support/">Support Forums</a>') . '</p>'
	);

	$settings = array(
		'codeEditor' => mcms_enqueue_code_editor( array( 'file' => $real_file ) ),
	);
	mcms_enqueue_script( 'mcms-myskin-module-editor' );
	mcms_add_inline_script( 'mcms-myskin-module-editor', sprintf( 'jQuery( function( $ ) { mcms.myskinModuleEditor.init( $( "#template" ), %s ); } )', mcms_json_encode( $settings ) ) );
	mcms_add_inline_script( 'mcms-myskin-module-editor', sprintf( 'mcms.myskinModuleEditor.myskinOrModule = "module";' ) );

	require_once(BASED_TREE_URI . 'mcms-admin/admin-header.php');

	update_recently_edited(MCMS_PLUGIN_DIR . '/' . $file);

	if ( ! empty( $posted_content ) ) {
		$content = $posted_content;
	} else {
		$content = file_get_contents( $real_file );
	}

	if ( '.php' == substr( $real_file, strrpos( $real_file, '.' ) ) ) {
		$functions = mcms_doc_link_parse( $content );

		if ( !empty($functions) ) {
			$docs_select = '<select name="docs-list" id="docs-list">';
			$docs_select .= '<option value="">' . __( 'Function Name&hellip;' ) . '</option>';
			foreach ( $functions as $function) {
				$docs_select .= '<option value="' . esc_attr( $function ) . '">' . esc_html( $function ) . '()</option>';
			}
			$docs_select .= '</select>';
		}
	}

	$content = esc_textarea( $content );
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

<div class="fileedit-sub">
<div class="alignleft">
<h2>
	<?php
	if ( is_module_active( $module ) ) {
		if ( is_writeable( $real_file ) ) {
			/* translators: %s: module file name */
			echo sprintf( __( 'Editing %s (active)' ), '<strong>' . esc_html( $file ) . '</strong>' );
		} else {
			/* translators: %s: module file name */
			echo sprintf( __( 'Browsing %s (active)' ), '<strong>' . esc_html( $file ) . '</strong>' );
		}
	} else {
		if ( is_writeable( $real_file ) ) {
			/* translators: %s: module file name */
			echo sprintf( __( 'Editing %s (inactive)' ), '<strong>' . esc_html( $file ) . '</strong>' );
		} else {
			/* translators: %s: module file name */
			echo sprintf( __( 'Browsing %s (inactive)' ), '<strong>' . esc_html( $file ) . '</strong>' );
		}
	}
	?>
</h2>
</div>
<div class="alignright">
	<form action="module-editor.php" method="get">
		<strong><label for="module"><?php _e('Select module to edit:'); ?> </label></strong>
		<select name="module" id="module">
<?php
	foreach ( $modules as $module_key => $a_module ) {
		$module_name = $a_module['Name'];
		if ( $module_key == $module )
			$selected = " selected='selected'";
		else
			$selected = '';
		$module_name = esc_attr($module_name);
		$module_key = esc_attr($module_key);
		echo "\n\t<option value=\"$module_key\" $selected>$module_name</option>";
	}
?>
		</select>
		<?php submit_button( __( 'Select' ), '', 'Submit', false ); ?>
	</form>
</div>
<br class="clear" />
</div>

<div id="templateside">
	<h2 id="module-files-label"><?php _e( 'Module Files' ); ?></h2>

	<?php
	$module_editable_files = array();
	foreach ( $module_files as $module_file ) {
		if ( preg_match('/\.([^.]+)$/', $module_file, $matches ) && in_array( $matches[1], $editable_extensions ) ) {
			$module_editable_files[] = $module_file;
		}
	}
	?>
	<ul role="tree" aria-labelledby="module-files-label">
	<li role="treeitem" tabindex="-1" aria-expanded="true" aria-level="1" aria-posinset="1" aria-setsize="1">
		<ul role="group">
			<?php mcms_print_module_file_tree( mcms_make_module_file_tree( $module_editable_files ) ); ?>
		</ul>
	</ul>
</div>
<form name="template" id="template" action="module-editor.php" method="post">
	<?php mcms_nonce_field( 'edit-module_' . $file, 'nonce' ); ?>
		<div>
			<label for="newcontent" id="myskin-module-editor-label"><?php _e( 'Selected file content:' ); ?></label>
			<textarea cols="70" rows="25" name="newcontent" id="newcontent" aria-describedby="editor-keyboard-trap-help-1 editor-keyboard-trap-help-2 editor-keyboard-trap-help-3 editor-keyboard-trap-help-4"><?php echo $content; ?></textarea>
			<input type="hidden" name="action" value="update" />
			<input type="hidden" name="file" value="<?php echo esc_attr( $file ); ?>" />
			<input type="hidden" name="module" value="<?php echo esc_attr( $module ); ?>" />
		</div>
		<?php if ( !empty( $docs_select ) ) : ?>
		<div id="documentation" class="hide-if-no-js"><label for="docs-list"><?php _e('Documentation:') ?></label> <?php echo $docs_select ?> <input type="button" class="button" value="<?php esc_attr_e( 'Look Up' ) ?> " onclick="if ( '' != jQuery('#docs-list').val() ) { window.open( 'https://api.mandarincms.com/core/handbook/1.0/?function=' + escape( jQuery( '#docs-list' ).val() ) + '&amp;locale=<?php echo urlencode( get_user_locale() ) ?>&amp;version=<?php echo urlencode( get_bloginfo( 'version' ) ) ?>&amp;redirect=true'); }" /></div>
		<?php endif; ?>
<?php if ( is_writeable($real_file) ) : ?>
	<div class="editor-notices">
		<?php if ( in_array( $module, (array) get_option( 'active_modules', array() ) ) ) { ?>
			<div class="notice notice-warning inline active-module-edit-warning">
			<p><?php _e('<strong>Warning:</strong> Making changes to active modules is not recommended.'); ?></p>
		</div>
		<?php } ?>
	</div>
	<p class="submit">
		<?php submit_button( __( 'Update File' ), 'primary', 'submit', false ); ?>
		<span class="spinner"></span>
	</p>
<?php else : ?>
	<p><em><?php _e('You need to make this file writable before you can save your changes. See <a href="https://dev.mandarincms.com/Changing_File_Permissions">the Codex</a> for more information.'); ?></em></p>
<?php endif; ?>
<?php mcms_print_file_editor_templates(); ?>
</form>
<br class="clear" />
</div>
<?php
$dismissed_pointers = explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_mcms_pointers', true ) );
if ( ! in_array( 'module_editor_notice', $dismissed_pointers, true ) ) :
	// Get a back URL
	$referer = mcms_get_referer();
	$excluded_referer_basenames = array( 'module-editor.php', 'signin.php' );

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
				<p><?php _e( 'You appear to be making direct edits to your module in the MandarinCMS dashboard. We recommend that you don&#8217;t! Editing modules directly may introduce incompatibilities that break your site and your changes may be lost in future updates.' ); ?></p>
				<p><?php _e( 'If you absolutely have to make direct edits to this module, use a file manager to create a copy with a new name and hang on to the original. That way, you can re-enable a functional version if something goes wrong.' ); ?></p>
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

include(BASED_TREE_URI . "mcms-admin/admin-footer.php");
