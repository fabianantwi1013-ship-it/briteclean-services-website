/**
 * Opening title sequence (homepage, first view of the session).
 *
 *   0.0s  the house mark draws itself; a counter runs 000 → 100
 *   0.35  "Briteclean" rises letter by letter
 *   0.9   "Services LLC" tightens into place, the tagline fades up
 *   1.85  the centre lifts away; a light-green line sweeps left to right and
 *         takes the curtain with it, uncovering the hero as it goes
 *
 * Any key, click or the Skip button jumps straight to the sweep.
 */
export function initIntro({ gsap, SplitText, lenis, onReveal }) {
  const root = document.documentElement;
  const intro = document.querySelector('div[data-intro]');

  if (!intro || !root.classList.contains('intro-on')) {
    intro?.remove();
    onReveal();
    return;
  }

  try {
    sessionStorage.setItem('bc-intro-seen', '1');
  } catch {
    /* private mode — the intro may simply play again next time */
  }

  lenis?.stop();

  const q = (sel) => intro.querySelector(sel);
  const qa = (sel) => intro.querySelectorAll(sel);

  const curtain = q('[data-intro-curtain]');
  const centre = q('.intro__center');
  const word = q('[data-intro-word]');
  const sub = q('[data-intro-sub]');
  const tag = q('[data-intro-tag]');
  const metas = qa('[data-intro-meta]');
  const count = q('[data-intro-count]');
  const squeegee = q('[data-intro-squeegee]');
  const skip = q('[data-intro-skip]');

  const chars = SplitText.create(word, { type: 'chars', mask: 'chars', tag: 'span', aria: 'none' }).chars;
  const counter = { v: 0 };
  let revealed = false;

  const reveal = () => {
    if (revealed) return;
    revealed = true;
    onReveal();
  };

  const finish = () => {
    reveal();
    root.classList.remove('intro-on');
    intro.remove();
    lenis?.start();
    document.removeEventListener('keydown', onKey);
  };

  const tl = gsap.timeline({ defaults: { ease: 'expo.out' }, onComplete: finish });

  tl.set(word, { opacity: 1 })
    .to(qa('.im-stroke'), { strokeDashoffset: 0, duration: 1.15, ease: 'power2.inOut', stagger: 0.09 }, 0)
    .fromTo(qa('.im-window rect'), { opacity: 0, scale: 0.3 }, { opacity: 1, scale: 1, duration: 0.6, stagger: 0.05 }, 0.5)
    .fromTo(qa('.im-spark'), { opacity: 0, scale: 0, rotate: -120 }, { opacity: 1, scale: 1, rotate: 0, duration: 0.9, ease: 'back.out(2.2)', stagger: 0.12 }, 0.85)
    .from(chars, { yPercent: 115, duration: 1.2, stagger: 0.035 }, 0.35)
    .fromTo(sub, { opacity: 0, letterSpacing: '1.2em' }, { opacity: 1, letterSpacing: '0.55em', duration: 1.5 }, 0.9)
    .fromTo(tag, { opacity: 0, y: 14 }, { opacity: 1, y: 0, duration: 1 }, 1.15)
    .to(metas, { opacity: 1, duration: 0.8, ease: 'power2.out' }, 0.6)
    .to(
      counter,
      {
        v: 100,
        duration: 1.75,
        ease: 'power2.inOut',
        onUpdate: () => {
          count.textContent = String(Math.round(counter.v)).padStart(3, '0');
        },
      },
      0,
    )
    .addLabel('sweep', 1.85)
    .to(centre, { y: -28, opacity: 0, duration: 0.6, ease: 'power2.in' }, 'sweep')
    .to([...metas, skip], { opacity: 0, duration: 0.3, ease: 'power1.in' }, 'sweep')
    .set(squeegee, { opacity: 1 }, 'sweep+=0.25')
    .fromTo(squeegee, { x: -40 }, { x: () => window.innerWidth + 40, duration: 1.2, ease: 'power3.inOut' }, 'sweep+=0.25')
    .fromTo(curtain, { clipPath: 'inset(0% 0% 0% 0%)' }, { clipPath: 'inset(0% 0% 0% 100%)', duration: 1.2, ease: 'power3.inOut' }, 'sweep+=0.25')
    .add(reveal, 'sweep+=0.3');

  const skipToSweep = () => {
    if (tl.time() < tl.labels.sweep) tl.seek('sweep');
    tl.timeScale(1.6);
  };

  function onKey(event) {
    if (event.key === 'Tab') return; // let keyboard users reach the Skip button
    skipToSweep();
  }

  skip?.addEventListener('click', skipToSweep);
  curtain.addEventListener('click', skipToSweep);
  document.addEventListener('keydown', onKey);
}
