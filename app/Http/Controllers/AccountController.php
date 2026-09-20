<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function delete(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        // Optionnel: révoquer tokens, supprimer données liées
        Auth::logout();

        try {
            $user->delete();
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erreur lors de la suppression'], 500);
        }

        return response()->json(['success' => true, 'message' => 'Compte supprimé']);
    }
}
