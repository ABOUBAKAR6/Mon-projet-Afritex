<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('categorie_id');
            $table->string('nom');
            $table->string('slug')->unique()->nullable();
            $table->text('description')->nullable();
            $table->string('meta_title', 191)->nullable();
            $table->text('meta_description')->nullable();
            $table->decimal('prix_metre', 10, 2)->nullable();
            $table->decimal('prix_rouleau', 10, 2)->nullable();
            $table->decimal('prix_pantalon', 10, 2)->nullable();
            $table->decimal('prix_chemise', 10, 2)->nullable();
            $table->decimal('prix_tunique', 10, 2)->nullable();
            $table->string('image')->nullable();
            $table->string('video_path')->nullable();
            $table->json('couleurs')->nullable();
            $table->integer('stock')->default(0);
            $table->boolean('disponible')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produits');
    }
};
