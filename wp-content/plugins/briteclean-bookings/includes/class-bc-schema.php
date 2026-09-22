<?php
/**
 * The booking field schema.
 *
 * This is the single source of truth for every booking field. The form markup, the
 * server-side validation, the admin meta box, the CSV export and both email templates
 * all read from here — so adding a field or changing a label happens in exactly one
 * place and cannot drift out of sync between them.
 *
 * @package BritecleanBookings
 */

defined( 'ABSPATH' ) || exit;

/**
 * Field and step definitions.
 */
class BC_Schema {

	/**
	 * Meta key prefix for stored booking values.
	 */
	const META_PREFIX = '_bc_';

	/**
	 * The five form steps, in order.
	 *
	 * @return array
	 */
	public static function steps() {
		return array(
			array(
				'id'    => 'services',
				'title' => __( 'Select Services', 'briteclean-bookings' ),
				'intro' => __( 'Choose everything you need — you can pick more than one.', 'briteclean-bookings' ),
			),
			array(
				'id'    => 'property',
				'title' => __( 'Property Details', 'briteclean-bookings' ),
				'intro' => __( 'This helps us size the job and quote accurately.', 'briteclean-bookings' ),
			),
			array(
				'id'    => 'schedule',
				'title' => __( 'Preferred Date & Time', 'briteclean-bookings' ),
				'intro' => __( 'Tell us when suits you. We will confirm the exact time with you directly.', 'briteclean-bookings' ),
			),
			array(
				'id'    => 'contact',
				'title' => __( 'Your Details', 'briteclean-bookings' ),
				'intro' => __( 'So we can reach you to confirm.', 'briteclean-bookings' ),
			),
			array(
				'id'    => 'review',
				'title' => __( 'Review & Submit', 'briteclean-bookings' ),
				'intro' => __( 'Check everything over, then send your request.', 'briteclean-bookings' ),
			),
		);
	}

