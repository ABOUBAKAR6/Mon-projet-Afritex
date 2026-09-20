<?php

namespace App\Http\Controllers;
use App\Events\CommandePassee;

use App\Models\Commande;
use App\Models\CommandeItem;
use App\Models\Coupon;
use App\Models\Produit;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Http\Request;

class CommandeController extends Controller
{
    protected $cartService;
    protected $orderService;

    public function __construct(CartService $cartService, OrderService $orderService)
    {
        $this->cartService = $cartService;
        $this->orderService = $orderService;
    }

    public function panier()
    {
        $panier = $this->cartService->getCart();
        $subtotal = $this->cartService->getSubtotal($panier);
        $discount = $this->cartService->getDiscount();
        $total = $this->cartService->getTotal($panier);
        $coupon = session('coupon', []);

        return view('panier', compact('panier', 'subtotal', 'discount', 'total', 'coupon'));
    }

    public function ajouter(Request $request)
    {
        $this->cartService->addItem(
            (int) $request->produit_id,
            $request->type_commande,
            (int) ($request->quantite ?? 1)
        );

        return redirect()->route('panier')->with('success', 'Produit ajouté au panier !');
    }

    public function supprimer($index)
    {
        $this->cartService->removeItem((int) $index);

        return redirect()->route('panier')->with('success', 'Produit supprimé !');
    }

    public function appliquerCoupon(Request $request)
    {
        $request->validate([
            'coupon_code' => 'required|string|max:255',
        ]);

        try {
            $coupon = $this->cartService->applyCoupon($request->coupon_code);
            return redirect()->route('panier')->with('success', 'Code promo appliqué : -' . number_format($coupon['discount'], 0, ',', ' ') . ' F');
        } catch (\Exception $e) {
            return redirect()->route('panier')->with('error', $e->getMessage());
        }
    }

    public function index()
    {
        $panier = $this->cartService->getCart();
        $subtotal = $this->cartService->getSubtotal($panier);
        $coupon = session('coupon', []);
        $discount = $coupon['discount'] ?? 0;
        $total = max(0, $subtotal - $discount);

        return view('commande', compact('panier', 'subtotal', 'discount', 'total', 'coupon'));
    }

    public function confirmer(Request $request)
    {
        $panier = $this->cartService->getCart();
        if (empty($panier)) {
            return redirect()->route('panier')->with('error', 'Panier vide !');
        }

        $coupon = session('coupon', []);

        try {
            $commande = $this->orderService->createOrder(
                $panier,
                $coupon,
                $request->adresse_livraison,
                auth()->id()
            );

            return redirect()->route('paiement', $commande->id);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de la confirmation : ' . $e->getMessage());
        }
    }

    public function mesCommandes()
    {
        $commandes = auth()->user()
            ->commandes()
            ->with(['items.produit'])
            ->latest()
            ->paginate(10);

        return view('commandes.index', compact('commandes'));
    }

    public function show(Commande $commande)
    {
        abort_unless($commande->user_id === auth()->id(), 403);

        $commande->load(['items.produit', 'paiement']);

        return view('commandes.show', compact('commande'));
    }

    public function track($tracking_number)
    {
        $commande = Commande::where('tracking_number', $tracking_number)->firstOrFail();

        return view('track', compact('commande'));
    }

    // API: Synchroniser panier depuis cookies
    public function syncPanier(Request $request)
    {
        $validated = $request->validate([
            'panier' => 'required|array',
            'panier.*.id' => 'required|integer',
            'panier.*.quantite' => 'required|integer|min:1',
        ]);

        foreach ($validated['panier'] as $item) {
            $this->cartService->addItem(
                $item['id'],
                $item['type_commande'] ?? 'metre',
                $item['quantite'] ?? 1
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Panier synchronisé',
            'panier' => $this->cartService->getCart()
        ]);
    }

    // API: Obtenir panier courant
    public function getPanierCourant()
    {
        $panier = $this->cartService->getCart();

        return response()->json([
            'success' => true,
            'panier' => $panier,
            'count' => count($panier)
        ]);
    }
}