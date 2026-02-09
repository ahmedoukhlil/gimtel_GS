<?php

namespace App\Livewire\Client;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\CommandeClient;

#[Layout('components.layouts.app')]
class ClientCommande extends Component
{
    public string $search = '';

    /** Filtre "commandes en cours" (soumis, en_cours_de_traitement, finalise) quand on arrive depuis la notification du dashboard (?actif=1) */
    public bool $filterActif = false;

    /** Filtre par statut unique (ex. ?statut=livre pour l'historique des livraisons) */
    public ?string $filterStatut = null;

    /** Statuts considérés comme "actifs" (ceux affichés dans la notification client) */
    public const STATUTS_ACTIFS = ['soumis', 'en_cours_de_traitement', 'finalise'];

    public function mount(): void
    {
        $user = auth()->user();
        if (!$user || !$user->isClient()) {
            abort(403, 'Accès réservé aux clients.');
        }
        if (request()->filled('search')) {
            $this->search = (string) request('search');
        }
        if (request()->boolean('actif')) {
            $this->filterActif = true;
            session(['client_commandes_notif_seen_at' => now()]);
        }
        if (request()->filled('statut') && in_array(request('statut'), ['soumis', 'en_cours_de_traitement', 'finalise', 'livre', 'rejetee'], true)) {
            $this->filterStatut = request('statut');
        }
    }

    public function render()
    {
        $query = CommandeClient::where('client_id', auth()->id())
            ->with('produit')
            ->orderBy('created_at', 'desc');

        if ($this->filterStatut !== null) {
            $query->where('statut', $this->filterStatut);
        } elseif ($this->filterActif) {
            $query->whereIn('statut', self::STATUTS_ACTIFS);
        }

        if ($this->search !== '') {
            $term = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('commande_numero', 'like', $term)
                    ->orWhereHas('produit', fn ($p) => $p->where('libelle', 'like', $term))
                    ->orWhere('statut', 'like', $term);
            });
        }

        $commandes = $query->get();

        return view('livewire.client.client-commande', [
            'commandes' => $commandes,
        ]);
    }
}
