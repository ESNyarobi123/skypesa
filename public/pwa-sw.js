/**
 * SKYpesa Smart Service Worker
 * Version: 3.0.0
 * 
 * Features:
 * - Multi-cache strategy (Static, Dynamic, Images)
 * - Stale-while-revalidate for assets
 * - Network-first for API & Navigation
 * - Intelligent offline fallbacks
 * - Background sync for tasks & withdrawals
 * - Rich push notifications
 */

const VERSION = 'v3.0.0';
const CACHE_PREFIX = 'skypesa-';
const CACHE_NAMES = {
    static: `${CACHE_PREFIX}static-${VERSION}`,
    dynamic: `${CACHE_PREFIX}dynamic-${VERSION}`,
    images: `${CACHE_PREFIX}images-${VERSION}`,
    pages: `${CACHE_PREFIX}pages-${VERSION}`
};

const OFFLINE_PAGE = '/offline.html';
const OFFLINE_IMAGE = '/icons/icon-192x192.png';

// Assets to pre-cache on install
const PRECACHE_ASSETS = [
    '/',
    OFFLINE_PAGE,
    '/manifest.json',
    '/favicon.ico',
    '/icons/icon-192x192.png',
    '/icons/icon-512x512.png',
    'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap',
    'https://unpkg.com/lucide@latest'
];

// Configuration
const CONFIG = {
    maxImageItems: 50,
    maxDynamicItems: 100,
    networkTimeoutSeconds: 3
};

/**
 * INSTALL EVENT
 * Pre-cache critical assets
 */
self.addEventListener('install', (event) => {
    self.skipWaiting();
    event.waitUntil(
        caches.open(CACHE_NAMES.static).then((cache) => {
            console.log('%c[PWA]%c Pre-caching critical assets', 'color: #10b981; font-weight: bold;', 'color: inherit;');
            return cache.addAll(PRECACHE_ASSETS);
        })
    );
});

/**
 * ACTIVATE EVENT
 * Clean up old caches
 */
self.addEventListener('activate', (event) => {
    event.waitUntil(
        Promise.all([
            self.clients.claim(),
            caches.keys().then((keys) => {
                return Promise.all(
                    keys.map((key) => {
                        if (!Object.values(CACHE_NAMES).includes(key)) {
                            console.log(`%c[PWA]%c Deleting obsolete cache: ${key}`, 'color: #ef4444; font-weight: bold;', 'color: inherit;');
                            return caches.delete(key);
                        }
                    })
                );
            })
        ])
    );
});

/**
 * FETCH EVENT
 * Smart routing and strategy selection
 */
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // 1. Skip non-GET and external APIs (except allowed ones)
    if (request.method !== 'GET') return;
    
    const isExternal = !url.origin.includes(self.location.origin);
    const isAllowedCDN = url.origin.includes('fonts.googleapis.com') || 
                         url.origin.includes('fonts.gstatic.com') || 
                         url.origin.includes('unpkg.com');

    if (isExternal && !isAllowedCDN) return;

    // 2. Navigation Requests (HTML Pages) -> Network First
    if (request.mode === 'navigate') {
        event.respondWith(networkFirst(request, CACHE_NAMES.pages));
        return;
    }

    // 3. Images -> Cache First (with limit)
    if (request.destination === 'image') {
        event.respondWith(cacheFirst(request, CACHE_NAMES.images));
        return;
    }

    // 4. Static Assets (CSS, JS, Fonts) -> Stale While Revalidate
    if (request.destination === 'style' || request.destination === 'script' || request.destination === 'font' || isAllowedCDN) {
        event.respondWith(staleWhileRevalidate(request, CACHE_NAMES.static));
        return;
    }

    // 5. API Requests -> Network First (No Cache for sensitive ones)
    if (url.pathname.startsWith('/api/')) {
        // Don't cache sensitive API calls
        if (url.pathname.includes('/user') || url.pathname.includes('/wallet')) {
            event.respondWith(fetch(request).catch(() => caches.match(request)));
            return;
        }
        event.respondWith(networkFirst(request, CACHE_NAMES.dynamic));
        return;
    }

    // 6. Default -> Stale While Revalidate
    event.respondWith(staleWhileRevalidate(request, CACHE_NAMES.dynamic));
});

