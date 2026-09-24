/**
 * Full-screen menu.
 *
 * The toggle is a link to #site-menu so the menu opens with CSS :target when scripts
 * are off. Here it becomes a proper disclosure: aria-expanded, focus moved into the
 * dialog and trapped there, Escape to close, the page behind made inert, and focus
 * returned to the toggle afterwards.
 */
export function initMenu({ lenis }) {
  const toggle = document.querySelector('[data-menu-toggle]');
  const menu = document.querySelector('[data-menu]');
  if (!toggle || !menu) return;

  const root = document.documentElement;
  const close = menu.querySelector('[data-menu-close]');
  const background = [document.querySelector('[data-header]'), document.getElementById('main'), document.querySelector('.site-footer'), document.querySelector('[data-wa-float]')].filter(Boolean);

  close?.setAttribute('role', 'button');
  close?.setAttribute('aria-label', 'Close menu');

  // Arriving with #site-menu in the address bar (the no-JS behaviour) — tidy it up.
  if (location.hash === '#site-menu') history.replaceState(null, '', location.pathname + location.search);

  let open = false;

  const focusables = () =>
    [...menu.querySelectorAll('a[href], button:not([disabled])')].filter((el) => el.offsetParent !== null || el === close);

  function setOpen(value, { restoreFocus = true } = {}) {
    if (open === value) return;
    open = value;
    menu.classList.toggle('is-open', value);
    root.classList.toggle('menu-open', value);
    toggle.setAttribute('aria-expanded', String(value));
    background.forEach((el) => (el.inert = value));

    if (value) {
      lenis?.stop();
      // Wait for the wipe to start so the focus ring does not flash before it.
      setTimeout(() => close?.focus(), 60);
    } else {
      lenis?.start();
      if (restoreFocus) toggle.focus();
    }
  }

  toggle.addEventListener('click', (event) => {
    event.preventDefault();
    setOpen(!open);
  });

  close?.addEventListener('click', (event) => {
    event.preventDefault();
    setOpen(false);
  });

  menu.addEventListener('click', (event) => {
    const link = event.target.closest('a[href]');
    if (link && link !== close) setOpen(false, { restoreFocus: false });
  });

  document.addEventListener('keydown', (event) => {
    if (!open) return;
    if (event.key === 'Escape') {
      event.preventDefault();
      setOpen(false);
      return;
    }
    if (event.key !== 'Tab') return;
    const items = focusables();
    if (!items.length) return;
    const first = items[0];
    const last = items[items.length - 1];
    if (event.shiftKey && document.activeElement === first) {
      event.preventDefault();
      last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
      event.preventDefault();
      first.focus();
    }
  });

  // Crossing into the desktop layout, where the menu button is hidden.
  window.matchMedia('(min-width: 1100px)').addEventListener('change', (e) => e.matches && setOpen(false, { restoreFocus: false }));
}
