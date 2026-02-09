<?php

namespace App\Livewire\Stock\Magasins;

use App\Models\StockMagasin;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class ListeMagasins extends Component
{
    use WithPagination;

    public $search = '';
    public $confirmingDeletion = false;
    public $magasinToDelete = null;

    /** 'appro' | 'commande_carte' selon la route */
    public string $usage = StockMagasin::USAGE_COMMANDE_CARTE;

    protected $queryString = ['search'];

    public function mount(): void
    {
        $user = auth()->user();
        if (!$user || !$user->hasRole('admin')) {
            abort(403, 'Accès réservé à l\'administrateur. Seul l\'administrateur peut paramétrer les magasins.');
        }
        $this->usage = request()->routeIs('stock.magasins-appro.*') ? StockMagasin::USAGE_APPRO : StockMagasin::USAGE_COMMANDE_CARTE;
    }

    /**
     * Reset pagination lors de la recherche
     */
    public function updatingSearch()
    {
        $this->resetPage();
    }

    /**
     * Confirmer la suppression
     */
    public function confirmDelete($id)
    {
        $this->magasinToDelete = $id;
        $this->confirmingDeletion = true;
    }

    /**
     * Annuler la suppression
     */
    public function cancelDelete()
    {
        $this->confirmingDeletion = false;
        $this->magasinToDelete = null;
    }

    /**
     * Supprimer le magasin
     */
    public function delete()
    {
        $magasin = StockMagasin::find($this->magasinToDelete);

        if ($magasin && $magasin->usage !== $this->usage) {
            session()->flash('error', 'Ce magasin n\'appartient pas à cette liste.');
            $this->cancelDelete();
            return;
        }

        if ($magasin) {
            // Vérifier si le magasin a des produits
            if ($magasin->produits()->count() > 0) {
                session()->flash('error', 'Impossible de supprimer ce magasin car il contient des produits.');
                $this->cancelDelete();
                return;
            }

            $magasin->delete();
            session()->flash('success', 'Magasin supprimé avec succès.');
        }

        $this->cancelDelete();
    }

    public function render()
    {
        $magasins = StockMagasin::query()
            ->where('usage', $this->usage)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('magasin', 'like', '%' . $this->search . '%')
                      ->orWhere('localisation', 'like', '%' . $this->search . '%')
                      ->orWhere('observations', 'like', '%' . $this->search . '%');
                });
            })
            ->withCount('produits')
            ->orderBy('magasin')
            ->paginate(15);

        return view('livewire.stock.magasins.liste-magasins', [
            'magasins' => $magasins,
            'usage' => $this->usage,
        ]);
    }
}
