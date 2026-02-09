<?php

namespace App\Livewire\Client;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\CommandeClient;

#[Layout('components.layouts.app')]
class ClientCommandeDetail extends Component
{
    /** @var CommandeClient */
    public $commande;

    public function mount($commande): void
    {
        $user = auth()->user();
        if (!$user || !$user->isClient()) {
            abort(403, 'Accès réservé aux clients.');
        }
        $id = (int) $commande;
        $this->commande = CommandeClient::with(['produit', 'statutHistorique'])
            ->where('client_id', $user->getAuthIdentifier())
            ->findOrFail($id);
    }

    private function buildTimelineEvents(): array
    {
        $order = $this->commande;
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
        return view('livewire.client.client-commande-detail', [
            'timelineEvents' => $this->buildTimelineEvents(),
        ]);
    }
}
