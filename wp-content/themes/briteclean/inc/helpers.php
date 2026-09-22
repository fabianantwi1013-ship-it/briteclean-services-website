<?php
/**
 * Content accessors and the inline SVG icon set.
 *
 * Every template reads content through these functions rather than calling
 * get_field() or get_theme_mod() directly, so a missing plugin degrades to the
 * default content instead of a blank section.
 *
 * @package Briteclean
 */

defined( 'ABSPATH' ) || exit;

/**
 * Read a Customizer setting, falling back to the business defaults.
 *
 * @param string $key     Setting key, matching a briteclean_business() array key.
 * @param mixed  $default Explicit fallback. Defaults to the business value.
 * @return mixed
 */
function briteclean_opt( $key, $default = null ) {
	$business = briteclean_business();

	if ( null === $default && isset( $business[ $key ] ) ) {
		$default = $business[ $key ];
	}

	$value = get_theme_mod( 'briteclean_' . $key, $default );

	return ( '' === $value || null === $value ) ? $default : $value;
}

/**
 * Read an ACF field with a fallback, safe when ACF is not installed.
 *
 * @param string   $selector Field name.
 * @param mixed    $default  Value to use when ACF is absent or the field is empty.
 * @param int|null $post_id  Post to read from. Defaults to the current post.
 * @return mixed
 */
function briteclean_field( $selector, $default = '', $post_id = null ) {
	if ( ! function_exists( 'get_field' ) ) {
		return $default;
	}

	$value = get_field( $selector, $post_id );

	return ( '' === $value || null === $value || false === $value ) ? $default : $value;
}

/**
 * Hero content for the homepage, ACF-overridable per page.
 *
 * @param int|null $post_id Page to read overrides from.
 * @return array
 */
function briteclean_hero( $post_id = null ) {
	$defaults = briteclean_hero_defaults();
	$hero     = array();

	foreach ( $defaults as $key => $default ) {
		$hero[ $key ] = briteclean_field( 'hero_' . $key, $default, $post_id );
	}

	return $hero;
}

/**
 * The service list, sourced from WooCommerce products when available.
 *
 * Products in the "cleaning-services" category are the editable source of truth. When
 * WooCommerce is inactive — or no products exist yet — the hardcoded eight are used so
 * the site is never missing its core content.
 *
 * @return array List of service arrays: slug, name, icon, short, long, url, image_id.
 */
function briteclean_services() {
	// Cached per request. This is called from the schema markup, the services grid,
	// the booking form's option list and once per option by service_icon() — without
	// this, a single page load would run the query a dozen times over.
	static $cache = null;

	if ( null !== $cache ) {
		return $cache;
	}

	$defaults = briteclean_services_defaults();

	if ( ! class_exists( 'WooCommerce' ) ) {
		$cache = briteclean_prepare_default_services( $defaults );

		return $cache;
	}

	$query = new WP_Query(
		array(
			'post_type'              => 'product',
			'posts_per_page'         => 20,
			'post_status'            => 'publish',
			'orderby'                => 'menu_order',
			'order'                  => 'ASC',
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
			'tax_query'              => array(
				array(
					'taxonomy' => 'product_cat',
					'field'    => 'slug',
					'terms'    => 'cleaning-services',
				),
			),
		)
	);

	if ( ! $query->have_posts() ) {
		$cache = briteclean_prepare_default_services( $defaults );

		return $cache;
	}

	// Index defaults by slug so a product missing its icon still gets the right one.
	$by_slug = array();
	foreach ( $defaults as $service ) {
		$by_slug[ $service['slug'] ] = $service;
	}

	$services = array();

	foreach ( $query->posts as $product_post ) {
		$slug     = $product_post->post_name;
		$fallback = isset( $by_slug[ $slug ] ) ? $by_slug[ $slug ] : array();

		$services[] = array(
			'slug'     => $slug,
			'name'     => get_the_title( $product_post ),
			'icon'     => briteclean_field( 'service_icon', ( $fallback['icon'] ?? 'sparkle' ), $product_post->ID ),
			'short'    => $product_post->post_excerpt ? $product_post->post_excerpt : ( $fallback['short'] ?? '' ),
			'long'     => $product_post->post_content ? $product_post->post_content : ( $fallback['long'] ?? '' ),
			'url'      => get_permalink( $product_post ),
			'image_id' => get_post_thumbnail_id( $product_post ),
			'featured' => (bool) briteclean_field( 'service_featured', false, $product_post->ID ),
		);
	}

	wp_reset_postdata();

	$cache = $services;

	return $cache;
}

/**
 * Normalise the hardcoded services into the shape templates expect.
 *
 * @param array $defaults Raw default service definitions.
 * @return array
 */
