const APP_VERSION = 'subdipass-guard-v1';

importScripts('https://storage.googleapis.com/workbox-cdn/releases/6.5.4/workbox-sw.js');

if (self.workbox) {
    const { core, precaching, routing, strategies, expiration, backgroundSync } = self.workbox;

    core.setCacheNameDetails({
        prefix: 'subdipass',
        suffix: 'v1',
    });

    precaching.precacheAndRoute(
        [
            { url: '/', revision: APP_VERSION },
            { url: '/guard/scanner', revision: APP_VERSION },
            { url: '/offline.html', revision: APP_VERSION },
            { url: '/manifest.webmanifest', revision: APP_VERSION },
        ],
        {
            cleanUrls: true,
        }
    );

    routing.registerRoute(
        ({ request }) => request.mode === 'navigate',
        new strategies.NetworkFirst({
            cacheName: 'subdipass-pages',
            networkTimeoutSeconds: 4,
            plugins: [
                new expiration.ExpirationPlugin({
                    maxEntries: 40,
                    purgeOnQuotaError: true,
                }),
            ],
        })
    );

    routing.registerRoute(
        ({ request }) => ['style', 'script', 'worker'].includes(request.destination),
        new strategies.StaleWhileRevalidate({
            cacheName: 'subdipass-assets',
            plugins: [
                new expiration.ExpirationPlugin({
                    maxEntries: 80,
                    maxAgeSeconds: 7 * 24 * 60 * 60,
                    purgeOnQuotaError: true,
                }),
            ],
        })
    );

    routing.registerRoute(
        ({ request }) => request.destination === 'image',
        new strategies.CacheFirst({
            cacheName: 'subdipass-images',
            plugins: [
                new expiration.ExpirationPlugin({
                    maxEntries: 60,
                    maxAgeSeconds: 30 * 24 * 60 * 60,
                    purgeOnQuotaError: true,
                }),
            ],
        })
    );

    const guardSyncQueue = new backgroundSync.BackgroundSyncPlugin('guard-scan-sync', {
        maxRetentionTime: 24 * 60, // minutes
    });

    routing.registerRoute(
        ({ url, request }) => request.method === 'POST' && url.pathname.startsWith('/guard/scans'),
        new strategies.NetworkOnly({
            plugins: [guardSyncQueue],
        }),
        'POST'
    );

    routing.setCatchHandler(async ({ event }) => {
        if (event.request.destination === 'document') {
            return caches.match('/offline.html');
        }
        return Response.error();
    });

    self.addEventListener('message', (event) => {
        if (event.data && event.data.type === 'SKIP_WAITING') {
            self.skipWaiting();
        }
    });
    self.addEventListener('push', (event) => {
        const defaultPayload = {
            title: 'SubdiPass Guard',
            body: 'New notification',
            url: '/guard/scanner',
        };

        let data = defaultPayload;
        if (event.data) {
            try {
                data = { ...defaultPayload, ...event.data.json() };
            } catch (error) {
                data = { ...defaultPayload, body: event.data.text() };
            }
        }

        const notificationPromise = self.registration.showNotification(data.title, {
            body: data.body,
            icon: '/icons/icon-192x192.png',
            badge: '/icons/icon-192x192.png',
            data: {
                url: data.url || data.actionUrl || '/guard/scanner',
            },
        });

        event.waitUntil(notificationPromise);
    });

    self.addEventListener('notificationclick', (event) => {
        event.notification.close();
        const destination = event.notification.data?.url || '/guard/scanner';

        event.waitUntil(
            clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
                for (const client of clientList) {
                    if ('focus' in client) {
                        client.postMessage({ type: 'OPEN_URL', url: destination });
                        return client.focus();
                    }
                }
                if (clients.openWindow) {
                    return clients.openWindow(destination);
                }
            })
        );
    });

    self.addEventListener('pushsubscriptionchange', () => {
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
            clientList.forEach((client) => {
                client.postMessage({ type: 'PUSH_SUBSCRIPTION_CHANGED' });
            });
        });
    });
} else {
    console.warn('Workbox failed to load. Offline support is disabled.');
}
