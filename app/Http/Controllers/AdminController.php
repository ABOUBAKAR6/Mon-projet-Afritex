<?php

namespace App\Http\Controllers;

use App\Models\Produit;
use App\Models\Categorie;
use App\Models\Commande;
use App\Models\Review;
use App\Services\MagicAssistantService;
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
        $today = now()->toDateString();
        $total_produits = Produit::count();
        $total_commandes = Commande::count();
        $total_categories = Categorie::count();

        $ca_total = Commande::where('statut', 'confirmee')->sum('total');
        $ca_jour = Commande::where('statut', 'confirmee')
                            ->whereDate('created_at', $today)
                            ->sum('total');

        $commandes_recentes = Commande::with('user')->latest()->take(5)->get();

        // --- STATS AVANCÉES ---

        // 1. Top 5 des produits les plus vendus (par quantité)
        $top_produits = \DB::table('commande_items')
            ->join('produits', 'commande_items.produit_id', '=', 'produits.id')
            ->select('produits.nom', \DB::raw('SUM(commande_items.quantite) as total_vendu'))
            ->groupBy('produits.id', 'produits.nom')
            ->orderByDesc('total_vendu')
            ->take(5)
            ->get();

        // 2. Alertes de stock (produits < 5 unités)
        $stocks_faibles = Produit::where('stock', '<', 5)->where('disponible', 1)->take(5)->get();

        // 3. Évolution du CA sur les 7 derniers jours (pour graphique)
        $ca_evolution = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $montant = Commande::where('statut', 'confirmee')
                                ->whereDate('created_at', $date)
                                ->sum('total');
            $ca_evolution[$date] = $montant;
        }

        return view('admin.dashboard', compact(
            'total_produits', 'total_commandes',
            'total_categories', 'commandes_recentes',
            'ca_total', 'ca_jour', 'top_produits', 'stocks_faibles', 'ca_evolution'
        ));
    }

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

   public function commandes()
{
    $commandes = Commande::with('user')->latest()->paginate(15);
    
    $today = now()->toDateString();
    $toutesCommandes = Commande::all();

    $stats = [
        'total' => $toutesCommandes->count(),
        'en_attente' => $toutesCommandes->where('statut', 'en_attente')->count(),
        'confirmee' => $toutesCommandes->where('statut', 'confirmee')->count(),
        'livree' => $toutesCommandes->where('statut', 'livree')->count(),
        'annulee' => $toutesCommandes->where('statut', 'annulee')->count(),
        'chiffre_affaires' => $toutesCommandes->where('statut', 'confirmee')->sum('total'),
        'ca_jour' => Commande::where('statut', 'confirmee')
                             ->whereDate('created_at', $today)
                             ->sum('total'),
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
            'image' => 'nullable|image|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $extension = $image->getClientOriginalExtension();
            $imageName = time() . '_' . Str::random(10) . '.' . $extension;
            $image->move(public_path('images/produits'), $imageName);
            $imagePath = 'images/produits/' . $imageName;
        }

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
            'disponible' => 1
        ]);

        return redirect()->route('admin.produits')->with('success', 'Produit ajouté ! ✅');
    }

    public function editProduit(Request $request, $id)
    {
        $produit = Produit::findOrFail($id);

        if ($request->hasFile('image')) {
            if ($produit->image && file_exists(public_path($produit->image))) {
                unlink(public_path($produit->image));
            }
            $image = $request->file('image');
            $extension = $image->getClientOriginalExtension();
            $imageName = time() . '_' . Str::random(10) . '.' . $extension;
            $image->move(public_path('images/produits'), $imageName);
            $produit->image = 'images/produits/' . $imageName;
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
            'image' => $produit->image
        ]);

        return redirect()->route('admin.produits')->with('success', 'Produit modifié ! ✅');
    }

    public function ajouterImage(Request $request, Produit $produit)
    {
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                // Générer un nom court et unique pour éviter les erreurs de chemin trop long sur Git
                $extension = $image->getClientOriginalExtension();
                $imageName = time() . '_' . Str::random(10) . '.' . $extension;
                $image->move(public_path('images/produits'), $imageName);

                \App\Models\ProduitImage::create([
                    'produit_id' => $produit->id,
                    'chemin' => 'images/produits/' . $imageName,
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