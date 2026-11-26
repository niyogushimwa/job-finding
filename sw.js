/* Service worker: offline access to previously viewed public pages and the app shell. */
const CACHE = 'joblink-v1';
const SHELL = ['./', './assets/css/style.css', './assets/js/app.js', './assets/img/icon.svg', './manifest.json'];
const PRIVATE = ['/dashboard', '/admin/', '/employer/', '/applications', '/messages', '/settings', '/profile', '/my-', '/notifications', '/api/'];

self.addEventListener('install', (event) => {
  event.waitUntil(caches.open(CACHE).then((cache) => cache.addAll(SHELL)).then(() => self.skipWaiting()));
});

self.addEventListener('activate', (event) => {
  event.waitUntil(caches.keys().then((keys) => Promise.all(keys.filter((k) => k !== CACHE).map((k) => caches.delete(k)))).then(() => self.clients.claim()));
});

self.addEventListener('fetch', (event) => {
  const request = event.request;
  if (request.method !== 'GET') return;
  const url = new URL(request.url);
  if (url.origin !== self.location.origin) return;
  if (PRIVATE.some((p) => url.pathname.includes(p))) return;

  if (request.mode === 'navigate' || url.pathname.endsWith('.css') || url.pathname.endsWith('.js')) {
    event.respondWith(
      fetch(request).then((response) => {
        if (response && response.status === 200) {
          const copy = response.clone();
          caches.open(CACHE).then((cache) => cache.put(request, copy));
        }
        return response;
      }).catch(() => caches.match(request).then((cached) => cached || caches.match('./')))
    );
  }
});
