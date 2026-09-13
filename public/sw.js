// RishiSMM PWA Service Worker
const CACHE_NAME = 'rishismm-pwa-v3';
const OFFLINE_URL = '/';

const ASSETS_TO_CACHE = [
    '/',
    '/css/style.css',
    '/images/logo_smm.png',
    '/images/icons/icon-192x192.png',
    '/images/icons/icon-512x512.png'
];

// Install Event
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(ASSETS_TO_CACHE).catch(() => {});
        })
    );
    self.skipWaiting();
});

// Activate Event
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keyList) => {
            return Promise.all(
                keyList.map((key) => {
                    if (key !== CACHE_NAME) {
                        return caches.delete(key);
                    }
                })
            );
        })
    );
    self.clients.claim();
});

// Fetch Event (Only intercept same-origin static requests, bypass external APIs and CDN fonts)
self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET') {
        return;
    }

    const url = new URL(event.request.url);

    // Bypass any cross-origin requests (Google Fonts, Cloudflare Analytics, CDNs)
    if (url.origin !== self.location.origin) {
        return;
    }

    // Bypass API or dynamic routes
    if (url.pathname.startsWith('/api/') || url.pathname.startsWith('/payment/')) {
        return;
    }

    event.respondWith(
        fetch(event.request)
            .then((networkResponse) => {
                if (networkResponse && networkResponse.status === 200 && (
                    url.pathname.endsWith('.css') || 
                    url.pathname.endsWith('.js') || 
                    url.pathname.endsWith('.png') || 
                    url.pathname.endsWith('.jpg') || 
                    url.pathname.endsWith('.svg') ||
                    url.pathname.endsWith('.woff2')
                )) {
                    const responseClone = networkResponse.clone();
                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(event.request, responseClone);
                    }).catch(() => {});
                }
                return networkResponse;
            })
            .catch(() => {
                return caches.match(event.request).then((cachedResponse) => {
                    if (cachedResponse) {
                        return cachedResponse;
                    }
                    if (event.request.mode === 'navigate') {
                        return caches.match(OFFLINE_URL);
                    }
                    return new Response('', { status: 408, headers: { 'Content-Type': 'text/plain' } });
                });
            })
    );
});
