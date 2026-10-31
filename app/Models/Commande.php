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
    ];

    protected function casts(): array
    {
        return [
            'confirmation_envoyee_at' => 'datetime',
        ];
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

        if (!$reserve || !$this->user) {
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
