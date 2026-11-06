{
    "name": @json(boutique('nom').' - '.boutique('slogan')),
    "short_name": @json(boutique('nom')),
    "start_url": "/",
    "display": "standalone",
    "background_color": "{{ couleur('foncee') }}",
    "theme_color": "{{ couleur('seconde') }}",
    "icons": [
        {
            "src": "/favicon.ico",
            "sizes": "64x64 32x32",
            "type": "image/x-icon"
        }
    ]
}
