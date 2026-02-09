<?php

namespace App\Livewire\Stock\Clients;

use App\Models\Client;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class FormClient extends Component
{
    use WithFileUploads;

    public $client = null;
    public $id = null;

    public $NomClient = '';
    public $contact = '';
    public $NomPointFocal = '';
    public $NumTel = '';
    public $adressmail = '';

    /** @var \Livewire\TemporaryUploadedFile|null */
    public $logo = null;

    public function mount($id = null): void
    {
        $user = auth()->user();
        if (!$user || !$user->hasRole('admin')) {
            abort(403, 'Accès réservé à l\'administrateur. Seul l\'administrateur peut paramétrer les clients.');
        }

        if ($id) {
            $this->id = $id;
            $this->client = Client::findOrFail($id);
            $this->NomClient = $this->client->NomClient;
            $this->contact = $this->client->contact ?? '';
            $this->NomPointFocal = $this->client->NomPointFocal ?? '';
            $this->NumTel = $this->client->NumTel ?? '';
            $this->adressmail = $this->client->adressmail ?? '';
        }
    }

    protected function rules(): array
    {
        return [
            'NomClient' => 'required|string|max:255',
            'contact' => 'nullable|string|max:255',
            'NomPointFocal' => 'nullable|string|max:255',
            'NumTel' => 'nullable|string|max:50',
            'adressmail' => 'nullable|email|max:255',
            'logo' => 'nullable|image|max:2048',
        ];
    }

    protected function messages(): array
    {
        return [
            'NomClient.required' => 'Le nom du client est obligatoire.',
            'NomClient.max' => 'Le nom ne peut pas dépasser 255 caractères.',
            'adressmail.email' => 'L\'adresse email doit être valide.',
            'logo.image' => 'Le logo doit être une image (jpeg, png, gif, webp).',
            'logo.max' => 'Le logo ne doit pas dépasser 2 Mo.',
        ];
    }

    public function save()
    {
        $validated = $this->validate();

        $logoPath = null;
        if ($this->logo) {
            $dir = 'clients/logos';
            if ($this->client && $this->client->logo) {
                Storage::disk('public')->delete($this->client->logo);
            }
            $logoPath = $this->logo->store($dir, 'public');
        }

        $data = collect($validated)->except('logo')->all();
        if ($logoPath !== null) {
            $data['logo'] = $logoPath;
        }

        if ($this->client) {
            $this->client->update($data);
            session()->flash('success', 'Client modifié avec succès.');
        } else {
            Client::create($data);
            session()->flash('success', 'Client créé avec succès.');
        }

        return redirect()->route('stock.clients.index');
    }

    public function cancel()
    {
        return redirect()->route('stock.clients.index');
    }

    public function render()
    {
        return view('livewire.stock.clients.form-client');
    }
}
