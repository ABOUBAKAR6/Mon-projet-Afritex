{{--
  Appli de gestion (admin) : une appli à part de celle de la boutique (autre « id »),
  qui démarre sur /espace-client : formulaire de connexion, ou directement l'admin si on est déjà connecté.
  Annoncée par partials/tete sur les pages /admin et /espace-client.
--}}
{
    "id": "/espace-client",
    "name": @json(boutique('nom').' · Gestion de la boutique'),
    "short_name": @json('Admin '.boutique('nom')),
    "description": @json('Commandes, produits, stock et revendeurs de '.boutique('nom').'.'),
    "lang": "fr",
    "dir": "ltr",
    "start_url": "/espace-client",
    "scope": "/",
    "display": "standalone",
    "orientation": "any",
    "background_color": "{{ couleur('foncee') }}",
    "theme_color": "{{ couleur('foncee') }}",
    "icons": [
        { "src": "{{ icone_app('admin-192') }}", "sizes": "192x192", "type": "image/png", "purpose": "any" },
        { "src": "{{ icone_app('admin-512') }}", "sizes": "512x512", "type": "image/png", "purpose": "any" },
        { "src": "{{ icone_app('admin-maskable-512') }}", "sizes": "512x512", "type": "image/png", "purpose": "maskable" }
    ],
    "shortcuts": [
        { "name": "Commandes", "url": "/admin/commandes", "icons": [{ "src": "{{ icone_app('admin-192') }}", "sizes": "192x192", "type": "image/png" }] },
        { "name": "Produits", "url": "/admin/produits", "icons": [{ "src": "{{ icone_app('admin-192') }}", "sizes": "192x192", "type": "image/png" }] }
    ]
}
