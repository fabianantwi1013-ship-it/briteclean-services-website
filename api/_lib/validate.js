/**
 * Server-side validation for booking submissions.
 *
 * Direct port of the rules the WordPress plugin enforced. Runs on every request
 * regardless of what the browser did — the client-side checks exist only for faster
 * feedback and are treated as a convenience, never a guarantee.
 */

export const SERVICE_SLUGS = [
  'residential-cleaning', 'office-cleaning', 'deep-cleaning', 'commercial-cleaning',
  'window-cleaning', 'move-in-cleaning', 'general-cleaning', 'emergency-cleaning',
];

export const SERVICE_LABELS = {
  'residential-cleaning': 'Residential Cleaning',
  'office-cleaning': 'Office Cleaning',
  'deep-cleaning': 'Deep Cleaning',
  'commercial-cleaning': 'Commercial Cleaning',
  'window-cleaning': 'Window Cleaning',
  'move-in-cleaning': 'Move-In Cleaning',
  'general-cleaning': 'General Cleaning',
  'emergency-cleaning': 'Emergency Cleaning',
};

const PROPERTY_TYPES = { residential: 'Residential', office: 'Office', commercial: 'Commercial' };
const TIME_PREFERENCES = {
  morning: 'Morning (8am – 12pm)',
  afternoon: 'Afternoon (12pm – 4pm)',
  evening: 'Evening (4pm – 8pm)',
};
const CONTACT_METHODS = { phone: 'Phone call', whatsapp: 'WhatsApp', email: 'Email' };

/** Strip tags and collapse whitespace. Equivalent to WP's sanitize_text_field. */
export function sanitizeText(value, maxLength = 500) {
  return String(value ?? '')
    .replace(/<[^>]*>/g, '')
    .replace(/[\r\n\t]+/g, ' ')
    .replace(/\s+/g, ' ')
    .trim()
    .slice(0, maxLength);
}

/** Like sanitizeText but keeps newlines, for textareas. */
export function sanitizeMultiline(value, maxLength = 2000) {
  return String(value ?? '')
    .replace(/<[^>]*>/g, '')
    .replace(/\r\n?/g, '\n')
    .replace(/\n{3,}/g, '\n\n')
    .trim()
    .slice(0, maxLength);
}

const isEmail = (v) => /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v) && v.length <= 190;

const isValidDate = (v) => {
  if (!/^\d{4}-\d{2}-\d{2}$/.test(v)) return false;
  const d = new Date(v + 'T00:00:00Z');
  // Date is lenient — "2026-13-45" rolls over. Comparing the round-trip rejects those.
  return !isNaN(d.getTime()) && d.toISOString().slice(0, 10) === v;
};

/**
 * Validate and normalise a raw booking payload.
 *
 * @returns {{ ok: boolean, errors: Record<string,string>, clean: Record<string,any> }}
 */
export function validateBooking(raw) {
  const errors = {};
  const clean = {};

  /* --- services (multi-select, allow-listed) --- */
  const submitted = Array.isArray(raw.services)
    ? raw.services
    : raw.services
      ? [raw.services]
      : [];

  // Allow-list: anything not an offered service is dropped silently.
  const services = [...new Set(submitted.map((s) => sanitizeText(s, 60)))]
    .filter((s) => SERVICE_SLUGS.includes(s))
    .slice(0, 12); // guards against a scripted submission ticking everything

  if (!services.length) errors.services = 'Please choose at least one service.';
  clean.services = services;

  /* --- property type (required, allow-listed) --- */
  const propertyType = sanitizeText(raw.propertyType, 40);
  if (!propertyType) errors.propertyType = 'Please choose a property type.';
  else if (!PROPERTY_TYPES[propertyType]) errors.propertyType = 'That is not a valid property type.';
  clean.propertyType = PROPERTY_TYPES[propertyType] ? propertyType : '';

  /* --- optional free-choice selects --- */
  for (const key of ['bedrooms', 'bathrooms', 'propertySize', 'frequency']) {
    clean[key] = sanitizeText(raw[key], 60);
  }

  clean.instructions = sanitizeMultiline(raw.instructions, 2000);

  /* --- dates --- */
  const today = new Date();
  today.setUTCHours(0, 0, 0, 0);
  const limit = new Date(today);
  limit.setUTCFullYear(limit.getUTCFullYear() + 2);

  const preferredDate = sanitizeText(raw.preferredDate, 10);
  if (!preferredDate) {
    errors.preferredDate = 'Please choose a date.';
  } else if (!isValidDate(preferredDate)) {
    errors.preferredDate = 'That date does not look right. Please pick one from the calendar.';
  } else {
    const d = new Date(preferredDate + 'T00:00:00Z');
    if (d < today) errors.preferredDate = 'Please choose a date that has not already passed.';
    // A request two years out is far more likely a typo than a real booking.
    else if (d > limit) errors.preferredDate = 'That date is too far ahead. Please choose one within the next two years.';
    else clean.preferredDate = preferredDate;
  }

  const alternateDate = sanitizeText(raw.alternateDate, 10);
  if (alternateDate && isValidDate(alternateDate)) {
    const d = new Date(alternateDate + 'T00:00:00Z');
    if (d >= today && d <= limit) clean.alternateDate = alternateDate;
  }

  /* --- time preference (required, allow-listed) --- */
  const timePreference = sanitizeText(raw.timePreference, 40);
  if (!timePreference) errors.timePreference = 'Please choose a time of day.';
  else if (!TIME_PREFERENCES[timePreference]) errors.timePreference = 'That is not a valid time of day.';
  clean.timePreference = TIME_PREFERENCES[timePreference] ? timePreference : '';

  /* --- contact --- */
  const fullName = sanitizeText(raw.fullName, 120);
  if (!fullName) errors.fullName = 'Please enter your full name.';
  else if (fullName.length < 2) errors.fullName = 'Please enter your full name.';
  clean.fullName = fullName;

  const phone = sanitizeText(raw.phone, 40);
  const digits = phone.replace(/\D+/g, '');
  if (!phone) errors.phone = 'Please enter your phone number.';
  else if (digits.length < 7 || digits.length > 15) {
    errors.phone = 'Please enter a phone number we can actually reach you on.';
  }
  clean.phone = phone;

  const email = sanitizeText(raw.email, 190).toLowerCase();
  if (!email) errors.email = 'Please enter your email address.';
  else if (!isEmail(email)) errors.email = 'That email address does not look valid.';
  clean.email = email;

  const address = sanitizeMultiline(raw.address, 400);
  if (!address) errors.address = 'Please enter the service address.';
  else if (address.length < 8) errors.address = 'Please enter the full service address, including ZIP code.';
  clean.address = address;

  const contactMethod = sanitizeText(raw.contactMethod, 40);
  clean.contactMethod = CONTACT_METHODS[contactMethod] ? contactMethod : '';

  return { ok: Object.keys(errors).length === 0, errors, clean };
}

