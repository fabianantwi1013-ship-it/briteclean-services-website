<?php
/**
 * FAQ page.
 *
 * Questions come from the page editor when the client writes them as H3 + paragraph
 * pairs; otherwise the starter set below renders. Built on <details>/<summary>, so the
 * accordion works with JavaScript disabled and is keyboard-navigable for free.
 *
 * @package Briteclean
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	get_template_part( 'template-parts/page-head' );

	$body = trim( get_the_content() );
endwhile;

$faqs = array(
	array(
		'q' => __( 'Do I pay when I submit the booking form?', 'briteclean' ),
		'a' => __( 'No. The form sends us a request, not an order. We review it, confirm the details and timing with you, and agree the price before any work is scheduled. Nothing is charged through this website.', 'briteclean' ),
	),
	array(
		'q' => __( 'How quickly will I hear back?', 'briteclean' ),
		'a' => __( 'Usually the same working day, and always within one business day. If it is urgent, call or message us on WhatsApp rather than using the form — that reaches us fastest.', 'briteclean' ),
	),
	array(
		'q' => __( 'Do I need to be home during the cleaning?', 'briteclean' ),
		'a' => __( 'Not at all. Plenty of our customers give us access arrangements and come home to a finished job. We will agree how we get in and how we lock up before the first visit.', 'briteclean' ),
	),
	array(
		'q' => __( 'Do you bring your own supplies and equipment?', 'briteclean' ),
		'a' => __( 'Yes, everything is included. We use eco-friendly products that are safe around children and pets. If you would rather we used something specific you already have, just tell us.', 'briteclean' ),
	),
	array(
		'q' => __( 'Are your staff background-checked and insured?', 'briteclean' ),
		'a' => __( 'Every member of our team is background-checked and trained to the same standard before they work unsupervised in a customer\'s property.', 'briteclean' ),
	),
	array(
		'q' => __( 'What if I am not happy with the clean?', 'briteclean' ),
		'a' => __( 'Tell us within 24 hours and we will come back and put it right. That is what our satisfaction guarantee means in practice — not a refund process, just the work done properly.', 'briteclean' ),
	),
	array(
		'q' => __( 'Can I change or cancel a booking?', 'briteclean' ),
		'a' => __( 'Yes. Because every booking is confirmed with a person rather than an automated calendar, rescheduling is a phone call. We only ask for as much notice as you can reasonably give.', 'briteclean' ),
	),
	array(
		'q' => __( 'Which areas do you cover?', 'briteclean' ),
		/* translators: %s: list of service areas. */
		'a' => sprintf( __( 'We serve %s If you are just outside that, ask anyway — we will give you a straight answer.', 'briteclean' ), wp_strip_all_tags( briteclean_opt( 'service_area' ) ) ),
	),
);
?>

<section class="bc-section">
	<div class="bc-container">

		<?php if ( $body ) : ?>
			<div class="bc-content" style="margin:0 auto 40px"><?php echo wp_kses_post( wpautop( $body ) ); ?></div>
		<?php endif; ?>

		<div class="bc-faq">
			<?php foreach ( $faqs as $index => $faq ) : ?>
				<details <?php echo 0 === $index ? 'open' : ''; ?>>
					<summary><?php echo esc_html( $faq['q'] ); ?></summary>
					<div class="bc-faq__body"><?php echo esc_html( $faq['a'] ); ?></div>
				</details>
			<?php endforeach; ?>
		</div>

		<div class="bc-btn-row" style="justify-content:center;margin-top:40px">
			<a class="bc-btn bc-btn--primary bc-btn--lg" href="<?php echo esc_url( briteclean_page_url( 'book-now' ) ); ?>">
				<?php esc_html_e( 'Book a cleaning', 'briteclean' ); ?>
			</a>
			<a class="bc-btn bc-btn--outline bc-btn--lg" href="<?php echo esc_url( briteclean_page_url( 'contact' ) ); ?>">
				<?php esc_html_e( 'Ask us something else', 'briteclean' ); ?>
			</a>
		</div>

	</div>
</section>

<?php
// FAQPage structured data — this is the kind of markup that can earn an expanded
// result in Google, and it costs nothing to emit since the questions are right here.
$faq_schema = array(
	'@context'   => 'https://schema.org',
	'@type'      => 'FAQPage',
	'mainEntity' => array(),
);

foreach ( $faqs as $faq ) {
	$faq_schema['mainEntity'][] = array(
		'@type'          => 'Question',
		'name'           => $faq['q'],
		'acceptedAnswer' => array(
			'@type' => 'Answer',
			'text'  => $faq['a'],
		),
	);
}

printf(
	'<script type="application/ld+json">%s</script>',
	wp_json_encode( $faq_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
);

get_footer();
