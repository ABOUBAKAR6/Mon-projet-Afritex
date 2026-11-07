<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Stock des tissus compté en mètres, affiché en rouleaux :
// - produits.stock reste le nombre de rouleaux ENTIERS (les chiffres actuels sont des rouleaux) ;
// - metres_entames : mètres restant dans le rouleau ouvert ;
// - metres_par_rouleau : longueur d'un rouleau de ce tissu (vide tant qu'elle n'est pas connue).
// Chaque entrée, sortie, inventaire ou vente laisse une ligne dans mouvements_stock.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produits', function (Blueprint $table) {
            $table->decimal('metres_par_rouleau', 8, 2)->nullable()->after('stock');
            $table->decimal('metres_entames', 10, 2)->default(0)->after('metres_par_rouleau');
        });

        Schema::create('mouvements_stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produit_id')->constrained('produits')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('commande_id')->nullable()->constrained('commandes')->nullOnDelete();
            $table->string('type', 20); // arrivage, retrait, inventaire, vente
            $table->integer('rouleaux')->default(0);          // variation des rouleaux entiers
            $table->decimal('metres', 10, 2)->default(0);     // variation des mètres entamés
            $table->integer('rouleaux_apres')->default(0);
            $table->decimal('metres_apres', 10, 2)->default(0);
            $table->string('note', 255)->nullable();
            $table->timestamps();

            $table->index(['produit_id', 'created_at']);
            $table->index(['commande_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mouvements_stock');

        Schema::table('produits', function (Blueprint $table) {
            $table->dropColumn(['metres_par_rouleau', 'metres_entames']);
        });
    }
};
