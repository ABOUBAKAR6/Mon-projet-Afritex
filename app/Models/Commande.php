<?php

namespace App\Models;

use App\Mail\CommandeRecueMail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

use function Illuminate\Support\defer;

class Commande extends Model
{
    protected $fillable = [
        'user_id',
        'total',
        'statut',
        'adresse_livraison',
        'mode_livraison',
        'tracking_number',
        'coupon_code',
        'discount_amount',
        'confirmation_envoyee_at',
        'est_pro',
        'frais_livraison',
        'livraison_prevue_le',
        'livraison_souhaitee_le',
        'note_client',
        'note_boutique',
        'devis_envoye_le',
    ];

    /**
     * Commandes en gros (Espace Pro) : le pro demande un devis (devis_demande), la boutique
     * le chiffre et l'envoie (devis_envoye), le pro l'accepte : la commande passe « en_attente »
     * et suit ensuite le parcours habituel (paiement, préparation, livraison). Ou il le refuse.
     */
    public const STATUTS_DEVIS = ['devis_demande', 'devis_envoye', 'devis_refuse'];

    // Ventes comptées dans le chiffre d'affaires : confirmées (payées ou paiement à la livraison validé) jusqu'à la livraison
    public const STATUTS_VENDUS = ['confirmee', 'en_preparation', 'expedie', 'livree'];

    // Commandes acceptées et pas encore livrées ni annulées
    public const STATUTS_EN_COURS = ['en_attente', 'confirmee', 'en_preparation', 'expedie'];

    protected function casts(): array
    {
        return [
            'confirmation_envoyee_at' => 'datetime',
            'est_pro' => 'boolean',
            'livraison_prevue_le' => 'date',
            'livraison_souhaitee_le' => 'date',
            'devis_envoye_le' => 'datetime',
        ];
    }

    public function estDevis(): bool
    {
        return in_array($this->statut, self::STATUTS_DEVIS, true);
    }

    /**
     * Seule une commande « en attente » se paie : jamais un devis pas encore chiffré ou validé.
     * Déjà payée, ou déjà réglée en espèces (stock décompté) : plus de second paiement.
     * Un essai Mobile Money ou carte resté en attente peut, lui, être recommencé.
     */
    public function estAPayer(): bool
    {
        return $this->statut === 'en_attente'
            && ! Paiement::where('commande_id', $this->id)
                ->where(fn ($q) => $q->where('statut', 'confirme')->orWhere('methode', 'especes'))
                ->exists();
    }

    // Total des articles, hors frais de livraison
    public function sousTotalArticles(): int
    {
        return (int) $this->items->sum('sous_total');
    }

    // Chaque commande reçoit son numéro de suivi dès sa création (lien « Suivre ma commande »)
    protected static function booted(): void
    {
        static::creating(function (Commande $commande) {
            $commande->tracking_number ??= 'AFX-' . strtoupper(Str::random(8));
        });
    }

    // ✅ Relation manquante — causait l'erreur "paiement"
    public function paiement()
    {
        return $this->hasOne(Paiement::class);
    }

    // ✅ Relation manquante — causait l'erreur "items"
    public function items()
    {
        return $this->hasMany(CommandeItem::class);
    }

    // ✅ Relation vers le client
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function estEnBoutique(): bool
    {
        return $this->mode_livraison === 'boutique';
    }

    /**
     * E-mail « commande bien reçue », une seule fois par commande :
     * dès le choix des espèces, ou à la confirmation d'un paiement en ligne (retour client ou webhook).
     * La date est posée d'abord, en une requête conditionnelle : deux confirmations simultanées n'envoient qu'un e-mail.
     * L'envoi a lieu après la réponse ; un échec est consigné dans les logs sans bloquer la commande.
     */
    public function envoyerConfirmation(bool $differe = true): bool
    {
        $reserve = static::whereKey($this->id)->whereNull('confirmation_envoyee_at')
            ->update(['confirmation_envoyee_at' => now()]);

        // Client sans e-mail (commande sans compte) : rien à envoyer, la commande est marquée comme traitée
        if (!$reserve || !$this->user || $this->user->estSansEmail()) {
            return false;
        }

        $commande = $this;
        $envoi = function () use ($commande) {
            try {
                Mail::to($commande->user)->send(new CommandeRecueMail($commande->fresh(['items.produit', 'paiement', 'user'])));
            } catch (\Throwable $e) {
                report($e);
                // Échec (serveur mail indisponible, clé absente…) : la commande redevient « à confirmer »,
                // pour qu'un nouvel essai (php artisan commandes:confirmations) puisse la renvoyer.
                static::whereKey($commande->id)->update(['confirmation_envoyee_at' => null]);
                return false;
            }
            return true;
        };

        // Sur le site : après la réponse. En ligne de commande : tout de suite, pour connaître le résultat.
        if ($differe) {
            defer($envoi);
            return true;
        }

        return $envoi();
    }
}
