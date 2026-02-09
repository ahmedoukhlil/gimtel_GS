<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Historique des changements de statut pour traçabilité (date/heure de chaque statut).
     */
    public function up(): void
    {
        Schema::create('commande_client_statut_historique', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commande_client_id')->constrained('commandes_clients')->onDelete('cascade');
            $table->string('statut', 50);
            $table->timestamps();

            $table->index(['commande_client_id', 'statut'], 'cc_statut_hist_commande_statut_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commande_client_statut_historique');
    }
};
