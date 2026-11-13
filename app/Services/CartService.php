<?php

namespace App\Services;

use App\Models\Produit;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

class CartService
{
    public function getCart(): array
    {
        $userId = Auth::id();
        $items = [];

        if ($userId) {
            $cart = Cart::firstOrCreate(['user_id' => $userId]);
            $items = $cart->items()->with('produit')->get()->map(function ($item) {
                return [
                    'id' => $item->id,
                    'produit_id' => $item->produit_id,
                    'nom' => $item->produit->nom,
                    'type_commande' => $item->type_commande,
                    'couleur' => $item->couleur,
                    'quantite' => $item->quantite,
                    'prix_unitaire' => $item->prix_unitaire,
                    'sous_total' => $item->sous_total,
                    'image' => $item->produit->image ?? $item->produit->images()->where('est_principale', true)->value('chemin') ?? $item->produit->images()->value('chemin'),
                ];
            })->toArray();
        } else {
            $panier = Session::get('panier', []);
            foreach ($panier as $k => $item) {
                $produit = Produit::find($item['produit_id']);
                $image = null;
                if ($produit) {
                    $image = $produit->image ?? $produit->images()->where('est_principale', true)->value('chemin') ?? $produit->images()->value('chemin');
                }
                $panier[$k]['image'] = $image;
                $items[] = $panier[$k];
            }
        }

        return $items;
    }

