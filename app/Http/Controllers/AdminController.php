<?php

namespace App\Http\Controllers;

use App\Models\Produit;
use App\Models\Categorie;
use App\Models\Commande;
use App\Models\Review;
use App\Services\MagicAssistantService;
use App\Services\OptimiseurImage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function destroyProduit($id)
    {
        Produit::findOrFail($id)->delete();
        return redirect()->route('admin.produits')->with('success', 'Produit supprimé !');
    }

    public function index()
    {
        // Un secrétaire sans le droit « tableau de bord » arrive sur sa première section autorisée
        if (!auth()->user()->can('view-dashboard')) {
            return redirect()->route(auth()->user()->premiereSectionAdmin());
        }

        // Période suivie : les 30 derniers jours, aujourd'hui compris, comparés aux 30 jours d'avant
        $debut = now()->subDays(29)->startOfDay();
        $debutPrecedent = $debut->copy()->subDays(30);

        // Ventes = commandes confirmées jusqu'à la livraison (ni devis, ni en attente, ni annulées)
        $ventes = Commande::whereIn('statut', Commande::STATUTS_VENDUS)
            ->where('created_at', '>=', $debut)
            ->get(['id', 'total', 'statut', 'est_pro', 'created_at']);
        $ca = (int) $ventes->sum('total');
        $caPrecedent = (int) Commande::whereIn('statut', Commande::STATUTS_VENDUS)
            ->whereBetween('created_at', [$debutPrecedent, $debut])->sum('total');

        // Chiffre d'affaires jour par jour (courbe)
        $caParJour = [];
        for ($i = 29; $i >= 0; $i--) {
            $caParJour[now()->subDays($i)->toDateString()] = 0;
        }
        foreach ($ventes as $vente) {
            $caParJour[$vente->created_at->toDateString()] += (int) $vente->total;
        }

        // Commandes reçues (hors devis et annulées), par tranches de 3 jours : 10 barres
        $tranches = array_fill(0, 10, 0);
        Commande::whereNotIn('statut', [...Commande::STATUTS_DEVIS, 'annulee'])
            ->where('created_at', '>=', $debut)
            ->pluck('created_at')
            ->each(function ($date) use (&$tranches, $debut) {
                $tranches[min(9, intdiv((int) $debut->diffInDays($date), 3))]++;
            });

        // À traiter : une ligne par étape, avec le lien vers la liste filtrée
        $parStatut = Commande::selectRaw('statut, count(*) as total')->groupBy('statut')->pluck('total', 'statut');

        // Encaissement : paiements reçus sur les ventes de la période
        $encaisse = (int) \App\Models\Paiement::whereIn('commande_id', $ventes->pluck('id'))
            ->whereIn('statut', ['paye', 'confirme'])->sum('montant');

        // Moyens de paiement utilisés sur la période (hors échecs)
        $paiements = \App\Models\Paiement::where('created_at', '>=', $debut)
            ->where('statut', '!=', 'echoue')
            ->selectRaw('methode, count(*) as total')->groupBy('methode')->pluck('total', 'methode');

        // Produits les plus vendus : quantités des seules ventes (les devis non acceptés ne comptent pas)
        $topProduits = \DB::table('commande_items')
            ->join('commandes', 'commande_items.commande_id', '=', 'commandes.id')
            ->join('produits', 'commande_items.produit_id', '=', 'produits.id')
            ->whereIn('commandes.statut', Commande::STATUTS_VENDUS)
            ->where('commandes.created_at', '>=', $debut)
            ->select('produits.id', 'produits.nom', \DB::raw('SUM(commande_items.quantite) as total_vendu'), \DB::raw('SUM(commande_items.sous_total) as montant'))
            ->groupBy('produits.id', 'produits.nom')
            ->orderByDesc('montant')
            ->take(5)
            ->get();

        return view('admin.dashboard', [
            'ca' => $ca,
            'caPrecedent' => $caPrecedent,
            'caParJour' => $caParJour,
            'aEncaisser' => (int) Commande::where('statut', 'en_attente')->sum('total'),
            'nbVentes' => $ventes->count(),
            'nbLivrees' => $ventes->where('statut', 'livree')->count(),
            'caDetail' => (int) $ventes->where('est_pro', false)->sum('total'),
            'caGros' => (int) $ventes->where('est_pro', true)->sum('total'),
            'tranches' => $tranches,
            'debut' => $debut,
            'parStatut' => $parStatut,
            'prosEnAttente' => \App\Models\ComptePro::where('statut', 'en_attente')->count(),
            'encaisse' => $encaisse,
            'paiements' => $paiements,
            'topProduits' => $topProduits,
            'stocksFaibles' => Produit::where('disponible', true)->where('stock', '<', self::SEUIL_STOCK)->orderBy('stock')->take(5)->get(['id', 'nom', 'stock', 'image']),
            'commandesRecentes' => Commande::with('user.comptePro')->latest()->take(6)->get(),
        ]);
    }

    // Sous ce stock, un produit en vente est signalé sur le tableau de bord
    private const SEUIL_STOCK = 5;

    public function produits()
    {
        $produits = Produit::with(['categorie', 'images'])->get();
        $categories = Categorie::all();
        return view('admin.produits', compact('produits', 'categories'));
    }

    public function showAjouterProduit()
    {
        $categories = Categorie::all();
        return view('admin.produits.ajouter', compact('categories'));
    }

    public function commandes(Request $request)
    {
        // Filtres de la page (onglets, recherche, date) : appliqués ici, sinon la liste restait complète
        $recherche = trim((string) $request->query('search', ''));
        $commandes = Commande::with(['user', 'paiement', 'items'])
            ->when($request->filled('statut'), fn ($q) => $q->where('statut', $request->query('statut')))
            ->when($request->filled('date'), fn ($q) => $q->whereDate('created_at', $request->query('date')))
            ->when($recherche !== '', function ($q) use ($recherche) {
                $numero = (int) ltrim(preg_replace('/\D+/', '', $recherche), '0');
                $q->where(function ($q) use ($recherche, $numero) {
                    $q->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$recherche}%")->orWhere('email', 'like', "%{$recherche}%"));
                    if ($numero > 0) {
                        $q->orWhere('id', $numero);
                    }
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $parStatut = Commande::selectRaw('statut, count(*) as total')->groupBy('statut')->pluck('total', 'statut');

        $stats = [
            'total' => $parStatut->sum(),
            'devis_demande' => $parStatut['devis_demande'] ?? 0,
            'en_attente' => $parStatut['en_attente'] ?? 0,
            'confirmee' => $parStatut['confirmee'] ?? 0,
            'en_preparation' => $parStatut['en_preparation'] ?? 0,
            'expedie' => $parStatut['expedie'] ?? 0,
            'livree' => $parStatut['livree'] ?? 0,
            'annulee' => $parStatut['annulee'] ?? 0,
            'ca_jour' => Commande::whereIn('statut', Commande::STATUTS_VENDUS)->whereDate('created_at', now()->toDateString())->sum('total'),
        ];

        return view('admin.commandes', compact('commandes', 'stats'));
    }

    public function categories()
    {
        $categories = Categorie::with('produits')->get();
        return view('admin.categories', compact('categories'));
    }

    public function utilisateurs()
    {
        $users = \App\Models\User::latest()->paginate(20);
        return view('admin.users', compact('users'));
    }

    public function updateUserRole(\Illuminate\Http\Request $request, \App\Models\User $user)
    {
        $request->validate([
            'role' => 'required|in:client,admin',
        ]);

        // Les secrétaires se gèrent depuis la page Équipe (droits), et on ne se retire pas ses propres droits
        if ($user->estSecretaire() || $user->is(auth()->user())) {
            return redirect()->back()->with('error', 'Ce compte se gère depuis la page Équipe.');
        }

        $user->update(['role' => $request->role]);

        return redirect()->back()->with('success', 'Rôle de l\'utilisateur mis à jour !');
    }

    public function commandeDetail($id)
    {
        $commande = Commande::with(['user', 'items.produit.categorie', 'paiement'])->findOrFail($id);
        return view('admin.commande-detail', compact('commande'));
    }

   public function updateStatut(Request $request, $id)
{
    $commande = Commande::with('user')->findOrFail($id);

    // Un devis pro en cours avance par son chiffrage puis la réponse du revendeur, jamais à la main
    if (in_array($commande->statut, ['devis_demande', 'devis_envoye'], true)) {
        return redirect()->route('admin.commande.detail', $commande)
            ->with('error', 'Ce devis attend son chiffrage ou la réponse du revendeur : son statut ne se change pas à la main.');
    }

    $request->validate([
        'statut' => ['required', \Illuminate\Validation\Rule::in(['en_attente', 'confirmee', 'en_preparation', 'expedie', 'livree', 'annulee'])],
    ]);

    $ancienStatut = $commande->statut;
    $commande->update([
        'statut' => $request->statut,
        'tracking_number' => $request->tracking_number ?? $commande->tracking_number
    ]);

    // Notification email au client
    try {
        $commande->user->notify(new \App\Notifications\StatutModifieNotification($commande));
    } catch (\Exception $e) {
        // Si email échoue, on continue quand même
    }

    return redirect()->back()->with('success', 'Statut et tracking mis à jour ! Client notifié ✅');
}

    public function ajouterProduit(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'categorie_id' => 'required|exists:categories,id',
            'tarifications' => 'nullable|array',
            'tarifications.*.label' => 'required_with:tarifications|string|max:255',
            'tarifications.*.prix' => 'nullable|numeric',
            'prix_pro' => 'nullable|array',
            'prix_pro.*' => 'nullable|integer|min:0',
            'image' => 'nullable|image|max:' . OptimiseurImage::POIDS_MAX_KO,
        ], ['image.max' => 'La photo dépasse 20 Mo.']);

        // Photo de téléphone acceptée jusqu'à 20 Mo, redressée et compressée pour l'affichage
        $imagePath = $request->hasFile('image')
            ? app(OptimiseurImage::class)->enregistrer($request->file('image'), 'images/produits')
            : null;

        // Handle dynamic pricing
        $tarifs = $request->input('tarifications', []);

        // Fallback for legacy support (if some old inputs are sent)
        if (empty($tarifs)) {
            $legacy = [
                ['label' => 'Au mètre', 'prix' => $request->prix_metre],
                ['label' => 'Au rouleau', 'prix' => $request->prix_rouleau],
                ['label' => 'Pantalon', 'prix' => $request->prix_pantalon],
                ['label' => 'Chemise', 'prix' => $request->prix_chemise],
                ['label' => 'Tunique', 'prix' => $request->prix_tunique],
            ];
            $tarifs = array_filter($legacy, fn($t) => $t['prix'] !== null);
        }

        Produit::create([
            'categorie_id' => $request->categorie_id,
            'nom' => $request->nom,
            'description' => $request->description,
            'tarifications' => $tarifs,
            'prix_metre' => $tarifs[0]['prix'] ?? null, // Keep for legacy display
            'image' => $imagePath,
            'stock' => $request->stock ?? 0,
            'disponible' => 1,
            'prix_pro' => $this->prixPro($request),
        ]);

        return redirect()->route('admin.produits')->with('success', 'Produit ajouté ! ✅');
    }

    public function editProduit(Request $request, $id)
    {
        $produit = Produit::findOrFail($id);
        $request->validate([
            'prix_pro' => 'nullable|array',
            'prix_pro.*' => 'nullable|integer|min:0',
            'image' => 'nullable|image|max:' . OptimiseurImage::POIDS_MAX_KO,
        ], ['image.max' => 'La photo dépasse 20 Mo.']);

        if ($request->hasFile('image')) {
            $nouvelle = app(OptimiseurImage::class)->enregistrer($request->file('image'), 'images/produits');
            if ($produit->image && file_exists(public_path($produit->image))) {
                unlink(public_path($produit->image));
            }
            $produit->image = $nouvelle;
        }

        $tarifs = $request->input('tarifications', []);

        $produit->update([
            'categorie_id' => $request->categorie_id,
            'nom' => $request->nom,
            'description' => $request->description,
            'meta_title' => $request->meta_title,
            'meta_description' => $request->meta_description,
            'tarifications' => $tarifs,
            'prix_metre' => $tarifs[0]['prix'] ?? $produit->prix_metre, // Legacy
            'stock' => $request->stock ?? 0,
            'image' => $produit->image,
            'prix_pro' => $this->prixPro($request),
        ]);

        return redirect()->route('admin.produits')->with('success', 'Produit modifié ! ✅');
    }

    /**
     * Prix pro saisis dans le formulaire produit : { format: prix } pour les seuls formats remplis,
     * null si aucun (le tissu n'est alors pas proposé dans l'Espace Pro).
     */
    private function prixPro(Request $request): ?array
    {
        $prix = collect($request->input('prix_pro', []))
            ->only(array_keys(Produit::FORMATS))
            ->map(fn ($valeur) => (int) $valeur)
            ->filter(fn ($valeur) => $valeur > 0)
            ->all();

        return $prix ?: null;
    }

    public function ajouterImage(Request $request, Produit $produit)
    {
        $request->validate([
            'images' => 'nullable|array',
            'images.*' => 'image|max:' . OptimiseurImage::POIDS_MAX_KO,
        ], ['images.*.max' => 'Une des photos dépasse 20 Mo.', 'images.*.image' => 'Un des fichiers n\'est pas une image.']);

        if ($request->hasFile('images')) {
            $optimiseur = app(OptimiseurImage::class);
            foreach ($request->file('images') as $image) {
                \App\Models\ProduitImage::create([
                    'produit_id' => $produit->id,
                    'chemin' => $optimiseur->enregistrer($image, 'images/produits'),
                ]);
            }
        }

        return redirect()->back()->with('success', 'Photos ajoutées avec succès ! ✅');
    }

    public function supprimerImage(\App\Models\ProduitImage $image)
    {
        if (file_exists(public_path($image->chemin))) {
            unlink(public_path($image->chemin));
        }
        $image->delete();

        return redirect()->back()->with('success', 'Photo supprimée ! ✅');
    }

    public function generateAiDescription(Request $request, Produit $produit)
    {
        $request->validate([
            'keywords' => 'required|string',
        ]);

        $keywords = explode(',', $request->keywords);
        $description = app(MagicAssistantService::class)->generateProductDescription($produit, $keywords);

        return response()->json(['description' => $description]);
    }

    public function analyzeReviews(Produit $produit)
    {
        $reviews = $produit->reviews()->get();
        $allText = $reviews->pluck('commentaire')->implode(' ');

        if (empty($allText)) {
            return response()->json(['error' => 'Aucun avis disponible pour ce produit.']);
        }

        $analysis = app(MagicAssistantService::class)->analyzeReviewSentiment($allText);

        return response()->json($analysis);
    }
}