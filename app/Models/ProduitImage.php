<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProduitImage extends Model
{
    // Définition des champs autorisés pour le remplissage de masse
    protected $fillable = [
        'produit_id',
        'chemin',
        'couleur',
        'code_couleur',
        'ordre',
        'est_principale',
    ];

    // Conversion automatique des types de données
    protected $casts = [
        'est_principale' => 'boolean',
        'ordre' => 'integer',
        'produit_id' => 'integer',
    ];

    /**
     * Relation inverse : Une image appartient à un seul produit.
     */
    public function produit(): BelongsTo
    {
        return $this->belongsTo(Produit::class);
    }
}