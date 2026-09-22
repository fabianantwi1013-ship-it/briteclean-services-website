<?php
/**
 * Customizer settings for global, site-wide content.
 *
 * ACF's free tier has no Options Page, so anything that appears on every page —
 * phone numbers, address, hours, socials, brand colours — lives here instead. The
 * Customizer is native, free, and gives the client live preview while they edit.
 * Page-specific copy (hero, about) uses ACF field groups on the page itself.
 *
 * @package Briteclean
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register panels, sections and settings.
 *
 * @param WP_Customize_Manager $wp_customize Customizer instance.
 */
function briteclean_customize_register( $wp_customize ) {
	$business = briteclean_business();

	$wp_customize->add_panel(
		'briteclean_panel',
		array(
			'title'       => __( 'Briteclean Settings', 'briteclean' ),
			'description' => __( 'Business details used across the whole site — footer, contact page, schema markup and the booking notifications.', 'briteclean' ),
			'priority'    => 20,
		)
	);

	/*
	 * Contact details.
	 */
	$wp_customize->add_section(
		'briteclean_contact',
		array(
			'title' => __( 'Contact Details', 'briteclean' ),
			'panel' => 'briteclean_panel',
		)
	);

	$contact_fields = array(
		'phone_primary'   => array( __( 'Primary phone / WhatsApp', 'briteclean' ), $business['phone_primary'] ),
		'phone_secondary' => array( __( 'Secondary phone / WhatsApp', 'briteclean' ), $business['phone_secondary'] ),
		'email'           => array( __( 'Email address', 'briteclean' ), $business['email'] ),
		'street'          => array( __( 'Street address', 'briteclean' ), $business['street'] ),
		'city'            => array( __( 'City', 'briteclean' ), $business['city'] ),
		'state'           => array( __( 'State', 'briteclean' ), $business['state'] ),
		'postal'          => array( __( 'ZIP code', 'briteclean' ), $business['postal'] ),
	);

	foreach ( $contact_fields as $key => $config ) {
		list( $label, $default ) = $config;

		$wp_customize->add_setting(
			'briteclean_' . $key,
			array(
				'default'           => $default,
				'sanitize_callback' => 'email' === $key ? 'sanitize_email' : 'sanitize_text_field',
				'transport'         => 'refresh',
			)
		);

		$wp_customize->add_control(
			'briteclean_' . $key,
			array(
				'label'   => $label,
				'section' => 'briteclean_contact',
				'type'    => 'email' === $key ? 'email' : 'text',
			)
		);
	}

	$wp_customize->add_setting(
		'briteclean_booking_notify_email',
		array(
			'default'           => '',
			'sanitize_callback' => 'briteclean_sanitize_email_list',
		)
	);

	$wp_customize->add_control(
		'briteclean_booking_notify_email',
		array(
			'label'       => __( 'Booking notification recipients', 'briteclean' ),
			'description' => __( 'Comma-separate multiple addresses. Leave blank to use the site admin email.', 'briteclean' ),
			'section'     => 'briteclean_contact',
			'type'        => 'text',
		)
	);

	$wp_customize->add_setting(
		'briteclean_whatsapp_message',
		array(
			'default'           => "Hi, I'd like to book a cleaning service",
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	$wp_customize->add_control(
		'briteclean_whatsapp_message',
		array(
			'label'       => __( 'WhatsApp pre-filled message', 'briteclean' ),
			'description' => __( 'Text that appears already typed when someone taps the WhatsApp button.', 'briteclean' ),
			'section'     => 'briteclean_contact',
			'type'        => 'text',
		)
	);

	$wp_customize->add_setting(
		'briteclean_map_query',
		array(
			'default'           => '9540 Woodland Hills Drive, West Chester, OH 45011',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	$wp_customize->add_control(
		'briteclean_map_query',
		array(
			'label'       => __( 'Google Map search query', 'briteclean' ),
			'description' => __( 'What the embedded map centres on. An address or place name.', 'briteclean' ),
			'section'     => 'briteclean_contact',
			'type'        => 'text',
		)
	);

	/*
	 * Opening hours.
	 */
	$wp_customize->add_section(
		'briteclean_hours',
		array(
			'title' => __( 'Opening Hours', 'briteclean' ),
			'panel' => 'briteclean_panel',
		)
	);

	$index = 1;

	foreach ( $business['hours'] as $days => $time ) {
		$wp_customize->add_setting(
			'briteclean_hours_' . $index . '_days',
			array(
				'default'           => $days,
				'sanitize_callback' => 'sanitize_text_field',
			)
		);

		$wp_customize->add_control(
			'briteclean_hours_' . $index . '_days',
			array(
				/* translators: %d: row number. */
				'label'   => sprintf( __( 'Row %d — days', 'briteclean' ), $index ),
				'section' => 'briteclean_hours',
				'type'    => 'text',
			)
		);

		$wp_customize->add_setting(
			'briteclean_hours_' . $index . '_time',
			array(
				'default'           => $time,
				'sanitize_callback' => 'sanitize_text_field',
			)
		);

		$wp_customize->add_control(
			'briteclean_hours_' . $index . '_time',
			array(
				/* translators: %d: row number. */
				'label'   => sprintf( __( 'Row %d — hours', 'briteclean' ), $index ),
				'section' => 'briteclean_hours',
				'type'    => 'text',
			)
		);

		$index++;
	}

	/*
	 * Why Choose Us.
	 */
	$wp_customize->add_section(
		'briteclean_value_props',
		array(
			'title'       => __( 'Why Choose Us', 'briteclean' ),
			'description' => __( 'The five value propositions shown on the homepage.', 'briteclean' ),
			'panel'       => 'briteclean_panel',
		)
	);

	foreach ( briteclean_value_props_defaults() as $i => $prop ) {
		$number = $i + 1;

		$wp_customize->add_setting(
			'briteclean_value_prop_' . $number . '_title',
			array(
				'default'           => $prop['title'],
				'sanitize_callback' => 'sanitize_text_field',
			)
		);

		$wp_customize->add_control(
			'briteclean_value_prop_' . $number . '_title',
			array(
				/* translators: %d: item number. */
				'label'   => sprintf( __( 'Item %d — title', 'briteclean' ), $number ),
				'section' => 'briteclean_value_props',
				'type'    => 'text',
			)
		);

		$wp_customize->add_setting(
			'briteclean_value_prop_' . $number . '_text',
			array(
				'default'           => $prop['text'],
				'sanitize_callback' => 'sanitize_text_field',
			)
		);

		$wp_customize->add_control(
			'briteclean_value_prop_' . $number . '_text',
			array(
				/* translators: %d: item number. */
				'label'   => sprintf( __( 'Item %d — description', 'briteclean' ), $number ),
				'section' => 'briteclean_value_props',
				'type'    => 'textarea',
			)
		);
	}

	/*
	 * Trust badges.
	 */
	$wp_customize->add_section(
		'briteclean_trust',
		array(
			'title' => __( 'Trust Badges', 'briteclean' ),
			'panel' => 'briteclean_panel',
		)
	);

	foreach ( briteclean_trust_badges_defaults() as $i => $badge ) {
		$number = $i + 1;

		$wp_customize->add_setting(
			'briteclean_trust_badge_' . $number,
			array(
				'default'           => $badge['title'],
				'sanitize_callback' => 'sanitize_text_field',
			)
		);

		$wp_customize->add_control(
			'briteclean_trust_badge_' . $number,
			array(
				/* translators: %d: badge number. */
				'label'   => sprintf( __( 'Badge %d', 'briteclean' ), $number ),
				'section' => 'briteclean_trust',
				'type'    => 'text',
			)
		);
	}

	/*
	 * Service area.
	 */
	$wp_customize->add_section(
		'briteclean_area',
		array(
			'title' => __( 'Service Area', 'briteclean' ),
			'panel' => 'briteclean_panel',
		)
	);

	$wp_customize->add_setting(
		'briteclean_service_area',
		array(
			'default'           => $business['service_area'],
			'sanitize_callback' => 'wp_kses_post',
		)
	);

	$wp_customize->add_control(
		'briteclean_service_area',
		array(
			'label'   => __( 'Areas served', 'briteclean' ),
			'section' => 'briteclean_area',
			'type'    => 'textarea',
		)
	);

	/*
	 * Taglines.
	 */
	$wp_customize->add_section(
		'briteclean_identity_extra',
		array(
			'title' => __( 'Taglines', 'briteclean' ),
			'panel' => 'briteclean_panel',
		)
	);

	foreach ( array(
		'tagline'           => array( __( 'Primary tagline', 'briteclean' ), $business['tagline'] ),
		'tagline_secondary' => array( __( 'Secondary tagline', 'briteclean' ), $business['tagline_secondary'] ),
	) as $key => $config ) {
		list( $label, $default ) = $config;

		$wp_customize->add_setting(
			'briteclean_' . $key,
			array(
				'default'           => $default,
				'sanitize_callback' => 'sanitize_text_field',
			)
		);

		$wp_customize->add_control(
			'briteclean_' . $key,
			array(
				'label'   => $label,
				'section' => 'briteclean_identity_extra',
				'type'    => 'text',
			)
		);
	}

	/*
	 * Social links.
	 */
	$wp_customize->add_section(
		'briteclean_social',
		array(
			'title'       => __( 'Social Links', 'briteclean' ),
			'description' => __( 'Leave blank to hide a link.', 'briteclean' ),
			'panel'       => 'briteclean_panel',
		)
	);

	foreach ( array(
		'facebook'  => __( 'Facebook URL', 'briteclean' ),
		'instagram' => __( 'Instagram URL', 'briteclean' ),
		'google'    => __( 'Google Business Profile URL', 'briteclean' ),
		'yelp'      => __( 'Yelp URL', 'briteclean' ),
	) as $key => $label ) {
		$wp_customize->add_setting(
			'briteclean_social_' . $key,
			array(
				'default'           => '',
				'sanitize_callback' => 'esc_url_raw',
			)
		);

		$wp_customize->add_control(
			'briteclean_social_' . $key,
			array(
				'label'   => $label,
				'section' => 'briteclean_social',
				'type'    => 'url',
			)
		);
	}

	/*
	 * Brand colours.
	 */
	$wp_customize->add_section(
		'briteclean_colors',
		array(
			'title'       => __( 'Brand Colours', 'briteclean' ),
			'description' => __( 'Changing these affects the whole site. The defaults are checked for text contrast — if you change them, re-check readability.', 'briteclean' ),
			'panel'       => 'briteclean_panel',
		)
	);

	foreach ( array(
		'color_primary' => array( __( 'Primary (red)', 'briteclean' ), '#C8102E' ),
		'color_accent'  => array( __( 'Accent (gold)', 'briteclean' ), '#F5B800' ),
		'color_support' => array( __( 'Supporting (green)', 'briteclean' ), '#1E6F50' ),
	) as $key => $config ) {
		list( $label, $default ) = $config;

		$wp_customize->add_setting(
			'briteclean_' . $key,
			array(
				'default'           => $default,
				'sanitize_callback' => 'sanitize_hex_color',
			)
		);

		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'briteclean_' . $key,
				array(
					'label'   => $label,
					'section' => 'briteclean_colors',
				)
			)
		);
	}

	/*
	 * Floating WhatsApp button.
	 */
	$wp_customize->add_section(
		'briteclean_float',
		array(
			'title' => __( 'Floating WhatsApp Button', 'briteclean' ),
			'panel' => 'briteclean_panel',
		)
	);

	$wp_customize->add_setting(
		'briteclean_float_enabled',
		array(
			'default'           => true,
			'sanitize_callback' => 'briteclean_sanitize_checkbox',
		)
	);

	$wp_customize->add_control(
		'briteclean_float_enabled',
		array(
			'label'   => __( 'Show the floating WhatsApp button', 'briteclean' ),
			'section' => 'briteclean_float',
			'type'    => 'checkbox',
		)
	);

	$wp_customize->add_setting(
		'briteclean_float_label',
		array(
			'default'           => __( 'Book via WhatsApp', 'briteclean' ),
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	$wp_customize->add_control(
		'briteclean_float_label',
		array(
			'label'   => __( 'Button label', 'briteclean' ),
			'section' => 'briteclean_float',
			'type'    => 'text',
		)
	);
}
add_action( 'customize_register', 'briteclean_customize_register' );

/**
 * Sanitize a checkbox setting.
 *
 * @param mixed $value Raw value.
 * @return bool
 */
function briteclean_sanitize_checkbox( $value ) {
	return (bool) $value;
}

/**
 * Sanitize a comma-separated list of email addresses.
 *
 * Invalid entries are dropped rather than rejecting the whole field, so one typo does
 * not silently discard the other recipients.
 *
 * @param string $value Raw value.
 * @return string
 */
function briteclean_sanitize_email_list( $value ) {
	$emails = array_filter( array_map( 'trim', explode( ',', (string) $value ) ) );
	$valid  = array();

	foreach ( $emails as $email ) {
		$clean = sanitize_email( $email );

		if ( $clean && is_email( $clean ) ) {
			$valid[] = $clean;
		}
	}

	return implode( ', ', $valid );
}

/**
 * Opening hours as a days => time map, read from the Customizer.
 *
 * @return array
 */
function briteclean_hours() {
	$defaults = briteclean_business()['hours'];
	$hours    = array();
	$index    = 1;

	foreach ( $defaults as $days => $time ) {
		$set_days = get_theme_mod( 'briteclean_hours_' . $index . '_days', $days );
		$set_time = get_theme_mod( 'briteclean_hours_' . $index . '_time', $time );

		if ( $set_days ) {
			$hours[ $set_days ] = $set_time;
		}

		$index++;
	}

	return $hours;
}

/**
 * Configured social links, keyed by network, excluding empties.
 *
 * @return array
 */
function briteclean_social_links() {
	$links = array();

	foreach ( array( 'facebook', 'instagram', 'google', 'yelp' ) as $network ) {
		$url = get_theme_mod( 'briteclean_social_' . $network, '' );

		if ( $url ) {
			$links[ $network ] = $url;
		}
	}

	return $links;
}
