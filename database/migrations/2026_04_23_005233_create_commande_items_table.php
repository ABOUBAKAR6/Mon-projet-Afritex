<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commande_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commande_id')->constrained('commandes');
            $table->foreignId('produit_id')->constrained('produits');
            $table->enum('type_commande', [
                'metre', 'rouleau', 'pantalon',
                'chemise_courte', 'chemise_longue',
                'tunique_courte', 'tunique_longue'
            ]);
            $table->decimal('quantite', 10, 2)->default(1);
            $table->decimal('prix_unitaire', 10, 2);
            $table->decimal('sous_total', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commande_items');
    }
};