	/**
	 * Every stored field.
	 *
	 * Keys:
	 *   step      — which step the field belongs to
	 *   label     — human label, reused in the admin, emails and CSV
	 *   type      — text | email | tel | textarea | date | select | checkboxes | radios
	 *   required  — enforced server-side
	 *   choices   — for select/checkboxes/radios; a callable or an array
	 *   sanitize  — sanitiser callback name
	 *   autocomplete — browser autofill hint
	 *   maxlength — enforced server-side as well as in the markup
	 *
	 * @return array
	 */
	public static function fields() {
		return array(

			/* ---- Step 1: services ---- */
			'services'         => array(
				'step'     => 'services',
				'label'    => __( 'Services requested', 'briteclean-bookings' ),
				'type'     => 'checkboxes',
				'required' => true,
				'choices'  => array( __CLASS__, 'service_choices' ),
				'sanitize' => 'sanitize_text_field',
			),

			/* ---- Step 2: property ---- */
			'property_type'    => array(
				'step'     => 'property',
				'label'    => __( 'Property type', 'briteclean-bookings' ),
				'type'     => 'radios',
				'required' => true,
				'choices'  => array( __CLASS__, 'property_type_choices' ),
				'sanitize' => 'sanitize_key',
			),
			'bedrooms'         => array(
				'step'     => 'property',
				'label'    => __( 'Bedrooms / rooms', 'briteclean-bookings' ),
				'type'     => 'select',
				'required' => false,
				'choices'  => array( __CLASS__, 'room_choices' ),
				'sanitize' => 'sanitize_text_field',
				'help'     => __( 'For offices and commercial spaces, count the rooms or work areas.', 'briteclean-bookings' ),
			),
			'bathrooms'        => array(
				'step'     => 'property',
				'label'    => __( 'Bathrooms', 'briteclean-bookings' ),
				'type'     => 'select',
				'required' => false,
				'choices'  => array( __CLASS__, 'bathroom_choices' ),
				'sanitize' => 'sanitize_text_field',
			),
			'property_size'    => array(
				'step'     => 'property',
				'label'    => __( 'Approximate size', 'briteclean-bookings' ),
				'type'     => 'select',
				'required' => false,
				'choices'  => array( __CLASS__, 'size_choices' ),
				'sanitize' => 'sanitize_text_field',
			),
			'frequency'        => array(
				'step'     => 'property',
				'label'    => __( 'How often', 'briteclean-bookings' ),
				'type'     => 'select',
				'required' => false,
				'choices'  => array( __CLASS__, 'frequency_choices' ),
				'sanitize' => 'sanitize_text_field',
			),
			'instructions'     => array(
				'step'      => 'property',
				'label'     => __( 'Special instructions', 'briteclean-bookings' ),
				'type'      => 'textarea',
				'required'  => false,
				'sanitize'  => 'sanitize_textarea_field',
				'maxlength' => 2000,
				'help'      => __( 'Pets, allergies, access or parking notes, anything needing particular care.', 'briteclean-bookings' ),
			),

			/* ---- Step 3: schedule ---- */
			'preferred_date'   => array(
				'step'     => 'schedule',
				'label'    => __( 'Preferred date', 'briteclean-bookings' ),
				'type'     => 'date',
				'required' => true,
				'sanitize' => 'sanitize_text_field',
				'help'     => __( 'A preference, not a confirmed slot — we will call to agree the exact time.', 'briteclean-bookings' ),
			),
			'time_preference'  => array(
				'step'     => 'schedule',
				'label'    => __( 'Time of day', 'briteclean-bookings' ),
				'type'     => 'radios',
				'required' => true,
				'choices'  => array( __CLASS__, 'time_choices' ),
				'sanitize' => 'sanitize_key',
			),
			'alternate_date'   => array(
				'step'     => 'schedule',
				'label'    => __( 'Backup date', 'briteclean-bookings' ),
				'type'     => 'date',
				'required' => false,
				'sanitize' => 'sanitize_text_field',
				'help'     => __( 'Optional. Gives us a second option if your first choice is full.', 'briteclean-bookings' ),
			),

			/* ---- Step 4: contact ---- */
			'full_name'        => array(
				'step'         => 'contact',
				'label'        => __( 'Full name', 'briteclean-bookings' ),
				'type'         => 'text',
				'required'     => true,
				'sanitize'     => 'sanitize_text_field',
				'autocomplete' => 'name',
				'maxlength'    => 120,
			),
			'phone'            => array(
				'step'         => 'contact',
				'label'        => __( 'Phone number', 'briteclean-bookings' ),
				'type'         => 'tel',
				'required'     => true,
				'sanitize'     => 'sanitize_text_field',
				'autocomplete' => 'tel',
				'maxlength'    => 40,
				'help'         => __( 'The fastest way for us to confirm your booking.', 'briteclean-bookings' ),
			),
			'email'            => array(
				'step'         => 'contact',
				'label'        => __( 'Email address', 'briteclean-bookings' ),
				'type'         => 'email',
				'required'     => true,
				'sanitize'     => 'sanitize_email',
				'autocomplete' => 'email',
				'maxlength'    => 190,
			),
			'address'          => array(
				'step'         => 'contact',
				'label'        => __( 'Service address', 'briteclean-bookings' ),
				'type'         => 'textarea',
				'required'     => true,
				'sanitize'     => 'sanitize_textarea_field',
				'autocomplete' => 'street-address',
				'maxlength'    => 400,
				'help'         => __( 'Where the cleaning takes place, including ZIP code.', 'briteclean-bookings' ),
			),
			'contact_method'   => array(
				'step'     => 'contact',
				'label'    => __( 'Best way to reach you', 'briteclean-bookings' ),
				'type'     => 'radios',
				'required' => false,
				'choices'  => array( __CLASS__, 'contact_method_choices' ),
				'sanitize' => 'sanitize_key',
			),
		);
	}

	/**
	 * Fields belonging to a given step.
	 *
	 * @param string $step Step id.
	 * @return array
	 */
	public static function fields_for_step( $step ) {
		return array_filter(
			self::fields(),
			static function ( $field ) use ( $step ) {
				return $field['step'] === $step;
			}
		);
	}

