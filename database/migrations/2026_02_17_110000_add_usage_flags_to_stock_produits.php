<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_produits', function (Blueprint $table) {
            $table->boolean('utilise_approvisionnement')->default(true)->after('observations')
                ->comment('Utilisable dans les demandes d\'approvisionnement internes');
            $table->boolean('utilise_commande_carte')->default(true)->after('utilise_approvisionnement')
                ->comment('Utilisable dans les commandes / cartes clients (réservations)');
        });
    }

    public function down(): void
    {
        Schema::table('stock_produits', function (Blueprint $table) {
            $table->dropColumn(['utilise_approvisionnement', 'utilise_commande_carte']);
        });
    }
};
