/**
 * Every piece of business content, in one place.
 *
 * This is the file to edit for copy changes: nothing else hardcodes text. Keep it
 * short. The business name, taglines, hero wording, phone numbers, address, service
 * names, value propositions and trust badges are the client's own wording and must
 * stay exactly as they are.
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
    'West Chester, Liberty Township, Mason, Fairfield, Hamilton, Sharonville and nearby Butler and Warren County communities.',
  hours: [
    { days: 'Monday to Friday', time: '8:00 AM to 6:00 PM', open: '08:00', close: '18:00' },
    { days: 'Saturday', time: '9:00 AM to 4:00 PM', open: '09:00', close: '16:00' },
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

/** Hero slideshow. `photo` is a key in src/data/photos.js. */
export const heroSlides = [
  { photo: 'scene-living', position: '50% 62%' },
  { photo: 'scene-kitchen', position: '50% 55%' },
  { photo: 'scene-loft', position: '50% 50%' },
  { photo: 'scene-bath', position: '50% 60%' },
];

/**
 * The eight services. `slug` is the stable identifier used by the booking form and
 * the API allow-list: changing one breaks bookings for that service.
 */
export const services = [
  {
    slug: 'residential-cleaning',
    name: 'Residential Cleaning',
    icon: 'home',
    short: 'Regular cleaning for houses and apartments.',
    long: 'Kitchens, bathrooms, bedrooms and living areas, cleaned weekly, every two weeks or monthly.',
  },
  {
    slug: 'office-cleaning',
    name: 'Office Cleaning',
    icon: 'briefcase',
    short: 'Clean, presentable workspaces.',
    long: 'Desks, meeting rooms, kitchens and washrooms, scheduled around your business hours.',
  },
  {
    slug: 'deep-cleaning',
    name: 'Deep Cleaning',
    icon: 'sparkle',
    short: 'A top-to-bottom clean.',
    long: 'Inside appliances, behind furniture, grout and limescale. Ideal seasonally or before an event.',
  },
  {
    slug: 'commercial-cleaning',
    name: 'Commercial Cleaning',
    icon: 'building',
    short: 'Retail, clinics and shared spaces.',
    long: 'A written checklist keeps every visit consistent, whatever the size of the space.',
  },
  {
    slug: 'window-cleaning',
    name: 'Window Cleaning',
    icon: 'window',
    short: 'Streak-free glass, inside and out.',
    long: 'Glass, frames, sills and tracks, on their own or as part of a deep clean.',
  },
  {
    slug: 'move-in-cleaning',
    name: 'Move-In Cleaning',
    icon: 'box',
    short: 'Empty homes, ready for day one.',
    long: 'Every room, cupboard and appliance, cleaned before you move in or after you move out.',
  },
  {
    slug: 'general-cleaning',
    name: 'General Cleaning',
    icon: 'broom',
    short: 'A one-off clean, your way.',
    long: 'After a party, before guests, or one room that needs attention. Tell us what you need.',
  },
  {
    slug: 'emergency-cleaning',
    name: 'Emergency Cleaning',
    icon: 'bolt',
    short: 'Fast help when it cannot wait.',
    long: 'Short-notice cleaning for spills, inspections and last-minute viewings. Call for the fastest response.',
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

/**
 * Customer reviews. Not shown anywhere yet: add real reviews here (name, town,
 * rating, quote) and they can go back on the homepage. Never publish invented ones.
 */
export const testimonials = [];

export const propertyTypes = [
  { value: 'residential', label: 'Residential' },
  { value: 'office', label: 'Office' },
  { value: 'commercial', label: 'Commercial' },
];

export const timePreferences = [
  { value: 'morning', label: 'Morning (8am to 12pm)' },
  { value: 'afternoon', label: 'Afternoon (12pm to 4pm)' },
  { value: 'evening', label: 'Evening (4pm to 8pm)' },
];

export const roomOptions = ['Studio or open plan', '1 room', '2 rooms', '3 rooms', '4 rooms', '5 rooms', '6 or more'];
export const bathroomOptions = ['1 bathroom', '2 bathrooms', '3 bathrooms', '4 or more'];
export const sizeOptions = ['Under 1,000 sq ft', '1,000 to 2,000 sq ft', '2,000 to 3,000 sq ft', '3,000 to 5,000 sq ft', 'Over 5,000 sq ft', 'Not sure'];
export const frequencyOptions = ['One-off visit', 'Weekly', 'Every two weeks', 'Monthly', 'Not sure yet'];
export const contactMethods = [
  { value: 'phone', label: 'Phone call' },
  { value: 'whatsapp', label: 'WhatsApp' },
  { value: 'email', label: 'Email' },
];

export const faqs = [
  { q: 'Do I pay when I book online?', a: 'No. The form is a request. We confirm the details and price with you before anything is scheduled.' },
  { q: 'How quickly will I hear back?', a: 'Usually the same day, and always within one business day. For anything urgent, call or WhatsApp us.' },
  { q: 'Do I need to be home?', a: 'No. We agree how we get in and lock up before the first visit.' },
  { q: 'Do you bring supplies?', a: 'Yes. Everything is included, and our products are safe for children and pets.' },
  { q: 'Are your staff background-checked?', a: 'Yes. Every team member is background-checked and trained before working in your space.' },
  { q: 'What if I am not happy with the clean?', a: 'Tell us within 24 hours and we will come back and put it right.' },
  { q: 'Can I change or cancel a booking?', a: 'Yes. Just call us, as early as you can.' },
  { q: 'Which areas do you cover?', a: `We serve ${business.serviceArea}` },
];
