<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('demandes_approvisionnement', function (Blueprint $table) {
            $table->foreignId('demandeur_id')->nullable()->after('service_id')->constrained('stock_demandeurs')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('demandes_approvisionnement', function (Blueprint $table) {
            $table->dropForeign(['demandeur_id']);
        });
    }
};
