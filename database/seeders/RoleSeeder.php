<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Permissions
        Permission::firstOrCreate(['name' => 'passer_commande']);
        Permission::firstOrCreate(['name' => 'valider_commande']);
        Permission::firstOrCreate(['name' => 'gerer_stock_total']);
        Permission::firstOrCreate(['name' => 'voir_mon_stock']);

        // Rôles actuels (alignés sur User::VALID_ROLES)
        $roleAdmin = Role::firstOrCreate(['name' => 'admin']);

        $roleClient = Role::firstOrCreate(['name' => 'client']);
        if (!$roleClient->hasPermissionTo('passer_commande')) {
            $roleClient->givePermissionTo(['passer_commande', 'voir_mon_stock']);
        }

        $roleProduction = Role::firstOrCreate(['name' => 'direction_production']);
        if (!$roleProduction->hasPermissionTo('valider_commande')) {
            $roleProduction->givePermissionTo(['valider_commande', 'voir_mon_stock']);
        }

        $roleAdminStock = Role::firstOrCreate(['name' => 'admin_stock']);
        if (!$roleAdminStock->hasPermissionTo('gerer_stock_total')) {
            $roleAdminStock->givePermissionTo(['gerer_stock_total', 'voir_mon_stock']);
        }

        Role::firstOrCreate(['name' => 'agent']);
        Role::firstOrCreate(['name' => 'demandeur_interne']);
        Role::firstOrCreate(['name' => 'direction_moyens_generaux']);

        // Synchroniser les utilisateurs existants (colonne role <-> Spatie)
        $validRoles = User::getValidRoles();
        User::all()->each(function (User $user) use ($validRoles) {
            $roleName = $user->getRawOriginal('role') ?? $user->getAttribute('role');
            $roleName = is_string($roleName) ? trim($roleName) : '';

            if (!in_array($roleName, $validRoles, true)) {
                $roleName = 'agent';
                $user->setAttribute('role', 'agent');
                $user->saveQuietly();
            }
            if (Role::where('name', $roleName)->exists()) {
                $user->syncRoles([$roleName]);
            }
        });

        $this->command->info('Rôles et permissions créés / synchronisés.');
    }
}
