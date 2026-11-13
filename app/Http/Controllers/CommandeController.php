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
        $remarques = (string) session('panier_remarques', '');
        $debutMessage = $this->messageWhatsappPanier($panier, (float) $total);

        return view('panier', compact('panier', 'subtotal', 'discount', 'total', 'coupon', 'remarques', 'debutMessage'));
    }

    // Boutons – et + d'une ligne du panier : enregistré tout de suite, totaux renvoyés pour la page
    public function quantite(Request $request, $id)
    {
        $donnees = $request->validate(['quantite' => ['required', 'integer', 'min:1', 'max:10000']]);

        $ligne = $this->cartService->updateQuantity((int) $id, (int) $donnees['quantite']);
        abort_if($ligne === null, 404);

        if (! $request->expectsJson()) {
            return redirect()->route('panier');
        }

        $panier = $this->cartService->getCart();
        $total = (float) $this->cartService->getTotal($panier);

        return response()->json([
            'ligne' => $ligne,
            'sous_total' => (float) $this->cartService->getSubtotal($panier),
            'remise' => (float) $this->cartService->getDiscount(),
            'total' => $total,
            'message' => $this->messageWhatsappPanier($panier, $total),
        ]);
    }

    /**
     * Début du message WhatsApp du panier pour la boutique (les remarques et « Merci ! » s'ajoutent sur la page).
     */
    private function messageWhatsappPanier(array $panier, float $total): string
    {
        $formats = [
            'metre' => 'Au mètre', 'rouleau' => 'Au rouleau', 'pantalon' => 'Pantalon',
            'chemise_courte' => 'Chemise manches courtes', 'chemise_longue' => 'Chemise manches longues',
            'tunique_courte' => 'Tunique manches courtes', 'tunique_longue' => 'Tunique manches longues',
        ];
        $f = fn ($n) => number_format((float) $n, 0, ',', ' ') . ' F';
        $lignes = ['Bonjour ' . boutique('nom') . ',', '', 'Voici mon panier :', ''];
        foreach ($panier as $item) {
            $q = (float) $item['quantite'];
            $unite = $item['type_commande'] === 'metre' ? ' m' : ($item['type_commande'] === 'rouleau' ? ($q > 1 ? ' rouleaux' : ' rouleau') : '');
            $lignes[] = '*' . $item['nom'] . '*';
            $lignes[] = ($formats[$item['type_commande']] ?? $item['type_commande']) . ' : ' . rtrim(rtrim(number_format($q, 2, ',', ''), '0'), ',') . $unite
                . ' × ' . $f($item['prix_unitaire'] ?? ($item['sous_total'] / max(1, $q))) . ' = ' . $f($item['sous_total']);
            if (! empty($item['couleur'])) {
                $lignes[] = 'Couleur souhaitée : ' . $item['couleur'];
            }
            $lignes[] = '';
        }
        $lignes[] = '*Total : ' . $f($total) . '*';

        return implode("\n", $lignes);
    }

    // Remarques du client pour la boutique, écrites dans le panier : gardées jusqu'à la commande
    public function remarques(Request $request)
    {
        $donnees = $request->validate(['remarques' => ['nullable', 'string', 'max:1000']]);
        session(['panier_remarques' => trim((string) ($donnees['remarques'] ?? ''))]);

        return $request->expectsJson()
            ? response()->json(['ok' => true])
            : redirect()->route('panier')->with('success', 'Remarques enregistrées.');
    }

    public function ajouter(Request $request)
    {
        // Formats en vente : au mètre ou au rouleau
        $request->validate([
            'produit_id' => ['required', 'integer', 'exists:produits,id'],
            'type_commande' => ['required', 'in:metre,rouleau'],
            'quantite' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'couleur' => ['nullable', 'string', 'max:100'],
        ], ['type_commande.in' => 'Choisissez « Au mètre » ou « Au rouleau ».']);

        $this->cartService->addItem(
            (int) $request->produit_id,
            $request->type_commande,
            (int) ($request->quantite ?? 1),
            $request->couleur
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
        // Visiteur sans compte : on lui crée son compte à partir de ses coordonnées, puis on continue
        if (! auth()->check()) {
            if (empty($this->cartService->getCart())) {
                return redirect()->route('panier')->with('error', 'Panier vide !');
            }
            $reponse = $this->creerCompteSansInscription($request);
            if ($reponse) {
                return $reponse;
            }
        }

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
                auth()->id(),
                $request->mode_livraison === 'boutique' ? 'boutique' : 'domicile',
                session('panier_remarques')
            );

            return redirect()->route('paiement', $commande->id);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de la confirmation : ' . $e->getMessage());
        }
    }

    /**
     * Compte créé à la commande, sans mot de passe à choisir : nom, téléphone et e-mail facultatif.
     * Un e-mail qui appartient déjà à un compte n'est jamais repris (sinon on entrerait dans le compte
     * d'un autre) : le visiteur est invité à se connecter. Retourne une redirection en cas de refus.
     */
    private function creerCompteSansInscription(Request $request)
    {
        $donnees = $request->validate([
            'prenom' => ['required', 'string', 'max:100'],
            'nom_complet' => ['required', 'string', 'max:100'],
            'indicatif' => ['nullable', 'string', 'max:6'],
            'telephone' => ['required', 'string', 'min:8', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
        ], [
            'prenom.required' => 'Indiquez votre prénom.',
            'nom_complet.required' => 'Indiquez votre nom.',
            'telephone.required' => 'Indiquez votre numéro de téléphone : le livreur vous appellera.',
            'telephone.min' => 'Ce numéro de téléphone semble incomplet.',
            'email.email' => 'Cette adresse e-mail n\'est pas valide.',
        ]);

        $email = strtolower(trim((string) ($donnees['email'] ?? '')));
        if ($email !== '' && \App\Models\User::where('email', $email)->exists()) {
            return back()->withInput()->withErrors([
                'email' => 'Un compte existe déjà avec cet e-mail : connectez-vous pour commander, ou laissez ce champ vide.',
            ]);
        }

        $chiffres = preg_replace('/\D+/', '', $donnees['telephone']);
        $utilisateur = \App\Models\User::create([
            'name' => trim($donnees['prenom'].' '.$donnees['nom_complet']),
            'email' => $email !== '' ? $email : 'client-'.\Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(16)).'@'.\App\Models\User::DOMAINE_SANS_EMAIL,
            'phone' => ($donnees['indicatif'] ?? '+225').$chiffres,
            'password' => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(40)),
        ]);

        // Connexion au nouveau compte, puis son panier (en session) passe dans le compte
        auth()->login($utilisateur, true);
        $request->session()->regenerate();
        $this->cartService->mergeGuestCart();

        return null;
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

        // Commande en gros : son devis, sa réponse et son suivi se trouvent dans l'Espace Pro
        if ($commande->est_pro) {
            return redirect()->route('pro.commande', $commande);
        }

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