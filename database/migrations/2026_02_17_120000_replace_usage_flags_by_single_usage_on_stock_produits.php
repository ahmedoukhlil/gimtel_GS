<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_produits', function (Blueprint $table) {
            $table->string('usage', 20)->default('commande_carte')->after('observations')
                ->comment('appro = demandes approvisionnement uniquement, commande_carte = commandes/cartes clients uniquement');
        });

        // Remplir usage : ancien produit avec les deux à true → commande_carte, sinon selon les anciens booléens
        DB::table('stock_produits')->orderBy('id')->chunk(100, function ($rows) {
            foreach ($rows as $row) {
                $usage = 'commande_carte';
                if (isset($row->utilise_approvisionnement) && isset($row->utilise_commande_carte)) {
                    if ($row->utilise_approvisionnement && !$row->utilise_commande_carte) {
                        $usage = 'appro';
                    } elseif (!$row->utilise_approvisionnement && $row->utilise_commande_carte) {
                        $usage = 'commande_carte';
                    } elseif ($row->utilise_approvisionnement && $row->utilise_commande_carte) {
                        $usage = 'commande_carte'; // les deux cochés → on met commande_carte par défaut
                    }
                }
                DB::table('stock_produits')->where('id', $row->id)->update(['usage' => $usage]);
            }
        });

        Schema::table('stock_produits', function (Blueprint $table) {
            $table->dropColumn(['utilise_approvisionnement', 'utilise_commande_carte']);
        });
    }

    public function down(): void
    {
        Schema::table('stock_produits', function (Blueprint $table) {
            $table->boolean('utilise_approvisionnement')->default(true)->after('observations');
            $table->boolean('utilise_commande_carte')->default(true)->after('utilise_approvisionnement');
        });

        DB::table('stock_produits')->where('usage', 'appro')->update([
            'utilise_approvisionnement' => true,
            'utilise_commande_carte' => false,
        ]);
        DB::table('stock_produits')->where('usage', 'commande_carte')->update([
            'utilise_approvisionnement' => false,
            'utilise_commande_carte' => true,
        ]);

        Schema::table('stock_produits', function (Blueprint $table) {
            $table->dropColumn('usage');
        });
    }
};
