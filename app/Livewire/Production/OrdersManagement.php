<?php

namespace App\Livewire\Production;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use App\Models\CommandeClient;
use App\Models\StockReservation;
use App\Models\StockProduit;
use App\Services\CommandeNotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

#[Layout('components.layouts.app')]
class OrdersManagement extends Component
{
    use WithFileUploads;

    /** Recherche : n° commande, client, produit, statut */
    public string $search = '';

    /** Modal Valider : modifier quantité puis valider */
    public bool $showModalValider = false;
    public ?int $orderIdToValidate = null;
    public ?int $quantiteValider = null;

    /** Modal Livré : upload BL signé */
    public bool $showModalLivrer = false;
    public ?int $orderIdToLivrer = null;
    public $blSigneFile = null;

    public function mount(): void
    {
        if (request()->filled('search')) {
            $this->search = (string) request('search');
        }
    }

    public function openModalValider(int $orderId): void
    {
        $order = CommandeClient::findOrFail($orderId);
        if ($order->statut !== 'soumis') {
            return;
        }
        $this->orderIdToValidate = $orderId;
        $this->quantiteValider = (int) $order->quantite;
        $this->showModalValider = true;
        $this->resetValidation();
    }

    public function closeModalValider(): void
    {
        $this->showModalValider = false;
        $this->orderIdToValidate = null;
        $this->quantiteValider = null;
    }

    protected function rulesValider(): array
    {
        return ['quantiteValider' => 'required|integer|min:1'];
    }

    public function submitValider(): void
    {
        $this->validate($this->rulesValider());
        $orderId = $this->orderIdToValidate;
        $order = CommandeClient::findOrFail($orderId);
        if ($order->statut !== 'soumis') {
            $this->closeModalValider();
            session()->flash('error', 'Cette commande n\'est plus au statut soumis.');
            return;
        }
        $qty = (int) $this->quantiteValider;
        if ($qty !== $order->quantite) {
            if ($qty > $order->quantite) {
                $clientId = $order->client->client_id ?? null;
                $reservation = $clientId
                    ? StockReservation::where('client_id', $clientId)->where('produit_id', $order->produit_id)->first()
                    : null;
                if (!$reservation || $reservation->quantite_reservee < $qty) {
                    $max = $reservation ? $reservation->quantite_reservee : 0;
                    session()->flash('error', 'Stock réservé insuffisant. Maximum possible : ' . $max . '.');
                    return;
                }
            }
            $updates = ['quantite' => $qty];
            if ($order->quantite_demandee === null) {
                $updates['quantite_demandee'] = $order->quantite;
            }
            $order->update($updates);
        }
        try {
            DB::transaction(function () use ($order) {
                $clientId = $order->client->client_id ?? null;
                $reservation = $clientId
                    ? StockReservation::where('client_id', $clientId)->where('produit_id', $order->produit_id)->first()
                    : null;
                if (!$reservation || $reservation->quantite_reservee < $order->quantite) {
                    throw new \Exception('Le stock réservé du client est insuffisant pour valider cette commande.');
                }
                $reservation->decrement('quantite_reservee', $order->quantite);
                $produit = StockProduit::find($order->produit_id);
                if ($produit && $produit->peutRetirer($order->quantite)) {
                    $produit->retirerStock($order->quantite);
                }
                $order->update(['statut' => 'en_cours_de_traitement']);
            });
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
            return;
        }
        $order->load(['client.client', 'produit']);
        CommandeNotificationService::commandeValidee($order, $order->quantite_modifiee_par_production);
        $this->closeModalValider();
        session()->flash('success', 'Commande validée. Stock physique déduit.');
    }

    public function validateOrder($orderId)
    {
        $this->openModalValider((int) $orderId);
    }

    public function rejectOrder($orderId)
    {
        $order = CommandeClient::with(['client.client', 'produit'])->findOrFail($orderId);
        $order->update(['statut' => 'rejetee']);
        CommandeNotificationService::commandeRejetee($order->fresh(['client.client', 'produit']));
        session()->flash('success', 'Commande rejetée.');
    }

