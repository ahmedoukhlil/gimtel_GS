<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('demandes_approvisionnement_lignes', function (Blueprint $table) {
            $table->unsignedBigInteger('produit_id')->nullable()->after('demande_approvisionnement_id');
        });

        // Migrer les données : associer chaque ligne à un stock_produit par libellé (premier produit trouvé avec le même libellé)
        if (Schema::hasTable('produits_approvisionnement')) {
            $lignes = DB::table('demandes_approvisionnement_lignes')->whereNotNull('produit_approvisionnement_id')->get();
            foreach ($lignes as $ligne) {
                $pa = DB::table('produits_approvisionnement')->find($ligne->produit_approvisionnement_id);
                if ($pa) {
                    $sp = DB::table('stock_produits')->where('libelle', $pa->libelle)->first();
                    if ($sp) {
                        DB::table('demandes_approvisionnement_lignes')
                            ->where('id', $ligne->id)
                            ->update(['produit_id' => $sp->id]);
                    }
                }
            }
        }

        Schema::table('demandes_approvisionnement_lignes', function (Blueprint $table) {
            $table->dropForeign('da_lignes_produit_fk');
            $table->dropColumn('produit_approvisionnement_id');
        });

        Schema::table('demandes_approvisionnement_lignes', function (Blueprint $table) {
            $table->foreign('produit_id', 'da_lignes_produit_fk')->references('id')->on('stock_produits')->onDelete('restrict');
        });

        Schema::dropIfExists('produits_approvisionnement');
    }

    public function down(): void
    {
        Schema::create('produits_approvisionnement', function (Blueprint $table) {
            $table->id();
            $table->string('libelle');
            $table->string('reference')->nullable();
            $table->string('unite')->default('unité');
            $table->string('categorie')->nullable();
            $table->text('description')->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });

        Schema::table('demandes_approvisionnement_lignes', function (Blueprint $table) {
            $table->dropForeign('da_lignes_produit_fk');
            $table->unsignedBigInteger('produit_approvisionnement_id')->nullable()->after('demande_approvisionnement_id');
        });

        $lignes = DB::table('demandes_approvisionnement_lignes')->whereNotNull('produit_id')->get();
        foreach ($lignes as $ligne) {
            $sp = DB::table('stock_produits')->find($ligne->produit_id);
            if ($sp) {
                $pa = DB::table('produits_approvisionnement')->where('libelle', $sp->libelle)->first();
                if (!$pa) {
                    $paId = DB::table('produits_approvisionnement')->insertGetId([
                        'libelle' => $sp->libelle,
                        'reference' => null,
                        'unite' => 'unité',
                        'categorie' => null,
                        'description' => $sp->descriptif,
                        'actif' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $pa = (object) ['id' => $paId];
                }
                DB::table('demandes_approvisionnement_lignes')->where('id', $ligne->id)->update(['produit_approvisionnement_id' => $pa->id]);
            }
        }

        Schema::table('demandes_approvisionnement_lignes', function (Blueprint $table) {
            $table->dropColumn('produit_id');
            $table->foreign('produit_approvisionnement_id', 'da_lignes_produit_fk')->references('id')->on('produits_approvisionnement')->onDelete('restrict');
        });
    }
};
