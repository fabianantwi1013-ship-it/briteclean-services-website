/** Service-name ticker: a slow constant drift that surges with scroll speed. */
export function initMarquee({ gsap, ScrollTrigger }) {
  document.querySelectorAll('[data-marquee]').forEach((marquee) => {
    const track = marquee.querySelector('[data-marquee-track]');
    const loop = gsap.to(track, { xPercent: -50, duration: 42, ease: 'none', repeat: -1, paused: true });
    let settle = null;

    ScrollTrigger.create({
      trigger: marquee,
      start: 'top bottom',
      end: 'bottom top',
      onToggle: (self) => (self.isActive ? loop.play() : loop.pause()),
      onUpdate: (self) => {
        const boost = Math.min(Math.abs(self.getVelocity()) / 220, 6);
        loop.timeScale(1 + boost);
        settle?.kill();
        settle = gsap.to(loop, { timeScale: 1, duration: 1.4, ease: 'power2.out', delay: 0.08 });
      },
    });
  });
}
