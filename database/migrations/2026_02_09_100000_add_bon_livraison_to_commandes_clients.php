<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commandes_clients', function (Blueprint $table) {
            $table->string('bon_livraison_numero', 50)->nullable()->after('motif_rejet');
            $table->string('bl_signe_path', 500)->nullable()->after('bon_livraison_numero');
        });
    }

    public function down(): void
    {
        Schema::table('commandes_clients', function (Blueprint $table) {
            $table->dropColumn(['bon_livraison_numero', 'bl_signe_path']);
        });
    }
};
