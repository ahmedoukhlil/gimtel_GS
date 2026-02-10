<?php

namespace App\Livewire\Client;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\CommandeClient;

#[Layout('components.layouts.app')]
class ClientCommande extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatut = '';

    protected $queryString = ['search', 'filterStatut'];

    public function mount(): void
    {
        $user = auth()->user();
        if (!$user || !$user->isClient()) {
            abort(403, 'Accès réservé aux clients.');
        }
        if (request()->filled('search')) {
            $this->search = (string) request('search');
        }
        if (request()->filled('filterStatut')) {
            $this->filterStatut = (string) request('filterStatut');
        } elseif (request()->filled('statut')) {
            $this->filterStatut = (string) request('statut');
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatut(): void
    {
        $this->resetPage();
    }

    public function getStatsProperty(): array
    {
        $counts = CommandeClient::where('client_id', auth()->id())
            ->selectRaw('statut, count(*) as total')
            ->groupBy('statut')
            ->pluck('total', 'statut')
            ->toArray();

        return [
            'all'                      => array_sum($counts),
            'soumis'                   => $counts['soumis'] ?? 0,
            'en_cours_de_traitement'   => $counts['en_cours_de_traitement'] ?? 0,
            'finalise'                 => $counts['finalise'] ?? 0,
            'livre'                    => $counts['livre'] ?? 0,
            'rejetee'                  => $counts['rejetee'] ?? 0,
        ];
    }

    public function render()
    {
        $query = CommandeClient::where('client_id', auth()->id())
            ->with('produit')
            ->orderBy('created_at', 'desc');

        if ($this->filterStatut !== '') {
            $query->where('statut', $this->filterStatut);
        }

        if ($this->search !== '') {
            $term = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('commande_numero', 'like', $term)
                    ->orWhereHas('produit', fn ($p) => $p->where('libelle', 'like', $term));
            });
        }

        $commandes = $query->paginate(15);

        return view('livewire.client.client-commande', [
            'commandes' => $commandes,
        ]);
    }
}
