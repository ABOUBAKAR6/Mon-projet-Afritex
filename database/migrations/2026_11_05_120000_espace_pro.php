<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

// Espace Pro (vente en gros) : comptes revendeurs validés par la boutique,
// prix pro par format de vente, commandes en gros passées par devis.
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('comptes_pro')) {
            Schema::create('comptes_pro', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->unique();
                $table->string('boutique');
                $table->string('activite', 30);
                $table->string('ville', 80);
                $table->string('commune', 80)->nullable();
                $table->string('whatsapp', 30);
                $table->string('volume_mensuel', 30)->nullable();
                $table->json('familles')->nullable();
                $table->string('rccm', 60)->nullable();
                $table->text('presentation')->nullable();
                $table->string('statut', 20)->default('en_attente')->index();
                $table->string('motif_refus')->nullable();
                $table->timestamp('valide_le')->nullable();
                $table->timestamps();
            });

            // Clé étrangère seulement si « users » la supporte (InnoDB, ou SQLite des tests).
            // Une ancienne base locale en MyISAM la refuse : la suppression du compte pro
            // avec l'utilisateur est de toute façon assurée par le modèle User.
            if ($this->usersAccepteLesCles()) {
                Schema::table('comptes_pro', function (Blueprint $table) {
                    $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
                });
            }
        }

        if (! Schema::hasColumn('produits', 'prix_pro')) {
            Schema::table('produits', function (Blueprint $table) {
                // { "metre": 2500, "rouleau": 42000, ... } : prix réservés aux comptes pro validés
                $table->json('prix_pro')->nullable();
            });
        }

        Schema::table('commandes', function (Blueprint $table) {
            if (! Schema::hasColumn('commandes', 'est_pro')) {
                $table->boolean('est_pro')->default(false)->index();
            }
            if (! Schema::hasColumn('commandes', 'frais_livraison')) {
                $table->unsignedInteger('frais_livraison')->nullable();
            }
            if (! Schema::hasColumn('commandes', 'livraison_prevue_le')) {
                $table->date('livraison_prevue_le')->nullable();
            }
            if (! Schema::hasColumn('commandes', 'livraison_souhaitee_le')) {
                $table->date('livraison_souhaitee_le')->nullable();
            }
            if (! Schema::hasColumn('commandes', 'note_client')) {
                $table->text('note_client')->nullable();
            }
            if (! Schema::hasColumn('commandes', 'note_boutique')) {
                $table->text('note_boutique')->nullable();
            }
            if (! Schema::hasColumn('commandes', 'devis_envoye_le')) {
                $table->timestamp('devis_envoye_le')->nullable();
            }
        });

        // Droit accordable à un secrétaire : examiner les demandes et gérer les comptes pro
        Permission::firstOrCreate(['name' => 'manage-pros', 'guard_name' => 'web']);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function usersAccepteLesCles(): bool
    {
        if (! in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            return true;
        }

        $moteur = DB::selectOne(
            'SELECT ENGINE AS moteur FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?',
            [DB::connection()->getDatabaseName(), 'users']
        )?->moteur;

        return strtolower((string) $moteur) === 'innodb';
    }

    public function down(): void
    {
        Permission::where('name', 'manage-pros')->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Schema::table('commandes', function (Blueprint $table) {
            foreach (['est_pro', 'frais_livraison', 'livraison_prevue_le', 'livraison_souhaitee_le', 'note_client', 'note_boutique', 'devis_envoye_le'] as $colonne) {
                if (Schema::hasColumn('commandes', $colonne)) {
                    $table->dropColumn($colonne);
                }
            }
        });

        if (Schema::hasColumn('produits', 'prix_pro')) {
            Schema::table('produits', fn (Blueprint $table) => $table->dropColumn('prix_pro'));
        }

        Schema::dropIfExists('comptes_pro');
    }
};
