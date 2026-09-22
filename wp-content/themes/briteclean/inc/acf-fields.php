<?php
/**
 * ACF field groups, registered in code.
 *
 * Defined with acf_add_local_field_group() rather than clicked together in wp-admin so
 * the field definitions live in version control and travel with the theme. The client
 * still edits the values normally; they just cannot accidentally delete a field.
 *
 * Everything here works on ACF's free tier — no Repeater, Options Page, Flexible
 * Content or Gallery. Global settings that would have wanted a repeater live in the
 * Customizer instead (see inc/customizer.php).
 *
 * @package Briteclean
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register all field groups.
 */
function briteclean_register_acf_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	$hero = briteclean_hero_defaults();

	/*
	 * Homepage hero. Attached to the page set as the static front page.
	 */
	acf_add_local_field_group(
		array(
			'key'                   => 'group_briteclean_hero',
			'title'                 => __( 'Homepage Hero', 'briteclean' ),
			'menu_order'            => 0,
			'position'              => 'acf_after_title',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'active'                => true,
			'description'           => __( 'The big red panel at the top of the homepage.', 'briteclean' ),
			'hide_on_screen'        => array(),
			'location'              => array(
				array(
					array(
						'param'    => 'page_type',
						'operator' => '==',
						'value'    => 'front_page',
					),
				),
			),
			'fields'                => array(
				array(
					'key'           => 'field_bc_hero_badge',
					'label'         => __( 'Badge text', 'briteclean' ),
					'name'          => 'hero_badge',
					'type'          => 'text',
					'default_value' => $hero['badge'],
					'instructions'  => __( 'The small gold pill above the headline.', 'briteclean' ),
					'maxlength'     => 60,
				),
				array(
					'key'           => 'field_bc_hero_headline',
					'label'         => __( 'Headline', 'briteclean' ),
					'name'          => 'hero_headline',
					'type'          => 'text',
					'default_value' => $hero['headline'],
					'required'      => 0,
					'maxlength'     => 90,
				),
				array(
					'key'           => 'field_bc_hero_subheadline',
					'label'         => __( 'Subheadline', 'briteclean' ),
					'name'          => 'hero_subheadline',
					'type'          => 'textarea',
					'default_value' => $hero['subheadline'],
					'rows'          => 2,
					'new_lines'     => '',
					'maxlength'     => 180,
				),
				array(
					'key'           => 'field_bc_hero_cta_primary',
					'label'         => __( 'Primary button label', 'briteclean' ),
					'name'          => 'hero_cta_primary',
					'type'          => 'text',
					'default_value' => $hero['cta_primary'],
					'instructions'  => __( 'Links to the Book Now page.', 'briteclean' ),
					'maxlength'     => 30,
					'wrapper'       => array( 'width' => '50' ),
				),
				array(
					'key'           => 'field_bc_hero_cta_secondary',
					'label'         => __( 'Secondary button label', 'briteclean' ),
					'name'          => 'hero_cta_secondary',
					'type'          => 'text',
					'default_value' => $hero['cta_secondary'],
					'instructions'  => __( 'Dials the primary phone number.', 'briteclean' ),
					'maxlength'     => 30,
					'wrapper'       => array( 'width' => '50' ),
				),
				array(
					'key'           => 'field_bc_hero_image',
					'label'         => __( 'Hero photo', 'briteclean' ),
					'name'          => 'hero_image',
					'type'          => 'image',
					'return_format' => 'id',
					'preview_size'  => 'medium',
					'library'       => 'all',
					'mime_types'    => 'jpg,jpeg,png,webp',
					'instructions'  => __( 'Landscape, at least 1600px wide. A branded placeholder shows if this is empty.', 'briteclean' ),
				),
			),
		)
	);

	/*
	 * Testimonials.
	 */
	acf_add_local_field_group(
		array(
			'key'                   => 'group_briteclean_testimonial',
			'title'                 => __( 'Review Details', 'briteclean' ),
			'menu_order'            => 0,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'active'                => true,
			'description'           => __( 'The review text itself goes in the main editor above.', 'briteclean' ),
			'location'              => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'bc_testimonial',
					),
				),
			),
			'fields'                => array(
				array(
					'key'          => 'field_bc_testimonial_location',
					'label'        => __( 'Location', 'briteclean' ),
					'name'         => 'testimonial_location',
					'type'         => 'text',
					'placeholder'  => 'West Chester, OH',
					'instructions' => __( 'Shown under the name. City and state is enough — do not publish a full address.', 'briteclean' ),
					'wrapper'      => array( 'width' => '50' ),
				),
				array(
					'key'           => 'field_bc_testimonial_rating',
					'label'         => __( 'Star rating', 'briteclean' ),
					'name'          => 'testimonial_rating',
					'type'          => 'select',
					'choices'       => array(
						5 => '★★★★★',
						4 => '★★★★',
						3 => '★★★',
						2 => '★★',
						1 => '★',
					),
					'default_value' => 5,
					'return_format' => 'value',
					'wrapper'       => array( 'width' => '50' ),
				),
			),
		)
	);

	/*
	 * Service icon, attached to WooCommerce products.
	 */
	$icon_choices = array(
		'home'      => __( 'House', 'briteclean' ),
		'briefcase' => __( 'Briefcase', 'briteclean' ),
		'sparkle'   => __( 'Sparkle', 'briteclean' ),
		'building'  => __( 'Building', 'briteclean' ),
		'window'    => __( 'Window', 'briteclean' ),
		'box'       => __( 'Moving box', 'briteclean' ),
		'broom'     => __( 'Broom', 'briteclean' ),
		'bolt'      => __( 'Lightning bolt', 'briteclean' ),
		'check'     => __( 'Checkmark', 'briteclean' ),
		'shield'    => __( 'Shield', 'briteclean' ),
		'leaf'      => __( 'Leaf', 'briteclean' ),
		'star'      => __( 'Star', 'briteclean' ),
	);

	acf_add_local_field_group(
		array(
			'key'                   => 'group_briteclean_service',
			'title'                 => __( 'Service Display', 'briteclean' ),
			'menu_order'            => 0,
			'position'              => 'side',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'active'                => true,
			'location'              => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'product',
					),
				),
			),
			'fields'                => array(
				array(
					'key'           => 'field_bc_service_icon',
					'label'         => __( 'Icon', 'briteclean' ),
					'name'          => 'service_icon',
					'type'          => 'select',
					'choices'       => $icon_choices,
					'default_value' => 'sparkle',
					'return_format' => 'value',
					'instructions'  => __( 'Shown in the services grid on the homepage.', 'briteclean' ),
				),
				array(
					'key'          => 'field_bc_service_featured',
					'label'        => __( 'Highlight this service', 'briteclean' ),
					'name'         => 'service_featured',
					'type'         => 'true_false',
					'ui'           => 1,
					'instructions' => __( 'Adds a gold outline to the card.', 'briteclean' ),
				),
			),
		)
	);

	/*
	 * Generic page intro, available on every page.
	 */
	acf_add_local_field_group(
		array(
			'key'                   => 'group_briteclean_page',
			'title'                 => __( 'Page Header', 'briteclean' ),
			'menu_order'            => 0,
			'position'              => 'acf_after_title',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'active'                => true,
			'location'              => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'page',
					),
				),
			),
			'fields'                => array(
				array(
					'key'          => 'field_bc_page_subtitle',
					'label'        => __( 'Subtitle', 'briteclean' ),
					'name'         => 'page_subtitle',
					'type'         => 'textarea',
					'rows'         => 2,
					'new_lines'    => '',
					'maxlength'    => 200,
					'instructions' => __( 'One line under the page title, in the red header band.', 'briteclean' ),
				),
			),
		)
	);
}
add_action( 'acf/init', 'briteclean_register_acf_fields' );

