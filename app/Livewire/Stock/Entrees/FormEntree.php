<?php

namespace App\Livewire\Stock\Entrees;

use App\Models\StockEntree;
use App\Models\StockProduit;
use App\Models\StockFournisseur;
use App\Livewire\Traits\WithCachedOptions;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class FormEntree extends Component
{
    use WithCachedOptions;

    // Champs du formulaire
    public $date_entree;
    public $reference_commande = '';
    public $produit_id = '';
    public $fournisseur_id = '';
    public $quantite = 1;
    public $observations = '';
    /** Type d'entrées : 'commande_carte' ou 'appro' */
    public string $usage = '';

    public function mount()
    {
        $user = auth()->user();
        if (!$user || !$user->canCreateEntree()) {
            abort(403, 'Accès non autorisé. Seuls les administrateurs peuvent créer des entrées.');
        }

        // Si le paramètre usage est passé en query string, on l'utilise ;
        // sinon, on détermine en fonction du rôle.
        if (empty($this->usage) || !in_array($this->usage, [StockProduit::USAGE_COMMANDE_CARTE, StockProduit::USAGE_APPRO], true)) {
            $this->usage = $user->isDirectionProduction()
                ? StockProduit::USAGE_COMMANDE_CARTE
                : StockProduit::USAGE_APPRO;
        }

        // Date du jour par défaut
        $this->date_entree = now()->format('Y-m-d');
    }

    protected function usageEntrees(): string
    {
        return $this->usage;
    }

    protected function rules()
    {
        $usage = $this->usageEntrees();
        $usageFournisseur = $usage === StockProduit::USAGE_COMMANDE_CARTE ? StockFournisseur::USAGE_COMMANDE_CARTE : StockFournisseur::USAGE_APPRO;
        return [
            'date_entree' => 'required|date',
            'reference_commande' => 'nullable|string|max:255',
            'produit_id' => ['required', Rule::exists('stock_produits', 'id')->where('usage', $usage)],
            'fournisseur_id' => ['required', Rule::exists('stock_fournisseurs', 'id')->where('usage', $usageFournisseur)],
            'quantite' => 'required|integer|min:1',
            'observations' => 'nullable|string',
        ];
    }

    protected function messages()
    {
        return [
            'date_entree.required' => 'La date d\'entrée est obligatoire.',
            'produit_id.required' => 'Le produit est obligatoire.',
            'produit_id.exists' => 'Le produit sélectionné n\'existe pas.',
            'fournisseur_id.required' => 'Le fournisseur est obligatoire.',
            'fournisseur_id.exists' => 'Le fournisseur sélectionné n\'existe pas.',
            'quantite.required' => 'La quantité est obligatoire.',
            'quantite.integer' => 'La quantité doit être un nombre entier.',
            'quantite.min' => 'La quantité doit être au moins 1.',
        ];
    }

    /**
     * Propriété calculée : Produit sélectionné pour affichage
     */
    public function getProduitSelectionneProperty()
    {
        if (empty($this->produit_id)) {
            return null;
        }

        return StockProduit::with(['categorie', 'magasin'])->find($this->produit_id);
    }

    /**
     * Options pour le select Produits (selon usage : commande_carte pour Direction Production, appro sinon)
     */
    public function getProduitOptionsProperty()
    {
        $usage = $this->usageEntrees();
        $cacheKey = 'stock_produits_' . $usage . '_options';
        return cache()->remember($cacheKey, 300, function () use ($usage) {
            return StockProduit::where('usage', $usage)
                ->with('categorie')
                ->orderBy('libelle')
                ->get()
                ->map(function ($produit) {
                    return [
                        'value' => (string)$produit->id,
                        'text' => $produit->libelle . ' [' . ($produit->categorie->libelle ?? 'Sans catégorie') . ']',
                    ];
                })
                ->toArray();
        });
    }

    /**
     * Options pour le select Fournisseurs (même usage que les produits)
     */
    public function getFournisseurOptionsProperty()
    {
        $usage = $this->usageEntrees();
        $usageFournisseur = $usage === StockProduit::USAGE_COMMANDE_CARTE ? StockFournisseur::USAGE_COMMANDE_CARTE : StockFournisseur::USAGE_APPRO;
        $cacheKey = 'stock_fournisseurs_' . $usageFournisseur . '_options';
        return cache()->remember($cacheKey, 300, function () use ($usageFournisseur) {
            return StockFournisseur::where('usage', $usageFournisseur)
                ->orderBy('libelle')
                ->get()
                ->map(function ($fournisseur) {
                    return [
                        'value' => (string)$fournisseur->id,
                        'text' => $fournisseur->libelle,
                    ];
                })
                ->toArray();
        });
    }

    public function save()
    {
        $validated = $this->validate();
        
        // Ajouter l'utilisateur qui crée l'entrée
        $validated['created_by'] = auth()->user()->idUser;

        try {
            // Créer l'entrée (le stock sera mis à jour automatiquement via l'event)
            StockEntree::create($validated);

            session()->flash('success', 'Entrée de stock enregistrée avec succès. Le stock a été mis à jour.');
            return redirect()->route('stock.entrees.index', ['usage' => $this->usage]);
        } catch (\Exception $e) {
            session()->flash('error', 'Erreur lors de l\'enregistrement : ' . $e->getMessage());
        }
    }

    public function cancel()
    {
        return redirect()->route('stock.entrees.index', ['usage' => $this->usage]);
    }

    public function render()
    {
        $usage = $this->usageEntrees();
        return view('livewire.stock.entrees.form-entree', [
            'usageEntrees' => $usage,
        ]);
    }
}