    public function addItem(int $produitId, string $typeCommande, int $quantite, ?string $couleur = null): void
    {
        $produit = Produit::findOrFail($produitId);
        // Couleur souhaitée par le client (page produit) : vide = pas de préférence
        $couleur = trim((string) $couleur) !== '' ? mb_substr(trim($couleur), 0, 100) : null;

        $prixUnitaire = match($typeCommande) {
            'metre' => $produit->prix_metre,
            'rouleau' => $produit->prix_rouleau,
            default => 0, // confections retirées de la vente
        };
        if ((int) $prixUnitaire <= 0) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'type_commande' => 'Ce format n\'est pas proposé pour ce tissu.',
            ]);
        }

        $sousTotal = $prixUnitaire * $quantite;

        if (Auth::check()) {
            $userId = Auth::id();
            $cart = Cart::firstOrCreate(['user_id' => $userId]);

            $item = CartItem::where('cart_id', $cart->id)
                ->where('produit_id', $produitId)
                ->where('type_commande', $typeCommande)
                ->when($couleur === null, fn ($q) => $q->whereNull('couleur'), fn ($q) => $q->where('couleur', $couleur))
                ->first();

            if ($item) {
                $item->increment('quantite', $quantite);
                $item->update(['sous_total' => $item->prix_unitaire * $item->quantite]);
            } else {
                CartItem::create([
                    'cart_id' => $cart->id,
                    'produit_id' => $produitId,
                    'type_commande' => $typeCommande,
                    'couleur' => $couleur,
                    'quantite' => $quantite,
                    'prix_unitaire' => $prixUnitaire,
                    'sous_total' => $sousTotal,
                ]);
            }
        } else {
            $panier = Session::get('panier', []);

            // Vérifier si le produit existe déjà dans le panier session
            $existingKey = null;
            foreach ($panier as $key => $item) {
                if ($item['produit_id'] === $produitId && $item['type_commande'] === $typeCommande && ($item['couleur'] ?? null) === $couleur) {
                    $existingKey = $key;
                    break;
                }
            }

            if ($existingKey !== null) {
                $panier[$existingKey]['quantite'] += $quantite;
                $panier[$existingKey]['sous_total'] = $panier[$existingKey]['prix_unitaire'] * $panier[$existingKey]['quantite'];
            } else {
                $panier[] = [
                    'produit_id' => $produit->id,
                    'nom' => $produit->nom,
                    'type_commande' => $typeCommande,
                    'couleur' => $couleur,
                    'quantite' => $quantite,
                    'prix_unitaire' => $prixUnitaire,
                    'sous_total' => $sousTotal
                ];
            }
            Session::put('panier', $panier);
        }

        Session::forget('coupon');
    }

    /**
     * Nouvelle quantité d'une ligne du panier (boutons – et + du panier).
     * Client connecté : $itemId = id de la ligne, vérifiée dans SON panier ; invité : index en session.
     * Retourne le nouveau sous-total de la ligne, ou null si la ligne n'existe pas.
     */
    public function updateQuantity(int $itemId, int $quantite): ?float
    {
        $quantite = max(1, min(10000, $quantite));
        $sousTotal = null;

        if (Auth::check()) {
            $cart = Cart::where('user_id', Auth::id())->first();
            $item = $cart ? CartItem::where('id', $itemId)->where('cart_id', $cart->id)->first() : null;
            if ($item) {
                $item->update(['quantite' => $quantite, 'sous_total' => $item->prix_unitaire * $quantite]);
                $sousTotal = (float) $item->sous_total;
            }
        } else {
            $panier = Session::get('panier', []);
            if (isset($panier[$itemId])) {
                $panier[$itemId]['quantite'] = $quantite;
                $panier[$itemId]['sous_total'] = $panier[$itemId]['prix_unitaire'] * $quantite;
                $sousTotal = (float) $panier[$itemId]['sous_total'];
                Session::put('panier', $panier);
            }
        }

        // Comme pour l'ajout et le retrait : un code promo se réapplique sur le nouveau panier
        if ($sousTotal !== null) {
            Session::forget('coupon');
        }

        return $sousTotal;
    }

    public function removeItem(int $itemId): void
    {
        if (Auth::check()) {
            $userId = Auth::id();
            $cart = Cart::where('user_id', $userId)->first();
            if ($cart) {
                // On s'assure que l'item appartient bien au panier de l'utilisateur
                CartItem::where('id', $itemId)
                    ->where('cart_id', $cart->id)
                    ->delete();
            }
        } else {
            $panier = Session::get('panier', []);
            // Pour les invités, $itemId est l'index dans le tableau session
            if (isset($panier[$itemId])) {
                unset($panier[$itemId]);
                // On réindexe le tableau pour éviter les trous et maintenir la cohérence
                Session::put('panier', array_values($panier));
            }
        }

        Session::forget('coupon');
    }

    public function applyCoupon(string $code): array
    {
        $couponCode = strtoupper(trim($code));
        $coupon = Coupon::where('code', $couponCode)
            ->where('active', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>=', now());
            })
            ->first();

        if (!$coupon) {
            throw new \Exception('Code promo invalide ou expiré.');
        }

        $panier = $this->getCart();
        if (empty($panier)) {
            throw new \Exception('Votre panier est vide.');
        }

        $subtotal = $this->getSubtotal($panier);

        if ($coupon->minimum_order && $subtotal < $coupon->minimum_order) {
            throw new \Exception('Le montant minimum pour ce coupon est de ' . number_format($coupon->minimum_order, 0, ',', ' ') . ' F.');
        }

        if ($coupon->max_uses && $coupon->uses >= $coupon->max_uses) {
            throw new \Exception('Ce coupon a déjà été utilisé le nombre maximum de fois.');
        }

        $discount = $coupon->type === 'percent'
            ? floor($subtotal * ($coupon->value / 100))
            : min($coupon->value, $subtotal);

        if ($coupon->max_discount) {
            $discount = min($discount, $coupon->max_discount);
        }

        $couponData = [
            'id' => $coupon->id,
            'code' => $coupon->code,
            'type' => $coupon->type,
            'value' => $coupon->value,
            'discount' => $discount,
        ];

        Session::put('coupon', $couponData);

        return $couponData;
    }

    public function getSubtotal(array $panier): float
    {
        return (float) array_sum(array_column($panier, 'sous_total'));
    }

    public function getDiscount(): float
    {
        $coupon = Session::get('coupon', []);
        return (float) ($coupon['discount'] ?? 0);
    }

    public function getTotal(array $panier): float
    {
        $subtotal = $this->getSubtotal($panier);
        $discount = $this->getDiscount();
        return max(0, $subtotal - $discount);
    }

    public function mergeGuestCart(): void
    {
        $panier = Session::get('panier', []);
        if (empty($panier)) return;

        $userId = Auth::id();
        $cart = Cart::firstOrCreate(['user_id' => $userId]);

        foreach ($panier as $item) {
            $this->addItem($item['produit_id'], $item['type_commande'], (int) $item['quantite'], $item['couleur'] ?? null);
        }

        Session::forget('panier');
    }
}
