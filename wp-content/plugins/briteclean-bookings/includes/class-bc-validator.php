<?php
/**
 * Server-side validation and sanitisation.
 *
 * Every rule here runs on the server regardless of what the browser did. The
 * client-side checks in booking-form.js exist only to give faster feedback — they are
 * treated as a convenience, never as a guarantee, because anything sent from a browser
 * can be forged.
 *
 * @package BritecleanBookings
 */

defined( 'ABSPATH' ) || exit;

/**
 * Validates a raw booking submission.
 */
class BC_Validator {

	/**
	 * Validation errors, keyed by field.
	 *
	 * @var array
	 */
	private $errors = array();

	/**
	 * Cleaned values, keyed by field.
	 *
	 * @var array
	 */
	private $clean = array();

	/**
	 * Validate and sanitise a raw $_POST array.
	 *
	 * @param array $raw Raw, unslashed input.
	 * @return bool True when there are no errors.
	 */
	public function validate( array $raw ) {
		$this->errors = array();
		$this->clean  = array();

		foreach ( BC_Schema::fields() as $key => $field ) {
			$value = $raw[ $key ] ?? null;

			switch ( $field['type'] ) {
				case 'checkboxes':
					$this->validate_checkboxes( $key, $field, $value );
					break;

				case 'select':
				case 'radios':
					$this->validate_choice( $key, $field, $value );
					break;

				case 'date':
					$this->validate_date( $key, $field, $value );
					break;

				case 'email':
					$this->validate_email( $key, $field, $value );
					break;

				default:
					$this->validate_text( $key, $field, $value );
					break;
			}
		}

		$this->validate_phone_shape();

		return empty( $this->errors );
	}

	/**
	 * Multi-select values, filtered against the allowed choices.
	 *
	 * @param string $key   Field key.
	 * @param array  $field Field definition.
	 * @param mixed  $value Raw value.
	 */
	private function validate_checkboxes( $key, $field, $value ) {
		$choices  = BC_Schema::choices( $field );
		$selected = is_array( $value ) ? $value : array();
		$valid    = array();

		foreach ( $selected as $item ) {
			$item = sanitize_text_field( wp_unslash( (string) $item ) );

			// Allow-list: anything not an offered service is dropped silently.
			if ( array_key_exists( $item, $choices ) ) {
				$valid[] = $item;
			}
		}

		$valid = array_values( array_unique( $valid ) );

		if ( ! empty( $field['required'] ) && ! $valid ) {
			$this->errors[ $key ] = __( 'Please choose at least one service.', 'briteclean-bookings' );
		}

		// Guards against a scripted submission ticking every box to pad the request.
		if ( count( $valid ) > 12 ) {
			$valid = array_slice( $valid, 0, 12 );
		}

		$this->clean[ $key ] = $valid;
	}

	/**
	 * Single-choice values, checked against the allowed set.
	 *
	 * @param string $key   Field key.
	 * @param array  $field Field definition.
	 * @param mixed  $value Raw value.
	 */
	private function validate_choice( $key, $field, $value ) {
		$choices = BC_Schema::choices( $field );
		$value   = is_scalar( $value ) ? (string) $value : '';
		$value   = trim( $value );

		if ( '' === $value ) {
			if ( ! empty( $field['required'] ) ) {
				$this->errors[ $key ] = sprintf(
					/* translators: %s: field label. */
					__( 'Please choose a %s.', 'briteclean-bookings' ),
					strtolower( $field['label'] )
				);
			}

			$this->clean[ $key ] = '';

			return;
		}

		if ( ! array_key_exists( $value, $choices ) ) {
			$this->errors[ $key ] = sprintf(
				/* translators: %s: field label. */
				__( 'That is not a valid option for %s.', 'briteclean-bookings' ),
				strtolower( $field['label'] )
			);

			$this->clean[ $key ] = '';

			return;
		}

		$this->clean[ $key ] = $value;
	}

