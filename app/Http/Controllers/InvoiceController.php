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
        // Le client de la commande, ou l'équipe ayant le droit « commandes ».
        // (L'ancien test « !role === 'admin' » était toujours faux : toute facture était téléchargeable.)
        if (Auth::id() !== $commande->user_id && !Auth::user()->can('manage-orders')) {
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
