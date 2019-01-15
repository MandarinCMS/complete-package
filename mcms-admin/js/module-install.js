/* global moduleinstallL10n, tb_click, tb_remove */

/**
 * Functionality for the module install screens.
 */
var tb_position;
jQuery( document ).ready( function( $ ) {

	var tbWindow,
		$iframeBody,
		$tabbables,
		$firstTabbable,
		$lastTabbable,
		$focusedBefore = $(),
		$uploadViewToggle = $( '.upload-view-toggle' ),
		$wrap = $ ( '.wrap' ),
		$body = $( document.body );

	tb_position = function() {
		var width = $( window ).width(),
			H = $( window ).height() - ( ( 792 < width ) ? 60 : 20 ),
			W = ( 792 < width ) ? 772 : width - 20;

		tbWindow = $( '#TB_window' );

		if ( tbWindow.length ) {
			tbWindow.width( W ).height( H );
			$( '#TB_iframeContent' ).width( W ).height( H );
			tbWindow.css({
				'margin-left': '-' + parseInt( ( W / 2 ), 10 ) + 'px'
			});
			if ( typeof document.body.style.maxWidth !== 'undefined' ) {
				tbWindow.css({
					'top': '30px',
					'margin-top': '0'
				});
			}
		}

		return $( 'a.thickbox' ).each( function() {
			var href = $( this ).attr( 'href' );
			if ( ! href ) {
				return;
			}
			href = href.replace( /&width=[0-9]+/g, '' );
			href = href.replace( /&height=[0-9]+/g, '' );
			$(this).attr( 'href', href + '&width=' + W + '&height=' + ( H ) );
		});
	};

	$( window ).resize( function() {
		tb_position();
	});

	/*
	 * Custom events: when a Thickbox iframe has loaded and when the Thickbox
	 * modal gets removed from the DOM.
	 */
	$body
		.on( 'thickbox:iframe:loaded', tbWindow, function() {
			/*
			 * Return if it's not the modal with the module details iframe. Other
			 * thickbox instances might want to load an iframe with content from
			 * an external domain. Avoid to access the iframe contents when we're
			 * not sure the iframe loads from the same domain.
			 */
			if ( ! tbWindow.hasClass( 'module-details-modal' ) ) {
				return;
			}

			iframeLoaded();
		})
		.on( 'thickbox:removed', function() {
			// Set focus back to the element that opened the modal dialog.
			// Note: IE 8 would need this wrapped in a fake setTimeout `0`.
			$focusedBefore.focus();
		});

	function iframeLoaded() {
		var $iframe = tbWindow.find( '#TB_iframeContent' );

		// Get the iframe body.
		$iframeBody = $iframe.contents().find( 'body' );

		// Get the tabbable elements and handle the keydown event on first load.
		handleTabbables();

		// Set initial focus on the "Close" button.
		$firstTabbable.focus();

		/*
		 * When the "Install" button is disabled (e.g. the Module is already installed)
		 * then we can't predict where the last focusable element is. We need to get
		 * the tabbable elements and handle the keydown event again and again,
		 * each time the active tab panel changes.
		 */
		$( '#module-information-tabs a', $iframeBody ).on( 'click', function() {
			handleTabbables();
		});

		// Close the modal when pressing Escape.
		$iframeBody.on( 'keydown', function( event ) {
			if ( 27 !== event.which ) {
				return;
			}
			tb_remove();
		});
	}

	/*
	 * Get the tabbable elements and detach/attach the keydown event.
	 * Called after the iframe has fully loaded so we have all the elements we need.
	 * Called again each time a Tab gets clicked.
	 * @todo Consider to implement a MandarinCMS general utility for this and don't use jQuery UI.
	 */
	function handleTabbables() {
		var $firstAndLast;
		// Get all the tabbable elements.
		$tabbables = $( ':tabbable', $iframeBody );
		// Our first tabbable element is always the "Close" button.
		$firstTabbable = tbWindow.find( '#TB_closeWindowButton' );
		// Get the last tabbable element.
		$lastTabbable = $tabbables.last();
		// Make a jQuery collection.
		$firstAndLast = $firstTabbable.add( $lastTabbable );
		// Detach any previously attached keydown event.
		$firstAndLast.off( 'keydown.mcms-module-details' );
		// Attach again the keydown event on the first and last focusable elements.
		$firstAndLast.on( 'keydown.mcms-module-details', function( event ) {
			constrainTabbing( event );
		});
	}

	// Constrain tabbing within the module modal dialog.
	function constrainTabbing( event ) {
		if ( 9 !== event.which ) {
			return;
		}

		if ( $lastTabbable[0] === event.target && ! event.shiftKey ) {
			event.preventDefault();
			$firstTabbable.focus();
		} else if ( $firstTabbable[0] === event.target && event.shiftKey ) {
			event.preventDefault();
			$lastTabbable.focus();
		}
	}

	/*
	 * Open the Module details modal. The event is delegated to get also the links
	 * in the modules search tab, after the AJAX search rebuilds the HTML. It's
	 * delegated on the closest ancestor and not on the body to avoid conflicts
	 * with other handlers, see Trac ticket #43082.
	 */
	$( '.wrap' ).on( 'click', '.thickbox.open-module-details-modal', function( e ) {
		// The `data-title` attribute is used only in the Module screens.
		var title = $( this ).data( 'title' ) ? moduleinstallL10n.module_information + ' ' + $( this ).data( 'title' ) : moduleinstallL10n.module_modal_label;

		e.preventDefault();
		e.stopPropagation();

		// Store the element that has focus before opening the modal dialog, i.e. the control which opens it.
		$focusedBefore = $( this );

		tb_click.call(this);

		// Set ARIA role, ARIA label, and add a CSS class.
		tbWindow
			.attr({
				'role': 'dialog',
				'aria-label': moduleinstallL10n.module_modal_label
			})
			.addClass( 'module-details-modal' );

		// Set title attribute on the iframe.
		tbWindow.find( '#TB_iframeContent' ).attr( 'title', title );
	});

	/* Module install related JS */
	$( '#module-information-tabs a' ).click( function( event ) {
		var tab = $( this ).attr( 'name' );
		event.preventDefault();

		// Flip the tab
		$( '#module-information-tabs a.current' ).removeClass( 'current' );
		$( this ).addClass( 'current' );

		// Only show the fyi box in the description section, on smaller screen, where it's otherwise always displayed at the top.
		if ( 'description' !== tab && $( window ).width() < 772 ) {
			$( '#module-information-content' ).find( '.fyi' ).hide();
		} else {
			$( '#module-information-content' ).find( '.fyi' ).show();
		}

		// Flip the content.
		$( '#section-holder div.section' ).hide(); // Hide 'em all.
		$( '#section-' + tab ).show();
	});

	/*
	 * When a user presses the "Upload Module" button, show the upload form in place
	 * rather than sending them to the devoted upload module page.
	 * The `?tab=upload` page still exists for no-js support and for modules that
	 * might access it directly. When we're in this page, let the link behave
	 * like a link. Otherwise we're in the normal module installer pages and the
	 * link should behave like a toggle button.
	 */
	if ( ! $wrap.hasClass( 'module-install-tab-upload' ) ) {
		$uploadViewToggle
			.attr({
				role: 'button',
				'aria-expanded': 'false'
			})
			.on( 'click', function( event ) {
				event.preventDefault();
				$body.toggleClass( 'show-upload-view' );
				$uploadViewToggle.attr( 'aria-expanded', $body.hasClass( 'show-upload-view' ) );
			});
	}
});
