/* global tinymce */
/**
 * Included for back-compat.
 * The default WindowManager in TinyMCE 4.0 supports three types of dialogs:
 *	- With HTML created from JS.
 *	- With inline HTML (like MCMSWindowManager).
 *	- Old type iframe based dialogs.
 * For examples see the default modules: https://github.com/tinymce/tinymce/tree/master/js/tinymce/modules
 */
tinymce.MCMSWindowManager = tinymce.InlineWindowManager = function( editor ) {
	if ( this.mcms ) {
		return this;
	}

	this.mcms = {};
	this.parent = editor.windowManager;
	this.editor = editor;

	tinymce.extend( this, this.parent );

	this.open = function( args, params ) {
		var $element,
			self = this,
			mcms = this.mcms;

		if ( ! args.mcmsDialog ) {
			return this.parent.open.apply( this, arguments );
		} else if ( ! args.id ) {
			return;
		}

		if ( typeof jQuery === 'undefined' || ! jQuery.mcms || ! jQuery.mcms.mcmsdialog ) {
			// mcmsdialog.js is not loaded
			if ( window.console && window.console.error ) {
				window.console.error('mcmsdialog.js is not loaded. Please set "mcmsdialogs" as dependency for your script when calling mcms_enqueue_script(). You may also want to enqueue the "mcms-jquery-ui-dialog" stylesheet.');
			}

			return;
		}

		mcms.$element = $element = jQuery( '#' + args.id );

		if ( ! $element.length ) {
			return;
		}

		if ( window.console && window.console.log ) {
			window.console.log('tinymce.MCMSWindowManager is deprecated. Use the default editor.windowManager to open dialogs with inline HTML.');
		}

		mcms.features = args;
		mcms.params = params;

		// Store selection. Takes a snapshot in the FocusManager of the selection before focus is moved to the dialog.
		editor.nodeChanged();

		// Create the dialog if necessary
		if ( ! $element.data('mcmsdialog') ) {
			$element.mcmsdialog({
				title: args.title,
				width: args.width,
				height: args.height,
				modal: true,
				dialogClass: 'mcms-dialog',
				zIndex: 300000
			});
		}

		$element.mcmsdialog('open');

		$element.on( 'mcmsdialogclose', function() {
			if ( self.mcms.$element ) {
				self.mcms = {};
			}
		});
	};

	this.close = function() {
		if ( ! this.mcms.features || ! this.mcms.features.mcmsDialog ) {
			return this.parent.close.apply( this, arguments );
		}

		this.mcms.$element.mcmsdialog('close');
	};
};

tinymce.ModuleManager.add( 'mcmsdialogs', function( editor ) {
	// Replace window manager
	editor.on( 'init', function() {
		editor.windowManager = new tinymce.MCMSWindowManager( editor );
	});
});
