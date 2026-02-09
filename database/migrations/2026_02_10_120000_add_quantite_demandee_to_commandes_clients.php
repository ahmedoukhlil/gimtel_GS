<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commandes_clients', function (Blueprint $table) {
            $table->unsignedInteger('quantite_demandee')->nullable()->after('quantite');
        });

        // Rétro-compat : considérer la quantité actuelle comme "demandée" pour les commandes existantes
        DB::table('commandes_clients')->whereNull('quantite_demandee')->update([
            'quantite_demandee' => DB::raw('quantite'),
        ]);
    }

    public function down(): void
    {
        Schema::table('commandes_clients', function (Blueprint $table) {
            $table->dropColumn('quantite_demandee');
        });
    }
};
