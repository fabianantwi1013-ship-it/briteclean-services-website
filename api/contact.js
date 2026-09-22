/**
 * Contact form handler — a short message, emailed to the business.
 *
 * Deliberately minimal. Anything that is actually a booking belongs in the booking
 * form, which is why this one links across to it.
 */
import { sanitizeText, sanitizeMultiline } from './_lib/validate.js';
import { send, wrap, isConfigured, config, escapeHtml } from './_lib/email.js';

const BUSINESS = {
  name: 'Briteclean Services LLC',
  phone: '+1 (484) 347-9523',
  address: '9540 Woodland Hills Drive, West Chester, Ohio, 45011',
};

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

  // Honeypot — pretend it worked rather than telling a bot it was caught.
  if (raw.website) return res.status(200).json({ ok: true });

  const name = sanitizeText(raw.name, 120);
  const email = sanitizeText(raw.email, 190).toLowerCase();
  const phone = sanitizeText(raw.phone, 40);
  const message = sanitizeMultiline(raw.message, 2000);

  const validEmail = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(email);

  if (!name || !validEmail || message.length < 5) {
    return res.status(422).json({
      ok: false,
      message: 'Please fill in your name, a valid email address and a message.',
    });
  }

  if (!isConfigured()) {
    console.error('[contact] Email is not configured.');
    return res.status(500).json({
      ok: false,
      message: `We could not send that. Please call us on ${BUSINESS.phone}.`,
    });
  }

  const html = wrap(
    'Website Enquiry',
    BUSINESS.name,
    `<p style="margin:0 0 8px;"><strong>Name:</strong> ${escapeHtml(name)}</p>
<p style="margin:0 0 8px;"><strong>Email:</strong> <a href="mailto:${escapeHtml(email)}">${escapeHtml(email)}</a></p>
<p style="margin:0 0 18px;"><strong>Phone:</strong> ${escapeHtml(phone || 'not provided')}</p>
<hr style="border:0;border-top:1px solid #e5e7eb;margin:0 0 18px;" />
<p style="margin:0;white-space:pre-wrap;">${escapeHtml(message)}</p>`,
    [BUSINESS.phone, BUSINESS.address]
  );

  const result = await send({
    to: config().to,
    subject: `Website enquiry from ${name}`,
    html,
    replyTo: email,
  });

  if (!result.ok) {
    console.error('[contact] Send failed:', result.error);
    return res.status(502).json({
      ok: false,
      message: `We could not send that. Please call us on ${BUSINESS.phone}.`,
    });
  }

  return res.status(200).json({ ok: true });
}
