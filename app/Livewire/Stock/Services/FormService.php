<?php

namespace App\Livewire\Stock\Services;

use App\Models\Service;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class FormService extends Component
{
    public ?int $id = null;
    public string $nom = '';
    public string $code = '';
    public string $description = '';
    public bool $actif = true;

    public function mount($id = null): void
    {
        if (!auth()->user()->hasAnyRole(['admin', 'admin_stock'])) {
            abort(403, 'Accès réservé.');
        }
        if ($id) {
            $s = Service::findOrFail($id);
            $this->id = $s->id;
            $this->nom = $s->nom;
            $this->code = $s->code ?? '';
            $this->description = $s->description ?? '';
            $this->actif = $s->actif;
        }
    }

    public function save(): void
    {
        $this->validate([
            'nom' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'actif' => 'boolean',
        ], ['nom.required' => 'Le nom est obligatoire.']);

        if ($this->id) {
            Service::findOrFail($this->id)->update([
                'nom' => $this->nom,
                'code' => $this->code ?: null,
                'description' => $this->description ?: null,
                'actif' => $this->actif,
            ]);
            session()->flash('success', 'Service modifié.');
        } else {
            Service::create([
                'nom' => $this->nom,
                'code' => $this->code ?: null,
                'description' => $this->description ?: null,
                'actif' => $this->actif,
            ]);
            session()->flash('success', 'Service créé.');
        }
        $this->redirect(route('stock.services.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.stock.services.form-service');
    }
}
