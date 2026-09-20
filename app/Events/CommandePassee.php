<?php
namespace App\Events;

use App\Models\Commande;
use Illuminate\Foundation\Events\Dispatchable;

class CommandePassee
{
    use Dispatchable;

    public function __construct(public Commande $commande) {}
}