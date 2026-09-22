<?php
/**
 * Booking Request post type.
 *
 * Private by design: bookings contain customer names, phone numbers and home
 * addresses, so the post type is not public, not queryable on the front end, and
 * excluded from search and REST. Nothing about a booking is ever addressable by URL.
 *
 * @package BritecleanBookings
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers the booking post type and its meta.
 */
class BC_CPT {

	/**
	 * Hook registration.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ) );
		add_action( 'init', array( __CLASS__, 'register_meta' ) );
	}

	/**
	 * Register the post type.
	 */
	public static function register() {
		$labels = array(
			'name'                  => __( 'Booking Requests', 'briteclean-bookings' ),
			'singular_name'         => __( 'Booking Request', 'briteclean-bookings' ),
			'menu_name'             => __( 'Bookings', 'briteclean-bookings' ),
			'all_items'             => __( 'All Bookings', 'briteclean-bookings' ),
			'add_new'               => __( 'Add New', 'briteclean-bookings' ),
			'add_new_item'          => __( 'Add Booking Request', 'briteclean-bookings' ),
			'edit_item'             => __( 'Booking Request', 'briteclean-bookings' ),
			'new_item'              => __( 'New Booking Request', 'briteclean-bookings' ),
			'view_item'             => __( 'View Booking Request', 'briteclean-bookings' ),
			'search_items'          => __( 'Search Bookings', 'briteclean-bookings' ),
			'not_found'             => __( 'No booking requests yet.', 'briteclean-bookings' ),
			'not_found_in_trash'    => __( 'No booking requests in the trash.', 'briteclean-bookings' ),
			'items_list'            => __( 'Booking requests list', 'briteclean-bookings' ),
			'item_published'        => __( 'Booking request saved.', 'briteclean-bookings' ),
			'item_updated'          => __( 'Booking request updated.', 'briteclean-bookings' ),
		);

		register_post_type(
			BRITECLEAN_BOOKING_CPT,
			array(
				'labels'              => $labels,
				'public'              => false,
				'publicly_queryable'  => false,
				'exclude_from_search' => true,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_nav_menus'   => false,
				'show_in_admin_bar'   => false,
				// Kept out of REST so booking data is never exposed via /wp-json.
				'show_in_rest'        => false,
				'menu_icon'           => 'dashicons-calendar-alt',
				'menu_position'       => 25,
				'hierarchical'        => false,
				'has_archive'         => false,
				'rewrite'             => false,
				'query_var'           => false,
				'supports'            => array( 'title' ),
				'capability_type'     => 'post',
				'map_meta_cap'        => true,
			)
		);
	}

	/**
	 * Register meta keys so WordPress knows their types and sanitisers.
	 *
	 * auth_callback denies everyone without edit_posts, which keeps the values out of
	 * any REST or meta API surface that might otherwise read them.
	 */
	public static function register_meta() {
		$auth = static function () {
			return current_user_can( 'edit_posts' );
		};

		foreach ( BC_Schema::fields() as $key => $field ) {
			register_post_meta(
				BRITECLEAN_BOOKING_CPT,
				BC_Schema::meta_key( $key ),
				array(
					'type'              => 'checkboxes' === $field['type'] ? 'array' : 'string',
					'single'            => true,
					'show_in_rest'      => false,
					'auth_callback'     => $auth,
					'sanitize_callback' => null,
				)
			);
		}

		foreach ( array( '_bc_status', '_bc_ip', '_bc_source', '_bc_user_agent', '_bc_admin_notes' ) as $meta_key ) {
			register_post_meta(
				BRITECLEAN_BOOKING_CPT,
				$meta_key,
				array(
					'type'          => 'string',
					'single'        => true,
					'show_in_rest'  => false,
					'auth_callback' => $auth,
				)
			);
		}
	}

	/**
	 * Current status of a booking, defaulting to "new".
	 *
	 * @param int $post_id Booking ID.
	 * @return string
	 */
	public static function get_status( $post_id ) {
		$status = get_post_meta( $post_id, '_bc_status', true );

		return array_key_exists( $status, BC_Schema::statuses() ) ? $status : 'new';
	}

	/**
	 * Update a booking's status, ignoring anything not in the known set.
	 *
	 * @param int    $post_id Booking ID.
	 * @param string $status  New status key.
	 * @return bool Whether the status was changed.
	 */
	public static function set_status( $post_id, $status ) {
		if ( ! array_key_exists( $status, BC_Schema::statuses() ) ) {
			return false;
		}

		$previous = self::get_status( $post_id );

		if ( $previous === $status ) {
			return false;
		}

		update_post_meta( $post_id, '_bc_status', $status );

		/**
		 * Fires after a booking's status changes.
		 *
		 * @param int    $post_id  Booking ID.
		 * @param string $status   New status.
		 * @param string $previous Previous status.
		 */
		do_action( 'briteclean_booking_status_changed', $post_id, $status, $previous );

		return true;
	}

	/**
	 * Count bookings per status, for the admin filter links.
	 *
	 * @return array Status key => count.
	 */
	public static function status_counts() {
		global $wpdb;

		$counts = array_fill_keys( array_keys( BC_Schema::statuses() ), 0 );

		// One grouped query rather than five WP_Query calls.
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT pm.meta_value AS status, COUNT(*) AS total
				 FROM {$wpdb->postmeta} pm
				 INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
				 WHERE pm.meta_key = %s
				   AND p.post_type = %s
				   AND p.post_status NOT IN ( 'trash', 'auto-draft' )
				 GROUP BY pm.meta_value",
				'_bc_status',
				BRITECLEAN_BOOKING_CPT
			)
		);

		foreach ( $rows as $row ) {
			if ( isset( $counts[ $row->status ] ) ) {
				$counts[ $row->status ] = (int) $row->total;
			}
		}

		return $counts;
	}
}
