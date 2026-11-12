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
