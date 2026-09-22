<?php
/**
 * Trust badge strip.
 *
 * Rendered above the footer on every page, and again under the hero on the homepage.
 *
 * @package Briteclean
 */

defined( 'ABSPATH' ) || exit;
?>

<section class="bc-trust">
	<div class="bc-container bc-trust__inner">
		<?php foreach ( briteclean_trust_badges() as $badge ) : ?>
			<div class="bc-trust__item">
				<span class="bc-trust__icon" aria-hidden="true">
					<?php echo briteclean_icon( $badge['icon'], array( 'size' => 18 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Hardcoded SVG. ?>
				</span>
				<span><?php echo esc_html( $badge['title'] ); ?></span>
			</div>
		<?php endforeach; ?>
	</div>
</section>