/**
 * Point ACF's JSON sync at the theme, so any field edits made in wp-admin are written
 * to disk instead of living only in the database.
 *
 * @param string $path Default save path.
 * @return string
 */
function briteclean_acf_json_save_point( $path ) {
	$dir = BRITECLEAN_DIR . '/acf-json';

	if ( ! is_dir( $dir ) ) {
		wp_mkdir_p( $dir );
	}

	return $dir;
}
add_filter( 'acf/settings/save_json', 'briteclean_acf_json_save_point' );

/**
 * Load ACF JSON from the theme as well as the default locations.
 *
 * @param array $paths Existing load paths.
 * @return array
 */
function briteclean_acf_json_load_point( $paths ) {
	$paths[] = BRITECLEAN_DIR . '/acf-json';

	return $paths;
}
add_filter( 'acf/settings/load_json', 'briteclean_acf_json_load_point' );

/**
 * Nudge the admin toward installing ACF, without nagging on every screen.
 */
function briteclean_acf_notice() {
	if ( function_exists( 'get_field' ) || ! current_user_can( 'install_plugins' ) ) {
		return;
	}

	$screen = get_current_screen();

	if ( ! $screen || ! in_array( $screen->id, array( 'dashboard', 'themes', 'plugins' ), true ) ) {
		return;
	}

	printf(
		'<div class="notice notice-info"><p><strong>%s</strong> %s <a href="%s">%s</a></p></div>',
		esc_html__( 'Briteclean:', 'briteclean' ),
		esc_html__( 'The site is running on its built-in default content. Install Advanced Custom Fields (free) to let the client edit the hero and service details.', 'briteclean' ),
		esc_url( admin_url( 'plugin-install.php?s=advanced+custom+fields&tab=search&type=term' ) ),
		esc_html__( 'Install ACF', 'briteclean' )
	);
}
add_action( 'admin_notices', 'briteclean_acf_notice' );
