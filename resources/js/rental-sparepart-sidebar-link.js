// PATH FILE: resources/js/rental-sparepart-sidebar-link.js

const MAIN_MENU_ITEMS = [
    {
        path: '/assets',
        label: 'Asset Management',
    },
    {
        path: '/command-center',
        label: 'Command Center',
    },
    {
        path: '/dashboard',
        label: 'Dashboard',
    },
    {
        path: '/rental-spareparts',
        label: 'Management Sparepart',
    },
    {
        path: '/profile',
        label: 'My Profile',
    },
];

function sidebarNav() {
    return document.querySelector('#sidebar nav');
}

function normalizePath(href) {
    try {
        return new URL(href, window.location.origin).pathname.replace(/\/$/, '') || '/';
    } catch (error) {
        return href || '';
    }
}

function menuMetaForLink(link) {
    const path = normalizePath(link.getAttribute('href'));

    return MAIN_MENU_ITEMS.find((item) => path === item.path || path.startsWith(`${item.path}/`));
}

function setLinkLabel(link, label) {
    const svg = link.querySelector('svg');
    const svgHtml = svg ? svg.outerHTML : '';

    link.innerHTML = `${svgHtml}\n        ${label}\n    `;
}

function ensureRentalSparepartSidebarLink() {
    const nav = sidebarNav();

    if (!nav || document.querySelector('[data-rental-sparepart-link="true"]')) {
        return;
    }

    const commandCenterLink = Array.from(nav.querySelectorAll('a')).find((link) => {
        return normalizePath(link.getAttribute('href')) === '/command-center' ||
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

function translateMainMenuHeading() {
    const nav = sidebarNav();

    if (!nav) {
        return;
    }

    const heading = Array.from(nav.querySelectorAll('p')).find((item) => {
        return item.textContent.trim().toLowerCase() === 'menu utama' ||
            item.textContent.trim().toLowerCase() === 'main menu';
    });

    if (heading) {
        heading.textContent = 'Main Menu';
    }
}

function sortMainSidebarMenu() {
    const nav = sidebarNav();

    if (!nav) {
        return;
    }

    const mainHeading = Array.from(nav.children).find((child) => {
        return child.tagName === 'P' && ['menu utama', 'main menu'].includes(child.textContent.trim().toLowerCase());
    });

    if (!mainHeading) {
        return;
    }

    const sortableLinks = Array.from(nav.querySelectorAll('a'))
        .map((link) => ({ link, meta: menuMetaForLink(link) }))
        .filter((entry) => entry.meta)
        .map((entry) => {
            setLinkLabel(entry.link, entry.meta.label);
            return entry;
        })
        .sort((first, second) => first.meta.label.localeCompare(second.meta.label));

    let insertAfter = mainHeading;

    sortableLinks.forEach((entry) => {
        insertAfter.insertAdjacentElement('afterend', entry.link);
        insertAfter = entry.link;
    });
}

function bootSidebarMainMenu() {
    ensureRentalSparepartSidebarLink();
    translateMainMenuHeading();
    sortMainSidebarMenu();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootSidebarMainMenu);
} else {
    bootSidebarMainMenu();
}
