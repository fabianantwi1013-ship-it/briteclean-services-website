<?php
/**
 * Site footer, plus the site-wide floating WhatsApp button.
 *
 * @package Briteclean
 */

defined( 'ABSPATH' ) || exit;

$phone_primary   = briteclean_opt( 'phone_primary' );
$phone_secondary = briteclean_opt( 'phone_secondary' );
$email           = briteclean_opt( 'email' );
$socials         = briteclean_social_links();

$social_icons = array(
	'facebook'  => 'check',
	'instagram' => 'star',
	'google'    => 'pin',
	'yelp'      => 'heart',
);

$social_labels = array(
	'facebook'  => __( 'Facebook', 'briteclean' ),
	'instagram' => __( 'Instagram', 'briteclean' ),
	'google'    => __( 'Google Business Profile', 'briteclean' ),
	'yelp'      => __( 'Yelp', 'briteclean' ),
);
?>

</main><!-- #bc-main -->

<?php get_template_part( 'template-parts/trust-badges' ); ?>

<footer class="bc-footer">
	<div class="bc-container">
		<div class="bc-footer__grid">

			<div>
				<h3 class="bc-footer__heading"><?php echo esc_html( briteclean_opt( 'name' ) ); ?></h3>
				<p><?php echo esc_html( briteclean_hero()['subheadline'] ); ?></p>
				<p class="bc-footer__tagline"><?php echo esc_html( briteclean_opt( 'tagline' ) ); ?></p>

				<?php if ( $socials ) : ?>
					<div class="bc-social">
						<?php foreach ( $socials as $network => $url ) : ?>
							<a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer">
								<span class="screen-reader-text"><?php echo esc_html( $social_labels[ $network ] ); ?></span>
								<?php echo briteclean_icon( $social_icons[ $network ], array( 'size' => 18 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Hardcoded SVG. ?>
							</a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>

			<div>
				<h3 class="bc-footer__heading"><?php esc_html_e( 'Get in touch', 'briteclean' ); ?></h3>
				<ul>
					<li>
						<a href="tel:+<?php echo esc_attr( briteclean_phone_digits( $phone_primary ) ); ?>"><?php echo esc_html( $phone_primary ); ?></a>
					</li>
					<?php if ( $phone_secondary ) : ?>
						<li>
							<a href="tel:+<?php echo esc_attr( briteclean_phone_digits( $phone_secondary ) ); ?>"><?php echo esc_html( $phone_secondary ); ?></a>
						</li>
					<?php endif; ?>
					<?php if ( $email ) : ?>
						<li><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></li>
					<?php endif; ?>
					<li>
						<address style="font-style:normal">
							<?php echo esc_html( briteclean_opt( 'street' ) ); ?><br />
							<?php echo esc_html( briteclean_opt( 'city' ) . ', ' . briteclean_opt( 'state' ) . ' ' . briteclean_opt( 'postal' ) ); ?>
						</address>
					</li>
				</ul>
			</div>

			<div>
				<h3 class="bc-footer__heading"><?php esc_html_e( 'Explore', 'briteclean' ); ?></h3>
				<?php
				if ( has_nav_menu( 'footer' ) ) {
					wp_nav_menu(
						array(
							'theme_location' => 'footer',
							'container'      => false,
							'depth'          => 1,
							'fallback_cb'    => false,
						)
					);
				} else {
					echo '<ul>';
					foreach ( array( 'services', 'about', 'faq', 'contact', 'book-now' ) as $slug ) {
						printf(
							'<li><a href="%s">%s</a></li>',
							esc_url( briteclean_page_url( $slug ) ),
							esc_html( ucwords( str_replace( '-', ' ', $slug ) ) )
						);
					}
					echo '</ul>';
				}
				?>
			</div>

			<div>
				<h3 class="bc-footer__heading"><?php esc_html_e( 'Opening hours', 'briteclean' ); ?></h3>
				<dl class="bc-footer__hours">
					<?php foreach ( briteclean_hours() as $days => $time ) : ?>
						<div>
							<dt><?php echo esc_html( $days ); ?></dt>
							<dd><?php echo esc_html( $time ); ?></dd>
						</div>
					<?php endforeach; ?>
				</dl>

				<?php if ( is_active_sidebar( 'footer-1' ) ) : ?>
					<div style="margin-top:20px"><?php dynamic_sidebar( 'footer-1' ); ?></div>
				<?php endif; ?>
			</div>

		</div>

		<div class="bc-footer__bottom">
			<p>
				<?php
				printf(
					/* translators: 1: year, 2: business name. */
					esc_html__( '© %1$s %2$s. All rights reserved.', 'briteclean' ),
					esc_html( gmdate( 'Y' ) ),
					esc_html( briteclean_opt( 'name' ) )
				);
				?>
			</p>
			<p><?php echo esc_html( briteclean_opt( 'tagline_secondary' ) ); ?></p>
		</div>
	</div>
</footer>

<?php get_template_part( 'template-parts/whatsapp-float' ); ?>

<?php wp_footer(); ?>
</body>
</html>
