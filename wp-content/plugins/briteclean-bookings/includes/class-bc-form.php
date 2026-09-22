<?php
/**
 * Booking form rendering.
 *
 * The form is a plain HTML form that posts to admin-post.php. Every step is a real
 * fieldset that is visible by default; the stepper behaviour is added by JavaScript,
 * which sets a class on the form. If the script fails to load — or is blocked — the
 * visitor gets one long form that still submits and still validates. Nobody loses the
 * ability to book because of a JS error.
 *
 * @package BritecleanBookings
 */

defined( 'ABSPATH' ) || exit;

/**
 * Renders the booking form and its shortcode.
 */
class BC_Form {

	/**
	 * Transient prefix for redirected form state.
	 */
	const STATE_PREFIX = 'bc_form_state_';

	/**
	 * Hook registration.
	 */
	public static function init() {
		add_shortcode( 'briteclean_booking_form', array( __CLASS__, 'shortcode' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_assets' ) );
		// Priority 20: after register_assets() above has defined the handles.
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'maybe_enqueue' ), 20 );
	}

	/**
	 * Register the form's CSS and JS.
	 *
	 * Registered rather than enqueued, so the assets only load on pages that actually
	 * render the form.
	 */
	public static function register_assets() {
		$css = BRITECLEAN_BOOKINGS_DIR . 'assets/css/booking-form.css';
		$js  = BRITECLEAN_BOOKINGS_DIR . 'assets/js/booking-form.js';

		wp_register_style(
			'briteclean-booking-form',
			BRITECLEAN_BOOKINGS_URI . 'assets/css/booking-form.css',
			array(),
			file_exists( $css ) ? filemtime( $css ) : BRITECLEAN_BOOKINGS_VERSION
		);

		wp_register_script(
			'briteclean-booking-form',
			BRITECLEAN_BOOKINGS_URI . 'assets/js/booking-form.js',
			array(),
			file_exists( $js ) ? filemtime( $js ) : BRITECLEAN_BOOKINGS_VERSION,
			true
		);
	}

	/**
	 * Enqueue the form assets during wp_enqueue_scripts, before the head is printed.
	 *
	 * The shortcode itself runs inside the template body, which is far too late for a
	 * stylesheet — WordPress would fall back to printing it with the late styles in the
	 * footer, and the form would visibly flash unstyled on first paint. So the pages
	 * that will render a form are detected up front instead.
	 */
	public static function maybe_enqueue() {
		if ( self::page_has_form() ) {
			self::enqueue();
		}
	}

	/**
	 * Whether the current request is going to render a booking or contact form.
	 *
	 * @return bool
	 */
	private static function page_has_form() {
		// front-page.php renders the booking form as a homepage section unconditionally.
		if ( is_front_page() ) {
			return true;
		}

		if ( ! is_singular() ) {
			return false;
		}

		$post = get_queried_object();

		if ( ! $post instanceof WP_Post ) {
			return false;
		}

		foreach ( array( 'briteclean_booking_form', 'briteclean_contact_form' ) as $tag ) {
			if ( has_shortcode( $post->post_content, $tag ) ) {
				return true;
			}
		}

		// page-contact.php calls the contact shortcode from the template rather than
		// the content, so has_shortcode() cannot see it.
		return is_page( 'contact' );
	}

	/**
	 * Enqueue and localise the form assets. Safe to call more than once.
	 */
	public static function enqueue() {
		wp_enqueue_style( 'briteclean-booking-form' );
		wp_enqueue_script( 'briteclean-booking-form' );

		wp_localize_script(
			'briteclean-booking-form',
			'britecleanBookingL10n',
			array(
				'stepOf'        => __( 'Step %1$d of %2$d', 'briteclean-bookings' ),
				'required'      => __( 'This field is required.', 'briteclean-bookings' ),
				'chooseService' => __( 'Please choose at least one service.', 'briteclean-bookings' ),
				'invalidEmail'  => __( 'Please enter a valid email address.', 'briteclean-bookings' ),
				'invalidPhone'  => __( 'Please enter a valid phone number.', 'briteclean-bookings' ),
				'pastDate'      => __( 'Please choose a date that has not already passed.', 'briteclean-bookings' ),
				'fixErrors'     => __( 'Please fix the highlighted fields before continuing.', 'briteclean-bookings' ),
				'none'          => __( 'Not provided', 'briteclean-bookings' ),
				'submitting'    => __( 'Sending…', 'briteclean-bookings' ),
			)
		);
	}

