<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commandes_clients', function (Blueprint $table) {
            $table->string('commande_numero', 50)->nullable()->after('id');
        });
        Schema::table('commandes_clients', function (Blueprint $table) {
            $table->index('commande_numero');
        });
    }

    public function down(): void
    {
        Schema::table('commandes_clients', function (Blueprint $table) {
            $table->dropIndex(['commande_numero']);
            $table->dropColumn('commande_numero');
        });
    }
};
