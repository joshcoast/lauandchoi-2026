<?php
/**
 * Isolated harness for the "Test this key" button.
 *
 * Loaded inside a same-origin iframe. Waits for the parent to post a key, loads
 * the Google Maps JS API with it, and reports back what actually worked.
 *
 * @since 1.3.0
 * @package GmapBlock
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<title>Gmap key test</title>
</head>
<body>
<script>
( function () {
	'use strict';

	var ORIGIN = window.location.origin;
	var reported = false;

	/*
	 * What can actually be measured here, and what cannot:
	 *
	 * - gm_authFailure is authoritative WHEN IT FIRES. It covers the common
	 *   real-world failures: Maps JavaScript API not enabled, referrer not
	 *   allowed, no billing account. It does NOT fire for a merely malformed
	 *   key, so its silence proves nothing.
	 * - `new google.maps.Map()` never throws on a bad key; it renders an error
	 *   tile. So it is useless as a pass signal — it is constructed here purely
	 *   to provoke gm_authFailure, which needs a real map to evaluate the key.
	 * - The Places request is the only genuine functional probe: it returns
	 *   suggestions on a working key and nothing on a broken or unentitled one.
	 *
	 * The script body is byte-identical for valid and invalid keys, so there is
	 * no server-side shortcut either. Report only these two facts and never
	 * claim more.
	 */
	var result = {
		scriptLoaded: false,   // the API bundle downloaded at all
		authFailed: false,     // Google explicitly rejected the key
		placesNew: false,      // Places API (New) actually answered
		error: ''
	};

	function report() {
		if ( reported ) {
			return;
		}
		reported = true;
		window.parent.postMessage( { type: 'gmap-key-test-result', result: result }, ORIGIN );
	}

	// Google calls this for any auth problem: bad key, API not enabled,
	// referrer not allowed, no billing account.
	window.gm_authFailure = function () {
		result.authFailed = true;
		report();
	};

	window.__gmapKeyTestReady = function () {
		result.scriptLoaded = true;

		var probe = document.createElement( 'div' );
		probe.style.cssText = 'width:240px;height:240px;position:absolute;left:-9999px';
		document.body.appendChild( probe );

		try {
			new google.maps.Map( probe, { center: { lat: 0, lng: 0 }, zoom: 3 } );
		} catch ( e ) {
			result.error = e.message;
			report();
			return;
		}

		// Give gm_authFailure time to arrive before probing anything else.
		setTimeout( function () {
			if ( result.authFailed ) {
				report();
				return;
			}

			if ( ! google.maps.importLibrary ) {
				report();
				return;
			}

			google.maps
				.importLibrary( 'places' )
				.then( function ( lib ) {
					if ( ! lib || ! lib.AutocompleteSuggestion || ! lib.AutocompleteSessionToken ) {
						return null;
					}

					return lib.AutocompleteSuggestion.fetchAutocompleteSuggestions( {
						input: 'London',
						sessionToken: new lib.AutocompleteSessionToken()
					} );
				} )
				.then( function ( res ) {
					// Places API (New) disabled resolves successfully but empty.
					result.placesNew = !! ( res && res.suggestions && res.suggestions.length );
					report();
				} )
				.catch( function () {
					result.placesNew = false;
					report();
				} );
		}, 2500 );
	};

	function runTest( key ) {
		var script = document.createElement( 'script' );

		script.src =
			'https://maps.googleapis.com/maps/api/js?key=' +
			encodeURIComponent( key ) +
			'&libraries=places&v=3.66&callback=__gmapKeyTestReady';
		script.async = true;
		script.onerror = function () {
			result.error = 'network';
			report();
		};

		document.head.appendChild( script );

		// Hard stop so the UI can never hang waiting on Google.
		setTimeout( report, 15000 );
	}

	window.addEventListener( 'message', function ( event ) {
		if ( event.origin !== ORIGIN ) {
			return;
		}

		if ( ! event.data || event.data.type !== 'gmap-key-test' || ! event.data.key ) {
			return;
		}

		runTest( event.data.key );
	} );

	window.parent.postMessage( { type: 'gmap-key-test-ready' }, ORIGIN );
}() );
</script>
</body>
</html>
