<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Produit;
use App\Services\GestionStock;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Section Stock de l'administration : rouleaux et mètres restants par tissu,
 * arrivages, retraits, inventaires et historique.
 */
class StockController extends Controller
{
    public const FILTRES = [
        'tous' => 'Tous',
        'surveiller' => 'À surveiller',
        'epuises' => 'Épuisés',
        'longueur' => 'Longueur à indiquer',
    ];

    public function __construct(private GestionStock $stock)
    {
    }

    public function index(Request $request): View
    {
        $produits = Produit::with('categorie')->orderBy('nom')->get();

        $compteurs = [
            'tous' => $produits->count(),
            'surveiller' => $produits->where(fn ($p) => $p->etatStock() === 'faible')->count(),
            'epuises' => $produits->where(fn ($p) => $p->etatStock() === 'epuise')->count(),
            'longueur' => $produits->whereNull('metres_par_rouleau')->count(),
        ];

        $filtre = array_key_exists((string) $request->query('filtre'), self::FILTRES) ? (string) $request->query('filtre') : 'tous';
        $liste = match ($filtre) {
            'surveiller' => $produits->filter(fn ($p) => $p->etatStock() === 'faible'),
            'epuises' => $produits->filter(fn ($p) => $p->etatStock() === 'epuise'),
            'longueur' => $produits->whereNull('metres_par_rouleau'),
            default => $produits,
        };

        return view('admin.stock.index', [
            'produits' => $liste->values(),
            'compteurs' => $compteurs,
            'filtre' => $filtre,
            'totalRouleaux' => $produits->sum('stock'),
        ]);
    }

    public function show(Produit $produit): View
    {
        return view('admin.stock.produit', [
            'produit' => $produit->load('categorie'),
            'mouvements' => $produit->mouvementsStock()->with(['user', 'commande'])->latest('id')->paginate(25),
        ]);
    }

    public function mouvement(Request $request, Produit $produit): RedirectResponse
    {
        $donnees = $request->validate([
            'type' => ['required', Rule::in(['arrivage', 'retrait', 'inventaire'])],
            'rouleaux' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'metres' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
            'note' => ['nullable', 'string', 'max:255'],
            'remettre_en_vente' => ['nullable', 'boolean'],
        ], [], ['rouleaux' => 'rouleaux', 'metres' => 'mètres']);

        $rouleaux = (int) ($donnees['rouleaux'] ?? 0);
        $metres = round((float) ($donnees['metres'] ?? 0), 2);

        if ($donnees['type'] !== 'inventaire' && $rouleaux === 0 && $metres <= 0) {
            return back()->withInput()->withErrors(['rouleaux' => 'Indiquez des rouleaux ou des mètres.']);
        }

        $note = $donnees['note'] ?? null;
        $message = match ($donnees['type']) {
            'arrivage' => $this->stock->arrivage($produit, $rouleaux, $metres, $note, $request->user(), (bool) ($donnees['remettre_en_vente'] ?? false)) ? 'Arrivage ajouté au stock.' : '',
            'retrait' => $this->stock->retrait($produit, $rouleaux, $metres, $note, $request->user()) ? 'Retrait enregistré.' : '',
            'inventaire' => $this->stock->inventaire($produit, $rouleaux, $metres, $note, $request->user()) ? 'Inventaire enregistré : le stock est à jour.' : '',
        };

        return redirect()->route('admin.stock.produit', $produit)
            ->with('success', $message.' Il reste '.$produit->refresh()->libelleStock().'.');
    }

    public function longueur(Request $request, Produit $produit): RedirectResponse
    {
        $donnees = $request->validate([
            'metres_par_rouleau' => ['nullable', 'numeric', 'min:0.5', 'max:1000'],
        ], [], ['metres_par_rouleau' => 'longueur d\'un rouleau']);

        $this->stock->definirLongueur($produit, isset($donnees['metres_par_rouleau']) ? round((float) $donnees['metres_par_rouleau'], 2) : null, $request->user());

        return redirect()->route('admin.stock.produit', $produit)->with('success', $produit->metres_par_rouleau
            ? 'Longueur enregistrée : un rouleau fait '.Produit::metres($produit->metres_par_rouleau).'.'
            : 'Longueur effacée.');
    }
}
