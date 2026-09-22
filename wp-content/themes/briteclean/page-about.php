<?php
/**
 * About page.
 *
 * The company story is placeholder copy — clearly flagged in the admin so it is not
 * mistaken for finished content — with the trust and quality messaging around it built
 * from the same value props used on the homepage.
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
?>

<section class="bc-section">
	<div class="bc-container bc-contact-grid">

		<div>
			<?php
			echo briteclean_image_or_placeholder( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped inside the helper.
				(int) get_post_thumbnail_id(),
				'briteclean-hero',
				__( 'About photo — the Briteclean team in branded aprons, smiling in a tidy modern living room', 'briteclean' )
			);
			?>
		</div>

		<div>
			<span class="bc-eyebrow"><?php esc_html_e( 'Our story', 'briteclean' ); ?></span>

			<?php if ( $body ) : ?>
				<div class="bc-content"><?php echo wp_kses_post( wpautop( $body ) ); ?></div>
			<?php else : ?>
				<div class="bc-content">
					<h2><?php esc_html_e( 'A local cleaning company built on showing up', 'briteclean' ); ?></h2>

					<p>
						<?php
						printf(
							/* translators: 1: business name, 2: city, 3: state. */
							esc_html__( '%1$s is a cleaning company serving %2$s, %3$s and the surrounding communities. We started with a simple observation: most people do not want to manage a cleaning service. They want to agree a time, come home to a clean space, and not think about it again.', 'briteclean' ),
							esc_html( briteclean_opt( 'name' ) ),
							esc_html( briteclean_opt( 'city' ) ),
							esc_html( briteclean_opt( 'state' ) )
						);
						?>
					</p>

					<p><?php esc_html_e( 'So that is what we built. Background-checked staff who are trained the same way and turn up when they said they would. Eco-friendly products that are safe around children and pets. A written scope for every job, so nobody is guessing what "clean" means. And a real person on the other end of the phone when something needs changing.', 'briteclean' ); ?></p>

					<p><?php esc_html_e( 'We are not the biggest cleaning company in the area, and we are not trying to be. We would rather keep the customers we have by doing the work properly than grow past the point where we can.', 'briteclean' ); ?></p>

					<p style="padding:14px 18px;background:var(--bc-bg-tint);border-radius:var(--bc-radius-sm);font-size:.9rem;color:var(--bc-muted)">
						<strong><?php esc_html_e( 'Placeholder copy.', 'briteclean' ); ?></strong>
						<?php esc_html_e( 'Replace this with the real company story by editing the About page in wp-admin. Anything typed into the editor replaces this text entirely.', 'briteclean' ); ?>
					</p>
				</div>
			<?php endif; ?>

			<p class="bc-gold-text" style="color:var(--bc-red);margin-top:1em">
				<?php echo esc_html( briteclean_opt( 'tagline' ) ); ?>
			</p>
		</div>

	</div>
</section>

<?php
get_template_part( 'template-parts/why-choose-us' );
get_template_part( 'template-parts/testimonials' );
get_template_part( 'template-parts/cta-band' );

get_footer();
