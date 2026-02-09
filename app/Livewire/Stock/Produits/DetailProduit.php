<?php

namespace App\Livewire\Stock\Produits;

use App\Models\StockProduit;
use App\Models\StockReservation;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class DetailProduit extends Component
{
    public $produit;
    public $id;
    public $onglet = 'info'; // info, entrees, sorties, historique, reservations

    public function mount($id)
    {
        $this->id = $id;
        $this->produit = StockProduit::with(['categorie', 'magasin'])
            ->findOrFail($id);

        // Rediriger vers la bonne section selon le type de produit
        $isApproRoute = request()->routeIs('stock.produits-appro.*');
        if ($this->produit->usage === StockProduit::USAGE_APPRO && !$isApproRoute) {
            $this->redirect(route('stock.produits-appro.show', $id), navigate: true);
            return;
        }
        if ($this->produit->usage === StockProduit::USAGE_COMMANDE_CARTE && $isApproRoute) {
            $this->redirect(route('stock.produits.show', $id), navigate: true);
            return;
        }
    }

    public function setOnglet($onglet)
    {
        $this->onglet = $onglet;
    }

    public function refresh()
    {
        $this->produit = StockProduit::with(['categorie', 'magasin'])
            ->findOrFail($this->id);
    }

    public function render()
    {
        // Charger les entrées et sorties selon l'onglet actif
        $entrees = collect();
        $sorties = collect();
        $historique = collect();

        if ($this->onglet === 'entrees' || $this->onglet === 'historique') {
            $entrees = $this->produit->entrees()
                ->with(['fournisseur', 'createur'])
                ->orderBy('date_entree', 'desc')
                ->limit(50)
                ->get();
        }

        if ($this->onglet === 'sorties' || $this->onglet === 'historique') {
            $sorties = $this->produit->sorties()
                ->with(['demandeur', 'createur'])
                ->orderBy('date_sortie', 'desc')
                ->limit(50)
                ->get();
        }

        if ($this->onglet === 'historique') {
            // Fusionner entrées et sorties pour l'historique complet
            $historique = $entrees->map(function ($entree) {
                return [
                    'type' => 'entree',
                    'date' => $entree->date_entree,
                    'quantite' => $entree->quantite,
                    'tiers' => $entree->fournisseur->libelle ?? 'N/A',
                    'reference' => $entree->reference_commande ?? '-',
                    'createur' => $entree->nom_createur,
                    'observations' => $entree->observations,
                ];
            })->concat($sorties->map(function ($sortie) {
                return [
                    'type' => 'sortie',
                    'date' => $sortie->date_sortie,
                    'quantite' => $sortie->quantite,
                    'tiers' => $sortie->demandeur->nom ?? 'N/A',
                    'reference' => '-',
                    'createur' => $sortie->nom_createur,
                    'observations' => $sortie->observations,
                ];
            }))->sortByDesc('date')->values();
        }

        $reservations = StockReservation::where('produit_id', $this->id)
            ->with('client')
            ->orderBy('quantite_reservee', 'desc')
            ->get();

        return view('livewire.stock.produits.detail-produit', [
            'entrees' => $entrees,
            'sorties' => $sorties,
            'historique' => $historique,
            'reservations' => $reservations,
        ]);
    }
}
