<?php
/**
 * "Why Choose Us" value propositions.
 *
 * @package Briteclean
 */

defined( 'ABSPATH' ) || exit;
?>

<section class="bc-section bc-panel--red" id="why-us">
	<div class="bc-container">

		<div class="bc-section__head">
			<span class="bc-eyebrow"><?php esc_html_e( 'The difference', 'briteclean' ); ?></span>
			<h2>
				<?php
				printf(
					/* translators: %s: business name. */
					esc_html__( 'Why Choose %s?', 'briteclean' ),
					esc_html( briteclean_opt( 'name' ) )
				);
				?>
			</h2>
		</div>

		<div class="bc-why">
			<?php foreach ( briteclean_value_props() as $prop ) : ?>
				<div class="bc-why__item">
					<span class="bc-medallion bc-medallion--gold" aria-hidden="true">
						<?php echo briteclean_icon( $prop['icon'], array( 'size' => 24 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Hardcoded SVG. ?>
					</span>
					<div>
						<h3><?php echo esc_html( $prop['title'] ); ?></h3>
						<p><?php echo esc_html( $prop['text'] ); ?></p>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

	</div>
</section>
