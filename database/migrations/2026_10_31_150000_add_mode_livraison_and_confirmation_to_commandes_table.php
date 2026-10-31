<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commandes', function (Blueprint $table) {
            // « domicile » ou « boutique » (retrait) : choisi au moment de la commande
            $table->string('mode_livraison', 20)->default('domicile')->after('adresse_livraison');
            // Date d'envoi de l'e-mail « commande reçue » : il ne part qu'une fois, quel que soit le paiement
            $table->timestamp('confirmation_envoyee_at')->nullable()->after('discount_amount');
        });

        // Les commandes existantes n'avaient jamais reçu de numéro de suivi
        DB::table('commandes')->whereNull('tracking_number')->orderBy('id')->each(function ($commande) {
            DB::table('commandes')->where('id', $commande->id)->update(['tracking_number' => 'AFX-' . strtoupper(Str::random(8))]);
        });
    }

    public function down(): void
    {
        Schema::table('commandes', function (Blueprint $table) {
            $table->dropColumn(['mode_livraison', 'confirmation_envoyee_at']);
        });
    }
};
