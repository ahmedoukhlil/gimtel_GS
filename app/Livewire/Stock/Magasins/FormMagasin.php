<?php

namespace App\Livewire\Stock\Magasins;

use App\Models\StockMagasin;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class FormMagasin extends Component
{
    public $magasin = null;
    public $id = null;

    // Champs du formulaire
    public $magasinNom = '';
    public $localisation = '';
    public $observations = '';

    public string $usage = StockMagasin::USAGE_COMMANDE_CARTE;

    public function mount($id = null): void
    {
        $user = auth()->user();
        if (!$user || !$user->hasRole('admin')) {
            abort(403, 'Accès réservé à l\'administrateur. Seul l\'administrateur peut paramétrer les magasins.');
        }
        $this->usage = request()->routeIs('stock.magasins-appro.*') ? StockMagasin::USAGE_APPRO : StockMagasin::USAGE_COMMANDE_CARTE;

        if ($id) {
            $this->id = $id;
            $this->magasin = StockMagasin::findOrFail($id);
            if ($this->magasin->usage !== $this->usage) {
                abort(404, 'Ce magasin n\'appartient pas à cette section.');
            }
            $this->magasinNom = $this->magasin->magasin;
            $this->localisation = $this->magasin->localisation;
            $this->observations = $this->magasin->observations ?? '';
        }
    }

    /**
     * Règles de validation
     */
    protected function rules()
    {
        return [
            'magasinNom' => 'required|string|max:255',
            'localisation' => 'required|string|max:255',
            'observations' => 'nullable|string',
        ];
    }

    /**
     * Messages de validation personnalisés
     */
    protected function messages()
    {
        return [
            'magasinNom.required' => 'Le nom du magasin est obligatoire.',
            'magasinNom.max' => 'Le nom du magasin ne peut pas dépasser 255 caractères.',
            'localisation.required' => 'La localisation est obligatoire.',
            'localisation.max' => 'La localisation ne peut pas dépasser 255 caractères.',
        ];
    }

    /**
     * Sauvegarder le magasin
     */
    public function save()
    {
        $validated = $this->validate();

        if ($this->magasin) {
            // Mise à jour
            $this->magasin->update([
                'magasin' => $validated['magasinNom'],
                'localisation' => $validated['localisation'],
                'observations' => $validated['observations'],
            ]);

            session()->flash('success', 'Magasin modifié avec succès.');
        } else {
            StockMagasin::create([
                'magasin' => $validated['magasinNom'],
                'localisation' => $validated['localisation'],
                'observations' => $validated['observations'],
                'usage' => $this->usage,
            ]);
            session()->flash('success', 'Magasin créé avec succès.');
        }

        Cache::forget('stock_magasins_options');
        return redirect()->to($this->indexRoute());
    }

    public function cancel()
    {
        return redirect()->to($this->indexRoute());
    }

    public function indexRoute(): string
    {
        return $this->usage === StockMagasin::USAGE_APPRO
            ? route('stock.magasins-appro.index')
            : route('stock.magasins.index');
    }

    public function render()
    {
        return view('livewire.stock.magasins.form-magasin', [
            'usage' => $this->usage,
            'indexRoute' => $this->indexRoute(),
        ]);
    }
}
