(function ( $ ) {
	var minHeight = '340px';

	function vc_jmcmslayer_resize( target ) {
		jmcmslayer( target ).onReady( function () {
			$( this.container ).css( 'min-height', minHeight );
		} );
		$( jmcmslayer( target ).container ).css( 'min-height', minHeight );
	}

	$( document ).on( 'ready', function () {
		$( "div" ).filter( function () {
			return this.id.match( /^jmcmslayer\-\d+$/ );
		} ).each( function () {
			vc_jmcmslayer_resize( this )
		} );
	} );
	$( window ).on( 'vc_reload', function () {
		$( "div" ).filter( function () {
			return this.id.match( /^jmcmslayer\-\d+$/ );
		} ).each( function () {
			vc_jmcmslayer_resize( this )
		} );
	} );

})( jQuery );