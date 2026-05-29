// PATH FILE: resources/js/penarikan-menu-link.js

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('a[href="#"]').forEach((link) => {
        const label = (link.textContent || '').trim().replace(/\s+/g, ' ');

        if (label === 'Penarikan Unit') {
            link.setAttribute('href', '/penarikans');
        }
    });
});
