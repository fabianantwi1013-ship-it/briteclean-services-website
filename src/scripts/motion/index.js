/**
 * Motion system entry point.
 *
 * Two paths:
 *   motion   — smooth scrolling, the intro, scroll-driven reveals, pinned sections,
 *              autoplaying slideshows.
 *   calm     — reduced-motion visitors (or a failed load): every control still works,
 *              nothing moves on its own, nothing is hidden waiting for an animation.
 *
 * Every module is wrapped so one failure cannot leave content invisible: an error
 * sets `motion-off`, which the CSS treats as "show everything as-is".
 */
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { SplitText } from 'gsap/SplitText';
import Lenis from 'lenis';

import { initHeader } from './header.js';
import { initMenu } from './menu.js';
import { initIntro } from './intro.js';
import { initHero, initPageHead } from './hero.js';
import { initReveals } from './reveals.js';
import { initRail } from './rail.js';
import { initProcess } from './process.js';
import { initReviews } from './reviews.js';
import { initMarquee } from './marquee.js';
import { initCursor, initMagnetic } from './pointer.js';
import { initFaq } from './faq.js';
import { initServicesNav } from './services-nav.js';

gsap.registerPlugin(ScrollTrigger, SplitText);

const root = document.documentElement;
window.__bcMotionReady = true;

const motion = root.classList.contains('motion') && !root.classList.contains('motion-off');

function safe(name, fn) {
  try {
    return fn();
  } catch (error) {
    console.error(`[motion] ${name} failed`, error);
    root.classList.add('motion-off');
    return null;
  }
}

/* ---- Smooth scroll -------------------------------------------------------- */

let lenis = null;
if (motion) {
  lenis = safe('lenis', () => {
    const instance = new Lenis({ lerp: 0.09, smoothWheel: true, wheelMultiplier: 1 });
    instance.on('scroll', ScrollTrigger.update);
    gsap.ticker.add((time) => instance.raf(time * 1000));
    gsap.ticker.lagSmoothing(0);
    return instance;
  });
  window.__bcLenis = lenis;
}

/**
 * In-page links: scroll through Lenis when it is running, and move focus to the
 * target so keyboard and screen-reader users land where they asked to go.
 */
document.addEventListener('click', (event) => {
  const link = event.target.closest && event.target.closest('a[href^="#"]');
  if (!link || link.hasAttribute('data-menu-toggle') || link.hasAttribute('data-menu-close')) return;
  const hash = link.getAttribute('href');
  if (hash === '#') return;
  const target = document.getElementById(decodeURIComponent(hash.slice(1)));
  if (!target) return;

  event.preventDefault();
  const toTop = target.id === 'main' && link.hasAttribute('data-to-top');
  // Lenis honours the html scroll-padding-top (header height + breathing room), so
  // no extra offset is needed for element targets.
  if (lenis) lenis.scrollTo(toTop ? 0 : target, { duration: 1.4 });
  else if (toTop) window.scrollTo({ top: 0 });
  else target.scrollIntoView();

  if (!target.hasAttribute('tabindex') && !/^(A|BUTTON|INPUT|SELECT|TEXTAREA)$/.test(target.tagName)) {
    target.setAttribute('tabindex', '-1');
  }
  target.focus({ preventScroll: true });
  if (!toTop) history.replaceState(null, '', hash);
});

/* ---- Modules -------------------------------------------------------------- */

const ctx = { gsap, ScrollTrigger, SplitText, lenis, motion };

safe('header', () => initHeader(ctx));
safe('menu', () => initMenu(ctx));

const hero = safe('hero', () => initHero(ctx));
const pageHead = safe('page head', () => initPageHead(ctx));

safe('intro', () =>
  initIntro({
    ...ctx,
    onReveal: () => {
      hero?.enter();
      pageHead?.enter();
    },
  }),
);

/*
 * Everything below the fold is set up in small slices, handing control back to the
 * browser between each one, so start-up never blocks input as one long task. The
 * rail goes first because its pin spacing moves every trigger after it.
 */
const deferred = [
  ['rail', () => initRail(ctx)],
  ['reviews', () => initReviews(ctx)],
  ['services nav', () => initServicesNav(ctx)],
  ...(motion
    ? [
        ['reveals', () => initReveals(ctx)],
        ['process', () => initProcess(ctx)],
        ['marquee', () => initMarquee(ctx)],
        ['cursor', () => initCursor(ctx)],
        ['magnetic', () => initMagnetic(ctx)],
        ['faq', () => initFaq(ctx)],
      ]
    : []),
];

const yieldToMain = () =>
  globalThis.scheduler?.yield ? globalThis.scheduler.yield() : new Promise((resolve) => setTimeout(resolve, 0));

(async () => {
  for (const [name, init] of deferred) {
    await yieldToMain();
    safe(name, init);
  }
  // One measuring pass once everything exists and the web fonts have settled the
  // layout. Each refresh re-measures every trigger, so it is kept to this single call
  // (ScrollTrigger also refreshes itself on resize, and on load if that is still to come).
  await (document.fonts?.ready ?? Promise.resolve());
  await yieldToMain();
  ScrollTrigger.refresh();
})();
