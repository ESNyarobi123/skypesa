/**
 * SKYpesa Progressive Web App Service Worker
 * Handles caching, offline support, and background sync
 */

const CACHE_NAME = 'skypesa-v2.1.0';
const OFFLINE_URL = '/offline.html';

// Disable update prompts for minor cache updates
const SKIP_UPDATE_NOTIFICATION = true;

// Static assets to cache immediately
const STATIC_ASSETS = [
    '/',
    '/offline.html',
    '/manifest.json',
    '/favicon.ico',
    '/icons/icon-16x16.png',
    '/icons/icon-32x32.png',
    '/icons/icon-192x192.png',
    '/icons/icon-512x512.png',
    'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap',
    'https://unpkg.com/lucide@latest'
];

// API routes that should use network-first strategy
const API_ROUTES = [
    '/api/',
    '/dashboard',
    '/tasks',
    '/wallet',
    '/withdrawals',
    '/subscriptions',
    '/referrals'
];

// Install event - cache static assets
self.addEventListener('install', (event) => {
    console.log('[PWA] Installing Service Worker...');

    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                console.log('[PWA] Caching static assets');
                return cache.addAll(STATIC_ASSETS);
            })
            .then(() => {
                console.log('[PWA] Service Worker installed successfully');
                return self.skipWaiting();
            })
            .catch((error) => {
                console.error('[PWA] Failed to cache assets:', error);
            })
    );
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
    console.log('[PWA] Activating Service Worker...');

    event.waitUntil(
        caches.keys()
            .then((cacheNames) => {
                return Promise.all(
                    cacheNames
                        .filter((name) => name !== CACHE_NAME)
                        .map((name) => {
                            console.log('[PWA] Deleting old cache:', name);
                            return caches.delete(name);
                        })
                );
            })
            .then(() => {
                console.log('[PWA] Service Worker activated');
                return self.clients.claim();
            })
    );
});

// Fetch event - handle requests with appropriate strategy
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Skip non-GET requests
    if (request.method !== 'GET') {
        return;
    }

    // Skip requests to external domains (except fonts and CDN)
    if (!url.origin.includes(self.location.origin) &&
        !url.origin.includes('fonts.googleapis.com') &&
        !url.origin.includes('fonts.gstatic.com') &&
        !url.origin.includes('unpkg.com')) {
        return;
    }

    // Special handling for API routes - network first
    if (API_ROUTES.some(route => url.pathname.startsWith(route))) {
        event.respondWith(networkFirst(request));
        return;
    }

    // For navigation requests (HTML pages)
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request)
                .then((response) => {
                    // Cache a copy of the response
                    const responseClone = response.clone();
                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(request, responseClone);
                    });
                    return response;
                })
                .catch(() => {
                    // Return cached version or offline page
                    return caches.match(request)
                        .then((cachedResponse) => {
                            return cachedResponse || caches.match(OFFLINE_URL);
                        });
                })
        );
        return;
    }

    // For other static assets - cache first
    event.respondWith(cacheFirst(request));
});

// Cache first strategy - for static assets
async function cacheFirst(request) {
    const cached = await caches.match(request);
    if (cached) {
        // Refresh cache in background
        fetchAndCache(request);
        return cached;
    }
    return fetchAndCache(request);
}

// Network first strategy - for dynamic content
async function networkFirst(request) {
    try {
        const response = await fetch(request);
        // Cache successful responses
        if (response.ok) {
            const responseClone = response.clone();
            const cache = await caches.open(CACHE_NAME);
            cache.put(request, responseClone);
        }
        return response;
    } catch (error) {
        const cached = await caches.match(request);
        if (cached) {
            return cached;
        }
        // Return offline page for navigation requests
        if (request.mode === 'navigate') {
            return caches.match(OFFLINE_URL);
        }
        throw error;
    }
}

// Helper function to fetch and cache
async function fetchAndCache(request) {
    try {
        const response = await fetch(request);
        if (response.ok) {
            const responseClone = response.clone();
            const cache = await caches.open(CACHE_NAME);
            cache.put(request, responseClone);
        }
        return response;
    } catch (error) {
        console.error('[PWA] Fetch failed:', error);
        throw error;
    }
}

