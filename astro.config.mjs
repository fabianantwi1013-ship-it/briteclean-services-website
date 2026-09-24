// @ts-check
import { defineConfig, fontProviders } from 'astro/config';

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

  /**
   * Self-hosted fonts, Latin subset only. Astro emits the @font-face rules, the
   * preload links, and metric-matched fallback faces (sized Times New Roman / Arial),
   * so text does not jump when the real fonts arrive.
   */
  fonts: [
    {
      provider: fontProviders.local(),
      name: 'Instrument Serif',
      cssVariable: '--font-serif-family',
      fallbacks: ['serif'],
      options: {
        variants: [
          { src: ['@fontsource/instrument-serif/files/instrument-serif-latin-400-normal.woff2'], weight: 400, style: 'normal' },
          { src: ['@fontsource/instrument-serif/files/instrument-serif-latin-400-italic.woff2'], weight: 400, style: 'italic' },
        ],
      },
    },
    {
      provider: fontProviders.local(),
      name: 'Manrope',
      cssVariable: '--font-sans-family',
      fallbacks: ['sans-serif'],
      options: {
        variants: [
          { src: ['@fontsource-variable/manrope/files/manrope-latin-wght-normal.woff2'], weight: '200 800', style: 'normal' },
        ],
      },
    },
  ],
});
