<?php
/**
 * Closing call-to-action band.
 *
 * @package Briteclean
 */

defined( 'ABSPATH' ) || exit;

$phone = briteclean_opt( 'phone_primary' );
?>

<section class="bc-section bc-panel--red bc-cta-band">
	<div class="bc-container">
		<span class="bc-eyebrow"><?php echo esc_html( briteclean_opt( 'tagline_secondary' ) ); ?></span>
		<h2><?php esc_html_e( 'Ready for a Sparkling Clean Space?', 'briteclean' ); ?></h2>
		<p><?php esc_html_e( 'Tell us what you need and when you need it. We will confirm your booking personally — usually the same day.', 'briteclean' ); ?></p>

		<div class="bc-btn-row">
			<a class="bc-btn bc-btn--gold bc-btn--lg" href="<?php echo esc_url( briteclean_page_url( 'book-now' ) ); ?>">
				<?php esc_html_e( 'Get a Free Quote', 'briteclean' ); ?>
				<?php echo briteclean_icon( 'arrow', array( 'size' => 18 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Hardcoded SVG. ?>
			</a>

			<a class="bc-btn bc-btn--ghost-light bc-btn--lg" href="tel:+<?php echo esc_attr( briteclean_phone_digits( $phone ) ); ?>">
				<?php echo briteclean_icon( 'phone', array( 'size' => 18 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Hardcoded SVG. ?>
				<?php echo esc_html( $phone ); ?>
			</a>
		</div>
	</div>
</section>
