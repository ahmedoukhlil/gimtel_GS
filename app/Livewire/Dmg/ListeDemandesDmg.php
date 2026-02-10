<?php

namespace App\Livewire\Dmg;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\DemandeApprovisionnement;
use App\Models\Service;

#[Layout('components.layouts.app')]
class ListeDemandesDmg extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatut = '';
    public ?int $filterServiceId = null;
    public ?int $filterDemandeurId = null;

    protected $queryString = ['search', 'filterStatut', 'filterDemandeurId'];

    public function mount(): void
    {
        if (!auth()->user()->isDirectionMoyensGeneraux()) {
            abort(403, 'Accès réservé à la direction des moyens généraux.');
        }
        if (request()->filled('statut') && array_key_exists(request('statut'), DemandeApprovisionnement::STATUTS)) {
            $this->filterStatut = request('statut');
        }
        if (request()->filled('service_id')) {
            $this->filterServiceId = (int) request('service_id');
        }
        if (request()->filled('demandeur_id')) {
            $this->filterDemandeurId = (int) request('demandeur_id');
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

    public function updatingFilterDemandeurId(): void
    {
        $this->resetPage();
    }

    public function getStatsProperty(): array
    {
        $counts = DemandeApprovisionnement::selectRaw('statut, count(*) as total')
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
        $query = DemandeApprovisionnement::query()
            ->with(['demandeurStock', 'service', 'lignes', 'demandeur'])
            ->orderByRaw("CASE WHEN statut IN ('soumis', 'en_cours') THEN 0 ELSE 1 END")
            ->orderBy('created_at', 'desc');

        if ($this->filterStatut !== '') {
            $query->where('statut', $this->filterStatut);
        }
        if ($this->filterServiceId !== null) {
            $query->where('service_id', $this->filterServiceId);
        }
        if ($this->filterDemandeurId !== null) {
            $query->where('demandeur_id', $this->filterDemandeurId);
        }
        if ($this->search !== '') {
            $term = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('numero', 'like', $term)
                    ->orWhereHas('demandeurStock', fn ($d) => $d->where('nom', 'like', $term)->orWhere('poste_service', 'like', $term))
                    ->orWhereHas('service', fn ($s) => $s->where('nom', 'like', $term));
            });
        }

        $demandes = $query->paginate(15);
        $demandeurs = \App\Models\StockDemandeur::orderBy('nom')->get();

        return view('livewire.dmg.liste-demandes-dmg', [
            'demandes' => $demandes,
            'demandeurs' => $demandeurs,
        ]);
    }
}
