/**
 * Pointer-only flourishes, for mouse and trackpad users. Touch devices never load
 * any of this behaviour.
 */
const finePointer = () => window.matchMedia('(hover: hover) and (pointer: fine)').matches;

/** A labelled disc that follows the pointer over photos and cards. */
export function initCursor({ gsap }) {
  const cursor = document.querySelector('.cursor');
  if (!cursor || !finePointer()) return;
  const label = cursor.querySelector('.cursor__label');

  const xTo = gsap.quickTo(cursor, 'x', { duration: 0.55, ease: 'power3' });
  const yTo = gsap.quickTo(cursor, 'y', { duration: 0.55, ease: 'power3' });

  window.addEventListener(
    'pointermove',
    (e) => {
      xTo(e.clientX);
      yTo(e.clientY);
    },
    { passive: true },
  );

  document.addEventListener('pointerover', (e) => {
    const target = e.target.closest && e.target.closest('[data-cursor]');
    if (!target) return;
    label.textContent = target.getAttribute('data-cursor');
    cursor.classList.add('is-active');
  });
  document.addEventListener('pointerout', (e) => {
    const target = e.target.closest && e.target.closest('[data-cursor]');
    if (target && !target.contains(e.relatedTarget)) cursor.classList.remove('is-active');
  });
  window.addEventListener('scroll', () => cursor.classList.remove('is-active'), { passive: true });
}

/** Primary buttons lean toward the pointer and spring back. */
export function initMagnetic({ gsap }) {
  if (!finePointer()) return;
  document.querySelectorAll('[data-magnetic]').forEach((el) => {
    const xTo = gsap.quickTo(el, 'x', { duration: 0.8, ease: 'elastic.out(1, 0.4)' });
    const yTo = gsap.quickTo(el, 'y', { duration: 0.8, ease: 'elastic.out(1, 0.4)' });
    el.addEventListener('pointermove', (e) => {
      const r = el.getBoundingClientRect();
      xTo((e.clientX - (r.left + r.width / 2)) * 0.28);
      yTo((e.clientY - (r.top + r.height / 2)) * 0.36);
    });
    el.addEventListener('pointerleave', () => {
      xTo(0);
      yTo(0);
    });
  });
}
