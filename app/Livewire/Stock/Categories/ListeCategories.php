<?php

namespace App\Livewire\Stock\Categories;

use App\Models\StockCategorie;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class ListeCategories extends Component
{
    use WithPagination;

    public $search = '';
    public $confirmingDeletion = false;
    public $categorieToDelete = null;

    public string $usage = StockCategorie::USAGE_COMMANDE_CARTE;

    protected $queryString = ['search'];

    public function mount(): void
    {
        $user = auth()->user();
        if (!$user || !$user->hasRole('admin')) {
            abort(403, 'Accès réservé à l\'administrateur. Seul l\'administrateur peut paramétrer les catégories.');
        }
        $this->usage = request()->routeIs('stock.categories-appro.*') ? StockCategorie::USAGE_APPRO : StockCategorie::USAGE_COMMANDE_CARTE;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function confirmDelete($id)
    {
        $this->categorieToDelete = $id;
        $this->confirmingDeletion = true;
    }

    public function cancelDelete()
    {
        $this->confirmingDeletion = false;
        $this->categorieToDelete = null;
    }

    public function delete()
    {
        $categorie = StockCategorie::find($this->categorieToDelete);

        if ($categorie && $categorie->usage !== $this->usage) {
            session()->flash('error', 'Cette catégorie n\'appartient pas à cette liste.');
            $this->cancelDelete();
            return;
        }

        if ($categorie) {
            if ($categorie->produits()->count() > 0) {
                session()->flash('error', 'Impossible de supprimer cette catégorie car elle contient des produits.');
                $this->cancelDelete();
                return;
            }

            $categorie->delete();
            session()->flash('success', 'Catégorie supprimée avec succès.');
        }

        $this->cancelDelete();
    }

    public function render()
    {
        $categories = StockCategorie::query()
            ->where('usage', $this->usage)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('libelle', 'like', '%' . $this->search . '%')
                      ->orWhere('observations', 'like', '%' . $this->search . '%');
                });
            })
            ->withCount('produits')
            ->orderBy('libelle')
            ->paginate(15);

        return view('livewire.stock.categories.liste-categories', [
            'categories' => $categories,
            'usage' => $this->usage,
        ]);
    }
}
