<?php

namespace App\Livewire\Stock;

use App\Models\DemandeApprovisionnement;
use App\Models\StockProduit;
use App\Models\StockEntree;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class DashboardAppro extends Component
{
    public function mount(): void
    {
        $user = auth()->user();
        if (!$user) {
            abort(403, 'Accès réservé.');
        }
        if ($user->hasAnyRole(['admin', 'admin_stock', 'direction_moyens_generaux']) || $user->isDemandeurInterne()) {
            return;
        }
        abort(403, 'Accès réservé.');
    }

    public function render()
    {
        $statsDemandes = [
            'soumis' => DemandeApprovisionnement::where('statut', 'soumis')->count(),
            'en_cours' => DemandeApprovisionnement::where('statut', 'en_cours')->count(),
            'approuve' => DemandeApprovisionnement::where('statut', 'approuve')->count(),
            'rejete' => DemandeApprovisionnement::where('statut', 'rejete')->count(),
            'servi' => DemandeApprovisionnement::where('statut', 'servi')->count(),
        ];
        $statsDemandes['total'] = array_sum($statsDemandes);

        $produitsApproCount = StockProduit::where('usage', StockProduit::USAGE_APPRO)->count();
        $produitsAlerteAppro = StockProduit::where('usage', StockProduit::USAGE_APPRO)
            ->whereColumn('stock_actuel', '<=', 'seuil_alerte')
            ->count();

        $debutMois = now()->startOfMonth();
        $finMois = now()->endOfMonth();
        $entreesMois = StockEntree::whereBetween('date_entree', [$debutMois, $finMois])
            ->whereHas('produit', fn ($q) => $q->where('usage', StockProduit::USAGE_APPRO))
            ->sum('quantite');

        return view('livewire.stock.dashboard-appro', [
            'statsDemandes' => $statsDemandes,
            'produitsApproCount' => $produitsApproCount,
            'produitsAlerteAppro' => $produitsAlerteAppro,
            'entreesMois' => $entreesMois,
        ]);
    }
}
