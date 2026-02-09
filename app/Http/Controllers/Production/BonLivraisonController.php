<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use App\Models\CommandeClient;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class BonLivraisonController extends Controller
{
    /**
     * Génère et télécharge le PDF du bon de livraison pour une commande finalisée.
     * La commande doit déjà avoir un numéro de BL (statut finalise ou livre).
     */
    public function __invoke(CommandeClient $order): Response
    {
        $order->load(['client.client', 'produit']);
        if (!$order->bon_livraison_numero) {
            abort(404, 'Bon de livraison non généré pour cette commande.');
        }
        $pdf = Pdf::loadView('pdf.bon-livraison', ['order' => $order]);
        $filename = 'bon-livraison-' . preg_replace('/[^a-z0-9\-]/i', '-', $order->bon_livraison_numero) . '.pdf';
        return $pdf->download($filename);
    }
}
