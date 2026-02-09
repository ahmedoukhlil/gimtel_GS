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
        Schema::create('commandes_clients', function (Blueprint $table) {
            $table->id();
            $table->integer('client_id'); // Relation avec users.idUser
            $table->foreignId('produit_id')->constrained('stock_produits')->onDelete('cascade');
            $table->integer('quantite');
            $table->string('statut')->default('en_attente'); // en_attente, validee, rejetee
            $table->text('motif_rejet')->nullable();
            $table->timestamps();

            // Clé étrangère manuelle car la table users utilise idUser (INT)
            $table->foreign('client_id')->references('idUser')->on('users')->onDelete('cascade');

            // Index
            $table->index('client_id');
            $table->index('statut');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commandes_clients');
    }
};
