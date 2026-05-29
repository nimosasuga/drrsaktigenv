// PATH FILE: resources/js/command-center-sidebar-link.js

function ensureCommandCenterSidebarLink() {
    const sidebarNav = document.querySelector('#sidebar nav');

    if (!sidebarNav || document.querySelector('[data-command-center-link="true"]')) {
        return;
    }

    const assetLink = sidebarNav.querySelector('a[href$="/assets"], a[href*="/assets"]');
    const profileLink = sidebarNav.querySelector('a[href$="/profile"], a[href*="/profile"]');
    const anchor = assetLink || profileLink;

    if (!anchor) {
        return;
    }

    const link = document.createElement('a');
    link.href = '/command-center';
    link.dataset.commandCenterLink = 'true';
    link.className = 'group flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-colors mt-1 text-slate-600 hover:bg-slate-50 hover:text-slate-900';
    link.innerHTML = `
        <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3a1 1 0 012 0v18a1 1 0 11-2 0V3zM4 13a1 1 0 012 0v8a1 1 0 11-2 0v-8zM18 7a1 1 0 012 0v14a1 1 0 11-2 0V7z"></path>
        </svg>
        Command Center
    `;

    anchor.insertAdjacentElement('afterend', link);
}

document.addEventListener('DOMContentLoaded', ensureCommandCenterSidebarLink);
