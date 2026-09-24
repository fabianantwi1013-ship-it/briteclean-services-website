/** Services page: highlight the service currently on screen in the side index. */
export function initServicesNav({ ScrollTrigger }) {
  const nav = document.querySelector('[data-svc-nav]');
  if (!nav) return;

  const links = new Map([...nav.querySelectorAll('[data-svc-link]')].map((a) => [a.dataset.svcLink, a]));

  document.querySelectorAll('[data-svc-row]').forEach((row) => {
    ScrollTrigger.create({
      trigger: row,
      start: 'top 55%',
      end: 'bottom 55%',
      onToggle: (self) => {
        if (!self.isActive) return;
        links.forEach((link) => {
          link.classList.remove('is-active');
          link.removeAttribute('aria-current');
        });
        const link = links.get(row.dataset.svcRow);
        link?.classList.add('is-active');
        link?.setAttribute('aria-current', 'location');
      },
    });
  });
}
