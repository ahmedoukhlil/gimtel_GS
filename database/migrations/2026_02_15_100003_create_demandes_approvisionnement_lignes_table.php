<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demandes_approvisionnement_lignes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('demande_approvisionnement_id');
            $table->unsignedBigInteger('produit_approvisionnement_id');
            $table->unsignedInteger('quantite_demandee');
            $table->unsignedInteger('quantite_accordee')->nullable();
            $table->timestamps();

            $table->foreign('demande_approvisionnement_id', 'da_lignes_demande_fk')->references('id')->on('demandes_approvisionnement')->onDelete('cascade');
            $table->foreign('produit_approvisionnement_id', 'da_lignes_produit_fk')->references('id')->on('produits_approvisionnement')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demandes_approvisionnement_lignes');
    }
};
