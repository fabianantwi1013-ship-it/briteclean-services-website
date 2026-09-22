<?php
/**
 * WooCommerce integration — catalog only, no checkout.
 *
 * Products are the editable source of truth for the eight services, and give the
 * client somewhere to attach per-service pricing later. Nothing is purchasable today:
 * this is a request-based booking business, and a half-configured cart that takes real
 * card details would be worse than no cart at all.
 *
 * Every add-to-cart path is closed here rather than just hidden in CSS, because hiding
 * a button does not stop a direct POST to the cart endpoint.
 *
 * @package Briteclean
 */

defined( 'ABSPATH' ) || exit;

/**
 * Declare theme support and take over the product templates.
 */
function briteclean_woocommerce_setup() {
	add_theme_support( 'woocommerce', array(
		'thumbnail_image_width' => 360,
		'single_image_width'    => 720,
	) );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
}
add_action( 'after_setup_theme', 'briteclean_woocommerce_setup' );

/**
 * Nothing on this site is purchasable.
 *
 * @return bool
 */
function briteclean_not_purchasable() {
	return false;
}
add_filter( 'woocommerce_is_purchasable', 'briteclean_not_purchasable', 100 );

/**
 * Hide prices entirely until the client decides to publish them.
 *
 * Products can still carry a price in the admin — it simply is not shown, so pricing
 * can be prepared privately and revealed by removing this one filter.
 *
 * @return string
 */
function briteclean_hide_price() {
	return '';
}
add_filter( 'woocommerce_get_price_html', 'briteclean_hide_price', 100 );

/**
 * Replace the add-to-cart button with a booking link on archives and single products.
 */
function briteclean_replace_add_to_cart() {
	remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );

	add_action( 'woocommerce_after_shop_loop_item', 'briteclean_book_service_button', 10 );
	add_action( 'woocommerce_single_product_summary', 'briteclean_book_service_button', 30 );
}
add_action( 'init', 'briteclean_replace_add_to_cart' );

/**
 * "Request this service" button, deep-linking into the booking form with the service
 * pre-selected.
 */
function briteclean_book_service_button() {
	global $product;

	if ( ! $product ) {
		return;
	}

	$url = add_query_arg(
		'service',
		rawurlencode( get_post_field( 'post_name', $product->get_id() ) ),
		briteclean_page_url( 'book-now' )
	);

	printf(
		'<a class="bc-btn bc-btn--primary bc-btn--sm" href="%s">%s %s</a>',
		esc_url( $url ),
		esc_html__( 'Request this service', 'briteclean' ),
		briteclean_icon( 'arrow', array( 'size' => 16 ) )
	);
}

/**
 * Remove the cart, checkout and account endpoints from the front end.
 *
 * Redirects rather than 404s, so an old bookmark or a search-engine hit lands on the
 * booking page instead of a dead end.
 */
function briteclean_block_shop_endpoints() {
	if ( is_admin() || ! function_exists( 'is_cart' ) ) {
		return;
	}

	if ( is_cart() || is_checkout() || is_account_page() ) {
		wp_safe_redirect( briteclean_page_url( 'book-now' ), 302 );
		exit;
	}
}
add_action( 'template_redirect', 'briteclean_block_shop_endpoints' );

/**
 * Drop WooCommerce's stylesheets and cart scripts on pages that do not need them.
 *
 * Woo enqueues ~80KB of CSS plus cart fragments AJAX on every page by default. With no
 * cart in play that is pure weight on a site whose visitors are mostly on phones.
 */
function briteclean_trim_woocommerce_assets() {
	wp_dequeue_style( 'woocommerce-general' );
	wp_dequeue_style( 'woocommerce-layout' );
	wp_dequeue_style( 'woocommerce-smallscreen' );
	wp_dequeue_style( 'wc-blocks-style' );
	wp_dequeue_style( 'brands-styles' );

	wp_dequeue_script( 'wc-cart-fragments' );
	wp_dequeue_script( 'woocommerce' );
	wp_dequeue_script( 'wc-add-to-cart' );
}
add_action( 'wp_enqueue_scripts', 'briteclean_trim_woocommerce_assets', 99 );

/**
 * Stop WooCommerce redirecting the admin to its onboarding wizard on every activation.
 *
 * @return bool
 */
function briteclean_skip_wc_setup_wizard() {
	return false;
}
add_filter( 'woocommerce_enable_setup_wizard', 'briteclean_skip_wc_setup_wizard' );

/**
 * Products per row in the shop archive.
 *
 * @return int
 */
function briteclean_wc_columns() {
	return 3;
}
add_filter( 'loop_shop_columns', 'briteclean_wc_columns' );

/**
 * Show all eight services on one archive page.
 *
 * @return int
 */
function briteclean_wc_per_page() {
	return 12;
}
add_filter( 'loop_shop_per_page', 'briteclean_wc_per_page' );

/**
 * Ensure the "Cleaning Services" product category exists.
 *
 * Called by the seeder, and again on theme activation, so the category the front end
 * queries against is always present.
 *
 * @return int|WP_Error Term ID, or an error.
 */
function briteclean_ensure_service_category() {
	if ( ! taxonomy_exists( 'product_cat' ) ) {
		return new WP_Error( 'no_woocommerce', __( 'WooCommerce is not active.', 'briteclean' ) );
	}

	$term = get_term_by( 'slug', 'cleaning-services', 'product_cat' );

	if ( $term ) {
		return (int) $term->term_id;
	}

	$created = wp_insert_term(
		__( 'Cleaning Services', 'briteclean' ),
		'product_cat',
		array( 'slug' => 'cleaning-services' )
	);

	if ( is_wp_error( $created ) ) {
		return $created;
	}

	return (int) $created['term_id'];
}
