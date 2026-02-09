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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('NomClient');
            $table->string('contact')->nullable();
            $table->string('NomPointFocal')->nullable();
            $table->string('NumTel')->nullable();
            $table->string('adressmail')->nullable();
            $table->timestamps();

            $table->index('NomClient');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
