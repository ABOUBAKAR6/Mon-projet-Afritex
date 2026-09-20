<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommandeItem extends Model
{
    protected $fillable = [
        'commande_id',
        'produit_id',
        'type_commande',
        'quantite',
        'prix_unitaire',
        'sous_total',
    ];

    // ✅ Relation manquante — c'est ça qui causait l'erreur
    public function produit()
    {
        return $this->belongsTo(Produit::class);
    }

    public function commande()
    {
        return $this->belongsTo(Commande::class);
    }
}