    public function setFinalise($orderId): void
    {
        $order = CommandeClient::findOrFail($orderId);
        if (!in_array($order->statut, ['soumis', 'en_cours_de_traitement'], true)) {
            session()->flash('error', 'Statut invalide pour finaliser.');
            return;
        }
        $numeroBL = CommandeClient::genererNumeroBL();
        $order->update([
            'statut' => 'finalise',
            'bon_livraison_numero' => $numeroBL,
        ]);
        $order->load(['client.client', 'produit']);
        CommandeNotificationService::commandeFinalisee($order->fresh(['client.client', 'produit']));
        session()->flash('success', 'Bon de livraison ' . $numeroBL . ' généré. Commande finalisée.');
        $this->dispatch('download-bl', url: route('production.orders.bon-livraison.download', $order));
    }

    public function openModalLivrer(int $orderId): void
    {
        $order = CommandeClient::findOrFail($orderId);
        if (!in_array($order->statut, ['en_cours_de_traitement', 'finalise'], true)) {
            session()->flash('error', 'Seules les commandes en cours ou finalisées peuvent être marquées livrées.');
            return;
        }
        $this->orderIdToLivrer = $orderId;
        $this->blSigneFile = null;
        $this->showModalLivrer = true;
        $this->resetValidation();
    }

    public function closeModalLivrer(): void
    {
        $this->showModalLivrer = false;
        $this->orderIdToLivrer = null;
        $this->blSigneFile = null;
    }

    protected function rulesLivrer(): array
    {
        return ['blSigneFile' => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png'];
    }

    public function submitLivrer(): void
    {
        $this->validate($this->rulesLivrer());
        $order = CommandeClient::findOrFail($this->orderIdToLivrer);
        if (!in_array($order->statut, ['en_cours_de_traitement', 'finalise'], true)) {
            $this->closeModalLivrer();
            session()->flash('error', 'Statut invalide.');
            return;
        }
        $path = $this->blSigneFile->store('commandes_bl/' . $order->id, 'public');
        $order->update([
            'statut' => 'livre',
            'bl_signe_path' => $path,
        ]);
        $order->load(['client.client', 'produit']);
        CommandeNotificationService::commandeLivree($order->fresh(['client.client', 'produit']));
        $this->closeModalLivrer();
        session()->flash('success', 'BL signé archivé. Commande marquée comme livrée.');
    }

    public function setLivre($orderId): void
    {
        $this->openModalLivrer((int) $orderId);
    }

    public function getOrderToValidateProperty(): ?CommandeClient
    {
        return $this->orderIdToValidate
            ? CommandeClient::with(['client', 'produit'])->find($this->orderIdToValidate)
            : null;
    }

    public function getOrderToLivrerProperty(): ?CommandeClient
    {
        return $this->orderIdToLivrer
            ? CommandeClient::with(['client', 'produit'])->find($this->orderIdToLivrer)
            : null;
    }

    public function render()
    {
        $query = CommandeClient::with(['client.client', 'produit'])
            ->orderBy('created_at', 'desc');

        if ($this->search !== '') {
            $s = trim($this->search);
            $query->where(function ($q) use ($s) {
                $q->where('commande_numero', 'like', "%{$s}%")
                    ->orWhere('statut', 'like', "%{$s}%")
                    ->orWhereHas('client', fn ($q2) => $q2->where('users', 'like', "%{$s}%"))
                    ->orWhereHas('produit', fn ($q2) => $q2->where('libelle', 'like', "%{$s}%"));
            });
        }

        $orders = $query->get();
        $orderValider = $this->orderIdToValidate
            ? CommandeClient::with(['client', 'produit'])->find($this->orderIdToValidate)
            : null;
        $orderLivrer = $this->orderIdToLivrer
            ? CommandeClient::with(['client', 'produit'])->find($this->orderIdToLivrer)
            : null;

        return view('livewire.production.orders-management', [
            'orders' => $orders,
            'orderValider' => $orderValider,
            'orderLivrer' => $orderLivrer,
        ]);
    }
}
