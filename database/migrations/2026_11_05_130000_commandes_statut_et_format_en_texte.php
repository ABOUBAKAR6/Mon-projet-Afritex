<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Deux colonnes de commande étaient des enum figés, que MySQL en mode strict fait respecter :
// - commandes.statut (en_attente, confirmee, livree, annulee) refusait « en_preparation » et « expedie »,
//   proposés dans l'administration, et les statuts de devis de l'Espace Pro (devis_demande, devis_envoye, devis_refuse) ;
// - commande_items.type_commande (metre, rouleau, pantalon, chemise_courte…) refusait les formats
//   « chemise » et « tunique » de l'Espace Pro (Produit::FORMATS).
// Un texte court accepte toutes ces valeurs ; les listes font foi dans le code.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commandes', function (Blueprint $table) {
            $table->string('statut', 30)->default('en_attente')->change();
        });

        Schema::table('commande_items', function (Blueprint $table) {
            $table->string('type_commande', 30)->change();
        });
    }

    public function down(): void
    {
        // Volontairement vide : revenir aux enum tronquerait les commandes en préparation,
        // expédiées ou en devis, et les lignes « chemise » ou « tunique » des pros.
        // Le texte court reste compatible avec l'ancien code.
    }
};
