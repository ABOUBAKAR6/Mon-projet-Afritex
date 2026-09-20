<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alerte_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('produit_id')->constrained()->onDelete('cascade');
            $table->integer('seuil_minimum')->default(5);
            $table->boolean('est_active')->default(true);
            $table->timestamp('date_prochaine_verification')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'produit_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alerte_stocks');
    }
};