	/**
	 * Date values: must parse, must be in Y-m-d form, and cannot be in the past.
	 *
	 * @param string $key   Field key.
	 * @param array  $field Field definition.
	 * @param mixed  $value Raw value.
	 */
	private function validate_date( $key, $field, $value ) {
		$value = is_scalar( $value ) ? trim( (string) $value ) : '';

		if ( '' === $value ) {
			if ( ! empty( $field['required'] ) ) {
				$this->errors[ $key ] = __( 'Please choose a date.', 'briteclean-bookings' );
			}

			$this->clean[ $key ] = '';

			return;
		}

		$date = DateTime::createFromFormat( 'Y-m-d', $value );

		// createFromFormat is lenient — "2026-13-45" parses and rolls over. Comparing
		// the formatted result back to the input rejects those.
		if ( ! $date || $date->format( 'Y-m-d' ) !== $value ) {
			$this->errors[ $key ] = __( 'That date does not look right. Please pick one from the calendar.', 'briteclean-bookings' );
			$this->clean[ $key ]  = '';

			return;
		}

		$today = new DateTime( current_time( 'Y-m-d' ) );

		if ( $date < $today ) {
			$this->errors[ $key ] = __( 'Please choose a date that has not already passed.', 'briteclean-bookings' );
			$this->clean[ $key ]  = '';

			return;
		}

		// A request two years out is far more likely to be a typo than a real booking.
		$limit = ( new DateTime( current_time( 'Y-m-d' ) ) )->modify( '+2 years' );

		if ( $date > $limit ) {
			$this->errors[ $key ] = __( 'That date is too far ahead. Please choose one within the next two years.', 'briteclean-bookings' );
			$this->clean[ $key ]  = '';

			return;
		}

		$this->clean[ $key ] = $date->format( 'Y-m-d' );
	}

	/**
	 * Email addresses.
	 *
	 * @param string $key   Field key.
	 * @param array  $field Field definition.
	 * @param mixed  $value Raw value.
	 */
	private function validate_email( $key, $field, $value ) {
		$value = is_scalar( $value ) ? trim( (string) $value ) : '';

		if ( '' === $value ) {
			if ( ! empty( $field['required'] ) ) {
				$this->errors[ $key ] = __( 'Please enter your email address.', 'briteclean-bookings' );
			}

			$this->clean[ $key ] = '';

			return;
		}

		$clean = sanitize_email( $value );

		if ( ! $clean || ! is_email( $clean ) ) {
			$this->errors[ $key ] = __( 'That email address does not look valid.', 'briteclean-bookings' );
			$this->clean[ $key ]  = '';

			return;
		}

		$this->clean[ $key ] = $clean;
	}

	/**
	 * Free-text fields.
	 *
	 * @param string $key   Field key.
	 * @param array  $field Field definition.
	 * @param mixed  $value Raw value.
	 */
	private function validate_text( $key, $field, $value ) {
		$value = is_scalar( $value ) ? (string) $value : '';

		$sanitizer = $field['sanitize'] ?? 'sanitize_text_field';
		$clean     = is_callable( $sanitizer ) ? call_user_func( $sanitizer, $value ) : sanitize_text_field( $value );
		$clean     = trim( $clean );

		if ( '' === $clean ) {
			if ( ! empty( $field['required'] ) ) {
				$this->errors[ $key ] = sprintf(
					/* translators: %s: field label. */
					__( 'Please enter your %s.', 'briteclean-bookings' ),
					strtolower( $field['label'] )
				);
			}

			$this->clean[ $key ] = '';

			return;
		}

		if ( ! empty( $field['maxlength'] ) && mb_strlen( $clean ) > (int) $field['maxlength'] ) {
			$clean = mb_substr( $clean, 0, (int) $field['maxlength'] );
		}

		// A name of one character is almost always a mis-tap or a bot.
		if ( 'full_name' === $key && mb_strlen( $clean ) < 2 ) {
			$this->errors[ $key ] = __( 'Please enter your full name.', 'briteclean-bookings' );
		}

		if ( 'address' === $key && mb_strlen( $clean ) < 8 ) {
			$this->errors[ $key ] = __( 'Please enter the full service address, including ZIP code.', 'briteclean-bookings' );
		}

		$this->clean[ $key ] = $clean;
	}

	/**
	 * Phone numbers: digit count rather than format, so international and
	 * locally-formatted numbers both pass.
	 */
	private function validate_phone_shape() {
		if ( isset( $this->errors['phone'] ) || empty( $this->clean['phone'] ) ) {
			return;
		}

		$digits = preg_replace( '/\D+/', '', $this->clean['phone'] );

		if ( strlen( $digits ) < 7 || strlen( $digits ) > 15 ) {
			$this->errors['phone'] = __( 'Please enter a phone number we can actually reach you on.', 'briteclean-bookings' );
		}
	}

	/**
	 * Collected errors.
	 *
	 * @return array
	 */
	public function errors() {
		return $this->errors;
	}

	/**
	 * Cleaned values.
	 *
	 * @return array
	 */
	public function clean() {
		return $this->clean;
	}
}
