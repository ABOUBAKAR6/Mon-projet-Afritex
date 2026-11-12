{
    "id": "/",
    "name": @json(boutique('nom').' - '.boutique('slogan')),
    "short_name": @json(boutique('nom')),
    "description": @json(boutique('description')),
    "lang": "fr",
    "dir": "ltr",
    "start_url": "/",
    "scope": "/",
    "display": "standalone",
    "orientation": "portrait",
    "background_color": "{{ couleur('foncee') }}",
    "theme_color": "{{ couleur('foncee') }}",
    "categories": ["shopping"],
    "icons": [
        { "src": "{{ icone_app('192') }}", "sizes": "192x192", "type": "image/png", "purpose": "any" },
        { "src": "{{ icone_app('512') }}", "sizes": "512x512", "type": "image/png", "purpose": "any" },
        { "src": "{{ icone_app('maskable-512') }}", "sizes": "512x512", "type": "image/png", "purpose": "maskable" }
    ],
    "shortcuts": [
        { "name": "Catalogue", "short_name": "Catalogue", "url": "{{ route('catalogue', [], false) }}", "icons": [{ "src": "{{ icone_app('192') }}", "sizes": "192x192", "type": "image/png" }] },
        { "name": "Mon panier", "short_name": "Panier", "url": "{{ route('panier', [], false) }}", "icons": [{ "src": "{{ icone_app('192') }}", "sizes": "192x192", "type": "image/png" }] },
        { "name": "Espace Pro", "short_name": "Espace Pro", "url": "{{ route('pro.accueil', [], false) }}", "icons": [{ "src": "{{ icone_app('192') }}", "sizes": "192x192", "type": "image/png" }] }
    ]
}
