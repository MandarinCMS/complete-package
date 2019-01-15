<?php

if ( ! defined( 'BASED_TREE_URI' ) ) {
	status_header( 403 );
	die();
}

?>
(function(){
	'use strict';

	var mcmsseoHelpscoutBeaconL10n = <?php echo $data; ?>;

	if ( mcmsseoHelpscoutBeaconL10n.type === 'no_search' ) {
		/* jshint ignore:start */
		!function(e,o,n){window.HSCW=o,window.HS=n,n.beacon=n.beacon||{};var t=n.beacon;t.userConfig={},t.readyQueue=[],t.config=function(e){this.userConfig=e},t.ready=function(e){this.readyQueue.push(e)},o.config={docs:{enabled:!1,baseUrl:""},contact:{enabled:!0,formId:"8a00db59-227f-11e6-aae8-0a7d6919297d"}};var r=e.getElementsByTagName("script")[0],c=e.createElement("script");c.type="text/javascript",c.async=!0,c.src="https://djtflbt20bdde.cloudfront.net/",r.parentNode.insertBefore(c,r)}(document,window.HSCW||{},window.HS||{});
		/* jshint ignore:end */
	} else {
		/* jshint ignore:start */
		!function(e,o,n){window.HSCW=o,window.HS=n,n.beacon=n.beacon||{};var t=n.beacon;t.userConfig={},t.readyQueue=[],t.config=function(e){this.userConfig=e},t.ready=function(e){this.readyQueue.push(e)},o.config={docs:{enabled:!0,baseUrl:"//ultimatum.helpscoutdocs.com/"},contact:{enabled:!0,formId:"f9665afe-77cd-11e5-8846-0e599dc12a51"}};var r=e.getElementsByTagName("script")[0],c=e.createElement("script");c.type="text/javascript",c.async=!0,c.src="https://djtflbt20bdde.cloudfront.net/",r.parentNode.insertBefore(c,r)}(document,window.HSCW||{},window.HS||{});
		/* jshint ignore:end */
	}

	HS.beacon.get_helpscout_beacon_identity = function() {
		return mcmsseoHelpscoutBeaconL10n.identify;
	};

	HS.beacon.config( mcmsseoHelpscoutBeaconL10n.config );
	HS.beacon.ready( function() {
		HS.beacon.identify( mcmsseoHelpscoutBeaconL10n.identify );
		HS.beacon.suggest( mcmsseoHelpscoutBeaconL10n.suggest );
	} );
}());
