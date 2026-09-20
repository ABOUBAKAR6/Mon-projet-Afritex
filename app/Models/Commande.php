<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commande extends Model
{
    protected $fillable = [
        'user_id',
        'total',
        'statut',
        'adresse_livraison',
        'coupon_code',
        'discount_amount',
    ];

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
}