<?php

namespace App\Livewire\Stock\Demandeurs;

use App\Models\StockDemandeur;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class FormDemandeur extends Component
{
    public $demandeur = null;
    public $id = null;
    public $nom = '';
    public $poste_service = '';

    public function mount($id = null)
    {
        $user = auth()->user();
        if (!$user || !$user->canManageStock()) {
            abort(403, 'Accès non autorisé.');
        }

        if ($id) {
            $this->id = $id;
            $this->demandeur = StockDemandeur::findOrFail($id);
            $this->nom = $this->demandeur->nom;
            $this->poste_service = $this->demandeur->poste_service;
        }
    }

    protected function rules()
    {
        return [
            'nom' => 'required|string|max:255',
            'poste_service' => 'required|string|max:255',
        ];
    }

    protected function messages()
    {
        return [
            'nom.required' => 'Le nom est obligatoire.',
            'nom.max' => 'Le nom ne peut pas dépasser 255 caractères.',
            'poste_service.required' => 'Le poste/service est obligatoire.',
            'poste_service.max' => 'Le poste/service ne peut pas dépasser 255 caractères.',
        ];
    }

    public function save()
    {
        $validated = $this->validate();

        if ($this->demandeur) {
            $this->demandeur->update($validated);
            session()->flash('success', 'Demandeur modifié avec succès.');
        } else {
            StockDemandeur::create($validated);
            session()->flash('success', 'Demandeur créé avec succès.');
        }

        return redirect()->route('stock.demandeurs.index');
    }

    public function cancel()
    {
        return redirect()->route('stock.demandeurs.index');
    }

    public function render()
    {
        return view('livewire.stock.demandeurs.form-demandeur');
    }
}
