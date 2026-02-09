<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['stock_magasins', 'stock_categories', 'stock_fournisseurs'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('usage', 20)->default('commande_carte')->after('observations')
                    ->comment('appro = approvisionnement uniquement, commande_carte = commandes/cartes uniquement');
            });
        }
    }

    public function down(): void
    {
        foreach (['stock_magasins', 'stock_categories', 'stock_fournisseurs'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('usage');
            });
        }
    }
};
