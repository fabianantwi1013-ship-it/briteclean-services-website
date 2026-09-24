/**
 * Every piece of business content, in one place.
 *
 * This is the file to edit for copy changes — nothing below it hardcodes text. It is
 * the direct descendant of the WordPress theme's inc/defaults.php, carried over so the
 * wording, service descriptions and FAQ answers stay identical.
 */

export const business = {
  name: 'Briteclean Services LLC',
  tagline: 'Clean Spaces, Healthy Lives',
  taglineSecondary: 'Your Clean Space, Our Priority',
  phonePrimary: '+1 (484) 347-9523',
  phoneSecondary: '+1 (513) 293-2971',
  email: 'info@britecleanservices.com',
  street: '9540 Woodland Hills Drive',
  city: 'West Chester',
  state: 'Ohio',
  stateShort: 'OH',
  postal: '45011',
  country: 'US',
  serviceArea:
    'West Chester, Liberty Township, Mason, Fairfield, Hamilton, Sharonville and surrounding Butler & Warren County communities.',
  hours: [
    { days: 'Monday – Friday', time: '8:00 AM – 6:00 PM', open: '08:00', close: '18:00' },
    { days: 'Saturday', time: '9:00 AM – 4:00 PM', open: '09:00', close: '16:00' },
    { days: 'Sunday', time: 'By appointment', open: null, close: null },
  ],
  social: {
    facebook: '',
    instagram: '',
    google: '',
    yelp: '',
  },
};

/** Digits only, for tel: and wa.me links. */
export const phoneDigits = (phone) => String(phone).replace(/\D+/g, '');

export const whatsappUrl = (message = "Hi, I'd like to book a cleaning service") =>
  `https://wa.me/${phoneDigits(business.phonePrimary)}?text=${encodeURIComponent(message)}`;

export const addressLine = () =>
  `${business.street}, ${business.city}, ${business.state}, ${business.postal}`;

export const hero = {
  badge: 'Sparkling Results Every Time',
  headline: 'Professional Cleaning Services for Your Space',
  subheadline: 'We make your home or business cleaner, fresher and healthier.',
  ctaPrimary: 'Get a Free Quote',
  ctaSecondary: 'Call Now',
};

/**
 * The eight services. `slug` is the stable identifier used by the booking form and
 * stored in submissions — changing one after launch breaks the link between a booking
 * record and the service it refers to.
 */
export const services = [
  {
    slug: 'residential-cleaning',
    name: 'Residential Cleaning',
    icon: 'home',
    short: 'Regular upkeep for houses and apartments, on a schedule that fits your week.',
    long: 'Routine cleaning for the spaces you actually live in. We cover kitchens, bathrooms, bedrooms and living areas — dusting, vacuuming, mopping, surface sanitising and bin changes — on a weekly, fortnightly or monthly rhythm. Same team each visit wherever we can manage it, so nobody has to re-explain how you like things done.',
  },
  {
    slug: 'office-cleaning',
    name: 'Office Cleaning',
    icon: 'briefcase',
    short: 'Desks, common areas and washrooms kept presentable for staff and visitors.',
    long: 'Scheduled cleaning for workplaces, timed around your business hours so nobody is vacuuming past a meeting. Workstations, meeting rooms, kitchens, washrooms and entryways, with waste and recycling handled. Evening and early-morning slots available.',
  },
  {
    slug: 'deep-cleaning',
    name: 'Deep Cleaning',
    icon: 'sparkle',
    short: 'A top-to-bottom reset that reaches what routine cleaning skips.',
    long: 'The full detail pass: inside appliances, behind and under furniture, skirting boards, door frames, light fittings, tile grout, and built-up limescale in bathrooms and kitchens. Worth booking seasonally, before hosting, or as a first visit to bring a property up to a standard that routine cleaning can then hold.',
  },
  {
    slug: 'commercial-cleaning',
    name: 'Commercial Cleaning',
    icon: 'building',
    short: 'Retail units, clinics and shared facilities, cleaned to a consistent standard.',
    long: 'Larger premises and higher-traffic environments — retail floors, waiting rooms, clinics, gyms, lobbies and shared facilities. We agree a written scope and checklist so standards stay consistent across visits and across staff, and we work to whatever compliance requirements your sector expects.',
  },
  {
    slug: 'window-cleaning',
    name: 'Window Cleaning',
    icon: 'window',
    short: 'Streak-free glass inside and out, frames and sills included.',
    long: 'Interior and accessible exterior glass cleaned streak-free, with frames, sills and tracks wiped down rather than left behind. Available as a standalone visit or folded into a deep clean. Tell us about upper floors when you enquire so we bring the right access equipment.',
  },
  {
    slug: 'move-in-cleaning',
    name: 'Move-In Cleaning',
    icon: 'box',
    short: 'An empty property made genuinely ready for the first night.',
    long: 'For the gap between keys and furniture. Every room cleaned while it is empty and easy to reach — cupboards and drawers inside and out, appliances, bathrooms, floors and windows. Also available as a move-out clean when you need a property handed back in good condition.',
  },
  {
    slug: 'general-cleaning',
    name: 'General Cleaning',
    icon: 'broom',
    short: 'A flexible one-off visit, scoped to whatever you need most.',
    long: 'Not everything fits a category. Tell us what needs doing — after a party, ahead of guests, a single room that has got away from you — and we will scope a one-off visit around it rather than selling you a package you do not need.',
  },
  {
    slug: 'emergency-cleaning',
    name: 'Emergency Cleaning',
    icon: 'bolt',
    short: 'Short-notice response when something cannot wait until next week.',
    long: 'Short-notice cleaning for the situations you did not plan for: a spill before an inspection, a last-minute viewing, a post-event turnaround. Call rather than use the form when it is genuinely urgent — we will tell you honestly what we can reach and when.',
  },
];

