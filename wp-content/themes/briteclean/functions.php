<?php
/**
 * Briteclean theme bootstrap.
 *
 * @package Briteclean
 */

defined( 'ABSPATH' ) || exit;

define( 'BRITECLEAN_VERSION', '1.0.0' );
define( 'BRITECLEAN_DIR', get_template_directory() );
define( 'BRITECLEAN_URI', get_template_directory_uri() );

require_once BRITECLEAN_DIR . '/inc/defaults.php';
require_once BRITECLEAN_DIR . '/inc/helpers.php';
require_once BRITECLEAN_DIR . '/inc/customizer.php';
require_once BRITECLEAN_DIR . '/inc/cpt.php';
require_once BRITECLEAN_DIR . '/inc/acf-fields.php';
require_once BRITECLEAN_DIR . '/inc/schema.php';
require_once BRITECLEAN_DIR . '/inc/woocommerce.php';
require_once BRITECLEAN_DIR . '/inc/seeder.php';

/**
 * Theme supports and registrations.
 */
function briteclean_setup() {
	load_theme_textdomain( 'briteclean', BRITECLEAN_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'custom-logo', array(
		'height'      => 80,
		'width'       => 260,
		'flex-height' => true,
		'flex-width'  => true,
	) );
	add_theme_support( 'html5', array(
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
		'style',
		'script',
	) );

	// Sized for the layouts they appear in, so the browser never downscales a 2000px
	// hero into a 400px card.
	add_image_size( 'briteclean-hero', 1600, 1000, true );
	add_image_size( 'briteclean-card', 720, 540, true );
	add_image_size( 'briteclean-thumb', 360, 270, true );

	register_nav_menus( array(
		'primary' => __( 'Primary Menu', 'briteclean' ),
		'footer'  => __( 'Footer Menu', 'briteclean' ),
	) );
}
add_action( 'after_setup_theme', 'briteclean_setup' );

/**
 * Content width, used by WordPress for oEmbed sizing.
 */
function briteclean_content_width() {
	$GLOBALS['content_width'] = 760;
}
add_action( 'after_setup_theme', 'briteclean_content_width', 0 );

/**
 * Front-end styles and scripts.
 *
 * One stylesheet, one small script, both versioned by filemtime so LocalWP and the
 * browser never serve a stale copy while editing.
 */
function briteclean_assets() {
	$css_path = BRITECLEAN_DIR . '/assets/css/main.css';
	$js_path  = BRITECLEAN_DIR . '/assets/js/main.js';

	wp_enqueue_style(
		'briteclean-main',
		BRITECLEAN_URI . '/assets/css/main.css',
		array(),
		file_exists( $css_path ) ? filemtime( $css_path ) : BRITECLEAN_VERSION
	);

	wp_enqueue_script(
		'briteclean-main',
		BRITECLEAN_URI . '/assets/js/main.js',
		array(),
		file_exists( $js_path ) ? filemtime( $js_path ) : BRITECLEAN_VERSION,
		true
	);

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'briteclean_assets' );

/**
 * Emit the brand palette as CSS custom properties.
 *
 * Kept inline rather than in main.css so Customizer colour changes apply without a
 * build step or a cache bust.
 */
function briteclean_inline_brand_vars() {
	$red   = briteclean_opt( 'color_primary', '#C8102E' );
	$gold  = briteclean_opt( 'color_accent', '#F5B800' );
	$green = briteclean_opt( 'color_support', '#1E6F50' );

	$css = sprintf(
		':root{--bc-red:%s;--bc-gold:%s;--bc-green:%s;}',
		sanitize_hex_color( $red ) ? $red : '#C8102E',
		sanitize_hex_color( $gold ) ? $gold : '#F5B800',
		sanitize_hex_color( $green ) ? $green : '#1E6F50'
	);

	wp_add_inline_style( 'briteclean-main', $css );
}
add_action( 'wp_enqueue_scripts', 'briteclean_inline_brand_vars', 20 );

/**
 * Preload the hero image so the largest contentful paint is not waiting on CSS.
 */
function briteclean_preload_hero() {
	if ( ! is_front_page() ) {
		return;
	}

	$hero_id = (int) briteclean_field( 'hero_image', 0 );

	if ( ! $hero_id ) {
		return;
	}

	$src = wp_get_attachment_image_src( $hero_id, 'briteclean-hero' );

	if ( $src ) {
		printf(
			'<link rel="preload" as="image" href="%s" fetchpriority="high" />' . "\n",
			esc_url( $src[0] )
		);
	}
}
add_action( 'wp_head', 'briteclean_preload_hero', 2 );

/**
 * Default meta description, so pages have one before an SEO plugin is installed.
 *
 * Yoast/Rank Math will override this if either is added later — both output their own
 * description tag, so this bails when they are active to avoid a duplicate.
 */
function briteclean_meta_description() {
	if ( defined( 'WPSEO_VERSION' ) || class_exists( 'RankMath' ) ) {
		return;
	}

	$description = '';

	if ( is_front_page() ) {
		$hero        = briteclean_hero();
		$description = $hero['subheadline'] . ' ' . briteclean_opt( 'name' ) . ' serves ' .
			briteclean_opt( 'city' ) . ', ' . briteclean_opt( 'state' ) . ' and surrounding areas.';
	} elseif ( is_singular() ) {
		$post = get_queried_object();

		if ( $post instanceof WP_Post ) {
			$description = $post->post_excerpt
				? $post->post_excerpt
				: wp_trim_words( wp_strip_all_tags( strip_shortcodes( $post->post_content ) ), 28, '' );
		}
	} elseif ( is_archive() ) {
		$description = wp_strip_all_tags( get_the_archive_description() );
	}

	$description = trim( wp_strip_all_tags( $description ) );

	if ( ! $description ) {
		return;
	}

	printf(
		'<meta name="description" content="%s" />' . "\n",
		esc_attr( wp_html_excerpt( $description, 158, '…' ) )
	);
}
add_action( 'wp_head', 'briteclean_meta_description', 3 );

