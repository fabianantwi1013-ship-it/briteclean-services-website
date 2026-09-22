<?php
/**
 * Service area note.
 *
 * @package Briteclean
 */

defined( 'ABSPATH' ) || exit;
?>

<section class="bc-section bc-section--tight bc-section--tint">
	<div class="bc-container">
		<div class="bc-area">
			<span class="bc-medallion bc-medallion--lg" aria-hidden="true">
				<?php echo briteclean_icon( 'pin', array( 'size' => 30 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Hardcoded SVG. ?>
			</span>

			<div>
				<h3 style="margin-bottom:.3em"><?php esc_html_e( 'Areas We Serve', 'briteclean' ); ?></h3>
				<p><?php echo wp_kses_post( briteclean_opt( 'service_area' ) ); ?></p>
				<p style="margin-top:.6em;font-size:.92rem;color:var(--bc-muted)">
					<?php esc_html_e( 'Not sure if we reach you? Call or message us — we will tell you straight away.', 'briteclean' ); ?>
				</p>
			</div>

			<a class="bc-btn bc-btn--outline" href="<?php echo esc_url( briteclean_page_url( 'contact' ) ); ?>">
				<?php esc_html_e( 'Contact Us', 'briteclean' ); ?>
			</a>
		</div>
	</div>
</section>
