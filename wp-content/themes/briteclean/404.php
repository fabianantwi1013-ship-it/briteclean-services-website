<?php
/**
 * 404 template.
 *
 * @package Briteclean
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<section class="bc-panel--red bc-page-head">
	<div class="bc-container">
		<h1><?php esc_html_e( 'Page Not Found', 'briteclean' ); ?></h1>
		<p class="bc-lead"><?php esc_html_e( 'That page has moved or never existed. Here is the way back.', 'briteclean' ); ?></p>
	</div>
</section>

<section class="bc-section">
	<div class="bc-container" style="text-align:center">
		<div class="bc-btn-row" style="justify-content:center">
			<a class="bc-btn bc-btn--primary bc-btn--lg" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<?php esc_html_e( 'Go to homepage', 'briteclean' ); ?>
			</a>
			<a class="bc-btn bc-btn--outline bc-btn--lg" href="<?php echo esc_url( briteclean_page_url( 'book-now' ) ); ?>">
				<?php esc_html_e( 'Book a cleaning', 'briteclean' ); ?>
			</a>
		</div>
	</div>
</section>

<?php
get_template_part( 'template-parts/services-grid' );

get_footer();
