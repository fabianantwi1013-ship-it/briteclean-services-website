/**
 * Briteclean theme scripts.
 *
 * Deliberately small: a nav toggle and a scroll affordance. The booking form ships its
 * own script in the plugin. No framework, no jQuery dependency.
 */
( function () {
	'use strict';

	/**
	 * Mobile navigation toggle.
	 *
	 * The menu is a real <nav> that is visible by default and hidden by CSS at small
	 * widths, so it still works if this script fails to load.
	 */
	function initNav() {
		var toggle = document.querySelector( '.bc-nav-toggle' );
		var nav = document.getElementById( 'bc-primary-nav' );

		if ( ! toggle || ! nav ) {
			return;
		}

		function setOpen( open ) {
			nav.classList.toggle( 'is-open', open );
			toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
		}

		toggle.addEventListener( 'click', function () {
			setOpen( toggle.getAttribute( 'aria-expanded' ) !== 'true' );
		} );

		// Escape closes the menu and returns focus to the button that opened it.
		document.addEventListener( 'keydown', function ( event ) {
			if ( 'Escape' === event.key && nav.classList.contains( 'is-open' ) ) {
				setOpen( false );
				toggle.focus();
			}
		} );

		// Clicking outside the open menu dismisses it.
		document.addEventListener( 'click', function ( event ) {
			if ( ! nav.classList.contains( 'is-open' ) ) {
				return;
			}

			if ( ! nav.contains( event.target ) && ! toggle.contains( event.target ) ) {
				setOpen( false );
			}
		} );

		// Reset state when the viewport crosses into the desktop layout, so the menu is
		// not left in a stale "open" state that CSS no longer styles.
		var desktop = window.matchMedia( '(min-width: 980px)' );

		function handleBreakpoint( event ) {
			if ( event.matches ) {
				setOpen( false );
			}
		}

		if ( desktop.addEventListener ) {
			desktop.addEventListener( 'change', handleBreakpoint );
		} else if ( desktop.addListener ) {
			desktop.addListener( handleBreakpoint );
		}
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', initNav );
	} else {
		initNav();
	}
}() );
