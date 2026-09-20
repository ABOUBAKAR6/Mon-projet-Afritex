<?php

namespace App\Events;


use App\Models\Commande;
use Illuminate\Foundation\Events\Dispatchable;

class StatutCommandeModifie
{
    use Dispatchable;

    public function __construct(public Commande $commande) {}
}
