<?php
/**
 * Default page template.
 *
 * @package Briteclean
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	get_template_part( 'template-parts/page-head' );
	?>

	<article class="bc-section">
		<div class="bc-container">
			<div class="bc-content" style="margin-inline:auto">
				<?php
				if ( has_post_thumbnail() ) {
					the_post_thumbnail( 'briteclean-hero', array( 'loading' => 'lazy', 'decoding' => 'async' ) );
				}

				the_content();

				wp_link_pages(
					array(
						'before' => '<nav class="bc-page-links">',
						'after'  => '</nav>',
					)
				);
				?>
			</div>
		</div>
	</article>

	<?php
	get_template_part( 'template-parts/cta-band' );

endwhile;

get_footer();
