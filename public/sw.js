// Service worker kecil supaya notifikasi "pesanan siap" bisa tampil di HP (Android butuh ini).
self.addEventListener('install', () => self.skipWaiting());
self.addEventListener('activate', (event) => event.waitUntil(self.clients.claim()));

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    event.waitUntil(
        self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clients) => {
            const client = clients.find((c) => c.url.includes('/menu/pesanan/'));
            return client ? client.focus() : self.clients.openWindow('/menu/riwayat');
        }),
    );
});
