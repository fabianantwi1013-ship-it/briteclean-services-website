/**
 * Services film strip.
 *
 * Wide screens with a mouse or trackpad: the section pins and the vertical scroll
 * moves the strip sideways, with each photo drifting inside its frame. Everywhere
 * else it stays a native, swipeable scroll-snap row. The progress bar follows
 * whichever is in use.
 */
export function initRail({ gsap, ScrollTrigger, lenis, motion }) {
  const section = document.querySelector('[data-rail]');
  if (!section) return;

  const pin = section.querySelector('[data-rail-pin]');
  const viewport = section.querySelector('[data-rail-viewport]');
  const track = section.querySelector('[data-rail-track]');
  const progress = section.querySelector('[data-rail-progress]');
  const mm = gsap.matchMedia();

  const nativeProgress = () => {
    const onScroll = () => {
      const max = viewport.scrollWidth - viewport.clientWidth;
      gsap.set(progress, { scaleX: max > 0 ? viewport.scrollLeft / max : 0 });
    };
    viewport.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
    return () => viewport.removeEventListener('scroll', onScroll);
  };

  if (!motion) {
    nativeProgress();
    return;
  }

  mm.add('(min-width: 1024px) and (min-height: 620px) and (hover: hover) and (pointer: fine)', () => {
    section.classList.add('is-pinned');

    const padding = () => {
      const s = getComputedStyle(viewport);
      return parseFloat(s.paddingLeft) + parseFloat(s.paddingRight);
    };
    const distance = () => Math.max(0, track.scrollWidth - (viewport.clientWidth - padding()));

    const tween = gsap.to(track, { x: () => -distance(), ease: 'none' });
    const st = ScrollTrigger.create({
      trigger: section,
      start: 'top top',
      end: () => `+=${distance()}`,
      pin,
      scrub: 0.8,
      animation: tween,
      invalidateOnRefresh: true,
      onUpdate: (self) => gsap.set(progress, { scaleX: self.progress }),
    });

    section.querySelectorAll('[data-rail-img]').forEach((img) => {
      gsap.fromTo(
        img,
        { xPercent: -7 },
        {
          xPercent: 7,
          ease: 'none',
          scrollTrigger: { trigger: img.closest('.rail__item'), containerAnimation: tween, start: 'left right', end: 'right left', scrub: true },
        },
      );
    });

    // Keyboard focus on a card that is off to the side: scroll the page to the point
    // where the strip shows it, instead of letting the browser scroll the clipped box.
    const onFocus = (event) => {
      const item = event.target.closest('.rail__item');
      if (!item) return;
      section.scrollLeft = 0;
      viewport.scrollLeft = 0;
      const ratio = Math.min(1, Math.max(0, (item.offsetLeft - 40) / (distance() || 1)));
      const y = st.start + (st.end - st.start) * ratio;
      if (lenis) lenis.scrollTo(y, { immediate: true });
      else window.scrollTo(0, y);
    };
    track.addEventListener('focusin', onFocus);

    return () => {
      track.removeEventListener('focusin', onFocus);
      section.classList.remove('is-pinned');
    };
  });

  mm.add('(max-width: 1023px), (max-height: 619px), (hover: none), (pointer: coarse)', nativeProgress);
}
