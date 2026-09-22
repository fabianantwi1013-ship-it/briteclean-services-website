<?php
/**
 * Canonical business content for Briteclean Services LLC.
 *
 * Everything the site displays falls back to these values when ACF, WooCommerce or
 * the Customizer have not been configured yet. That means a fresh activation renders
 * a complete, correct site with zero setup — the client's edits layer on top.
 *
 * @package Briteclean
 */

defined( 'ABSPATH' ) || exit;

/**
 * Business identity, contact details and hours.
 *
 * @return array
 */
function briteclean_business() {
	return array(
		'name'              => 'Briteclean Services LLC',
		'tagline'           => 'Clean Spaces, Healthy Lives',
		'tagline_secondary' => 'Your Clean Space, Our Priority',
		'phone_primary'     => '+1 (484) 347-9523',
		'phone_secondary'   => '+1 (513) 293-2971',
		'street'            => '9540 Woodland Hills Drive',
		'city'              => 'West Chester',
		'state'             => 'Ohio',
		'state_short'       => 'OH',
		'postal'            => '45011',
		'country'           => 'US',
		'email'             => 'info@britecleanservices.com',
		'hours'             => array(
			'Monday – Friday' => '8:00 AM – 6:00 PM',
			'Saturday'        => '9:00 AM – 4:00 PM',
			'Sunday'          => 'By appointment',
		),
		'service_area'      => 'West Chester, Liberty Township, Mason, Fairfield, Hamilton, Sharonville and surrounding Butler & Warren County communities.',
	);
}

/**
 * Homepage hero copy.
 *
 * @return array
 */
function briteclean_hero_defaults() {
	return array(
		'badge'       => 'Sparkling Results Every Time',
		'headline'    => 'Professional Cleaning Services for Your Space',
		'subheadline' => 'We make your home or business cleaner, fresher and healthier.',
		'cta_primary' => 'Get a Free Quote',
		'cta_secondary' => 'Call Now',
	);
}

/**
 * The eight cleaning services.
 *
 * Used to seed WooCommerce products, and as the display fallback when WooCommerce is
 * inactive. `slug` doubles as the product slug and the booking form's stored value —
 * changing one after launch orphans existing booking records, so treat them as stable.
 *
 * @return array
 */
function briteclean_services_defaults() {
	return array(
		array(
			'slug'  => 'residential-cleaning',
			'name'  => 'Residential Cleaning',
			'icon'  => 'home',
			'short' => 'Regular upkeep for houses and apartments, on a schedule that fits your week.',
			'long'  => 'Routine cleaning for the spaces you actually live in. We cover kitchens, bathrooms, bedrooms and living areas — dusting, vacuuming, mopping, surface sanitising and bin changes — on a weekly, fortnightly or monthly rhythm. Same team each visit wherever we can manage it, so nobody has to re-explain how you like things done.',
		),
		array(
			'slug'  => 'office-cleaning',
			'name'  => 'Office Cleaning',
			'icon'  => 'briefcase',
			'short' => 'Desks, common areas and washrooms kept presentable for staff and visitors.',
			'long'  => 'Scheduled cleaning for workplaces, timed around your business hours so nobody is vacuuming past a meeting. Workstations, meeting rooms, kitchens, washrooms and entryways, with waste and recycling handled. Evening and early-morning slots available.',
		),
		array(
			'slug'  => 'deep-cleaning',
			'name'  => 'Deep Cleaning',
			'icon'  => 'sparkle',
			'short' => 'A top-to-bottom reset that reaches what routine cleaning skips.',
			'long'  => 'The full detail pass: inside appliances, behind and under furniture, skirting boards, door frames, light fittings, tile grout, and built-up limescale in bathrooms and kitchens. Worth booking seasonally, before hosting, or as a first visit to bring a property up to a standard that routine cleaning can then hold.',
		),
		array(
			'slug'  => 'commercial-cleaning',
			'name'  => 'Commercial Cleaning',
			'icon'  => 'building',
			'short' => 'Retail units, clinics and shared facilities, cleaned to a consistent standard.',
			'long'  => 'Larger premises and higher-traffic environments — retail floors, waiting rooms, clinics, gyms, lobbies and shared facilities. We agree a written scope and checklist so standards stay consistent across visits and across staff, and we work to whatever compliance requirements your sector expects.',
		),
		array(
			'slug'  => 'window-cleaning',
			'name'  => 'Window Cleaning',
			'icon'  => 'window',
			'short' => 'Streak-free glass inside and out, frames and sills included.',
			'long'  => 'Interior and accessible exterior glass cleaned streak-free, with frames, sills and tracks wiped down rather than left behind. Available as a standalone visit or folded into a deep clean. Tell us about upper floors when you enquire so we bring the right access equipment.',
		),
		array(
			'slug'  => 'move-in-cleaning',
			'name'  => 'Move-In Cleaning',
			'icon'  => 'box',
			'short' => 'An empty property made genuinely ready for the first night.',
			'long'  => 'For the gap between keys and furniture. Every room cleaned while it is empty and easy to reach — cupboards and drawers inside and out, appliances, bathrooms, floors and windows. Also available as a move-out clean when you need a property handed back in good condition.',
		),
		array(
			'slug'  => 'general-cleaning',
			'name'  => 'General Cleaning',
			'icon'  => 'broom',
			'short' => 'A flexible one-off visit, scoped to whatever you need most.',
			'long'  => 'Not everything fits a category. Tell us what needs doing — after a party, ahead of guests, a single room that has got away from you — and we will scope a one-off visit around it rather than selling you a package you do not need.',
		),
		array(
			'slug'  => 'emergency-cleaning',
			'name'  => 'Emergency Cleaning',
			'icon'  => 'bolt',
			'short' => 'Short-notice response when something cannot wait until next week.',
			'long'  => 'Short-notice cleaning for the situations you did not plan for: a spill before an inspection, a last-minute viewing, a post-event turnaround. Call rather than use the form when it is genuinely urgent — we will tell you honestly what we can reach and when.',
		),
	);
}

