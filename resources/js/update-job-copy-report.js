// PATH FILE: resources/js/update-job-copy-report.js

function getDetailId() {
    const match = window.location.pathname.match(/^\/update-jobs\/(\d+)$/);
    return match ? match[1] : null;
}

function getActionContainer() {
    const backLink = document.querySelector('a[href$="/update-jobs"]');
    return backLink ? backLink.parentElement : null;
}

async function copyReport(id, button) {
    const oldText = button.textContent;
    button.textContent = 'Menyiapkan...';
    button.disabled = true;

    try {
        const response = await fetch(`/update-jobs/${id}/share-message`, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (!response.ok) {
            throw new Error('Request failed');
        }

        const payload = await response.json();
        const message = payload.message || '';

        if (!message) {
            throw new Error('Empty report');
        }

        await navigator.clipboard.writeText(message);
        button.textContent = 'Report Tersalin';
        setTimeout(() => {
            button.textContent = oldText;
        }, 1800);
    } catch (error) {
        console.error(error);
        button.textContent = 'Gagal Copy';
        setTimeout(() => {
            button.textContent = oldText;
        }, 1800);
    } finally {
        button.disabled = false;
    }
}

function injectCopyReportButton() {
    const id = getDetailId();

    if (!id || document.querySelector('[data-copy-update-job-report="true"]')) {
        return;
    }

    const container = getActionContainer();
    if (!container) {
        return;
    }

    const button = document.createElement('button');
    button.type = 'button';
    button.dataset.copyUpdateJobReport = 'true';
    button.className = 'inline-flex items-center justify-center px-4 py-2 bg-emerald-600 text-white rounded-xl text-sm font-semibold hover:bg-emerald-700 transition-colors shadow-sm focus:ring-2 focus:ring-emerald-200';
    button.textContent = 'Copy Report';
    button.addEventListener('click', () => copyReport(id, button));

    container.prepend(button);
}

document.addEventListener('DOMContentLoaded', injectCopyReportButton);
