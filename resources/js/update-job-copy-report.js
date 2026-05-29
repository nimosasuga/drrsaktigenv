// PATH FILE: resources/js/update-job-copy-report.js

function getDetailId() {
    const match = window.location.pathname.match(/^\/update-jobs\/(\d+)$/);
    return match ? match[1] : null;
}

function injectFloatingShareReportButton() {
    const id = getDetailId();

    if (!id || document.querySelector('[data-share-update-job-report="true"]')) {
        return;
    }

    const link = document.createElement('a');
    link.href = `/update-jobs/${id}/share-message`;
    link.target = '_blank';
    link.rel = 'noopener noreferrer';
    link.dataset.shareUpdateJobReport = 'true';
    link.className = 'fixed bottom-28 right-5 z-50 inline-flex h-14 w-14 items-center justify-center rounded-full bg-emerald-600 text-white shadow-2xl shadow-emerald-900/30 ring-4 ring-emerald-100 transition hover:bg-emerald-700 active:scale-95 sm:bottom-32 sm:right-8';
    link.setAttribute('aria-label', 'Share WhatsApp Update Job');
    link.setAttribute('title', 'Share WhatsApp');
    link.innerHTML = `
        <svg class="h-7 w-7" viewBox="0 0 32 32" fill="currentColor" aria-hidden="true">
            <path d="M16.01 3.2C9.04 3.2 3.36 8.84 3.36 15.78c0 2.4.69 4.73 1.99 6.75L3.2 28.8l6.47-2.07a12.72 12.72 0 006.34 1.68c6.98 0 12.65-5.64 12.65-12.58S22.99 3.2 16.01 3.2zm0 22.96c-1.98 0-3.91-.56-5.58-1.63l-.4-.25-3.84 1.23 1.26-3.67-.27-.42a10.15 10.15 0 01-1.58-5.43c0-5.69 4.67-10.33 10.41-10.33 5.73 0 10.4 4.64 10.4 10.33 0 5.7-4.67 10.17-10.4 10.17zm5.72-7.6c-.31-.16-1.84-.91-2.13-1.01-.29-.11-.5-.16-.71.16-.21.31-.82 1.01-1.01 1.22-.18.21-.37.24-.68.08-.31-.16-1.31-.48-2.49-1.53-.92-.82-1.54-1.83-1.72-2.14-.18-.31-.02-.48.14-.63.14-.14.31-.37.47-.55.16-.18.21-.31.31-.52.1-.21.05-.39-.03-.55-.08-.16-.71-1.7-.97-2.33-.26-.61-.52-.53-.71-.54h-.61c-.21 0-.55.08-.84.39-.29.31-1.1 1.08-1.1 2.62s1.13 3.03 1.29 3.24c.16.21 2.23 3.39 5.4 4.76.75.32 1.34.52 1.8.66.76.24 1.45.21 2 .13.61-.09 1.84-.75 2.1-1.48.26-.73.26-1.35.18-1.48-.08-.13-.29-.21-.6-.37z"/>
        </svg>
    `;

    document.body.appendChild(link);
}

document.addEventListener('DOMContentLoaded', injectFloatingShareReportButton);
