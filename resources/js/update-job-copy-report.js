// PATH FILE: resources/js/update-job-copy-report.js

function getDetailId() {
    const match = window.location.pathname.match(/^\/update-jobs\/(\d+)$/);
    return match ? match[1] : null;
}

function getActionContainer() {
    const backLink = document.querySelector('a[href$="/update-jobs"]');
    return backLink ? backLink.parentElement : null;
}

function injectShareReportButton() {
    const id = getDetailId();

    if (!id || document.querySelector('[data-share-update-job-report="true"]')) {
        return;
    }

    const container = getActionContainer();
    if (!container) {
        return;
    }

    const link = document.createElement('a');
    link.href = `/update-jobs/${id}/share-message`;
    link.target = '_blank';
    link.rel = 'noopener noreferrer';
    link.dataset.shareUpdateJobReport = 'true';
    link.className = 'inline-flex items-center justify-center px-4 py-2 bg-emerald-600 text-white rounded-xl text-sm font-semibold hover:bg-emerald-700 transition-colors shadow-sm focus:ring-2 focus:ring-emerald-200';
    link.textContent = 'Share WhatsApp';

    container.prepend(link);
}

document.addEventListener('DOMContentLoaded', injectShareReportButton);
