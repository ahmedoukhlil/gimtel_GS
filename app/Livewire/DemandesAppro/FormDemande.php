<?php

namespace App\Livewire\DemandesAppro;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\DemandeApprovisionnement;
use App\Models\DemandeApprovisionnementLigne;
use App\Models\StockDemandeur;
use App\Models\StockProduit;

#[Layout('components.layouts.app')]
class FormDemande extends Component
{
    public ?int $demandeur_id = null;
    /** @var array<int, array{produit_id: string|int, quantite_demandee: string|int}> */
    public array $lignes = [];

    public function mount(): void
    {
        if (!auth()->user()->isDemandeurInterne()) {
            abort(403, 'Accès réservé aux demandeurs internes.');
        }
        $user = auth()->user();
        if ($user->demandeur_id) {
            $this->demandeur_id = $user->demandeur_id;
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
            'demandeur_id' => $this->demandeur_id,
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

        session()->flash('success', 'Demande ' . $demande->numero . ' créée et soumise.');
        $this->redirect(route('demandes-appro.show', $demande), navigate: true);
    }

    public function render()
    {
        $demandeurs = StockDemandeur::orderBy('nom')->get();
        $produits = StockProduit::pourApprovisionnement()->with(['categorie', 'magasin'])->orderBy('libelle')->get();

        return view('livewire.demandes-appro.form-demande', [
            'demandeurs' => $demandeurs,
            'produits' => $produits,
        ]);
    }
}
