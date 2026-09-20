<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryPoint extends Model
{
    protected $fillable = ['name', 'city', 'address'];

    public function commandes(): HasMany
    {
        return $this->hasMany(Commande::class);
    }
}
