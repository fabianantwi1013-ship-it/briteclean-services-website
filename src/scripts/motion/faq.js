/**
 * Smooth open and close for the FAQ accordion. It stays a native <details>, so
 * find-in-page, keyboard and screen readers behave exactly as they would without it.
 */
export function initFaq({ gsap, ScrollTrigger }) {
  let refresh = null;
  const queueRefresh = () => {
    clearTimeout(refresh);
    refresh = setTimeout(() => ScrollTrigger.refresh(), 150);
  };

  document.querySelectorAll('[data-faq] details').forEach((details) => {
    const summary = details.querySelector('summary');
    const body = details.querySelector('.faq__body');
    if (!summary || !body) return;

    summary.addEventListener('click', (event) => {
      event.preventDefault();
      gsap.killTweensOf(body);
      if (details.open) {
        gsap.to(body, {
          height: 0,
          opacity: 0,
          duration: 0.5,
          ease: 'power3.inOut',
          onComplete: () => {
            details.open = false;
            gsap.set(body, { clearProps: 'height,opacity' });
            queueRefresh();
          },
        });
      } else {
        details.open = true;
        gsap.fromTo(
          body,
          { height: 0, opacity: 0 },
          {
            height: 'auto',
            opacity: 1,
            duration: 0.8,
            ease: 'expo.out',
            onComplete: () => {
              gsap.set(body, { clearProps: 'height,opacity' });
              queueRefresh();
            },
          },
        );
      }
    });
  });
}
