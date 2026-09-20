<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Permissions
        $permissions = [
            'manage-products' => 'Gérer les produits',
            'manage-orders' => 'Gérer les commandes',
            'manage-users' => 'Gérer les utilisateurs',
            'view-dashboard' => 'Voir le tableau de bord',
            'update-stock' => 'Mettre à jour le stock',
        ];

        foreach ($permissions as $name => $description) {
            Permission::create(['name' => $name, 'guard_name' => 'web']);
        }

        // Create Roles and Assign Permissions
        $adminRole = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $adminRole->givePermissionTo(array_keys($permissions));

        $clientRole = Role::create(['name' => 'client', 'guard_name' => 'web']);
        // Clients usually don't have these administrative permissions
    }
}
