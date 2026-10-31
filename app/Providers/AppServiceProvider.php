<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);

        // Dates en français (jours, mois, « il y a 2 jours ») sans changer la langue des messages
        \Carbon\Carbon::setLocale('fr');

        // Droits de l'administration : le propriétaire a tout ; un secrétaire n'a que les permissions
        // cochées pour lui (Spatie, vérifiées par can('manage-orders'), @can, middleware can:…).
        \Illuminate\Support\Facades\Gate::before(fn ($user) => $user->estProprietaire() ? true : null);
        // Réservé au propriétaire : l'équipe, les rôles, le logo (Gate::before l'accorde au propriétaire seul)
        \Illuminate\Support\Facades\Gate::define('proprietaire', fn ($user) => false);

        // Seul le layout de la boutique utilise les catégories : une requête par page,
        // au lieu d'une par vue et par composant rendu (icônes comprises).
        \View::composer('layouts.app', function ($view) {
            $view->with('globalCategories', \App\Models\Categorie::all());
        });
    }
}