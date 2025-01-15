self.addEventListener('install', function(event) {

    event.waitUntil(

        caches.open('pwa-cache').then(function(cache) {

            return cache.addAll([

                '/css/app.css',

                '/js/app.js',

                '/images/logo.png',

                // Add other assets to cache

            ]);

        })

    );

});