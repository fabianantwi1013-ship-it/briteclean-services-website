# Placeholder Photography

Ten Pexels photographs ship with the theme in
`wp-content/themes/briteclean/assets/img/placeholders/` (832 KB total). They are
imported automatically — **Tools → Briteclean Setup → Seed site content** copies them
into the Media Library, writes alt text, and attaches each one to the right page or
product.

Running the seeder twice does not duplicate them, and it never overwrites an image the
client has already set — theirs always wins.

## Licence

All ten are **Pexels License**: free for commercial use, no attribution required,
modification permitted. Full manifest with source IDs and per-image alt text is in
`assets/img/placeholders/credits.json`. Each imported attachment also carries a
`_briteclean_source` meta value recording its Pexels ID, so provenance survives even if
the manifest is lost.

## What ships

| File | Goes to | Shows |
| --- | --- | --- |
| `hero-kitchen-counter.jpg` | Homepage hero | Pink-gloved hands wiping a white shelf with a spray bottle |
| `about-team.jpg` | About page | Three cleaners in red uniforms with equipment in a bright living room |
| `service-residential-cleaning.jpg` | Residential Cleaning | Cleaner mopping a modern open-plan home |
| `service-office-cleaning.jpg` | Office Cleaning | Wiping a wooden desk with multi-purpose cleaner |
| `service-deep-cleaning.jpg` | Deep Cleaning | Detail-cleaning a kitchen stove in rubber gloves |
| `service-commercial-cleaning.jpg` | Commercial Cleaning | Janitorial cart in a bright commercial hallway |
| `service-window-cleaning.jpg` | Window Cleaning | Red gloves spraying and wiping a window pane |
| `service-move-in-cleaning.jpg` | Move-In Cleaning | Empty apartment room, cleaned and ready |
| `service-general-cleaning.jpg` | General Cleaning | Cleaner in red uniform with a bucket of supplies |
| `service-emergency-cleaning.jpg` | Emergency Cleaning | Crew carrying equipment up to a house on a call-out |

The hero and About images are 1600×1000; the eight service images are 900×675, matching
the registered `briteclean-hero` and `briteclean-card` crops so nothing is upscaled.

Several were chosen from the same shoot — the red-uniformed team — because red uniforms
happen to match the brand palette, and a consistent crew reads as one company rather
than a stock-photo grab bag.

## Replacing them with the client's photos

Set a featured image on the product (or page), or pick a new hero under
**Pages → Home → Homepage Hero**. The placeholder is only used when the slot is empty,
so simply setting the real photo replaces it. The old attachments can then be deleted
from the Media Library.

### What to brief the client to shoot

- **Bright and daylight-lit.** No dim or moody interiors.
- **Real people mid-task** — gloved hands, spray bottle, microfibre cloth.
- **Modern, tidy interiors.** The result being sold, not the mess.
- **Warm colour temperature.** Cold blue-grey photos fight the red and gold.
- Faces optional; hands-and-surfaces shots crop more reliably across breakpoints.
- Branded aprons or polos in the company red, if they have them — that is what makes
  the photos look like *this* company rather than stock.

Avoid: cartoon or 3D-rendered cleaners, heavy filters, visible competitor branding,
hazmat suits (reads as biohazard work, not cleaning), and rope-access or high-rise
window work unless the business genuinely offers it.

### Before uploading

1. **Resize** — nothing above 2000px on the long edge.
2. **Convert to WebP** where possible, roughly 30% smaller than JPEG at equal quality.
   `Squoosh.app` does both in the browser, free, no account.
3. **Write alt text in the Media Library.** The theme falls back to a placeholder
   caption when alt text is missing, but that is a safety net, not a substitute.
