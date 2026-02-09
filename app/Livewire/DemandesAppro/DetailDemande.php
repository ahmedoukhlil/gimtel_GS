<?php

namespace App\Livewire\DemandesAppro;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\DemandeApprovisionnement;

#[Layout('components.layouts.app')]
class DetailDemande extends Component
{
    public DemandeApprovisionnement $demande;

    public function mount(DemandeApprovisionnement $demande): void
    {
        $user = auth()->user();
        if (!$user->isDemandeurInterne()) {
            abort(403, 'Accès réservé aux demandeurs internes.');
        }
        $allowed = $demande->demandeur_user_id === $user->getAuthIdentifier()
            || ($user->demandeur_id && $demande->demandeur_id === $user->demandeur_id);
        if (!$allowed) {
            abort(403, 'Vous ne pouvez pas consulter cette demande.');
        }
        $this->demande = $demande->load(['demandeurStock', 'service', 'lignes.produit', 'demandeur', 'traitePar']);
    }

    public function annuler(): void
    {
        if (!$this->demande->peutEtreAnnulee()) {
            session()->flash('error', 'Cette demande ne peut plus être annulée.');
            return;
        }
        $this->demande->update(['statut' => 'rejete', 'motif_rejet' => 'Annulée par le demandeur.']);
        session()->flash('success', 'Demande annulée.');
        $this->redirect(route('demandes-appro.index'), navigate: true);
    }

    private function buildTimelineEvents(): array
    {
        $d = $this->demande;
        $events = [
            ['label' => 'Soumis', 'color' => 'green', 'date' => $d->created_at, 'description' => null],
        ];
        $events[] = [
            'label' => 'En cours d\'examen',
            'color' => in_array($d->statut, ['en_cours', 'approuve', 'servi']) ? 'green' : ($d->statut === 'rejete' ? 'red' : 'grey'),
            'date' => in_array($d->statut, ['en_cours', 'approuve', 'rejete', 'servi']) ? $d->date_traitement : null,
            'description' => null,
        ];
        $events[] = [
            'label' => $d->statut === 'rejete' ? 'Rejeté' : 'Approuvé',
            'color' => $d->statut === 'rejete' ? 'red' : ($d->statut === 'approuve' || $d->statut === 'servi' ? 'green' : 'grey'),
            'date' => in_array($d->statut, ['approuve', 'rejete', 'servi']) ? $d->date_traitement : null,
            'description' => $d->statut === 'rejete' ? $d->motif_rejet : null,
        ];
        $events[] = [
            'label' => 'Servi',
            'color' => $d->statut === 'servi' ? 'green' : 'grey',
            'date' => null,
            'description' => null,
        ];
        return $events;
    }

    public function render()
    {
        return view('livewire.demandes-appro.detail-demande', [
            'timelineEvents' => $this->buildTimelineEvents(),
        ]);
    }
}
