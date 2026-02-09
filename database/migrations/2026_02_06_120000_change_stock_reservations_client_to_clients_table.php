<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Fait pointer stock_reservations.client_id vers la table clients au lieu de users.
     */
    public function up(): void
    {
        Schema::table('stock_reservations', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
        });

        // Anciennes valeurs = idUser ; les nouvelles = clients.id → on vide pour éviter violation FK
        DB::table('stock_reservations')->truncate();

        DB::statement('ALTER TABLE stock_reservations MODIFY client_id BIGINT UNSIGNED NOT NULL');
        Schema::table('stock_reservations', function (Blueprint $table) {
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_reservations', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
        });

        DB::statement('ALTER TABLE stock_reservations MODIFY client_id INT NOT NULL');
        Schema::table('stock_reservations', function (Blueprint $table) {
            $table->foreign('client_id')->references('idUser')->on('users')->onDelete('cascade');
        });
    }
};
