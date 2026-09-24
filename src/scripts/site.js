/**
 * Site behaviour, in plain JavaScript with no libraries:
 *   header   solid background once the page scrolls
 *   menu     full-screen menu on small screens, focus-trapped, Escape to close
 *   hero     slow crossfade slideshow with dots and a pause button
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

/* ---- Hero slideshow ---------------------------------------------------------- */

const hero = document.querySelector('[data-hero]');
if (hero) {
  const slides = [...hero.querySelectorAll('[data-slide]')];
  const dots = [...hero.querySelectorAll('[data-dot]')];
  const pause = hero.querySelector('[data-pause]');
  const controls = hero.querySelector('[data-hero-controls]');
  const INTERVAL = 6000;
  let current = 0;
  let timer = null;
  let paused = reduceMotion;
  let visible = true;

  const show = (i) => {
    current = (i + slides.length) % slides.length;
    slides.forEach((s, k) => s.classList.toggle('is-active', k === current));
    dots.forEach((d, k) => d.setAttribute('aria-pressed', String(k === current)));
  };

  const schedule = () => {
    clearInterval(timer);
    timer = null;
    if (!paused && visible && !document.hidden) timer = setInterval(() => show(current + 1), INTERVAL);
    hero.classList.toggle('is-paused', paused);
    pause?.setAttribute('aria-label', paused ? 'Play slideshow' : 'Pause slideshow');
  };

  if (slides.length > 1 && controls) {
    controls.hidden = false;
    dots.forEach((dot, i) => dot.addEventListener('click', () => { show(i); schedule(); }));
    pause?.addEventListener('click', () => { paused = !paused; schedule(); });
    document.addEventListener('visibilitychange', schedule);
    if ('IntersectionObserver' in window) {
      new IntersectionObserver(([entry]) => { visible = entry.isIntersecting; schedule(); }).observe(hero);
    }
    schedule();
  }
}

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
