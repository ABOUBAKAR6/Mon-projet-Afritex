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

        // Plus de validation par la boutique : une demande restée en attente s'ouvre tout de suite
        if ($user?->comptePro?->estEnAttente()) {
            $user->comptePro->update(['statut' => 'valide', 'valide_le' => now()]);

            return redirect()->route('pro.accueil');
        }

        // Compte refusé par la boutique : la page d'accueil explique le refus
        if ($user?->comptePro?->estRefuse()) {
            return view('pro.accueil', ['compte' => $user->comptePro]);
        }

        // Tout le monde voit directement tous les tissus et peut demander un devis
        return redirect()->route('pro.commander');
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
                'statut' => 'valide',
                'valide_le' => now(),
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
            'Nouveau compte pro ouvert : '.$compte->boutique,
            [
                $compte->boutique.' ('.$compte->libelleActivite().', '.$compte->ville.') demande à commander en gros.',
                'WhatsApp : '.$compte->whatsapp.($compte->libelleVolume() ? '. Achat mensuel estimé : '.$compte->libelleVolume() : '').'.',
            ],
            route('admin.pros'),
            'Voir les revendeurs',
            'demande_pro',
        ));

        return redirect()->route('pro.accueil')
            ->with('success', 'Votre compte pro est ouvert : vous pouvez commander en gros.');
    }

    // ===== Revendeur validé =====

    public function commander(Request $request)
    {
        $categories = Categorie::orderBy('nom')
            ->with(['produits' => fn ($q) => $q->where('disponible', true)->orderBy('nom')])
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
        if ($request->filled('depuis') && $request->user()) {
            $source = Commande::where('user_id', $request->user()->id)->where('est_pro', true)
                ->with('items')->find($request->integer('depuis'));
            foreach ($source?->items ?? [] as $item) {
                // Entier : la colonne décimale renvoie « 25.00 », que la règle « integer » refuserait à l'envoi
                $quantites[$item->produit_id][$item->type_commande] = (int) $item->quantite;
            }
        }

        return view('pro.commander', [
            'compte' => $request->user()?->comptePro,
            // Pas encore revendeur : ses coordonnées sont demandées sous la commande
            'nouveauPro' => ! $request->user()?->estProValide(),
            'activites' => ComptePro::ACTIVITES,
            'categories' => $categories,
            'quantites' => old('quantites', $quantites),
            'source' => $source,
            'derniereAdresse' => $request->user()?->commandes()->where('mode_livraison', 'domicile')->latest()->value('adresse_livraison'),
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

        $user = $request->user();
        if (! $user?->estProValide()) {
            if ($user?->comptePro?->estRefuse()) {
                return back()->withInput()->with('error', 'Votre compte pro a été refusé par la boutique : écrivez-lui sur WhatsApp.');
            }
            // Pas de compte à créer pour rien : au moins une quantité d'abord
            if (! collect($infos['quantites'])->flatten()->contains(fn ($q) => (int) $q > 0)) {
                throw \Illuminate\Validation\ValidationException::withMessages(['quantites' => 'Indiquez au moins une quantité pour un tissu.']);
            }
            $user = $this->ouvrirCompteProAutomatique($request, $user);
            if ($user instanceof \Illuminate\Http\RedirectResponse) {
                return $user;
            }
        }

        $commande = $this->espacePro->demanderDevis($user, $infos['quantites'], $infos);

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
            ->with(['produits' => fn ($q) => $q->where('disponible', true)->orderBy('nom')])
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

    /**
     * Compte pro ouvert à la première demande de devis, sans validation de la boutique.
     * Visiteur : un compte client est créé (mot de passe aléatoire, e-mail facultatif) et il est connecté ;
     * un e-mail déjà utilisé n'est jamais repris (« connectez-vous »). Retourne l'utilisateur, ou une redirection.
     */
    private function ouvrirCompteProAutomatique(Request $request, ?User $user)
    {
        $regles = [
            'boutique' => ['nullable', 'string', 'max:120'],
            'activite' => ['required', Rule::in(array_keys(ComptePro::ACTIVITES))],
            'whatsapp' => ['required', 'string', 'min:8', 'max:30'],
            'ville' => ['required', 'string', 'max:80'],
        ];
        if (! $user) {
            $regles += ['name' => ['required', 'string', 'max:120'], 'email' => ['nullable', 'email', 'max:255']];
        }
        $donnees = $request->validate($regles, [
            'name.required' => 'Indiquez votre nom.',
            'activite.required' => 'Indiquez votre activité.',
            'whatsapp.required' => 'Indiquez votre numéro WhatsApp : la boutique vous y envoie le devis.',
            'whatsapp.min' => 'Ce numéro semble incomplet.',
            'ville.required' => 'Indiquez votre ville.',
            'email.email' => 'Cette adresse e-mail n\'est pas valide.',
        ]);

        if (! $user) {
            $email = strtolower(trim((string) ($donnees['email'] ?? '')));
            if ($email !== '' && User::where('email', $email)->exists()) {
                return back()->withInput()->withErrors(['email' => 'Un compte existe déjà avec cet e-mail : connectez-vous, ou laissez ce champ vide.']);
            }
            $user = User::create([
                'name' => $donnees['name'],
                'email' => $email !== '' ? $email : 'pro-'.\Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(16)).'@'.User::DOMAINE_SANS_EMAIL,
                'phone' => $donnees['whatsapp'],
                'password' => Hash::make(\Illuminate\Support\Str::random(40)),
                'role' => 'client',
            ]);
            Auth::login($user, true);
            $request->session()->regenerate();
        }

        $compte = ComptePro::updateOrCreate(['user_id' => $user->id], [
            'boutique' => trim((string) ($donnees['boutique'] ?? '')) ?: $user->name,
            'activite' => $donnees['activite'],
            'ville' => $donnees['ville'],
            'whatsapp' => $donnees['whatsapp'],
            'statut' => 'valide',
            'valide_le' => now(),
        ]);
        $user->setRelation('comptePro', $compte);

        $this->espacePro->prevenirEquipe('manage-pros', new EspaceProNotification(
            'Nouveau compte pro ouvert : '.$compte->boutique,
            [$compte->boutique.' ('.$compte->libelleActivite().', '.$compte->ville.') commande en gros. WhatsApp : '.$compte->whatsapp.'.'],
            route('admin.pros'),
            'Voir les revendeurs',
            'demande_pro',
        ));

        return $user;
    }

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
