/**
 * Gmap Block dashboard behaviour.
 *
 * Two small enhancements, no dependencies:
 * - reveal / hide the stored API key
 * - swap the video poster for the YouTube player on click
 *
 * @package GmapBlock
 */
( function () {
	'use strict';

	function bindApiKeyToggle() {
		var toggles = document.querySelectorAll( '[data-gmapb-toggle]' );

		Array.prototype.forEach.call( toggles, function ( toggle ) {
			toggle.addEventListener( 'click', function () {
				var input = document.getElementById( toggle.getAttribute( 'data-gmapb-toggle' ) );

				if ( ! input ) {
					return;
				}

				var reveal = 'password' === input.type;

				input.type = reveal ? 'text' : 'password';
				toggle.setAttribute( 'aria-pressed', reveal ? 'true' : 'false' );
			} );
		} );
	}

	function bindVideoFacade() {
		var thumbs = document.querySelectorAll( '[data-gmapb-video]' );

		Array.prototype.forEach.call( thumbs, function ( thumb ) {
			thumb.addEventListener( 'click', function () {
				var id = thumb.getAttribute( 'data-gmapb-video' );

				if ( ! id || thumb.querySelector( 'iframe' ) ) {
					return;
				}

				var iframe = document.createElement( 'iframe' );

				iframe.src = 'https://www.youtube-nocookie.com/embed/' + encodeURIComponent( id ) + '?autoplay=1&rel=0';
				iframe.title = thumb.getAttribute( 'aria-label' ) || 'Gmap Block tutorial';
				iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share';
				iframe.allowFullscreen = true;
				iframe.setAttribute( 'frameborder', '0' );

				thumb.innerHTML = '';
				thumb.appendChild( iframe );
				thumb.style.backgroundImage = 'none';
				thumb.style.cursor = 'default';
			} );
		} );
	}

	/**
	 * "Test this key" — proves whether Google accepts the key from this site.
	 *
	 * The check runs inside a same-origin iframe: the Maps API can only load
	 * once per browsing context, so a throwaway frame is what allows testing
	 * again after changing the key. The key is handed over by postMessage so it
	 * never appears in a URL.
	 */
	function bindKeyTest() {
		var host = document.querySelector( '[data-gmapb-keytest]' );

		if ( ! host ) {
			return;
		}

		var button = host.querySelector( '[data-gmapb-keytest-run]' );
		var out = host.querySelector( '[data-gmapb-keytest-out]' );
		var input = document.getElementById( 'gmapb-api-key' );
		var frame = null;
		var timeout = null;

		function show( html, state ) {
			out.hidden = false;
			out.className = 'gmapb-keytest__out is-' + state;
			out.innerHTML = html;
		}

		function row( ok, label ) {
			return '<span class="gmapb-keytest__row is-' + ( ok ? 'ok' : 'bad' ) + '">' +
				( ok ? '✓' : '✗' ) + ' ' + label + '</span>';
		}

		function cleanup() {
			window.clearTimeout( timeout );
			window.removeEventListener( 'message', onMessage );

			if ( frame && frame.parentNode ) {
				frame.parentNode.removeChild( frame );
			}

			frame = null;
			button.disabled = false;
		}

		function onMessage( event ) {
			if ( event.origin !== window.location.origin || ! event.data ) {
				return;
			}

			if ( 'gmap-key-test-ready' === event.data.type ) {
				frame.contentWindow.postMessage(
					{ type: 'gmap-key-test', key: input.value.trim() },
					window.location.origin
				);
				return;
			}

			if ( 'gmap-key-test-result' !== event.data.type ) {
				return;
			}

			var r = event.data.result;
			var lines;

			if ( ! r.scriptLoaded ) {
				show( row( false, 'Google did not respond. Check your connection and try again.' ), 'bad' );
			} else if ( r.authFailed ) {
				// gm_authFailure is authoritative when it fires.
				lines = row( false, 'Google rejected this key' ) +
					'<span class="gmapb-keytest__note">Enable <strong>Maps JavaScript API</strong> for this key’s project, ' +
					'confirm a billing account is linked, and allow this site’s domain if the key is restricted.</span>';
				show( lines, 'bad' );
			} else if ( r.placesNew ) {
				lines = row( true, 'Google raised no authorisation error' ) +
					row( true, 'Places API (New) responded' ) +
					'<span class="gmapb-keytest__note">This key works from this site.</span>';
				show( lines, 'ok' );
			} else {
				// Ambiguous on purpose: silence from gm_authFailure does not
				// prove the key is good, and Places can fail for two reasons.
				lines = row( true, 'Google raised no authorisation error' ) +
					row( false, 'Places API (New) did not respond' ) +
					'<span class="gmapb-keytest__note">Either <strong>Places API (New)</strong> is not enabled for this ' +
					'project, or the key is not valid. Location search in the editor needs it. Check both, then re-test.</span>';
				show( lines, 'warn' );
			}

			cleanup();
		}

		button.addEventListener( 'click', function () {
			if ( ! input || ! input.value.trim() ) {
				show( row( false, 'Enter an API key first.' ), 'bad' );
				return;
			}

			button.disabled = true;
			show( 'Testing…', 'busy' );

			window.addEventListener( 'message', onMessage );

			frame = document.createElement( 'iframe' );
			frame.setAttribute( 'aria-hidden', 'true' );
			frame.style.cssText = 'position:absolute;width:0;height:0;border:0;left:-9999px';
			frame.src = host.getAttribute( 'data-harness' );
			document.body.appendChild( frame );

			// Belt and braces: the harness has its own stop, this covers a frame
			// that never loads at all.
			timeout = window.setTimeout( function () {
				show( row( false, 'The test timed out. Try again.' ), 'bad' );
				cleanup();
			}, 20000 );
		} );
	}

	function init() {
		bindApiKeyToggle();
		bindVideoFacade();
		bindKeyTest();
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
}() );
