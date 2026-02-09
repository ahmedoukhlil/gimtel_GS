<?php

namespace App\Livewire\Stock\Services;

use App\Models\Service;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class ListeServices extends Component
{
    use WithPagination;

    public string $search = '';

    public function mount(): void
    {
        if (!auth()->user()->hasAnyRole(['admin', 'admin_stock'])) {
            abort(403, 'Accès réservé.');
        }
    }

    public function render()
    {
        $services = Service::query()
            ->when($this->search, fn ($q) => $q->where('nom', 'like', '%' . $this->search . '%')
                ->orWhere('code', 'like', '%' . $this->search . '%'))
            ->orderBy('nom')
            ->paginate(15);

        return view('livewire.stock.services.liste-services', ['services' => $services]);
    }
}
