import { initializeApp } from "firebase/app";
import { getMessaging, getToken, onMessage } from "firebase/messaging";

const firebaseConfig = {
    apiKey: "AIzaSyBD96VaHGW7rcjmMFi5FoFubpjO-QFmR-I",
    authDomain: "abodafarm-71166.firebaseapp.com",
    projectId: "abodafarm-71166",
    storageBucket: "abodafarm-71166.firebasestorage.app",
    messagingSenderId: "625353760702",
    appId: "1:625353760702:web:471b92d430615cb3d2a3ef",
    measurementId: "G-BWVELQQ3XC",
};

const app = initializeApp(firebaseConfig);

// VAPID key for web push
const vapidKey = "BEl62iUYgUivxIkv69yViEuiBIa-Ib9-SkvMeAtA3LFgDzkrxZYJjR1glwV9Rv2VuTdU3poCTUDUM1MVoG0lHQQ";

let messaging = null;

/**
 * Initialize Firebase Messaging
 * Firebase compat SDK automatically uses /firebase-messaging-sw.js
 */
async function initializeMessaging() {
    if (!('serviceWorker' in navigator)) {
        console.log('Service workers not supported');
        return null;
    }

    if (!('Notification' in window)) {
        console.log('Notifications not supported');
        return null;
    }

    try {
        // Register custom service worker for better control
        await navigator.serviceWorker.register('/firebase-messaging-sw.js');

        messaging = getMessaging(app);
        console.log('Firebase messaging initialized');
        return messaging;
    } catch (error) {
        console.error('Error initializing messaging:', error);
        return null;
    }
}

/**
 * Request permission and get FCM token
 */
export async function requestPermissionAndGetToken() {
    try {
        if (!messaging) {
            await initializeMessaging();
        }

        if (!messaging) {
            console.log('Messaging not available');
            return null;
        }

        const permission = await Notification.requestPermission();
        if (permission === 'granted') {
            const swReg = await navigator.serviceWorker.ready;
            const token = await getToken(messaging, {
                vapidKey: vapidKey,
                serviceWorkerRegistration: swReg
            });

            if (token) {
                console.log('FCM Token obtained');
                await saveTokenToServer(token);
                return token;
            }
        } else {
            console.log('Notification permission denied');
        }
    } catch (error) {
        console.error('Error getting FCM token:', error);
    }
    return null;
}

/**
 * Save FCM token to server
 */
async function saveTokenToServer(token) {
    try {
        // Use web route with session auth for web app
        const response = await fetch('/fcm-token/save', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            },
            body: JSON.stringify({
                token: token,
                device_type: 'web',
                device_name: navigator.userAgent,
            }),
        });

        if (response.ok) {
            console.log('FCM token saved to server');
            return true;
        } else {
            console.error('Failed to save FCM token:', response.status);
            return false;
        }
    } catch (error) {
        console.error('Error saving FCM token:', error);
        return false;
    }
}

/**
 * Handle foreground messages
 */
export function onForegroundMessage() {
    if (!messaging) {
        console.log('Messaging not initialized');
        return;
    }

    onMessage(messaging, (payload) => {
        console.log('Foreground message received:', payload);

        const notification = payload.notification || payload.data;

        if (notification) {
            if (Notification.permission === 'granted') {
                new Notification(notification.title || 'AbodaFarm', {
                    body: notification.body || '',
                    icon: '/icons/IconTitle.png',
                    badge: '/icons/IconTitle.png',
                    tag: payload.data?.type || 'default',
                    data: payload.data,
                });
            }

            window.dispatchEvent(new CustomEvent('fcm-notification', {
                detail: {
                    title: notification.title,
                    body: notification.body,
                    data: payload.data,
                }
            }));
        }
    });
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', async () => {
    // Expose initialize function to window for manual calling
    window.initializeFcm = async function() {
        if (!('Notification' in window) || !('serviceWorker' in navigator)) {
            console.log('Notifications or service workers not supported');
            return;
        }

        await initializeMessaging();

        if (messaging) {
            await requestPermissionAndGetToken();
            onForegroundMessage();
        }
    };

    // Check if user is authenticated
    const isAuthenticated = document.body.dataset.authenticated === 'true' ||
                          document.querySelector('[data-user-id]') !== null;

    if (isAuthenticated) {
        // Initialize FCM automatically
        await window.initializeFcm();
    }
});

// Also initialize when Livewire loads (for SPAs)
if (window.Livewire) {
    window.Livewire.hook('component.initialized', () => {
        const isAuthenticated = document.body.dataset.authenticated === 'true' ||
                              document.querySelector('[data-user-id]') !== null;
        if (isAuthenticated && !window.fcmInitialized) {
            window.fcmInitialized = true;
            window.initializeFcm();
        }
    });
}
