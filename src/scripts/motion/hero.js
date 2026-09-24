/**
 * Homepage hero slideshow and inner-page header entrances.
 *
 * Slides change with a squeegee wipe: the incoming photo is uncovered left to right
 * behind a glowing light-green edge, while the outgoing one drifts away. Each photo
 * then drifts slowly (Ken Burns) while a progress bar fills.
 *
 * Autoplay runs only with motion allowed, pauses when the hero leaves the screen or
 * the tab is hidden, and can always be stopped with the pause button (WCAG 2.2.2).
 */

const SLIDE_SECONDS = 6.5;
const pad = (n) => String(n).padStart(2, '0');

export function initHero({ gsap, ScrollTrigger, SplitText, motion }) {
  const hero = document.querySelector('[data-hero]');
  if (!hero) return null;

  const slides = [...hero.querySelectorAll('[data-slide]')];
  const imgs = slides.map((s) => s.querySelector('[data-slide-img]'));
  const dots = [...hero.querySelectorAll('[data-hero-dot]')];
  const fills = dots.map((d) => d.querySelector('[data-hero-fill]'));
  const pauseBtn = hero.querySelector('[data-hero-pause]');
  const indexEl = hero.querySelector('[data-hero-index]');
  const labelEl = hero.querySelector('[data-hero-label]');
  const edge = hero.querySelector('[data-hero-edge]');
  const title = hero.querySelector('[data-hero-title]');
  const items = hero.querySelectorAll('[data-hero-item]');
  const labels = slides.map((s) => (s.getAttribute('aria-label') || '').split(': ')[1] || '');

  let current = 0;
  let busy = false;
  let timer = null;
  let userPaused = !motion;
  let inView = true;
  let entered = false;

  const isPaused = () => userPaused || !inView || document.hidden;

  function setMeta(i) {
    if (indexEl) indexEl.textContent = pad(i + 1);
    if (labelEl) labelEl.textContent = labels[i];
    dots.forEach((d, k) => (k === i ? d.setAttribute('aria-current', 'true') : d.removeAttribute('aria-current')));
    slides.forEach((s, k) => s.setAttribute('aria-hidden', k === i ? 'false' : 'true'));
  }

  function kenBurns(img) {
    gsap.killTweensOf(img, 'scale,xPercent');
    gsap.to(img, { scale: 1, xPercent: 0, duration: SLIDE_SECONDS + 2, ease: 'none' });
  }

  function startTimer() {
    timer?.kill();
    fills.forEach((f, k) => gsap.set(f, { scaleX: k < current ? 1 : 0 }));
    if (!motion) {
      gsap.set(fills[current], { scaleX: 1 });
      return;
    }
    timer = gsap.fromTo(
      fills[current],
      { scaleX: 0 },
      { scaleX: 1, duration: SLIDE_SECONDS, ease: 'none', paused: isPaused(), onComplete: () => go(current + 1, 1) },
    );
  }

  function syncPlayback() {
    hero.classList.toggle('is-paused', userPaused);
    if (pauseBtn) pauseBtn.setAttribute('aria-label', userPaused ? 'Play slideshow' : 'Pause slideshow');
    if (!timer) return;
    if (isPaused()) timer.pause();
    else timer.resume();
  }

  function go(target, dir) {
    const next = (target + slides.length) % slides.length;
    if (next === current || busy) return;
    const direction = dir || (next > current ? 1 : -1);
    const from = slides[current];
    const to = slides[next];
    const fromImg = imgs[current];
    const toImg = imgs[next];

    current = next;
    setMeta(next);

    from.classList.add('is-leaving');
    from.classList.remove('is-active');
    to.classList.add('is-active');
    gsap.set(to, { zIndex: 2 });
    gsap.set(from, { zIndex: 1 });

    const done = () => {
      from.classList.remove('is-leaving');
      gsap.set([from, to], { clearProps: 'zIndex,clipPath' });
      gsap.set(fromImg, { filter: 'none' });
      busy = false;
    };

    if (!motion) {
      done();
      startTimer();
      return;
    }

    busy = true;
    const width = hero.offsetWidth;
    const hidden = direction > 0 ? 'inset(0% 100% 0% 0%)' : 'inset(0% 0% 0% 100%)';

    gsap
      .timeline({ onComplete: done })
      .fromTo(to, { clipPath: hidden }, { clipPath: 'inset(0% 0% 0% 0%)', duration: 1.5, ease: 'expo.inOut' }, 0)
      .fromTo(edge, { x: direction > 0 ? 0 : width, opacity: 1 }, { x: direction > 0 ? width : 0, duration: 1.5, ease: 'expo.inOut' }, 0)
      .to(edge, { opacity: 0, duration: 0.25 }, 1.3)
      .fromTo(toImg, { scale: 1.28, xPercent: -8 * direction }, { scale: 1.1, xPercent: 0, duration: 1.8, ease: 'expo.out', onComplete: () => kenBurns(toImg) }, 0.1)
      .to(fromImg, { xPercent: 10 * direction, filter: 'brightness(.55)', duration: 1.5, ease: 'expo.inOut' }, 0);

    startTimer();
  }

  /* ---- controls ---- */

  dots.forEach((dot, i) =>
    dot.addEventListener('click', () => {
      go(i);
    }),
  );

  hero.querySelector('[data-hero-controls]')?.addEventListener('keydown', (event) => {
    if (event.key === 'ArrowRight') go(current + 1, 1);
    else if (event.key === 'ArrowLeft') go(current - 1, -1);
    else return;
    event.preventDefault();
    dots[current]?.focus();
  });

  pauseBtn?.addEventListener('click', () => {
    userPaused = !userPaused;
    syncPlayback();
  });

  // Swipe on touch screens.
  let touchX = null;
  hero.addEventListener('touchstart', (e) => (touchX = e.touches[0].clientX), { passive: true });
  hero.addEventListener(
    'touchend',
    (e) => {
      if (touchX === null) return;
      const dx = e.changedTouches[0].clientX - touchX;
      touchX = null;
      if (Math.abs(dx) > 60) go(current + (dx < 0 ? 1 : -1), dx < 0 ? 1 : -1);
    },
    { passive: true },
  );

  document.addEventListener('visibilitychange', syncPlayback);

  if (!motion) {
    pauseBtn?.setAttribute('hidden', '');
    setMeta(0);
    startTimer();
    return { enter() {} };
  }

  ScrollTrigger.create({
    trigger: hero,
    start: 'top bottom',
    end: 'bottom top',
    onToggle: (self) => {
      inView = self.isActive;
      syncPlayback();
    },
  });

  // Scrolling away: the copy lifts and fades, the photograph sinks behind it.
  const content = hero.querySelector('[data-hero-content]');
  const media = hero.querySelector('[data-hero-media]');
  gsap.to(content, { yPercent: -16, opacity: 0.15, ease: 'none', scrollTrigger: { trigger: hero, start: 'top top', end: 'bottom top', scrub: true } });
  gsap.to(media, { yPercent: 16, ease: 'none', scrollTrigger: { trigger: hero, start: 'top top', end: 'bottom top', scrub: true } });

  // Hold the opening frame soft and slightly dark until the entrance plays.
  gsap.set(imgs[0], { scale: 1.3, filter: 'blur(16px) brightness(.6)' });

  return {
    enter() {
      if (entered) return;
      entered = true;
      gsap.set(title, { visibility: 'visible' });
      SplitText.create(title, {
        type: 'lines',
        tag: 'span',
        mask: 'lines',
        linesClass: 'split-line',
        autoSplit: true,
        onSplit: (self) => gsap.from(self.lines, { yPercent: 118, duration: 1.5, ease: 'expo.out', stagger: 0.11, delay: 0.15 }),
      });
      gsap.fromTo(items, { opacity: 0, y: 26 }, { opacity: 1, y: 0, duration: 1.3, ease: 'expo.out', stagger: 0.09, delay: 0.55 });
      gsap.to(imgs[0], {
        scale: 1.1,
        filter: 'blur(0px) brightness(1)',
        duration: 2.4,
        ease: 'expo.out',
        onComplete: () => {
          gsap.set(imgs[0], { filter: 'none' });
          kenBurns(imgs[0]);
          startTimer();
          syncPlayback();
        },
      });
    },
  };
}

