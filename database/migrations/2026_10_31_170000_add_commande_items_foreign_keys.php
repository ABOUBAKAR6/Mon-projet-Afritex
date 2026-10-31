<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Ajoute a la fin les FK de commande_items (la table de creation s'executant
// avant commandes/produits a timestamps identiques sur base vierge).
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('commande_items') || !Schema::hasTable('commandes')) {
            return;
        }

        $db = DB::connection()->getDatabaseName();

        $addFk = function (string $column, string $referenced) use ($db) {
            $exists = DB::selectOne(
                "SELECT COUNT(*) AS n FROM information_schema.TABLE_CONSTRAINTS
                 WHERE CONSTRAINT_SCHEMA = ? AND TABLE_NAME = 'commande_items'
                 AND CONSTRAINT_NAME = ?", [$db, "commande_items_{$column}_foreign"]
            )->n;
            if (! $exists) {
                Schema::table('commande_items', function (Blueprint $table) use ($column, $referenced) {
                    $table->foreign($column)->references('id')->on($referenced);
                });
            }
        };

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        try {
            $addFk('commande_id', 'commandes');
            $addFk('produit_id', 'produits');
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    public function down(): void
    {
        Schema::table('commande_items', function (Blueprint $table) {
            $table->dropForeign(['commande_id']);
            $table->dropForeign(['produit_id']);
        });
    }
};
