/*
 * Service worker PWA — cache shell + offline-lite (docs/13 §7.1).
 * Kebijakan privasi:
 *  - HALAMAN PANEL TIDAK PERNAH di-cache (data privat tidak tersimpan di device).
 *  - Hanya asset statis same-origin yang di-cache.
 *  - Navigasi: network-first; offline → fallback cache bila ada, else response 503.
 */
const CACHE = 'pmr-shell-v1';
const SHELL = [
    '/manifest.json',
    '/icons/icon-192.png',
    '/icons/icon-512.png',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE)
            .then((cache) => cache.addAll(SHELL))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(
                keys.filter((key) => key !== CACHE).map((key) => caches.delete(key))
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

    // Asset build (Vite) & icon: cache-first + isi cache saat fetch pertama
    if (url.pathname.startsWith('/build/') || url.pathname.startsWith('/icons/')) {
        event.respondWith(
            caches.match(request).then((cached) => {
                if (cached) {
                    return cached;
                }
                return fetch(request).then((response) => {
                    if (response.ok) {
                        const copy = response.clone();
                        caches.open(CACHE).then((cache) => cache.put(request, copy));
                    }
                    return response;
                });
            })
        );
        return;
    }

    // Navigasi (termasuk panel): network-first, JANGAN simpan respons ke cache
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(() =>
                caches.match(request).then((cached) => cached ||
                    new Response('Anda sedang offline.', {
                        status: 503,
                        headers: { 'Content-Type': 'text/plain; charset=utf-8' },
                    }))
            )
        );
    }
});
