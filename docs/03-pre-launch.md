# Pre-Launch Checklist

What is left before this can move from LocalWP to live hosting. Grouped by whether it
blocks launch.

---

## 🔴 Blocks launch

### Email delivery — the most important item here

`wp_mail()` uses PHP's `mail()` by default. On most hosts that lands in spam or is
dropped silently. **Every booking would still be saved, but nobody would be told about
it** — which defeats the point of the whole system.

- [ ] Install an SMTP plugin (**WP Mail SMTP** or **FluentSMTP**, both free)
- [ ] Connect a real mailbox — Google Workspace, Microsoft 365, or a transactional
      service like Brevo or Postmark
- [ ] Send a test to a Gmail address **and** an Outlook address; confirm neither lands
      in spam
- [ ] Set the booking notification recipients in **Customize → Briteclean Settings →
      Contact Details**
- [ ] Submit a real test booking and confirm *both* emails arrive

The plugin shows an admin warning if notification emails fail, so you will not find out
weeks later — but configure this properly before launch rather than relying on it.

### SSL

- [ ] Issue a certificate (Let's Encrypt is free and included with most hosts)
- [ ] Set both **Site Address** and **WordPress Address** to `https://` in Settings → General
- [ ] Force HTTPS (host-level redirect preferred, or the Really Simple SSL plugin)
- [ ] Fix any mixed-content warnings — the embedded Google Map is the likely culprit

### Content the client must supply

- [ ] Replace all three placeholder testimonials with real reviews *(they are labelled
      "Sample Review — replace before launch" precisely so this cannot be missed)*
- [ ] Rewrite the About page story — the shipped copy is flagged as placeholder on the
      page itself
- [ ] Confirm the business email address (currently the assumed
      `info@britecleanservices.com`) and that the mailbox actually exists
- [ ] Confirm the opening hours are correct
- [ ] Replace the ten Pexels placeholder photos with the client's own — see
      [02-photography.md](02-photography.md). They are correctly licensed for
      commercial use, so launching with them is legal, just generic.
- [ ] Add a logo in Customize → Site Identity

### Legal

- [ ] Privacy policy page — the form collects names, phone numbers, emails and **home
      addresses**, so this is not optional
- [ ] Link it from the footer and from the booking form's consent line
      (`BC_Form::render_review_step()`)
- [ ] Decide a retention period for old bookings and actually apply it

---

## 🟡 Do it at migration

### Moving the site

- [ ] Migrate with **All-in-One WP Migration** or **Duplicator** (both have free tiers
      that handle a site this size comfortably)
- [ ] **Copy the real theme and plugin folders — do not carry the symlinks across.**
      Many managed hosts do not follow symlinks:
      ```bash
      rsync -av --copy-links wp-content/themes/briteclean/ user@host:.../wp-content/themes/briteclean/
      ```
- [ ] Re-save **Settings → Permalinks** on the live site (booking submission 404s
      otherwise)
- [ ] Re-run **Tools → Briteclean Setup** if any pages did not survive the move

### Hosting configuration

- [ ] PHP 8.2 or 8.3
- [ ] `memory_limit` at least 256M
- [ ] Object caching if offered (Redis or Memcached)
- [ ] Daily automated backups, retained at least 30 days, **restore tested once** — an
      untested backup is a guess
- [ ] Staging environment for future changes

### Search engines

- [ ] Uncheck Settings → Reading → "Discourage search engines" *(LocalWP often sets this)*
- [ ] Google Search Console: verify, submit the sitemap
- [ ] Google Business Profile: claim it, and make the name, address and phone match the
      footer **character for character** — inconsistent NAP data measurably hurts local
      ranking, and the JSON-LD in `inc/schema.php` reads from the same Customizer values
      the footer does, so fix it in one place
- [ ] Validate the structured data at `search.google.com/test/rich-results`

---

## 🟢 Worth doing soon after

### Security

- [ ] Strong admin password + 2FA (Wordfence or Solid Security, free tiers are fine)
- [ ] Rename or lock down `/wp-admin` access if the host supports it
- [ ] Disable file editing — add to `wp-config.php`:
      ```php
      define( 'DISALLOW_FILE_EDIT', true );
      ```
- [ ] Set up automatic WordPress and plugin updates
- [ ] Add reCAPTCHA or Cloudflare Turnstile **if** the honeypot and time-trap start
      letting spam through. `BC_Handler::check_spam()` documents exactly where the code
      goes. Do not add it pre-emptively — it costs conversions and accessibility.

### Performance

- [ ] Caching plugin (WP Super Cache, or whatever the host provides)
- [ ] Cloudflare or the host's CDN
- [ ] Run PageSpeed Insights on mobile and fix whatever it finds — the theme is already
      light, so remaining wins will be image weight

### Analytics

- [ ] Google Analytics 4 or a privacy-friendly alternative (Plausible, Fathom)
- [ ] Track booking form submissions as a conversion — the success state is reachable at
      `?booking=success`, which is trivial to configure as a goal
- [ ] Track WhatsApp button clicks; for this business that is likely the highest-volume
      conversion path and it is easy to under-measure

### Handover

- [ ] Walk the client through: Tools → Briteclean Setup, Bookings, Customize →
      Briteclean Settings, editing services as Products
- [ ] Show them the CSV export under Bookings → Export CSV
- [ ] Confirm the booking status workflow is how they actually work
      (New → Contacted → Confirmed → Completed)

---

## Not built, deliberately

Worth naming so nobody assumes otherwise:

- **No real-time availability calendar.** Bookings are requests, confirmed manually —
  that was the specified flow.
- **No online payment.** WooCommerce is installed but checkout is disabled in PHP.
- **No customer accounts or booking history.** Each request stands alone.
- **No SMS notifications.** Feasible via Twilio if the client wants them; a couple of
  hours' work hooking `briteclean_booking_created`.
- **No recurring-booking automation.** The form captures a frequency preference; acting
  on it is a manual process.
