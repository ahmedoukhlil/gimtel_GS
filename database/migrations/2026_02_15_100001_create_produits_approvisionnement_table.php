<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
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
    }

    public function down(): void
    {
        Schema::dropIfExists('produits_approvisionnement');
    }
};
