<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCouleursToProduits extends Migration
{
    public function up()
    {
        Schema::table('produits', function (Blueprint $table) {
            $table->json('couleurs')->nullable()->after('image');
        });
    }

    public function down()
    {
        Schema::table('produits', function (Blueprint $table) {
            $table->dropColumn('couleurs');
        });
    }
}