	/**
	 * Shortcode handler.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public static function shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'context' => 'page',
			),
			$atts,
			'briteclean_booking_form'
		);

		// Normally already enqueued by maybe_enqueue(); this covers the form being
		// placed somewhere that detection cannot see, such as inside a widget.
		self::enqueue();

		ob_start();
		self::render( $atts['context'] );

		return ob_get_clean();
	}

	/**
	 * Retrieve and clear any state carried over from a failed submission.
	 *
	 * Uses a one-shot transient keyed by a token in the URL rather than a session, so
	 * it works on every host and cannot leak one visitor's values to another.
	 *
	 * @return array {
	 *     @type array $errors Field key => message.
	 *     @type array $values Field key => previous value.
	 * }
	 */
	private static function pull_state() {
		$empty = array(
			'errors' => array(),
			'values' => array(),
		);

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only token, not a state change.
		$token = isset( $_GET['bc_token'] ) ? sanitize_key( wp_unslash( $_GET['bc_token'] ) ) : '';

		if ( ! $token ) {
			return $empty;
		}

		$state = get_transient( self::STATE_PREFIX . $token );

		if ( ! is_array( $state ) ) {
			return $empty;
		}

		// One-shot: a refresh should not replay stale errors.
		delete_transient( self::STATE_PREFIX . $token );

		return array(
			'errors' => isset( $state['errors'] ) && is_array( $state['errors'] ) ? $state['errors'] : array(),
			'values' => isset( $state['values'] ) && is_array( $state['values'] ) ? $state['values'] : array(),
		);
	}

	/**
	 * Store form state for the redirect after a failed submission.
	 *
	 * @param array $errors Field errors.
	 * @param array $values Submitted values.
	 * @return string Token to pass back in the URL.
	 */
	public static function push_state( array $errors, array $values ) {
		$token = wp_generate_password( 20, false, false );

		set_transient(
			self::STATE_PREFIX . $token,
			array(
				'errors' => $errors,
				'values' => $values,
			),
			10 * MINUTE_IN_SECONDS
		);

		return $token;
	}

