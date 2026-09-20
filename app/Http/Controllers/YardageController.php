<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class YardageController extends Controller
{
    public function index()
    {
        return view('yardage');
    }

    public function calculate(Request $request)
    {
        $type = $request->type;
        $size = $request->size; // 'small', 'medium', 'large'

        // Logique simplifiée de calcul de métrage
        $rules = [
            'robe_longue' => ['small' => 3, 'medium' => 4, 'large' => 5],
            'robe_courte' => ['small' => 2, 'medium' => 2.5, 'large' => 3],
            'chemise' => ['small' => 1.5, 'medium' => 2, 'large' => 2.5],
            'pantalon' => ['small' => 1.2, 'medium' => 1.5, 'large' => 2],
            'tunique' => ['small' => 2, 'medium' => 2.5, 'large' => 3],
        ];

        $amount = $rules[$type][$size] ?? 2;

        return response()->json([
            'amount' => $amount,
            'message' => "Pour un(e) $type de taille $size, nous vous suggérons environ $amount mètres de tissu."
        ]);
    }
}
