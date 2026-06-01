// PATH FILE: resources/js/rental-sparepart-adjustment-link.js

function addRentalSparepartAdjustmentLink() {
    if (window.location.pathname !== '/rental-spareparts') {
        return;
    }

    if (document.querySelector('[data-adjustment-import-link="true"]')) {
        return;
    }

    const actionBar = Array.from(document.querySelectorAll('div')).find((element) => {
        return element.className.includes('flex') &&
            element.textContent.includes('Import History') &&
            element.textContent.includes('Template Import');
    });

    if (!actionBar) {
        return;
    }

    const link = document.createElement('a');
    link.href = '/rental-spareparts/adjustments/create';
    link.dataset.adjustmentImportLink = 'true';
    link.className = 'inline-flex min-w-[145px] items-center justify-center rounded-2xl bg-orange-600 px-5 py-3 text-sm font-black text-white shadow-sm transition hover:bg-orange-700';
    link.textContent = 'Correction Stock';

    const importHistoryLink = Array.from(actionBar.querySelectorAll('a')).find((anchor) => {
        return anchor.textContent.trim().toLowerCase().includes('import history');
    });

    if (importHistoryLink) {
        importHistoryLink.insertAdjacentElement('afterend', link);
        return;
    }

    actionBar.appendChild(link);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', addRentalSparepartAdjustmentLink);
} else {
    addRentalSparepartAdjustmentLink();
}