/** Human-readable labels for a validated booking, shared by both emails. */
export function describeBooking(clean) {
  const fmtDate = (v) => {
    if (!v) return '';
    const d = new Date(v + 'T00:00:00Z');
    return d.toLocaleDateString('en-US', {
      weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', timeZone: 'UTC',
    });
  };

  return [
    ['Services requested', clean.services.map((s) => SERVICE_LABELS[s]).join(', ')],
    ['Property type', PROPERTY_TYPES[clean.propertyType] || ''],
    ['Bedrooms / rooms', clean.bedrooms],
    ['Bathrooms', clean.bathrooms],
    ['Approximate size', clean.propertySize],
    ['How often', clean.frequency],
    ['Special instructions', clean.instructions],
    ['Preferred date', fmtDate(clean.preferredDate)],
    ['Time of day', TIME_PREFERENCES[clean.timePreference] || ''],
    ['Backup date', fmtDate(clean.alternateDate)],
    ['Full name', clean.fullName],
    ['Phone number', clean.phone],
    ['Email address', clean.email],
    ['Service address', clean.address],
    ['Best way to reach you', CONTACT_METHODS[clean.contactMethod] || ''],
  ].filter(([, value]) => value);
}

/**
 * Spam heuristics. Two layers ship enabled, both invisible to real visitors:
 *   1. Honeypot — a hidden field only an automated filler would populate.
 *   2. Time trap — submissions arriving impossibly fast, or from a form older than
 *      two hours, are rejected.
 *
 * ---------------------------------------------------------------------------
 * ADDING A CAPTCHA (third layer)
 * ---------------------------------------------------------------------------
 * If these stop holding, add Cloudflare Turnstile — no visible challenge, no Google
 * cookie, and none of the accessibility problems image challenges cause:
 *   1. Render the widget in BookingForm.astro above `.bc-form__nav`.
 *   2. Verify the token here, before returning false:
 *        const r = await fetch('https://challenges.cloudflare.com/turnstile/v0/siteverify', {
 *          method: 'POST',
 *          headers: { 'Content-Type': 'application/json' },
 *          body: JSON.stringify({ secret: process.env.TURNSTILE_SECRET, response: token }),
 *        });
 *        if (!(await r.json()).success) return true;
 *   3. Store the secret as a Vercel environment variable, never in this file.
 *
 * Note there is deliberately no rate limiting here — serverless functions share no
 * memory, so it would need Vercel KV or similar. Add it there if abuse appears.
 */
export function looksLikeSpam(raw) {
  if (raw.website) return true; // honeypot

  const renderedAt = Number(raw.renderedAt);
  if (!Number.isFinite(renderedAt) || renderedAt <= 0) return false; // no-JS submission

  const elapsed = (Date.now() - renderedAt) / 1000;
  if (elapsed < 3) return true;      // nobody fills five steps in three seconds
  if (elapsed > 7200) return true;   // stale form, likely a replayed capture

  return false;
}
