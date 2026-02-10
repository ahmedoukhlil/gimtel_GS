<?php

namespace App\Livewire\DemandesAppro;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\DemandeApprovisionnement;

#[Layout('components.layouts.app')]
class ListeDemandes extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatut = '';

    protected $queryString = ['search', 'filterStatut'];

    public function mount(): void
    {
        if (!auth()->user()->isDemandeurInterne()) {
            abort(403, 'Accès réservé aux demandeurs internes.');
        }
        if (request()->filled('statut') && array_key_exists(request('statut'), DemandeApprovisionnement::STATUTS)) {
            $this->filterStatut = request('statut');
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
        $user = auth()->user();
        $counts = DemandeApprovisionnement::query()
            ->where(function ($q) use ($user) {
                $q->where('demandeur_user_id', $user->getAuthIdentifier());
                if ($user->demandeur_id) {
                    $q->orWhere('demandeur_id', $user->demandeur_id);
                }
            })
            ->selectRaw('statut, count(*) as total')
            ->groupBy('statut')
            ->pluck('total', 'statut')
            ->toArray();

        return [
            'all'      => array_sum($counts),
            'soumis'   => $counts['soumis'] ?? 0,
            'en_cours' => $counts['en_cours'] ?? 0,
            'approuve' => $counts['approuve'] ?? 0,
            'rejete'   => $counts['rejete'] ?? 0,
            'servi'    => $counts['servi'] ?? 0,
        ];
    }

    public function render()
    {
        $user = auth()->user();
        $query = DemandeApprovisionnement::query()
            ->with(['demandeurStock', 'lignes'])
            ->where(function ($q) use ($user) {
                $q->where('demandeur_user_id', $user->getAuthIdentifier());
                if ($user->demandeur_id) {
                    $q->orWhere('demandeur_id', $user->demandeur_id);
                }
            })
            ->orderBy('created_at', 'desc');

        if ($this->filterStatut !== '') {
            $query->where('statut', $this->filterStatut);
        }
        if ($this->search !== '') {
            $term = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('numero', 'like', $term)
                    ->orWhereHas('demandeurStock', fn ($d) => $d->where('nom', 'like', $term)->orWhere('poste_service', 'like', $term));
            });
        }

        $demandes = $query->paginate(15);

        return view('livewire.demandes-appro.liste-demandes', [
            'demandes' => $demandes,
        ]);
    }
}
