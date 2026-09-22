<?php
/**
 * Fallback contact form.
 *
 * Intentionally minimal: four fields for people who want to ask a question rather than
 * book. Anything that is actually a booking belongs in the booking form, which is why
 * this one links across to it. Messages are emailed and not stored as posts — there is
 * no admin workflow around them, so keeping them out of the database avoids holding
 * personal data nobody is going to manage.
 *
 * @package BritecleanBookings
 */

defined( 'ABSPATH' ) || exit;

/**
 * Contact form shortcode and handler.
 */
class BC_Contact {

	/**
	 * Hook registration.
	 */
	public static function init() {
		add_shortcode( 'briteclean_contact_form', array( __CLASS__, 'shortcode' ) );
		add_action( 'admin_post_nopriv_briteclean_submit_contact', array( __CLASS__, 'handle' ) );
		add_action( 'admin_post_briteclean_submit_contact', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Render the form.
	 *
	 * @return string
	 */
	public static function shortcode() {
		// Shares the booking form's stylesheet.
		wp_enqueue_style( 'briteclean-booking-form' );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Display-only flag.
		$sent = isset( $_GET['contact'] ) ? sanitize_key( wp_unslash( $_GET['contact'] ) ) : '';

		ob_start();

		if ( 'sent' === $sent ) {
			?>
			<div class="bc-alert bc-alert--success" role="status">
				<strong><?php esc_html_e( 'Message sent.', 'briteclean-bookings' ); ?></strong>
				<p><?php esc_html_e( 'Thanks for getting in touch — we will reply as soon as we can.', 'briteclean-bookings' ); ?></p>
			</div>
			<?php

			return ob_get_clean();
		}
		?>

		<form class="bc-form bc-form--contact"
			method="post"
			action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">

			<input type="hidden" name="action" value="briteclean_submit_contact" />
			<input type="hidden" name="bc_redirect" value="<?php echo esc_url( get_permalink() ); ?>" />
			<?php wp_nonce_field( 'briteclean_submit_contact', 'bc_contact_nonce' ); ?>

			<?php if ( 'error' === $sent ) : ?>
				<div class="bc-alert bc-alert--error" role="alert">
					<?php esc_html_e( 'Please fill in your name, a valid email address and a message.', 'briteclean-bookings' ); ?>
				</div>
			<?php endif; ?>

			<div class="bc-hp" aria-hidden="true">
				<label for="bc-contact-website"><?php esc_html_e( 'Leave this field empty', 'briteclean-bookings' ); ?></label>
				<input type="text" id="bc-contact-website" name="bc_website" value="" tabindex="-1" autocomplete="off" />
			</div>

			<div class="bc-field">
				<label class="bc-label" for="bc-contact-name">
					<?php esc_html_e( 'Your name', 'briteclean-bookings' ); ?>
					<span class="bc-req" aria-hidden="true">*</span>
					<span class="screen-reader-text"><?php esc_html_e( '(required)', 'briteclean-bookings' ); ?></span>
				</label>
				<input class="bc-input" type="text" id="bc-contact-name" name="bc_name" autocomplete="name" maxlength="120" required />
			</div>

			<div class="bc-field">
				<label class="bc-label" for="bc-contact-email">
					<?php esc_html_e( 'Email address', 'briteclean-bookings' ); ?>
					<span class="bc-req" aria-hidden="true">*</span>
					<span class="screen-reader-text"><?php esc_html_e( '(required)', 'briteclean-bookings' ); ?></span>
				</label>
				<input class="bc-input" type="email" id="bc-contact-email" name="bc_email" autocomplete="email" maxlength="190" required />
			</div>

			<div class="bc-field">
				<label class="bc-label" for="bc-contact-phone"><?php esc_html_e( 'Phone (optional)', 'briteclean-bookings' ); ?></label>
				<input class="bc-input" type="tel" id="bc-contact-phone" name="bc_phone" autocomplete="tel" maxlength="40" />
			</div>

			<div class="bc-field">
				<label class="bc-label" for="bc-contact-message">
					<?php esc_html_e( 'Message', 'briteclean-bookings' ); ?>
					<span class="bc-req" aria-hidden="true">*</span>
					<span class="screen-reader-text"><?php esc_html_e( '(required)', 'briteclean-bookings' ); ?></span>
				</label>
				<textarea class="bc-input" id="bc-contact-message" name="bc_message" rows="5" maxlength="2000" required></textarea>
			</div>

			<button type="submit" class="bc-btn bc-btn--primary bc-btn--block">
				<?php esc_html_e( 'Send Message', 'briteclean-bookings' ); ?>
			</button>
		</form>
		<?php

		return ob_get_clean();
	}

	/**
	 * Handle a contact submission.
	 */
	public static function handle() {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Validated below.
		$raw_redirect = isset( $_POST['bc_redirect'] ) ? esc_url_raw( wp_unslash( $_POST['bc_redirect'] ) ) : '';
		$redirect     = wp_validate_redirect( $raw_redirect, home_url( '/contact/' ) );
		$redirect     = remove_query_arg( 'contact', $redirect );

		if ( ! isset( $_POST['bc_contact_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['bc_contact_nonce'] ) ), 'briteclean_submit_contact' ) ) {
			wp_safe_redirect( add_query_arg( 'contact', 'error', $redirect ) );
			exit;
		}

		// Honeypot: pretend it worked rather than telling a bot it was caught.
		if ( ! empty( $_POST['bc_website'] ) ) {
			wp_safe_redirect( add_query_arg( 'contact', 'sent', $redirect ) );
			exit;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified above.
		$name = isset( $_POST['bc_name'] ) ? sanitize_text_field( wp_unslash( $_POST['bc_name'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified above.
		$email = isset( $_POST['bc_email'] ) ? sanitize_email( wp_unslash( $_POST['bc_email'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified above.
		$phone = isset( $_POST['bc_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['bc_phone'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified above.
		$message = isset( $_POST['bc_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['bc_message'] ) ) : '';

		if ( ! $name || ! $email || ! is_email( $email ) || mb_strlen( $message ) < 5 ) {
			wp_safe_redirect( add_query_arg( 'contact', 'error', $redirect ) );
			exit;
		}

		$to = get_option( 'admin_email' );

		if ( function_exists( 'get_theme_mod' ) ) {
			$configured = get_theme_mod( 'briteclean_booking_notify_email', '' );
			$first      = trim( (string) strtok( (string) $configured, ',' ) );

			if ( $first && is_email( $first ) ) {
				$to = $first;
			}
		}

		$subject = sprintf(
			/* translators: %s: sender name. */
			__( 'Website enquiry from %s', 'briteclean-bookings' ),
			$name
		);

		$body = sprintf(
			"<p><strong>%s</strong> %s</p><p><strong>%s</strong> %s</p><p><strong>%s</strong> %s</p><hr /><p>%s</p>",
			esc_html__( 'Name:', 'briteclean-bookings' ),
			esc_html( $name ),
			esc_html__( 'Email:', 'briteclean-bookings' ),
			esc_html( $email ),
			esc_html__( 'Phone:', 'briteclean-bookings' ),
			esc_html( $phone ? $phone : __( 'not provided', 'briteclean-bookings' ) ),
			nl2br( esc_html( $message ) )
		);

		wp_mail(
			$to,
			$subject,
			$body,
			array(
				'Content-Type: text/html; charset=UTF-8',
				sprintf( 'Reply-To: %s <%s>', $name, $email ),
			)
		);

		wp_safe_redirect( add_query_arg( 'contact', 'sent', $redirect ) );
		exit;
	}
}
