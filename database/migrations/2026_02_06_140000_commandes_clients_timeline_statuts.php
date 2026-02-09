<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Mise à jour des anciens statuts vers la timeline : Soumis, En cours de traitement, Finalisé, Livré
        DB::table('commandes_clients')
            ->where('statut', 'en_attente')
            ->update(['statut' => 'soumis']);
        DB::table('commandes_clients')
            ->where('statut', 'validee')
            ->update(['statut' => 'en_cours_de_traitement']);
    }

    public function down(): void
    {
        DB::table('commandes_clients')
            ->where('statut', 'soumis')
            ->update(['statut' => 'en_attente']);
        DB::table('commandes_clients')
            ->where('statut', 'en_cours_de_traitement')
            ->update(['statut' => 'validee']);
    }
};
