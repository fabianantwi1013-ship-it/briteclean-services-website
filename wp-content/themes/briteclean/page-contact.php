<?php
/**
 * Contact page — phone, WhatsApp, address, map and a fallback contact form.
 *
 * @package Briteclean
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	get_template_part( 'template-parts/page-head' );

	$body = trim( get_the_content() );
endwhile;

$phone_primary   = briteclean_opt( 'phone_primary' );
$phone_secondary = briteclean_opt( 'phone_secondary' );
$email           = briteclean_opt( 'email' );
$map_query       = briteclean_opt( 'map_query', briteclean_address_line() );
?>

<section class="bc-section">
	<div class="bc-container bc-contact-grid">

		<div>
			<?php if ( ! empty( $body ) ) : ?>
				<div class="bc-content" style="margin-bottom:32px"><?php echo wp_kses_post( wpautop( $body ) ); ?></div>
			<?php endif; ?>

			<h2><?php esc_html_e( 'Talk to us', 'briteclean' ); ?></h2>

			<ul class="bc-contact-list">
				<li>
					<span class="bc-medallion bc-medallion--sm" aria-hidden="true">
						<?php echo briteclean_icon( 'phone', array( 'size' => 18 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Hardcoded SVG. ?>
					</span>
					<div>
						<span class="bc-contact-list__label"><?php esc_html_e( 'Call or WhatsApp', 'briteclean' ); ?></span>
						<a href="tel:+<?php echo esc_attr( briteclean_phone_digits( $phone_primary ) ); ?>"><?php echo esc_html( $phone_primary ); ?></a>
						<?php if ( $phone_secondary ) : ?>
							<br />
							<a href="tel:+<?php echo esc_attr( briteclean_phone_digits( $phone_secondary ) ); ?>"><?php echo esc_html( $phone_secondary ); ?></a>
						<?php endif; ?>
					</div>
				</li>

				<?php if ( $email ) : ?>
					<li>
						<span class="bc-medallion bc-medallion--sm" aria-hidden="true">
							<?php echo briteclean_icon( 'mail', array( 'size' => 18 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Hardcoded SVG. ?>
						</span>
						<div>
							<span class="bc-contact-list__label"><?php esc_html_e( 'Email', 'briteclean' ); ?></span>
							<a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
						</div>
					</li>
				<?php endif; ?>

				<li>
					<span class="bc-medallion bc-medallion--sm" aria-hidden="true">
						<?php echo briteclean_icon( 'pin', array( 'size' => 18 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Hardcoded SVG. ?>
					</span>
					<div>
						<span class="bc-contact-list__label"><?php esc_html_e( 'Address', 'briteclean' ); ?></span>
						<address style="font-style:normal">
							<?php echo esc_html( briteclean_opt( 'street' ) ); ?><br />
							<?php echo esc_html( briteclean_opt( 'city' ) . ', ' . briteclean_opt( 'state' ) . ' ' . briteclean_opt( 'postal' ) ); ?>
						</address>
					</div>
				</li>

				<li>
					<span class="bc-medallion bc-medallion--sm" aria-hidden="true">
						<?php echo briteclean_icon( 'clock', array( 'size' => 18 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Hardcoded SVG. ?>
					</span>
					<div>
						<span class="bc-contact-list__label"><?php esc_html_e( 'Hours', 'briteclean' ); ?></span>
						<dl class="bc-footer__hours" style="color:var(--bc-body)">
							<?php foreach ( briteclean_hours() as $days => $time ) : ?>
								<div>
									<dt><?php echo esc_html( $days ); ?></dt>
									<dd style="color:var(--bc-muted)"><?php echo esc_html( $time ); ?></dd>
								</div>
							<?php endforeach; ?>
						</dl>
					</div>
				</li>
			</ul>

			<div class="bc-btn-row" style="margin-top:28px">
				<a class="bc-btn bc-btn--whatsapp" href="<?php echo esc_url( briteclean_whatsapp_url() ); ?>" target="_blank" rel="noopener noreferrer">
					<?php echo briteclean_icon( 'whatsapp', array( 'size' => 18 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Hardcoded SVG. ?>
					<?php esc_html_e( 'Message on WhatsApp', 'briteclean' ); ?>
				</a>
				<a class="bc-btn bc-btn--primary" href="<?php echo esc_url( briteclean_page_url( 'book-now' ) ); ?>">
					<?php esc_html_e( 'Book a cleaning', 'briteclean' ); ?>
				</a>
			</div>
		</div>

		<div>
			<div class="bc-map">
				<?php
				// Google's keyless embed endpoint — no API key, no billing account, and
				// no third-party script. Lazy-loaded so it never blocks first paint.
				$map_src = 'https://www.google.com/maps?q=' . rawurlencode( $map_query ) . '&output=embed';
				?>
				<iframe
					src="<?php echo esc_url( $map_src ); ?>"
					title="<?php echo esc_attr( sprintf( /* translators: %s: address. */ __( 'Map showing %s', 'briteclean' ), $map_query ) ); ?>"
					loading="lazy"
					referrerpolicy="no-referrer-when-downgrade"
					allowfullscreen></iframe>
			</div>

			<div style="margin-top:32px">
				<h2><?php esc_html_e( 'Send a message', 'briteclean' ); ?></h2>
				<p style="color:var(--bc-muted)">
					<?php esc_html_e( 'For a full booking, use the Book Now form — it captures everything we need in one go.', 'briteclean' ); ?>
				</p>
				<?php echo do_shortcode( '[briteclean_contact_form]' ); ?>
			</div>
		</div>

	</div>
</section>

<?php
get_template_part( 'template-parts/service-area' );

get_footer();
