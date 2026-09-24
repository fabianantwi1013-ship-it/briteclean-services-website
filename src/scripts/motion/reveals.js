/**
 * Scroll-driven reveals.
 *
 *   [data-split]        headings rise line by line from behind a mask
 *   [data-reveal]       blocks fade up, batched so neighbours stagger
 *   [data-reveal-img]   photos wipe open upward while the image settles from a zoom
 *   [data-parallax]     inner image layers drift against the scroll
 *   [data-scrub-words]  a paragraph brightens word by word with the scroll position
 *   [data-count]        numbers count up when they come into view
 */
/**
 * SplitText's default labels the element and hides the pieces, which is right for a
 * heading but not allowed on a paragraph. Split paragraphs keep their real text.
 */
export const ariaFor = (el) => (/^H[1-6]$/.test(el.tagName) ? 'auto' : 'none');

export function initReveals({ gsap, ScrollTrigger, SplitText }) {
  const radius = getComputedStyle(document.documentElement).getPropertyValue('--radius-l').trim() || '28px';

  document.querySelectorAll('[data-split]').forEach((el) => {
    gsap.set(el, { visibility: 'visible' });
    SplitText.create(el, {
      type: 'lines',
      tag: 'span',
      aria: ariaFor(el),
      mask: 'lines',
      linesClass: 'split-line',
      autoSplit: true,
      onSplit: (self) =>
        gsap.from(self.lines, {
          yPercent: 118,
          duration: 1.35,
          ease: 'expo.out',
          stagger: 0.1,
          scrollTrigger: { trigger: el, start: 'top 88%', once: true },
        }),
    });
  });

  ScrollTrigger.batch('[data-reveal]', {
    start: 'top 90%',
    once: true,
    onEnter: (batch) => gsap.to(batch, { opacity: 1, y: 0, duration: 1.2, ease: 'expo.out', stagger: 0.09, overwrite: true }),
  });

  document.querySelectorAll('[data-reveal-img]').forEach((frame) => {
    const inner = frame.querySelector('.parallax, .pic, iframe');
    const round = frame.classList.contains('why__arch') ? `999px 999px ${radius} ${radius}` : radius;
    const tl = gsap.timeline({ scrollTrigger: { trigger: frame, start: 'top 86%', once: true } });
    tl.fromTo(
      frame,
      { clipPath: `inset(100% 0% 0% 0% round ${round})` },
      { clipPath: `inset(0% 0% 0% 0% round ${round})`, duration: 1.6, ease: 'expo.inOut' },
    );
    if (inner) tl.from(inner, { scale: 1.3, duration: 2.1, ease: 'expo.out' }, 0.15);
  });

  // Parallax travel is measured from the layout, so it can never expose an edge.
  document.querySelectorAll('[data-parallax]').forEach((el) => {
    const frame = el.parentElement;
    const travel = () => Math.max(0, (el.offsetHeight - frame.offsetHeight) / 2) * 0.92;
    gsap.fromTo(
      el,
      { y: () => -travel() },
      {
        y: () => travel(),
        ease: 'none',
        scrollTrigger: { trigger: frame, start: 'top bottom', end: 'bottom top', scrub: true, invalidateOnRefresh: true },
      },
    );
  });

  document.querySelectorAll('[data-scrub-words]').forEach((el) => {
    const { words } = SplitText.create(el, { type: 'words', wordsClass: 'word', tag: 'span', aria: 'none' });
    // Words brighten from slate to ink. The starting slate still clears the 3:1 WCAG
    // minimum for large text, so the paragraph is readable at every scroll position.
    gsap.fromTo(
      words,
      { color: '#848EA0' },
      {
        color: '#0A1830',
        ease: 'none',
        stagger: 0.1,
        scrollTrigger: { trigger: el, start: 'top 82%', end: 'bottom 48%', scrub: 0.6 },
      },
    );
  });

  document.querySelectorAll('[data-count]').forEach((el) => {
    const end = parseFloat(el.dataset.count);
    if (!end) return;
    const value = { n: 0 };
    el.textContent = '0';
    gsap.to(value, {
      n: end,
      duration: 1.8,
      ease: 'power3.out',
      scrollTrigger: { trigger: el, start: 'top 92%', once: true },
      onUpdate: () => (el.textContent = String(Math.round(value.n))),
    });
  });

  const mega = document.querySelector('[data-footer-mega]');
  if (mega) {
    const { chars } = SplitText.create(mega, { type: 'chars', tag: 'span', aria: 'none' });
    gsap.from(chars, {
      yPercent: 70,
      opacity: 0,
      duration: 1.6,
      ease: 'expo.out',
      stagger: 0.045,
      scrollTrigger: { trigger: mega, start: 'top 98%', once: true },
    });
  }
}
