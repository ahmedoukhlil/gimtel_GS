<?php

namespace App\Livewire\Stock\Sorties;

use App\Models\StockSortie;
use App\Models\StockProduit;
use App\Models\StockDemandeur;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class ListeSorties extends Component
{
    use WithPagination;

    public $search = '';
    public $filterProduit = '';
    public $filterDemandeur = '';
    public $dateDebut = '';
    public $dateFin = '';

    protected $queryString = ['search', 'filterProduit', 'filterDemandeur', 'dateDebut', 'dateFin'];

    public function mount()
    {
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
        $query = StockSortie::query()
            ->with(['produit.categorie', 'demandeur', 'createur'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('observations', 'like', '%' . $this->search . '%')
                      ->orWhereHas('produit', function ($pq) {
                          $pq->where('libelle', 'like', '%' . $this->search . '%');
                      })
                      ->orWhereHas('demandeur', function ($dq) {
                          $dq->where('nom', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->when($this->filterProduit, function ($query) {
                $query->where('produit_id', $this->filterProduit);
            })
            ->when($this->filterDemandeur, function ($query) {
                $query->where('demandeur_id', $this->filterDemandeur);
            })
            ->when($this->dateDebut, function ($query) {
                $query->where('date_sortie', '>=', $this->dateDebut);
            })
            ->when($this->dateFin, function ($query) {
                $query->where('date_sortie', '<=', $this->dateFin);
            });

        // Si agent, voir seulement ses propres sorties
        $user = auth()->user();
        if ($user && !$user->canViewAllMovements()) {
            $query->where('created_by', $user->idUser);
        }

        $sorties = $query->orderBy('date_sortie', 'desc')->paginate(20);

        $produits = StockProduit::orderBy('libelle')->get();
        $demandeurs = StockDemandeur::orderBy('nom')->get();

        $totalQuantite = StockSortie::query()
            ->when($this->dateDebut, fn($q) => $q->where('date_sortie', '>=', $this->dateDebut))
            ->when($this->dateFin, fn($q) => $q->where('date_sortie', '<=', $this->dateFin))
            ->when($user && !$user->canViewAllMovements(), fn($q) => $q->where('created_by', $user->idUser))
            ->sum('quantite');

        return view('livewire.stock.sorties.liste-sorties', [
            'sorties' => $sorties,
            'produits' => $produits,
            'demandeurs' => $demandeurs,
            'totalQuantite' => $totalQuantite,
        ]);
    }
}
