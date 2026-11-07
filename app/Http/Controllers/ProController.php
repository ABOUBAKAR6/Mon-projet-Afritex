<?php

namespace App\Http\Controllers;

use App\Mail\BienvenueMail;
use App\Models\Categorie;
use App\Models\Commande;
use App\Models\ComptePro;
use App\Models\Produit;
use App\Models\User;
use App\Notifications\EspaceProNotification;
use App\Services\EspaceProService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

use function Illuminate\Support\defer;

/**
 * Espace Pro, côté revendeur : présentation et demande de compte (public),
 * puis, une fois le compte validé : tableau de bord, commande en gros par devis,
 * suivi des commandes, compte et catalogue des tarifs en PDF.
 */
class ProController extends Controller
{
    public function __construct(private EspaceProService $espacePro) {}

    // ===== Public =====

    public function accueil(Request $request)
    {
        $user = $request->user();

        if ($user?->estProValide()) {
            $commandes = $user->commandes()->where('est_pro', true)->with('items')->latest()->take(5)->get();

            return view('pro.tableau', [
                'compte' => $user->comptePro,
                'chiffres' => $this->espacePro->chiffres($user),
                'commandes' => $commandes,
            ]);
        }

        // Après la connexion depuis cette page, retour ici plutôt qu'au panier
        if (! $user) {
            redirect()->setIntendedUrl(route('pro.accueil'));
        }

        return view('pro.accueil', ['compte' => $user?->comptePro]);
    }

    public function inscription(Request $request)
    {
        if ($request->user()?->comptePro) {
            return redirect()->route('pro.accueil');
        }

        return view('pro.inscription', [
            'categories' => Categorie::orderBy('nom')->get(['id', 'nom']),
        ]);
    }

    public function inscrire(Request $request)
    {
        $user = $request->user();
        if ($user?->comptePro) {
            return redirect()->route('pro.accueil');
        }

        $regles = $this->reglesCompte() + [
            'familles' => ['nullable', 'array'],
            'familles.*' => ['integer', 'exists:categories,id'],
        ];
        if (! $user) {
            $regles += [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ];
        }
        $donnees = $request->validate($regles, [], $this->nomsChamps());

        $compte = DB::transaction(function () use (&$user, $donnees) {
            if (! $user) {
                $user = User::create([
                    'name' => $donnees['name'],
                    'email' => $donnees['email'],
                    'phone' => $donnees['whatsapp'],
                    'password' => Hash::make($donnees['password']),
                    'role' => 'client',
                ]);
            }

            return ComptePro::create([
                'user_id' => $user->id,
                'statut' => 'en_attente',
            ] + collect($donnees)->only([
                'boutique', 'activite', 'ville', 'commune', 'whatsapp', 'volume_mensuel', 'familles', 'rccm', 'presentation',
            ])->all());
        });

        if (! Auth::check()) {
            event(new Registered($user));
            defer(function () use ($user) {
                try {
                    Mail::to($user)->send(new BienvenueMail($user));
                } catch (\Throwable $e) {
                    report($e);
                }
            });
            Auth::login($user);
            $request->session()->regenerate();
        }

        $this->espacePro->prevenirEquipe('manage-pros', new EspaceProNotification(
            'Nouvelle demande de compte pro : '.$compte->boutique,
            [
                $compte->boutique.' ('.$compte->libelleActivite().', '.$compte->ville.') demande à commander en gros.',
                'WhatsApp : '.$compte->whatsapp.($compte->libelleVolume() ? '. Achat mensuel estimé : '.$compte->libelleVolume() : '').'.',
            ],
            route('admin.pros'),
            'Examiner la demande',
            'demande_pro',
        ));

        return redirect()->route('pro.accueil')
            ->with('success', 'Demande envoyée. La boutique l\'examine et vous prévient dès que votre compte pro est ouvert.');
    }

    // ===== Revendeur validé =====

    public function commander(Request $request)
    {
        $categories = Categorie::orderBy('nom')
            ->with(['produits' => fn ($q) => $q->where('disponible', true)->whereNotNull('prix_pro')->orderBy('nom')])
            ->get()
            ->map(function ($categorie) {
                $categorie->setRelation('produits', $categorie->produits->filter(fn (Produit $p) => $p->formatsPro())->values());

                return $categorie;
            })
            ->filter(fn ($categorie) => $categorie->produits->isNotEmpty())
            ->values();

        // « Recommander » : quantités reprises d'une commande précédente de ce revendeur
        $quantites = [];
        $source = null;
        if ($request->filled('depuis')) {
            $source = Commande::where('user_id', $request->user()->id)->where('est_pro', true)
                ->with('items')->find($request->integer('depuis'));
            foreach ($source?->items ?? [] as $item) {
                // Entier : la colonne décimale renvoie « 25.00 », que la règle « integer » refuserait à l'envoi
                $quantites[$item->produit_id][$item->type_commande] = (int) $item->quantite;
            }
        }

        return view('pro.commander', [
            'compte' => $request->user()->comptePro,
            'categories' => $categories,
            'quantites' => old('quantites', $quantites),
            'source' => $source,
            'derniereAdresse' => $request->user()->commandes()->where('mode_livraison', 'domicile')->latest()->value('adresse_livraison'),
        ]);
    }

