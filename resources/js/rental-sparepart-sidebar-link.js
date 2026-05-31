// PATH FILE: resources/js/rental-sparepart-sidebar-link.js

function ensureRentalSparepartSidebarLink() {
    const sidebarNav = document.querySelector('#sidebar nav');

    if (!sidebarNav || document.querySelector('[data-rental-sparepart-link="true"]')) {
        return;
    }

    const commandCenterLink = Array.from(sidebarNav.querySelectorAll('a')).find((link) => {
        return link.getAttribute('href')?.includes('/command-center') ||
            link.textContent.trim().toLowerCase().includes('command center');
    });

    if (!commandCenterLink) {
        return;
    }

    const isActive = window.location.pathname.startsWith('/rental-spareparts');
    const activeClass = isActive ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900';
    const iconClass = isActive ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600';

    const link = document.createElement('a');
    link.href = '/rental-spareparts';
    link.dataset.rentalSparepartLink = 'true';
    link.className = `group flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-colors mt-1 ${activeClass}`;
    link.innerHTML = `
        <svg class="w-5 h-5 mr-3 ${iconClass}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
        </svg>
        Management Sparepart
    `;

    commandCenterLink.insertAdjacentElement('afterend', link);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', ensureRentalSparepartSidebarLink);
} else {
    ensureRentalSparepartSidebarLink();
}
