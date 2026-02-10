<?php

namespace App\Livewire\Production;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\StockReservation;
use App\Models\StockProduit;
use App\Models\Client;

#[Layout('components.layouts.app')]
class GestionReservations extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterClient = '';

    public $showForm = false;
    public $editingId = null;

    public $client_id = '';
    public $produit_id = '';
    public $quantite_reservee = 0;

    protected $queryString = ['search', 'filterClient'];

    protected function rules(): array
    {
        return [
            'client_id' => 'required|exists:clients,id',
            'produit_id' => 'required|exists:stock_produits,id',
            'quantite_reservee' => 'required|integer|min:0',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'client_id' => 'client',
            'produit_id' => 'produit',
            'quantite_reservee' => 'quantité réservée',
        ];
    }

    public function mount(): void
    {
        $user = auth()->user();
        if (!$user || !$user->isDirectionProduction()) {
            abort(403, 'Accès réservé à la direction production.');
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterClient(): void
    {
        $this->resetPage();
    }

    /**
     * Stats résumées pour les cartes en haut.
     */
    public function getStatsProperty(): array
    {
        $reservations = StockReservation::all();
        $totalReserve = $reservations->sum('quantite_reservee');
        $totalClients = $reservations->pluck('client_id')->unique()->count();
        $totalProduits = $reservations->pluck('produit_id')->unique()->count();

        return [
            'total_reservations' => $reservations->count(),
            'total_clients' => $totalClients,
            'total_produits' => $totalProduits,
            'total_reserve' => $totalReserve,
        ];
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->editingId = null;
        $this->showForm = true;
    }

    public function openEdit(int $id): void
    {
        $reservation = StockReservation::findOrFail($id);
        $this->editingId = $id;
        $this->client_id = (string) $reservation->client_id;
        $this->produit_id = (string) $reservation->produit_id;
        $this->quantite_reservee = $reservation->quantite_reservee;
        $this->showForm = true;
    }

    public function cancelForm(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    protected function resetForm(): void
    {
        $this->client_id = '';
        $this->produit_id = '';
        $this->quantite_reservee = 0;
        $this->resetValidation();
    }

    public function save(): void
    {
        $this->validate();

        $clientId = (int) $this->client_id;
        $produitId = (int) $this->produit_id;
        $newQty = (int) $this->quantite_reservee;

        $produit = StockProduit::findOrFail($produitId);
        $existing = StockReservation::where('client_id', $clientId)->where('produit_id', $produitId)->first();
        $oldQty = $existing ? $existing->quantite_reservee : 0;

        // Total déjà réservé pour ce produit (tous clients)
        $totalReserve = (int) StockReservation::where('produit_id', $produitId)->sum('quantite_reservee');
        // Disponible = stock physique - réservé + ancienne qty de cette ligne (on la remplace)
        $disponible = $produit->stock_actuel - $totalReserve + $oldQty;

        if ($newQty > $disponible) {
            session()->flash('error', 'Stock disponible insuffisant pour ce produit. Disponible après réservations : ' . max(0, $disponible) . '.');
            return;
        }

        StockReservation::updateOrCreate(
            [
                'client_id' => $clientId,
                'produit_id' => $produitId,
            ],
            ['quantite_reservee' => $newQty]
        );

        $this->showForm = false;
        $this->resetForm();
        session()->flash('success', 'Réservation enregistrée.');
    }

    public function delete(int $id): void
    {
        $reservation = StockReservation::findOrFail($id);
        $reservation->delete();
        session()->flash('success', 'Réservation supprimée.');
    }

    public function render()
    {
        $query = StockReservation::with(['client', 'produit.categorie'])
            ->orderBy('client_id')
            ->orderBy('produit_id');

        // Recherche
        if ($this->search !== '') {
            $term = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->whereHas('client', fn ($c) => $c->where('NomClient', 'like', $term))
                  ->orWhereHas('produit', fn ($p) => $p->where('libelle', 'like', $term));
            });
        }

        // Filtre par client
        if ($this->filterClient !== '') {
            $query->where('client_id', $this->filterClient);
        }

        $reservations = $query->paginate(15);

        $clients = Client::orderBy('NomClient')->get(['id', 'NomClient']);
        $produits = StockProduit::pourCommandeCarte()->with('categorie')->orderBy('libelle')->get();

        return view('livewire.production.gestion-reservations', [
            'reservations' => $reservations,
            'clients' => $clients,
            'produits' => $produits,
        ]);
    }
}
