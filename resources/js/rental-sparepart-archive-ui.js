// PATH FILE: resources/js/rental-sparepart-archive-ui.js

function currentLifecycleStatus() {
    const params = new URLSearchParams(window.location.search);
    return (params.get('stock_lifecycle_status') || 'ACTIVE').toUpperCase();
}

function lifecycleUrl(status) {
    const params = new URLSearchParams(window.location.search);
    params.set('stock_lifecycle_status', status);
    params.delete('page');

    return `/rental-spareparts?${params.toString()}`;
}

function addLifecycleFilterButtons() {
    if (window.location.pathname !== '/rental-spareparts') {
        return;
    }

    if (document.querySelector('[data-stock-lifecycle-filter="true"]')) {
        return;
    }

    const exportButton = Array.from(document.querySelectorAll('a')).find((anchor) => {
        return anchor.textContent.trim().toLowerCase() === 'export stok csv';
    });

    const actionBar = exportButton?.parentElement;

    if (!actionBar) {
        return;
    }

    actionBar.dataset.stockLifecycleFilter = 'true';
    actionBar.classList.add('items-center');

    const activeStatus = currentLifecycleStatus();

    const activeLink = document.createElement('a');
    activeLink.href = lifecycleUrl('ACTIVE');
    activeLink.className = `inline-flex min-w-[145px] items-center justify-center rounded-2xl px-5 py-3 text-sm font-black shadow-sm transition ${activeStatus === 'ACTIVE' ? 'bg-slate-900 text-white' : 'border border-slate-200 bg-white text-slate-700 hover:bg-slate-50'}`;
    activeLink.textContent = 'Active Stock';

    const archivedLink = document.createElement('a');
    archivedLink.href = lifecycleUrl('ARCHIVED');
    archivedLink.className = `inline-flex min-w-[145px] items-center justify-center rounded-2xl px-5 py-3 text-sm font-black shadow-sm transition ${activeStatus === 'ARCHIVED' ? 'bg-red-600 text-white' : 'border border-red-200 bg-white text-red-700 hover:bg-red-50'}`;
    archivedLink.textContent = 'Archived Stock';

    const insertAfter = Array.from(actionBar.querySelectorAll('a')).find((anchor) => {
        return anchor.textContent.trim().toLowerCase() === 'import history';
    }) || exportButton;

    insertAfter.insertAdjacentElement('afterend', archivedLink);
    insertAfter.insertAdjacentElement('afterend', activeLink);
}

function improveArchiveButtons() {
    if (window.location.pathname !== '/rental-spareparts') {
        return;
    }

    const activeStatus = currentLifecycleStatus();
    const stockForms = Array.from(document.querySelectorAll('form[action*="/rental-spareparts/stocks/"]'));

    stockForms.forEach((form) => {
        const methodInput = form.querySelector('input[name="_method"]');
        const button = form.querySelector('button[type="submit"]');

        if (!methodInput || methodInput.value.toUpperCase() !== 'DELETE' || !button) {
            return;
        }

        if (activeStatus === 'ARCHIVED') {
            const stockUrl = form.getAttribute('action');
            form.setAttribute('action', `${stockUrl}/restore`);
            methodInput.remove();
            form.setAttribute('onsubmit', "return confirm('Restore stok archived ini ke Active Stock? Qty akan dikembalikan dari archived_qty_on_hand.');");

            const token = form.querySelector('input[name="_token"]');
            const note = document.createElement('textarea');
            note.name = 'restore_note';
            note.rows = 2;
            note.placeholder = 'Catatan restore opsional';
            note.className = 'mb-2 w-full rounded-2xl border border-emerald-200 bg-white px-3 py-2 text-xs focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100';

            if (token && !form.querySelector('textarea[name="restore_note"]')) {
                token.insertAdjacentElement('afterend', note);
            }

            button.className = 'inline-flex w-full items-center justify-center rounded-2xl bg-emerald-600 px-4 py-3 text-sm font-black text-white hover:bg-emerald-700';
            button.textContent = 'Restore';

            const card = form.closest('.rounded-3xl');
            const editLink = card?.querySelector('a[href*="/edit"]');
            if (editLink) {
                editLink.remove();
            }
            return;
        }

        button.textContent = 'Archive';
        form.setAttribute('onsubmit', "return confirm('Archive baris stok ini? Data tidak dihapus permanen. Qty aktif akan menjadi 0 dan audit ADJUSTMENT dibuat.');");

        const token = form.querySelector('input[name="_token"]');
        const note = document.createElement('textarea');
        note.name = 'archive_note';
        note.rows = 2;
        note.placeholder = 'Catatan archive opsional';
        note.className = 'mb-2 w-full rounded-2xl border border-red-200 bg-white px-3 py-2 text-xs focus:border-red-500 focus:outline-none focus:ring-4 focus:ring-red-100';

        if (token && !form.querySelector('textarea[name="archive_note"]')) {
            token.insertAdjacentElement('afterend', note);
        }
    });
}

function bootRentalSparepartArchiveUi() {
    addLifecycleFilterButtons();
    improveArchiveButtons();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootRentalSparepartArchiveUi);
} else {
    bootRentalSparepartArchiveUi();
}
