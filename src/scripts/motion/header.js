/**
 * Header states: transparent over the opening image, frosted white once the page
 * scrolls, tucked away while scrolling down and back the moment you scroll up.
 */
export function initHeader() {
  const header = document.querySelector('[data-header]');
  if (!header) return;

  const root = document.documentElement;
  let lastY = window.scrollY;
  let queued = false;

  // The WhatsApp button waits until the opening image has scrolled away, so it
  // never sits on top of the hero's own calls to action.
  const whatsapp = document.querySelector('[data-wa-float]');

  const update = () => {
    queued = false;
    const y = Math.max(0, window.scrollY);
    header.classList.toggle('is-scrolled', y > 40);
    whatsapp?.classList.toggle('is-tucked', y < window.innerHeight * 0.45);

    if (root.classList.contains('menu-open')) {
      lastY = y;
      return;
    }
    if (y > window.innerHeight * 0.6 && y > lastY + 6) header.classList.add('is-hidden');
    else if (y < lastY - 6 || y < 200) header.classList.remove('is-hidden');
    lastY = y;
  };

  window.addEventListener(
    'scroll',
    () => {
      if (queued) return;
      queued = true;
      requestAnimationFrame(update);
    },
    { passive: true },
  );

  // Never leave keyboard focus inside a header that has slid off screen.
  header.addEventListener('focusin', () => header.classList.remove('is-hidden'));
  update();
}
