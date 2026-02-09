<?php

namespace App\Livewire\Dmg;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\DemandeApprovisionnement;
use App\Models\DemandeApprovisionnementLigne;
use App\Models\StockDemandeur;
use App\Models\StockProduit;

#[Layout('components.layouts.app')]
class CreerDemandePourDemandeur extends Component
{
    public ?int $demandeur_id = null;
    /** @var array<int, array{produit_id: string|int, quantite_demandee: string|int}> */
    public array $lignes = [];

    public function mount(): void
    {
        if (!auth()->user()->isDirectionMoyensGeneraux()) {
            abort(403, 'Accès réservé à la direction des moyens généraux.');
        }
        $this->lignes = [['produit_id' => '', 'quantite_demandee' => 1]];
    }

    public function addLigne(): void
    {
        $this->lignes[] = ['produit_id' => '', 'quantite_demandee' => 1];
    }

    public function removeLigne(int $index): void
    {
        if (count($this->lignes) <= 1) {
            return;
        }
        array_splice($this->lignes, $index, 1);
    }

    /**
     * Options pour la liste déroulante searchable (demandeurs).
     */
    public function getDemandeurOptionsProperty(): array
    {
        return StockDemandeur::orderBy('nom')->get()->map(fn ($d) => [
            'value' => $d->id,
            'text' => $d->nom . ' (' . ($d->poste_service ?? '') . ')',
        ])->values()->toArray();
    }

    /**
     * Options pour la liste déroulante searchable (produits appro).
     */
    public function getProduitOptionsProperty(): array
    {
        return StockProduit::pourApprovisionnement()
            ->with(['categorie'])
            ->orderBy('libelle')
            ->get()
            ->map(fn ($p) => [
                'value' => $p->id,
                'text' => $p->libelle . ($p->categorie ? ' [' . $p->categorie->libelle . ']' : ''),
            ])
            ->values()
            ->toArray();
    }

    public function submit(): void
    {
        $this->validate([
            'demandeur_id' => 'required|exists:stock_demandeurs,id',
            'lignes' => 'required|array|min:1',
            'lignes.*.produit_id' => 'required|exists:stock_produits,id',
            'lignes.*.quantite_demandee' => 'required|integer|min:1',
        ], [
            'demandeur_id.required' => 'Veuillez sélectionner un demandeur.',
            'lignes.*.produit_id.required' => 'Sélectionnez un produit.',
            'lignes.*.quantite_demandee.required' => 'La quantité est requise.',
            'lignes.*.quantite_demandee.min' => 'La quantité doit être au moins 1.',
        ]);

        $lignesValides = array_filter($this->lignes, fn ($l) => !empty($l['produit_id']) && ((int) ($l['quantite_demandee'] ?? 0)) > 0);
        if (empty($lignesValides)) {
            $this->addError('lignes', 'Ajoutez au moins une ligne avec un produit et une quantité.');
            return;
        }

        $demande = DemandeApprovisionnement::create([
            'numero' => DemandeApprovisionnement::genererNumero(),
            'demandeur_id' => (int) $this->demandeur_id,
            'demandeur_user_id' => auth()->id(),
            'statut' => 'soumis',
        ]);

        foreach ($lignesValides as $l) {
            DemandeApprovisionnementLigne::create([
                'demande_approvisionnement_id' => $demande->id,
                'produit_id' => (int) $l['produit_id'],
                'quantite_demandee' => (int) $l['quantite_demandee'],
            ]);
        }

        session()->flash('success', 'Demande ' . $demande->numero . ' créée pour le demandeur (soumise).');
        $this->redirect(route('dmg.demandes.show', $demande), navigate: true);
    }

    public function render()
    {
        return view('livewire.dmg.creer-demande-pour-demandeur');
    }
}
