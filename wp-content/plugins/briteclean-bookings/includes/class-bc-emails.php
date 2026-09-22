<?php
/**
 * Booking notification emails.
 *
 * Two messages go out per booking: a detailed one to the business, and a short
 * reassurance to the customer. Both are HTML with inline styles, because email
 * clients strip <style> blocks and ignore external stylesheets.
 *
 * @package BritecleanBookings
 */

defined( 'ABSPATH' ) || exit;

/**
 * Builds and sends the notification emails.
 */
class BC_Emails {

	/**
	 * Send the detailed notification to the business.
	 *
	 * @param int $post_id Booking ID.
	 * @return bool Whether the mail was handed off successfully.
	 */
	public static function notify_owner( $post_id ) {
		$recipients = self::owner_recipients();

		if ( ! $recipients ) {
			return false;
		}

		$name    = get_post_meta( $post_id, BC_Schema::meta_key( 'full_name' ), true );
		$date    = BC_Schema::display_value( $post_id, 'preferred_date' );
		$subject = sprintf(
			/* translators: 1: customer name, 2: preferred date. */
			__( 'New booking request — %1$s (%2$s)', 'briteclean-bookings' ),
			$name,
			$date
		);

		$customer_email = get_post_meta( $post_id, BC_Schema::meta_key( 'email' ), true );

		$headers = array( 'Content-Type: text/html; charset=UTF-8' );

		// Reply-To the customer, so hitting reply in the inbox reaches them directly.
		if ( $customer_email && is_email( $customer_email ) ) {
			$headers[] = sprintf( 'Reply-To: %s <%s>', $name, $customer_email );
		}

		return self::send( $recipients, $subject, self::owner_body( $post_id ), $headers );
	}

	/**
	 * Send the confirmation to the customer.
	 *
	 * @param int $post_id Booking ID.
	 * @return bool
	 */
	public static function confirm_customer( $post_id ) {
		$to = get_post_meta( $post_id, BC_Schema::meta_key( 'email' ), true );

		if ( ! $to || ! is_email( $to ) ) {
			return false;
		}

		$subject = sprintf(
			/* translators: %s: business name. */
			__( 'We have received your booking request — %s', 'briteclean-bookings' ),
			self::business_name()
		);

		$headers = array( 'Content-Type: text/html; charset=UTF-8' );

		$reply_to = self::business_email();

		if ( $reply_to && is_email( $reply_to ) ) {
			$headers[] = sprintf( 'Reply-To: %s <%s>', self::business_name(), $reply_to );
		}

		return self::send( array( $to ), $subject, self::customer_body( $post_id ), $headers );
	}

