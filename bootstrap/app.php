<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \App\Http\Middleware\CacheImages::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'stripe/webhook',
        ]);

        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'cle-espace' => \App\Http\Middleware\CleEspace::class,
        ]);

        // Un visiteur non connecté qui ouvre l'administration est envoyé vers l'espace de gestion
        // (avec sa clé secrète, la page reste invisible du public), pas vers la connexion de la boutique.
        $middleware->redirectGuestsTo(function (\Illuminate\Http\Request $request) {
            if ($request->is('admin', 'admin/*')) {
                return route('espace.connexion', ['cle' => config('espace.cle')]);
            }

            return route('login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Fichier plus lourd que la limite de PHP (post_max_size) : retour au formulaire avec un message,
        // plutôt que la page d'erreur « Content Too Large ». La session n'est pas encore ouverte à ce stade
        // (le contrôle a lieu avant), d'où le paramètre ?fichier=trop-lourd lu par les layouts.
        $exceptions->render(function (\Illuminate\Http\Exceptions\PostTooLargeException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return null;
            }

            // Retour uniquement vers une page du site (jamais vers un autre domaine)
            $precedente = $request->headers->get('referer');
            $retour = $precedente && parse_url($precedente, PHP_URL_HOST) === $request->getHost()
                ? strtok($precedente, '?')
                : url('/');

            return redirect($retour . '?fichier=trop-lourd');
        });
    })->create();