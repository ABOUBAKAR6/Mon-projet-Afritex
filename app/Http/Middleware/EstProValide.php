<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pages réservées aux revendeurs validés (commander, devis, tarifs pro).
 * Les autres reviennent à l'accueil de l'Espace Pro, qui affiche connexion, demande ou état de la demande.
 */
class EstProValide
{
    public function handle(Request $request, Closure $next): Response
    {
        // Plus de validation par la boutique : une demande restée en attente s'ouvre à la première visite
        $compte = $request->user()?->comptePro;
        if ($compte?->estEnAttente()) {
            $compte->update(['statut' => 'valide', 'valide_le' => now()]);
        }

        if (! $request->user()?->estProValide()) {
            return redirect()->route('pro.accueil');
        }

        return $next($request);
    }
}