	/**
	 * Render the whole form.
	 *
	 * @param string $context Where the form is rendered — "page" or "home".
	 */
	public static function render( $context = 'page' ) {
		$state  = self::pull_state();
		$errors = $state['errors'];
		$values = $state['values'];

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Display-only flag.
		$status = isset( $_GET['booking'] ) ? sanitize_key( wp_unslash( $_GET['booking'] ) ) : '';

		if ( 'success' === $status ) {
			self::render_success();

			return;
		}

		// Deep link from a service card: ?service=deep-cleaning pre-ticks that box.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Display-only prefill.
		$prefill = isset( $_GET['service'] ) ? sanitize_title( wp_unslash( $_GET['service'] ) ) : '';

		if ( $prefill && empty( $values['services'] ) ) {
			$service_choices = BC_Schema::service_choices();

			if ( array_key_exists( $prefill, $service_choices ) ) {
				$values['services'] = array( $prefill );
			}
		}

		$steps      = BC_Schema::steps();
		$step_count = count( $steps );
		$form_id    = 'bc-booking-form-' . sanitize_key( $context );
		?>

		<div class="bc-form-wrap" id="book-now">

			<?php if ( 'error' === $status && ! $errors ) : ?>
				<div class="bc-alert bc-alert--error" role="alert">
					<strong><?php esc_html_e( 'We could not send that.', 'briteclean-bookings' ); ?></strong>
					<p><?php esc_html_e( 'Something went wrong on our end. Please try again, or call us and we will take the booking over the phone.', 'briteclean-bookings' ); ?></p>
				</div>
			<?php endif; ?>

			<?php if ( $errors ) : ?>
				<div class="bc-alert bc-alert--error" role="alert" tabindex="-1" id="bc-form-errors">
					<strong><?php esc_html_e( 'Please check the following:', 'briteclean-bookings' ); ?></strong>
					<ul>
						<?php
						$all_fields = BC_Schema::fields();

						foreach ( $errors as $field_key => $message ) :
							$label = $all_fields[ $field_key ]['label'] ?? $field_key;
							?>
							<li>
								<a href="#bc-field-<?php echo esc_attr( $field_key ); ?>">
									<?php echo esc_html( $label ); ?>
								</a>
								— <?php echo esc_html( $message ); ?>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

			<form class="bc-form"
				id="<?php echo esc_attr( $form_id ); ?>"
				method="post"
				action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
				novalidate>

				<input type="hidden" name="action" value="briteclean_submit_booking" />
				<input type="hidden" name="bc_context" value="<?php echo esc_attr( $context ); ?>" />
				<input type="hidden" name="bc_redirect" value="<?php echo esc_url( self::current_url() ); ?>" />
				<?php wp_nonce_field( 'briteclean_submit_booking', 'bc_nonce' ); ?>

				<?php
				/*
				 * Spam protection, layer 1: honeypot.
				 *
				 * A field no human sees, hidden in CSS rather than with the hidden
				 * attribute so naive bots still fill it in. Any value at all rejects
				 * the submission. aria-hidden and tabindex keep it away from screen
				 * readers and the keyboard tab order.
				 *
				 * Layer 2 is the timestamp below. For layer 3, see the reCAPTCHA note
				 * in BC_Handler::check_spam().
				 */
				?>
				<div class="bc-hp" aria-hidden="true">
					<label for="bc-website-<?php echo esc_attr( $context ); ?>">
						<?php esc_html_e( 'Leave this field empty', 'briteclean-bookings' ); ?>
					</label>
					<input type="text"
						id="bc-website-<?php echo esc_attr( $context ); ?>"
						name="bc_website"
						value=""
						tabindex="-1"
						autocomplete="off" />
				</div>

				<?php
				/*
				 * Spam protection, layer 2: a render timestamp. A submission arriving
				 * within a couple of seconds of the page loading was not typed by a
				 * person. Signed with wp_hash so it cannot simply be back-dated.
				 */
				$issued = time();
				?>
				<input type="hidden" name="bc_ts" value="<?php echo esc_attr( $issued ); ?>" />
				<input type="hidden" name="bc_tsh" value="<?php echo esc_attr( wp_hash( (string) $issued ) ); ?>" />

				<ol class="bc-progress" aria-hidden="true">
					<?php foreach ( $steps as $index => $step ) : ?>
						<li class="bc-progress__item <?php echo 0 === $index ? 'is-active' : ''; ?>"
							data-step="<?php echo esc_attr( $index + 1 ); ?>">
							<span class="bc-progress__dot"><?php echo esc_html( $index + 1 ); ?></span>
							<span class="bc-progress__label"><?php echo esc_html( $step['title'] ); ?></span>
						</li>
					<?php endforeach; ?>
				</ol>

				<p class="bc-step-counter" role="status" aria-live="polite"></p>

				<?php foreach ( $steps as $index => $step ) : ?>
					<fieldset class="bc-step" data-step="<?php echo esc_attr( $index + 1 ); ?>">
						<legend class="bc-step__legend">
							<span class="bc-step__num"><?php echo esc_html( $index + 1 ); ?></span>
							<span>
								<span class="bc-step__title"><?php echo esc_html( $step['title'] ); ?></span>
								<span class="bc-step__intro"><?php echo esc_html( $step['intro'] ); ?></span>
							</span>
						</legend>

						<?php
						if ( 'review' === $step['id'] ) {
							self::render_review_step();
						} else {
							foreach ( BC_Schema::fields_for_step( $step['id'] ) as $key => $field ) {
								self::render_field( $key, $field, $values, $errors );
							}
						}
						?>
					</fieldset>
				<?php endforeach; ?>

				<div class="bc-form__nav">
					<button type="button" class="bc-btn bc-btn--outline bc-form__back" hidden>
						<?php esc_html_e( 'Back', 'briteclean-bookings' ); ?>
					</button>

					<button type="button" class="bc-btn bc-btn--primary bc-form__next" hidden>
						<?php esc_html_e( 'Continue', 'briteclean-bookings' ); ?>
					</button>

					<button type="submit" class="bc-btn bc-btn--primary bc-btn--lg bc-form__submit">
						<?php esc_html_e( 'Send My Booking Request', 'briteclean-bookings' ); ?>
					</button>
				</div>

				<p class="bc-form__note">
					<?php esc_html_e( 'No payment is taken now. We review every request and confirm the details with you personally before anything is scheduled.', 'briteclean-bookings' ); ?>
				</p>
			</form>
		</div>
		<?php
	}

