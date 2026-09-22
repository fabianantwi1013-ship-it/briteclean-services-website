# Deploying to Vercel

The site is a static Astro build plus two serverless functions. Vercel hosts both on
its free Hobby tier — no hosting plan to buy, no server to maintain.

## Architecture

```
src/pages/        → 7 pages, pre-rendered to HTML at build time, served from Vercel's CDN
src/components/   → shared layout, header, footer, sections, booking form
src/data/site.js  → every piece of business copy, in one file
src/styles/       → one stylesheet, no framework
api/booking.js    → serverless function: validates, emails owner + customer
api/contact.js    → serverless function: short enquiry, emails owner
```

**Bookings are not stored.** The form emails the business and confirms to the customer,
and that is the whole record. This is deliberate — see the warning below.

## 1. Email (do this first — nothing works without it)

The site sends through [Resend](https://resend.com). Free tier covers 3,000 emails a
month, far beyond what this site will generate.

1. Create a Resend account
2. **Add and verify `britecleanservices.com`** as a sending domain. Resend gives you
   DNS records (SPF, DKIM) to add wherever the domain's DNS is managed. Without this,
   mail either fails or lands in spam.
3. Create an API key

## 2. Deploy

Push to GitHub (already set up), then:

1. [vercel.com/new](https://vercel.com/new) → import `briteclean-services-website`
2. Framework preset: **Astro** (auto-detected)
3. Add three **Environment Variables** before the first deploy:

| Name | Value |
| --- | --- |
| `RESEND_API_KEY` | your Resend key |
| `MAIL_FROM` | `Briteclean Services <bookings@britecleanservices.com>` |
| `MAIL_TO` | `info@britecleanservices.com` (comma-separate for several) |

4. Deploy

`MAIL_FROM` must be on the domain you verified in Resend. `MAIL_TO` can be any inbox.

## 3. Point the domain

In Vercel → project → **Settings → Domains** → add `britecleanservices.com`. Vercel
shows the DNS records to set. The domain currently uses Hostinger's parking
nameservers, so change them at the registrar.

SSL is issued automatically once DNS resolves.

## ⚠️ The risk of not storing bookings

Email-only means **a delivery failure is a lost customer**. There is no database to
recover from.

Three things reduce that risk:

1. **The form never shows false success.** If the owner notification fails, the visitor
   gets an error telling them to call, rather than a thank-you page. Check
   `api/booking.js` — this is deliberate and worth preserving.
2. **Resend keeps a log** of every message for 3 days on the free tier, which is a
   de facto audit trail. Check it if a customer says they booked and you have nothing.
3. **Send to two addresses.** Set `MAIL_TO` to two inboxes at different providers —
   e.g. the business address and a personal Gmail. One provider's spam filter then
   cannot silently swallow a booking.

If bookings ever start mattering more than this setup can carry, adding a database is
a contained change: `api/booking.js` already has clean validated data at the point it
sends. Writing a row before sending is a few lines.

## Local development

```bash
npm install
npm run dev
```

Serves at `localhost:4321`. The `/api` functions do **not** run under `astro dev` —
they are Vercel's own convention. To exercise them locally, install the Vercel CLI and
run `vercel dev`, or test the handlers directly as `/tmp/bc-test/api.test.mjs` does.

## What is NOT included

Named plainly so nothing is assumed:

- **No booking dashboard.** The client reads emails. There is no list, no status
  workflow, no CSV export — all of which the previous WordPress build had.
- **No CMS.** Copy changes mean editing `src/data/site.js` and redeploying. That is a
  developer task, not something the client can do.
- **No stored history.** Searching past bookings means searching the inbox.

If any of those become a problem, they are the reasons to reconsider — not performance
or hosting cost, which this setup handles well.
