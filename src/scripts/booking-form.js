/**
 * Multi-step booking form.
 *
 * Progressive enhancement: the markup is a complete, working form before this runs.
 * The script adds `is-enhanced`, which is what the CSS keys the stepper off. Without
 * it the visitor gets one long form that still POSTs to /api/booking and is still
 * validated server-side.
 *
 * Client-side validation here is for faster feedback only. Every rule is enforced
 * again in api/booking.js, which is the only check that counts.
 */

const L10N = {
  stepOf: 'Step %1$d of %2$d',
  required: 'This field is required.',
  chooseService: 'Please choose at least one service.',
  invalidEmail: 'Please enter a valid email address.',
  invalidPhone: 'Please enter a valid phone number.',
  pastDate: 'Please choose a date that has not already passed.',
  none: 'Not provided',
  submitting: 'Sending…',
  submit: 'Send My Booking Request',
  networkError: 'We could not send that. Please check your connection and try again, or call us.',
};

export function initBookingForm() {
  const form = document.getElementById('bc-booking-form');
  if (!form) return;

  const steps = [...form.querySelectorAll('.bc-step')];
  if (steps.length < 2) return;

  const progressItems = [...form.querySelectorAll('.bc-progress__item')];
  const counter = form.querySelector('.bc-step-counter');
  const backBtn = form.querySelector('.bc-form__back');
  const nextBtn = form.querySelector('.bc-form__next');
  const submitBtn = form.querySelector('.bc-form__submit');
  const reviewBox = form.querySelector('[data-bc-review]');
  const errorBox = document.getElementById('bc-form-errors');
  const successBox = document.getElementById('bc-booking-confirmation');
  const renderedAt = document.getElementById('bc-rendered-at');

  // Stamped client-side so the server can measure fill time. Forgeable on its own,
  // which is exactly why the server treats it as one signal among several.
  if (renderedAt) renderedAt.value = String(Date.now());

  let current = 0;
  form.classList.add('is-enhanced');
  if (backBtn) backBtn.hidden = false;
  if (nextBtn) nextBtn.hidden = false;

  const prefersReducedMotion = () =>
    window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // Focus is moved with preventScroll, then the page scrolls once, to a position
  // measured after the step has changed.
  function scrollToElement(el, offset) {
    const top = Math.max(0, el.getBoundingClientRect().top + window.pageYOffset + offset);
    window.scrollTo({ top, behavior: prefersReducedMotion() ? 'auto' : 'smooth' });
  }

  function scrollToCentre(el) {
    const offset = -Math.max(90, (window.innerHeight - el.offsetHeight) / 2);
    scrollToElement(el, offset);
  }

  function showStep(index, moveFocus) {
    current = Math.max(0, Math.min(steps.length - 1, index));

    steps.forEach((step, i) => step.classList.toggle('is-active', i === current));
    progressItems.forEach((item, i) => {
      item.classList.toggle('is-active', i === current);
      item.classList.toggle('is-done', i < current);
    });

    if (counter) {
      counter.textContent = L10N.stepOf
        .replace('%1$d', String(current + 1))
        .replace('%2$d', String(steps.length));
    }

    const isLast = current === steps.length - 1;
    if (backBtn) backBtn.hidden = current === 0;
    if (nextBtn) nextBtn.hidden = isLast;
    if (submitBtn) submitBtn.hidden = !isLast;

    if (isLast) buildReview();

    form.style.setProperty('--bc-progress', String(current / (steps.length - 1)));

    if (moveFocus) {
      // Focus the heading rather than the first input, so a screen reader announces
      // what the step is before what to type.
      const legend = steps[current].querySelector('.bc-step__title');
      if (legend) {
        legend.setAttribute('tabindex', '-1');
        legend.focus({ preventScroll: true });
      }
      scrollToElement(form, -110);
    }
  }

  const isValidEmail = (v) => /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v);
  const isValidPhone = (v) => {
    const d = v.replace(/\D+/g, '');
    return d.length >= 7 && d.length <= 15;
  };
  const isPastDate = (v) => {
    const picked = new Date(v + 'T00:00:00');
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    return picked < today;
  };

  function setFieldError(field, message) {
    const errorEl = field.querySelector('.bc-error');
    const inputs = [...field.querySelectorAll('input, select, textarea')];

    field.classList.toggle('bc-field--error', !!message);
    if (errorEl) {
      errorEl.textContent = message;
      errorEl.hidden = !message;
    }
    inputs.forEach((i) => {
      if (message) i.setAttribute('aria-invalid', 'true');
      else i.removeAttribute('aria-invalid');
    });
  }

  function validateField(field) {
    const inputs = [...field.querySelectorAll('input, select, textarea')];
    if (!inputs.length) return true;

    const first = inputs[0];
    const type = first.type;
    let message = '';

    if (type === 'checkbox' || type === 'radio') {
      const name = field.getAttribute('data-field');
      const isRequired = ['services', 'propertyType', 'timePreference'].includes(name);
      const anyChecked = inputs.some((i) => i.checked);
      if (isRequired && !anyChecked) {
        message = type === 'checkbox' ? L10N.chooseService : L10N.required;
      }
    } else {
      const value = (first.value || '').trim();
      if (first.required && !value) message = L10N.required;
      else if (value && type === 'email' && !isValidEmail(value)) message = L10N.invalidEmail;
      else if (value && type === 'tel' && !isValidPhone(value)) message = L10N.invalidPhone;
      else if (value && type === 'date' && isPastDate(value)) message = L10N.pastDate;
    }

    setFieldError(field, message);
    return !message;
  }

  function validateStep(step) {
    let firstInvalid = null;
    [...step.querySelectorAll('.bc-field')].forEach((field) => {
      if (!validateField(field) && !firstInvalid) firstInvalid = field;
    });
    if (firstInvalid) {
      const focusable = firstInvalid.querySelector('input, select, textarea');
      if (focusable) focusable.focus();
      return false;
    }
    return true;
  }

  function getFieldLabel(field) {
    const label = field.querySelector('.bc-label');
    if (!label) return '';
    const clone = label.cloneNode(true);
    clone.querySelectorAll('.bc-req, .screen-reader-text').forEach((n) => n.remove());
    return clone.textContent.trim();
  }

  function getFieldValue(field) {
    const checked = [...field.querySelectorAll('input:checked')];
    if (checked.length) {
      return checked
        .map((input) => {
          const label = field.querySelector(`label[for="${input.id}"]`);
          const text = label ? label.querySelector('.bc-option__text') : null;
          return text ? text.textContent.trim() : input.value;
        })
        .join(', ');
    }

    const select = field.querySelector('select');
    if (select) return select.value ? select.options[select.selectedIndex].text.trim() : '';

    const input = field.querySelector('input, textarea');
    if (!input || input.type === 'checkbox' || input.type === 'radio') return '';

    const value = (input.value || '').trim();

    // Show a date the way a person reads it, not as 2026-04-18.
    if (input.type === 'date' && value) {
      const parsed = new Date(value + 'T00:00:00');
      if (!isNaN(parsed.getTime())) {
        return parsed.toLocaleDateString(undefined, {
          weekday: 'long', year: 'numeric', month: 'long', day: 'numeric',
        });
      }
    }
    return value;
  }

  function buildReview() {
    if (!reviewBox) return;
    reviewBox.innerHTML = '';

    steps.forEach((step, stepIndex) => {
      [...step.querySelectorAll('.bc-field')].forEach((field) => {
        const label = getFieldLabel(field);
        if (!label) return;
        const value = getFieldValue(field);

        const row = document.createElement('div');
        row.className = 'bc-review__row';

        const labelEl = document.createElement('span');
        labelEl.className = 'bc-review__label';
        labelEl.textContent = label;

        const valueEl = document.createElement('span');
        valueEl.className = 'bc-review__value' + (value ? '' : ' bc-review__value--empty');
        valueEl.textContent = value || L10N.none;

        const edit = document.createElement('button');
        edit.type = 'button';
        edit.className = 'bc-review__edit';
        edit.textContent = 'Edit';
        edit.addEventListener('click', () => showStep(stepIndex, true));

        row.append(labelEl, valueEl, edit);
        reviewBox.appendChild(row);
      });
    });
  }

  function showErrors(errors) {
    if (!errorBox) return;
    const list = errorBox.querySelector('ul');
    list.innerHTML = '';

    Object.entries(errors).forEach(([key, message]) => {
      const field = form.querySelector(`[data-field="${key}"]`);
      if (field) setFieldError(field, message);

      const li = document.createElement('li');
      if (field) {
        const link = document.createElement('a');
        link.href = `#bc-field-${key}`;
        link.textContent = getFieldLabel(field) || key;
        link.addEventListener('click', (e) => {
          e.preventDefault();
          const stepIndex = steps.indexOf(field.closest('.bc-step'));
          if (stepIndex > -1) showStep(stepIndex, true);
          const focusable = field.querySelector('input, select, textarea');
          if (focusable) focusable.focus();
        });
        li.append(link, document.createTextNode(`: ${message}`));
      } else {
        li.textContent = message;
      }
      list.appendChild(li);
    });

    errorBox.hidden = false;
    errorBox.focus({ preventScroll: true });
    scrollToCentre(errorBox);
  }

  /* ---- wiring ---- */

  if (nextBtn) {
    nextBtn.addEventListener('click', () => {
      if (validateStep(steps[current])) showStep(current + 1, true);
    });
  }
  if (backBtn) {
    backBtn.addEventListener('click', () => showStep(current - 1, true));
  }

  // Clear a field's error as soon as it is fixed, rather than making the visitor
  // press Continue again to find out.
  ['input', 'change'].forEach((evt) => {
    form.addEventListener(evt, (event) => {
      const field = event.target.closest && event.target.closest('.bc-field');
      if (field && field.classList.contains('bc-field--error')) validateField(field);
    });
  });

  // Enter advances a step rather than submitting from step 1.
  form.addEventListener('keydown', (event) => {
    if (event.key !== 'Enter' || event.target.tagName === 'TEXTAREA') return;
    if (current < steps.length - 1) {
      event.preventDefault();
      if (validateStep(steps[current])) showStep(current + 1, true);
    }
  });

  form.addEventListener('submit', async (event) => {
    event.preventDefault();

    // Validate every step, not just the visible one: someone can reach the last
    // step, go back, clear a required field, and return.
    let allValid = true;
    let firstBadStep = -1;
    steps.forEach((step, index) => {
      [...step.querySelectorAll('.bc-field')].forEach((field) => {
        if (!validateField(field)) {
          allValid = false;
          if (firstBadStep === -1) firstBadStep = index;
        }
      });
    });

    if (!allValid) {
      showStep(firstBadStep, true);
      return;
    }

    if (errorBox) errorBox.hidden = true;
    submitBtn.disabled = true;
    submitBtn.textContent = L10N.submitting;

    try {
      const payload = Object.fromEntries(new FormData(form).entries());
      payload.services = [...form.querySelectorAll('input[name="services"]:checked')].map((i) => i.value);

      const response = await fetch('/api/booking', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });
      const result = await response.json().catch(() => ({}));

      if (response.ok && result.ok) {
        form.hidden = true;
        if (errorBox) errorBox.hidden = true;
        if (successBox) {
          successBox.hidden = false;
          successBox.focus({ preventScroll: true });
          scrollToCentre(successBox);
        }
        return;
      }

      showErrors(result.errors || { form: result.message || L10N.networkError });
    } catch {
      showErrors({ form: L10N.networkError });
    } finally {
      submitBtn.disabled = false;
      submitBtn.textContent = L10N.submit;
    }
  });

  showStep(0, false);
}
