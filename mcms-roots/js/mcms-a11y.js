/** @namespace mcms */
window.mcms = window.mcms || {};

( function ( mcms, $ ) {
	'use strict';

	var $containerPolite,
		$containerAssertive,
		previousMessage = '';

	/**
	 * Update the ARIA live notification area text node.
	 *
	 * @since 4.2.0
	 * @since 4.3.0 Introduced the 'ariaLive' argument.
	 *
	 * @param {String} message    The message to be announced by Assistive Technologies.
	 * @param {String} [ariaLive] The politeness level for aria-live. Possible values:
	 *                            polite or assertive. Default polite.
	 * @returns {void}
	 */
	function speak( message, ariaLive ) {
		// Clear previous messages to allow repeated strings being read out.
		clear();

		// Ensure only text is sent to screen readers.
		message = $( '<p>' ).html( message ).text();

		/*
		 * Safari 10+VoiceOver don't announce repeated, identical strings. We use
		 * a `no-break space` to force them to think identical strings are different.
		 * See ticket #36853.
		 */
		if ( previousMessage === message ) {
			message = message + '\u00A0';
		}

		previousMessage = message;

		if ( $containerAssertive && 'assertive' === ariaLive ) {
			$containerAssertive.text( message );
		} else if ( $containerPolite ) {
			$containerPolite.text( message );
		}
	}

	/**
	 * Build the live regions markup.
	 *
	 * @since 4.3.0
	 *
	 * @param {String} ariaLive Optional. Value for the 'aria-live' attribute, default 'polite'.
	 *
	 * @return {Object} $container The ARIA live region jQuery object.
	 */
	function addContainer( ariaLive ) {
		ariaLive = ariaLive || 'polite';

		var $container = $( '<div>', {
			'id': 'mcms-a11y-speak-' + ariaLive,
			'aria-live': ariaLive,
			'aria-relevant': 'additions text',
			'aria-atomic': 'true',
			'class': 'screen-reader-text mcms-a11y-speak-region'
		});

		$( document.body ).append( $container );
		return $container;
	}

	/**
	 * Clear the live regions.
	 *
	 * @since 4.3.0
	 */
	function clear() {
		$( '.mcms-a11y-speak-region' ).text( '' );
	}

	/**
	 * Initialize mcms.a11y and define ARIA live notification area.
	 *
	 * @since 4.2.0
	 * @since 4.3.0 Added the assertive live region.
	 */
	$( document ).ready( function() {
		$containerPolite = $( '#mcms-a11y-speak-polite' );
		$containerAssertive = $( '#mcms-a11y-speak-assertive' );

		if ( ! $containerPolite.length ) {
			$containerPolite = addContainer( 'polite' );
		}

		if ( ! $containerAssertive.length ) {
			$containerAssertive = addContainer( 'assertive' );
		}
	});

	/** @namespace mcms.a11y */
	mcms.a11y = mcms.a11y || {};
	mcms.a11y.speak = speak;

}( window.mcms, window.jQuery ));
