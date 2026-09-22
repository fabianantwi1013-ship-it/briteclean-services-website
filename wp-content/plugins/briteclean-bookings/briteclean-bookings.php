<?php
/**
 * Plugin Name:       Briteclean Bookings
 * Plugin URI:        https://britecleanservices.com
 * Description:       Request-based booking engine for Briteclean Services LLC. Registers a Booking Request post type, renders the multi-step booking form, validates and stores submissions, and notifies both the business and the customer by email.
 * Version:           1.0.0
 * Requires at least: 6.4
 * Requires PHP:      8.0
 * Author:            Built for Briteclean Services LLC
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       briteclean-bookings
 * Domain Path:       /languages
 *
 * @package BritecleanBookings
 */

defined( 'ABSPATH' ) || exit;

define( 'BRITECLEAN_BOOKINGS_VERSION', '1.0.0' );
define( 'BRITECLEAN_BOOKINGS_FILE', __FILE__ );
define( 'BRITECLEAN_BOOKINGS_DIR', plugin_dir_path( __FILE__ ) );
define( 'BRITECLEAN_BOOKINGS_URI', plugin_dir_url( __FILE__ ) );

/**
 * The post type key for booking requests.
 *
 * Deliberately short and prefixed. WordPress caps post type keys at 20 characters.
 */
define( 'BRITECLEAN_BOOKING_CPT', 'bc_booking' );

require_once BRITECLEAN_BOOKINGS_DIR . 'includes/class-bc-schema.php';
require_once BRITECLEAN_BOOKINGS_DIR . 'includes/class-bc-cpt.php';
require_once BRITECLEAN_BOOKINGS_DIR . 'includes/class-bc-validator.php';
require_once BRITECLEAN_BOOKINGS_DIR . 'includes/class-bc-form.php';
require_once BRITECLEAN_BOOKINGS_DIR . 'includes/class-bc-handler.php';
require_once BRITECLEAN_BOOKINGS_DIR . 'includes/class-bc-emails.php';
require_once BRITECLEAN_BOOKINGS_DIR . 'includes/class-bc-admin.php';
require_once BRITECLEAN_BOOKINGS_DIR . 'includes/class-bc-export.php';
require_once BRITECLEAN_BOOKINGS_DIR . 'includes/class-bc-contact.php';

/**
 * Boot the plugin.
 */
function briteclean_bookings_init() {
	BC_CPT::init();
	BC_Form::init();
	BC_Handler::init();
	BC_Admin::init();
	BC_Export::init();
	BC_Contact::init();

	load_plugin_textdomain(
		'briteclean-bookings',
		false,
		dirname( plugin_basename( BRITECLEAN_BOOKINGS_FILE ) ) . '/languages'
	);
}
add_action( 'plugins_loaded', 'briteclean_bookings_init' );

/**
 * On activation, register the post type then flush rewrites so the admin URLs resolve
 * immediately rather than after the next permalink save.
 */
function briteclean_bookings_activate() {
	BC_CPT::register();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'briteclean_bookings_activate' );

/**
 * Clean up rewrite rules on deactivation. Booking data is intentionally left in place —
 * deactivating a plugin should never destroy the client's records.
 */
function briteclean_bookings_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'briteclean_bookings_deactivate' );