/**
 * "Why Choose Briteclean Services LLC?" value propositions.
 *
 * @return array
 */
function briteclean_value_props_defaults() {
	return array(
		array(
			'icon'  => 'check',
			'title' => 'Quality Cleaning',
			'text'  => "We pay attention to the details so you don't have to.",
		),
		array(
			'icon'  => 'calendar',
			'title' => 'Flexible Scheduling',
			'text'  => 'We work around your time, not the other way around.',
		),
		array(
			'icon'  => 'shield',
			'title' => 'Trained & Trusted Staff',
			'text'  => 'Professional, background-checked and courteous.',
		),
		array(
			'icon'  => 'leaf',
			'title' => 'Eco-Friendly Products',
			'text'  => 'Safe for your family, pets and the environment.',
		),
		array(
			'icon'  => 'heart',
			'title' => '100% Satisfaction',
			'text'  => 'Your happiness is our priority.',
		),
	);
}

/**
 * Trust badges for the hero strip and footer.
 *
 * @return array
 */
function briteclean_trust_badges_defaults() {
	return array(
		array(
			'icon'  => 'check',
			'title' => 'Reliable & Trustworthy',
		),
		array(
			'icon'  => 'star',
			'title' => 'Professional & Experienced',
		),
		array(
			'icon'  => 'heart',
			'title' => 'Customer Satisfaction',
		),
	);
}

/**
 * Placeholder testimonials, seeded as editable posts.
 *
 * Clearly marked as samples so nobody mistakes them for real reviews at launch.
 *
 * @return array
 */
function briteclean_testimonials_defaults() {
	return array(
		array(
			'author'   => 'Sample Review — replace before launch',
			'location' => 'West Chester, OH',
			'rating'   => 5,
			'quote'    => 'They were on time, thorough and genuinely pleasant to have in the house. The kitchen has not looked like that since we moved in.',
		),
		array(
			'author'   => 'Sample Review — replace before launch',
			'location' => 'Liberty Township, OH',
			'rating'   => 5,
			'quote'    => 'We use them for our office every Friday evening. Monday mornings feel completely different. Booking took about two minutes.',
		),
		array(
			'author'   => 'Sample Review — replace before launch',
			'location' => 'Mason, OH',
			'rating'   => 5,
			'quote'    => 'Booked a move-in clean on short notice. The team handled the whole place before the furniture arrived and the price was exactly what they quoted.',
		),
	);
}

/**
 * Property-type options offered in the booking form.
 *
 * @return array
 */
function briteclean_property_types() {
	return array(
		'residential' => 'Residential',
		'office'      => 'Office',
		'commercial'  => 'Commercial',
	);
}

/**
 * Time-of-day preferences offered in the booking form.
 *
 * @return array
 */
function briteclean_time_preferences() {
	return array(
		'morning'   => 'Morning (8am – 12pm)',
		'afternoon' => 'Afternoon (12pm – 4pm)',
		'evening'   => 'Evening (4pm – 8pm)',
	);
}
