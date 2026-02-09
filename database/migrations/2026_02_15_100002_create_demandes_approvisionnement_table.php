<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demandes_approvisionnement', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique();
            $table->foreignId('service_id')->constrained('services')->onDelete('restrict');
            $table->integer('demandeur_user_id')->nullable();
            $table->string('statut')->default('soumis');
            $table->text('motif_rejet')->nullable();
            $table->timestamp('date_traitement')->nullable();
            $table->integer('traite_par_user_id')->nullable();
            $table->text('commentaire_dmg')->nullable();
            $table->timestamps();

            $table->foreign('demandeur_user_id')->references('idUser')->on('users')->onDelete('set null');
            $table->foreign('traite_par_user_id')->references('idUser')->on('users')->onDelete('set null');
            $table->index('statut');
            $table->index('service_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demandes_approvisionnement');
    }
};
