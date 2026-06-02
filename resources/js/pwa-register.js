// PATH FILE: resources/js/pwa-register.js

function ensureMeta(name, content) {
    if (document.querySelector(`meta[name="${name}"]`)) {
        return;
    }

    const meta = document.createElement('meta');
    meta.name = name;
    meta.content = content;
    document.head.appendChild(meta);
}

function ensureLink(rel, href, attributes = {}) {
    if (document.querySelector(`link[rel="${rel}"][href="${href}"]`)) {
        return;
    }

    const link = document.createElement('link');
    link.rel = rel;
    link.href = href;

    Object.entries(attributes).forEach(([key, value]) => {
        link.setAttribute(key, value);
    });

    document.head.appendChild(link);
}

function bootPwa() {
    ensureMeta('theme-color', '#2563eb');
    ensureMeta('mobile-web-app-capable', 'yes');
    ensureMeta('apple-mobile-web-app-capable', 'yes');
    ensureMeta('apple-mobile-web-app-status-bar-style', 'default');
    ensureMeta('apple-mobile-web-app-title', 'DRR SAKTI');

    ensureLink('manifest', '/manifest.webmanifest');
    ensureLink('icon', '/images/icon.png', { type: 'image/png' });
    ensureLink('apple-touch-icon', '/images/icon.png');

    if (!('serviceWorker' in navigator)) {
        return;
    }

    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch(() => {
            // App remains fully usable even if PWA registration fails.
        });
    });
}

bootPwa();