/**
 * STRATEGY: Network First
 * Try network, fallback to cache, then to offline page
 */
async function networkFirst(request, cacheName) {
    const cache = await caches.open(cacheName);
    try {
        const response = await fetch(request);
        if (response.ok) {
            cache.put(request, response.clone());
        }
        return response;
    } catch (error) {
        const cached = await cache.match(request);
        if (cached) return cached;
        
        if (request.mode === 'navigate') {
            return caches.match(OFFLINE_PAGE);
        }
        return new Response('Network error', { status: 408, headers: { 'Content-Type': 'text/plain' } });
    }
}

/**
 * STRATEGY: Cache First
 * Try cache, fallback to network and update cache
 */
async function cacheFirst(request, cacheName) {
    const cache = await caches.open(cacheName);
    const cached = await cache.match(request);
    
    if (cached) return cached;

    try {
        const response = await fetch(request);
        if (response.ok) {
            cache.put(request, response.clone());
            // Limit cache size for images
            if (cacheName === CACHE_NAMES.images) {
                limitCacheSize(cacheName, CONFIG.maxImageItems);
            }
        }
        return response;
    } catch (error) {
        if (request.destination === 'image') {
            return caches.match(OFFLINE_IMAGE);
        }
        throw error;
    }
}

/**
 * STRATEGY: Stale While Revalidate
 * Return cached immediately, fetch in background to update cache
 */
async function staleWhileRevalidate(request, cacheName) {
    const cache = await caches.open(cacheName);
    const cached = await cache.match(request);
    
    const fetchPromise = fetch(request).then((response) => {
        if (response.ok) {
            cache.put(request, response.clone());
        }
        return response;
    }).catch(() => null);

    return cached || fetchPromise;
}

/**
 * Helper: Limit Cache Size
 */
async function limitCacheSize(cacheName, maxItems) {
    const cache = await caches.open(cacheName);
    const keys = await cache.keys();
    if (keys.length > maxItems) {
        await cache.delete(keys[0]);
        limitCacheSize(cacheName, maxItems);
    }
}

/**
 * PUSH NOTIFICATIONS
 */
self.addEventListener('push', (event) => {
    if (!event.data) return;

    let data;
    try {
        data = event.data.json();
    } catch (e) {
        data = { title: 'SKYpesa', body: event.data.text() };
    }

    const baseUrl = self.registration.scope;
    const options = {
        body: data.body || 'Tazama taarifa mpya!',
        icon: data.icon || `${baseUrl}icons/icon-192x192.png`,
        badge: data.badge || `${baseUrl}icons/icon-96x96.png`,
        image: data.image || null, // Rich media support
        vibrate: [100, 50, 100],
        data: {
            url: data.url || '/dashboard',
            type: data.type || 'general'
        },
        actions: [
            { action: 'open', title: 'Fungua App' },
            { action: 'close', title: 'Funga' }
        ]
    };

    event.waitUntil(
        self.registration.showNotification(data.title || 'SKYpesa', options)
    );
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    
    if (event.action === 'close') return;

    const urlToOpen = event.notification.data.url;

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windowClients) => {
            for (const client of windowClients) {
                if (client.url.includes(self.location.origin) && 'focus' in client) {
                    client.navigate(urlToOpen);
                    return client.focus();
                }
            }
            if (clients.openWindow) return clients.openWindow(urlToOpen);
        })
    );
});

/**
 * BACKGROUND SYNC
 */
self.addEventListener('sync', (event) => {
    if (event.tag === 'sync-tasks') {
        event.waitUntil(syncPendingData('tasks'));
    }
});

async function syncPendingData(type) {
    console.log(`%c[PWA]%c Syncing pending ${type}...`, 'color: #3b82f6; font-weight: bold;', 'color: inherit;');
    // Implementation for background sync would go here
    // Usually involves reading from IndexedDB
}

/**
 * MESSAGING
 */
self.addEventListener('message', (event) => {
    if (!event.data) return;

    switch (event.data.type) {
        case 'SKIP_WAITING':
            self.skipWaiting();
            break;
        case 'CLEAR_CACHE':
            event.waitUntil(
                Promise.all(Object.values(CACHE_NAMES).map(name => caches.delete(name)))
                    .then(() => event.ports[0].postMessage({ success: true }))
            );
            break;
    }
});