export const valueProps = [
  { icon: 'check', title: 'Quality Cleaning', text: "We pay attention to the details so you don't have to." },
  { icon: 'calendar', title: 'Flexible Scheduling', text: 'We work around your time, not the other way around.' },
  { icon: 'shield', title: 'Trained & Trusted Staff', text: 'Professional, background-checked and courteous.' },
  { icon: 'leaf', title: 'Eco-Friendly Products', text: 'Safe for your family, pets and the environment.' },
  { icon: 'heart', title: '100% Satisfaction', text: 'Your happiness is our priority.' },
];

export const trustBadges = [
  { icon: 'check', title: 'Reliable & Trustworthy' },
  { icon: 'star', title: 'Professional & Experienced' },
  { icon: 'heart', title: 'Customer Satisfaction' },
];

/** Placeholder reviews — labelled so they cannot be mistaken for real ones. */
export const testimonials = [
  {
    author: 'Sample Review — replace before launch',
    location: 'West Chester, OH',
    rating: 5,
    quote: 'They were on time, thorough and genuinely pleasant to have in the house. The kitchen has not looked like that since we moved in.',
  },
  {
    author: 'Sample Review — replace before launch',
    location: 'Liberty Township, OH',
    rating: 5,
    quote: 'We use them for our office every Friday evening. Monday mornings feel completely different. Booking took about two minutes.',
  },
  {
    author: 'Sample Review — replace before launch',
    location: 'Mason, OH',
    rating: 5,
    quote: 'Booked a move-in clean on short notice. The team handled the whole place before the furniture arrived and the price was exactly what they quoted.',
  },
];

export const propertyTypes = [
  { value: 'residential', label: 'Residential' },
  { value: 'office', label: 'Office' },
  { value: 'commercial', label: 'Commercial' },
];

export const timePreferences = [
  { value: 'morning', label: 'Morning (8am – 12pm)' },
  { value: 'afternoon', label: 'Afternoon (12pm – 4pm)' },
  { value: 'evening', label: 'Evening (4pm – 8pm)' },
];

export const roomOptions = ['Studio / open plan', '1 room', '2 rooms', '3 rooms', '4 rooms', '5 rooms', '6 or more'];
export const bathroomOptions = ['1 bathroom', '2 bathrooms', '3 bathrooms', '4 or more'];
export const sizeOptions = ['Under 1,000 sq ft', '1,000 – 2,000 sq ft', '2,000 – 3,000 sq ft', '3,000 – 5,000 sq ft', 'Over 5,000 sq ft', 'Not sure'];
export const frequencyOptions = ['One-off visit', 'Weekly', 'Every two weeks', 'Monthly', 'Not sure yet'];
export const contactMethods = [
  { value: 'phone', label: 'Phone call' },
  { value: 'whatsapp', label: 'WhatsApp' },
  { value: 'email', label: 'Email' },
];

