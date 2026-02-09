<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ProduitApprovisionnement;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemandeApprovisionnementSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            ['nom' => 'Direction technique', 'code' => 'DT', 'description' => null],
            ['nom' => 'Service RH', 'code' => 'SRH', 'description' => null],
            ['nom' => 'Direction des moyens généraux', 'code' => 'DMG', 'description' => null],
            ['nom' => 'Service administratif', 'code' => 'SA', 'description' => null],
        ];
        foreach ($services as $s) {
            Service::firstOrCreate(
                ['code' => $s['code']],
                ['nom' => $s['nom'], 'description' => $s['description'], 'actif' => true]
            );
        }

        $produits = [
            ['libelle' => 'Ramette papier A4', 'reference' => 'CONS-001', 'unite' => 'ramette', 'categorie' => 'Bureau'],
            ['libelle' => 'Stylos bleus', 'reference' => 'CONS-002', 'unite' => 'unité', 'categorie' => 'Bureau'],
            ['libelle' => 'Classeurs A4', 'reference' => 'CONS-003', 'unite' => 'unité', 'categorie' => 'Bureau'],
            ['libelle' => 'Enveloppes', 'reference' => 'CONS-004', 'unite' => 'paquet', 'categorie' => 'Bureau'],
            ['libelle' => 'Liquide correcteur', 'reference' => 'CONS-005', 'unite' => 'unité', 'categorie' => 'Bureau'],
        ];
        foreach ($produits as $p) {
            ProduitApprovisionnement::firstOrCreate(
                ['reference' => $p['reference']],
                [
                    'libelle' => $p['libelle'],
                    'unite' => $p['unite'],
                    'categorie' => $p['categorie'],
                    'actif' => true,
                ]
            );
        }

        if (\Spatie\Permission\Models\Role::where('name', 'demandeur_interne')->exists()
            && !User::where('users', 'demandeur1')->exists()) {
            $u = User::create([
                'users' => 'demandeur1',
                'mdp' => \Illuminate\Support\Facades\Hash::make('password'),
                'role' => 'demandeur_interne',
                'service_id' => Service::where('code', 'DT')->value('id'),
            ]);
            $u->syncRoles(['demandeur_interne']);
        }

        if (\Spatie\Permission\Models\Role::where('name', 'direction_moyens_generaux')->exists()
            && !User::where('users', 'dmg')->exists()) {
            $u = User::create([
                'users' => 'dmg',
                'mdp' => \Illuminate\Support\Facades\Hash::make('password'),
                'role' => 'direction_moyens_generaux',
            ]);
            $u->syncRoles(['direction_moyens_generaux']);
        }
    }
}
