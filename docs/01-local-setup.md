# Briteclean Services LLC — Local Setup (LocalWP)

Do this while the theme and plugin are being built. Nothing here depends on the code
being finished.

## 1. Install Local by Flywheel

Download from https://localwp.com (free, no account strictly required — you can skip
the sign-in prompt). Install and open it.

## 2. Create the site

Click **+ Create a new site** and use these values:

| Setting        | Value                          |
| -------------- | ------------------------------ |
| Site name      | `Briteclean`                   |
| Local site domain | `briteclean.local`          |
| Environment    | **Preferred** (PHP 8.2+, nginx, MySQL 8) |
| WP username    | `admin` (or your choice)       |
| WP password    | something you'll remember      |
| WP email       | your email                     |

Do **not** enable the WordPress multisite option.

When it finishes, click **WP Admin** to confirm you can log in.

## 3. Find the site folder

In Local, right-click the site in the left sidebar → **Show Folder**. You'll land in
something like:

```
~/Local Sites/briteclean/
└── app/
    └── public/          <- this is the WordPress root
        ├── wp-admin/
        ├── wp-content/
        │   ├── plugins/
        │   └── themes/
        └── wp-config.php
```

## 4. Link this project into the site

This project keeps the theme and plugin under version-controllable folders, separate
from the WordPress core install. Symlink them in so editing here edits the live site
with no copying step.

Run this once, adjusting the path on the first line if your site folder differs:

```bash
WP_PUBLIC="$HOME/Local Sites/briteclean/app/public"
PROJECT="/Users/fabian/Movies/cleaning website"

ln -s "$PROJECT/wp-content/themes/briteclean"            "$WP_PUBLIC/wp-content/themes/briteclean"
ln -s "$PROJECT/wp-content/plugins/briteclean-bookings"  "$WP_PUBLIC/wp-content/plugins/briteclean-bookings"
```

Verify:

```bash
ls -l "$HOME/Local Sites/briteclean/app/public/wp-content/themes/"
```

You should see `briteclean -> /Users/fabian/Movies/cleaning website/...`.

> **Note on symlinks:** LocalWP handles them fine. Some managed hosts do not, so the
> migration checklist at the end covers copying the real folders up instead.

## 5. Turn on debugging

Open `wp-config.php` in the site root and find `define( 'WP_DEBUG', false );`. Replace
that single line with:

```php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
define( 'SCRIPT_DEBUG', true );
```

Errors then land in `wp-content/debug.log` instead of breaking the page layout.

## 6. Install the required plugins

In **wp-admin → Plugins → Add New**, search for and install:

1. **Advanced Custom Fields** (by WP Engine) — the free version. Activate it.
2. **WooCommerce** — activate it, then run its setup wizard. Answers that matter:
   - Industry: *Other* / Home services
   - Product types: *Physical products* (nothing digital needed)
   - Skip payment setup entirely — this install never takes payment
   - Skip Jetpack / marketing extras

Then activate, from **Plugins**:

3. **Briteclean Bookings** (this project's plugin — appears once symlinked)

And in **Appearance → Themes**, activate **Briteclean**.

> Order matters slightly: activate ACF and WooCommerce *before* the theme, so the
> theme's field groups and product setup register on first load. If you do it the
> other way round, just visit **Settings → Permalinks** and click Save to re-flush.

## 7. Flush permalinks

**Settings → Permalinks** → choose **Post name** → **Save Changes**.

This is required for `/book-now/` and the booking form's submit handler to route
correctly. If you ever get a 404 on a page you know exists, come back and re-save here.

## 8. Seed the demo content

The theme ships a one-click seeder that creates all pages, the 8 services as
WooCommerce products, placeholder testimonials, and the menus.

Go to **Tools → Briteclean Setup** and click **Seed site content**. It is safe to run
more than once — it skips anything that already exists.

---

## What you should see when this is done

- `http://briteclean.local/` — full homepage, red/gold brand, 8 services
- `http://briteclean.local/book-now/` — the multi-step booking form
- `http://briteclean.local/wp-admin/edit.php?post_type=bc_booking` — Booking Requests
- A floating WhatsApp button on every page, bottom-right