    public function envoyerCommande(Request $request)
    {
        $infos = $request->validate([
            'quantites' => ['required', 'array'],
            'quantites.*' => ['array'],
            'quantites.*.*' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'mode_livraison' => ['required', Rule::in(['domicile', 'boutique'])],
            'adresse_livraison' => ['required_if:mode_livraison,domicile', 'nullable', 'string', 'max:500'],
            'livraison_souhaitee_le' => ['nullable', 'date', 'after_or_equal:today'],
            'note_client' => ['nullable', 'string', 'max:1000'],
        ], [
            'adresse_livraison.required_if' => 'Indiquez l\'adresse de livraison, ou choisissez le retrait en boutique.',
            'livraison_souhaitee_le.after_or_equal' => 'La date souhaitée ne peut pas être passée.',
        ]);

        $commande = $this->espacePro->demanderDevis($request->user(), $infos['quantites'], $infos);

        return redirect()->route('pro.commande', $commande)
            ->with('success', 'Demande de devis envoyée. La boutique vérifie le stock et vous envoie le devis.')
            ->with('demande_whatsapp', $request->input('partager') === 'whatsapp');
    }

    public function commandes(Request $request)
    {
        $commandes = $request->user()->commandes()->where('est_pro', true)
            ->with('items')->latest()->paginate(15);

        return view('pro.commandes', ['compte' => $request->user()->comptePro, 'commandes' => $commandes]);
    }

    public function commande(Request $request, Commande $commande)
    {
        $this->verifierProprietaire($request, $commande);
        $commande->load(['items.produit.categorie', 'paiement']);

        return view('pro.commande', ['compte' => $request->user()->comptePro, 'commande' => $commande]);
    }

    public function accepter(Request $request, Commande $commande)
    {
        $this->verifierProprietaire($request, $commande);
        if ($commande->statut !== 'devis_envoye') {
            return back()->with('error', 'Ce devis n\'est plus à valider.');
        }

        $this->espacePro->accepterDevis($commande);

        return redirect()->route('paiement', $commande)->with('success', 'Devis accepté. Choisissez votre moyen de paiement.');
    }

    public function refuser(Request $request, Commande $commande)
    {
        $this->verifierProprietaire($request, $commande);
        if ($commande->statut !== 'devis_envoye') {
            return back()->with('error', 'Ce devis n\'est plus à valider.');
        }

        $this->espacePro->refuserDevis($commande);

        return redirect()->route('pro.commande', $commande)->with('success', 'Devis refusé. La boutique en est informée.');
    }

    /** Le devis ne convient pas : le revendeur dit ce qu'il veut changer, la boutique le revoit. */
    public function demanderModification(Request $request, Commande $commande)
    {
        $this->verifierProprietaire($request, $commande);
        if ($commande->statut !== 'devis_envoye') {
            return back()->with('error', 'Ce devis n\'est plus à valider.');
        }

        $donnees = $request->validate(
            ['message' => ['required', 'string', 'min:5', 'max:1000']],
            ['message.required' => 'Dites à la boutique ce que vous souhaitez changer.'],
            ['message' => 'message']
        );

        $this->espacePro->demanderModification($commande, $donnees['message']);

        return redirect()->route('pro.commande', $commande)
            ->with('success', 'Demande envoyée. La boutique revoit le devis et vous renvoie une nouvelle version.');
    }

    public function compte(Request $request)
    {
        return view('pro.compte', [
            'compte' => $request->user()->comptePro,
            'categories' => Categorie::orderBy('nom')->get(['id', 'nom']),
        ]);
    }

    public function majCompte(Request $request)
    {
        $donnees = $request->validate($this->reglesCompte() + [
            'familles' => ['nullable', 'array'],
            'familles.*' => ['integer', 'exists:categories,id'],
        ], [], $this->nomsChamps());

        $request->user()->comptePro->update($donnees + ['familles' => $donnees['familles'] ?? null]);

        return back()->with('success', 'Vos informations sont à jour.');
    }

    public function catalogue(Request $request)
    {
        $categories = Categorie::orderBy('nom')
            ->with(['produits' => fn ($q) => $q->where('disponible', true)->whereNotNull('prix_pro')->orderBy('nom')])
            ->get()
            ->map(function ($categorie) {
                $categorie->setRelation('produits', $categorie->produits->filter(fn (Produit $p) => $p->formatsPro())->values());

                return $categorie;
            })
            ->filter(fn ($categorie) => $categorie->produits->isNotEmpty())
            ->values();

        $pdf = Pdf::loadView('pro.catalogue-pdf', [
            'compte' => $request->user()->comptePro,
            'categories' => $categories,
        ]);

        return $pdf->download('tarifs-pro-'.boutique_slug().'-'.now()->format('Y-m-d').'.pdf');
    }

    // ===== Outils =====

    private function verifierProprietaire(Request $request, Commande $commande): void
    {
        abort_unless($commande->est_pro && $commande->user_id === $request->user()->id, 404);
    }

    private function reglesCompte(): array
    {
        return [
            'boutique' => ['required', 'string', 'max:120'],
            'activite' => ['required', Rule::in(array_keys(ComptePro::ACTIVITES))],
            'ville' => ['required', 'string', 'max:80'],
            'commune' => ['nullable', 'string', 'max:80'],
            'whatsapp' => ['required', 'string', 'regex:/^[0-9 +().-]{8,20}$/'],
            'volume_mensuel' => ['nullable', Rule::in(array_keys(ComptePro::VOLUMES))],
            'rccm' => ['nullable', 'string', 'max:60'],
            'presentation' => ['nullable', 'string', 'max:1000'],
        ];
    }

    private function nomsChamps(): array
    {
        return [
            'boutique' => 'nom de l\'atelier ou de la boutique',
            'activite' => 'activité',
            'ville' => 'ville',
            'whatsapp' => 'numéro WhatsApp',
            'name' => 'nom et prénom',
            'email' => 'adresse e-mail',
            'password' => 'mot de passe',
        ];
    }
}
