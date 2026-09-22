# Briteclean Services LLC — Website

A hand-coded WordPress theme and a custom booking plugin for Briteclean Services LLC,
a cleaning company in West Chester, Ohio.

```
cleaning website/
├── README.md                    ← you are here
├── docs/
│   ├── 01-local-setup.md        ← start here: LocalWP setup
│   ├── 02-photography.md        ← what images to add and where
│   └── 03-pre-launch.md         ← checklist before going live
└── wp-content/
    ├── themes/briteclean/       ← the theme
    └── plugins/briteclean-bookings/  ← the booking engine
```

These two folders symlink into a LocalWP site's `wp-content/`. WordPress core is never
copied into this project — only the code that is actually ours.

## Architecture decisions

**Custom booking form, not WPForms or Gravity Forms.** Multi-step is a paid feature in
both, Gravity has no free tier, and neither writes cleanly into a `bc_booking` post type
with this exact field set. Roughly 300 lines of plain PHP does the job with no licence
for the client to renew and no plugin update that can change the markup underneath us.

**Services are WooCommerce products.** WooCommerce was a requirement, and making it the
source of truth for the eight services means one place to edit them rather than a
product catalogue and a separate service list drifting apart. Checkout is fully
disabled — see below.

**Global settings live in the Customizer, not ACF.** ACF's free tier has no Options
Page (it is Pro-only), so site-wide values — phone numbers, address, hours, trust
badges — use the Customizer, which is native, free, and gives live preview. ACF free
handles page-specific content: the hero, page subtitles, testimonial details, service
icons.

**Everything degrades.** The site renders complete and correct with ACF, WooCommerce and
the plugin all inactive, falling back to hardcoded defaults in
`inc/defaults.php`. You can activate the theme on a bare install and see a finished
homepage before installing anything.

**Photography ships with the theme.** Ten Pexels-licensed placeholders (free for
commercial use, no attribution required) import automatically via the seeder, with alt
text written and each attached to the right page or product. Any image slot that is
still empty renders a branded SVG placeholder rather than a broken frame. See
[docs/02-photography.md](docs/02-photography.md).

## The theme

| Path | What it does |
| --- | --- |
| `inc/defaults.php` | All business content as PHP arrays. The fallback source of truth. |
| `inc/helpers.php` | Content accessors (`briteclean_opt`, `briteclean_services`…) and the inline SVG icon set. |
| `inc/customizer.php` | Global settings: contact, hours, value props, badges, colours, socials. |
| `inc/cpt.php` | The testimonial post type. |
| `inc/acf-fields.php` | ACF field groups, registered in code so they are version-controlled. |
| `inc/schema.php` | LocalBusiness + WebSite JSON-LD. |
| `inc/woocommerce.php` | Catalog-only mode — see below. |
| `inc/seeder.php` | Tools → Briteclean Setup. Creates pages, products, testimonials, menus, and imports the placeholder photos. |
| `assets/img/placeholders/` | Ten Pexels-licensed photos (832 KB) with a `credits.json` manifest. |
| `template-parts/` | Homepage sections, each reusable on other pages. |
| `assets/css/main.css` | The whole stylesheet. No build step. |

Page templates resolve by slug (`page-services.php`, `page-contact.php`,
`page-about.php`, `page-faq.php`), so renaming a page's slug in wp-admin will silently
fall back to `page.php`.

### WooCommerce is catalog-only

Nothing is purchasable. This is enforced in PHP, not hidden in CSS:

- `woocommerce_is_purchasable` returns false
- prices are suppressed via `woocommerce_get_price_html`
- add-to-cart is replaced with a "Request this service" link into the booking form
- `/cart/`, `/checkout/` and `/my-account/` redirect to `/book-now/`
- Woo's ~80KB of CSS and its cart-fragments AJAX are dequeued

To start taking payment later, remove the `is_purchasable` and `get_price_html` filters
in `inc/woocommerce.php` and re-run Woo's payment setup.

## The plugin

`BC_Schema` is the single source of truth for every booking field. The form markup,
server-side validation, the admin meta box, both emails and the CSV export all read
from it — add a field there and it appears everywhere, correctly, at once.

| Class | Responsibility |
| --- | --- |
| `BC_Schema` | Field and step definitions, statuses, value formatting. |
| `BC_CPT` | The `bc_booking` post type and status handling. |
| `BC_Form` | Renders the form; carries state across a failed submission. |
| `BC_Validator` | Server-side validation and sanitisation. |
| `BC_Handler` | Receives submissions, spam checks, rate limiting, storage. |
| `BC_Emails` | Owner notification and customer confirmation. |
| `BC_Admin` | List table, filters, meta boxes, inline status changes. |
| `BC_Export` | CSV export. |
| `BC_Contact` | The fallback contact form. |

### The booking flow

The form posts to `admin-post.php` and every outcome ends in a redirect, so refreshing
after submitting cannot create a duplicate. On success it stores the booking, emails
both parties, and returns to `?booking=success`. On failure it stores the errors and
the submitted values in a 10-minute transient and returns with a token in the URL, so
nobody retypes anything.

**Progressive enhancement:** every step is a real `<fieldset>`, visible by default. The
JavaScript adds an `is-enhanced` class, which is what the CSS keys the stepper off. If
the script fails to load, the visitor gets one long form that still submits and is
still validated. Client-side validation is for faster feedback only — every rule runs
again in PHP.

**Spam protection:** two layers ship enabled — a honeypot field, and a signed render
timestamp rejecting submissions that arrive impossibly fast or from a stale form. Plus
a 5-per-hour-per-IP rate limit. `BC_Handler::check_spam()` documents exactly where and
how to add reCAPTCHA or Turnstile as a third layer if those prove insufficient.

## Accessibility and SEO notes

Colour contrast against the brand red was measured, not assumed:

| Combination | Ratio | Verdict |
| --- | --- | --- |
| White on red `#C8102E` | 5.89:1 | ✅ passes AA at all sizes |
| Gold `#F5B800` on red | 3.29:1 | ⚠️ large/bold text only |
| Gold on white | 1.79:1 | ❌ never used as text |
| Ink `#1A1A1A` on gold | 9.73:1 | ✅ how gold is actually used |

So gold is a background carrying dark text, or oversized display text on red. **If the
client changes the palette in the Customizer, these need re-checking.**

Also: 48px minimum tap targets, 16px minimum font size on inputs (smaller makes iOS
zoom on focus), visible focus rings everywhere including inside the red panels, a skip
link, `prefers-reduced-motion` honoured, and the FAQ built on `<details>` so it is
keyboard-navigable for free.

SEO ships with LocalBusiness and FAQPage JSON-LD, meta descriptions, Open Graph tags,
semantic landmarks, and lazy loading everywhere except the hero (which is preloaded
with `fetchpriority="high"` as the LCP element). The meta/OG output stands down
automatically if Yoast or Rank Math is installed later.
