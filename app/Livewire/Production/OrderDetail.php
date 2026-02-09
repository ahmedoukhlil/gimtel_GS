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

#[Layout('components.layouts.app')]
class OrderDetail extends Component
{
    use WithFileUploads;

    public CommandeClient $order;

    /** Édition de la quantité (affichée uniquement si statut = soumis) */
    public bool $showEditQuantite = false;
    public ?int $quantiteSaisie = null;

    /** Modal Valider : modifier quantité puis valider (même workflow que liste) */
    public bool $showModalValider = false;
    public ?int $quantiteValider = null;

    /** Modal Livré : upload BL signé */
    public bool $showModalLivrer = false;
    public $blSigneFile = null;

    public function mount(CommandeClient $order): void
    {
        $user = auth()->user();
        if (!$user || !$user->isDirectionProduction()) {
            abort(403, 'Accès réservé à la direction production.');
        }
        $this->order = $order->load(['client.client', 'produit', 'statutHistorique']);
    }

    public function openEditQuantite(): void
    {
        $this->quantiteSaisie = (int) $this->order->quantite;
        $this->showEditQuantite = true;
    }

    public function cancelEditQuantite(): void
    {
        $this->showEditQuantite = false;
        $this->quantiteSaisie = null;
    }

    protected function rules(): array
    {
        return [
            'quantiteSaisie' => 'required|integer|min:1',
        ];
    }

    protected function validationAttributes(): array
    {
        return ['quantiteSaisie' => 'quantité'];
    }

    public function updateQuantite(): void
    {
        $this->validate();
        $qty = (int) $this->quantiteSaisie;
        $order = $this->order;
        if ($order->statut !== 'soumis') {
            session()->flash('error', 'La quantité ne peut être modifiée que pour une commande soumise.');
            return;
        }
        if ($qty > $order->quantite) {
            $clientId = $order->client->client_id ?? null;
            $reservation = $clientId
                ? StockReservation::where('client_id', $clientId)->where('produit_id', $order->produit_id)->first()
                : null;
            if (!$reservation || $reservation->quantite_reservee < $qty) {
                $max = $reservation ? $reservation->quantite_reservee : 0;
                session()->flash('error', 'Stock réservé insuffisant pour ce client. Maximum possible : ' . $max . '.');
                return;
            }
        }
        $updates = ['quantite' => $qty];
        if ($qty !== $order->quantite && $order->quantite_demandee === null) {
            $updates['quantite_demandee'] = $order->quantite;
        }
        $order->update($updates);
        $this->order->refresh();
        $this->showEditQuantite = false;
        $this->quantiteSaisie = null;
        session()->flash('success', 'Quantité mise à jour.');
    }

    public function openModalValider(): void
    {
        if ($this->order->statut !== 'soumis') {
            return;
        }
        $this->quantiteValider = (int) $this->order->quantite;
        $this->showModalValider = true;
        $this->resetValidation();
    }

    public function closeModalValider(): void
    {
        $this->showModalValider = false;
        $this->quantiteValider = null;
    }

    protected function rulesValider(): array
    {
        return ['quantiteValider' => 'required|integer|min:1'];
    }

    public function submitValider(): void
    {
        $this->validate($this->rulesValider());
        $order = $this->order;
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
        $this->order->refresh();
        $this->order->load(['client.client', 'produit']);
        CommandeNotificationService::commandeValidee($this->order, $this->order->quantite_modifiee_par_production);
        $this->closeModalValider();
        session()->flash('success', 'Commande validée. Stock physique déduit.');
    }

    public function validateOrder(): void
    {
        $this->openModalValider();
    }

    public function rejectOrder(): void
    {
        $this->order->update(['statut' => 'rejetee']);
        $this->order->refresh();
        $this->order->load(['client.client', 'produit']);
        CommandeNotificationService::commandeRejetee($this->order);
        session()->flash('success', 'Commande rejetée.');
    }

    public function setFinalise(): void
    {
        if (!in_array($this->order->statut, ['soumis', 'en_cours_de_traitement'], true)) {
            session()->flash('error', 'Statut invalide pour finaliser.');
            return;
        }
        $numeroBL = CommandeClient::genererNumeroBL();
        $this->order->update([
            'statut' => 'finalise',
            'bon_livraison_numero' => $numeroBL,
        ]);
        $this->order->refresh();
        $this->order->load(['client.client', 'produit']);
        CommandeNotificationService::commandeFinalisee($this->order);
        session()->flash('success', 'Bon de livraison ' . $numeroBL . ' généré. Commande finalisée.');
        $this->dispatch('download-bl', url: route('production.orders.bon-livraison.download', $this->order));
    }

    public function openModalLivrer(): void
    {
        if (!in_array($this->order->statut, ['en_cours_de_traitement', 'finalise'], true)) {
            session()->flash('error', 'Seules les commandes en cours ou finalisées peuvent être marquées livrées.');
            return;
        }
        $this->blSigneFile = null;
        $this->showModalLivrer = true;
        $this->resetValidation();
    }

    public function closeModalLivrer(): void
    {
        $this->showModalLivrer = false;
        $this->blSigneFile = null;
    }

    protected function rulesLivrer(): array
    {
        return ['blSigneFile' => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png'];
    }

    public function submitLivrer(): void
    {
        $this->validate($this->rulesLivrer());
        if (!in_array($this->order->statut, ['en_cours_de_traitement', 'finalise'], true)) {
            $this->closeModalLivrer();
            session()->flash('error', 'Statut invalide.');
            return;
        }
        $path = $this->blSigneFile->store('commandes_bl/' . $this->order->id, 'public');
        $this->order->update([
            'statut' => 'livre',
            'bl_signe_path' => $path,
        ]);
        $this->order->refresh();
        $this->order->load(['client.client', 'produit']);
        CommandeNotificationService::commandeLivree($this->order);
        $this->closeModalLivrer();
        session()->flash('success', 'BL signé archivé. Commande marquée comme livrée.');
    }

    public function setLivre(): void
    {
        $this->openModalLivrer();
    }

    private function buildTimelineEvents(): array
    {
        $order = $this->order;
        $step = $order->timeline_step;
        $rejetee = $order->statut === 'rejetee';
        $events = [
            ['label' => 'Soumis', 'done' => true, 'color' => 'green', 'date' => $order->getDateAtStatut('soumis'), 'description' => null],
        ];
        if ($rejetee) {
            $events[] = ['label' => 'Rejetée', 'done' => true, 'color' => 'red', 'date' => $order->getDateAtStatut('rejetee'), 'description' => $order->motif_rejet];
        }
        $events[] = ['label' => 'En cours de traitement', 'done' => $step >= 2, 'color' => $step >= 2 ? 'green' : 'grey', 'date' => $order->getDateAtStatut('en_cours_de_traitement'), 'description' => null];
        $events[] = ['label' => 'Finalisé', 'done' => $step >= 3, 'color' => $step >= 3 ? 'green' : 'grey', 'date' => $order->getDateAtStatut('finalise'), 'description' => null];
        $events[] = ['label' => 'Livré', 'done' => $step >= 4, 'color' => $step >= 4 ? 'green' : 'grey', 'date' => $order->getDateAtStatut('livre'), 'description' => null];
        return $events;
    }

    public function render()
    {
        return view('livewire.production.order-detail', [
            'timelineEvents' => $this->buildTimelineEvents(),
        ]);
    }
}
