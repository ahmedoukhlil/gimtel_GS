<?php

namespace App\Livewire\Dmg;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\DemandeApprovisionnement;

#[Layout('components.layouts.app')]
class DetailDemandeDmg extends Component
{
    public DemandeApprovisionnement $demande;
    public bool $showRejet = false;
    public string $motif_rejet = '';
    public ?string $commentaire_dmg = null;
    /** @var array<int, int> quantite_accordee par ligne id */
    public array $quantites_accordees = [];

    public function mount(DemandeApprovisionnement $demande): void
    {
        if (!auth()->user()->isDirectionMoyensGeneraux()) {
            abort(403, 'Accès réservé à la direction des moyens généraux.');
        }
        $this->demande = $demande->load(['demandeurStock', 'service', 'lignes.produit', 'demandeur', 'traitePar']);
        foreach ($this->demande->lignes as $ligne) {
            $this->quantites_accordees[$ligne->id] = $ligne->quantite_demandee;
        }
    }

    public function marquerEnCours(): void
    {
        if ($this->demande->statut !== 'soumis') {
            return;
        }
        $this->demande->update(['statut' => 'en_cours']);
        session()->flash('success', 'Demande marquée en cours d\'examen.');
        $this->demande->refresh();
        $this->demande->load(['service', 'lignes.produit', 'demandeur', 'traitePar']);
    }

    public function approuver(): void
    {
        if (!in_array($this->demande->statut, ['soumis', 'en_cours'], true)) {
            return;
        }
        foreach ($this->demande->lignes as $ligne) {
            $q = $this->quantites_accordees[$ligne->id] ?? $ligne->quantite_demandee;
            $ligne->update(['quantite_accordee' => (int) $q]);
        }
        $this->demande->update([
            'statut' => 'approuve',
            'date_traitement' => now(),
            'traite_par_user_id' => auth()->id(),
            'motif_rejet' => null,
            'commentaire_dmg' => $this->commentaire_dmg,
        ]);
        session()->flash('success', 'Demande approuvée.');
        $this->redirect(route('dmg.demandes.index'), navigate: true);
    }

    public function rejeter(): void
    {
        $this->validate(['motif_rejet' => 'required|string|min:3'], ['motif_rejet.required' => 'Le motif de rejet est obligatoire.']);
        if (!in_array($this->demande->statut, ['soumis', 'en_cours'], true)) {
            return;
        }
        $this->demande->update([
            'statut' => 'rejete',
            'motif_rejet' => $this->motif_rejet,
            'date_traitement' => now(),
            'traite_par_user_id' => auth()->id(),
            'commentaire_dmg' => $this->commentaire_dmg,
        ]);
        session()->flash('success', 'Demande rejetée.');
        $this->redirect(route('dmg.demandes.index'), navigate: true);
    }

    public function marquerServi(): void
    {
        if ($this->demande->statut !== 'approuve') {
            session()->flash('error', 'Seules les demandes approuvées peuvent être marquées comme servies.');
            return;
        }
        $this->demande->update(['statut' => 'servi']);
        session()->flash('success', 'Demande marquée comme servie.');
        $this->demande->refresh();
        $this->demande->load(['service', 'lignes.produit', 'demandeur', 'traitePar']);
    }

    /** Événements pour la timeline (même logique visuelle que OrderDetail Production). */
    public function getTimelineEventsProperty(): array
    {
        $d = $this->demande;
        $events = [];

        $events[] = [
            'label' => 'Demande soumise',
            'date' => $d->created_at,
            'description' => '',
            'color' => 'green',
        ];

        $events[] = [
            'label' => 'En cours d\'examen',
            'date' => in_array($d->statut, ['en_cours', 'approuve', 'rejete', 'servi']) ? $d->updated_at : null,
            'description' => '',
            'color' => in_array($d->statut, ['approuve', 'rejete', 'servi']) ? 'green' : (($d->statut === 'en_cours') ? 'blue' : 'gray'),
        ];

        if (in_array($d->statut, ['approuve', 'rejete', 'servi'])) {
            $events[] = [
                'label' => $d->statut === 'rejete' ? 'Demande rejetée' : 'Demande approuvée',
                'date' => $d->date_traitement,
                'description' => $d->statut === 'rejete' && $d->motif_rejet ? $d->motif_rejet : '',
                'color' => $d->statut === 'rejete' ? 'red' : 'green',
            ];
        } else {
            $events[] = [
                'label' => 'Décision (approuver / rejeter)',
                'date' => null,
                'description' => '',
                'color' => 'gray',
            ];
        }

        $events[] = [
            'label' => 'Servi',
            'date' => $d->statut === 'servi' ? $d->updated_at : null,
            'description' => '',
            'color' => $d->statut === 'servi' ? 'green' : 'gray',
        ];

        return $events;
    }

    public function render()
    {
        return view('livewire.dmg.detail-demande-dmg', [
            'timelineEvents' => $this->getTimelineEventsProperty(),
        ]);
    }
}