/** Inner pages: the header photograph settles from blur to sharp as the title rises. */
export function initPageHead({ gsap, SplitText, motion }) {
  const head = document.querySelector('[data-page-head]');
  if (!head || !motion) return null;

  const media = head.querySelector('[data-page-head-media]');
  const title = head.querySelector('[data-hero-title]');
  const items = head.querySelectorAll('[data-hero-item]');

  if (media) gsap.set(media, { scale: 1.25, filter: 'blur(14px) brightness(.7)' });

  if (media) {
    gsap.to(media, {
      yPercent: 14,
      ease: 'none',
      scrollTrigger: { trigger: head, start: 'top top', end: 'bottom top', scrub: true },
    });
  }

  return {
    enter() {
      if (title) {
        gsap.set(title, { visibility: 'visible' });
        SplitText.create(title, {
          type: 'lines,chars',
          tag: 'span',
          mask: 'lines',
          linesClass: 'split-line',
          autoSplit: true,
          onSplit: (self) => gsap.from(self.chars, { yPercent: 120, duration: 1.4, ease: 'expo.out', stagger: 0.035, delay: 0.1 }),
        });
      }
      gsap.fromTo(items, { opacity: 0, y: 22 }, { opacity: 1, y: 0, duration: 1.2, ease: 'expo.out', stagger: 0.08, delay: 0.35 });
      if (media) {
        gsap.to(media, {
          scale: 1.04,
          filter: 'blur(0px) brightness(1)',
          duration: 2.2,
          ease: 'expo.out',
          onComplete: () => gsap.set(media, { filter: 'none' }),
        });
      }
    },
  };
}
