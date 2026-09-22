<?php
/**
 * Services grid.
 *
 * @package Briteclean
 */

defined( 'ABSPATH' ) || exit;

$services = briteclean_services();
?>

<section class="bc-section bc-section--soft" id="services">
	<div class="bc-container">

		<div class="bc-section__head">
			<span class="bc-eyebrow"><?php esc_html_e( 'What we do', 'briteclean' ); ?></span>
			<h2><?php esc_html_e( 'Our Cleaning Services', 'briteclean' ); ?></h2>
			<p><?php esc_html_e( 'Homes, offices and commercial spaces — booked around your schedule, cleaned to a standard you can see.', 'briteclean' ); ?></p>
		</div>

		<div class="bc-grid bc-grid--4">
			<?php foreach ( $services as $service ) : ?>
				<a class="bc-service-card <?php echo empty( $service['featured'] ) ? '' : 'bc-service-card--featured'; ?>"
					href="<?php echo esc_url( $service['url'] ); ?>">

					<span class="bc-medallion" aria-hidden="true">
						<?php echo briteclean_icon( $service['icon'], array( 'size' => 26 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Hardcoded SVG. ?>
					</span>

					<h3><?php echo esc_html( $service['name'] ); ?></h3>

					<?php if ( ! empty( $service['short'] ) ) : ?>
						<p><?php echo esc_html( wp_strip_all_tags( $service['short'] ) ); ?></p>
					<?php endif; ?>

					<span class="bc-service-card__more">
						<?php esc_html_e( 'Learn more', 'briteclean' ); ?>
						<?php echo briteclean_icon( 'arrow', array( 'size' => 15 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Hardcoded SVG. ?>
					</span>
				</a>
			<?php endforeach; ?>
		</div>

		<div class="bc-btn-row" style="justify-content:center;margin-top:36px">
			<a class="bc-btn bc-btn--primary bc-btn--lg" href="<?php echo esc_url( briteclean_page_url( 'book-now' ) ); ?>">
				<?php esc_html_e( 'Book a Cleaning', 'briteclean' ); ?>
				<?php echo briteclean_icon( 'arrow', array( 'size' => 18 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Hardcoded SVG. ?>
			</a>
		</div>

	</div>
</section>
