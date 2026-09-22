<?php
/**
 * Homepage hero.
 *
 * @package Briteclean
 */

defined( 'ABSPATH' ) || exit;

$hero     = briteclean_hero();
$hero_img = (int) briteclean_field( 'hero_image', 0 );
$phone    = briteclean_opt( 'phone_primary' );
?>

<section class="bc-hero">
	<div class="bc-container bc-hero__grid">

		<div class="bc-hero__copy">
			<?php if ( $hero['badge'] ) : ?>
				<span class="bc-badge bc-hero__badge">
					<?php echo briteclean_icon( 'sparkle', array( 'size' => 16 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Hardcoded SVG. ?>
					<?php echo esc_html( $hero['badge'] ); ?>
				</span>
			<?php endif; ?>

			<h1><?php echo esc_html( $hero['headline'] ); ?></h1>

			<p class="bc-hero__sub"><?php echo esc_html( $hero['subheadline'] ); ?></p>

			<div class="bc-btn-row">
				<a class="bc-btn bc-btn--gold bc-btn--lg" href="<?php echo esc_url( briteclean_page_url( 'book-now' ) ); ?>">
					<?php echo esc_html( $hero['cta_primary'] ); ?>
					<?php echo briteclean_icon( 'arrow', array( 'size' => 18 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Hardcoded SVG. ?>
				</a>

				<a class="bc-btn bc-btn--ghost-light bc-btn--lg" href="tel:+<?php echo esc_attr( briteclean_phone_digits( $phone ) ); ?>">
					<?php echo briteclean_icon( 'phone', array( 'size' => 18 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Hardcoded SVG. ?>
					<?php echo esc_html( $hero['cta_secondary'] ); ?>
				</a>
			</div>

			<p class="bc-hero__tagline"><?php echo esc_html( briteclean_opt( 'tagline' ) ); ?></p>
		</div>

		<div class="bc-hero__media">
			<?php
			// Not lazy-loaded and given high fetch priority: this is the LCP element.
			echo briteclean_image_or_placeholder( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped inside the helper.
				$hero_img,
				'briteclean-hero',
				__( 'Hero photo — a cleaner in gloves wiping a bright modern kitchen counter', 'briteclean' ),
				array(
					'loading'       => 'eager',
					'fetchpriority' => 'high',
					'decoding'      => 'sync',
				)
			);
			?>

			<span class="bc-hero__float">
				<span class="bc-medallion bc-medallion--sm bc-medallion--green" aria-hidden="true">
					<?php echo briteclean_icon( 'leaf', array( 'size' => 18 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Hardcoded SVG. ?>
				</span>
				<?php esc_html_e( 'Eco-friendly products', 'briteclean' ); ?>
			</span>
		</div>

	</div>
</section>
