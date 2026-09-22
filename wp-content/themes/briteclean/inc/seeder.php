<?php
/**
 * One-click demo content seeder.
 *
 * Creates the page tree, the eight services as WooCommerce products, placeholder
 * testimonials and the two menus. Idempotent: every step checks for an existing item
 * first and skips it, so running this twice never duplicates content and never
 * overwrites an edit the client has already made.
 *
 * @package Briteclean
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the Tools → Briteclean Setup screen.
 */
function briteclean_seeder_menu() {
	add_management_page(
		__( 'Briteclean Setup', 'briteclean' ),
		__( 'Briteclean Setup', 'briteclean' ),
		'manage_options',
		'briteclean-setup',
		'briteclean_seeder_page'
	);
}
add_action( 'admin_menu', 'briteclean_seeder_menu' );

/**
 * The pages this site is built around.
 *
 * @return array
 */
function briteclean_seed_pages() {
	return array(
		'home'     => array(
			'title'   => __( 'Home', 'briteclean' ),
			'content' => '',
			'front'   => true,
		),
		'services' => array(
			'title'   => __( 'Services', 'briteclean' ),
			'content' => '',
		),
		'about'    => array(
			'title'   => __( 'About', 'briteclean' ),
			'content' => '',
		),
		'book-now' => array(
			'title'   => __( 'Book Now', 'briteclean' ),
			'content' => '[briteclean_booking_form]',
		),
		'contact'  => array(
			'title'   => __( 'Contact', 'briteclean' ),
			'content' => '',
		),
		'faq'      => array(
			'title'   => __( 'FAQ', 'briteclean' ),
			'content' => '',
		),
	);
}

/**
 * Render the setup screen.
 */
function briteclean_seeder_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to do this.', 'briteclean' ) );
	}

	$log = array();

	if ( isset( $_POST['briteclean_seed'] ) ) {
		check_admin_referer( 'briteclean_seed_action', 'briteclean_seed_nonce' );
		$log = briteclean_run_seeder();
	}

	$acf_active = function_exists( 'get_field' );
	$wc_active  = class_exists( 'WooCommerce' );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Briteclean Setup', 'briteclean' ); ?></h1>

		<p><?php esc_html_e( 'Creates the pages, services, testimonials and menus this theme expects. Safe to run more than once — anything that already exists is left alone.', 'briteclean' ); ?></p>

		<h2><?php esc_html_e( 'Plugin status', 'briteclean' ); ?></h2>
		<ul>
			<li>
				<?php echo $acf_active ? '✅' : '⚠️'; ?>
				<strong><?php esc_html_e( 'Advanced Custom Fields', 'briteclean' ); ?></strong> —
				<?php
				echo $acf_active
					? esc_html__( 'active.', 'briteclean' )
					: esc_html__( 'not active. The site will run on built-in defaults, but the client cannot edit the hero copy.', 'briteclean' );
				?>
			</li>
			<li>
				<?php echo $wc_active ? '✅' : '⚠️'; ?>
				<strong><?php esc_html_e( 'WooCommerce', 'briteclean' ); ?></strong> —
				<?php
				echo $wc_active
					? esc_html__( 'active.', 'briteclean' )
					: esc_html__( 'not active. Services will render from defaults and will not be editable as products.', 'briteclean' );
				?>
			</li>
			<li>
				<?php echo defined( 'BRITECLEAN_BOOKINGS_VERSION' ) ? '✅' : '⚠️'; ?>
				<strong><?php esc_html_e( 'Briteclean Bookings', 'briteclean' ); ?></strong> —
				<?php
				echo defined( 'BRITECLEAN_BOOKINGS_VERSION' )
					? esc_html__( 'active.', 'briteclean' )
					: esc_html__( 'not active. The Book Now page will show its shortcode as plain text until you activate the plugin.', 'briteclean' );
				?>
			</li>
		</ul>

		<?php if ( $log ) : ?>
			<h2><?php esc_html_e( 'Results', 'briteclean' ); ?></h2>
			<div class="notice notice-success"><ul style="margin:1em 0 1em 1.5em;list-style:disc">
				<?php foreach ( $log as $line ) : ?>
					<li><?php echo esc_html( $line ); ?></li>
				<?php endforeach; ?>
			</ul></div>
			<p>
				<a class="button" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'View the site', 'briteclean' ); ?></a>
				<a class="button" href="<?php echo esc_url( admin_url( 'options-permalink.php' ) ); ?>"><?php esc_html_e( 'Flush permalinks', 'briteclean' ); ?></a>
			</p>
		<?php endif; ?>

		<form method="post">
			<?php wp_nonce_field( 'briteclean_seed_action', 'briteclean_seed_nonce' ); ?>
			<p>
				<button type="submit" name="briteclean_seed" value="1" class="button button-primary button-hero">
					<?php esc_html_e( 'Seed site content', 'briteclean' ); ?>
				</button>
			</p>
		</form>
	</div>
	<?php
}