export const faqs = [
  { q: 'Do I pay when I submit the booking form?', a: 'No. The form sends us a request, not an order. We review it, confirm the details and timing with you, and agree the price before any work is scheduled. Nothing is charged through this website.' },
  { q: 'How quickly will I hear back?', a: 'Usually the same working day, and always within one business day. If it is urgent, call or message us on WhatsApp rather than using the form — that reaches us fastest.' },
  { q: 'Do I need to be home during the cleaning?', a: 'Not at all. Plenty of our customers give us access arrangements and come home to a finished job. We will agree how we get in and how we lock up before the first visit.' },
  { q: 'Do you bring your own supplies and equipment?', a: 'Yes, everything is included. We use eco-friendly products that are safe around children and pets. If you would rather we used something specific you already have, just tell us.' },
  { q: 'Are your staff background-checked and insured?', a: 'Every member of our team is background-checked and trained to the same standard before they work unsupervised in a customer’s property.' },
  { q: 'What if I am not happy with the clean?', a: 'Tell us within 24 hours and we will come back and put it right. That is what our satisfaction guarantee means in practice — not a refund process, just the work done properly.' },
  { q: 'Can I change or cancel a booking?', a: 'Yes. Because every booking is confirmed with a person rather than an automated calendar, rescheduling is a phone call. We only ask for as much notice as you can reasonably give.' },
  { q: 'Which areas do you cover?', a: `We serve ${business.serviceArea} If you are just outside that, ask anyway — we will give you a straight answer.` },
];

/* ---------------------------------------------------------------------------
 * Redesign content. Everything below is derived from the copy above or the FAQ —
 * no new claims. Keep it that way: nothing here should state a number or promise
 * the business has not made elsewhere.
 * ------------------------------------------------------------------------- */

/** Hero slideshow. `photo` is a key in src/data/photos.js. */
export const heroSlides = [
  { photo: 'scene-living', label: 'Living spaces', position: '50% 62%' },
  { photo: 'scene-kitchen', label: 'Kitchens', position: '50% 55%' },
  { photo: 'scene-loft', label: 'Open-plan homes', position: '50% 50%' },
  { photo: 'scene-bath', label: 'Bathrooms', position: '50% 60%' },
];

/** Facts shown as counters. Each one is stated elsewhere on the site. */
export const facts = [
  { value: 8, suffix: '', label: 'Specialist cleaning services' },
  { value: 5, suffix: '', label: 'Simple steps to request a booking' },
  { value: 1, suffix: '', label: 'Business day, at most, to hear back' },
  { value: 0, prefix: '$', suffix: '', label: 'Taken online — you pay nothing to book' },
];

/** How booking works, paraphrasing the FAQ answers. */
export const process = [
  {
    title: 'Request',
    text: 'Tell us what needs cleaning and when, in five short steps. No account, no payment.',
  },
  {
    title: 'Confirm',
    text: 'A real person reviews your request, calls to agree the details and timing, and confirms the price before anything is scheduled.',
  },
  {
    title: 'Clean',
    text: 'Our trained, background-checked team arrives with everything needed — eco-friendly products that are safe for children and pets.',
  },
  {
    title: 'Guarantee',
    text: 'Not happy with something? Tell us within 24 hours and we will come back and put it right.',
  },
];

/**
 * Service-area map. Coordinates are approximate town centres, used only to place
 * the dots on a stylised map — not for navigation.
 */
export const areaTowns = [
  { name: 'West Chester', lat: 39.3312, lng: -84.4077, hub: true },
  { name: 'Liberty Township', lat: 39.3845, lng: -84.4302 },
  { name: 'Mason', lat: 39.3601, lng: -84.3099 },
  { name: 'Fairfield', lat: 39.3454, lng: -84.5603 },
  { name: 'Hamilton', lat: 39.3995, lng: -84.5613 },
  { name: 'Sharonville', lat: 39.2681, lng: -84.4133 },
];
