<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Conformément à la conception stock / réservations :
     * stock_actuel = stock physique uniquement (entrées - sorties).
     * Les réservations ne doivent plus être déduites de stock_actuel.
     * Cette migration réintègre les quantités réservées dans stock_actuel
     * (annule les déductions faites auparavant par l’appli).
     */
    public function up(): void
    {
        $reserves = DB::table('stock_reservations')
            ->selectRaw('produit_id, SUM(quantite_reservee) as total')
            ->groupBy('produit_id')
            ->get();

        foreach ($reserves as $row) {
            DB::table('stock_produits')
                ->where('id', $row->produit_id)
                ->increment('stock_actuel', (int) $row->total);
        }
    }

    /**
     * Reverse : re-déduire les réservations du stock_actuel (ancien comportement).
     */
    public function down(): void
    {
        $reserves = DB::table('stock_reservations')
            ->selectRaw('produit_id, SUM(quantite_reservee) as total')
            ->groupBy('produit_id')
            ->get();

        foreach ($reserves as $row) {
            DB::table('stock_produits')
                ->where('id', $row->produit_id)
                ->decrement('stock_actuel', (int) $row->total);
        }
    }
};
