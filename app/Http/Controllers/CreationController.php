<?php

namespace App\Http\Controllers;

use App\Models\ProductCreation;
use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CreationController extends Controller
{
    public function index()
    {
        $creations = ProductCreation::with(['user', 'product'])->latest()->paginate(12);
        return view('inspirations', compact('creations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:produits,id',
            'image' => 'required|image|max:2048',
            'caption' => 'nullable|string|max:500',
        ]);

        $path = $request->file('image')->store('creations', 'public');

        ProductCreation::create([
            'user_id' => auth()->id(),
            'product_id' => $request->product_id,
            'image_path' => $path,
            'caption' => $request->caption,
        ]);

        return back()->with('success', 'Votre création a été partagée avec la communauté !');
    }

    public function productCreations($id)
    {
        $produit = Produit::findOrFail($id);
        $creations = ProductCreation::where('product_id', $id)->with('user')->latest()->get();

        return view('produit', compact('produit', 'creations'));
        // Note: Usually I'd update the existing ProduitController@show, but creating a specific route helps for now.
    }
}