/**
 * Run every seeding step and collect a human-readable log.
 *
 * @return array Log lines.
 */
function briteclean_run_seeder() {
	$log = array();

	$log = array_merge( $log, briteclean_seed_page_tree() );
	$log = array_merge( $log, briteclean_seed_services() );
	$log = array_merge( $log, briteclean_seed_testimonials() );
	// After pages and products exist — each photo attaches to one of them.
	$log = array_merge( $log, briteclean_seed_photos() );
	$log = array_merge( $log, briteclean_seed_logo() );
	$log = array_merge( $log, briteclean_seed_menus() );

	flush_rewrite_rules();
	$log[] = __( 'Flushed permalinks.', 'briteclean' );

	return $log;
}

/**
 * Create the pages and set the front page.
 *
 * @return array Log lines.
 */
function briteclean_seed_page_tree() {
	$log = array();

	foreach ( briteclean_seed_pages() as $slug => $config ) {
		$existing = get_page_by_path( $slug );

		if ( $existing ) {
			$log[] = sprintf(
				/* translators: %s: page title. */
				__( 'Page "%s" already exists — skipped.', 'briteclean' ),
				$config['title']
			);
			$page_id = $existing->ID;
		} else {
			$page_id = wp_insert_post(
				array(
					'post_title'   => $config['title'],
					'post_name'    => $slug,
					'post_content' => $config['content'],
					'post_status'  => 'publish',
					'post_type'    => 'page',
				)
			);

			if ( is_wp_error( $page_id ) || ! $page_id ) {
				$log[] = sprintf(
					/* translators: %s: page title. */
					__( 'Failed to create page "%s".', 'briteclean' ),
					$config['title']
				);
				continue;
			}

			$log[] = sprintf(
				/* translators: %s: page title. */
				__( 'Created page "%s".', 'briteclean' ),
				$config['title']
			);
		}

		if ( ! empty( $config['front'] ) ) {
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', $page_id );
			$log[] = __( 'Set Home as the static front page.', 'briteclean' );
		}
	}

	return $log;
}

/**
 * Create the eight services as WooCommerce products.
 *
 * @return array Log lines.
 */
function briteclean_seed_services() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return array( __( 'WooCommerce is not active — skipped creating service products.', 'briteclean' ) );
	}

	$term_id = briteclean_ensure_service_category();

	if ( is_wp_error( $term_id ) ) {
		return array( __( 'Could not create the Cleaning Services product category.', 'briteclean' ) );
	}

	$log      = array();
	$created  = 0;
	$skipped  = 0;
	$position = 0;

	foreach ( briteclean_services_defaults() as $service ) {
		$position++;

		$existing = get_page_by_path( $service['slug'], OBJECT, 'product' );

		if ( $existing ) {
			$skipped++;
			continue;
		}

		$product_id = wp_insert_post(
			array(
				'post_title'   => $service['name'],
				'post_name'    => $service['slug'],
				'post_content' => $service['long'],
				'post_excerpt' => $service['short'],
				'post_status'  => 'publish',
				'post_type'    => 'product',
				'menu_order'   => $position,
			)
		);

		if ( is_wp_error( $product_id ) || ! $product_id ) {
			continue;
		}

		wp_set_object_terms( $product_id, array( (int) $term_id ), 'product_cat' );
		wp_set_object_terms( $product_id, 'simple', 'product_type' );

		// Catalog-only: visible, never purchasable, no stock handling.
		update_post_meta( $product_id, '_visibility', 'visible' );
		update_post_meta( $product_id, '_stock_status', 'instock' );
		update_post_meta( $product_id, '_manage_stock', 'no' );
		update_post_meta( $product_id, '_virtual', 'yes' );
		update_post_meta( $product_id, '_sold_individually', 'yes' );

		// Write the icon in ACF's format when available, plain meta otherwise, so the
		// value survives ACF being installed later.
		if ( function_exists( 'update_field' ) ) {
			update_field( 'service_icon', $service['icon'], $product_id );
		} else {
			update_post_meta( $product_id, 'service_icon', $service['icon'] );
			update_post_meta( $product_id, '_service_icon', 'field_bc_service_icon' );
		}

		$created++;
	}

	if ( $created ) {
		/* translators: %d: number of products. */
		$log[] = sprintf( __( 'Created %d service products.', 'briteclean' ), $created );
	}

	if ( $skipped ) {
		/* translators: %d: number of products. */
		$log[] = sprintf( __( '%d service products already existed — skipped.', 'briteclean' ), $skipped );
	}

	return $log;
}

