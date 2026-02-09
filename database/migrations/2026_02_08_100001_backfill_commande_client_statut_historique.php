<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Remplit l'historique pour les commandes existantes : au moins "Soumis" (created_at) et le statut actuel (updated_at).
     */
    public function up(): void
    {
        $commandes = DB::table('commandes_clients')->get();
        foreach ($commandes as $c) {
            DB::table('commande_client_statut_historique')->insert([
                'commande_client_id' => $c->id,
                'statut' => 'soumis',
                'created_at' => $c->created_at,
                'updated_at' => $c->created_at,
            ]);
            if ($c->statut !== 'soumis') {
                DB::table('commande_client_statut_historique')->insert([
                    'commande_client_id' => $c->id,
                    'statut' => $c->statut,
                    'created_at' => $c->updated_at,
                    'updated_at' => $c->updated_at,
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('commande_client_statut_historique')->truncate();
    }
};
