// Public-site behaviour, deliberately framework-free (~2 KB): theme toggle, mobile menu, scroll reveals.
// Livewire/Alpine are loaded only on the contact page.

import.meta.glob(['../fonts/*.woff2', '../images/**'], { eager: false });

const root = document.documentElement;
// Reveal styles only activate once this module runs, so a failed script never hides content.
root.classList.add('js');
const STORAGE_KEY = 'theme';

function effectiveTheme() {
    const forced = root.dataset.theme;
    if (forced === 'light' || forced === 'dark') return forced;
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
}

function syncThemeButtons() {
    const isDark = effectiveTheme() === 'dark';
    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.setAttribute('aria-pressed', String(isDark));
    });
}

function initThemeToggle() {
    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const next = effectiveTheme() === 'dark' ? 'light' : 'dark';
            root.dataset.theme = next;
            try {
                localStorage.setItem(STORAGE_KEY, next);
            } catch {
                // Storage can be unavailable (private mode); the choice then lasts for this page only.
            }
            syncThemeButtons();
        });
    });

    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', syncThemeButtons);
    syncThemeButtons();
}

function initMenu() {
    const dialog = document.getElementById('site-menu');
    const opener = document.querySelector('[data-menu-open]');
    if (!dialog || !opener || typeof dialog.showModal !== 'function') return;

    opener.addEventListener('click', (event) => {
        event.preventDefault();
        dialog.showModal();
        opener.setAttribute('aria-expanded', 'true');
    });

    dialog.addEventListener('close', () => {
        opener.setAttribute('aria-expanded', 'false');
        opener.focus();
    });

    dialog.querySelectorAll('[data-menu-close], a').forEach((el) => {
        el.addEventListener('click', () => dialog.close());
    });

    // Click on the backdrop closes the menu.
    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) dialog.close();
    });
}

function initReveal() {
    const items = document.querySelectorAll('[data-reveal]');
    if (!items.length) return;

    if (!('IntersectionObserver' in window) || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        items.forEach((el) => el.classList.add('is-visible'));
        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        },
        { rootMargin: '0px 0px -8% 0px', threshold: 0.08 },
    );

    items.forEach((el) => observer.observe(el));
}

initThemeToggle();
initMenu();
initReveal();
