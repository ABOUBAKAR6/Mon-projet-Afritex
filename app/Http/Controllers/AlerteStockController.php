<?php

namespace App\Http\Controllers;

use App\Models\AlerteStock;
use App\Models\Produit;
use App\Notifications\StockFaibleNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class AlerteStockController extends Controller
{
    // Ajouter une alerte de stock
    public function creer(Request $request)
    {
        $validated = $request->validate([
            'produit_id' => 'required|exists:produits,id',
            'seuil_minimum' => 'required|integer|min:1|max:1000'
        ]);

        $alerte = AlerteStock::firstOrCreate(
            [
                'user_id' => auth()->id(),
                'produit_id' => $validated['produit_id']
            ],
            [
                'seuil_minimum' => $validated['seuil_minimum'],
                'est_active' => true
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Alerte créée',
            'alerte' => $alerte
        ]);
    }

    // Lister les alertes de l'utilisateur
    public function index()
    {
        $alertes = auth()->user()
            ->alertesStock()
            ->with('produit')
            ->latest()
            ->get();

        return view('alertes.index', compact('alertes'));
    }

    // Supprimer une alerte
    public function supprimer(AlerteStock $alerte)
    {
        abort_unless($alerte->user_id === auth()->id(), 403);
        
        $alerte->delete();

        return response()->json([
            'success' => true,
            'message' => 'Alerte supprimée'
        ]);
    }

    // Activer/Désactiver une alerte
    public function basculer(AlerteStock $alerte)
    {
        abort_unless($alerte->user_id === auth()->id(), 403);
        
        $alerte->est_active = !$alerte->est_active;
        $alerte->save();

        return response()->json([
            'success' => true,
            'message' => $alerte->est_active ? 'Alerte activée' : 'Alerte désactivée',
            'est_active' => $alerte->est_active
        ]);
    }

    // Vérifier le stock et envoyer les notifications
    public static function verifierStocksEtAlerter()
    {
        $alertes = AlerteStock::where('est_active', true)
            ->where(function ($query) {
                $query->whereNull('date_prochaine_verification')
                      ->orWhere('date_prochaine_verification', '<=', now());
            })
            ->with(['user', 'produit'])
            ->get();

        foreach ($alertes as $alerte) {
            $produit = $alerte->produit;

            // Vérifier si le stock est en dessous du seuil
            if ($produit->stock_disponible <= $alerte->seuil_minimum) {
                // Envoyer notification
                $alerte->user->notify(
                    new StockFaibleNotification($produit, $alerte->seuil_minimum)
                );
            }

            // Marquer comme vérifiée (prochaine vérification dans 24h)
            $alerte->update([
                'date_prochaine_verification' => now()->addHours(24)
            ]);
        }
    }
}
