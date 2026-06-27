// Firebase Messaging Service Worker
importScripts(
    "https://www.gstatic.com/firebasejs/9.0.0/firebase-app-compat.js"
);
importScripts(
    "https://www.gstatic.com/firebasejs/9.0.0/firebase-messaging-compat.js"
);

firebase.initializeApp({
    apiKey: "AIzaSyBD96VaHGW7rcjmMFi5FoFubpjO-QFmR-I",
    authDomain: "abodafarm-71166.firebaseapp.com",
    projectId: "abodafarm-71166",
    storageBucket: "abodafarm-71166.firebasestorage.app",
    messagingSenderId: "625353760702",
    appId: "1:625353760702:web:471b92d430615cb3d2a3ef",
    measurementId: "G-BWVELQQ3XC",
});

const messaging = firebase.messaging();

// Handle background messages
messaging.onBackgroundMessage((payload) => {
    console.log('[FCM SW] Received background message:', payload);

    const notification = payload.notification || payload.data;

    if (notification) {
        const notificationTitle = notification.title || 'AbodaFarm';
        const notificationOptions = {
            body: notification.body || '',
            icon: '/icons/IconTitle.png',
            badge: '/icons/IconTitle.png',
            tag: payload.data?.type || 'default',
            data: payload.data,
            vibrate: [200, 100, 200],
            requireInteraction: true,
            actions: [
                { action: 'view', title: 'Lihat' },
                { action: 'dismiss', title: 'Tutup' }
            ]
        };

        return self.registration.showNotification(notificationTitle, notificationOptions);
    }
});

// Handle notification click
self.addEventListener('notificationclick', (event) => {
    console.log('[FCM SW] Notification click:', event);

    event.notification.close();

    if (event.action === 'dismiss') {
        return;
    }

    // Default action - open the app
    const urlToOpen = self.location.origin;

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true })
            .then((windowClients) => {
                // Check if there's already a window/tab open
                for (const client of windowClients) {
                    if (client.url.includes(self.location.origin) && 'focus' in client) {
                        return client.focus();
                    }
                }
                // If no window open, open new one
                if (clients.openWindow) {
                    return clients.openWindow(urlToOpen);
                }
            })
    );
});
