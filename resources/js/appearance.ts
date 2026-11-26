/**
 * The light/auto/dark switch.
 *
 * Flux owns the contract: it applies `.dark` to `<html>` before first
 * paint from `localStorage['flux.appearance']` and exposes
 * `window.Flux.applyAppearance()`. This module only wires the buttons to
 * that call, keeps `aria-pressed` honest, and follows the OS while the
 * page is open. Bootstrap 5.3 pages follow through `data-bs-theme`.
 */
type Appearance = 'light' | 'dark' | 'system';

// Module scope, so the Window augmentation below is legal.
export {};

declare global {
  interface Window {
    Flux?: {
      applyAppearance: (appearance: Appearance) => void;
    };
  }
}

const STORAGE_KEY = 'flux.appearance';
const GROUP_SELECTOR = '[data-appearance-group]';

function current(): Appearance {
  const saved = window.localStorage.getItem(STORAGE_KEY);

  return saved === 'light' || saved === 'dark' ? saved : 'system';
}

function syncBsTheme(): void {
  document.documentElement.dataset.bsTheme = document.documentElement.classList.contains('dark')
    ? 'dark'
    : 'light';
}

function markPressed(value: Appearance): void {
  for (const group of document.querySelectorAll(GROUP_SELECTOR)) {
    for (const button of group.querySelectorAll<HTMLButtonElement>('[data-appearance]')) {
      button.setAttribute('aria-pressed', String(button.dataset.appearance === value));
    }
  }
}

function apply(value: Appearance): void {
  // Flux's runtime replaces window.Flux, so its pre-paint helper is not
  // guaranteed to be there. The storage key and the .dark class are the
  // stable contract, implemented here when the helper is missing.
  if (typeof window.Flux?.applyAppearance === 'function') {
    window.Flux.applyAppearance(value);
  } else {
    window.localStorage.removeItem(STORAGE_KEY);

    if (value !== 'system') {
      window.localStorage.setItem(STORAGE_KEY, value);
    }

    const dark = value === 'system'
      ? window.matchMedia('(prefers-color-scheme: dark)').matches
      : value === 'dark';

    document.documentElement.classList.toggle('dark', dark);
  }

  syncBsTheme();
  markPressed(value);
}

for (const group of document.querySelectorAll(GROUP_SELECTOR)) {
  for (const button of group.querySelectorAll<HTMLButtonElement>('[data-appearance]')) {
    button.addEventListener('click', () => {
      const value = button.dataset.appearance;

      if (value === 'light' || value === 'dark' || value === 'system') {
        apply(value);
      }
    });
  }
}

// Auto follows the OS while the page is open; an explicit choice wins.
window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
  if (current() === 'system') {
    apply('system');
  }
});

// Tabs stay in step with each other.
window.addEventListener('storage', (event) => {
  if (event.key === STORAGE_KEY) {
    apply(current());
  }
});

markPressed(current());
syncBsTheme();
