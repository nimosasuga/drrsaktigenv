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
    link.dataset.iconVersion = 'whatsapp-v2';
    link.className = 'fixed bottom-28 right-5 z-50 inline-flex h-14 w-14 items-center justify-center rounded-full bg-emerald-600 text-white shadow-2xl shadow-emerald-900/30 ring-4 ring-emerald-100 transition hover:bg-emerald-700 active:scale-95 sm:bottom-32 sm:right-8';
    link.setAttribute('aria-label', 'Share WhatsApp Update Job');
    link.setAttribute('title', 'Share WhatsApp');
    link.innerHTML = `
        <svg class="h-8 w-8" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
            <path d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.93 7.93 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.52a6.57 6.57 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.96-6.586 6.591-6.586a6.56 6.56 0 0 1 4.66 1.931 6.56 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.594-6.592 6.594zm3.613-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.064-.133.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.73.73 0 0 0-.529.247c-.182.198-.691.677-.691 1.654s.71 1.916.81 2.049c.098.133 1.394 2.132 3.383 2.99.473.205.842.326 1.13.418.475.152.904.129 1.246.08.38-.058 1.17-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232z"/>
        </svg>
    `;

    document.body.appendChild(link);
}

document.addEventListener('DOMContentLoaded', injectFloatingShareReportButton);
