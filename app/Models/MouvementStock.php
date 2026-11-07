<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Une ligne de l'historique du stock d'un tissu : arrivage, retrait, inventaire ou vente.
 * « rouleaux » et « metres » sont des variations ; « *_apres » le stock juste après.
 */
class MouvementStock extends Model
{
    protected $table = 'mouvements_stock';

    public const TYPES = [
        'arrivage' => ['libelle' => 'Arrivage', 'pastille' => 'pastille-succes'],
        'retrait' => ['libelle' => 'Retrait', 'pastille' => 'pastille-danger'],
        'inventaire' => ['libelle' => 'Inventaire', 'pastille' => 'pastille-brun'],
        'vente' => ['libelle' => 'Vente', 'pastille' => 'pastille-attente'],
    ];

    protected $fillable = [
        'produit_id', 'user_id', 'commande_id', 'type',
        'rouleaux', 'metres', 'rouleaux_apres', 'metres_apres', 'note',
    ];

    protected $casts = [
        'rouleaux' => 'integer',
        'metres' => 'float',
        'rouleaux_apres' => 'integer',
        'metres_apres' => 'float',
    ];

    public function produit(): BelongsTo
    {
        return $this->belongsTo(Produit::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function commande(): BelongsTo
    {
        return $this->belongsTo(Commande::class);
    }

    public function libelle(): string
    {
        return self::TYPES[$this->type]['libelle'] ?? ucfirst($this->type);
    }

    public function pastille(): string
    {
        return self::TYPES[$this->type]['pastille'] ?? '';
    }
}