/**
 * Open Graph tags for link previews when the site is shared.
 */
function briteclean_open_graph() {
	if ( defined( 'WPSEO_VERSION' ) || class_exists( 'RankMath' ) ) {
		return;
	}

	$title = is_front_page() ? get_bloginfo( 'name' ) : wp_get_document_title();

	printf( '<meta property="og:type" content="website" />' . "\n" );
	printf( '<meta property="og:title" content="%s" />' . "\n", esc_attr( $title ) );
	printf( '<meta property="og:url" content="%s" />' . "\n", esc_url( briteclean_current_url() ) );
	printf( '<meta property="og:site_name" content="%s" />' . "\n", esc_attr( briteclean_opt( 'name' ) ) );

	$image_id = is_singular() && has_post_thumbnail() ? get_post_thumbnail_id() : (int) briteclean_field( 'hero_image', 0, get_option( 'page_on_front' ) );

	if ( $image_id ) {
		$src = wp_get_attachment_image_src( $image_id, 'briteclean-hero' );
		if ( $src ) {
			printf( '<meta property="og:image" content="%s" />' . "\n", esc_url( $src[0] ) );
		}
	}
}
add_action( 'wp_head', 'briteclean_open_graph', 4 );

/**
 * Current request URL, used for canonical and og:url.
 *
 * @return string
 */
function briteclean_current_url() {
	if ( is_singular() ) {
		return get_permalink();
	}

	if ( is_front_page() ) {
		return home_url( '/' );
	}

	return home_url( add_query_arg( array() ) );
}

/**
 * Body classes that templates and CSS key off.
 *
 * @param array $classes Existing classes.
 * @return array
 */
function briteclean_body_classes( $classes ) {
	if ( ! is_active_sidebar( 'sidebar-1' ) ) {
		$classes[] = 'bc-no-sidebar';
	}

	if ( is_front_page() ) {
		$classes[] = 'bc-home';
	}

	return $classes;
}
add_filter( 'body_class', 'briteclean_body_classes' );

/**
 * Lazy-load and async-decode images in post content below the fold.
 *
 * WordPress already adds loading="lazy"; this adds decoding="async", which it does not
 * do for content images on older cores.
 *
 * @param string $content Post content.
 * @return string
 */
function briteclean_image_decoding( $content ) {
	if ( is_admin() || ! $content ) {
		return $content;
	}

	return str_replace( '<img ', '<img decoding="async" ', $content );
}
add_filter( 'the_content', 'briteclean_image_decoding' );

/**
 * A branded placeholder for any image slot the client has not filled yet.
 *
 * Renders an inline SVG rather than shipping placeholder JPEGs: no HTTP request, no
 * files to delete later, and it is visually obvious that a real photo belongs there.
 *
 * @param string $label Short caption describing the intended photo.
 * @param string $ratio CSS aspect-ratio value.
 * @return string
 */
function briteclean_placeholder( $label, $ratio = '4 / 3' ) {
	return sprintf(
		'<div class="bc-placeholder" style="aspect-ratio:%s" role="img" aria-label="%s">
			<span class="bc-placeholder__icon">%s</span>
			<span class="bc-placeholder__label">%s</span>
		</div>',
		esc_attr( $ratio ),
		esc_attr( $label ),
		briteclean_icon( 'sparkle', array( 'size' => 28 ) ),
		esc_html( $label )
	);
}

/**
 * Render a featured image, or a placeholder when none is set.
 *
 * @param int    $image_id Attachment ID. 0 renders the placeholder.
 * @param string $size     Registered image size.
 * @param string $label    Placeholder caption.
 * @param array  $attr     Extra img attributes.
 * @return string
 */
function briteclean_image_or_placeholder( $image_id, $size, $label, $attr = array() ) {
	if ( ! $image_id ) {
		return briteclean_placeholder( $label );
	}

	$defaults = array(
		'loading'  => 'lazy',
		'decoding' => 'async',
	);

	// Alt text comes from the attachment's own alt field; fall back to the label so an
	// image is never announced as unlabelled.
	$alt = get_post_meta( $image_id, '_wp_attachment_image_alt', true );

	if ( ! $alt ) {
		$defaults['alt'] = $label;
	}

	return wp_get_attachment_image( $image_id, $size, false, array_merge( $defaults, $attr ) );
}

/**
 * Excerpt ellipsis.
 *
 * @return string
 */
function briteclean_excerpt_more() {
	return '…';
}
add_filter( 'excerpt_more', 'briteclean_excerpt_more' );

/**
 * Footer widget area.
 */
function briteclean_widgets_init() {
	register_sidebar( array(
		'name'          => __( 'Footer Widgets', 'briteclean' ),
		'id'            => 'footer-1',
		'description'   => __( 'Optional extra column in the footer.', 'briteclean' ),
		'before_widget' => '<div id="%1$s" class="bc-widget %2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<h3 class="bc-footer__heading">',
		'after_title'   => '</h3>',
	) );
}
add_action( 'widgets_init', 'briteclean_widgets_init' );
