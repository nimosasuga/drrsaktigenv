// PATH FILE: resources/js/reminder-badge.js

(function () {
    function formatBadgeCount(count) {
        if (count > 99) {
            return '99+';
        }

        return String(count);
    }

    function ensureBadge(reminderLink) {
        let badge = reminderLink.querySelector('[data-reminder-badge]');

        if (badge) {
            return badge;
        }

        badge = document.createElement('span');
        badge.setAttribute('data-reminder-badge', 'true');
        badge.className = 'absolute -top-1 -right-1 hidden min-w-[18px] rounded-full bg-red-600 px-1.5 py-0.5 text-center text-[10px] font-black leading-none text-white ring-2 ring-white';

        reminderLink.classList.add('relative');
        reminderLink.appendChild(badge);

        return badge;
    }

    async function refreshReminderBadge() {
        const reminderLink = document.querySelector('a[href$="/reminders"]');

        if (!reminderLink) {
            return;
        }

        try {
            const response = await fetch('/reminders/count', {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                return;
            }

            const payload = await response.json();
            const count = Number(payload.count || 0);
            const badge = ensureBadge(reminderLink);

            if (count <= 0) {
                badge.classList.add('hidden');
                badge.textContent = '';
                return;
            }

            badge.textContent = formatBadgeCount(count);
            badge.classList.remove('hidden');
        } catch (error) {
            // Badge is cosmetic. Do not block navigation if count endpoint fails.
        }
    }

    document.addEventListener('DOMContentLoaded', refreshReminderBadge);
})();
