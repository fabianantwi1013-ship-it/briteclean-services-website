<?php
/**
 * Persistent floating WhatsApp button.
 *
 * The fast path for customers who would rather message than fill in a form. Opens
 * WhatsApp with the message already typed.
 *
 * @package Briteclean
 */

defined( 'ABSPATH' ) || exit;

if ( ! get_theme_mod( 'briteclean_float_enabled', true ) ) {
	return;
}

$label = get_theme_mod( 'briteclean_float_label', __( 'Book via WhatsApp', 'briteclean' ) );
?>

<a class="bc-whatsapp-float"
	href="<?php echo esc_url( briteclean_whatsapp_url() ); ?>"
	target="_blank"
	rel="noopener noreferrer">
	<?php echo briteclean_icon( 'whatsapp', array( 'size' => 24 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Hardcoded SVG. ?>
	<span class="bc-whatsapp-float__label"><?php echo esc_html( $label ); ?></span>
	<span class="screen-reader-text">
		<?php
		printf(
			/* translators: %s: button label. */
			esc_html__( '%s — opens WhatsApp in a new tab', 'briteclean' ),
			esc_html( $label )
		);
		?>
	</span>
</a>