function briteclean_prepare_default_services( $defaults ) {
	$services_page = get_page_by_path( 'services' );
	$base          = $services_page ? get_permalink( $services_page ) : home_url( '/services/' );

	foreach ( $defaults as &$service ) {
		$service['url']      = $base . '#' . $service['slug'];
		$service['image_id'] = 0;
		$service['featured'] = false;
	}
	unset( $service );

	return $defaults;
}

/**
 * Value propositions for the "Why Choose Us" section.
 *
 * @return array
 */
function briteclean_value_props() {
	$defaults = briteclean_value_props_defaults();
	$props    = array();

	foreach ( $defaults as $index => $default ) {
		$number = $index + 1;

		$props[] = array(
			'icon'  => $default['icon'],
			'title' => briteclean_opt( 'value_prop_' . $number . '_title', $default['title'] ),
			'text'  => briteclean_opt( 'value_prop_' . $number . '_text', $default['text'] ),
		);
	}

	return $props;
}

/**
 * Trust badges. Static by design — three fixed claims, edited in the Customizer.
 *
 * @return array
 */
function briteclean_trust_badges() {
	$defaults = briteclean_trust_badges_defaults();
	$badges   = array();

	foreach ( $defaults as $index => $default ) {
		$badges[] = array(
			'icon'  => $default['icon'],
			'title' => briteclean_opt( 'trust_badge_' . ( $index + 1 ), $default['title'] ),
		);
	}

	return $badges;
}

/**
 * Published testimonials, falling back to the seeded samples.
 *
 * @param int $limit Maximum to return.
 * @return array
 */
function briteclean_testimonials( $limit = 3 ) {
	$query = new WP_Query(
		array(
			'post_type'      => 'bc_testimonial',
			'posts_per_page' => $limit,
			'post_status'    => 'publish',
			'no_found_rows'  => true,
		)
	);

	if ( ! $query->have_posts() ) {
		return array_slice( briteclean_testimonials_defaults(), 0, $limit );
	}

	$testimonials = array();

	foreach ( $query->posts as $post ) {
		$testimonials[] = array(
			'author'   => get_the_title( $post ),
			'location' => briteclean_field( 'testimonial_location', '', $post->ID ),
			'rating'   => (int) briteclean_field( 'testimonial_rating', 5, $post->ID ),
			'quote'    => wp_strip_all_tags( $post->post_content ),
		);
	}

	wp_reset_postdata();

	return $testimonials;
}

/**
 * Strip a phone number down to a tel:/wa.me-safe digit string.
 *
 * @param string $phone Human-formatted phone number.
 * @return string Digits only, no leading plus.
 */
function briteclean_phone_digits( $phone ) {
	return preg_replace( '/\D+/', '', (string) $phone );
}

/**
 * Build the WhatsApp deep link for a given number.
 *
 * @param string $phone   Human-formatted number. Defaults to the primary line.
 * @param string $message Pre-filled message.
 * @return string
 */
function briteclean_whatsapp_url( $phone = '', $message = '' ) {
	$phone   = $phone ? $phone : briteclean_opt( 'phone_primary' );
	$message = $message ? $message : briteclean_opt( 'whatsapp_message', "Hi, I'd like to book a cleaning service" );

	return 'https://wa.me/' . briteclean_phone_digits( $phone ) . '?text=' . rawurlencode( $message );
}

/**
 * Full street address as a single line.
 *
 * @return string
 */
function briteclean_address_line() {
	return sprintf(
		'%s, %s, %s, %s',
		briteclean_opt( 'street' ),
		briteclean_opt( 'city' ),
		briteclean_opt( 'state' ),
		briteclean_opt( 'postal' )
	);
}

/**
 * Permalink for a page by slug, falling back to a path off the home URL.
 *
 * @param string $slug Page slug.
 * @return string
 */
function briteclean_page_url( $slug ) {
	$page = get_page_by_path( $slug );

	return $page ? get_permalink( $page ) : home_url( '/' . $slug . '/' );
}

/**
 * Inline SVG icon.
 *
 * Inline rather than an icon font or sprite sheet: no extra request, no FOUT, and
 * currentColor means one icon works on both the red and white panels.
 *
 * @param string $name  Icon key.
 * @param array  $args  Optional. 'size' in px, 'class' for extra classes.
 * @return string Escaped-safe SVG markup.
 */
