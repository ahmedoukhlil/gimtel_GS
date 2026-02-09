<?php

namespace App\Livewire\Stock\Clients;

use App\Models\Client;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class ListeClients extends Component
{
    use WithPagination;

    public $search = '';
    public $confirmingDeletion = false;
    public $clientToDelete = null;

    protected $queryString = ['search'];

    public function mount(): void
    {
        $user = auth()->user();
        if (!$user || !$user->hasRole('admin')) {
            abort(403, 'Accès réservé à l\'administrateur. Seul l\'administrateur peut paramétrer les clients.');
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function confirmDelete($id): void
    {
        $this->clientToDelete = $id;
        $this->confirmingDeletion = true;
    }

    public function cancelDelete(): void
    {
        $this->confirmingDeletion = false;
        $this->clientToDelete = null;
    }

    public function delete(): void
    {
        $client = Client::find($this->clientToDelete);
        if ($client) {
            $client->delete();
            session()->flash('success', 'Client supprimé avec succès.');
        }
        $this->cancelDelete();
    }

    public function render()
    {
        $clients = Client::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('NomClient', 'like', '%' . $this->search . '%')
                        ->orWhere('contact', 'like', '%' . $this->search . '%')
                        ->orWhere('NomPointFocal', 'like', '%' . $this->search . '%')
                        ->orWhere('NumTel', 'like', '%' . $this->search . '%')
                        ->orWhere('adressmail', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('NomClient')
            ->paginate(15);

        return view('livewire.stock.clients.liste-clients', [
            'clients' => $clients,
        ]);
    }
}
