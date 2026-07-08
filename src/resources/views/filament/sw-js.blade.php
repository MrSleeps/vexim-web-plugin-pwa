@php
    $cacheName = 'pwa-v1';
    $cacheDuration = (int) ($settings['pwa_cache_duration'] ?? 86400);
    $allowOffline = filter_var($settings['pwa_allow_offline'] ?? true, FILTER_VALIDATE_BOOLEAN);
    $routesToCache = $settings['pwa_routes_to_cache'] ?? '/';
    $excludeRoutes = $settings['pwa_exclude_routes'] ?? '/admin,/login';
    
    // Parse routes
    $routesArray = array_map('trim', explode(',', $routesToCache));
    $excludeArray = array_map('trim', explode(',', $excludeRoutes));
    
    // Filter out invalid routes and external URLs
    $validRoutes = array_filter($routesArray, function($route) {
        // Skip external URLs
        if (str_starts_with($route, 'http://') || str_starts_with($route, 'https://')) {
            return false;
        }
        // Skip admin and login
        if (str_contains($route, 'admin') || str_contains($route, 'login')) {
            return false;
        }
        // Skip api routes
        if (str_starts_with($route, 'api/')) {
            return false;
        }
        return true;
    });
    
    // Always include root
    if (!in_array('/', $validRoutes)) {
        array_unshift($validRoutes, '/');
    }
    
    $routesToCacheJson = json_encode(array_values($validRoutes));
    $excludeRoutesJson = json_encode($excludeArray);
    $offlineEnabled = $allowOffline ? 'true' : 'false';
@endphp

const CACHE_NAME = '{{ $cacheName }}';
const CACHE_DURATION = {{ $cacheDuration }};
const OFFLINE_ENABLED = {{ $offlineEnabled }};
const ROUTES_TO_CACHE = {!! $routesToCacheJson !!};
const EXCLUDE_ROUTES = {!! $excludeRoutesJson !!};

// Install event - cache essential files
self.addEventListener('install', function(event) {
    
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(function(cache) {
                
                // Only cache routes that exist and are internal
                const cachePromises = ROUTES_TO_CACHE.map(function(route) {
                    // Skip external URLs
                    if (route.startsWith('http://') || route.startsWith('https://')) {
                        return Promise.resolve();
                    }
                    
                    return fetch(route, { method: 'HEAD' })
                        .then(function(response) {
                            if (response.ok) {
                                return cache.add(route);
                            } else {
                                console.warn('Service Worker: Route not found - ' + route);
                                return Promise.resolve();
                            }
                        })
                        .catch(function() {
                            console.warn('Service Worker: Failed to fetch - ' + route);
                            return Promise.resolve();
                        });
                });
                
                return Promise.allSettled(cachePromises);
            })
            .then(function() {
                return self.skipWaiting();
            })
    );
});

// Activate event - clean up old caches
self.addEventListener('activate', function(event) {
    
    event.waitUntil(
        caches.keys().then(function(cacheNames) {
            return Promise.all(
                cacheNames.map(function(cacheName) {
                    if (cacheName !== CACHE_NAME) {
                        return caches.delete(cacheName);
                    }
                })
            );
        })
        .then(function() {
            return self.clients.claim();
        })
    );
});

// Fetch event - serve from cache or network
self.addEventListener('fetch', function(event) {
    const url = new URL(event.request.url);
    
    // Only handle GET requests
    if (event.request.method !== 'GET') {
        return;
    }
    
    // Skip external URLs (avatars, external APIs, etc.)
    if (url.origin !== self.location.origin) {
        return; // Don't intercept external requests
    }
    
    // Skip API requests (especially those that might break)
    if (url.pathname.startsWith('/api/')) {
        return;
    }
    
    // Check if the request should be excluded from caching
    const shouldExclude = EXCLUDE_ROUTES.some(function(pattern) {
        if (typeof pattern === 'string') {
            return url.pathname.includes(pattern);
        }
        return pattern.test(url.pathname);
    });

    // Don't cache excluded routes or if offline is disabled
    if (shouldExclude || !OFFLINE_ENABLED) {
        return;
    }

    event.respondWith(
        fetch(event.request)
            .then(function(networkResponse) {
                if (networkResponse && networkResponse.status === 200) {
                    const responseToCache = networkResponse.clone();
                    caches.open(CACHE_NAME).then(cache => cache.put(event.request, responseToCache));
                }
                return networkResponse;
            })
            .catch(function() {
                return caches.match(event.request).then(function(cachedResponse) {
                    return cachedResponse || new Response('Offline - Please check your internet connection', {
                        status: 503,
                        statusText: 'Service Unavailable'
                    });
                });
            })
    );
});