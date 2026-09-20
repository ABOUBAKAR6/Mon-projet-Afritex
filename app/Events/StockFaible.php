<?php

namespace App\Events;

use App\Models\Produit;
use Illuminate\Foundation\Events\Dispatchable;

class StockFaible
{
    use Dispatchable;

    public function __construct(public Produit $produit) {}
}