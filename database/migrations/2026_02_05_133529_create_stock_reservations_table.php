<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stock_reservations', function (Blueprint $table) {
            $table->id();
            $table->integer('client_id'); // Relation avec users.idUser
            $table->foreignId('produit_id')->constrained('stock_produits')->onDelete('cascade');
            $table->integer('quantite_reservee')->default(0);
            $table->timestamps();

            // Clé étrangère manuelle car la table users utilise idUser (INT)
            $table->foreign('client_id')->references('idUser')->on('users')->onDelete('cascade');

            // Index pour la performance
            $table->index(['client_id', 'produit_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_reservations');
    }
};
