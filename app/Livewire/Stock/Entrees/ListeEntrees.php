<?php

namespace App\Livewire\Stock\Entrees;

use App\Models\StockEntree;
use App\Models\StockProduit;
use App\Models\StockFournisseur;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class ListeEntrees extends Component
{
    use WithPagination;

    public $search = '';
    public $filterProduit = '';
    public $filterFournisseur = '';
    public $dateDebut = '';
    public $dateFin = '';
    /** Type d'entrées : 'commande_carte' ou 'appro' */
    public string $usage = '';

    protected $queryString = ['search', 'filterProduit', 'filterFournisseur', 'dateDebut', 'dateFin', 'usage'];

    public function mount()
    {
        $user = auth()->user();
        if (!$user || !$user->canCreateEntree()) {
            abort(403, 'Accès non autorisé.');
        }

        // Si le paramètre usage est passé en query string, on l'utilise ;
        // sinon, on détermine en fonction du rôle.
        if (empty($this->usage) || !in_array($this->usage, [StockProduit::USAGE_COMMANDE_CARTE, StockProduit::USAGE_APPRO], true)) {
            $this->usage = $user->isDirectionProduction()
                ? StockProduit::USAGE_COMMANDE_CARTE
                : StockProduit::USAGE_APPRO;
        }

        // Dates par défaut : dernier mois
        if (empty($this->dateDebut)) {
            $this->dateDebut = now()->subMonth()->format('Y-m-d');
        }
        if (empty($this->dateFin)) {
            $this->dateFin = now()->format('Y-m-d');
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterProduit()
    {
        $this->resetPage();
    }

    public function updatingFilterFournisseur()
    {
        $this->resetPage();
    }

    /**
     * Statistiques résumées pour les cartes.
     */
    public function getStatsProperty(): array
    {
        $usage = $this->usage;

        $baseQuery = StockEntree::query()
            ->whereHas('produit', fn ($q) => $q->where('usage', $usage))
            ->when($this->dateDebut, fn($q) => $q->where('date_entree', '>=', $this->dateDebut))
            ->when($this->dateFin, fn($q) => $q->where('date_entree', '<=', $this->dateFin));

        $totalQuantite = (clone $baseQuery)->sum('quantite');
        $totalEntrees = (clone $baseQuery)->count();
        $totalProduits = (clone $baseQuery)->distinct('produit_id')->count('produit_id');
        $totalFournisseurs = (clone $baseQuery)->distinct('fournisseur_id')->count('fournisseur_id');

        return [
            'total_quantite' => $totalQuantite,
            'total_entrees' => $totalEntrees,
            'total_produits' => $totalProduits,
            'total_fournisseurs' => $totalFournisseurs,
        ];
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->filterProduit = '';
        $this->filterFournisseur = '';
        $this->dateDebut = now()->subMonth()->format('Y-m-d');
        $this->dateFin = now()->format('Y-m-d');
        $this->resetPage();
    }

    public function render()
    {
        $usage = $this->usage;

        $entrees = StockEntree::query()
            ->whereHas('produit', fn ($q) => $q->where('usage', $usage))
            ->with(['produit.categorie', 'fournisseur', 'createur'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('reference_commande', 'like', '%' . $this->search . '%')
                      ->orWhere('observations', 'like', '%' . $this->search . '%')
                      ->orWhereHas('produit', function ($pq) {
                          $pq->where('libelle', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->when($this->filterProduit, function ($query) {
                $query->where('produit_id', $this->filterProduit);
            })
            ->when($this->filterFournisseur, function ($query) {
                $query->where('fournisseur_id', $this->filterFournisseur);
            })
            ->when($this->dateDebut, function ($query) {
                $query->where('date_entree', '>=', $this->dateDebut);
            })
            ->when($this->dateFin, function ($query) {
                $query->where('date_entree', '<=', $this->dateFin);
            })
            ->orderBy('date_entree', 'desc')
            ->paginate(20);

        $produits = StockProduit::where('usage', $usage)->orderBy('libelle')->get();
        $fournisseurs = StockFournisseur::where('usage', $usage === StockProduit::USAGE_COMMANDE_CARTE ? StockFournisseur::USAGE_COMMANDE_CARTE : StockFournisseur::USAGE_APPRO)->orderBy('libelle')->get();

        return view('livewire.stock.entrees.liste-entrees', [
            'entrees' => $entrees,
            'produits' => $produits,
            'fournisseurs' => $fournisseurs,
            'usageEntrees' => $usage,
        ]);
    }
}
