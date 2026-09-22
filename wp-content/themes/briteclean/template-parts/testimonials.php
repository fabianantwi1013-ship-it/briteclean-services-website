<?php
/**
 * Customer reviews.
 *
 * @package Briteclean
 */

defined( 'ABSPATH' ) || exit;

$testimonials = briteclean_testimonials( 3 );

if ( ! $testimonials ) {
	return;
}
?>

<section class="bc-section" id="reviews">
	<div class="bc-container">

		<div class="bc-section__head">
			<span class="bc-eyebrow"><?php esc_html_e( 'Reviews', 'briteclean' ); ?></span>
			<h2><?php esc_html_e( 'What Our Customers Say', 'briteclean' ); ?></h2>
			<p><?php esc_html_e( 'Real feedback from homes and businesses across the Tri-State area.', 'briteclean' ); ?></p>
		</div>

		<div class="bc-grid bc-grid--3">
			<?php foreach ( $testimonials as $testimonial ) : ?>
				<figure class="bc-quote">
					<span class="bc-quote__mark" aria-hidden="true">
						<?php echo briteclean_icon( 'quote', array( 'size' => 28 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Hardcoded SVG. ?>
					</span>

					<?php
					$rating = max( 0, min( 5, (int) $testimonial['rating'] ) );
					?>
					<div class="bc-quote__stars">
						<span aria-hidden="true"><?php echo esc_html( str_repeat( '★', $rating ) ); ?></span>
						<span class="screen-reader-text">
							<?php
							printf(
								/* translators: %d: star rating out of five. */
								esc_html__( 'Rated %d out of 5', 'briteclean' ),
								absint( $rating )
							);
							?>
						</span>
					</div>

					<blockquote><?php echo esc_html( $testimonial['quote'] ); ?></blockquote>

					<figcaption class="bc-quote__author">
						<span class="bc-quote__name"><?php echo esc_html( $testimonial['author'] ); ?></span>
						<?php if ( ! empty( $testimonial['location'] ) ) : ?>
							<span class="bc-quote__place"><?php echo esc_html( $testimonial['location'] ); ?></span>
						<?php endif; ?>
					</figcaption>
				</figure>
			<?php endforeach; ?>
		</div>

	</div>
</section>
