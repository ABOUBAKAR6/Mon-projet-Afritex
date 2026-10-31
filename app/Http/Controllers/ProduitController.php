<?php

namespace App\Http\Controllers;

use App\Models\Produit;
use App\Models\Categorie;
use App\Models\Review;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProduitController extends Controller
{
    public function show($slug)
    {
        $produit = Produit::with(['categorie', 'reviews.user'])->where('slug', $slug)->firstOrFail();

        // Produits similaires : même catégorie, exclu le produit actuel, limité à 4
        $produitsSimilaires = Produit::where('categorie_id', $produit->categorie_id)
            ->where('id', '!=', $produit->id)
            ->with('categorie')
            ->take(4)
            ->get();

        $categories = Categorie::with('produits')->get();
        return view('produit', compact('produit', 'categories', 'produitsSimilaires'));
    }

    public function indexReact()
    {
        $categories = Categorie::all();
        $produits = Produit::with('categorie')->take(8)->get();

        return Inertia::render('Accueil', [
            'categories' => $categories,
            'produits' => $produits,
            'auth' => [
                'user' => auth()->user(),
            ],
        ]);
    }

    public function index()
    {
        $categories = Categorie::all();
        $produits = Produit::with('categorie')->take(8)->get();
        return view('accueil', compact('categories', 'produits'));
    }

    public function catalogue(Request $request)
    {
        $categories = Categorie::all();

        // Extract unique colors for the filter
        $allColors = Produit::pluck('couleurs')
            ->flatMap(fn($colors) => is_array($colors) ? $colors : [])
            ->unique()
            ->values()
            ->toArray();

        // Recherche sur le nom, la description et la catégorie. (Scout plantait : son Builder n'accepte
        // ni with() ni where('>='), et le moteur database cherchait une colonne « categorie » inexistante.)
        if ($request->recherche) {
            $terme = '%' . $request->recherche . '%';
            $query = Produit::query()->where(fn ($q) => $q
                ->where('nom', 'like', $terme)
                ->orWhere('description', 'like', $terme)
                ->orWhereHas('categorie', fn ($c) => $c->where('nom', 'like', $terme)));
        } else {
            $query = Produit::query();
        }

        // Apply other filters on top of the search results
        if ($request->categorie_id) {
            $query->where('categorie_id', $request->categorie_id);
        }

        // Price range filtering
        if ($request->filled('min_price')) {
            $query->where('prix_metre', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('prix_metre', '<=', $request->max_price);
        }

        // Color filtering
        if ($request->has('couleurs') && is_array($request->couleurs)) {
            $query->where(function($q) use ($request) {
                foreach ($request->couleurs as $color) {
                    $q->orWhereJsonContains('couleurs', $color);
                }
            });
        }

        // Sorting
        $sort = $request->get('sort', 'latest');
        switch ($sort) {
            case 'price_asc':
                $query->orderBy('prix_metre', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('prix_metre', 'desc');
                break;
            case 'latest':
            default:
                $query->latest();
                break;
        }

        // Une catégorie s'affiche en rangée défilante : tous ses tissus d'un coup (48 au plus).
        // Sinon, grille paginée par 12.
        $produits = $query->with('categorie')->paginate($request->categorie_id ? 48 : 12)->withQueryString();

        // Sous la rangée d'une catégorie : des tissus des autres familles
        $autresProduits = $request->categorie_id
            ? Produit::with('categorie')->where('categorie_id', '!=', $request->categorie_id)->latest()->take(8)->get()
            : collect();

        return view('catalogue', compact('categories', 'produits', 'allColors', 'autresProduits'));
    }

    public function storeReview(Request $request, $id)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        Review::create([
            'user_id' => auth()->id(),
            'produit_id' => $id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return back()->with('success', 'Votre avis a été enregistré !');
    }
}