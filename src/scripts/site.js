/**
 * Site behaviour, in plain JavaScript with no libraries:
 *   header   solid background once the page scrolls
 *   menu     full-screen menu on small screens, focus-trapped, Escape to close
 *   intro    skip button for the homepage title sequence (the sequence itself is CSS)
 *   hero     diagonal-wipe slideshow; each progress bar's CSS animation advances it
 *   fader    small crossfading photo slideshows (About page)
 *   reveal   sections below the fold fade in once as they scroll into view
 *
 * Scrolling is always the browser's own. Nothing here runs on every scroll frame
 * except one cheap class toggle.
 */

const root = document.documentElement;
const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

/* ---- Header ---------------------------------------------------------------- */

const header = document.querySelector('[data-header]');
const whatsapp = document.querySelector('[data-wa-float]');
const hasHero = !!document.querySelector('[data-hero]');

function onScroll() {
  const y = window.scrollY;
  header?.classList.toggle('is-scrolled', y > 24);
  // On the homepage the WhatsApp button waits until the hero has scrolled away, so
  // it never covers the hero's own buttons on a phone.
  if (hasHero) whatsapp?.classList.toggle('is-hidden', y < window.innerHeight * 0.5);
}
window.addEventListener('scroll', onScroll, { passive: true });
onScroll();

/* ---- Menu ------------------------------------------------------------------ */

const toggle = document.querySelector('[data-menu-toggle]');
const menu = document.querySelector('[data-menu]');
const close = menu?.querySelector('[data-menu-close]');

if (toggle && menu) {
  if (location.hash === '#site-menu') history.replaceState(null, '', location.pathname + location.search);
  const behind = [header, document.getElementById('main'), document.querySelector('.site-footer'), whatsapp].filter(Boolean);
  let open = false;

  const setOpen = (value, restoreFocus = true) => {
    open = value;
    menu.classList.toggle('is-open', value);
    root.classList.toggle('menu-open', value);
    toggle.setAttribute('aria-expanded', String(value));
    behind.forEach((el) => (el.inert = value));
    if (value) close?.focus();
    else if (restoreFocus) toggle.focus();
  };

  toggle.addEventListener('click', (e) => {
    e.preventDefault();
    setOpen(!open);
  });
  close?.addEventListener('click', (e) => {
    e.preventDefault();
    setOpen(false);
  });
  menu.addEventListener('click', (e) => {
    if (e.target.closest('a[href]') && e.target.closest('a') !== close) setOpen(false, false);
  });
  document.addEventListener('keydown', (e) => {
    if (!open) return;
    if (e.key === 'Escape') return setOpen(false);
    if (e.key !== 'Tab') return;
    const items = [...menu.querySelectorAll('a[href]')];
    const first = items[0];
    const last = items[items.length - 1];
    if (e.shiftKey && document.activeElement === first) {
      e.preventDefault();
      last.focus();
    } else if (!e.shiftKey && document.activeElement === last) {
      e.preventDefault();
      first.focus();
    }
  });
  window.matchMedia('(min-width: 1000px)').addEventListener('change', (e) => e.matches && open && setOpen(false, false));
}

/* ---- Intro ------------------------------------------------------------------ */

const skip = document.querySelector('[data-intro-skip]');
const endIntro = () => {
  clearTimeout(window.__bcIntroEnd);
  root.classList.remove('intro-on', 'intro-played');
};
skip?.addEventListener('click', endIntro);
if (root.classList.contains('intro-on')) {
  document.addEventListener('keydown', (e) => e.key === 'Escape' && endIntro(), { once: true });
}

/* ---- Hero slideshow ---------------------------------------------------------- */

const hero = document.querySelector('[data-hero]');
if (hero) {
  const slides = [...hero.querySelectorAll('[data-slide]')];
  const bars = [...hero.querySelectorAll('[data-dot]')];
  const pause = hero.querySelector('[data-pause]');
  const controls = hero.querySelector('[data-hero-controls]');
  const edge = hero.querySelector('[data-edge]');
  let current = 0;
  let paused = reduceMotion;

  const show = (i) => {
    const next = (i + slides.length) % slides.length;
    if (next === current) return;
    const prev = slides[current];
    current = next;
    slides.forEach((s) => s.classList.remove('is-first'));
    prev.classList.remove('is-active');
    prev.classList.add('is-prev');
    slides[current].classList.add('is-active');
    bars.forEach((b, k) => b.setAttribute('aria-pressed', String(k === current)));
    if (edge && !reduceMotion) {
      edge.classList.remove('is-running');
      void edge.offsetWidth; // restart the sweep
      edge.classList.add('is-running');
    }
    setTimeout(() => prev.classList.remove('is-prev'), reduceMotion ? 0 : 1300);
  };

  const sync = () => {
    hero.classList.toggle('is-paused', paused);
    pause?.setAttribute('aria-label', paused ? 'Play slideshow' : 'Pause slideshow');
  };

  if (slides.length > 1 && controls) {
    controls.hidden = false;
    bars.forEach((bar, i) => bar.addEventListener('click', () => show(i)));
    pause?.addEventListener('click', () => { paused = !paused; sync(); });
    // The active bar's fill animation ending is the cue for the next photo.
    controls.addEventListener('animationend', (e) => {
      if (e.animationName === 'hero-progress' && !paused && !reduceMotion) show(current + 1);
    });
    if ('IntersectionObserver' in window) {
      new IntersectionObserver(([entry]) => hero.classList.toggle('is-offscreen', !entry.isIntersecting)).observe(hero);
    }
    sync();
  }
}

/* ---- Crossfading photo slideshows ------------------------------------------- */

document.querySelectorAll('[data-fader]').forEach((fader) => {
  const frames = [...fader.children];
  if (frames.length < 2 || reduceMotion) return;
  let i = 0;
  let timer = null;
  const run = (on) => {
    clearInterval(timer);
    if (on) timer = setInterval(() => {
      frames[i].classList.remove('is-active');
      i = (i + 1) % frames.length;
      frames[i].classList.add('is-active');
    }, 4500);
  };
  if ('IntersectionObserver' in window) {
    new IntersectionObserver(([entry]) => run(entry.isIntersecting && !document.hidden)).observe(fader);
  }
});

/* ---- Reveal on scroll -------------------------------------------------------- */

// Only content that starts below the fold fades in. Anything visible when the page
// opens is shown immediately, and nothing is hidden unless this script is running.
if (!reduceMotion && 'IntersectionObserver' in window) {
  const io = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        entry.target.classList.remove('is-pending');
        io.unobserve(entry.target);
      });
    },
    { rootMargin: '0px 0px -6% 0px' },
  );
  document.querySelectorAll('[data-reveal]').forEach((el) => {
    if (el.getBoundingClientRect().top < window.innerHeight) return;
    el.classList.add('is-pending');
    io.observe(el);
  });
}
