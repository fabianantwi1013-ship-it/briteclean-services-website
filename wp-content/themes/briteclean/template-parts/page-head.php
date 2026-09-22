<?php
/**
 * Red page-title band for interior pages.
 *
 * @package Briteclean
 */

defined( 'ABSPATH' ) || exit;

$subtitle = briteclean_field( 'page_subtitle', '' );
?>

<section class="bc-panel--red bc-page-head">
	<div class="bc-container">
		<nav class="bc-breadcrumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'briteclean' ); ?>">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'briteclean' ); ?></a>
			<span aria-hidden="true"> / </span>
			<span><?php echo esc_html( get_the_title() ); ?></span>
		</nav>

		<h1><?php echo esc_html( get_the_title() ); ?></h1>

		<?php if ( $subtitle ) : ?>
			<p class="bc-lead"><?php echo esc_html( $subtitle ); ?></p>
		<?php endif; ?>
	</div>
</section>
