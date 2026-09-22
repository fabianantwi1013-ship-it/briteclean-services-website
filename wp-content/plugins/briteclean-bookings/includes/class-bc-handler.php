<?php
/**
 * Booking submission handling.
 *
 * Post/Redirect/Get: the form POSTs to admin-post.php, and every outcome ends in a
 * redirect. A refresh after submitting therefore cannot create a duplicate booking.
 *
 * @package BritecleanBookings
 */

defined( 'ABSPATH' ) || exit;

/**
 * Receives, validates and stores booking submissions.
 */
class BC_Handler {

	/**
	 * Minimum seconds between page render and submission.
	 *
	 * Nobody fills in five steps in under three seconds. Bots routinely do.
	 */
	const MIN_FILL_SECONDS = 3;

	/**
	 * Maximum age of a rendered form, in seconds. Two hours.
	 */
	const MAX_FORM_AGE = 7200;

	/**
	 * Maximum submissions from one IP per hour.
	 */
	const RATE_LIMIT = 5;

	/**
	 * Hook registration.
	 */
	public static function init() {
		add_action( 'admin_post_nopriv_briteclean_submit_booking', array( __CLASS__, 'handle' ) );
		add_action( 'admin_post_briteclean_submit_booking', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle a submission end to end.
	 */
	public static function handle() {
		$redirect = self::resolve_redirect();

		// Nonce first: everything downstream assumes the request came from our form.
		if ( ! isset( $_POST['bc_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['bc_nonce'] ) ), 'briteclean_submit_booking' ) ) {
			self::bail( $redirect, array( 'form' => __( 'Your session expired. Please try again.', 'briteclean-bookings' ) ), array() );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified above.
		$raw = wp_unslash( $_POST );

		// Silent success for spam: telling a bot why it failed only helps it adapt.
		if ( self::check_spam( $raw ) ) {
			wp_safe_redirect( add_query_arg( 'booking', 'success', $redirect ) . '#bc-booking-confirmation' );
			exit;
		}

		if ( self::rate_limited() ) {
			self::bail(
				$redirect,
				array( 'form' => __( 'That is several requests in a short time. Please call us instead and we will sort it out directly.', 'briteclean-bookings' ) ),
				array()
			);
		}

		$validator = new BC_Validator();

		if ( ! $validator->validate( $raw ) ) {
			self::bail( $redirect, $validator->errors(), $validator->clean() );
		}

		$clean   = $validator->clean();
		$post_id = self::create_booking( $clean );

		if ( is_wp_error( $post_id ) || ! $post_id ) {
			self::bail(
				$redirect,
				array( 'form' => __( 'We could not save your request. Please try again or give us a call.', 'briteclean-bookings' ) ),
				$clean
			);
		}

		self::bump_rate_limit();

		// Emails are best-effort. A mail failure must not lose the booking — it is
		// already stored, and the admin will see it in the dashboard regardless.
		$owner_sent    = BC_Emails::notify_owner( $post_id );
		$customer_sent = BC_Emails::confirm_customer( $post_id );

		if ( ! $owner_sent ) {
			update_post_meta( $post_id, '_bc_owner_email_failed', '1' );
		}

		if ( ! $customer_sent ) {
			update_post_meta( $post_id, '_bc_customer_email_failed', '1' );
		}

		/**
		 * Fires after a booking request has been stored and notifications attempted.
		 *
		 * @param int   $post_id Booking ID.
		 * @param array $clean   Validated values.
		 */
		do_action( 'briteclean_booking_created', $post_id, $clean );

		wp_safe_redirect( add_query_arg( 'booking', 'success', $redirect ) . '#bc-booking-confirmation' );
		exit;
	}

	/**
	 * Create the booking post and write its meta.
	 *
	 * @param array $clean Validated values.
	 * @return int|WP_Error
	 */
	private static function create_booking( array $clean ) {
		$name     = $clean['full_name'] ?? __( 'Unknown', 'briteclean-bookings' );
		$date     = $clean['preferred_date'] ?? '';
		$date_txt = $date ? date_i18n( 'j M Y', strtotime( $date ) ) : __( 'no date', 'briteclean-bookings' );

		$post_id = wp_insert_post(
			array(
				'post_type'   => BRITECLEAN_BOOKING_CPT,
				'post_status' => 'publish',
				'post_title'  => sprintf(
					/* translators: 1: customer name, 2: preferred date. */
					__( '%1$s — %2$s', 'briteclean-bookings' ),
					$name,
					$date_txt
				),
			),
			true
		);

		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}

		foreach ( BC_Schema::fields() as $key => $field ) {
			$value = $clean[ $key ] ?? ( 'checkboxes' === $field['type'] ? array() : '' );
			update_post_meta( $post_id, BC_Schema::meta_key( $key ), $value );
		}

		update_post_meta( $post_id, '_bc_status', 'new' );
		update_post_meta( $post_id, '_bc_source', isset( $_POST['bc_context'] ) ? sanitize_key( wp_unslash( $_POST['bc_context'] ) ) : 'page' ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified by caller.
		update_post_meta( $post_id, '_bc_ip', self::client_ip() );
		update_post_meta( $post_id, '_bc_user_agent', isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '' );

		return $post_id;
	}

	/**
	 * Spam heuristics.
	 *
	 * Two layers ship enabled, both invisible to real visitors:
	 *
	 *   1. Honeypot — a hidden field that only an automated filler would populate.
	 *   2. Time trap — a signed render timestamp; submissions that arrive impossibly
	 *      fast, or from a form older than two hours, are rejected.
	 *
	 * ---------------------------------------------------------------------------
	 * ADDING reCAPTCHA (or hCaptcha / Turnstile)
	 * ---------------------------------------------------------------------------
	 * If these two are not holding back the spam, add a challenge as a third layer.
	 * The insertion points are:
	 *
	 *   1. Enqueue the provider script and render the widget in BC_Form::render(),
	 *      just above the `.bc-form__nav` div.
	 *   2. Verify the token HERE, at the end of this method, before returning false:
	 *
	 *          $token = isset( $raw['g-recaptcha-response'] ) ? sanitize_text_field( $raw['g-recaptcha-response'] ) : '';
	 *          $check = wp_remote_post(
	 *              'https://www.google.com/recaptcha/api/siteverify',
	 *              array( 'body' => array( 'secret' => RECAPTCHA_SECRET, 'response' => $token, 'remoteip' => self::client_ip() ) )
	 *          );
	 *          $body = json_decode( wp_remote_retrieve_body( $check ), true );
	 *          if ( empty( $body['success'] ) || ( isset( $body['score'] ) && $body['score'] < 0.5 ) ) {
	 *              return true;
	 *          }
	 *
	 *   3. Store the keys as constants in wp-config.php, never in this file — it is
	 *      version-controlled.
	 *
	 * Turnstile is worth preferring over reCAPTCHA here: no visible challenge, no
	 * Google cookie, and it avoids the accessibility problems image challenges cause.
	 *
	 * @param array $raw Unslashed POST data.
	 * @return bool True when the submission looks automated.
	 */
	private static function check_spam( array $raw ) {
		// Layer 1: the honeypot must be empty.
		if ( ! empty( $raw['bc_website'] ) ) {
			return true;
		}

		// Layer 2: the signed render timestamp.
		$issued = isset( $raw['bc_ts'] ) ? (int) $raw['bc_ts'] : 0;
		$hash   = isset( $raw['bc_tsh'] ) ? (string) $raw['bc_tsh'] : '';

		if ( ! $issued || ! hash_equals( wp_hash( (string) $issued ), $hash ) ) {
			return true;
		}

		$elapsed = time() - $issued;

		if ( $elapsed < self::MIN_FILL_SECONDS || $elapsed > self::MAX_FORM_AGE ) {
			return true;
		}

		/**
		 * Filter the spam verdict, for adding a captcha or an external service.
		 *
		 * @param bool  $is_spam Current verdict.
		 * @param array $raw     Unslashed POST data.
		 */
		return (bool) apply_filters( 'briteclean_booking_is_spam', false, $raw );
	}

	/**
	 * Whether this IP has submitted too many bookings in the last hour.
	 *
	 * @return bool
	 */
	private static function rate_limited() {
		$key   = 'bc_rate_' . md5( self::client_ip() );
		$count = (int) get_transient( $key );

		return $count >= self::RATE_LIMIT;
	}

	/**
	 * Record a successful submission against the rate limit.
	 */
	private static function bump_rate_limit() {
		$key   = 'bc_rate_' . md5( self::client_ip() );
		$count = (int) get_transient( $key );

		set_transient( $key, $count + 1, HOUR_IN_SECONDS );
	}

	/**
	 * The client IP, stored for abuse handling only.
	 *
	 * Deliberately reads REMOTE_ADDR and not X-Forwarded-For: that header is
	 * trivially spoofed, which would let an abuser sidestep the rate limit by
	 * inventing a new value per request. Behind a reverse proxy or Cloudflare, read
	 * the proxy's own trusted header here instead.
	 *
	 * @return string
	 */
	private static function client_ip() {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

		return filter_var( $ip, FILTER_VALIDATE_IP ) ? $ip : '';
	}

	/**
	 * Where to send the visitor back to.
	 *
	 * wp_validate_redirect keeps an attacker from turning the form into an open
	 * redirect by posting an off-site bc_redirect value.
	 *
	 * @return string
	 */
	private static function resolve_redirect() {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Validated against the site host below.
		$raw = isset( $_POST['bc_redirect'] ) ? esc_url_raw( wp_unslash( $_POST['bc_redirect'] ) ) : '';

		$fallback = home_url( '/book-now/' );

		if ( function_exists( 'briteclean_page_url' ) ) {
			$fallback = briteclean_page_url( 'book-now' );
		}

		$redirect = wp_validate_redirect( $raw, $fallback );

		// Drop any previous run's flags so they do not stack up in the URL.
		return remove_query_arg( array( 'booking', 'bc_token' ), $redirect );
	}

	/**
	 * Redirect back to the form with errors and the submitted values preserved.
	 *
	 * @param string $redirect Target URL.
	 * @param array  $errors   Field errors.
	 * @param array  $values   Submitted values.
	 */
	private static function bail( $redirect, array $errors, array $values ) {
		$token = BC_Form::push_state( $errors, $values );

		$url = add_query_arg(
			array(
				'booking'  => 'error',
				'bc_token' => $token,
			),
			$redirect
		);

		wp_safe_redirect( $url . '#book-now' );
		exit;
	}
}
