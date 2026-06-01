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
    {
        path: '/payment-settings',
        label: 'Payment Settings',
    },
    {
        path: '/sparepart-recommendations',
        label: 'Recommendation Control',
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

function normalizeText(value) {
    return String(value || '')
        .toLowerCase()
        .replaceAll('_', ' ')
        .replaceAll('-', ' ')
        .replace(/\s+/g, ' ')
        .trim();
}

function userCanManagePaymentSettings() {
    const sidebar = document.querySelector('#sidebar');

    if (!sidebar) {
        return false;
    }

    const footerText = normalizeText(sidebar.querySelector('.border-t')?.textContent || '');

    return footerText.includes('super admin') ||
        footerText.includes('superadmin') ||
        footerText.includes('admin');
}

function menuMetaForLink(link) {
    const path = normalizePath(link.getAttribute('href'));

    return MAIN_MENU_ITEMS.find((item) => path === item.path || path.startsWith(`${item.path}/`));
}

function setLinkLabel(link, label) {
    const svg = link.querySelector('svg');
    const svgHtml = svg ? svg.outerHTML : '';

    link.innerHTML = `${svgHtml}
        ${label}
    `;
}

function commandCenterLink(nav) {
    return Array.from(nav.querySelectorAll('a')).find((link) => {
        return normalizePath(link.getAttribute('href')) === '/command-center' ||
            link.textContent.trim().toLowerCase().includes('command center');
    });
}

function fallbackAnchorLink(nav) {
    return commandCenterLink(nav) || Array.from(nav.querySelectorAll('a')).find((link) => {
        return normalizePath(link.getAttribute('href')) === '/dashboard';
    });
}

function createSidebarLink({ path, label, datasetKey, iconPath }) {
    const isActive = window.location.pathname === path || window.location.pathname.startsWith(`${path}/`);
    const activeClass = isActive ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900';
    const iconClass = isActive ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600';

    const link = document.createElement('a');
    link.href = path;
    link.dataset[datasetKey] = 'true';
    link.className = `group flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-colors mt-1 ${activeClass}`;
    link.innerHTML = `
        <svg class="w-5 h-5 mr-3 ${iconClass}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${iconPath}"></path>
        </svg>
        ${label}
    `;

    return link;
}

function ensureRentalSparepartSidebarLink() {
    const nav = sidebarNav();

    if (!nav || document.querySelector('[data-rental-sparepart-link="true"]')) {
        return;
    }

    const anchorLink = fallbackAnchorLink(nav);

    if (!anchorLink) {
        return;
    }

    const link = createSidebarLink({
        path: '/rental-spareparts',
        label: 'Management Sparepart',
        datasetKey: 'rentalSparepartLink',
        iconPath: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
    });

    anchorLink.insertAdjacentElement('afterend', link);
}

function ensureRecommendationControlSidebarLink() {
    const nav = sidebarNav();

    if (!nav || document.querySelector('[data-recommendation-control-link="true"]')) {
        return;
    }

    const anchorLink = fallbackAnchorLink(nav);

    if (!anchorLink) {
        return;
    }

    const link = createSidebarLink({
        path: '/sparepart-recommendations',
        label: 'Recommendation Control',
        datasetKey: 'recommendationControlLink',
        iconPath: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
    });

    anchorLink.insertAdjacentElement('afterend', link);
}

function ensurePaymentSettingsSidebarLink() {
    const nav = sidebarNav();

    if (!nav || !userCanManagePaymentSettings() || document.querySelector('[data-payment-settings-link="true"]')) {
        return;
    }

    const anchorLink = fallbackAnchorLink(nav);

    if (!anchorLink) {
        return;
    }

    const link = createSidebarLink({
        path: '/payment-settings',
        label: 'Payment Settings',
        datasetKey: 'paymentSettingsLink',
        iconPath: 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z',
    });

    anchorLink.insertAdjacentElement('afterend', link);
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
    ensureRecommendationControlSidebarLink();
    ensurePaymentSettingsSidebarLink();
    translateMainMenuHeading();
    sortMainSidebarMenu();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootSidebarMainMenu);
} else {
    bootSidebarMainMenu();
}
