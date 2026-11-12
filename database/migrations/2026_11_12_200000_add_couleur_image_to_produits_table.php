<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Couleur montrée sur la photo principale d'un produit (les autres photos ont déjà
 * produit_images.couleur). La page produit affiche « Famille + couleur » selon la photo vue.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produits', function (Blueprint $table) {
            $table->string('couleur_image', 40)->nullable()->after('image');
        });
    }

    public function down(): void
    {
        Schema::table('produits', function (Blueprint $table) {
            $table->dropColumn('couleur_image');
        });
    }
};
