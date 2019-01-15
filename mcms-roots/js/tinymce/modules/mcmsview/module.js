/**
 * MandarinCMS View module.
 */
( function( tinymce, mcms ) {
	tinymce.ModuleManager.add( 'mcmsview', function( editor ) {
		function noop () {}

		if ( ! mcms || ! mcms.mce || ! mcms.mce.views ) {
			return {
				getView: noop
			};
		}

		// Check if a node is a view or not.
		function isView( node ) {
			return editor.dom.hasClass( node, 'mcmsview' );
		}

		// Replace view tags with their text.
		function resetViews( content ) {
			function callback( match, $1 ) {
				return '<p>' + window.decodeURIComponent( $1 ) + '</p>';
			}

			if ( ! content ) {
				return content;
			}

			return content
				.replace( /<div[^>]+data-mcmsview-text="([^"]+)"[^>]*>(?:\.|[\s\S]+?mcmsview-end[^>]+>\s*<\/span>\s*)?<\/div>/g, callback )
				.replace( /<p[^>]+data-mcmsview-marker="([^"]+)"[^>]*>[\s\S]*?<\/p>/g, callback );
		}

		editor.on( 'init', function() {
			var MutationObserver = window.MutationObserver || window.WebKitMutationObserver;

			if ( MutationObserver ) {
				new MutationObserver( function() {
					editor.fire( 'mcms-body-class-change' );
				} )
				.observe( editor.getBody(), {
					attributes: true,
					attributeFilter: ['class']
				} );
			}

			// Pass on body class name changes from the editor to the mcmsView iframes.
			editor.on( 'mcms-body-class-change', function() {
				var className = editor.getBody().className;

				editor.$( 'iframe[class="mcmsview-sandbox"]' ).each( function( i, iframe ) {
					// Make sure it is a local iframe
					// jshint scripturl: true
					if ( ! iframe.src || iframe.src === 'javascript:""' ) {
						try {
							iframe.contentWindow.document.body.className = className;
						} catch( er ) {}
					}
				});
			} );
		});

		// Scan new content for matching view patterns and replace them with markers.
		editor.on( 'beforesetcontent', function( event ) {
			var node;

			if ( ! event.selection ) {
				mcms.mce.views.unbind();
			}

			if ( ! event.content ) {
				return;
			}

			if ( ! event.load ) {
				node = editor.selection.getNode();

				if ( node && node !== editor.getBody() && /^\s*https?:\/\/\S+\s*$/i.test( event.content ) ) {
					// When a url is pasted or inserted, only try to embed it when it is in an empty paragrapgh.
					node = editor.dom.getParent( node, 'p' );

					if ( node && /^[\s\uFEFF\u00A0]*$/.test( editor.$( node ).text() || '' ) ) {
						// Make sure there are no empty inline elements in the <p>
						node.innerHTML = '';
					} else {
						return;
					}
				}
			}

			event.content = mcms.mce.views.setMarkers( event.content, editor );
		} );

		// Replace any new markers nodes with views.
		editor.on( 'setcontent', function() {
			mcms.mce.views.render();
		} );

		// Empty view nodes for easier processing.
		editor.on( 'preprocess hide', function( event ) {
			editor.$( 'div[data-mcmsview-text], p[data-mcmsview-marker]', event.node ).each( function( i, node ) {
				node.innerHTML = '.';
			} );
		}, true );

		// Replace views with their text.
		editor.on( 'postprocess', function( event ) {
			event.content = resetViews( event.content );
		} );

		// Replace views with their text inside undo levels.
		// This also prevents that new levels are added when there are changes inside the views.
		editor.on( 'beforeaddundo', function( event ) {
			event.level.content = resetViews( event.level.content );
		} );

		// Make sure views are copied as their text.
		editor.on( 'drop objectselected', function( event ) {
			if ( isView( event.targetClone ) ) {
				event.targetClone = editor.getDoc().createTextNode(
					window.decodeURIComponent( editor.dom.getAttrib( event.targetClone, 'data-mcmsview-text' ) )
				);
			}
		} );

		// Clean up URLs for easier processing.
		editor.on( 'pastepreprocess', function( event ) {
			var content = event.content;

			if ( content ) {
				content = tinymce.trim( content.replace( /<[^>]+>/g, '' ) );

				if ( /^https?:\/\/\S+$/i.test( content ) ) {
					event.content = content;
				}
			}
		} );

		// Show the view type in the element path.
		editor.on( 'resolvename', function( event ) {
			if ( isView( event.target ) ) {
				event.name = editor.dom.getAttrib( event.target, 'data-mcmsview-type' ) || 'object';
			}
		} );

		// See `media` module.
		editor.on( 'click keyup', function() {
			var node = editor.selection.getNode();

			if ( isView( node ) ) {
				if ( editor.dom.getAttrib( node, 'data-mce-selected' ) ) {
					node.setAttribute( 'data-mce-selected', '2' );
				}
			}
		} );

		editor.addButton( 'mcms_view_edit', {
			tooltip: 'Edit ', // trailing space is needed, used for context
			icon: 'dashicon dashicons-edit',
			onclick: function() {
				var node = editor.selection.getNode();

				if ( isView( node ) ) {
					mcms.mce.views.edit( editor, node );
				}
			}
		} );

		editor.addButton( 'mcms_view_remove', {
			tooltip: 'Remove',
			icon: 'dashicon dashicons-no',
			onclick: function() {
				editor.fire( 'cut' );
			}
		} );

		editor.once( 'preinit', function() {
			var toolbar;

			if ( editor.mcms && editor.mcms._createToolbar ) {
				toolbar = editor.mcms._createToolbar( [
					'mcms_view_edit',
					'mcms_view_remove'
				] );

				editor.on( 'mcmstoolbar', function( event ) {
					if ( ! event.collapsed && isView( event.element ) ) {
						event.toolbar = toolbar;
					}
				} );
			}
		} );

		editor.mcms = editor.mcms || {};
		editor.mcms.getView = noop;
		editor.mcms.setViewCursor = noop;

		return {
			getView: noop
		};
	} );
} )( window.tinymce, window.mcms );
