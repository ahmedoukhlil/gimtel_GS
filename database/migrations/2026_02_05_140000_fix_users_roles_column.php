<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Rôles valides (alignés sur App\Models\User::VALID_ROLES et Spatie).
     * À garder en phase si vous modifiez la logique métier.
     */
    private const VALID_ROLES = ['admin', 'admin_stock', 'agent', 'client', 'direction_production'];

    /**
     * Corriger les entrées de la table users pour que la colonne role
     * ne contienne que des rôles valides. Les valeurs invalides sont mises à 'agent'.
     */
    public function up(): void
    {
        $placeholders = implode(',', array_fill(0, count(self::VALID_ROLES), '?'));
        $defaultRole = 'agent';
        $params = array_merge([$defaultRole], self::VALID_ROLES);

        DB::statement(
            "UPDATE users SET role = ? WHERE TRIM(COALESCE(role, '')) = '' OR role NOT IN ({$placeholders})",
            $params
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Pas de rollback : on ne peut pas restaurer les anciennes valeurs
    }
};
