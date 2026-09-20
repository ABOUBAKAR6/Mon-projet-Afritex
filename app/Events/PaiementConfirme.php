<?php

namespace App\Events;

use App\Models\Paiement;
use Illuminate\Foundation\Events\Dispatchable;

class PaiementConfirme
{
    use Dispatchable;

    public function __construct(public Paiement $paiement) {}
}