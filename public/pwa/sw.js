const CACHE_PREFIX = 'texora-pwa';
const APP_VERSION = '1.3.0';
const ACTIVE_CACHE = CACHE_PREFIX + ':' + APP_VERSION;

self.addEventListener('install', (event) => {
    event.waitUntil(self.skipWaiting());
});

self.addEventListener('activate', (event) => {
    event.waitUntil((async () => {
        const keys = await caches.keys();

        await Promise.all(
            keys
                .filter((key) => key.startsWith(CACHE_PREFIX) && key !== ACTIVE_CACHE)
                .map((key) => caches.delete(key)),
        );

        await self.clients.claim();

        const clients = await self.clients.matchAll({ type: 'window' });

        for (const client of clients) {
            client.postMessage({ type: 'PWA_VERSION_READY', version: APP_VERSION });
        }
    })());
});

self.addEventListener('message', (event) => {
    if (event.data?.type === 'PWA_SKIP_WAITING') {
        self.skipWaiting();
    }
});