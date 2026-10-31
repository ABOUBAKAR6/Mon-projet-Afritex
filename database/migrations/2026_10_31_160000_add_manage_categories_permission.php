<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

// Droits que le propriétaire peut accorder à un secrétaire (voir User::DROITS).
// « manage-categories » manquait : les catégories étaient rangées avec les produits.
return new class extends Migration
{
    public function up(): void
    {
        foreach (['view-dashboard', 'manage-orders', 'manage-products', 'manage-categories', 'manage-users'] as $droit) {
            Permission::firstOrCreate(['name' => $droit, 'guard_name' => 'web']);
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Permission::where('name', 'manage-categories')->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
