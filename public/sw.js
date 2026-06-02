/*
|--------------------------------------------------------------------------
| PATH FILE:
| public/sw.js
|--------------------------------------------------------------------------
|
| Conservative service worker for DRR SAKTI GEN V.
| It caches only static shell assets and offline fallback.
| It does NOT cache authenticated application pages, forms, API responses,
| dashboards, sparepart data, update job pages, or payment pages.
|
*/

const CACHE_VERSION = 'drr-sakti-pwa-v1';
const OFFLINE_URL = '/offline.html';

const STATIC_ASSETS = [
    OFFLINE_URL,
    '/images/icon.png',
    '/manifest.webmanifest'
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_VERSION)
            .then((cache) => cache.addAll(STATIC_ASSETS))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(
                keys
                    .filter((key) => key !== CACHE_VERSION)
                    .map((key) => caches.delete(key))
            ))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const request = event.request;

    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);

    if (url.origin !== self.location.origin) {
        return;
    }

    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(() => caches.match(OFFLINE_URL))
        );
        return;
    }

    const isStaticAsset =
        url.pathname.startsWith('/build/') ||
        url.pathname.startsWith('/images/') ||
        url.pathname === '/manifest.webmanifest' ||
        url.pathname === OFFLINE_URL;

    if (!isStaticAsset) {
        return;
    }

    event.respondWith(
        caches.match(request).then((cachedResponse) => {
            if (cachedResponse) {
                return cachedResponse;
            }

            return fetch(request).then((networkResponse) => {
                if (!networkResponse || networkResponse.status !== 200 || networkResponse.type !== 'basic') {
                    return networkResponse;
                }

                const responseToCache = networkResponse.clone();

                caches.open(CACHE_VERSION).then((cache) => {
                    cache.put(request, responseToCache);
                });

                return networkResponse;
            });
        })
    );
});
