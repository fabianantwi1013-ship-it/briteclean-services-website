<?php
/**
 * Site header.
 *
 * @package Briteclean
 */

defined( 'ABSPATH' ) || exit;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<link rel="profile" href="https://gmpg.org/xfn/11" />
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="bc-skip-link" href="#bc-main"><?php esc_html_e( 'Skip to content', 'briteclean' ); ?></a>

<?php
$phone_primary = briteclean_opt( 'phone_primary' );
$tagline       = briteclean_opt( 'tagline' );
?>

<div class="bc-topbar">
	<div class="bc-container bc-topbar__inner">
		<a href="tel:+<?php echo esc_attr( briteclean_phone_digits( $phone_primary ) ); ?>">
			<?php echo briteclean_icon( 'phone', array( 'size' => 15 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Hardcoded SVG. ?>
			<span><?php echo esc_html( $phone_primary ); ?></span>
		</a>

		<span class="bc-topbar__tagline"><?php echo esc_html( $tagline ); ?></span>

		<a href="<?php echo esc_url( briteclean_page_url( 'contact' ) ); ?>">
			<?php echo briteclean_icon( 'pin', array( 'size' => 15 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Hardcoded SVG. ?>
			<span><?php echo esc_html( briteclean_opt( 'city' ) . ', ' . briteclean_opt( 'state' ) ); ?></span>
		</a>
	</div>
</div>

<header class="bc-header">
	<div class="bc-container bc-header__inner">

		<?php if ( has_custom_logo() ) : ?>
			<div class="bc-brand bc-brand__logo"><?php the_custom_logo(); ?></div>
		<?php else : ?>
			<a class="bc-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
				<span class="bc-brand__mark" aria-hidden="true">
					<?php echo briteclean_icon( 'sparkle', array( 'size' => 22 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Hardcoded SVG. ?>
				</span>
				<span class="bc-brand__text">
					<span class="bc-brand__name"><?php echo esc_html( briteclean_opt( 'name' ) ); ?></span>
					<span class="bc-brand__tag"><?php echo esc_html( briteclean_opt( 'tagline_secondary' ) ); ?></span>
				</span>
			</a>
		<?php endif; ?>

		<button class="bc-nav-toggle" type="button" aria-expanded="false" aria-controls="bc-primary-nav">
			<span class="bc-nav-toggle__bars" aria-hidden="true"><span></span><span></span><span></span></span>
			<span><?php esc_html_e( 'Menu', 'briteclean' ); ?></span>
		</button>

		<nav class="bc-nav" id="bc-primary-nav" aria-label="<?php esc_attr_e( 'Primary navigation', 'briteclean' ); ?>">
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'container'      => false,
						'depth'          => 2,
						'fallback_cb'    => false,
					)
				);
			} else {
				// Before the seeder has run there is no menu, so fall back to the core
				// page list rather than showing an empty header.
				wp_list_pages(
					array(
						'title_li' => '',
						'depth'    => 1,
					)
				);
			}
			?>
		</nav>

		<a class="bc-btn bc-btn--primary bc-btn--sm bc-header__cta" href="<?php echo esc_url( briteclean_page_url( 'book-now' ) ); ?>">
			<?php esc_html_e( 'Book Now', 'briteclean' ); ?>
		</a>

	</div>
</header>

<main id="bc-main">