	/**
	 * Resolve a field's choices, whether defined inline or as a callable.
	 *
	 * @param array $field Field definition.
	 * @return array
	 */
	public static function choices( $field ) {
		if ( empty( $field['choices'] ) ) {
			return array();
		}

		if ( is_callable( $field['choices'] ) ) {
			return call_user_func( $field['choices'] );
		}

		return (array) $field['choices'];
	}

	/**
	 * Service options, keyed by slug.
	 *
	 * Reads the live service list from the theme when it is available, so adding a
	 * ninth service as a WooCommerce product automatically adds it to the form.
	 *
	 * @return array
	 */
	public static function service_choices() {
		$choices = array();

		if ( function_exists( 'briteclean_services' ) ) {
			foreach ( briteclean_services() as $service ) {
				$choices[ $service['slug'] ] = $service['name'];
			}
		}

		if ( $choices ) {
			return $choices;
		}

		// Standalone fallback, so the plugin works even with a different theme active.
		return array(
			'residential-cleaning' => __( 'Residential Cleaning', 'briteclean-bookings' ),
			'office-cleaning'      => __( 'Office Cleaning', 'briteclean-bookings' ),
			'deep-cleaning'        => __( 'Deep Cleaning', 'briteclean-bookings' ),
			'commercial-cleaning'  => __( 'Commercial Cleaning', 'briteclean-bookings' ),
			'window-cleaning'      => __( 'Window Cleaning', 'briteclean-bookings' ),
			'move-in-cleaning'     => __( 'Move-In Cleaning', 'briteclean-bookings' ),
			'general-cleaning'     => __( 'General Cleaning', 'briteclean-bookings' ),
			'emergency-cleaning'   => __( 'Emergency Cleaning', 'briteclean-bookings' ),
		);
	}

	/**
	 * Icon for a service slug, for the form's service cards.
	 *
	 * @param string $slug Service slug.
	 * @return string
	 */
	public static function service_icon( $slug ) {
		if ( function_exists( 'briteclean_services' ) ) {
			foreach ( briteclean_services() as $service ) {
				if ( $service['slug'] === $slug ) {
					return $service['icon'];
				}
			}
		}

		return 'sparkle';
	}

	/**
	 * Property types.
	 *
	 * @return array
	 */
	public static function property_type_choices() {
		if ( function_exists( 'briteclean_property_types' ) ) {
			return briteclean_property_types();
		}

		return array(
			'residential' => __( 'Residential', 'briteclean-bookings' ),
			'office'      => __( 'Office', 'briteclean-bookings' ),
			'commercial'  => __( 'Commercial', 'briteclean-bookings' ),
		);
	}

	/**
	 * Time-of-day preferences.
	 *
	 * @return array
	 */
	public static function time_choices() {
		if ( function_exists( 'briteclean_time_preferences' ) ) {
			return briteclean_time_preferences();
		}

		return array(
			'morning'   => __( 'Morning (8am – 12pm)', 'briteclean-bookings' ),
			'afternoon' => __( 'Afternoon (12pm – 4pm)', 'briteclean-bookings' ),
			'evening'   => __( 'Evening (4pm – 8pm)', 'briteclean-bookings' ),
		);
	}

	/**
	 * Room-count options.
	 *
	 * @return array
	 */
	public static function room_choices() {
		return array(
			''      => __( 'Please choose…', 'briteclean-bookings' ),
			'studio' => __( 'Studio / open plan', 'briteclean-bookings' ),
			'1'     => __( '1 room', 'briteclean-bookings' ),
			'2'     => __( '2 rooms', 'briteclean-bookings' ),
			'3'     => __( '3 rooms', 'briteclean-bookings' ),
			'4'     => __( '4 rooms', 'briteclean-bookings' ),
			'5'     => __( '5 rooms', 'briteclean-bookings' ),
			'6+'    => __( '6 or more', 'briteclean-bookings' ),
		);
	}

	/**
	 * Bathroom-count options.
	 *
	 * @return array
	 */
	public static function bathroom_choices() {
		return array(
			''   => __( 'Please choose…', 'briteclean-bookings' ),
			'1'  => __( '1 bathroom', 'briteclean-bookings' ),
			'2'  => __( '2 bathrooms', 'briteclean-bookings' ),
			'3'  => __( '3 bathrooms', 'briteclean-bookings' ),
			'4+' => __( '4 or more', 'briteclean-bookings' ),
		);
	}

