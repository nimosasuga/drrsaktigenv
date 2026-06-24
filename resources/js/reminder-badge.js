// PATH FILE: resources/js/reminder-badge.js

(function () {
    function badgeText(count) {
        if (count > 99) {
            return '99+';
        }

        return String(count);
    }

    function isMainReminderNavLink(link) {
        const href = link.getAttribute('href') || '';

        if (href === '/reminders') {
            return true;
        }

        try {
            const url = new URL(href, window.location.origin);

            return url.pathname === '/reminders' && url.search === '';
        } catch (error) {
            return false;
        }
    }

    function findReminderLinks() {
        return Array.from(document.querySelectorAll('nav a[href]')).filter(isMainReminderNavLink);
    }

    function removeWrongBadges() {
        document.querySelectorAll('[data-reminder-badge]').forEach((badge) => {
            const link = badge.closest('a[href]');

            if (!link || !link.closest('nav') || !isMainReminderNavLink(link)) {
                badge.remove();
            }
        });
    }

    function ensureBadge(link, count) {
        let badge = link.querySelector('[data-reminder-badge]');

        if (count < 1) {
            if (badge) {
                badge.remove();
            }

            return;
        }

        link.classList.add('relative');

        if (!badge) {
            badge = document.createElement('span');
            badge.dataset.reminderBadge = 'true';
            badge.className = 'absolute -right-1 -top-1 inline-flex min-h-5 min-w-5 items-center justify-center rounded-full bg-red-600 px-1.5 text-[10px] font-black leading-none text-white shadow-sm ring-2 ring-white';
            link.appendChild(badge);
        }

        badge.textContent = badgeText(count);
        badge.setAttribute('aria-label', `${count} pengingat aktif`);
        badge.setAttribute('title', `${count} pengingat aktif`);
    }

    function renderReminderBadge(count) {
        removeWrongBadges();
        findReminderLinks().forEach((link) => ensureBadge(link, count));
    }

    function loadReminderCount() {
        fetch('/reminders/count', {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        })
            .then((response) => {
                if (!response.ok) {
                    throw new Error('Gagal mengambil jumlah pengingat.');
                }

                return response.json();
            })
            .then((data) => {
                const count = Number.parseInt(data.count, 10);
                renderReminderBadge(Number.isFinite(count) ? count : 0);
            })
            .catch(() => {
                renderReminderBadge(0);
            });
    }

    document.addEventListener('DOMContentLoaded', loadReminderCount);
})();