	/**
	 * Render the post-submission confirmation.
	 */
	private static function render_success() {
		?>
		<div class="bc-alert bc-alert--success" role="status" tabindex="-1" id="bc-booking-confirmation">
			<h3><?php esc_html_e( 'Thank you — your request is in.', 'briteclean-bookings' ); ?></h3>

			<p>
				<?php esc_html_e( 'We have received your booking request and sent a confirmation to your email address. A member of the team will be in touch shortly to confirm the details and timing.', 'briteclean-bookings' ); ?>
			</p>

			<p>
				<?php esc_html_e( 'Need it sooner? Call or message us and we will get straight onto it.', 'briteclean-bookings' ); ?>
			</p>

			<?php
			$phone = function_exists( 'briteclean_opt' ) ? briteclean_opt( 'phone_primary' ) : '+1 (484) 347-9523';
			$wa    = function_exists( 'briteclean_whatsapp_url' ) ? briteclean_whatsapp_url() : '';
			$tel   = preg_replace( '/\D+/', '', $phone );
			?>

			<div class="bc-btn-row" style="margin-top:18px">
				<a class="bc-btn bc-btn--primary" href="tel:+<?php echo esc_attr( $tel ); ?>">
					<?php echo esc_html( $phone ); ?>
				</a>

				<?php if ( $wa ) : ?>
					<a class="bc-btn bc-btn--whatsapp" href="<?php echo esc_url( $wa ); ?>" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e( 'Message on WhatsApp', 'briteclean-bookings' ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render the review step.
	 *
	 * JavaScript fills the summary from the live form values. Without JavaScript the
	 * summary stays empty and the fallback text tells the visitor to scroll up — the
	 * answers are all still on screen in that case, because no step is hidden.
	 */
	private static function render_review_step() {
		?>
		<div class="bc-review" data-bc-review>
			<p class="bc-review__fallback">
				<?php esc_html_e( 'Please scroll up and check your answers, then send your request below.', 'briteclean-bookings' ); ?>
			</p>
		</div>

		<div class="bc-consent">
			<p>
				<?php
				printf(
					/* translators: %s: privacy policy link or plain text. */
					esc_html__( 'We use the details above only to contact you about this booking. %s', 'briteclean-bookings' ),
					esc_html__( 'We never sell or share your information.', 'briteclean-bookings' )
				);
				?>
			</p>
		</div>
		<?php
	}

	/**
	 * Render one field.
	 *
	 * @param string $key    Field key.
	 * @param array  $field  Field definition.
	 * @param array  $values Previously submitted values.
	 * @param array  $errors Validation errors.
	 */
	private static function render_field( $key, $field, $values, $errors ) {
		$value    = $values[ $key ] ?? ( 'checkboxes' === $field['type'] ? array() : '' );
		$error    = $errors[ $key ] ?? '';
		$field_id = 'bc-field-' . $key;
		$help_id  = $field_id . '-help';
		$err_id   = $field_id . '-error';
		$required = ! empty( $field['required'] );

		$described = array();

		if ( ! empty( $field['help'] ) ) {
			$described[] = $help_id;
		}

		// The error region is always referenced, even when empty. It carries the
		// `hidden` attribute until there is something to say, so assistive tech
		// ignores it — and when JavaScript reveals it mid-typing, the association is
		// already in place rather than needing the attribute rewritten.
		$described[] = $err_id;

		$describedby = $described ? ' aria-describedby="' . esc_attr( implode( ' ', $described ) ) . '"' : '';
		$invalid     = $error ? ' aria-invalid="true"' : '';
		$req_attr    = $required ? ' required' : '';
		$wrap_class  = 'bc-field bc-field--' . sanitize_html_class( $field['type'] ) . ( $error ? ' bc-field--error' : '' );

		// Grouped inputs need a fieldset/legend rather than a label, so screen readers
		// announce the group name before each option.
		$is_group = in_array( $field['type'], array( 'checkboxes', 'radios' ), true );
		?>

		<div class="<?php echo esc_attr( $wrap_class ); ?>" data-field="<?php echo esc_attr( $key ); ?>">

			<?php if ( $is_group ) : ?>
				<fieldset class="bc-group" id="<?php echo esc_attr( $field_id ); ?>"<?php echo $describedby; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built from esc_attr above. ?>>
					<legend class="bc-label">
						<?php echo esc_html( $field['label'] ); ?>
						<?php if ( $required ) : ?>
							<span class="bc-req" aria-hidden="true">*</span>
							<span class="screen-reader-text"><?php esc_html_e( '(required)', 'briteclean-bookings' ); ?></span>
						<?php endif; ?>
					</legend>

					<?php if ( ! empty( $field['help'] ) ) : ?>
						<p class="bc-help" id="<?php echo esc_attr( $help_id ); ?>"><?php echo esc_html( $field['help'] ); ?></p>
					<?php endif; ?>

					<?php self::render_group_options( $key, $field, $value ); ?>
				</fieldset>
			<?php else : ?>
				<label class="bc-label" for="<?php echo esc_attr( $field_id ); ?>">
					<?php echo esc_html( $field['label'] ); ?>
					<?php if ( $required ) : ?>
						<span class="bc-req" aria-hidden="true">*</span>
						<span class="screen-reader-text"><?php esc_html_e( '(required)', 'briteclean-bookings' ); ?></span>
					<?php endif; ?>
				</label>

				<?php if ( ! empty( $field['help'] ) ) : ?>
					<p class="bc-help" id="<?php echo esc_attr( $help_id ); ?>"><?php echo esc_html( $field['help'] ); ?></p>
				<?php endif; ?>

				<?php
				switch ( $field['type'] ) {
					case 'textarea':
						printf(
							'<textarea class="bc-input" id="%s" name="%s" rows="4"%s%s%s%s%s>%s</textarea>',
							esc_attr( $field_id ),
							esc_attr( $key ),
							$req_attr, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Literal.
							$invalid, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Literal.
							$describedby, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped above.
							! empty( $field['maxlength'] ) ? ' maxlength="' . esc_attr( $field['maxlength'] ) . '"' : '',
							! empty( $field['autocomplete'] ) ? ' autocomplete="' . esc_attr( $field['autocomplete'] ) . '"' : '',
							esc_textarea( is_array( $value ) ? '' : $value )
						);
						break;

					case 'select':
						printf(
							'<select class="bc-input bc-select" id="%s" name="%s"%s%s%s>',
							esc_attr( $field_id ),
							esc_attr( $key ),
							$req_attr, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Literal.
							$invalid, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Literal.
							$describedby // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped above.
						);

						foreach ( BC_Schema::choices( $field ) as $choice_value => $choice_label ) {
							printf(
								'<option value="%s"%s>%s</option>',
								esc_attr( $choice_value ),
								selected( (string) $value, (string) $choice_value, false ),
								esc_html( $choice_label )
							);
						}

						echo '</select>';
						break;

					case 'date':
						printf(
							'<input class="bc-input" type="date" id="%s" name="%s" value="%s" min="%s"%s%s%s />',
							esc_attr( $field_id ),
							esc_attr( $key ),
							esc_attr( is_array( $value ) ? '' : $value ),
							esc_attr( current_time( 'Y-m-d' ) ),
							$req_attr, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Literal.
							$invalid, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Literal.
							$describedby // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped above.
						);
						break;

					default:
						printf(
							'<input class="bc-input" type="%s" id="%s" name="%s" value="%s"%s%s%s%s%s />',
							esc_attr( $field['type'] ),
							esc_attr( $field_id ),
							esc_attr( $key ),
							esc_attr( is_array( $value ) ? '' : $value ),
							$req_attr, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Literal.
							$invalid, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Literal.
							$describedby, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped above.
							! empty( $field['maxlength'] ) ? ' maxlength="' . esc_attr( $field['maxlength'] ) . '"' : '',
							! empty( $field['autocomplete'] ) ? ' autocomplete="' . esc_attr( $field['autocomplete'] ) . '"' : ''
						);
						break;
				}
				?>
			<?php endif; ?>

			<p class="bc-error" id="<?php echo esc_attr( $err_id ); ?>" <?php echo $error ? '' : 'hidden'; ?>>
				<?php echo esc_html( $error ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Render checkbox or radio options.
	 *
	 * Services render as large tappable cards with an icon; everything else renders as
	 * a compact pill. Both are real inputs with real labels — the styling is entirely
	 * on the label, so keyboard and screen-reader behaviour is the browser default.
	 *
	 * @param string $key    Field key.
	 * @param array  $field  Field definition.
	 * @param mixed  $value  Current value.
	 */
	private static function render_group_options( $key, $field, $value ) {
		$choices  = BC_Schema::choices( $field );
		$is_multi = 'checkboxes' === $field['type'];
		$selected = is_array( $value ) ? array_map( 'strval', $value ) : array( (string) $value );
		$as_cards = 'services' === $key;

		printf( '<div class="%s">', $as_cards ? 'bc-options bc-options--cards' : 'bc-options bc-options--pills' );

		foreach ( $choices as $choice_value => $choice_label ) {
			// The empty "Please choose…" entry only makes sense in a <select>.
			if ( '' === $choice_value ) {
				continue;
			}

			$option_id = 'bc-' . $key . '-' . sanitize_html_class( (string) $choice_value );
			$checked   = in_array( (string) $choice_value, $selected, true );
			?>
			<div class="bc-option">
				<input type="<?php echo $is_multi ? 'checkbox' : 'radio'; ?>"
					id="<?php echo esc_attr( $option_id ); ?>"
					name="<?php echo esc_attr( $key . ( $is_multi ? '[]' : '' ) ); ?>"
					value="<?php echo esc_attr( $choice_value ); ?>"
					<?php checked( $checked ); ?> />

				<label for="<?php echo esc_attr( $option_id ); ?>">
					<?php if ( $as_cards && function_exists( 'briteclean_icon' ) ) : ?>
						<span class="bc-option__icon" aria-hidden="true">
							<?php
							echo briteclean_icon( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Hardcoded SVG.
								BC_Schema::service_icon( $choice_value ),
								array( 'size' => 24 )
							);
							?>
						</span>
					<?php endif; ?>

					<span class="bc-option__text"><?php echo esc_html( $choice_label ); ?></span>

					<span class="bc-option__check" aria-hidden="true">
						<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><path d="m4.5 12.5 5 5 10-11"/></svg>
					</span>
				</label>
			</div>
			<?php
		}

		echo '</div>';
	}

	/**
	 * The URL of the page currently being rendered, used as the redirect target.
	 *
	 * @return string
	 */
	private static function current_url() {
		if ( is_singular() ) {
			return get_permalink();
		}

		return home_url( add_query_arg( array() ) );
	}
}
