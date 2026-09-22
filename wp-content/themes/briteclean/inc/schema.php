<?php
/**
 * LocalBusiness structured data.
 *
 * Emitted as JSON-LD in the head on every page. Google treats this as the canonical
 * description of the business for local search and the knowledge panel, so the values
 * here must match the NAP (name, address, phone) shown in the footer exactly —
 * inconsistency between them actively hurts local ranking.
 *
 * @package Briteclean
 */

defined( 'ABSPATH' ) || exit;

/**
 * Output the LocalBusiness JSON-LD graph.
 */
function briteclean_json_ld() {
	$name  = briteclean_opt( 'name' );
	$hours = briteclean_hours();

	$schema = array(
		'@context'    => 'https://schema.org',
		'@type'       => 'HomeAndConstructionBusiness',
		'@id'         => home_url( '/#business' ),
		'name'        => $name,
		'description' => briteclean_hero()['subheadline'],
		'slogan'      => briteclean_opt( 'tagline' ),
		'url'         => home_url( '/' ),
		'telephone'   => briteclean_opt( 'phone_primary' ),
		'email'       => briteclean_opt( 'email' ),
		'address'     => array(
			'@type'           => 'PostalAddress',
			'streetAddress'   => briteclean_opt( 'street' ),
			'addressLocality' => briteclean_opt( 'city' ),
			'addressRegion'   => briteclean_opt( 'state_short', 'OH' ),
			'postalCode'      => briteclean_opt( 'postal' ),
			'addressCountry'  => briteclean_opt( 'country', 'US' ),
		),
		'areaServed'  => array(
			array(
				'@type' => 'City',
				'name'  => briteclean_opt( 'city' ) . ', ' . briteclean_opt( 'state' ),
			),
		),
		'priceRange'  => '$$',
	);

	$secondary = briteclean_opt( 'phone_secondary' );

	if ( $secondary ) {
		$schema['contactPoint'] = array(
			array(
				'@type'       => 'ContactPoint',
				'contactType' => 'customer service',
				'telephone'   => $secondary,
			),
		);
	}

	$logo_id = get_theme_mod( 'custom_logo' );

	if ( $logo_id ) {
		$logo_src = wp_get_attachment_image_src( $logo_id, 'full' );

		if ( $logo_src ) {
			$schema['logo']  = $logo_src[0];
			$schema['image'] = $logo_src[0];
		}
	}

	$socials = array_values( briteclean_social_links() );

	if ( $socials ) {
		$schema['sameAs'] = $socials;
	}

	$specs = briteclean_schema_opening_hours( $hours );

	if ( $specs ) {
		$schema['openingHoursSpecification'] = $specs;
	}

	// The eight services, as an offer catalog.
	$items = array();

	foreach ( briteclean_services() as $service ) {
		$items[] = array(
			'@type'       => 'Offer',
			'itemOffered' => array(
				'@type'       => 'Service',
				'name'        => $service['name'],
				'description' => wp_strip_all_tags( $service['short'] ),
			),
		);
	}

	if ( $items ) {
		$schema['hasOfferCatalog'] = array(
			'@type'           => 'OfferCatalog',
			'name'            => __( 'Cleaning Services', 'briteclean' ),
			'itemListElement' => $items,
		);
	}

	$graph = array( $schema );

	// A WebSite node lets Google attribute sitelinks search to the right entity.
	$graph[] = array(
		'@context'  => 'https://schema.org',
		'@type'     => 'WebSite',
		'@id'       => home_url( '/#website' ),
		'url'       => home_url( '/' ),
		'name'      => $name,
		'publisher' => array( '@id' => home_url( '/#business' ) ),
	);

	foreach ( $graph as $node ) {
		printf(
			'<script type="application/ld+json">%s</script>' . "\n",
			wp_json_encode( $node, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
		);
	}
}
add_action( 'wp_head', 'briteclean_json_ld', 5 );

/**
 * Convert the human-readable hours into schema.org OpeningHoursSpecification.
 *
 * Rows it cannot parse are skipped rather than guessed at — bad structured data is
 * worse than none.
 *
 * @param array $hours Days => time-range map.
 * @return array
 */
function briteclean_schema_opening_hours( $hours ) {
	$day_map = array(
		'monday'    => 'Monday',
		'tuesday'   => 'Tuesday',
		'wednesday' => 'Wednesday',
		'thursday'  => 'Thursday',
		'friday'    => 'Friday',
		'saturday'  => 'Saturday',
		'sunday'    => 'Sunday',
	);

	$specs = array();

	foreach ( $hours as $days_label => $time_label ) {
		// Match "8:00 AM – 6:00 PM" with any dash style between the two times.
		// The /u modifier is required: en- and em-dashes are multi-byte in UTF-8, and
		// without it the character class matches single bytes and never fires.
		if ( ! preg_match( '/(\d{1,2}:\d{2}\s*(?:AM|PM))\s*[–—-]\s*(\d{1,2}:\d{2}\s*(?:AM|PM))/iu', $time_label, $times ) ) {
			continue;
		}

		$opens  = gmdate( 'H:i', strtotime( $times[1] ) );
		$closes = gmdate( 'H:i', strtotime( $times[2] ) );

		$matched_days = array();
		$label_lower  = strtolower( $days_label );

		// "Monday – Friday" expands to the full run; anything else lists the days named.
		if ( preg_match( '/(\w+day)\s*[–—-]\s*(\w+day)/iu', $label_lower, $range ) ) {
			$order = array_keys( $day_map );
			$start = array_search( strtolower( $range[1] ), $order, true );
			$end   = array_search( strtolower( $range[2] ), $order, true );

			if ( false !== $start && false !== $end && $start <= $end ) {
				for ( $i = $start; $i <= $end; $i++ ) {
					$matched_days[] = $day_map[ $order[ $i ] ];
				}
			}
		} else {
			foreach ( $day_map as $needle => $proper ) {
				if ( false !== strpos( $label_lower, $needle ) ) {
					$matched_days[] = $proper;
				}
			}
		}

		if ( ! $matched_days ) {
			continue;
		}

		$specs[] = array(
			'@type'     => 'OpeningHoursSpecification',
			'dayOfWeek' => $matched_days,
			'opens'     => $opens,
			'closes'    => $closes,
		);
	}

	return $specs;
}
