<?php

namespace App\Livewire\Stock\Sorties;

use App\Models\StockSortie;
use App\Models\StockProduit;
use App\Models\StockDemandeur;
use App\Livewire\Traits\WithCachedOptions;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class FormSortie extends Component
{
    use WithCachedOptions;

    // Champs du formulaire
    public $date_sortie;
    public $produit_id = '';
    public $demandeur_id = '';
    public $quantite = 1;
    public $observations = '';

    // Informations du produit sélectionné
    public $produitSelectionne = null;
    public $stockDisponible = 0;

    public function mount()
    {
        $user = auth()->user();
        if (!$user || !$user->canCreateSortie()) {
            abort(403, 'Accès non autorisé.');
        }

        // Date du jour par défaut
        $this->date_sortie = now()->format('Y-m-d');
    }

    /**
     * Mise à jour du produit sélectionné
     */
    public function updatedProduitId($value)
    {
        if ($value) {
            $this->produitSelectionne = StockProduit::find($value);
            $this->stockDisponible = $this->produitSelectionne->stock_actuel ?? 0;
        } else {
            $this->produitSelectionne = null;
            $this->stockDisponible = 0;
        }
    }

    protected function rules()
    {
        return [
            'date_sortie' => 'required|date',
            'produit_id' => 'required|exists:stock_produits,id',
            'demandeur_id' => 'required|exists:stock_demandeurs,id',
            'quantite' => 'required|integer|min:1|max:' . ($this->stockDisponible > 0 ? $this->stockDisponible : 999999),
            'observations' => 'nullable|string',
        ];
    }

    protected function messages()
    {
        return [
            'date_sortie.required' => 'La date de sortie est obligatoire.',
            'produit_id.required' => 'Le produit est obligatoire.',
            'produit_id.exists' => 'Le produit sélectionné n\'existe pas.',
            'demandeur_id.required' => 'Le demandeur est obligatoire.',
            'demandeur_id.exists' => 'Le demandeur sélectionné n\'existe pas.',
            'quantite.required' => 'La quantité est obligatoire.',
            'quantite.integer' => 'La quantité doit être un nombre entier.',
            'quantite.min' => 'La quantité doit être au moins 1.',
            'quantite.max' => 'Stock insuffisant. Stock disponible : ' . $this->stockDisponible,
        ];
    }

    /**
     * Options pour le select Produits
     */
    public function getProduitOptionsProperty()
    {
        return cache()->remember('stock_produits_options_with_stock', 300, function () {
            return StockProduit::with('categorie')
                ->orderBy('libelle')
                ->get()
                ->map(function ($produit) {
                    $stockInfo = '(Stock: ' . $produit->stock_actuel . ')';
                    $couleur = $produit->en_alerte ? ' 🔴' : ($produit->stock_faible ? ' 🟡' : '');
                    
                    return [
                        'value' => (string)$produit->id,
                        'text' => $produit->libelle . ' ' . $stockInfo . $couleur,
                    ];
                })
                ->toArray();
        });
    }

    /**
     * Options pour le select Demandeurs
     */
    public function getDemandeurOptionsProperty()
    {
        return cache()->remember('stock_demandeurs_options', 300, function () {
            return StockDemandeur::orderBy('nom')
                ->get()
                ->map(function ($demandeur) {
                    return [
                        'value' => (string)$demandeur->id,
                        'text' => $demandeur->nom . ' - ' . $demandeur->poste_service,
                    ];
                })
                ->toArray();
        });
    }

    public function save()
    {
        // Vérifier le stock avant validation
        if ($this->produitSelectionne && $this->quantite > $this->stockDisponible) {
            session()->flash('error', 'Stock insuffisant. Stock disponible : ' . $this->stockDisponible);
            return;
        }

        $validated = $this->validate();
        
        // Ajouter l'utilisateur qui crée la sortie
        $validated['created_by'] = auth()->user()->idUser;

        try {
            // Créer la sortie (le stock sera mis à jour automatiquement via l'event)
            StockSortie::create($validated);

            // Recharger le produit pour vérifier l'alerte
            $produit = StockProduit::find($this->produit_id);
            $message = 'Sortie de stock enregistrée avec succès. Le stock a été mis à jour.';
            
            if ($produit && $produit->en_alerte) {
                $message .= ' ⚠️ ALERTE : Le stock est maintenant en dessous du seuil d\'alerte (' . $produit->stock_actuel . '/' . $produit->seuil_alerte . ').';
            }

            session()->flash('success', $message);
            return redirect()->route('stock.sorties.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Erreur lors de l\'enregistrement : ' . $e->getMessage());
        }
    }

    public function cancel()
    {
        return redirect()->route('stock.sorties.index');
    }

    public function render()
    {
        return view('livewire.stock.sorties.form-sortie');
    }
}
