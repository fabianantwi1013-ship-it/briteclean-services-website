<?php
/**
 * Fallback template — blog index, archives and search results.
 *
 * @package Briteclean
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<section class="bc-panel--red bc-page-head">
	<div class="bc-container">
		<h1>
			<?php
			if ( is_search() ) {
				printf(
					/* translators: %s: search query. */
					esc_html__( 'Search results for "%s"', 'briteclean' ),
					esc_html( get_search_query() )
				);
			} elseif ( is_archive() ) {
				the_archive_title();
			} else {
				esc_html_e( 'News & Cleaning Tips', 'briteclean' );
			}
			?>
		</h1>
	</div>
</section>

<section class="bc-section">
	<div class="bc-container">
		<?php if ( have_posts() ) : ?>

			<div class="bc-grid bc-grid--3">
				<?php
				while ( have_posts() ) :
					the_post();
					?>
					<article <?php post_class( 'bc-service-card' ); ?>>
						<?php if ( has_post_thumbnail() ) : ?>
							<a href="<?php the_permalink(); ?>">
								<?php the_post_thumbnail( 'briteclean-card', array( 'loading' => 'lazy', 'decoding' => 'async' ) ); ?>
							</a>
						<?php endif; ?>

						<h3><a href="<?php the_permalink(); ?>" style="text-decoration:none;color:inherit"><?php the_title(); ?></a></h3>
						<p><?php echo esc_html( get_the_excerpt() ); ?></p>

						<span class="bc-service-card__more">
							<?php esc_html_e( 'Read more', 'briteclean' ); ?>
							<?php echo briteclean_icon( 'arrow', array( 'size' => 15 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Hardcoded SVG. ?>
						</span>
					</article>
					<?php
				endwhile;
				?>
			</div>

			<?php
			the_posts_pagination(
				array(
					'mid_size'  => 2,
					'prev_text' => __( 'Previous', 'briteclean' ),
					'next_text' => __( 'Next', 'briteclean' ),
				)
			);
			?>

		<?php else : ?>
			<div class="bc-content" style="margin-inline:auto;text-align:center">
				<p><?php esc_html_e( 'Nothing found here yet.', 'briteclean' ); ?></p>
				<a class="bc-btn bc-btn--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<?php esc_html_e( 'Back to home', 'briteclean' ); ?>
				</a>
			</div>
		<?php endif; ?>
	</div>
</section>

<?php
get_footer();
