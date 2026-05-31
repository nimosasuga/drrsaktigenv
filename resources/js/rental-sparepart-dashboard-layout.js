// PATH FILE: resources/js/rental-sparepart-dashboard-layout.js

function tidyRentalSparepartDashboardHeader() {
    if (!window.location.pathname.startsWith('/rental-spareparts')) {
        return;
    }

    const title = Array.from(document.querySelectorAll('h1')).find((heading) => {
        return heading.textContent.trim().toLowerCase().includes('dashboard stok sparepart rental');
    });

    if (!title) {
        return;
    }

    const titleBlock = title.closest('div');
    const headerRow = titleBlock?.parentElement;

    if (!headerRow || headerRow.dataset.sparepartHeaderTidied === 'true') {
        return;
    }

    const actionBlock = Array.from(headerRow.children).find((child) => {
        return child !== titleBlock && child.querySelector('a, button');
    });

    if (!actionBlock) {
        return;
    }

    headerRow.dataset.sparepartHeaderTidied = 'true';
    headerRow.className = 'flex flex-col gap-5';
    titleBlock.className = 'max-w-5xl';
    actionBlock.className = 'flex flex-wrap items-center gap-2';

    const departmentCard = actionBlock.querySelector('div');

    if (departmentCard) {
        departmentCard.className = 'inline-flex min-w-[150px] items-center justify-between gap-4 rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3';
    }

    actionBlock.querySelectorAll('a').forEach((link) => {
        link.classList.remove('lg:flex-col');
        link.classList.add('min-w-[145px]');
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', tidyRentalSparepartDashboardHeader);
} else {
    tidyRentalSparepartDashboardHeader();
}