/**
 * Create the placeholder testimonials.
 *
 * @return array Log lines.
 */
function briteclean_seed_testimonials() {
	$existing = get_posts(
		array(
			'post_type'      => 'bc_testimonial',
			'posts_per_page' => 1,
			'post_status'    => 'any',
			'fields'         => 'ids',
		)
	);

	if ( $existing ) {
		return array( __( 'Testimonials already exist — skipped.', 'briteclean' ) );
	}

	$count = 0;

	foreach ( briteclean_testimonials_defaults() as $index => $testimonial ) {
		$post_id = wp_insert_post(
			array(
				'post_title'   => $testimonial['author'],
				'post_content' => $testimonial['quote'],
				'post_status'  => 'publish',
				'post_type'    => 'bc_testimonial',
				'menu_order'   => $index,
			)
		);

		if ( is_wp_error( $post_id ) || ! $post_id ) {
			continue;
		}

		if ( function_exists( 'update_field' ) ) {
			update_field( 'testimonial_location', $testimonial['location'], $post_id );
			update_field( 'testimonial_rating', $testimonial['rating'], $post_id );
		} else {
			update_post_meta( $post_id, 'testimonial_location', $testimonial['location'] );
			update_post_meta( $post_id, '_testimonial_location', 'field_bc_testimonial_location' );
			update_post_meta( $post_id, 'testimonial_rating', $testimonial['rating'] );
			update_post_meta( $post_id, '_testimonial_rating', 'field_bc_testimonial_rating' );
		}

		$count++;
	}

	/* translators: %d: number of testimonials. */
	return array( sprintf( __( 'Created %d placeholder testimonials — replace these with real reviews before launch.', 'briteclean' ), $count ) );
}

/**
 * Build the primary and footer menus.
 *
 * @return array Log lines.
 */
function briteclean_seed_menus() {
	$log = array();

	$menus = array(
		'primary' => array(
			'name'  => __( 'Primary Menu', 'briteclean' ),
			'items' => array( 'home', 'services', 'about', 'faq', 'contact', 'book-now' ),
		),
		'footer'  => array(
			'name'  => __( 'Footer Menu', 'briteclean' ),
			'items' => array( 'services', 'about', 'faq', 'contact' ),
		),
	);

	$locations = get_theme_mod( 'nav_menu_locations', array() );

	foreach ( $menus as $location => $config ) {
		$menu = wp_get_nav_menu_object( $config['name'] );

		if ( $menu ) {
			$log[] = sprintf(
				/* translators: %s: menu name. */
				__( 'Menu "%s" already exists — skipped.', 'briteclean' ),
				$config['name']
			);
			$locations[ $location ] = (int) $menu->term_id;
			continue;
		}

		$menu_id = wp_create_nav_menu( $config['name'] );

		if ( is_wp_error( $menu_id ) ) {
			continue;
		}

		foreach ( $config['items'] as $slug ) {
			$page = get_page_by_path( $slug );

			if ( ! $page ) {
				continue;
			}

			wp_update_nav_menu_item(
				$menu_id,
				0,
				array(
					'menu-item-title'     => $page->post_title,
					'menu-item-object'    => 'page',
					'menu-item-object-id' => $page->ID,
					'menu-item-type'      => 'post_type',
					'menu-item-status'    => 'publish',
				)
			);
		}

		$locations[ $location ] = (int) $menu_id;

		$log[] = sprintf(
			/* translators: %s: menu name. */
			__( 'Created menu "%s".', 'briteclean' ),
			$config['name']
		);
	}

	set_theme_mod( 'nav_menu_locations', $locations );

	return $log;
}

/**
 * Install the bundled logo as the site's custom logo.
 *
 * The logo is white and gold on red — it was drawn for the flyer's red field and is
 * invisible on a white background — so the shipped asset keeps a rounded red tile
 * behind it rather than being keyed to transparency.
 *
 * Skipped entirely if a logo has already been set, so re-running never overwrites the
 * client's own upload.
 *
 * @return array Log lines.
 */
