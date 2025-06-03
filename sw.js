// sw.js - Service Worker (Basic Placeholder)

const CACHE_NAME = 'quiz-app-cache-v1';
const urlsToCache = [
  './wqai.html', // Cache the main HTML file
  // Add other important assets here if needed, e.g., CSS files, specific JS files
  // For this example, we'll keep it simple.
  // 'style.css', // If you had an external CSS
  // 'https://cdn.tailwindcss.com', // Be careful caching large external libraries
];

self.addEventListener('install', event => {
  // Perform install steps
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log('Opened cache');
        // Add core assets to cache. Caching external CDN might be tricky
        // and might not be necessary if user is expected to be online for API calls.
        // For a truly offline app, you'd cache more aggressively.
        return cache.addAll(urlsToCache.filter(url => !url.startsWith('http'))); // Filter out CDN for simplicity
      })
  );
});

self.addEventListener('fetch', event => {
  // For API calls (to Gemini), we always want to fetch from the network.
  if (event.request.url.includes('generativelanguage.googleapis.com')) {
    event.respondWith(fetch(event.request));
    return;
  }

  // For other requests, try cache first, then network.
  event.respondWith(
    caches.match(event.request)
      .then(response => {
        // Cache hit - return response
        if (response) {
          return response;
        }
        return fetch(event.request).then(
          networkResponse => {
            // Check if we received a valid response
            if(!networkResponse || networkResponse.status !== 200 || networkResponse.type !== 'basic') {
              return networkResponse;
            }

            // IMPORTANT: Clone the response. A response is a stream
            // and because we want the browser to consume the response
            // as well as the cache consuming the response, we need
            // to clone it so we have two streams.
            const responseToCache = networkResponse.clone();

            caches.open(CACHE_NAME)
              .then(cache => {
                // We only cache GET requests for our own assets
                if (event.request.method === 'GET' && !event.request.url.startsWith('http')) {
                    cache.put(event.request, responseToCache);
                }
              });

            return networkResponse;
          }
        );
      })
    );
});

self.addEventListener('activate', event => {
  const cacheWhitelist = [CACHE_NAME];
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheWhitelist.indexOf(cacheName) === -1) {
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
});
