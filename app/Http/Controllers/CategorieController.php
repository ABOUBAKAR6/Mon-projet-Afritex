<?php

namespace App\Http\Controllers;

use App\Models\Categorie;
use App\Services\OptimiseurImage;
use Illuminate\Http\Request;

class CategorieController extends Controller
{
    public function index()
    {
        $categories = Categorie::with('produits')->get();
        return view('admin.categories', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:' . OptimiseurImage::POIDS_MAX_KO,
        ], ['image.max' => "L'image dépasse 20 Mo."]);

        // Image acceptée jusqu'à 20 Mo, puis réduite et compressée pour l'affichage
        $imagePath = $request->hasFile('image')
            ? app(OptimiseurImage::class)->enregistrer($request->file('image'), 'images/categories', OptimiseurImage::CATEGORIE)
            : null;

        Categorie::create([
            'nom' => $request->nom,
            'image' => $imagePath
        ]);

        return redirect()->route('admin.categories')
                        ->with('success', 'Catégorie ajoutée avec succès ! ✅');
    }

    public function update(Request $request, $id)
    {
        $categorie = Categorie::findOrFail($id);

        $request->validate([
            'nom' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:' . OptimiseurImage::POIDS_MAX_KO,
        ], ['image.max' => "L'image dépasse 20 Mo."]);

        if ($request->hasFile('image')) {
            $nouvelle = app(OptimiseurImage::class)->enregistrer($request->file('image'), 'images/categories', OptimiseurImage::CATEGORIE);
            if ($categorie->image && file_exists(public_path($categorie->image))) {
                unlink(public_path($categorie->image));
            }
            $categorie->image = $nouvelle;
        }

        $categorie->update(['nom' => $request->nom, 'image' => $categorie->image]);

        return redirect()->route('admin.categories')
                        ->with('success', 'Catégorie modifiée avec succès ! ✅');
    }

    public function destroy($id)
    {
        $categorie = Categorie::findOrFail($id);

        if ($categorie->image && file_exists(public_path($categorie->image))) {
            unlink(public_path($categorie->image));
        }

        $categorie->delete();

        return redirect()->route('admin.categories')
                        ->with('success', 'Catégorie supprimée ! ✅');
    }
}