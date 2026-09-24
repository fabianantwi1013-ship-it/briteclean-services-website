/**
 * Email delivery via Resend.
 *
 * Plain fetch rather than the SDK: one less dependency, and the API is two fields.
 *
 * Required environment variables (set in Vercel → Settings → Environment Variables):
 *   RESEND_API_KEY   Your Resend API key.
 *   MAIL_FROM        Verified sender, e.g. "Briteclean <bookings@britecleanservices.com>".
 *   MAIL_TO          Where booking notifications go. Comma-separate for several.
 */

const BRAND = '#C8102E';

export function config() {
  return {
    apiKey: process.env.RESEND_API_KEY,
    from: process.env.MAIL_FROM,
    to: (process.env.MAIL_TO || '').split(',').map((s) => s.trim()).filter(Boolean),
  };
}

export function isConfigured() {
  const c = config();
  return Boolean(c.apiKey && c.from && c.to.length);
}

const escapeHtml = (s) =>
  String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

/**
 * Table-based HTML shell, still what Outlook reliably renders.
 */
export function wrap(heading, businessName, inner, footerLines = []) {
  return `<!doctype html>
<html><head><meta charset="utf-8" /><meta name="viewport" content="width=device-width,initial-scale=1" /><title>${escapeHtml(heading)}</title></head>
<body style="margin:0;padding:0;background:#f4f4f5;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#40464e;line-height:1.6;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f4f4f5;padding:24px 12px;"><tr><td align="center">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;background:#fff;border-radius:14px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.06);">
<tr><td style="background:${BRAND};padding:26px 30px;">
<p style="margin:0;color:#fff;font-size:20px;font-weight:800;line-height:1.3;">${escapeHtml(heading)}</p>
<p style="margin:6px 0 0;color:#fff;opacity:.85;font-size:13px;">${escapeHtml(businessName)}</p>
</td></tr>
<tr><td style="padding:30px;">${inner}</td></tr>
<tr><td style="background:#1a1a1a;padding:20px 30px;color:#a1a1aa;font-size:12px;">
<p style="margin:0 0 4px;color:#fff;font-weight:700;">${escapeHtml(businessName)}</p>
${footerLines.map((l) => `<p style="margin:0;">${escapeHtml(l)}</p>`).join('')}
</td></tr>
</table></td></tr></table></body></html>`;
}

/** Render label/value pairs as a definition table. */
export function detailsTable(rows) {
  return `<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">${rows
    .map(
      ([label, value]) => `<tr>
<td style="padding:10px 0;border-bottom:1px solid #e5e7eb;vertical-align:top;width:38%;color:#6b7280;font-size:13px;font-weight:600;">${escapeHtml(label)}</td>
<td style="padding:10px 0;border-bottom:1px solid #e5e7eb;vertical-align:top;color:#1a1a1a;font-size:14px;">${escapeHtml(value).replace(/\n/g, '<br />')}</td>
</tr>`
    )
    .join('')}</table>`;
}

/**
 * Send one message. Returns { ok, id? , error? } rather than throwing, so the caller
 * can decide what a failure means.
 */
export async function send({ to, subject, html, replyTo }) {
  const { apiKey, from } = config();
  if (!apiKey || !from) return { ok: false, error: 'Email is not configured on the server.' };

  try {
    const response = await fetch('https://api.resend.com/emails', {
      method: 'POST',
      headers: { Authorization: `Bearer ${apiKey}`, 'Content-Type': 'application/json' },
      body: JSON.stringify({
        from,
        to,
        subject,
        html,
        ...(replyTo ? { reply_to: replyTo } : {}),
      }),
    });

    const body = await response.json().catch(() => ({}));
    if (!response.ok) return { ok: false, error: body?.message || `Resend returned ${response.status}` };
    return { ok: true, id: body?.id };
  } catch (error) {
    return { ok: false, error: error?.message || 'Network error contacting Resend' };
  }
}

export { escapeHtml };
