<?php
/**
 * Services page — the expanded list of all eight services.
 *
 * @package Briteclean
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	get_template_part( 'template-parts/page-head' );
endwhile;

$services = briteclean_services();
?>

<section class="bc-section">
	<div class="bc-container">

		<div class="bc-section__head">
			<span class="bc-eyebrow"><?php esc_html_e( 'Full service list', 'briteclean' ); ?></span>
			<h2><?php esc_html_e( 'Everything We Clean', 'briteclean' ); ?></h2>
			<p><?php esc_html_e( 'Eight services covering homes, workplaces and everything in between. Not sure which you need? Ask us and we will scope it with you.', 'briteclean' ); ?></p>
		</div>

		<?php
		// Quick-jump pills, mirroring the flyer's rounded service list.
		?>
		<div class="bc-btn-row" style="justify-content:center;margin-bottom:44px">
			<?php foreach ( $services as $service ) : ?>
				<a class="bc-pill" href="#<?php echo esc_attr( $service['slug'] ); ?>">
					<span class="bc-pill__dot" aria-hidden="true">
						<?php echo briteclean_icon( 'check', array( 'size' => 14 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Hardcoded SVG. ?>
					</span>
					<?php echo esc_html( $service['name'] ); ?>
				</a>
			<?php endforeach; ?>
		</div>

		<?php foreach ( $services as $service ) : ?>
			<article class="bc-service-detail" id="<?php echo esc_attr( $service['slug'] ); ?>">

				<div class="bc-service-detail__head">
					<span class="bc-medallion bc-medallion--lg" aria-hidden="true">
						<?php echo briteclean_icon( $service['icon'], array( 'size' => 32 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Hardcoded SVG. ?>
					</span>

					<h2><?php echo esc_html( $service['name'] ); ?></h2>

					<a class="bc-btn bc-btn--primary bc-btn--sm"
						href="<?php echo esc_url( add_query_arg( 'service', rawurlencode( $service['slug'] ), briteclean_page_url( 'book-now' ) ) ); ?>">
						<?php esc_html_e( 'Book this', 'briteclean' ); ?>
						<?php echo briteclean_icon( 'arrow', array( 'size' => 15 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Hardcoded SVG. ?>
					</a>
				</div>

				<div>
					<?php if ( ! empty( $service['short'] ) ) : ?>
						<p class="bc-lead" style="color:var(--bc-ink);font-weight:600">
							<?php echo esc_html( wp_strip_all_tags( $service['short'] ) ); ?>
						</p>
					<?php endif; ?>

					<?php if ( ! empty( $service['long'] ) ) : ?>
						<div class="bc-content"><?php echo wp_kses_post( wpautop( $service['long'] ) ); ?></div>
					<?php endif; ?>
				</div>

			</article>
		<?php endforeach; ?>

	</div>
</section>

<?php
get_template_part( 'template-parts/why-choose-us' );
get_template_part( 'template-parts/cta-band' );

get_footer();
