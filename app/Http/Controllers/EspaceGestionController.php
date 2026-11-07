<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Espace de gestion : connexion séparée de la boutique, réservée à l'équipe (propriétaire et secrétaires).
 * Accessible uniquement par son adresse (/espace-client), aucun lien depuis la boutique.
 */
class EspaceGestionController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (Auth::user()?->estPersonnel()) {
            return redirect()->route(Auth::user()->premiereSectionAdmin());
        }

        return view('espace.connexion');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        // Mêmes règles que la connexion de la boutique (dont la limite de tentatives)
        $request->authenticate();

        // Propriétaire et secrétaires ; un client est refusé
        if (!$request->user()->estPersonnel()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => 'Ce compte n\'a pas accès à l\'espace de gestion.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->route($request->user()->premiereSectionAdmin());
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('espace.connexion')
            ->with('status', 'Vous êtes déconnecté de l\'espace de gestion.');
    }
}