	/**
	 * Send a mail, returning the result.
	 *
	 * @param array  $to      Recipients.
	 * @param string $subject Subject line.
	 * @param string $body    HTML body.
	 * @param array  $headers Headers.
	 * @return bool
	 */
	private static function send( array $to, $subject, $body, array $headers ) {
		$sent = wp_mail( $to, $subject, $body, $headers );

		if ( ! $sent && defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( '[Briteclean Bookings] wp_mail failed for: ' . implode( ', ', $to ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Debug only.
		}

		return (bool) $sent;
	}

	/**
	 * Who receives booking notifications.
	 *
	 * @return array
	 */
	private static function owner_recipients() {
		$configured = function_exists( 'get_theme_mod' ) ? get_theme_mod( 'briteclean_booking_notify_email', '' ) : '';

		$emails = array_filter( array_map( 'trim', explode( ',', (string) $configured ) ) );
		$valid  = array();

		foreach ( $emails as $email ) {
			if ( is_email( $email ) ) {
				$valid[] = $email;
			}
		}

		if ( $valid ) {
			return $valid;
		}

		$admin = get_option( 'admin_email' );

		return $admin && is_email( $admin ) ? array( $admin ) : array();
	}

	/**
	 * Business display name.
	 *
	 * @return string
	 */
	private static function business_name() {
		if ( function_exists( 'briteclean_opt' ) ) {
			return briteclean_opt( 'name' );
		}

		return get_bloginfo( 'name' );
	}

	/**
	 * Business contact email.
	 *
	 * @return string
	 */
	private static function business_email() {
		if ( function_exists( 'briteclean_opt' ) ) {
			return briteclean_opt( 'email' );
		}

		return get_option( 'admin_email' );
	}

	/**
	 * Business phone number.
	 *
	 * @return string
	 */
	private static function business_phone() {
		if ( function_exists( 'briteclean_opt' ) ) {
			return briteclean_opt( 'phone_primary' );
		}

		return '';
	}

	/**
	 * Brand colour, for the email header band.
	 *
	 * @return string
	 */
	private static function brand_color() {
		if ( function_exists( 'get_theme_mod' ) ) {
			$color = get_theme_mod( 'briteclean_color_primary', '#C8102E' );

			return sanitize_hex_color( $color ) ? $color : '#C8102E';
		}

		return '#C8102E';
	}

	/**
	 * Shared HTML shell.
	 *
	 * Table-based, because that is still what Outlook reliably renders.
	 *
	 * @param string $heading Header band heading.
	 * @param string $inner   Body HTML.
	 * @return string
	 */
	private static function wrap( $heading, $inner ) {
		$brand = self::brand_color();
		$name  = self::business_name();

		ob_start();
		?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title><?php echo esc_html( $heading ); ?></title>
</head>
<body style="margin:0;padding:0;background:#f4f4f5;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#40464e;line-height:1.6;">
	<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f4f4f5;padding:24px 12px;">
		<tr>
			<td align="center">
				<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;background:#ffffff;border-radius:14px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.06);">

					<tr>
						<td style="background:<?php echo esc_attr( $brand ); ?>;padding:26px 30px;">
							<p style="margin:0;color:#ffffff;font-size:20px;font-weight:800;line-height:1.3;">
								<?php echo esc_html( $heading ); ?>
							</p>
							<p style="margin:6px 0 0;color:#ffffff;opacity:.85;font-size:13px;">
								<?php echo esc_html( $name ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<td style="padding:30px;">
							<?php echo $inner; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built from escaped parts by the callers. ?>
						</td>
					</tr>

					<tr>
						<td style="background:#1a1a1a;padding:20px 30px;color:#a1a1aa;font-size:12px;">
							<p style="margin:0 0 4px;color:#ffffff;font-weight:700;"><?php echo esc_html( $name ); ?></p>
							<?php if ( self::business_phone() ) : ?>
								<p style="margin:0;"><?php echo esc_html( self::business_phone() ); ?></p>
							<?php endif; ?>
							<?php if ( function_exists( 'briteclean_address_line' ) ) : ?>
								<p style="margin:4px 0 0;"><?php echo esc_html( briteclean_address_line() ); ?></p>
							<?php endif; ?>
						</td>
					</tr>

				</table>
			</td>
		</tr>
	</table>
</body>
</html>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render the booking's fields as an HTML definition table.
	 *
	 * @param int $post_id Booking ID.
	 * @return string
	 */
	private static function details_table( $post_id ) {
		$rows = '';

		foreach ( BC_Schema::fields() as $key => $field ) {
			$value = BC_Schema::display_value( $post_id, $key );

			if ( '' === $value ) {
				continue;
			}

			$rows .= sprintf(
				'<tr>
					<td style="padding:10px 0;border-bottom:1px solid #e5e7eb;vertical-align:top;width:38%%;color:#6b7280;font-size:13px;font-weight:600;">%s</td>
					<td style="padding:10px 0;border-bottom:1px solid #e5e7eb;vertical-align:top;color:#1a1a1a;font-size:14px;">%s</td>
				</tr>',
				esc_html( $field['label'] ),
				nl2br( esc_html( $value ) )
			);
		}

		return '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">' . $rows . '</table>';
	}

	/**
	 * Body of the business notification.
	 *
	 * @param int $post_id Booking ID.
	 * @return string
	 */
	private static function owner_body( $post_id ) {
		$edit_url = admin_url( 'post.php?post=' . (int) $post_id . '&action=edit' );
		$phone    = get_post_meta( $post_id, BC_Schema::meta_key( 'phone' ), true );
		$email    = get_post_meta( $post_id, BC_Schema::meta_key( 'email' ), true );
		$brand    = self::brand_color();

		ob_start();
		?>
		<p style="margin:0 0 18px;font-size:15px;">
			<?php esc_html_e( 'A new booking request has come in through the website. Full details below.', 'briteclean-bookings' ); ?>
		</p>

		<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:22px;background:#fdf2f4;border-radius:10px;">
			<tr>
				<td style="padding:16px 18px;">
					<p style="margin:0 0 6px;font-size:12px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#6b7280;">
						<?php esc_html_e( 'Reach them on', 'briteclean-bookings' ); ?>
					</p>
					<?php if ( $phone ) : ?>
						<p style="margin:0;font-size:17px;font-weight:800;color:#1a1a1a;">
							<a href="tel:<?php echo esc_attr( preg_replace( '/\D+/', '', $phone ) ); ?>" style="color:<?php echo esc_attr( $brand ); ?>;text-decoration:none;"><?php echo esc_html( $phone ); ?></a>
						</p>
					<?php endif; ?>
					<?php if ( $email ) : ?>
						<p style="margin:4px 0 0;font-size:14px;">
							<a href="mailto:<?php echo esc_attr( $email ); ?>" style="color:<?php echo esc_attr( $brand ); ?>;"><?php echo esc_html( $email ); ?></a>
						</p>
					<?php endif; ?>
				</td>
			</tr>
		</table>

		<?php echo self::details_table( $post_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped inside. ?>

		<p style="margin:26px 0 0;">
			<a href="<?php echo esc_url( $edit_url ); ?>"
				style="display:inline-block;background:<?php echo esc_attr( $brand ); ?>;color:#ffffff;padding:13px 26px;border-radius:999px;text-decoration:none;font-weight:800;font-size:14px;">
				<?php esc_html_e( 'Open in the dashboard', 'briteclean-bookings' ); ?>
			</a>
		</p>

		<p style="margin:20px 0 0;font-size:12px;color:#6b7280;">
			<?php
			printf(
				/* translators: %s: submission time. */
				esc_html__( 'Submitted %s. Status is set to New until you change it.', 'briteclean-bookings' ),
				esc_html( get_the_date( 'j M Y, g:i a', $post_id ) )
			);
			?>
		</p>
		<?php
		$inner = ob_get_clean();

		return self::wrap( __( 'New Booking Request', 'briteclean-bookings' ), $inner );
	}

	/**
	 * Body of the customer confirmation.
	 *
	 * @param int $post_id Booking ID.
	 * @return string
	 */
	private static function customer_body( $post_id ) {
		$name  = get_post_meta( $post_id, BC_Schema::meta_key( 'full_name' ), true );
		$first = trim( strtok( (string) $name, ' ' ) );
		$phone = self::business_phone();

		ob_start();
		?>
		<p style="margin:0 0 16px;font-size:16px;">
			<?php
			printf(
				/* translators: %s: customer first name. */
				esc_html__( 'Hi %s,', 'briteclean-bookings' ),
				esc_html( $first ? $first : __( 'there', 'briteclean-bookings' ) )
			);
			?>
		</p>

		<p style="margin:0 0 16px;font-size:15px;">
			<?php esc_html_e( 'Thank you — we have received your booking request. A member of our team will be in touch shortly to confirm the details and agree a time.', 'briteclean-bookings' ); ?>
		</p>

		<p style="margin:0 0 24px;font-size:15px;">
			<strong><?php esc_html_e( 'Nothing has been charged.', 'briteclean-bookings' ); ?></strong>
			<?php esc_html_e( 'This is a request, not a confirmed appointment — we always speak to you before anything is scheduled.', 'briteclean-bookings' ); ?>
		</p>

		<p style="margin:0 0 10px;font-size:12px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#6b7280;">
			<?php esc_html_e( 'What you asked for', 'briteclean-bookings' ); ?>
		</p>

		<?php echo self::details_table( $post_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped inside. ?>

		<p style="margin:26px 0 0;font-size:15px;">
			<?php esc_html_e( 'Spotted something wrong, or need it sooner?', 'briteclean-bookings' ); ?>
			<?php if ( $phone ) : ?>
				<?php
				printf(
					/* translators: %s: business phone number. */
					esc_html__( 'Just reply to this email or call us on %s.', 'briteclean-bookings' ),
					esc_html( $phone )
				);
				?>
			<?php else : ?>
				<?php esc_html_e( 'Just reply to this email.', 'briteclean-bookings' ); ?>
			<?php endif; ?>
		</p>
		<?php
		$inner = ob_get_clean();

		return self::wrap( __( 'Your Booking Request', 'briteclean-bookings' ), $inner );
	}
}
