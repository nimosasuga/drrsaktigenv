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
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.77 9.77 0 01-4-.82L3 20l1.38-3.45A7.4 7.4 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
        </svg>
    `;

    document.body.appendChild(link);
}

document.addEventListener('DOMContentLoaded', injectFloatingShareReportButton);