// Handle push notifications
self.addEventListener('push', (event) => {
    console.log('[PWA] Push notification received');

    // Get the base URL from the service worker scope
    const baseUrl = self.registration.scope.replace(/\/$/, '');

    // Default notification data with absolute URLs for icons
    let data = {
        title: 'SKYpesa',
        body: 'Tazama Task mpya!',
        icon: `${baseUrl}/icons/icon-192x192.png`,
        badge: `${baseUrl}/icons/icon-96x96.png`,
        tag: 'skypesa-notification'
    };

    if (event.data) {
        try {
            const pushData = event.data.json();
            // Merge but ensure icon paths are absolute
            data = { ...data, ...pushData };
            // If push data has relative icon paths, convert to absolute
            if (pushData.icon && !pushData.icon.startsWith('http')) {
                data.icon = `${baseUrl}${pushData.icon.startsWith('/') ? '' : '/'}${pushData.icon}`;
            }
            if (pushData.badge && !pushData.badge.startsWith('http')) {
                data.badge = `${baseUrl}${pushData.badge.startsWith('/') ? '' : '/'}${pushData.badge}`;
            }
        } catch (e) {
            data.body = event.data.text();
        }
    }

    const options = {
        body: data.body,
        icon: data.icon,
        badge: data.badge,
        tag: data.tag,
        vibrate: [100, 50, 100],
        renotify: true,
        requireInteraction: false,
        data: data.data || {},
        actions: data.actions || [
            { action: 'open', title: 'Fungua' },
            { action: 'close', title: 'Funga' }
        ]
    };

    console.log('[PWA] Showing notification with icon:', options.icon);

    event.waitUntil(
        self.registration.showNotification(data.title, options)
    );
});

// Handle notification click
self.addEventListener('notificationclick', (event) => {
    console.log('[PWA] Notification clicked');
    event.notification.close();

    const action = event.action;
    const notificationData = event.notification.data;

    if (action === 'close') {
        return;
    }

    // Navigate to appropriate page
    let urlToOpen = '/dashboard';

    if (notificationData.url) {
        urlToOpen = notificationData.url;
    } else if (notificationData.type === 'withdrawal') {
        urlToOpen = '/withdrawals';
    } else if (notificationData.type === 'task') {
        urlToOpen = '/tasks';
    } else if (notificationData.type === 'wallet') {
        urlToOpen = '/wallet';
    }

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true })
            .then((windowClients) => {
                // If a window is already open, focus it
                for (const client of windowClients) {
                    if (client.url.includes(self.location.origin) && 'focus' in client) {
                        client.navigate(urlToOpen);
                        return client.focus();
                    }
                }
                // Otherwise, open a new window
                if (clients.openWindow) {
                    return clients.openWindow(urlToOpen);
                }
            })
    );
});

// Handle background sync (for offline submissions)
self.addEventListener('sync', (event) => {
    console.log('[PWA] Background sync triggered:', event.tag);

    if (event.tag === 'sync-withdrawals') {
        event.waitUntil(syncWithdrawals());
    } else if (event.tag === 'sync-tasks') {
        event.waitUntil(syncTasks());
    }
});

// Sync pending withdrawals when back online
async function syncWithdrawals() {
    try {
        const cache = await caches.open(CACHE_NAME);
        const requests = await cache.keys();
        const pendingWithdrawals = requests.filter(r =>
            r.url.includes('pending-withdrawal')
        );

        for (const request of pendingWithdrawals) {
            const response = await cache.match(request);
            const data = await response.json();

            await fetch('/api/withdrawals', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            await cache.delete(request);
        }

        console.log('[PWA] Withdrawals synced successfully');
    } catch (error) {
        console.error('[PWA] Failed to sync withdrawals:', error);
        throw error;
    }
}

// Sync pending task completions
async function syncTasks() {
    console.log('[PWA] Syncing tasks...');
    // Similar to syncWithdrawals but for task completions
}

// Periodic background sync (for future use)
self.addEventListener('periodicsync', (event) => {
    if (event.tag === 'check-new-tasks') {
        event.waitUntil(checkNewTasks());
    }
});

async function checkNewTasks() {
    console.log('[PWA] Checking for new tasks...');
    // Could implement notification for new tasks
}

// Message handling for communication with main app
self.addEventListener('message', (event) => {
    console.log('[PWA] Message received:', event.data);

    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }

    if (event.data && event.data.type === 'GET_VERSION') {
        event.ports[0].postMessage({ version: CACHE_NAME });
    }

    if (event.data && event.data.type === 'CLEAR_CACHE') {
        caches.delete(CACHE_NAME).then(() => {
            event.ports[0].postMessage({ success: true });
        });
    }
});
