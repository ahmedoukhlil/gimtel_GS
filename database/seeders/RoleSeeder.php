<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use App\Models\StockProduit;
use App\Models\StockReservation;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Créer les permissions
        Permission::firstOrCreate(['name' => 'passer_commande']);
        Permission::firstOrCreate(['name' => 'valider_commande']);
        Permission::firstOrCreate(['name' => 'gerer_stock_total']);
        Permission::firstOrCreate(['name' => 'voir_mon_stock']);

        // Créer tous les rôles (Spatie)
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

        $validRoles = User::getValidRoles();

        // Corriger et synchroniser tous les utilisateurs (colonne role <-> Spatie)
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

        // Créer des utilisateurs de test uniquement s'ils n'existent pas
        if (!User::where('users', 'admin')->exists()) {
            $admin = User::create([
                'users' => 'admin',
                'mdp' => Hash::make('password'),
                'role' => 'admin',
            ]);
            $admin->syncRoles(['admin']);
        }

        if (!User::where('users', 'client1')->exists()) {
            $client = User::create([
                'users' => 'client1',
                'mdp' => Hash::make('password'),
                'role' => 'client',
            ]);
            $client->syncRoles(['client']);
            $produit = StockProduit::first();
            if ($produit) {
                StockReservation::create([
                    'client_id' => $client->idUser,
                    'produit_id' => $produit->id,
                    'quantite_reservee' => 50,
                ]);
            }
        }

        if (!User::where('users', 'production')->exists()) {
            $production = User::create([
                'users' => 'production',
                'mdp' => Hash::make('password'),
                'role' => 'direction_production',
            ]);
            $production->syncRoles(['direction_production']);
        }
    }
}
