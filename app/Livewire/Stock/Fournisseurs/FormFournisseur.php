<?php

namespace App\Livewire\Stock\Fournisseurs;

use App\Models\StockFournisseur;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class FormFournisseur extends Component
{
    public $fournisseur = null;
    public $id = null;
    public $libelle = '';
    public $observations = '';

    public string $usage = StockFournisseur::USAGE_COMMANDE_CARTE;

    public function mount($id = null): void
    {
        $user = auth()->user();
        if (!$user || !$user->hasRole('admin')) {
            abort(403, 'Accès réservé à l\'administrateur. Seul l\'administrateur peut paramétrer les fournisseurs.');
        }
        $this->usage = request()->routeIs('stock.fournisseurs-appro.*') ? StockFournisseur::USAGE_APPRO : StockFournisseur::USAGE_COMMANDE_CARTE;

        if ($id) {
            $this->id = $id;
            $this->fournisseur = StockFournisseur::findOrFail($id);
            if ($this->fournisseur->usage !== $this->usage) {
                abort(404, 'Ce fournisseur n\'appartient pas à cette section.');
            }
            $this->libelle = $this->fournisseur->libelle;
            $this->observations = $this->fournisseur->observations ?? '';
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
            'libelle.required' => 'Le nom du fournisseur est obligatoire.',
            'libelle.max' => 'Le nom du fournisseur ne peut pas dépasser 255 caractères.',
        ];
    }

    public function save()
    {
        $validated = $this->validate();

        if ($this->fournisseur) {
            $this->fournisseur->update($validated);
            session()->flash('success', 'Fournisseur modifié avec succès.');
        } else {
            $validated['usage'] = $this->usage;
            StockFournisseur::create($validated);
            session()->flash('success', 'Fournisseur créé avec succès.');
        }

        return redirect()->to($this->indexRoute());
    }

    public function cancel()
    {
        return redirect()->to($this->indexRoute());
    }

    public function indexRoute(): string
    {
        return $this->usage === StockFournisseur::USAGE_APPRO
            ? route('stock.fournisseurs-appro.index')
            : route('stock.fournisseurs.index');
    }

    public function render()
    {
        return view('livewire.stock.fournisseurs.form-fournisseur', [
            'usage' => $this->usage,
            'indexRoute' => $this->indexRoute(),
        ]);
    }
}
