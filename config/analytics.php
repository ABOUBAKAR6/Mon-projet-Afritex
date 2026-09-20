<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Google Analytics Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration pour Google Analytics.
    | Vous pouvez obtenir votre ID via https://analytics.google.com
    |
    */

    'google_analytics_id' => env('GOOGLE_ANALYTICS_ID', ''),

    /*
    |--------------------------------------------------------------------------
    | Hotjar Configuration (optionnel)
    |--------------------------------------------------------------------------
    |
    | Pour les heatmaps et session recordings
    |
    */

    'hotjar_id' => env('HOTJAR_ID', ''),

];
