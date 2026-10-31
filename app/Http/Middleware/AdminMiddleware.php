<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Entrée de l'administration : propriétaire et secrétaires.
 * Chaque section vérifie ensuite son propre droit (middleware can:… dans routes/web.php).
 */
class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->estPersonnel()) {
            return $next($request);
        }

        return redirect('/')->with('error', 'Accès refusé. Vous n\'avez pas les droits d\'administrateur.');
    }
}
