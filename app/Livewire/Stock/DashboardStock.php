<?php

namespace App\Livewire\Stock;

use App\Models\StockProduit;
use App\Models\StockMagasin;
use App\Models\StockEntree;
use App\Models\CommandeClient;
use App\Models\StockReservation;
use App\Models\DemandeApprovisionnement;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class DashboardStock extends Component
{
    public $totalProduits = 0;
    public $produitsEnAlerte = 0;
    public $totalMagasins = 0;
    public $entreesduMois = 0;
    public $sortiesduMois = 0;
    /** Quantité restante (stock total − réservations clients) */
    public $quantiteRestanteApresReservation = 0;
        /** Taux de produits en alerte (0–100) */
    public $tauxAlerte = 0;
    /** Quantité totale réservée (tous clients) pour le bloc Stock */
    public $quantiteReserveeTotale = 0;
    public $produitsAlerteDetails = [];

    public function mount()
    {
        $this->loadStatistics();
    }

    public function refresh()
    {
        $this->loadStatistics();
    }

    private function loadStatistics()
    {
        // Statistiques globales
        $this->totalProduits = StockProduit::count();
        $this->produitsEnAlerte = StockProduit::whereColumn('stock_actuel', '<=', 'seuil_alerte')->count();
        $this->totalMagasins = StockMagasin::count();

        // Mouvements du mois en cours
        $debutMois = now()->startOfMonth();
        $finMois = now()->endOfMonth();

        $this->entreesduMois = StockEntree::whereBetween('date_entree', [$debutMois, $finMois])->sum('quantite');
        // Sorties = quantités des commandes validées ce mois (passage en « en_cours_de_traitement »)
        $this->sortiesduMois = (int) CommandeClient::query()
            ->whereIn('statut', ['en_cours_de_traitement', 'finalise', 'livre'])
            ->whereHas('statutHistorique', function ($q) use ($debutMois, $finMois) {
                $q->where('statut', 'en_cours_de_traitement')
                    ->whereBetween('created_at', [$debutMois, $finMois]);
            })
            ->sum('quantite');

        $this->tauxAlerte = $this->totalProduits > 0
            ? round((float) $this->produitsEnAlerte / $this->totalProduits * 100, 1)
            : 0;

        // Quantité restante après réservation = par produit : max(0, stock_actuel - réservé), puis somme
        $reservesParProduit = DB::table('stock_reservations')
            ->selectRaw('produit_id, COALESCE(SUM(quantite_reservee), 0) as total_reserve')
            ->groupBy('produit_id')
            ->pluck('total_reserve', 'produit_id');
        $this->quantiteRestanteApresReservation = (int) DB::table('stock_produits')->get()->sum(function ($p) use ($reservesParProduit) {
            $reserve = (int) ($reservesParProduit[$p->id] ?? 0);
            return max(0, (int) $p->stock_actuel - $reserve);
        });
        $this->quantiteReserveeTotale = (int) DB::table('stock_reservations')->sum('quantite_reservee');

        // Produits en alerte (top 10)
        $this->produitsAlerteDetails = StockProduit::with(['categorie', 'magasin'])
            ->whereColumn('stock_actuel', '<=', 'seuil_alerte')
            ->orderBy('stock_actuel')
            ->limit(10)
            ->get()
            ->map(function ($produit) {
                return [
                    'id' => $produit->id,
                    'libelle' => $produit->libelle,
                    'categorie' => $produit->categorie->libelle ?? '-',
                    'magasin' => $produit->magasin->magasin ?? '-',
                    'stock_actuel' => $produit->stock_actuel,
                    'seuil_alerte' => $produit->seuil_alerte,
                    'statut' => $produit->statut_stock,
                ];
            })
            ->toArray();
    }

    public function render()
    {
        $isClient = auth()->check() && auth()->user()->isClient();
        $isDemandeurInterne = auth()->check() && auth()->user()->isDemandeurInterne() && !$isClient;
        $commandes = collect();
        $statsCommandes = ['soumis' => 0, 'en_cours_de_traitement' => 0, 'finalise' => 0, 'livre' => 0, 'rejetee' => 0];
        $statsStockReserve = ['lignes' => 0, 'quantite_reservee' => 0];
        $clientNotificationCount = 0;

        $demandes = collect();
        $statsDemandes = ['soumis' => 0, 'en_cours' => 0, 'approuve' => 0, 'rejete' => 0, 'servi' => 0];
        $demandeurNotificationCount = 0;
        $prochaineEtapeSuggereeDemandeur = '';

        if ($isDemandeurInterne) {
            $user = auth()->user();
            $demandes = DemandeApprovisionnement::query()
                ->with(['demandeurStock', 'lignes'])
                ->where(function ($q) use ($user) {
                    $q->where('demandeur_user_id', $user->getAuthIdentifier());
                    if ($user->demandeur_id) {
                        $q->orWhere('demandeur_id', $user->demandeur_id);
                    }
                })
                ->orderBy('created_at', 'desc')
                ->get();
            $statsDemandes['soumis'] = $demandes->where('statut', 'soumis')->count();
            $statsDemandes['en_cours'] = $demandes->where('statut', 'en_cours')->count();
            $statsDemandes['approuve'] = $demandes->where('statut', 'approuve')->count();
            $statsDemandes['rejete'] = $demandes->where('statut', 'rejete')->count();
            $statsDemandes['servi'] = $demandes->where('statut', 'servi')->count();
            $demandeurNotificationCount = $statsDemandes['soumis'] + $statsDemandes['en_cours'] + $statsDemandes['approuve'];
            if ($statsDemandes['soumis'] > 0) {
                $prochaineEtapeSuggereeDemandeur = $statsDemandes['soumis'] . ' demande(s) soumise(s) : en attente d\'examen par la DMG.';
            } elseif ($statsDemandes['en_cours'] > 0) {
                $prochaineEtapeSuggereeDemandeur = $statsDemandes['en_cours'] . ' demande(s) en cours d\'examen.';
            } elseif ($statsDemandes['approuve'] > 0) {
                $prochaineEtapeSuggereeDemandeur = $statsDemandes['approuve'] . ' demande(s) approuvée(s) : en attente de traitement.';
            } elseif ($statsDemandes['servi'] > 0) {
                $prochaineEtapeSuggereeDemandeur = 'Vos demandes ont été traitées.';
            }
        }

        if ($isClient) {
            $commandes = CommandeClient::where('client_id', auth()->id())
                ->with('produit')
                ->orderBy('created_at', 'desc')
                ->get();
            $statsCommandes['soumis'] = $commandes->where('statut', 'soumis')->count();
            $statsCommandes['en_cours_de_traitement'] = $commandes->where('statut', 'en_cours_de_traitement')->count();
            $statsCommandes['finalise'] = $commandes->where('statut', 'finalise')->count();
            $statsCommandes['livre'] = $commandes->where('statut', 'livre')->count();
            $statsCommandes['rejetee'] = $commandes->where('statut', 'rejetee')->count();
            $seenAt = session('client_commandes_notif_seen_at');
            if ($seenAt !== null) {
                $clientNotificationCount = CommandeClient::where('client_id', auth()->id())
                    ->whereIn('statut', ['soumis', 'en_cours_de_traitement', 'finalise'])
                    ->where(function ($q) use ($seenAt) {
                        $q->where('created_at', '>', $seenAt)->orWhere('updated_at', '>', $seenAt);
                    })
                    ->count();
            } else {
                $clientNotificationCount = $statsCommandes['soumis'] + $statsCommandes['en_cours_de_traitement'] + $statsCommandes['finalise'];
            }
            $clientId = auth()->user()->client_id;
            if ($clientId) {
        $reservations = StockReservation::where('client_id', $clientId)->get();
                $statsStockReserve['lignes'] = $reservations->count();
                $statsStockReserve['quantite_reservee'] = $reservations->sum('quantite_reservee');
            }
            $livreesClient = $commandes->where('statut', 'livre');
            $delaiMoyenClientJours = $livreesClient->isNotEmpty()
                ? round((float) $livreesClient->sum(fn ($c) => $c->created_at->diffInDays($c->updated_at)) / $livreesClient->count(), 1)
                : null;
            $prochaineEtapeSuggeree = '';
            if ($statsCommandes['soumis'] > 0) {
                $prochaineEtapeSuggeree = $statsCommandes['soumis'] . ' commande(s) soumise(s) : elles seront traitées par la production sous peu.';
            } elseif ($statsCommandes['en_cours_de_traitement'] > 0) {
                $prochaineEtapeSuggeree = $statsCommandes['en_cours_de_traitement'] . ' commande(s) en cours de traitement.';
            } elseif ($statsCommandes['finalise'] > 0) {
                $prochaineEtapeSuggeree = $statsCommandes['finalise'] . ' commande(s) finalisée(s) : prête(s) à la livraison.';
            } elseif ($statsCommandes['livre'] > 0) {
                $prochaineEtapeSuggeree = 'Toutes vos commandes en cours ont été livrées.';
            }
        } else {
            $delaiMoyenClientJours = null;
            $prochaineEtapeSuggeree = '';
        }

        $isDirectionProduction = auth()->check() && auth()->user()->isDirectionProduction();
        $nouvellesCommandesCount = 0;
        $chartProduitClient = ['productLabels' => [], 'clientDatasets' => []];
        $chartDelaiTraitement = ['labels' => [], 'avgDays' => [], 'counts' => []];
        $cartesClients = [];

        if ($isDirectionProduction) {
            $nouvellesCommandesCount = CommandeClient::where('statut', 'soumis')->count();
            $reservations = StockReservation::with(['client:id,NomClient,logo', 'produit:id,libelle'])->get();
            $byClient = $reservations->groupBy('client_id');
            $productOrder = StockReservation::query()
                ->selectRaw('produit_id, SUM(quantite_reservee) as total')
                ->groupBy('produit_id')
                ->orderByDesc('total')
                ->limit(12)
                ->pluck('produit_id')
                ->toArray();
            $productLabels = [];
            foreach ($productOrder as $pid) {
                $r = $reservations->where('produit_id', $pid)->first();
                $productLabels[] = $r && $r->produit ? $r->produit->libelle : 'Produit #' . $pid;
            }
            $chartProduitClient['productLabels'] = $productLabels;

            $colors = [
                'rgba(99, 102, 241, 0.7)', 'rgba(34, 197, 94, 0.7)', 'rgba(234, 179, 8, 0.7)',
                'rgba(239, 68, 68, 0.7)', 'rgba(168, 85, 247, 0.7)', 'rgba(20, 184, 166, 0.7)',
                'rgba(249, 115, 22, 0.7)', 'rgba(236, 72, 153, 0.7)',
            ];
            $clientDatasets = [];
            $idx = 0;
            foreach ($byClient as $clientId => $clientReservations) {
                $clientName = $clientReservations->first()->client->NomClient ?? 'Client #' . $clientId;
                $data = [];
                foreach ($productOrder as $pid) {
                    $data[] = (int) $clientReservations->where('produit_id', $pid)->sum('quantite_reservee');
                }
                $clientDatasets[] = [
                    'label' => $clientName,
                    'data' => $data,
                    'backgroundColor' => $colors[$idx % count($colors)],
                ];
                $idx++;
            }
            $chartProduitClient['clientDatasets'] = $clientDatasets;

            $cartesClients = [];
            foreach ($byClient as $clientId => $clientReservations) {
                $client = $clientReservations->first()->client;
                $nom = $client->NomClient ?? 'Client #' . $clientId;
                $initial = mb_strtoupper(mb_substr(preg_replace('/\s+/', '', $nom), 0, 1)) ?: '?';
                $lignes = [];
                $total = 0;
                foreach ($clientReservations->groupBy('produit_id') as $pid => $res) {
                    $produit = $res->first()->produit;
                    $qty = (int) $res->sum('quantite_reservee');
                    $total += $qty;
                    $lignes[] = ['produit' => $produit->libelle ?? 'Produit #' . $pid, 'quantite' => $qty];
                }
                usort($lignes, fn ($a, $b) => $b['quantite'] <=> $a['quantite']);
                $cartesClients[] = [
                    'client_id' => $clientId,
                    'nom' => $nom,
                    'initial' => $initial,
                    'logo' => $client->logo ?? null,
                    'total' => $total,
                    'lignes' => $lignes,
                ];
            }
            usort($cartesClients, fn ($a, $b) => $b['total'] <=> $a['total']);

            // KPI Délai de traitement : moyenne du délai (created_at → updated_at) en jours, par semaine (commandes livrées)
            $nbSemaines = 12;
            $commandesLivrees = CommandeClient::where('statut', 'livre')
                ->select('created_at', 'updated_at')
                ->orderBy('updated_at')
                ->get();
            $parSemaine = [];
            foreach ($commandesLivrees as $c) {
                $semaine = $c->updated_at->format('Y-\WW');
                $jours = $c->created_at->diffInDays($c->updated_at);
                if (!isset($parSemaine[$semaine])) {
                    $parSemaine[$semaine] = ['total' => 0, 'count' => 0];
                }
                $parSemaine[$semaine]['total'] += $jours;
                $parSemaine[$semaine]['count'] += 1;
            }
            $semainesOrdonnees = collect($parSemaine)->keys()->sort()->values()->take(-$nbSemaines);
            foreach ($semainesOrdonnees as $s) {
                $chartDelaiTraitement['labels'][] = 'Sem. ' . substr($s, 6, 2) . ' ' . substr($s, 0, 4);
                $chartDelaiTraitement['avgDays'][] = round($parSemaine[$s]['total'] / $parSemaine[$s]['count'], 1);
                $chartDelaiTraitement['counts'][] = $parSemaine[$s]['count'];
            }

            // Délai moyen actuel (30 derniers jours, commandes livrées)
            $livrees30j = CommandeClient::where('statut', 'livre')
                ->where('updated_at', '>=', now()->subDays(30))
                ->get();
            $delaiMoyenJours = null;
            if ($livrees30j->isNotEmpty()) {
                $totalJours = $livrees30j->sum(fn ($c) => $c->created_at->diffInDays($c->updated_at));
                $delaiMoyenJours = round($totalJours / $livrees30j->count(), 1);
            }

            // Plus ancienne commande en attente (statut soumis)
            $plusAncienneSoumise = CommandeClient::where('statut', 'soumis')
                ->orderBy('created_at')
                ->first();
            $plusAncienneSoumiseJours = $plusAncienneSoumise ? now()->diffInDays($plusAncienneSoumise->created_at, false) : null;

            // Taux de livraison / rejet (30 derniers jours)
            $nbLivrees30j = $livrees30j->count();
            $nbRejetees30j = CommandeClient::where('statut', 'rejetee')
                ->where('updated_at', '>=', now()->subDays(30))
                ->count();
            $totalTraitees30j = $nbLivrees30j + $nbRejetees30j;
            $tauxLivraison30j = $totalTraitees30j > 0
                ? round((float) $livrees30j->count() / $totalTraitees30j * 100, 1)
                : null;

            // Répartition par statut (global)
            $repartitionStatuts = CommandeClient::selectRaw('statut, count(*) as total')
                ->groupBy('statut')
                ->pluck('total', 'statut')
                ->toArray();

            // Tendance délai : dernière semaine vs précédente
            $tendanceDelai = null;
            $avgDays = $chartDelaiTraitement['avgDays'] ?? [];
            if (count($avgDays) >= 2) {
                $dernier = $avgDays[array_key_last($avgDays)];
                $avant = $avgDays[array_key_last($avgDays) - 1];
                $tendanceDelai = $dernier < $avant ? 'En baisse' : ($dernier > $avant ? 'En hausse' : 'Stable');
            }
        } else {
            $delaiMoyenJours = null;
            $plusAncienneSoumise = null;
            $plusAncienneSoumiseJours = null;
            $tauxLivraison30j = null;
            $nbLivrees30j = 0;
            $nbRejetees30j = 0;
            $repartitionStatuts = [];
            $tendanceDelai = null;
        }

        $isDirectionMoyensGeneraux = auth()->check() && auth()->user()->isDirectionMoyensGeneraux();
        $demandesEnAttenteDmg = 0;
        $plusAncienneDemandeSoumise = null;
        $plusAncienneDemandeSoumiseJours = null;
        $repartitionStatutsDemandes = [];

        if ($isDirectionMoyensGeneraux) {
            $demandesEnAttenteDmg = DemandeApprovisionnement::whereIn('statut', ['soumis', 'en_cours'])->count();
            $plusAncienneDemandeSoumise = DemandeApprovisionnement::where('statut', 'soumis')
                ->orderBy('created_at')
                ->first();
            $plusAncienneDemandeSoumiseJours = $plusAncienneDemandeSoumise ? now()->diffInDays($plusAncienneDemandeSoumise->created_at, false) : null;
            $repartitionStatutsDemandes = DemandeApprovisionnement::selectRaw('statut, count(*) as total')
                ->groupBy('statut')
                ->pluck('total', 'statut')
                ->toArray();
        }

        return view('livewire.stock.dashboard-stock', [
            'isClient' => $isClient,
            'isDemandeurInterne' => $isDemandeurInterne,
            'clientNotificationCount' => $clientNotificationCount,
            'commandes' => $isClient ? $commandes->where('statut', '!=', 'livre')->values() : $commandes,
            'statsCommandes' => $statsCommandes,
            'statsStockReserve' => $statsStockReserve,
            'delaiMoyenClientJours' => $delaiMoyenClientJours ?? null,
            'prochaineEtapeSuggeree' => $prochaineEtapeSuggeree ?? '',
            'demandes' => $isDemandeurInterne ? $demandes->where('statut', '!=', 'servi')->values() : collect(),
            'statsDemandes' => $statsDemandes ?? ['soumis' => 0, 'en_cours' => 0, 'approuve' => 0, 'rejete' => 0, 'servi' => 0],
            'demandeurNotificationCount' => $demandeurNotificationCount ?? 0,
            'prochaineEtapeSuggereeDemandeur' => $prochaineEtapeSuggereeDemandeur ?? '',
            'isDirectionProduction' => $isDirectionProduction,
            'nouvellesCommandesCount' => $nouvellesCommandesCount,
            'chartProduitClient' => $chartProduitClient,
            'chartDelaiTraitement' => $chartDelaiTraitement,
            'cartesClients' => $cartesClients,
            'delaiMoyenJours' => $delaiMoyenJours ?? null,
            'plusAncienneSoumise' => $plusAncienneSoumise ?? null,
            'plusAncienneSoumiseJours' => $plusAncienneSoumiseJours ?? null,
            'tauxLivraison30j' => $tauxLivraison30j ?? null,
            'nbLivrees30j' => $nbLivrees30j ?? 0,
            'nbRejetees30j' => $nbRejetees30j ?? 0,
            'repartitionStatuts' => $repartitionStatuts ?? [],
            'tendanceDelai' => $tendanceDelai ?? null,
            'isDirectionMoyensGeneraux' => $isDirectionMoyensGeneraux,
            'demandesEnAttenteDmg' => $demandesEnAttenteDmg,
            'plusAncienneDemandeSoumise' => $plusAncienneDemandeSoumise,
            'plusAncienneDemandeSoumiseJours' => $plusAncienneDemandeSoumiseJours,
            'repartitionStatutsDemandes' => $repartitionStatutsDemandes,
        ]);
    }
}
