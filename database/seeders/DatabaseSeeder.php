<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Rôles et permissions avant les utilisateurs
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            LocalisationSeeder::class,
            BienSeeder::class,
            InventaireSeeder::class,
        ]);
    }
}
