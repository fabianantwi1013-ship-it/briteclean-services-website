/**
 * Reviews slideshow. One quote at a time; words resolve out of a soft blur.
 * Autoplay only with motion allowed, paused off screen, pausable at any time.
 * Manual controls work either way.
 */
const SECONDS = 8;
const pad = (n) => String(n).padStart(2, '0');

export function initReviews({ gsap, ScrollTrigger, SplitText, motion }) {
  document.querySelectorAll('[data-reviews]').forEach((root) => {
    const items = [...root.querySelectorAll('[data-review]')];
    if (items.length < 2) return;

    const stage = root.querySelector('[data-reviews-stage]');
    const prev = root.querySelector('[data-reviews-prev]');
    const next = root.querySelector('[data-reviews-next]');
    const pauseBtn = root.querySelector('[data-reviews-pause]');
    const indexEl = root.querySelector('[data-reviews-index]');
    const timerEl = root.querySelector('[data-reviews-timer]');

    let current = 0;
    let userPaused = !motion;
    let inView = false;
    let timer = null;
    const splits = new Map();

    const isPaused = () => userPaused || !inView || document.hidden;
    const parts = (item) => item.querySelectorAll('.review__stars, .review__author');

    items.forEach((item, i) => {
      item.setAttribute('aria-hidden', i === 0 ? 'false' : 'true');
      if (motion) splits.set(item, SplitText.create(item.querySelector('[data-review-quote]'), { type: 'words', tag: 'span', aria: 'none' }));
    });

    function restartTimer() {
      timer?.kill();
      timer = null;
      if (!motion || !timerEl) return;
      timer = gsap.fromTo(
        timerEl,
        { scaleX: 0 },
        { scaleX: 1, duration: SECONDS, ease: 'none', paused: isPaused(), onComplete: () => show(current + 1, 1) },
      );
    }

    function sync() {
      root.classList.toggle('is-paused', userPaused);
      pauseBtn?.setAttribute('aria-label', userPaused ? 'Play reviews' : 'Pause reviews');
      // Announce changes only when the visitor is driving them.
      stage?.setAttribute('aria-live', userPaused ? 'polite' : 'off');
      if (timer) isPaused() ? timer.pause() : timer.resume();
    }

    function show(target, dir) {
      const nextIndex = (target + items.length) % items.length;
      if (nextIndex === current) return;
      const from = items[current];
      const to = items[nextIndex];
      current = nextIndex;
      if (indexEl) indexEl.textContent = pad(current + 1);
      from.setAttribute('aria-hidden', 'true');
      to.setAttribute('aria-hidden', 'false');

      if (!motion) {
        from.classList.remove('is-active');
        to.classList.add('is-active');
        restartTimer();
        return;
      }

      const quote = to.querySelector('[data-review-quote]');
      gsap.to([from.querySelector('[data-review-quote]'), ...parts(from)], {
        opacity: 0,
        y: -18 * dir,
        duration: 0.45,
        ease: 'power2.in',
        onComplete: () => from.classList.remove('is-active'),
      });

      to.classList.add('is-active');
      gsap.set([quote, ...parts(to)], { opacity: 1, y: 0 });
      gsap.fromTo(
        splits.get(to).words,
        { opacity: 0, y: 22, filter: 'blur(8px)' },
        { opacity: 1, y: 0, filter: 'blur(0px)', duration: 1.1, ease: 'expo.out', stagger: 0.018, delay: 0.3, clearProps: 'filter' },
      );
      gsap.fromTo(parts(to), { opacity: 0, y: 14 }, { opacity: 1, y: 0, duration: 0.9, ease: 'expo.out', stagger: 0.08, delay: 0.45 });
      restartTimer();
    }

    prev?.addEventListener('click', () => show(current - 1, -1));
    next?.addEventListener('click', () => show(current + 1, 1));
    pauseBtn?.addEventListener('click', () => {
      userPaused = !userPaused;
      sync();
    });
    document.addEventListener('visibilitychange', sync);

    if (!motion) {
      pauseBtn?.setAttribute('hidden', '');
      sync();
      return;
    }

    ScrollTrigger.create({
      trigger: root,
      start: 'top 75%',
      end: 'bottom 25%',
      onToggle: (self) => {
        inView = self.isActive;
        sync();
      },
    });

    restartTimer();
    sync();
  });
}
