<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produit_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produit_id')->constrained()->onDelete('cascade');
            $table->string('chemin');         // chemin de l'image
            $table->string('couleur')->nullable();  // nom couleur ex: "Beige"
            $table->string('code_couleur')->nullable(); // ex: "#F5DEB3"
            $table->integer('ordre')->default(0);   // ordre d'affichage
            $table->boolean('est_principale')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produit_images');
    }
};