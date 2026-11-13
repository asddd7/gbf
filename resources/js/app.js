import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

const pageLoading = document.querySelector('[data-page-loading]');
let loadingTimer;

function showPageLoading() {
    if (!pageLoading || pageLoading.classList.contains('is-visible')) {
        return;
    }

    window.clearTimeout(loadingTimer);
    loadingTimer = window.setTimeout(() => {
        pageLoading.classList.add('is-visible');
        pageLoading.setAttribute('aria-hidden', 'false');
    }, 120);
}

function hidePageLoading() {
    window.clearTimeout(loadingTimer);
    pageLoading?.classList.remove('is-visible');
    pageLoading?.setAttribute('aria-hidden', 'true');
}

document.addEventListener('click', (event) => {
    if (
        !(event.target instanceof Element) ||
        event.defaultPrevented ||
        event.button !== 0 ||
        event.ctrlKey ||
        event.metaKey ||
        event.shiftKey ||
        event.altKey
    ) {
        return;
    }

    const link = event.target.closest('a[href]');

    if (
        !link ||
        link.hasAttribute('download') ||
        (link.target && link.target !== '_self')
    ) {
        return;
    }

    const destination = new URL(link.href, window.location.href);

    if (destination.origin === window.location.origin && destination.href !== window.location.href) {
        showPageLoading();
    }
});

document.addEventListener('submit', (event) => {
    const form = event.target;

    if (
        form instanceof HTMLFormElement &&
        (!form.target || form.target === '_self') &&
        form.method.toLowerCase() !== 'dialog'
    ) {
        showPageLoading();
    }
});

window.addEventListener('pageshow', hidePageLoading);

const passwordVisibilityIcons = {
    hidden: '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M2.25 12s3.5-6 9.75-6 9.75 6 9.75 6-3.5 6-9.75 6-9.75-6-9.75-6Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="12" r="2.75" stroke="currentColor" stroke-width="1.7"/></svg>',
    visible: '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m3 3 18 18M10.6 6.2A10.7 10.7 0 0 1 12 6c6.25 0 9.75 6 9.75 6a16.2 16.2 0 0 1-3.1 3.65M6.2 6.25C3.65 7.9 2.25 12 2.25 12s3.5 6 9.75 6c1.05 0 2.02-.17 2.92-.46" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>',
};

document.querySelectorAll('input[type="password"]').forEach((input) => {
    if (!(input instanceof HTMLInputElement) || input.dataset.passwordToggleReady) {
        return;
    }

    input.dataset.passwordToggleReady = 'true';

    const wrapper = document.createElement('span');
    wrapper.className = 'password-toggle-wrap';
    input.parentNode.insertBefore(wrapper, input);
    wrapper.append(input);

    const toggle = document.createElement('button');
    toggle.className = 'password-visibility-toggle';
    toggle.type = 'button';
    toggle.setAttribute('aria-label', 'Tampilkan password');
    toggle.setAttribute('aria-pressed', 'false');
    toggle.innerHTML = passwordVisibilityIcons.hidden;
    wrapper.append(toggle);

    toggle.addEventListener('click', () => {
        const isVisible = input.type === 'text';

        input.type = isVisible ? 'password' : 'text';
        toggle.setAttribute('aria-label', isVisible ? 'Tampilkan password' : 'Sembunyikan password');
        toggle.setAttribute('aria-pressed', String(!isVisible));
        toggle.innerHTML = isVisible ? passwordVisibilityIcons.hidden : passwordVisibilityIcons.visible;
    });
});
