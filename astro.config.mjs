// @ts-check
import { defineConfig, fontProviders } from 'astro/config';

/**
 * Static output: every marketing page is pre-rendered to plain HTML at build time,
 * so Vercel serves them from its CDN with no server involved.
 *
 * The one dynamic piece, the booking form handler, lives in /api/booking.js and is
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

  /**
   * One typeface, Montserrat (variable, Latin subset), self-hosted. Astro emits the
   * @font-face rules, the preload link and a size-matched Arial fallback, so text
   * does not jump when the font arrives.
   */
  fonts: [
    {
      provider: fontProviders.local(),
      name: 'Montserrat',
      cssVariable: '--font-sans-family',
      fallbacks: ['sans-serif'],
      options: {
        variants: [
          { src: ['@fontsource-variable/montserrat/files/montserrat-latin-wght-normal.woff2'], weight: '100 900', style: 'normal' },
        ],
      },
    },
  ],
});
