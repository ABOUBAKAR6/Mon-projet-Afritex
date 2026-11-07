<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Commande;
use App\Models\ComptePro;
use App\Notifications\EspaceProNotification;
use App\Services\EspaceProService;
use Illuminate\Http\Request;

/**
 * Espace Pro, côté équipe : demandes de comptes revendeurs (valider, refuser, suspendre)
 * et chiffrage des devis demandés par les revendeurs.
 */
class ProController extends Controller
{
    public function __construct(private EspaceProService $espacePro) {}

    public function index(Request $request)
    {
        $filtre = in_array($request->query('statut'), ['en_attente', 'valide', 'refuse'], true)
            ? $request->query('statut')
            : null;

        $comptes = ComptePro::with('user')
            ->select('comptes_pro.*')
            ->addSelect(['commandes_pro' => Commande::selectRaw('count(*)')
                ->whereColumn('commandes.user_id', 'comptes_pro.user_id')
                ->where('est_pro', true)])
            ->when($filtre, fn ($q) => $q->where('statut', $filtre))
            // Les demandes à examiner d'abord, puis les plus récentes
            ->orderByRaw("CASE statut WHEN 'en_attente' THEN 0 WHEN 'valide' THEN 1 ELSE 2 END")
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $compteurs = ComptePro::selectRaw('statut, count(*) as total')->groupBy('statut')->pluck('total', 'statut');

        return view('admin.pros', compact('comptes', 'compteurs', 'filtre'));
    }

    public function valider(ComptePro $compte)
    {
        $compte->update(['statut' => 'valide', 'motif_refus' => null, 'valide_le' => now()]);

        $this->espacePro->prevenir($compte->user, new EspaceProNotification(
            'Votre compte pro est ouvert',
            [
                'Bonne nouvelle : '.$compte->boutique.' a maintenant accès à l\'Espace Pro.',
                'Vous y voyez nos tarifs de gros, vous commandez en quelques clics et vous suivez vos devis et livraisons.',
            ],
            route('pro.accueil'),
            'Ouvrir mon Espace Pro',
            'pro_valide',
        ));

        return back()->with('success', $compte->boutique.' peut maintenant commander en gros. Le revendeur est prévenu.');
    }

    public function refuser(Request $request, ComptePro $compte)
    {
        $donnees = $request->validate(['motif_refus' => ['nullable', 'string', 'max:255']]);
        $etaitValide = $compte->estValide();

        $compte->update(['statut' => 'refuse', 'motif_refus' => $donnees['motif_refus'] ?? null, 'valide_le' => null]);

        $this->espacePro->prevenir($compte->user, new EspaceProNotification(
            $etaitValide ? 'Votre accès pro est suspendu' : 'Votre demande de compte pro',
            array_values(array_filter([
                $etaitValide
                    ? 'L\'accès de '.$compte->boutique.' à l\'Espace Pro est suspendu.'
                    : 'Nous ne pouvons pas ouvrir de compte pro pour '.$compte->boutique.' pour le moment.',
                $compte->motif_refus ? 'Motif : '.$compte->motif_refus : null,
                'Vous pouvez toujours acheter sur la boutique, ou nous écrire pour en parler.',
            ])),
            route('pro.accueil'),
            'Voir ma demande',
            'pro_refuse',
        ));

        return back()->with('success', $etaitValide ? 'Accès pro suspendu.' : 'Demande refusée. Le demandeur est prévenu.');
    }

    public function envoyerDevis(Request $request, Commande $commande)
    {
        abort_unless($commande->est_pro, 404);
        if (! in_array($commande->statut, ['devis_demande', 'devis_envoye'], true)) {
            return back()->with('error', 'Ce devis a déjà reçu une réponse : il ne se modifie plus.');
        }

        $donnees = $request->validate([
            'lignes' => ['required', 'array'],
            'lignes.*.quantite' => ['required', 'integer', 'min:0', 'max:9999'],
            'lignes.*.prix_unitaire' => ['required', 'integer', 'min:0'],
            'frais_livraison' => ['nullable', 'integer', 'min:0'],
            'livraison_prevue_le' => ['nullable', 'date'],
            'note_boutique' => ['nullable', 'string', 'max:1000'],
        ], [], [
            'lignes.*.quantite' => 'quantité',
            'lignes.*.prix_unitaire' => 'prix unitaire',
            'livraison_prevue_le' => 'date de livraison',
        ]);

        $this->espacePro->envoyerDevis(
            $commande->load('items', 'user'),
            $donnees['lignes'],
            (int) ($donnees['frais_livraison'] ?? 0),
            $donnees['livraison_prevue_le'] ?? null,
            $donnees['note_boutique'] ?? null,
        );

        // « Envoyer + WhatsApp » : la page propose ensuite d'ouvrir WhatsApp avec le devis enregistré
        return back()
            ->with('success', 'Devis envoyé. Le revendeur est prévenu et peut le valider depuis son Espace Pro.')
            ->with('devis_whatsapp', $request->input('partager') === 'whatsapp');
    }
}
