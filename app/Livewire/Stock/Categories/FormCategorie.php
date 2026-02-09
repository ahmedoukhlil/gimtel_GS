<?php

namespace App\Livewire\Stock\Categories;

use App\Models\StockCategorie;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class FormCategorie extends Component
{
    public $categorie = null;
    public $id = null;
    public $libelle = '';
    public $observations = '';

    public string $usage = StockCategorie::USAGE_COMMANDE_CARTE;

    public function mount($id = null): void
    {
        $user = auth()->user();
        if (!$user || !$user->hasRole('admin')) {
            abort(403, 'Accès réservé à l\'administrateur. Seul l\'administrateur peut paramétrer les catégories.');
        }
        $this->usage = request()->routeIs('stock.categories-appro.*') ? StockCategorie::USAGE_APPRO : StockCategorie::USAGE_COMMANDE_CARTE;

        if ($id) {
            $this->id = $id;
            $this->categorie = StockCategorie::findOrFail($id);
            if ($this->categorie->usage !== $this->usage) {
                abort(404, 'Cette catégorie n\'appartient pas à cette section.');
            }
            $this->libelle = $this->categorie->libelle;
            $this->observations = $this->categorie->observations ?? '';
        }
    }

    protected function rules()
    {
        return [
            'libelle' => 'required|string|max:255',
            'observations' => 'nullable|string',
        ];
    }

    protected function messages()
    {
        return [
            'libelle.required' => 'Le libellé est obligatoire.',
            'libelle.max' => 'Le libellé ne peut pas dépasser 255 caractères.',
        ];
    }

    public function save()
    {
        $validated = $this->validate();

        if ($this->categorie) {
            $this->categorie->update($validated);
            session()->flash('success', 'Catégorie modifiée avec succès.');
        } else {
            $validated['usage'] = $this->usage;
            StockCategorie::create($validated);
            session()->flash('success', 'Catégorie créée avec succès.');
        }

        Cache::forget('stock_categories_options');
        return redirect()->to($this->indexRoute());
    }

    public function cancel()
    {
        return redirect()->to($this->indexRoute());
    }

    public function indexRoute(): string
    {
        return $this->usage === StockCategorie::USAGE_APPRO
            ? route('stock.categories-appro.index')
            : route('stock.categories.index');
    }

    public function render()
    {
        return view('livewire.stock.categories.form-categorie', [
            'usage' => $this->usage,
            'indexRoute' => $this->indexRoute(),
        ]);
    }
}
