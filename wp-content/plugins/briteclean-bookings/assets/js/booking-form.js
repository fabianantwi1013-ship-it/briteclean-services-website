/**
 * Briteclean multi-step booking form.
 *
 * Progressive enhancement: the markup is a complete, working form before this script
 * runs. The script adds the `is-enhanced` class, which is what the CSS keys the
 * stepper behaviour off. If this file fails to parse or never loads, the visitor sees
 * one long form that still submits and is still validated on the server.
 *
 * Client-side validation here is purely for faster feedback. Every rule is enforced
 * again in PHP, which is the only check that actually counts.
 */
( function () {
	'use strict';

	var L10N = window.britecleanBookingL10n || {};

	function t( key, fallback ) {
		return L10N[ key ] || fallback;
	}

	/**
	 * Set up one booking form.
	 *
	 * @param {HTMLFormElement} form The form element.
	 */
	function initForm( form ) {
		var steps = Array.prototype.slice.call( form.querySelectorAll( '.bc-step' ) );

		if ( steps.length < 2 ) {
			return;
		}

		var progressItems = Array.prototype.slice.call( form.querySelectorAll( '.bc-progress__item' ) );
		var counter = form.querySelector( '.bc-step-counter' );
		var backBtn = form.querySelector( '.bc-form__back' );
		var nextBtn = form.querySelector( '.bc-form__next' );
		var submitBtn = form.querySelector( '.bc-form__submit' );
		var reviewBox = form.querySelector( '[data-bc-review]' );

		var current = 0;

		form.classList.add( 'is-enhanced' );

		if ( backBtn ) { backBtn.hidden = false; }
		if ( nextBtn ) { nextBtn.hidden = false; }

		/**
		 * Show a step and update everything that depends on which step is active.
		 *
		 * @param {number}  index      Zero-based step index.
		 * @param {boolean} moveFocus  Whether to move focus into the new step.
		 */
		function showStep( index, moveFocus ) {
			current = Math.max( 0, Math.min( steps.length - 1, index ) );

			steps.forEach( function ( step, i ) {
				step.classList.toggle( 'is-active', i === current );
			} );

			progressItems.forEach( function ( item, i ) {
				item.classList.toggle( 'is-active', i === current );
				item.classList.toggle( 'is-done', i < current );
			} );

			if ( counter ) {
				counter.textContent = t( 'stepOf', 'Step %1$d of %2$d' )
					.replace( '%1$d', current + 1 )
					.replace( '%2$d', steps.length );
			}

			var isLast = current === steps.length - 1;

			if ( backBtn ) { backBtn.hidden = current === 0; }
			if ( nextBtn ) { nextBtn.hidden = isLast; }
			if ( submitBtn ) { submitBtn.hidden = ! isLast; }

			if ( isLast ) {
				buildReview();
			}

			if ( moveFocus ) {
				// Focus the step's heading rather than its first input: a screen reader
				// then announces what the step is before what to type.
				var legend = steps[ current ].querySelector( '.bc-step__title' );

				if ( legend ) {
					legend.setAttribute( 'tabindex', '-1' );
					legend.focus();
				}

				// Keep the form in view when steps differ a lot in height.
				var top = form.getBoundingClientRect().top + window.pageYOffset - 90;

				window.scrollTo( {
					top: top,
					behavior: prefersReducedMotion() ? 'auto' : 'smooth'
				} );
			}
		}

		function prefersReducedMotion() {
			return window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
		}

		/**
		 * Validate every field in one step.
		 *
		 * @param {HTMLElement} step The step element.
		 * @return {boolean} Whether the step is valid.
		 */
		function validateStep( step ) {
			var fields = Array.prototype.slice.call( step.querySelectorAll( '.bc-field' ) );
			var firstInvalid = null;

			fields.forEach( function ( field ) {
				if ( ! validateField( field ) && ! firstInvalid ) {
					firstInvalid = field;
				}
			} );

			if ( firstInvalid ) {
				var focusable = firstInvalid.querySelector( 'input, select, textarea' );

				if ( focusable ) {
					focusable.focus();
				}

				return false;
			}

			return true;
		}

		/**
		 * Validate a single field wrapper.
		 *
		 * @param {HTMLElement} field The .bc-field wrapper.
		 * @return {boolean} Whether it passes.
		 */
		function validateField( field ) {
			var errorEl = field.querySelector( '.bc-error' );
			var inputs = Array.prototype.slice.call( field.querySelectorAll( 'input, select, textarea' ) );

			if ( ! inputs.length ) {
				return true;
			}

			var message = '';
			var first = inputs[ 0 ];
			var type = first.type;

			if ( 'checkbox' === type || 'radio' === type ) {
				var isRequired = field.getAttribute( 'data-field' ) === 'services'
					|| inputs.some( function ( input ) { return input.required; } );

				var anyChecked = inputs.some( function ( input ) { return input.checked; } );

				if ( isRequired && ! anyChecked ) {
					message = 'checkbox' === type
						? t( 'chooseService', 'Please choose at least one service.' )
						: t( 'required', 'This field is required.' );
				}
			} else {
				var value = ( first.value || '' ).trim();

				if ( first.required && ! value ) {
					message = t( 'required', 'This field is required.' );
				} else if ( value && 'email' === type && ! isValidEmail( value ) ) {
					message = t( 'invalidEmail', 'Please enter a valid email address.' );
				} else if ( value && 'tel' === type && ! isValidPhone( value ) ) {
					message = t( 'invalidPhone', 'Please enter a valid phone number.' );
				} else if ( value && 'date' === type && isPastDate( value ) ) {
					message = t( 'pastDate', 'Please choose a date that has not already passed.' );
				}
			}

			setFieldError( field, errorEl, inputs, message );

			return ! message;
		}

		/**
		 * Apply or clear a field's error state.
		 *
		 * @param {HTMLElement}      field   Wrapper.
		 * @param {HTMLElement|null} errorEl Error paragraph.
		 * @param {Array}            inputs  Inputs in the field.
		 * @param {string}           message Error text, or empty to clear.
		 */
		function setFieldError( field, errorEl, inputs, message ) {
			field.classList.toggle( 'bc-field--error', !! message );

			if ( errorEl ) {
				errorEl.textContent = message;
				errorEl.hidden = ! message;
			}

			inputs.forEach( function ( input ) {
				if ( message ) {
					input.setAttribute( 'aria-invalid', 'true' );
				} else {
					input.removeAttribute( 'aria-invalid' );
				}
			} );
		}

		function isValidEmail( value ) {
			// Deliberately loose. The server uses is_email(); this only catches typos.
			return /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test( value );
		}

		function isValidPhone( value ) {
			var digits = value.replace( /\D+/g, '' );

			return digits.length >= 7 && digits.length <= 15;
		}

		function isPastDate( value ) {
			var picked = new Date( value + 'T00:00:00' );
			var today = new Date();

			today.setHours( 0, 0, 0, 0 );

			return picked < today;
		}

		/**
		 * Build the review summary from the current form values.
		 */
		function buildReview() {
			if ( ! reviewBox ) {
				return;
			}

			reviewBox.innerHTML = '';

			steps.forEach( function ( step, stepIndex ) {
				var fields = Array.prototype.slice.call( step.querySelectorAll( '.bc-field' ) );

				fields.forEach( function ( field ) {
					var label = getFieldLabel( field );
					var value = getFieldValue( field );

					if ( ! label ) {
						return;
					}

					var row = document.createElement( 'div' );
					row.className = 'bc-review__row';

					var labelEl = document.createElement( 'span' );
					labelEl.className = 'bc-review__label';
					labelEl.textContent = label;

					var valueEl = document.createElement( 'span' );
					valueEl.className = 'bc-review__value' + ( value ? '' : ' bc-review__value--empty' );
					valueEl.textContent = value || t( 'none', 'Not provided' );

					var edit = document.createElement( 'button' );
					edit.type = 'button';
					edit.className = 'bc-review__edit';
					edit.textContent = 'Edit';
					edit.addEventListener( 'click', function () {
						showStep( stepIndex, true );
					} );

					row.appendChild( labelEl );
					row.appendChild( valueEl );
					row.appendChild( edit );
					reviewBox.appendChild( row );
				} );
			} );
		}

		/**
		 * The human label for a field.
		 *
		 * @param {HTMLElement} field Wrapper.
		 * @return {string}
		 */
		function getFieldLabel( field ) {
			var label = field.querySelector( '.bc-label' );

			if ( ! label ) {
				return '';
			}

			// Strip the "*" and the screen-reader "(required)" suffix.
			var clone = label.cloneNode( true );

			Array.prototype.slice.call( clone.querySelectorAll( '.bc-req, .screen-reader-text' ) )
				.forEach( function ( node ) { node.remove(); } );

			return clone.textContent.trim();
		}

		/**
		 * The display value for a field.
		 *
		 * @param {HTMLElement} field Wrapper.
		 * @return {string}
		 */
		function getFieldValue( field ) {
			var checked = Array.prototype.slice.call( field.querySelectorAll( 'input:checked' ) );

			if ( checked.length ) {
				return checked.map( function ( input ) {
					var label = field.querySelector( 'label[for="' + input.id + '"]' );
					var text = label ? label.querySelector( '.bc-option__text' ) : null;

					return text ? text.textContent.trim() : input.value;
				} ).join( ', ' );
			}

			var select = field.querySelector( 'select' );

			if ( select ) {
				if ( ! select.value ) {
					return '';
				}

				return select.options[ select.selectedIndex ].text.trim();
			}

			var input = field.querySelector( 'input, textarea' );

			if ( ! input || 'checkbox' === input.type || 'radio' === input.type ) {
				return '';
			}

			var value = ( input.value || '' ).trim();

			// Show a date the way a person would read it, not as 2026-04-18.
			if ( 'date' === input.type && value ) {
				var parsed = new Date( value + 'T00:00:00' );

				if ( ! isNaN( parsed.getTime() ) ) {
					return parsed.toLocaleDateString( undefined, {
						weekday: 'long',
						year: 'numeric',
						month: 'long',
						day: 'numeric'
					} );
				}
			}

			return value;
		}

		/* ---- Wiring ---- */

		if ( nextBtn ) {
			nextBtn.addEventListener( 'click', function () {
				if ( validateStep( steps[ current ] ) ) {
					showStep( current + 1, true );
				}
			} );
		}

		if ( backBtn ) {
			backBtn.addEventListener( 'click', function () {
				showStep( current - 1, true );
			} );
		}

		// Clear a field's error as soon as the visitor fixes it, rather than making
		// them press Continue again to find out.
		form.addEventListener( 'input', function ( event ) {
			var field = event.target.closest ? event.target.closest( '.bc-field' ) : null;

			if ( field && field.classList.contains( 'bc-field--error' ) ) {
				validateField( field );
			}
		} );

		form.addEventListener( 'change', function ( event ) {
			var field = event.target.closest ? event.target.closest( '.bc-field' ) : null;

			if ( field && field.classList.contains( 'bc-field--error' ) ) {
				validateField( field );
			}
		} );

		// Enter should advance a step, not submit from step 1. Textareas keep their
		// normal newline behaviour.
		form.addEventListener( 'keydown', function ( event ) {
			if ( 'Enter' !== event.key || 'TEXTAREA' === event.target.tagName ) {
				return;
			}

			if ( current < steps.length - 1 ) {
				event.preventDefault();

				if ( validateStep( steps[ current ] ) ) {
					showStep( current + 1, true );
				}
			}
		} );

		form.addEventListener( 'submit', function ( event ) {
			// Validate every step, not just the visible one — someone can reach the
			// last step, go back, clear a required field, and return.
			var allValid = true;
			var firstBadStep = -1;

			steps.forEach( function ( step, index ) {
				var fields = Array.prototype.slice.call( step.querySelectorAll( '.bc-field' ) );

				fields.forEach( function ( field ) {
					if ( ! validateField( field ) ) {
						allValid = false;

						if ( firstBadStep === -1 ) {
							firstBadStep = index;
						}
					}
				} );
			} );

			if ( ! allValid ) {
				event.preventDefault();
				showStep( firstBadStep, true );

				return;
			}

			if ( submitBtn ) {
				submitBtn.disabled = true;
				submitBtn.textContent = t( 'submitting', 'Sending…' );
			}
		} );

		showStep( 0, false );

		// A server-side error sends the visitor back with the fields flagged; jump
		// straight to the first step that has one.
		var firstServerError = form.querySelector( '.bc-field--error' );

		if ( firstServerError ) {
			var errorStep = firstServerError.closest( '.bc-step' );
			var errorIndex = steps.indexOf( errorStep );

			if ( errorIndex > -1 ) {
				showStep( errorIndex, false );
			}
		}
	}

	function init() {
		Array.prototype.slice.call( document.querySelectorAll( '.bc-form:not(.bc-form--contact)' ) )
			.forEach( initForm );

		// A summary link in the error box should open the step holding that field.
		document.addEventListener( 'click', function ( event ) {
			var link = event.target.closest ? event.target.closest( '#bc-form-errors a' ) : null;

			if ( ! link ) {
				return;
			}

			var target = document.querySelector( link.getAttribute( 'href' ) );

			if ( ! target ) {
				return;
			}

			var step = target.closest( '.bc-step' );

			if ( step && ! step.classList.contains( 'is-active' ) ) {
				event.preventDefault();

				var form = step.closest( '.bc-form' );
				var steps = Array.prototype.slice.call( form.querySelectorAll( '.bc-step' ) );
				var index = steps.indexOf( step );
				var nextBtn = form.querySelector( '.bc-form__next' );

				// Re-run the stepper by clicking through; simpler than exposing the
				// internal showStep and keeps one source of truth for step changes.
				steps.forEach( function ( s, i ) {
					s.classList.toggle( 'is-active', i === index );
				} );

				form.querySelectorAll( '.bc-progress__item' ).forEach( function ( item, i ) {
					item.classList.toggle( 'is-active', i === index );
					item.classList.toggle( 'is-done', i < index );
				} );

				if ( nextBtn ) {
					nextBtn.hidden = index === steps.length - 1;
				}

				var focusable = target.querySelector( 'input, select, textarea' );

				if ( focusable ) {
					focusable.focus();
				}
			}
		} );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
}() );
