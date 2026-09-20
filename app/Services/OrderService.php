<?php

namespace App\Services;

use App\Models\Commande;
use App\Models\CommandeItem;
use App\Models\Coupon;
use App\Events\CommandePassee;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class OrderService
{
    public function createOrder(array $panier, array $coupon, string $adresseLivraison, int $userId): Commande
    {
        return DB::transaction(function () use ($panier, $coupon, $adresseLivraison, $userId) {
            $subtotal = array_sum(array_column($panier, 'sous_total'));
            $discount = $coupon['discount'] ?? 0;
            $total = max(0, $subtotal - $discount);

            $commande = Commande::create([
                'user_id' => $userId,
                'total' => $total,
                'statut' => 'en_attente',
                'adresse_livraison' => $adresseLivraison,
                'coupon_code' => $coupon['code'] ?? null,
                'discount_amount' => $discount,
            ]);

            foreach ($panier as $item) {
                CommandeItem::create([
                    'commande_id' => $commande->id,
                    'produit_id' => $item['produit_id'],
                    'type_commande' => $item['type_commande'],
                    'quantite' => $item['quantite'],
                    'prix_unitaire' => $item['prix_unitaire'],
                    'sous_total' => $item['sous_total']
                ]);
            }

            if (!empty($coupon['id'])) {
                $couponModel = Coupon::find($coupon['id']);
                if ($couponModel) {
                    $couponModel->increment('uses');
                }
            }

            event(new CommandePassee($commande));

            // Vider le panier (session et base de données)
            Session::forget(['panier', 'coupon']);
            if ($userId) {
                \App\Models\CartItem::whereHas('cart', function($q) use ($userId) {
                    $q->where('user_id', $userId);
                })->delete();
            }

            return $commande;
        });
    }
}
