<?php
/**
 * CSV export of booking requests.
 *
 * Feasible and cheap, so it ships: one submenu page, a couple of filters, and a
 * streamed CSV. It deliberately stops short of scheduled exports, column pickers or
 * Excel formatting — those are a different feature, and this covers the actual need
 * (handing the list to an accountant or importing it into a spreadsheet).
 *
 * Note it writes directly to the output stream rather than building the whole file in
 * memory, so a few thousand bookings will not exhaust PHP's memory limit.
 *
 * @package BritecleanBookings
 */

defined( 'ABSPATH' ) || exit;

/**
 * Booking CSV export.
 */
class BC_Export {

	/**
	 * Hook registration.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_post_briteclean_export_bookings', array( __CLASS__, 'export' ) );
	}

	/**
	 * Add the export submenu page.
	 */
	public static function menu() {
		add_submenu_page(
			'edit.php?post_type=' . BRITECLEAN_BOOKING_CPT,
			__( 'Export Bookings', 'briteclean-bookings' ),
			__( 'Export CSV', 'briteclean-bookings' ),
			'edit_posts',
			'briteclean-export',
			array( __CLASS__, 'render_page' )
		);
	}

	/**
	 * Render the export form.
	 */
	public static function render_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Export Bookings', 'briteclean-bookings' ); ?></h1>

			<p><?php esc_html_e( 'Download booking requests as a CSV file, ready to open in Excel, Numbers or Google Sheets.', 'briteclean-bookings' ); ?></p>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="briteclean_export_bookings" />
				<?php wp_nonce_field( 'briteclean_export_bookings', 'bc_export_nonce' ); ?>

				<table class="form-table" role="presentation">
					<tbody>
						<tr>
							<th scope="row"><label for="bc_export_status"><?php esc_html_e( 'Status', 'briteclean-bookings' ); ?></label></th>
							<td>
								<select name="bc_export_status" id="bc_export_status">
									<option value=""><?php esc_html_e( 'All statuses', 'briteclean-bookings' ); ?></option>
									<?php foreach ( BC_Schema::statuses() as $key => $label ) : ?>
										<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></option>
									<?php endforeach; ?>
								</select>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="bc_export_from"><?php esc_html_e( 'Submitted from', 'briteclean-bookings' ); ?></label></th>
							<td><input type="date" name="bc_export_from" id="bc_export_from" /></td>
						</tr>
						<tr>
							<th scope="row"><label for="bc_export_to"><?php esc_html_e( 'Submitted to', 'briteclean-bookings' ); ?></label></th>
							<td>
								<input type="date" name="bc_export_to" id="bc_export_to" />
								<p class="description"><?php esc_html_e( 'Leave both dates blank to export everything.', 'briteclean-bookings' ); ?></p>
							</td>
						</tr>
					</tbody>
				</table>

				<?php submit_button( __( 'Download CSV', 'briteclean-bookings' ) ); ?>
			</form>

			<hr />

			<h2><?php esc_html_e( 'A note on customer data', 'briteclean-bookings' ); ?></h2>
			<p>
				<?php esc_html_e( 'This file contains customers\' names, phone numbers, email addresses and home addresses. Treat the download the way you would a printed client list: keep it off shared drives and delete it when you are finished with it.', 'briteclean-bookings' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Stream the CSV.
	 */
	public static function export() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( esc_html__( 'You do not have permission to export bookings.', 'briteclean-bookings' ) );
		}

		check_admin_referer( 'briteclean_export_bookings', 'bc_export_nonce' );

		$args = array(
			'post_type'              => BRITECLEAN_BOOKING_CPT,
			'post_status'            => 'publish',
			'posts_per_page'         => -1,
			'orderby'                => 'date',
			'order'                  => 'DESC',
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
		);

		$status = isset( $_POST['bc_export_status'] ) ? sanitize_key( wp_unslash( $_POST['bc_export_status'] ) ) : '';

		if ( $status && array_key_exists( $status, BC_Schema::statuses() ) ) {
			$args['meta_query'] = array(
				array(
					'key'   => '_bc_status',
					'value' => $status,
				),
			);
		}

		$from = isset( $_POST['bc_export_from'] ) ? sanitize_text_field( wp_unslash( $_POST['bc_export_from'] ) ) : '';
		$to   = isset( $_POST['bc_export_to'] ) ? sanitize_text_field( wp_unslash( $_POST['bc_export_to'] ) ) : '';

		$date_query = array();

		if ( $from && preg_match( '/^\d{4}-\d{2}-\d{2}$/', $from ) ) {
			$date_query['after'] = $from . ' 00:00:00';
		}

		if ( $to && preg_match( '/^\d{4}-\d{2}-\d{2}$/', $to ) ) {
			$date_query['before'] = $to . ' 23:59:59';
		}

		if ( $date_query ) {
			$date_query['inclusive'] = true;
			$args['date_query']      = array( $date_query );
		}

		$bookings = get_posts( $args );

		$filename = sprintf( 'briteclean-bookings-%s.csv', gmdate( 'Y-m-d' ) );

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );

		$out = fopen( 'php://output', 'w' );

		// UTF-8 BOM, so Excel on Windows does not mangle accented characters.
		fwrite( $out, "\xEF\xBB\xBF" );

		$headers = array(
			__( 'Booking ID', 'briteclean-bookings' ),
			__( 'Submitted', 'briteclean-bookings' ),
			__( 'Status', 'briteclean-bookings' ),
		);

		foreach ( BC_Schema::fields() as $field ) {
			$headers[] = $field['label'];
		}

		$headers[] = __( 'Internal notes', 'briteclean-bookings' );

		fputcsv( $out, $headers );

		$statuses = BC_Schema::statuses();

		foreach ( $bookings as $booking ) {
			$row = array(
				$booking->ID,
				get_the_date( 'Y-m-d H:i', $booking ),
				$statuses[ BC_CPT::get_status( $booking->ID ) ] ?? '',
			);

			foreach ( BC_Schema::fields() as $key => $field ) {
				$row[] = self::csv_safe( BC_Schema::display_value( $booking->ID, $key ) );
			}

			$row[] = self::csv_safe( (string) get_post_meta( $booking->ID, '_bc_admin_notes', true ) );

			fputcsv( $out, $row );
		}

		fclose( $out );
		exit;
	}

	/**
	 * Neutralise spreadsheet formula injection.
	 *
	 * A value beginning =, +, - or @ is executed as a formula when the CSV is opened
	 * in Excel or Sheets. Since these values come from a public form, prefixing a
	 * single quote keeps a hostile submission from running code on the owner's machine.
	 *
	 * @param string $value Cell value.
	 * @return string
	 */
	private static function csv_safe( $value ) {
		$value = (string) $value;

		if ( '' !== $value && in_array( $value[0], array( '=', '+', '-', '@', "\t", "\r" ), true ) ) {
			return "'" . $value;
		}

		return $value;
	}
}
