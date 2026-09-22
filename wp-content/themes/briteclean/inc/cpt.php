<?php
/**
 * Theme-owned custom post types.
 *
 * Only testimonials live here. Services are WooCommerce products (see
 * inc/woocommerce.php) and booking requests belong to the briteclean-bookings plugin —
 * content that must survive a theme switch does not belong in a theme.
 *
 * @package Briteclean
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the testimonial post type.
 */
function briteclean_register_testimonials() {
	$labels = array(
		'name'               => __( 'Testimonials', 'briteclean' ),
		'singular_name'      => __( 'Testimonial', 'briteclean' ),
		'add_new'            => __( 'Add New', 'briteclean' ),
		'add_new_item'       => __( 'Add New Testimonial', 'briteclean' ),
		'edit_item'          => __( 'Edit Testimonial', 'briteclean' ),
		'new_item'           => __( 'New Testimonial', 'briteclean' ),
		'view_item'          => __( 'View Testimonial', 'briteclean' ),
		'search_items'       => __( 'Search Testimonials', 'briteclean' ),
		'not_found'          => __( 'No testimonials yet', 'briteclean' ),
		'not_found_in_trash' => __( 'No testimonials in trash', 'briteclean' ),
		'menu_name'          => __( 'Testimonials', 'briteclean' ),
	);

	register_post_type(
		'bc_testimonial',
		array(
			'labels'          => $labels,
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => true,
			'show_in_rest'    => true,
			'menu_icon'       => 'dashicons-format-quote',
			'menu_position'   => 26,
			'supports'        => array( 'title', 'editor', 'page-attributes' ),
			'has_archive'     => false,
			'rewrite'         => false,
			'capability_type' => 'post',
		)
	);
}
add_action( 'init', 'briteclean_register_testimonials' );

/**
 * Tell the client what goes in the title and body fields.
 *
 * @param array $texts Existing placeholder text.
 * @return array
 */
function briteclean_testimonial_title_placeholder( $texts ) {
	$screen = get_current_screen();

	if ( $screen && 'bc_testimonial' === $screen->post_type ) {
		$texts['enter_title_here'] = __( "Customer's name", 'briteclean' );
	}

	return $texts;
}
add_filter( 'post_type_labels_bc_testimonial', 'briteclean_testimonial_title_placeholder' );

/**
 * Add a rating column to the testimonials list table.
 *
 * @param array $columns Existing columns.
 * @return array
 */
function briteclean_testimonial_columns( $columns ) {
	$reordered = array();

	foreach ( $columns as $key => $label ) {
		$reordered[ $key ] = $label;

		if ( 'title' === $key ) {
			$reordered['bc_rating']   = __( 'Rating', 'briteclean' );
			$reordered['bc_location'] = __( 'Location', 'briteclean' );
		}
	}

	return $reordered;
}
add_filter( 'manage_bc_testimonial_posts_columns', 'briteclean_testimonial_columns' );

/**
 * Render the custom testimonial columns.
 *
 * @param string $column  Column key.
 * @param int    $post_id Post ID.
 */
function briteclean_testimonial_column_content( $column, $post_id ) {
	if ( 'bc_rating' === $column ) {
		$rating = (int) briteclean_field( 'testimonial_rating', 5, $post_id );
		echo esc_html( str_repeat( '★', max( 0, min( 5, $rating ) ) ) );
	}

	if ( 'bc_location' === $column ) {
		echo esc_html( briteclean_field( 'testimonial_location', '—', $post_id ) );
	}
}
add_action( 'manage_bc_testimonial_posts_custom_column', 'briteclean_testimonial_column_content', 10, 2 );
