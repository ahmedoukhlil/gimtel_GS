<?php

namespace App\Livewire\Stock\Demandeurs;

use App\Models\StockDemandeur;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class ListeDemandeurs extends Component
{
    use WithPagination;

    public $search = '';
    public $confirmingDeletion = false;
    public $demandeurToDelete = null;

    protected $queryString = ['search'];

    public function mount()
    {
        $user = auth()->user();
        if (!$user || !$user->canManageStock()) {
            abort(403, 'Accès non autorisé.');
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function confirmDelete($id)
    {
        $this->demandeurToDelete = $id;
        $this->confirmingDeletion = true;
    }

    public function cancelDelete()
    {
        $this->confirmingDeletion = false;
        $this->demandeurToDelete = null;
    }

    public function delete()
    {
        $demandeur = StockDemandeur::find($this->demandeurToDelete);

        if ($demandeur) {
            if ($demandeur->sorties()->count() > 0) {
                session()->flash('error', 'Impossible de supprimer ce demandeur car il a des sorties de stock associées.');
                $this->cancelDelete();
                return;
            }

            $demandeur->delete();
            session()->flash('success', 'Demandeur supprimé avec succès.');
        }

        $this->cancelDelete();
    }

    public function render()
    {
        $demandeurs = StockDemandeur::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('nom', 'like', '%' . $this->search . '%')
                      ->orWhere('poste_service', 'like', '%' . $this->search . '%');
                });
            })
            ->withCount('sorties')
            ->orderBy('nom')
            ->paginate(15);

        return view('livewire.stock.demandeurs.liste-demandeurs', [
            'demandeurs' => $demandeurs,
        ]);
    }
}
