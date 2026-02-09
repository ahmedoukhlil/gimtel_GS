<?php

namespace App\Livewire\Client;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\StockReservation;
use App\Models\CommandeClient;
use App\Services\CommandeNotificationService;
use Illuminate\Support\Facades\Auth;

#[Layout('components.layouts.app')]
class ReservedStock extends Component
{
    public $quantities = [];

    public function mount(): void
    {
        $user = auth()->user();
        if (!$user || !$user->isClient()) {
            abort(403, 'Accès réservé aux clients.');
        }
    }

    public function order($reservationId)
    {
        $reservation = StockReservation::findOrFail($reservationId);
        $qty = (int)($this->quantities[$reservationId] ?? 0);

        if ($qty <= 0) {
            session()->flash('error', 'Veuillez saisir une quantité valide.');
            return;
        }

        if ($qty > $reservation->quantite_restante) {
            session()->flash('error', 'La quantité dépasse votre stock disponible (reste : ' . $reservation->quantite_restante . ').');
            return;
        }

        $numero = CommandeClient::genererNumero();
        $commande = CommandeClient::create([
            'commande_numero' => $numero,
            'client_id' => Auth::id(),
            'produit_id' => $reservation->produit_id,
            'quantite' => $qty,
            'quantite_demandee' => $qty,
            'statut' => 'soumis',
        ]);
        $commande->load(['client.client', 'produit']);
        CommandeNotificationService::commandeEnregistree($commande);

        $this->quantities[$reservationId] = 0;
        session()->flash('success', 'Votre commande a été envoyée à la production.');
    }

    public function render()
    {
        $clientId = auth()->user()->client_id;
        $reservations = $clientId
            ? StockReservation::where('client_id', $clientId)->with(['produit.categorie'])->get()
            : collect();

        return view('livewire.client.reserved-stock', [
            'reservations' => $reservations
        ]);
    }
}