	/**
	 * Approximate floor-area options.
	 *
	 * @return array
	 */
	public static function size_choices() {
		return array(
			''            => __( 'Please choose…', 'briteclean-bookings' ),
			'under-1000'  => __( 'Under 1,000 sq ft', 'briteclean-bookings' ),
			'1000-2000'   => __( '1,000 – 2,000 sq ft', 'briteclean-bookings' ),
			'2000-3000'   => __( '2,000 – 3,000 sq ft', 'briteclean-bookings' ),
			'3000-5000'   => __( '3,000 – 5,000 sq ft', 'briteclean-bookings' ),
			'over-5000'   => __( 'Over 5,000 sq ft', 'briteclean-bookings' ),
			'not-sure'    => __( 'Not sure', 'briteclean-bookings' ),
		);
	}

	/**
	 * Cleaning frequency options.
	 *
	 * @return array
	 */
	public static function frequency_choices() {
		return array(
			''           => __( 'Please choose…', 'briteclean-bookings' ),
			'one-off'    => __( 'One-off visit', 'briteclean-bookings' ),
			'weekly'     => __( 'Weekly', 'briteclean-bookings' ),
			'fortnightly' => __( 'Every two weeks', 'briteclean-bookings' ),
			'monthly'    => __( 'Monthly', 'briteclean-bookings' ),
			'not-sure'   => __( 'Not sure yet', 'briteclean-bookings' ),
		);
	}

	/**
	 * Preferred contact method.
	 *
	 * @return array
	 */
	public static function contact_method_choices() {
		return array(
			'phone'    => __( 'Phone call', 'briteclean-bookings' ),
			'whatsapp' => __( 'WhatsApp', 'briteclean-bookings' ),
			'email'    => __( 'Email', 'briteclean-bookings' ),
		);
	}

	/**
	 * Booking statuses, in workflow order.
	 *
	 * @return array
	 */
	public static function statuses() {
		return array(
			'new'       => __( 'New', 'briteclean-bookings' ),
			'contacted' => __( 'Contacted', 'briteclean-bookings' ),
			'confirmed' => __( 'Confirmed', 'briteclean-bookings' ),
			'completed' => __( 'Completed', 'briteclean-bookings' ),
			'cancelled' => __( 'Cancelled', 'briteclean-bookings' ),
		);
	}

	/**
	 * Meta key for a field.
	 *
	 * @param string $key Field key.
	 * @return string
	 */
	public static function meta_key( $key ) {
		return self::META_PREFIX . $key;
	}

	/**
	 * Read a stored value and render it as human-readable text.
	 *
	 * Turns stored slugs back into their labels, so "move-in-cleaning" displays as
	 * "Move-In Cleaning" in the admin, the emails and the CSV alike.
	 *
	 * @param int    $post_id Booking post ID.
	 * @param string $key     Field key.
	 * @return string
	 */
	public static function display_value( $post_id, $key ) {
		$fields = self::fields();

		if ( ! isset( $fields[ $key ] ) ) {
			return '';
		}

		$field = $fields[ $key ];
		$raw   = get_post_meta( $post_id, self::meta_key( $key ), true );

		if ( '' === $raw || null === $raw || array() === $raw ) {
			return '';
		}

		$choices = self::choices( $field );

		if ( 'checkboxes' === $field['type'] ) {
			$values = is_array( $raw ) ? $raw : array( $raw );
			$labels = array();

			foreach ( $values as $value ) {
				$labels[] = $choices[ $value ] ?? $value;
			}

			return implode( ', ', $labels );
		}

		if ( in_array( $field['type'], array( 'select', 'radios' ), true ) ) {
			return (string) ( $choices[ $raw ] ?? $raw );
		}

		if ( 'date' === $field['type'] ) {
			$timestamp = strtotime( $raw );

			return $timestamp ? date_i18n( 'l, j F Y', $timestamp ) : (string) $raw;
		}

		return (string) $raw;
	}
}