function briteclean_seed_logo() {
	$log = array();

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	// Header logo and browser-tab icon are handled independently: having set one is
	// no reason to skip the other.
	$targets = array(
		'logo'      => array(
			'file' => 'logo.png',
			'alt'  => __( 'Briteclean Services LLC — Clean Spaces, Healthy Lives', 'briteclean' ),
			'set'  => (bool) get_theme_mod( 'custom_logo' ),
			'done' => __( 'Set the site logo.', 'briteclean' ),
			'skip' => __( 'A site logo is already set — left alone.', 'briteclean' ),
		),
		'site_icon' => array(
			'file' => 'site-icon.png',
			'alt'  => __( 'Briteclean Services LLC icon', 'briteclean' ),
			'set'  => (bool) get_option( 'site_icon' ),
			'done' => __( 'Set the browser tab icon.', 'briteclean' ),
			'skip' => __( 'A browser tab icon is already set — left alone.', 'briteclean' ),
		),
	);

	foreach ( $targets as $key => $target ) {
		if ( $target['set'] ) {
			$log[] = $target['skip'];
			continue;
		}

		$path = BRITECLEAN_DIR . '/assets/img/brand/' . $target['file'];

		if ( ! file_exists( $path ) ) {
			continue;
		}

		$attachment_id = briteclean_find_placeholder_attachment( $target['file'] );

		if ( ! $attachment_id ) {
			$attachment_id = briteclean_sideload_placeholder(
				$path,
				$target['file'],
				array( 'alt' => $target['alt'] )
			);
		}

		if ( is_wp_error( $attachment_id ) || ! $attachment_id ) {
			continue;
		}

		if ( 'logo' === $key ) {
			set_theme_mod( 'custom_logo', $attachment_id );
		} else {
			update_option( 'site_icon', $attachment_id );
		}

		$log[] = $target['done'];
	}

	return $log;
}

/**
 * Import the bundled placeholder photographs into the Media Library.
 *
 * The files ship in assets/img/placeholders/ with a credits.json manifest describing
 * each one's alt text and where it belongs. They are sideloaded (copied, not moved —
 * the originals stay in the theme) and attached to the hero field, the About page or
 * the matching service product.
 *
 * Idempotent: each attachment is tagged with the filename it came from, so a second
 * run finds it and reuses the existing attachment instead of creating duplicates.
 *
 * All images are Pexels-licensed: free for commercial use, no attribution required.
 * They are placeholders — replace them with the client's own photography.
 *
 * @return array Log lines.
 */
function briteclean_seed_photos() {
	$dir      = BRITECLEAN_DIR . '/assets/img/placeholders';
	$manifest = $dir . '/credits.json';

	if ( ! file_exists( $manifest ) ) {
		return array( __( 'No placeholder photos bundled — skipped.', 'briteclean' ) );
	}

	$data = json_decode( file_get_contents( $manifest ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local theme file.

	if ( ! is_array( $data ) || empty( $data['images'] ) ) {
		return array( __( 'Could not read the photo manifest — skipped.', 'briteclean' ) );
	}

	// media_handle_sideload() and friends are admin-only includes.
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$imported = 0;
	$reused   = 0;
	$attached = 0;
	$failed   = array();

	foreach ( $data['images'] as $filename => $meta ) {
		$path = $dir . '/' . $filename;

		if ( ! file_exists( $path ) ) {
			$failed[] = $filename;
			continue;
		}

		$attachment_id = briteclean_find_placeholder_attachment( $filename );

		if ( $attachment_id ) {
			$reused++;
		} else {
			$attachment_id = briteclean_sideload_placeholder( $path, $filename, $meta );

			if ( is_wp_error( $attachment_id ) ) {
				$failed[] = $filename;
				continue;
			}

			$imported++;
		}

		if ( briteclean_attach_placeholder( $attachment_id, $meta['target'] ?? '' ) ) {
			$attached++;
		}
	}

	$log = array();

	if ( $imported ) {
		/* translators: %d: number of images. */
		$log[] = sprintf( __( 'Imported %d placeholder photos into the Media Library.', 'briteclean' ), $imported );
	}

	if ( $reused ) {
		/* translators: %d: number of images. */
		$log[] = sprintf( __( '%d photos were already imported — reused.', 'briteclean' ), $reused );
	}

	if ( $attached ) {
		/* translators: %d: number of images. */
		$log[] = sprintf( __( 'Attached %d photos to the hero, About page and service products.', 'briteclean' ), $attached );
	}

	if ( $failed ) {
		/* translators: %s: comma-separated filenames. */
		$log[] = sprintf( __( 'Could not import: %s', 'briteclean' ), implode( ', ', $failed ) );
	}

	$log[] = __( 'Photos are Pexels-licensed placeholders — swap in the client\'s own before launch.', 'briteclean' );

	return $log;
}

/**
 * Find a previously imported placeholder by its original filename.
 *
 * @param string $filename Source filename.
 * @return int Attachment ID, or 0.
 */
function briteclean_find_placeholder_attachment( $filename ) {
	$existing = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_query'     => array(
				array(
					'key'   => '_briteclean_placeholder',
					'value' => $filename,
				),
			),
		)
	);

	return $existing ? (int) $existing[0] : 0;
}

