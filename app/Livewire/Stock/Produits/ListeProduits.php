<?php

namespace App\Livewire\Stock\Produits;

use App\Models\StockProduit;
use App\Models\StockCategorie;
use App\Models\StockMagasin;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class ListeProduits extends Component
{
    use WithPagination;

    public $search = '';
    public $filterCategorie = '';
    public $filterMagasin = '';
    public $filterStatut = ''; // alerte, faible, suffisant, tous
    public $confirmingDeletion = false;
    public $produitToDelete = null;

    /** 'appro' | 'commande_carte' : défini selon la route (produits-appro vs produits) */
    public string $usage = StockProduit::USAGE_COMMANDE_CARTE;

    protected $queryString = ['search', 'filterCategorie', 'filterMagasin', 'filterStatut'];

    public function mount(): void
    {
        if (request()->routeIs('stock.produits-appro.*')) {
            $this->usage = StockProduit::USAGE_APPRO;
        } else {
            $this->usage = StockProduit::USAGE_COMMANDE_CARTE;
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterCategorie()
    {
        $this->resetPage();
    }

    public function updatingFilterMagasin()
    {
        $this->resetPage();
    }

    public function updatingFilterStatut()
    {
        $this->resetPage();
    }

    public function confirmDelete($id)
    {
        $user = auth()->user();
        if (!$user || !$user->hasRole('admin')) {
            session()->flash('error', 'Seul l\'administrateur peut supprimer des produits.');
            return;
        }

        $this->produitToDelete = $id;
        $this->confirmingDeletion = true;
    }

    public function cancelDelete()
    {
        $this->confirmingDeletion = false;
        $this->produitToDelete = null;
    }

    public function delete()
    {
        $user = auth()->user();
        if (!$user || !$user->hasRole('admin')) {
            session()->flash('error', 'Seul l\'administrateur peut supprimer des produits.');
            $this->cancelDelete();
            return;
        }

        $produit = StockProduit::find($this->produitToDelete);

        if ($produit && $produit->usage !== $this->usage) {
            session()->flash('error', 'Ce produit n\'appartient pas à cette liste.');
            $this->cancelDelete();
            return;
        }

        if ($produit) {
            if ($produit->entrees()->count() > 0 || $produit->sorties()->count() > 0) {
                session()->flash('error', 'Impossible de supprimer ce produit car il a des mouvements de stock associés.');
                $this->cancelDelete();
                return;
            }

            $produit->delete();
            session()->flash('success', 'Produit supprimé avec succès.');
        }

        $this->cancelDelete();
    }

    public function render()
    {
        $produits = StockProduit::query()
            ->where('usage', $this->usage)
            ->with(['categorie', 'magasin'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('libelle', 'like', '%' . $this->search . '%')
                      ->orWhere('descriptif', 'like', '%' . $this->search . '%')
                      ->orWhere('stockage', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filterCategorie, function ($query) {
                $query->where('categorie_id', $this->filterCategorie);
            })
            ->when($this->filterMagasin, function ($query) {
                $query->where('magasin_id', $this->filterMagasin);
            })
            ->when($this->filterStatut, function ($query) {
                if ($this->filterStatut === 'alerte') {
                    $query->whereColumn('stock_actuel', '<=', 'seuil_alerte');
                } elseif ($this->filterStatut === 'faible') {
                    $query->whereColumn('stock_actuel', '>', 'seuil_alerte')
                          ->whereRaw('stock_actuel <= seuil_alerte * 1.5');
                } elseif ($this->filterStatut === 'suffisant') {
                    $query->whereRaw('stock_actuel > seuil_alerte * 1.5');
                }
            })
            ->orderBy('libelle')
            ->paginate(20);

        $categories = StockCategorie::where('usage', $this->usage)->orderBy('libelle')->get();
        $magasins = StockMagasin::where('usage', $this->usage)->orderBy('magasin')->get();

        return view('livewire.stock.produits.liste-produits', [
            'produits' => $produits,
            'categories' => $categories,
            'magasins' => $magasins,
            'usage' => $this->usage,
        ]);
    }
}
