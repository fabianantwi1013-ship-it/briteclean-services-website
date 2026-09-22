# Choosing a Host & Going Live

This site is a standard WordPress install. It needs a host that runs **PHP 8.2+ with
MySQL** — which is most shared WordPress hosting. It does *not* run on Vercel, Netlify,
GitHub Pages or Cloudflare Pages: those serve static files and serverless JavaScript,
and WordPress needs a persistent PHP runtime, a database and a writable uploads folder.

## What this site actually needs

| Requirement | Why |
| --- | --- |
| PHP **8.2 or newer** | The theme and plugin declare `Requires PHP: 8.0`; 8.2 is what everything was verified on |
| MySQL 5.7+ / MariaDB 10.4+ | WordPress, WooCommerce and the booking records |
| Free SSL (Let's Encrypt) | The booking form collects names, phones and home addresses |
| ~500 MB disk | WordPress + WooCommerce + uploads, with room to grow |
| **Reliable outbound email** | See the warning below — this is the one that bites |
| Daily backups | Booking requests are business records |

Resource needs are modest. This is roughly ten pages and a form; it does not need a
VPS, a CDN, or edge distribution.

## Suggested hosts

| Host | Cost | Good for |
| --- | --- | --- |
| **Cloudways** | ~$11–14/mo | Client work. Managed cloud on DigitalOcean/Vultr, proper staging, easy PHP version switching, no renewal-price jump. Best balance here. |
| **SiteGround** | ~$3 intro, ~$15 renewal | Easy for a non-technical client to log into. Good support. Watch the renewal price. |
| **Hostinger** | ~$3/mo | Tight budgets. Fine for a site this size; less headroom if it grows. |
| **WP Engine** | ~$20–30/mo | If the client will pay for premium managed WordPress and wants someone else on the hook. |

Avoid the very cheapest unlimited-everything shared plans — they oversell, and slow
admin makes the booking dashboard unpleasant to work in daily.

## ⚠️ Email is the thing that will break

`wp_mail()` falls back to PHP's `mail()`, which most shared hosts either drop silently
or deliver straight to spam. **The booking would still be saved, but nobody would be
told about it** — which defeats the whole system.

Fix this before launch, not after:

1. Install **WP Mail SMTP** or **FluentSMTP** (both free)
2. Connect a real mailbox — Google Workspace, Microsoft 365, or a transactional
   service like Brevo or Postmark (both have free tiers)
3. Send a test to a Gmail address **and** an Outlook address
4. Submit a real booking and confirm both emails arrive

The plugin raises an admin warning when notification email fails, so you will not
discover this weeks later — but configure it properly rather than relying on the
warning.

## Deploying

`./bin/package.sh` builds two installable zips into `dist/`:

```bash
./bin/package.sh
```

Then on the live site, in this order:

1. **Plugins → Add New → Search** — install and activate **Advanced Custom Fields**
2. **Plugins → Add New → Search** — install and activate **WooCommerce**
   (skip its payment setup entirely; this install never takes payment)
3. **Plugins → Add New → Upload** — `dist/briteclean-bookings.zip`, activate
4. **Appearance → Themes → Add New → Upload** — `dist/briteclean-theme.zip`, activate
5. **Settings → Permalinks** → **Post name** → Save
6. **Tools → Briteclean Setup** → *Seed site content*

That creates the pages, the eight services as products, testimonials, menus, and
imports the photos and logo. It is safe to run more than once.

### Updating later

Re-run `./bin/package.sh` and upload again — WordPress will offer to replace the
existing copy. Or, if the host gives you SSH and git, clone the repo and symlink
`wp-content/themes/briteclean` and `wp-content/plugins/briteclean-bookings`, which
makes future updates a `git pull`.

## After it is live

Work through [03-pre-launch.md](03-pre-launch.md) — SSL, backups, search engine
visibility, Google Business Profile, analytics, and the content the client still owes
you (real reviews, the About story, their own photos).
