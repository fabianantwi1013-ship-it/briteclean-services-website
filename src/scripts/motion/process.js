/**
 * "How it works": a gradient line grows down the steps, the active step lights up,
 * and the big numeral beside them rolls to match.
 */
export function initProcess({ gsap, ScrollTrigger }) {
  document.querySelectorAll('[data-process]').forEach((section) => {
    const steps = [...section.querySelectorAll('[data-process-step]')];
    const reel = section.querySelector('[data-process-reel]');
    const line = section.querySelector('[data-process-line]');
    const body = section.querySelector('.process__body');
    let active = -1;

    const setActive = (i) => {
      if (i === active) return;
      active = i;
      steps.forEach((step, k) => step.classList.toggle('is-active', k === i));
      if (reel) gsap.to(reel, { yPercent: (-100 / steps.length) * i, duration: 0.9, ease: 'expo.out' });
    };

    if (line && body) {
      gsap.fromTo(line, { scaleY: 0 }, { scaleY: 1, ease: 'none', scrollTrigger: { trigger: body, start: 'top 62%', end: 'bottom 62%', scrub: true } });
    }

    // A step becomes current once its top passes the 62% line, and stays current
    // until the next one does — so there is always exactly one lit step, even in
    // the gaps between them or after a jump.
    steps.forEach((step, i) => {
      ScrollTrigger.create({
        trigger: step,
        start: 'top 62%',
        onEnter: () => setActive(i),
        onLeaveBack: () => setActive(Math.max(0, i - 1)),
      });
    });

    setActive(0);
  });
}
