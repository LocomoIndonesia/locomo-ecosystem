// PitiCoin.id Service Worker for RFC 8292 VAPID WebPush Notifications
const SW_VERSION = 'piticoin-sw-v1.0';

self.addEventListener('install', (event) => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(self.clients.claim());
});

self.addEventListener('push', (event) => {
    if (!event.data) {
        return;
    }

    let payload = {};
    try {
        payload = event.data.json();
    } catch (e) {
        payload = {
            title: 'PitiCoin Ekspedisi',
            body: event.data.text(),
        };
    }

    const title = payload.title || '🔔 Pemberitahuan PitiCoin.id';
    const options = {
        body: payload.body || 'Ada pembaruan status ekspedisi Anda.',
        icon: payload.icon || '/favicon.ico',
        badge: payload.badge || '/favicon.ico',
        vibrate: [150, 50, 150],
        tag: payload.tag || 'piticoin-notification',
        renotify: true,
        data: payload.data || { url: payload.url || '/dashboard' },
        actions: [
            {
                action: 'open',
                title: 'Buka Stasiun',
            },
            {
                action: 'dismiss',
                title: 'Tutup',
            }
        ]
    };

    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    if (event.action === 'dismiss') {
        return;
    }

    const targetUrl = (event.notification.data && event.notification.data.url) ? event.notification.data.url : '/dashboard';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windowClients) => {
            for (let i = 0; i < windowClients.length; i++) {
                const client = windowClients[i];
                if (client.url.includes(self.location.origin) && 'focus' in client) {
                    if (targetUrl) {
                        client.navigate(targetUrl);
                    }
                    return client.focus();
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(targetUrl);
            }
        })
    );
});
