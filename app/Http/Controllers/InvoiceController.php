<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    public function download(Commande $commande)
    {
        // Verify if the user is allowed to download this invoice
        if (Auth::id() !== $commande->user_id && !Auth::user()->role === 'admin') {
            abort(403);
        }

        // Load order details
        $commande->load(['user', 'items.produit']);

        // Generate PDF from view
        $pdf = Pdf::loadView('emails.invoice', compact('commande'));

        // Return the PDF as a download
        return $pdf->download('facture-commande-'.$commande->id.'.pdf');
    }
}
