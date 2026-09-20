<?php

namespace App\Http\Controllers;

use App\Models\Categorie;
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
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048'
        ]);

        $imagePath = null;

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('images/categories'), $imageName);
            $imagePath = 'images/categories/' . $imageName;
        }

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
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048'
        ]);

        if ($request->hasFile('image')) {
            if ($categorie->image && file_exists(public_path($categorie->image))) {
                unlink(public_path($categorie->image));
            }
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('images/categories'), $imageName);
            $categorie->image = 'images/categories/' . $imageName;
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