<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Compte revendeur de l'Espace Pro : demandé par un couturier, un atelier ou une boutique,
 * puis validé (ou refusé) par l'équipe. Seul un compte validé voit les prix pro et commande en gros.
 */
class ComptePro extends Model
{
    protected $table = 'comptes_pro';

    protected $fillable = [
        'user_id', 'boutique', 'activite', 'ville', 'commune', 'whatsapp', 'volume_mensuel',
        'familles', 'rccm', 'presentation', 'statut', 'motif_refus', 'valide_le',
    ];

    public const ACTIVITES = [
        'couturier' => 'Couturier ou couturière',
        'atelier' => 'Atelier de confection',
        'boutique' => 'Boutique de tissus ou de pagnes',
        'en_ligne' => 'Vente en ligne ou en live',
    ];

    public const VOLUMES = [
        'moins_100k' => 'Moins de 100 000 F',
        '100k_300k' => '100 000 à 300 000 F',
        '300k_1m' => '300 000 à 1 000 000 F',
        'plus_1m' => 'Plus de 1 000 000 F',
    ];

    protected function casts(): array
    {
        return [
            'familles' => 'array',
            'valide_le' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function estValide(): bool
    {
        return $this->statut === 'valide';
    }

    public function estEnAttente(): bool
    {
        return $this->statut === 'en_attente';
    }

    public function estRefuse(): bool
    {
        return $this->statut === 'refuse';
    }

    public function libelleActivite(): string
    {
        return self::ACTIVITES[$this->activite] ?? $this->activite;
    }

    public function libelleVolume(): ?string
    {
        return $this->volume_mensuel ? (self::VOLUMES[$this->volume_mensuel] ?? $this->volume_mensuel) : null;
    }

    /** Lien WhatsApp vers le revendeur (numéro ivoirien, indicatif 225 ajouté si absent). */
    public function lienWhatsapp(): string
    {
        $chiffres = preg_replace('/\D+/', '', $this->whatsapp);
        if (! str_starts_with($chiffres, '225')) {
            $chiffres = '225'.$chiffres;
        }

        return 'https://wa.me/'.$chiffres;
    }
}
