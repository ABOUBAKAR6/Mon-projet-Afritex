<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Porte dérobée de l'espace de gestion : sans la clé secrète (ESPACE_CLE),
 * l'URL répond 404 comme si la page n'existait pas.
 */
class CleEspace
{
    public function handle(Request $request, Closure $next): Response
    {
        $cle = (string) config('espace.cle');
        $fournie = (string) ($request->query('cle') ?: $request->input('cle'));

        if ($cle === '' || ! hash_equals($cle, $fournie)) {
            // Sans la clé : le visiteur est renvoyé discrètement vers la page publique
            return redirect()->route('accueil');
        }

        return $next($request);
    }
}
