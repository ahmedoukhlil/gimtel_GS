<?php

namespace App\Livewire\Production;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Client;
use App\Models\StockReservation;
use App\Models\CommandeClient;
use App\Models\User;
use App\Services\CommandeNotificationService;

#[Layout('components.layouts.app')]
class CommandePourClient extends Component
{
    /** ID du client (société) pour lequel on commande */
    public string $client_id = '';

    /** Panier : [ reservation_id => quantite ] */
    public array $panier = [];

    /** Quantité saisie avant d'ajouter au panier (temporaire par ligne) */
    public array $quantities = [];

    public function mount(): void
    {
        $user = auth()->user();
        if (!$user || !$user->isDirectionProduction()) {
            abort(403, 'Accès réservé à la direction production.');
        }
    }

    public function updatedClientId(): void
    {
        $this->resetPanier();
    }

    public function resetPanier(): void
    {
        $this->panier = [];
        $this->quantities = [];
    }

    public function addToPanier(int $reservationId): void
    {
        $qty = (int) ($this->quantities[$reservationId] ?? 0);
        if ($qty <= 0) {
            session()->flash('error', 'Veuillez saisir une quantité valide.');
            return;
        }

        $reservation = StockReservation::with('produit')->findOrFail($reservationId);
        if ($this->client_id === '' || (int) $this->client_id !== $reservation->client_id) {
            session()->flash('error', 'Cette réservation ne correspond pas au client sélectionné.');
            return;
        }

        $dejaDansPanier = (int) ($this->panier[$reservationId] ?? 0);
        $totalVoulu = $dejaDansPanier + $qty;
        if ($totalVoulu > $reservation->quantite_restante) {
            session()->flash('error', 'La quantité dépasse le stock disponible (reste : ' . $reservation->quantite_restante . ').');
            return;
        }

        $this->panier[$reservationId] = $totalVoulu;
        $this->quantities[$reservationId] = 0;
        session()->flash('success', 'Ajouté au panier.');
    }

    public function updatedPanier(): void
    {
        foreach ($this->panier as $reservationId => $qty) {
            $qty = (int) $qty;
            if ($qty <= 0) {
                unset($this->panier[$reservationId]);
                continue;
            }
            $reservation = StockReservation::with('produit')->find($reservationId);
            if (!$reservation || $qty <= $reservation->quantite_restante) {
                continue;
            }
            $this->panier[$reservationId] = $reservation->quantite_restante;
            session()->flash('error', 'Quantité max. pour « ' . ($reservation->produit->libelle ?? 'ce produit') . ' » : ' . $reservation->quantite_restante);
        }
    }

    public function updateQuantitePanier(int $reservationId, $qty): void
    {
        $qty = (int) $qty;
        if ($qty <= 0) {
            unset($this->panier[$reservationId]);
            return;
        }

        $reservation = StockReservation::findOrFail($reservationId);
        if ($qty > $reservation->quantite_restante) {
            $this->panier[$reservationId] = $reservation->quantite_restante;
            session()->flash('error', 'Quantité max. pour « ' . ($reservation->produit->libelle ?? 'ce produit') . ' » : ' . $reservation->quantite_restante);
            return;
        }

        $this->panier[$reservationId] = $qty;
    }

    public function removeFromPanier(int $reservationId): void
    {
        unset($this->panier[$reservationId]);
    }

    public function validerPanier(): void
    {
        if (empty($this->panier)) {
            session()->flash('error', 'Le panier est vide.');
            return;
        }

        if ($this->client_id === '') {
            session()->flash('error', 'Veuillez sélectionner un client.');
            return;
        }

        $clientId = (int) $this->client_id;
        $clientUser = User::where('client_id', $clientId)->first();
        if (!$clientUser) {
            session()->flash('error', 'Aucun utilisateur client associé à cette société. Impossible de créer la commande.');
            return;
        }

        $client = Client::find($clientId);
        $created = 0;
        $errors = [];

        foreach ($this->panier as $reservationId => $qty) {
            $qty = (int) $qty;
            if ($qty <= 0) {
                continue;
            }

            $reservation = StockReservation::with('produit')->find($reservationId);
            if (!$reservation || $reservation->client_id != $clientId) {
                continue;
            }

            if ($qty > $reservation->quantite_restante) {
                $errors[] = $reservation->produit->libelle . ' : quantité disponible ' . $reservation->quantite_restante;
                continue;
            }

            $commande = CommandeClient::create([
                'commande_numero' => CommandeClient::genererNumero(),
                'client_id' => $clientUser->idUser,
                'produit_id' => $reservation->produit_id,
                'quantite' => $qty,
                'quantite_demandee' => $qty,
                'statut' => 'soumis',
            ]);
            $commande->load(['client.client', 'produit']);
            CommandeNotificationService::commandeEnregistree($commande);
            $created++;
        }

        $this->resetPanier();

        if (!empty($errors)) {
            session()->flash('error', 'Commande partielle. ' . implode(' ; ', $errors));
        }
        if ($created > 0) {
            session()->flash('success', $created . ' commande(s) créée(s) au profit de ' . ($client->NomClient ?? 'ce client') . '.');
        }
    }

    /** Lignes du panier avec infos produit (pour la vue) */
    public function getPanierLignesProperty()
    {
        if (empty($this->panier)) {
            return collect();
        }

        $reservations = StockReservation::with(['produit.categorie'])
            ->whereIn('id', array_keys($this->panier))
            ->get()
            ->keyBy('id');

        $lignes = [];
        foreach ($this->panier as $reservationId => $qty) {
            $res = $reservations->get($reservationId);
            if (!$res || (int) $qty <= 0) {
                continue;
            }
            $lignes[] = [
                'reservation_id' => $reservationId,
                'produit' => $res->produit,
                'quantite' => (int) $qty,
                'quantite_restante' => $res->quantite_restante,
            ];
        }
        return collect($lignes);
    }

    public function getReservationsProperty()
    {
        if ($this->client_id === '') {
            return collect();
        }
        $clientId = (int) $this->client_id;
        return StockReservation::where('client_id', $clientId)
            ->with(['produit.categorie', 'client'])
            ->get();
    }

    public function render()
    {
        $clients = Client::orderBy('NomClient')->get(['id', 'NomClient']);
        $reservations = $this->reservations;
        $panierLignes = $this->panierLignes;
        $clientSelectionne = $this->client_id !== '' ? Client::find((int) $this->client_id) : null;

        return view('livewire.production.commande-pour-client', [
            'clients' => $clients,
            'reservations' => $reservations,
            'panierLignes' => $panierLignes,
            'clientSelectionne' => $clientSelectionne,
        ]);
    }
}
