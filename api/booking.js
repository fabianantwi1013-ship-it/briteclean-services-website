/**
 * Booking request handler.
 *
 * Validates the submission, then emails the business and confirms to the customer.
 * Nothing is stored, by design. That means a delivery failure is a lost booking,
 * which is why the owner notification is treated as the one that must succeed: if it
 * fails, the visitor is told to call instead rather than being shown a false success.
 */
import { validateBooking, describeBooking, looksLikeSpam, SERVICE_LABELS } from './_lib/validate.js';
import { send, wrap, detailsTable, isConfigured, config, escapeHtml } from './_lib/email.js';

const BUSINESS = {
  name: 'Briteclean Services LLC',
  phone: '+1 (484) 347-9523',
  address: '9540 Woodland Hills Drive, West Chester, Ohio, 45011',
};

const BRAND = '#C8102E';

export default async function handler(req, res) {
  if (req.method !== 'POST') {
    res.setHeader('Allow', 'POST');
    return res.status(405).json({ ok: false, message: 'Method not allowed.' });
  }

  let raw = req.body;
  if (typeof raw === 'string') {
    try { raw = JSON.parse(raw); } catch { raw = {}; }
  }
  if (!raw || typeof raw !== 'object') raw = {};

  // Silent success for spam: telling a bot why it failed only helps it adapt.
  if (looksLikeSpam(raw)) {
    return res.status(200).json({ ok: true });
  }

  const { ok, errors, clean } = validateBooking(raw);
  if (!ok) {
    return res.status(422).json({ ok: false, errors });
  }

  if (!isConfigured()) {
    console.error('[booking] Email is not configured: RESEND_API_KEY, MAIL_FROM or MAIL_TO missing.');
    return res.status(500).json({
      ok: false,
      message: `We could not send your request. Please call us on ${BUSINESS.phone} and we will take the booking over the phone.`,
    });
  }

  const rows = describeBooking(clean);
  const { to } = config();
  const serviceList = clean.services.map((s) => SERVICE_LABELS[s]).join(', ');
  const prettyDate = rows.find(([label]) => label === 'Preferred date')?.[1] ?? '';

  /* ---- owner notification (must succeed) ---- */
  const ownerHtml = wrap(
    'New Booking Request',
    BUSINESS.name,
    `<p style="margin:0 0 18px;font-size:15px;">A new booking request has come in through the website. Full details below.</p>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:22px;background:#fdf2f4;border-radius:10px;"><tr><td style="padding:16px 18px;">
<p style="margin:0 0 6px;font-size:12px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#6b7280;">Reach them on</p>
<p style="margin:0;font-size:17px;font-weight:800;"><a href="tel:${escapeHtml(clean.phone.replace(/\D+/g, ''))}" style="color:${BRAND};text-decoration:none;">${escapeHtml(clean.phone)}</a></p>
<p style="margin:4px 0 0;font-size:14px;"><a href="mailto:${escapeHtml(clean.email)}" style="color:${BRAND};">${escapeHtml(clean.email)}</a></p>
</td></tr></table>
${detailsTable(rows)}
<p style="margin:20px 0 0;font-size:12px;color:#6b7280;">Submitted ${escapeHtml(new Date().toLocaleString('en-US', { timeZone: 'America/New_York' }))} (Eastern).</p>`,
    [BUSINESS.phone, BUSINESS.address]
  );

  const ownerResult = await send({
    to,
    subject: `New booking request: ${clean.fullName} (${prettyDate})`,
    html: ownerHtml,
    replyTo: clean.email,
  });

  if (!ownerResult.ok) {
    // Never show success when the business was not actually told.
    console.error('[booking] Owner notification failed:', ownerResult.error, JSON.stringify(clean));
    return res.status(502).json({
      ok: false,
      message: `We could not send your request just now. Please call us on ${BUSINESS.phone} so we do not lose your booking.`,
    });
  }

  /* ---- customer confirmation (best effort) ---- */
  const firstName = clean.fullName.split(' ')[0] || 'there';
  const customerHtml = wrap(
    'Your Booking Request',
    BUSINESS.name,
    `<p style="margin:0 0 16px;font-size:16px;">Hi ${escapeHtml(firstName)},</p>
<p style="margin:0 0 16px;font-size:15px;">Thank you. We have received your booking request and will be in touch shortly to confirm the details and agree a time.</p>
<p style="margin:0 0 24px;font-size:15px;"><strong>Nothing has been charged.</strong> This is a request, not a confirmed appointment. We always speak to you before anything is scheduled.</p>
<p style="margin:0 0 10px;font-size:12px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#6b7280;">What you asked for</p>
${detailsTable(rows)}
<p style="margin:26px 0 0;font-size:15px;">Spotted something wrong, or need it sooner? Reply to this email or call us on ${escapeHtml(BUSINESS.phone)}.</p>`,
    [BUSINESS.phone, BUSINESS.address]
  );

  const customerResult = await send({
    to: [clean.email],
    subject: `We have received your booking request | ${BUSINESS.name}`,
    html: customerHtml,
    replyTo: config().to[0],
  });

  // The business has the booking, so this is not worth failing the request over.
  if (!customerResult.ok) {
    console.warn('[booking] Customer confirmation failed:', customerResult.error);
  }

  return res.status(200).json({ ok: true, confirmationSent: customerResult.ok });
}
