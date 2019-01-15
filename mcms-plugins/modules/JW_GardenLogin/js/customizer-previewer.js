/**
 * Customizer Previewer
 * @since 1.0.23
 */
( function ( mcms, $ ) {
	"use strict";

	// Bail if the customizer isn't initialized
	if ( ! mcms || ! mcms.customize ) {
		return;
	}

	var api = mcms.customize, OldPreview;

	// Custom Customizer Preview class (attached to the Customize API)
	api.myCustomizerPreview = {
		// Init
		init: function () {

				var $body = $('body'),
				$body_bg 	= $('#login h1'),
				$form			= $('#login form'),
				$button 	= $('#login .submit'),
				$nav 			= $('#nav a:first-child'),
				$document = $( document ); // Store references to the body and document elements

				// Append our button to the <body> element
				if( $('.login-action-login').length > 0 ) { // If .login-action-login exist

					$body_bg.append( '<span class="gardenlogin-logo-partial gardenlogin-partial customize-partial-edit-shortcut" data-title="Change Logo"><button class="gardenlogin-event-button customize-partial-edit-shortcut-button" data-customizer-event="customize_logo_section"><span class="dashicons dashicons-edit"></span></button></span>' );

					$body.append( '<span class="gardenlogin-presets-partial gardenlogin-partial customize-partial-edit-shortcut" data-title="Change Template"><button class="gardenlogin-event-button customize-partial-edit-shortcut-button" data-customizer-event="customize_presets"><span class="dashicons dashicons-admin-appearance"></span></button></span>' );

					$body.append( '<span class="gardenlogin-background-partial gardenlogin-partial customize-partial-edit-shortcut" data-title="Change Background"><button class="gardenlogin-event-button customize-partial-edit-shortcut-button" data-customizer-event="section_background"><span class="dashicons dashicons-images-alt"></span></button></span>' );

					$body.append( '<span class="gardenlogin-footer-partial gardenlogin-partial customize-partial-edit-shortcut" data-title="Change Footer"><button class="gardenlogin-event-button customize-partial-edit-shortcut-button" data-customizer-event="section_fotter"><span class="dashicons dashicons-edit"></span></button></span>' );

					$button.append( '<span class="gardenlogin-button-partial gardenlogin-partial customize-partial-edit-shortcut" data-title="Customize Button"><button class="gardenlogin-event-button customize-partial-edit-shortcut-button" data-customizer-event="section_button"><span class="dashicons dashicons-edit"></span></button></span>' );

					$( '<span class="gardenlogin-nav-partial gardenlogin-partial customize-partial-edit-shortcut" data-title="Customize Navigation"><button class="gardenlogin-event-button customize-partial-edit-shortcut-button" data-customizer-event="section_fotter"><span class="dashicons dashicons-edit"></span></button></span>' ).insertAfter($nav);

					$form.append( '<span class="gardenlogin-input-partial gardenlogin-partial customize-partial-edit-shortcut" data-title="Customize Form"><button class="gardenlogin-event-button customize-partial-edit-shortcut-button" data-customizer-event="section_form"><span class="dashicons dashicons-edit"></span></button></span>' );
				}

				// $form.append( '<span class="gardenlogin-form-partial gardenlogin-partial customize-partial-edit-shortcut"><button class="gardenlogin-event-button customize-partial-edit-shortcut-button" data-customizer-event="section_form"><span class="dashicons dashicons-edit"></span></button></span>' );

				/**
				 * Listen for events on the GardenLogin previewer button
				 */
				$document.on( 'touch click', '.gardenlogin-partial.customize-partial-edit-shortcut', function( e ) {

					var $el 		= $(this),
					$event 			= $el.children().data('customizer-event'),
					$title 			= ' .accordion-section-title',
					$panel 			= '#accordion-panel-gardenlogin_panel' + $title,
					$section 		= '#accordion-section-' + $event + $title,
					$customizer = parent.document;

						if( !$el.hasClass( "active" ) ) {

							$( $panel, $customizer ).trigger('click');
							$( $section, $customizer ).trigger('click');
						}

						$('.gardenlogin-partial.customize-partial-edit-shortcut').removeClass( 'active' );
						if($el.hasClass('gardenlogin-footer-partial')){
							$('.gardenlogin-nav-partial').addClass('active');
						}
						if($el.hasClass('gardenlogin-nav-partial')){
							$('.gardenlogin-footer-partial').addClass('active');
						}
						$el.addClass( 'active' );
				} );

				/**
				 * Prevent logo link for customizer
				 */
				$document.on( 'click touch', '.login h1 a', function( e ) {
					e.preventDefault();
				});

				/**
				 * Prevent Submit Button for customizer
				 */
				$document.on( 'click touch', '.submit, #backtoblog a', function( e ) {
					e.preventDefault();
				});
				/**
				 * Add spans to labels
				 */
				$(window).on('load',function(){
					$('label').each(function(){
						// console.log($(this).html());
						var headerClone = $(this).clone();
						$(headerClone).find('br').remove();
						$(headerClone).find('input').remove();
						var currentText = $(headerClone).html().replace(/(\r\n|\n|\r|\t)/gm,"");

						var newHtml = $(this).html().replace(currentText,"<span>"+currentText+"</span>");
						$(this).html(newHtml);
					});
				});


				/* remove border around all input elements */
				if (navigator.userAgent.toLowerCase().indexOf("chrome") >= 0) {
					$(window).load(function () {
						$('input:-webkit-autofill').each(function () {
							console.log($(this).length);
							var text = $(this).val();
							var sText = text.substring(text.length - 1, text.length);
							var id = $(this).attr('id');
							$(this).after(this.outerHTML).remove();
							$('input[id=' + id + ']').val(text.slice(0,-1));
							setTimeout(function(){
								$('input[id=' + id + ']').val(text.slice(0,-1)+sText);
							}, 1000)
						});
					});
				}


		}
	};

	/**
	 * Capture the instance of the Preview since it is private (this has changed in MandarinCMS 4.0)
	 */
	OldPreview = api.Preview;
	api.Preview = OldPreview.extend( {
		initialize: function( params, options ) {
			// Store a reference to the Preview
			api.myCustomizerPreview.preview = this;

			// Call the old Preview's initialize function
			OldPreview.prototype.initialize.call( this, params, options );
		}
	} );

	// Document ready
	$( function () {
		// Initialize our Preview
		api.myCustomizerPreview.init();
	} );
} )( window.mcms, jQuery );
