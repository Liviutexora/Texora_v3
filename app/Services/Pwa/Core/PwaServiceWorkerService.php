<?php

namespace App\Services\Pwa\Core;

use App\Services\Pwa\Contracts\PwaVersionSourceInterface;

class PwaServiceWorkerService
{
    public function __construct(private readonly PwaVersionSourceInterface $versionSource)
    {
    }

    public function render(): string
    {
        $version = $this->versionSource->current();
        $cachePrefix = (string) config('pwa.service_worker.cache_prefix', 'laravel-pwa');

        return <<<JS
const CACHE_PREFIX = '{$cachePrefix}';
const APP_VERSION = '{$version->version}';
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
JS;
    }
}