/**
 * Copy a bundled photo into the uploads directory as an attachment.
 *
 * @param string $path     Absolute source path.
 * @param string $filename Original filename.
 * @param array  $meta     Manifest entry.
 * @return int|WP_Error Attachment ID or error.
 */
function briteclean_sideload_placeholder( $path, $filename, $meta ) {
	// Sideloading MOVES the temp file, so hand it a copy and keep the theme's original.
	$temp = wp_tempnam( $filename );

	if ( ! $temp || ! copy( $path, $temp ) ) {
		return new WP_Error( 'briteclean_copy_failed', __( 'Could not stage the file.', 'briteclean' ) );
	}

	$file = array(
		'name'     => $filename,
		'tmp_name' => $temp,
	);

	$title = ucwords( str_replace( array( '-', '_' ), ' ', pathinfo( $filename, PATHINFO_FILENAME ) ) );

	$attachment_id = media_handle_sideload(
		$file,
		0,
		$title,
		array( 'post_excerpt' => $meta['alt'] ?? '' )
	);

	if ( is_wp_error( $attachment_id ) ) {
		// media_handle_sideload cleans up after itself on success; on failure it may not.
		if ( file_exists( $temp ) ) {
			wp_delete_file( $temp );
		}

		return $attachment_id;
	}

	if ( ! empty( $meta['alt'] ) ) {
		update_post_meta( $attachment_id, '_wp_attachment_image_alt', sanitize_text_field( $meta['alt'] ) );
	}

	// Tag it so a re-run recognises it rather than importing a duplicate.
	update_post_meta( $attachment_id, '_briteclean_placeholder', $filename );

	if ( ! empty( $meta['pexels_id'] ) ) {
		update_post_meta( $attachment_id, '_briteclean_source', 'Pexels #' . $meta['pexels_id'] );
	}

	return (int) $attachment_id;
}

/**
 * Attach an imported photo to whatever the manifest says it belongs to.
 *
 * Targets are "hero", "page:{slug}" or "product:{slug}". An existing image is never
 * overwritten — if the client has already set one, theirs wins.
 *
 * @param int    $attachment_id Attachment ID.
 * @param string $target        Manifest target string.
 * @return bool Whether anything was attached.
 */
function briteclean_attach_placeholder( $attachment_id, $target ) {
	if ( ! $target ) {
		return false;
	}

	if ( 'hero' === $target ) {
		$front_id = (int) get_option( 'page_on_front' );

		if ( ! $front_id ) {
			return false;
		}

		// Do not clobber a hero the client has already chosen.
		if ( briteclean_field( 'hero_image', 0, $front_id ) ) {
			return false;
		}

		if ( function_exists( 'update_field' ) ) {
			update_field( 'hero_image', $attachment_id, $front_id );
		} else {
			// Written in ACF's own format, so the value is picked up if ACF is
			// installed later rather than being stranded as orphan meta.
			update_post_meta( $front_id, 'hero_image', $attachment_id );
			update_post_meta( $front_id, '_hero_image', 'field_bc_hero_image' );
		}

		return true;
	}

	list( $type, $slug ) = array_pad( explode( ':', $target, 2 ), 2, '' );

	if ( ! $slug ) {
		return false;
	}

	if ( 'page' === $type ) {
		$post = get_page_by_path( $slug );
	} elseif ( 'product' === $type ) {
		$post = get_page_by_path( $slug, OBJECT, 'product' );
	} else {
		return false;
	}

	if ( ! $post || has_post_thumbnail( $post->ID ) ) {
		return false;
	}

	set_post_thumbnail( $post->ID, $attachment_id );

	return true;
}

/**
 * On theme activation, make sure the service category exists and permalinks are fresh.
 */
function briteclean_after_switch_theme() {
	briteclean_register_testimonials();

	if ( class_exists( 'WooCommerce' ) ) {
		briteclean_ensure_service_category();
	}

	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'briteclean_after_switch_theme' );
