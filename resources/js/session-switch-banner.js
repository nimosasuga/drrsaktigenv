// PATH FILE: resources/js/session-switch-banner.js

(function () {
    async function loadSessionSwitchStatus() {
        try {
            const response = await fetch('/login-as/status', {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                return null;
            }

            return await response.json();
        } catch (error) {
            return null;
        }
    }

    function createBanner(payload) {
        if (!payload || !payload.active) {
            return;
        }

        if (document.querySelector('[data-session-switch-banner]')) {
            return;
        }

        const banner = document.createElement('div');
        banner.setAttribute('data-session-switch-banner', 'true');
        banner.className = 'fixed left-3 right-3 top-3 z-[9999] rounded-2xl border border-amber-300 bg-amber-50 px-4 py-3 text-amber-950 shadow-2xl sm:left-1/2 sm:right-auto sm:w-[640px] sm:-translate-x-1/2';
        banner.innerHTML = [
            '<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">',
            '<div>',
            '<p class="text-xs font-black uppercase tracking-[0.18em] text-amber-700">Mode Akses User</p>',
            '<p class="mt-1 text-sm font-bold">Anda sedang memakai akun: ' + escapeHtml(payload.impersonated_user_name || '-') + '</p>',
            '<p class="text-xs text-amber-700">Akun asal: ' + escapeHtml(payload.impersonator_name || '-') + ' · ' + escapeHtml(payload.impersonator_status_user || '-') + '</p>',
            '</div>',
            '<a href="' + escapeHtml(payload.stop_url || '/login-as/stop') + '" class="inline-flex items-center justify-center rounded-xl bg-amber-600 px-4 py-2 text-sm font-black text-white transition hover:bg-amber-700">Kembali ke Admin</a>',
            '</div>',
        ].join('');

        document.body.appendChild(banner);
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    document.addEventListener('DOMContentLoaded', async function () {
        const payload = await loadSessionSwitchStatus();
        createBanner(payload);
    });
})();
