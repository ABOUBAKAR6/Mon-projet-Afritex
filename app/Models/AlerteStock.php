<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlerteStock extends Model
{
    protected $fillable = [
        'user_id',
        'produit_id',
        'seuil_minimum',
        'est_active',
        'date_prochaine_verification'
    ];

    protected $casts = [
        'est_active' => 'boolean',
        'date_prochaine_verification' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function produit()
    {
        return $this->belongsTo(Produit::class);
    }
}
