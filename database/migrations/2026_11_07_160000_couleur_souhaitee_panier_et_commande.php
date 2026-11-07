<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Couleur de tissu souhaitée par le client, choisie sur la page produit :
// elle suit l'article du panier jusqu'à la ligne de commande. Les remarques du
// panier vont dans commandes.note_client (colonne déjà présente).
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->string('couleur', 100)->nullable()->after('type_commande');
        });

        Schema::table('commande_items', function (Blueprint $table) {
            $table->string('couleur', 100)->nullable()->after('type_commande');
        });
    }

    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropColumn('couleur');
        });

        Schema::table('commande_items', function (Blueprint $table) {
            $table->dropColumn('couleur');
        });
    }
};
