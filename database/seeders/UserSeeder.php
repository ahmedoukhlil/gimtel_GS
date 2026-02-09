<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Utilisateurs à créer (un par rôle actuel).
     * Colonnes réelles : users (identifiant / email), mdp, role.
     */
    protected function getDefaultUsers(): array
    {
        return [
            [
                'users' => 'admin@gimtel.com',
                'mdp' => 'password',
                'role' => 'admin',
            ],
            [
                'users' => 'admin.stock@gimtel.com',
                'mdp' => 'password',
                'role' => 'admin_stock',
            ],
            [
                'users' => 'agent@gimtel.com',
                'mdp' => 'password',
                'role' => 'agent',
            ],
            [
                'users' => 'client@gimtel.com',
                'mdp' => 'password',
                'role' => 'client',
            ],
            [
                'users' => 'direction.production@gimtel.com',
                'mdp' => 'password',
                'role' => 'direction_production',
            ],
            [
                'users' => 'demandeur.interne@gimtel.com',
                'mdp' => 'password',
                'role' => 'demandeur_interne',
            ],
            [
                'users' => 'direction.moyens.generaux@gimtel.com',
                'mdp' => 'password',
                'role' => 'direction_moyens_generaux',
            ],
        ];
    }

    public function run(): void
    {
        $created = 0;
        $skipped = 0;

        foreach ($this->getDefaultUsers() as $data) {
            $exists = User::where('users', $data['users'])->exists();
            if ($exists) {
                $this->command->warn("Utilisateur déjà existant : {$data['users']}");
                $skipped++;
                continue;
            }

            $user = User::create([
                'users' => $data['users'],
                'mdp' => Hash::make($data['mdp']),
                'role' => $data['role'],
            ]);
            $user->syncRoles([$data['role']]);

            $label = User::getRoleLabel($data['role']);
            $this->command->info("{$label} créé : {$data['users']}");
            $created++;
        }

        $this->command->info("UserSeeder terminé : {$created} créé(s), {$skipped} ignoré(s).");
    }
}
