<?php
/**
 * Homepage.
 *
 * Composed entirely from template parts so each section can be reordered or reused on
 * other pages without touching this file.
 *
 * @package Briteclean
 */

defined( 'ABSPATH' ) || exit;

get_header();

get_template_part( 'template-parts/hero' );
get_template_part( 'template-parts/trust-badges' );
get_template_part( 'template-parts/services-grid' );
get_template_part( 'template-parts/why-choose-us' );

// Any content the client writes into the Home page in the editor renders here,
// between the fixed sections.
if ( have_posts() ) :
	while ( have_posts() ) :
		the_post();

		$body = trim( get_the_content() );

		if ( $body ) :
			?>
			<section class="bc-section">
				<div class="bc-container">
					<div class="bc-content" style="margin-inline:auto">
						<?php the_content(); ?>
					</div>
				</div>
			</section>
			<?php
		endif;
	endwhile;
endif;

get_template_part( 'template-parts/testimonials' );
get_template_part( 'template-parts/service-area' );

// The booking form doubles as a homepage section, exactly as it appears on /book-now/.
if ( shortcode_exists( 'briteclean_booking_form' ) ) :
	?>
	<section class="bc-section bc-section--soft" id="book">
		<div class="bc-container">
			<div class="bc-section__head">
				<span class="bc-eyebrow"><?php esc_html_e( 'Book a cleaning', 'briteclean' ); ?></span>
				<h2><?php esc_html_e( 'Request Your Cleaning in Five Steps', 'briteclean' ); ?></h2>
				<p><?php esc_html_e( 'Tell us what you need. No payment now — we confirm every booking with you personally first.', 'briteclean' ); ?></p>
			</div>

			<?php echo do_shortcode( '[briteclean_booking_form context="home"]' ); ?>
		</div>
	</section>
	<?php
endif;

get_template_part( 'template-parts/cta-band' );

get_footer();
