const CACHE_NAME = 'smekisa-ekstra-cache-v1';

self.addEventListener('install', (event) => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(clients.claim());
});

self.addEventListener('fetch', (event) => {
    // Minimal fetch listener to pass PWA criteria.
    // For a dynamic Laravel app, we typically don't cache pages aggressively 
    // unless building offline functionality.
    event.respondWith(fetch(event.request).catch(() => {
        // Fallback for offline (optional)
        return new Response('Offline Mode');
    }));
});
