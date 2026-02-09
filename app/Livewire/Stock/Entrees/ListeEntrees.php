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

    protected $queryString = ['search', 'filterProduit', 'filterFournisseur', 'dateDebut', 'dateFin'];

    public function mount()
    {
        $user = auth()->user();
        if (!$user || !$user->canCreateEntree()) {
            abort(403, 'Accès non autorisé.');
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

    public function render()
    {
        $user = auth()->user();
        // Direction Production : entrées commandes/cartes ; Admin / Admin stock : entrées appro
        $usage = $user->isDirectionProduction()
            ? StockProduit::USAGE_COMMANDE_CARTE
            : StockProduit::USAGE_APPRO;

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

        $totalQuantite = StockEntree::query()
            ->whereHas('produit', fn ($q) => $q->where('usage', $usage))
            ->when($this->dateDebut, fn($q) => $q->where('date_entree', '>=', $this->dateDebut))
            ->when($this->dateFin, fn($q) => $q->where('date_entree', '<=', $this->dateFin))
            ->sum('quantite');

        return view('livewire.stock.entrees.liste-entrees', [
            'entrees' => $entrees,
            'produits' => $produits,
            'fournisseurs' => $fournisseurs,
            'totalQuantite' => $totalQuantite,
            'usageEntrees' => $usage,
        ]);
    }
}
