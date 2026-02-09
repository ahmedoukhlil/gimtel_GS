<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\User;
use Spatie\Permission\Models\Role;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * Synchronise les rôles : colonne users.role <-> Spatie (model_has_roles).
 * Corrige les rôles invalides (mis à 'agent') et assigne chaque utilisateur au rôle Spatie correspondant.
 */
Artisan::command('users:sync-roles', function () {
    $validRoles = ['admin', 'admin_stock', 'agent', 'client', 'direction_production'];

    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

    // S'assurer que tous les rôles existent dans Spatie
    foreach ($validRoles as $name) {
        Role::firstOrCreate(['name' => $name]);
    }

    $users = User::all();
    $fixed = 0;
    $synced = 0;

    foreach ($users as $user) {
        $columnRole = $user->getRawOriginal('role') ?? $user->getAttribute('role');
        $columnRole = is_string($columnRole) ? trim($columnRole) : '';

        // Corriger la colonne si invalide ou vide
        if (!in_array($columnRole, $validRoles, true)) {
            $user->setAttribute('role', 'agent');
            $user->saveQuietly(); // évite de re-déclencher l'observer en boucle
            $user->syncRoles(['agent']);
            $fixed++;
        } else {
            $user->syncRoles([$columnRole]);
            $synced++;
        }
    }

    $this->info("Rôles synchronisés : {$synced} utilisateur(s) à jour, {$fixed} corrigé(s) (role → agent).");
})->purpose('Synchroniser la colonne users.role avec les rôles Spatie et corriger les valeurs invalides');
