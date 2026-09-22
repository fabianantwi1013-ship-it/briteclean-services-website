// @ts-check
import { defineConfig } from 'astro/config';

/**
 * Static output: every marketing page is pre-rendered to plain HTML at build time,
 * so Vercel serves them from its CDN with no server involved.
 *
 * The one dynamic piece — the booking form handler — lives in /api/booking.js and is
 * picked up by Vercel's own serverless-function convention, which is why no adapter
 * is needed here.
 */
export default defineConfig({
  site: 'https://britecleanservices.com',
  output: 'static',
  trailingSlash: 'ignore',
  build: {
    inlineStylesheets: 'auto',
  },
  devToolbar: {
    enabled: false,
  },
});
