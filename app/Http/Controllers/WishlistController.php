<?php
namespace App\Http\Controllers;

use App\Models\Wishlist;
use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    public function index()
    {
        $wishlist = Auth::user()->wishlist()->with('produit')->get();
        return view('wishlist', compact('wishlist'));
    }

    public function toggle(Request $request, $id)
    {
        $userId = Auth::id();
        $produitId = $id;

        $item = Wishlist::where('user_id', $userId)
                        ->where('produit_id', $produitId)
                        ->first();

        if ($item) {
            $item->delete();
            $message = 'Produit retiré de vos favoris';
        } else {
            Wishlist::create([
                'user_id' => $userId,
                'produit_id' => $produitId,
            ]);
            $message = 'Produit ajouté à vos favoris';
        }

        return back()->with('success', $message);
    }

    public function remove($id)
    {
        Wishlist::where('user_id', Auth::id())->where('id', $id)->delete();
        return back()->with('success', 'Produit retiré de la liste de souhaits');
    }
}