function briteclean_icon( $name, $args = array() ) {
	$size  = isset( $args['size'] ) ? (int) $args['size'] : 24;
	$class = isset( $args['class'] ) ? $args['class'] : '';

	$paths = array(
		'home'      => '<path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/><path d="M9.5 21v-6h5v6"/>',
		'briefcase' => '<rect x="2.5" y="7" width="19" height="13" rx="2"/><path d="M8.5 7V5a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v2"/><path d="M2.5 12.5h19"/>',
		'sparkle'   => '<path d="M12 3l1.9 5.1L19 10l-5.1 1.9L12 17l-1.9-5.1L5 10l5.1-1.9z"/><path d="M18 15l.8 2.2L21 18l-2.2.8L18 21l-.8-2.2L15 18l2.2-.8z"/>',
		'building'  => '<rect x="4" y="3" width="16" height="18" rx="1.5"/><path d="M8.5 7h1.5M14 7h1.5M8.5 11h1.5M14 11h1.5M8.5 15h1.5M14 15h1.5"/><path d="M10 21v-3h4v3"/>',
		'window'    => '<rect x="3.5" y="3.5" width="17" height="17" rx="1.5"/><path d="M12 3.5v17M3.5 12h17"/>',
		'box'       => '<path d="M3.5 7.5 12 3l8.5 4.5v9L12 21l-8.5-4.5z"/><path d="M3.5 7.5 12 12l8.5-4.5M12 12v9"/>',
		'broom'     => '<path d="M14 3 9.5 7.5"/><path d="m8 6 4 4"/><path d="M11 9 5.5 14.5a3 3 0 0 0-.8 1.5L4 21l5-.7a3 3 0 0 0 1.5-.8L16 14z"/>',
		'bolt'      => '<path d="M13 2 4.5 13.5H11l-1 8.5 8.5-11.5H12z"/>',
		'check'     => '<path d="m4.5 12.5 5 5 10-11"/>',
		'calendar'  => '<rect x="3.5" y="5" width="17" height="16" rx="2"/><path d="M3.5 10h17M8 3v4M16 3v4"/>',
		'shield'    => '<path d="M12 3l7.5 3v6c0 4.3-3 8.2-7.5 9.5C7.5 20.2 4.5 16.3 4.5 12V6z"/><path d="m9 12 2 2 4-4"/>',
		'leaf'      => '<path d="M20 4c0 9-5.5 14-13 14a7 7 0 0 1 0-14c4 0 6-2 13 0z"/><path d="M4.5 19.5C8 16 12 13.5 17 11.5"/>',
		'heart'     => '<path d="M12 20.5C6.5 16.5 3 13.4 3 9.5A4.5 4.5 0 0 1 12 7a4.5 4.5 0 0 1 9 2.5c0 3.9-3.5 7-9 11z"/>',
		'star'      => '<path d="m12 3.5 2.6 5.5 5.9.8-4.3 4.1 1.1 5.9-5.3-2.9-5.3 2.9 1.1-5.9L3.5 9.8l5.9-.8z"/>',
		'phone'     => '<path d="M6.5 3.5h3l1.5 4-2 1.5a12 12 0 0 0 6 6l1.5-2 4 1.5v3a2 2 0 0 1-2.2 2A16.5 16.5 0 0 1 4.5 5.7a2 2 0 0 1 2-2.2z"/>',
		'mail'      => '<rect x="2.5" y="5" width="19" height="14" rx="2"/><path d="m3 6.5 9 6 9-6"/>',
		'pin'       => '<path d="M12 21s7-6 7-11a7 7 0 1 0-14 0c0 5 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/>',
		'clock'     => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5.5l3.5 2"/>',
		'whatsapp'  => '<path d="M12.04 2a9.9 9.9 0 0 0-8.5 14.9L2 22l5.3-1.4A9.9 9.9 0 1 0 12.04 2z"/><path d="M8.6 7.3c.2-.4.4-.4.7-.4h.5c.2 0 .4 0 .6.5l.8 1.9c.1.2 0 .4-.1.6l-.5.6c-.1.2-.2.3 0 .6a7.4 7.4 0 0 0 3.4 3c.3.1.5.1.6 0l.7-.8c.2-.2.4-.2.6-.1l1.8.9c.3.1.4.3.4.5a2 2 0 0 1-1.4 1.8c-.5.2-1.2.3-3.5-.7a11 11 0 0 1-4.8-4.6c-.9-1.6-.6-2.7-.3-3.3z"/>',
		'arrow'     => '<path d="M4 12h15"/><path d="m13 6 6 6-6 6"/>',
		'quote'     => '<path d="M9.5 6C6.5 7.5 5 10 5 13v5h6v-6H8c0-2 .6-3.3 2.5-4.3zM19.5 6C16.5 7.5 15 10 15 13v5h6v-6h-3c0-2 .6-3.3 2.5-4.3z"/>',
	);

	if ( ! isset( $paths[ $name ] ) ) {
		$name = 'check';
	}

	// Fill-style icons read better solid; the rest are stroked outlines.
	$solid = in_array( $name, array( 'bolt', 'star', 'heart', 'sparkle', 'whatsapp', 'quote' ), true );

	return sprintf(
		'<svg class="bc-icon %s" width="%d" height="%d" viewBox="0 0 24 24" fill="%s" stroke="%s" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">%s</svg>',
		esc_attr( $class ),
		$size,
		$size,
		$solid ? 'currentColor' : 'none',
		$solid ? 'none' : 'currentColor',
		$paths[ $name ]
	);
}
