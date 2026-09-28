/**
 * Lau & Choi accessibility fixes (WCAG 2.2 AA).
 *
 * Patches behaviour in GP Premium, IvyForms and GTranslate without editing
 * those plugins, so their updates don't wipe the fixes.
 */
( function () {
	'use strict';

	var FOCUSABLE = 'a[href], button:not([disabled]), input:not([disabled]):not([type="hidden"]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

	function isVisible( el ) {
		return !! ( el.offsetWidth || el.offsetHeight || el.getClientRects().length );
	}

	/**
	 * Run a callback now and whenever matching elements are added later
	 * (IvyForms renders with Vue, GTranslate injects its widget on load).
	 */
	function whenPresent( selector, callback ) {
		var run = function () {
			document.querySelectorAll( selector ).forEach( function ( el ) {
				if ( ! el.dataset.lcA11y ) {
					el.dataset.lcA11y = '1';
					callback( el );
				}
			} );
		};

		run();
		new MutationObserver( run ).observe( document.body, { childList: true, subtree: true } );
	}

	/**
	 * GP Premium slide-out menu.
	 *
	 * - 4.1.2: offside.js only resets aria-expanded on the first .menu-toggle in the
	 *   page, so the mobile header toggle keeps announcing "expanded" after closing.
	 * - 2.4.3: closing with the Close button drops focus to <body>.
	 * - 2.4.11: Tab can leave the open panel for page content hidden behind the overlay.
	 */
	function slideoutMenu() {
		var panel = document.getElementById( 'generate-slideout-menu' );

		if ( ! panel ) {
			return;
		}

		var opener = null;

		document.addEventListener( 'click', function ( e ) {
			var toggle = e.target.closest( '.main-navigation .menu-toggle, .slideout-toggle a' );

			if ( toggle && ! panel.contains( toggle ) ) {
				opener = toggle;
			}
		}, true );

		new MutationObserver( function () {
			if ( panel.classList.contains( 'is-open' ) ) {
				return;
			}

			document.querySelectorAll( '.main-navigation .menu-toggle[aria-expanded="true"]' ).forEach( function ( toggle ) {
				toggle.setAttribute( 'aria-expanded', 'false' );
			} );

			var active = document.activeElement;

			if ( opener && isVisible( opener ) && ( ! active || active === document.body || panel.contains( active ) ) ) {
				opener.focus();
			}
		} ).observe( panel, { attributes: true, attributeFilter: [ 'class' ] } );

		document.addEventListener( 'keydown', function ( e ) {
			if ( 'Tab' !== e.key || ! panel.classList.contains( 'is-open' ) ) {
				return;
			}

			var items = Array.prototype.filter.call( panel.querySelectorAll( FOCUSABLE ), isVisible );

			if ( ! items.length ) {
				return;
			}

			var first = items[ 0 ];
			var last = items[ items.length - 1 ];

			if ( ! panel.contains( document.activeElement ) ) {
				e.preventDefault();
				first.focus();
			} else if ( e.shiftKey && document.activeElement === first ) {
				e.preventDefault();
				last.focus();
			} else if ( ! e.shiftKey && document.activeElement === last ) {
				e.preventDefault();
				first.focus();
			}
		} );
	}

	/**
	 * IvyForms hard-codes autocomplete="off" on every input, which fails 1.3.5
	 * (Identify Input Purpose). Map fields to the matching autocomplete tokens.
	 */
	function formAutocomplete() {
		var byLabel = [
			[ /first name|given name/i, 'given-name' ],
			[ /last name|surname|family name/i, 'family-name' ],
			[ /\bname\b/i, 'name' ],
			[ /company|organi[sz]ation/i, 'organization' ],
			[ /phone/i, 'tel' ],
		];

		whenPresent( '[class*="ivyforms-field__"] input', function ( input ) {
			var field = input.closest( '[class*="ivyforms-field__"]' );
			var token = '';

			if ( 'email' === input.type || field.matches( '.ivyforms-field__email' ) ) {
				token = 'email';
			} else if ( 'tel' === input.type || field.matches( '.ivyforms-field__phone' ) ) {
				token = 'tel';
			} else if ( field.matches( '.ivyforms-field__website' ) ) {
				token = 'url';
			} else {
				var label = input.getAttribute( 'aria-label' ) || '';

				for ( var i = 0; i < byLabel.length; i++ ) {
					if ( byLabel[ i ][ 0 ].test( label ) ) {
						token = byLabel[ i ][ 1 ];
						break;
					}
				}
			}

			if ( token ) {
				input.setAttribute( 'autocomplete', token );
			}
		} );
	}

	/**
	 * GTranslate floating switcher opens from a plain <div>, so keyboard users
	 * can't reach it (2.1.1, 4.1.2). Turn it into a disclosure button.
	 */
	function languageSwitcher() {
		whenPresent( '.gt_float_switcher', function ( switcher ) {
			var button = switcher.querySelector( '.gt-selected' );
			var options = switcher.querySelector( '.gt_options' );

			if ( ! button || ! options ) {
				return;
			}

			// Flags sit next to the language name, so they're decorative.
			switcher.querySelectorAll( 'img' ).forEach( function ( img ) {
				img.setAttribute( 'alt', '' );
			} );

			button.setAttribute( 'role', 'button' );
			button.setAttribute( 'tabindex', '0' );
			button.setAttribute( 'aria-expanded', 'false' );

			var isOpen = function () {
				return options.classList.contains( 'gt-open' );
			};

			var updateName = function () {
				var code = ( button.querySelector( '.gt-lang-code' ) || {} ).textContent || '';
				var match = options.querySelector( 'a[data-gt-lang="' + code.trim() + '"]' );
				var name = match ? match.textContent.trim() : code.trim().toUpperCase();

				button.setAttribute( 'aria-label', 'Change language, current: ' + name );
			};

			updateName();
			new MutationObserver( updateName ).observe( button, { childList: true, subtree: true, characterData: true } );

			new MutationObserver( function () {
				button.setAttribute( 'aria-expanded', isOpen() ? 'true' : 'false' );
			} ).observe( options, { attributes: true, attributeFilter: [ 'class' ] } );

			button.addEventListener( 'keydown', function ( e ) {
				if ( 'Enter' !== e.key && ' ' !== e.key ) {
					return;
				}

				e.preventDefault();
				var opening = ! isOpen();
				button.click();

				// The options come before the button in the DOM, so move focus into them.
				if ( opening ) {
					setTimeout( function () {
						var first = Array.prototype.filter.call( options.querySelectorAll( 'a' ), isVisible )[ 0 ];

						if ( first ) {
							first.focus();
						}
					}, 250 );
				}
			} );

			switcher.addEventListener( 'keydown', function ( e ) {
				if ( 'Escape' === e.key && isOpen() ) {
					button.click();
					button.focus();
				}
			} );

			switcher.addEventListener( 'focusout', function ( e ) {
				if ( isOpen() && ! switcher.contains( e.relatedTarget ) ) {
					button.click();
				}
			} );
		} );
	}

	slideoutMenu();
	formAutocomplete();
	languageSwitcher();
}() );
