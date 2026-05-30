// PATH FILE: resources/js/update-job-detail-extra-fields.js

function getUpdateJobDetailId() {
    const match = window.location.pathname.match(/^\/update-jobs\/(\d+)$/);
    return match ? match[1] : null;
}

function makeDetailItem(label, value) {
    const wrapper = document.createElement('div');
    wrapper.dataset.updateJobDetailExtraField = label;
    wrapper.innerHTML = `
        <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">${label}</p>
        <p class="text-sm font-medium text-slate-800">${value || '-'}</p>
    `;

    return wrapper;
}

function findUnitTypeDetailWrapper() {
    const labels = Array.from(document.querySelectorAll('p'));
    const unitTypeLabel = labels.find((element) => element.textContent.trim().toLowerCase() === 'tipe unit');

    return unitTypeLabel ? unitTypeLabel.parentElement : null;
}

async function injectUpdateJobDetailExtraFields() {
    const id = getUpdateJobDetailId();

    if (!id || document.querySelector('[data-update-job-detail-extra-field="Nomor Lambung"]')) {
        return;
    }

    const unitTypeWrapper = findUnitTypeDetailWrapper();
    if (!unitTypeWrapper) {
        return;
    }

    try {
        const response = await fetch(`/update-jobs/${id}/extra-fields`, {
            headers: { Accept: 'application/json' },
        });

        if (!response.ok) {
            return;
        }

        const data = await response.json();
        const nomorLambung = makeDetailItem('Nomor Lambung', data.nomor_lambung || '-');
        const year = makeDetailItem('Year', data.year || '-');

        unitTypeWrapper.insertAdjacentElement('afterend', year);
        unitTypeWrapper.insertAdjacentElement('afterend', nomorLambung);
    } catch (error) {
        console.error(error);
    }
}

document.addEventListener('DOMContentLoaded', injectUpdateJobDetailExtraFields);
