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

    /**
     * Charge les statistiques de stock en fonction du rôle de l'utilisateur.
     *
     * - Direction Production : uniquement usage = commande_carte
     * - Admin / Admin Stock : tous les usages
     * - Client, Demandeur Interne, DMG : ces stats ne sont pas affichées, on les skip
     */
    private function loadStatistics()
    {
        $user = auth()->user();
        if (!$user) {
            return;
        }

        // Client, Demandeur Interne et DMG n'affichent pas les stats stock
        if ($user->isClient() || $user->isDemandeurInterne() || $user->isDirectionMoyensGeneraux()) {
            return;
        }

        // Déterminer le filtre d'usage selon le rôle
        $isProduction = $user->isDirectionProduction();
        $usageFilter = $isProduction ? StockProduit::USAGE_COMMANDE_CARTE : null; // null = tous

        // Requête de base pour les produits
        $produitsQuery = StockProduit::query();
        if ($usageFilter) {
            $produitsQuery->where('usage', $usageFilter);
        }

        $this->totalProduits = (clone $produitsQuery)->count();
        $this->produitsEnAlerte = (clone $produitsQuery)->whereColumn('stock_actuel', '<=', 'seuil_alerte')->count();
        $this->totalMagasins = StockMagasin::count();

        // Mouvements du mois en cours
        $debutMois = now()->startOfMonth();
        $finMois = now()->endOfMonth();

        $entreesQuery = StockEntree::whereBetween('date_entree', [$debutMois, $finMois]);
        if ($usageFilter) {
            $entreesQuery->whereHas('produit', fn ($q) => $q->where('usage', $usageFilter));
        }
        $this->entreesduMois = $entreesQuery->sum('quantite');

        // Sorties = quantités des commandes validées ce mois (passage en « en_cours_de_traitement »)
        // Les commandes clients concernent des produits commande_carte, donc pour Direction Production c'est correct.
        // Pour Admin, on prend toutes les commandes.
        $sortiesQuery = CommandeClient::query()
            ->whereIn('statut', ['en_cours_de_traitement', 'finalise', 'livre'])
            ->whereHas('statutHistorique', function ($q) use ($debutMois, $finMois) {
                $q->where('statut', 'en_cours_de_traitement')
                    ->whereBetween('created_at', [$debutMois, $finMois]);
            });
        if ($usageFilter) {
            $sortiesQuery->whereHas('produit', fn ($q) => $q->where('usage', $usageFilter));
        }
        $this->sortiesduMois = (int) $sortiesQuery->sum('quantite');

        $this->tauxAlerte = $this->totalProduits > 0
            ? round((float) $this->produitsEnAlerte / $this->totalProduits * 100, 1)
            : 0;

        // Quantité restante après réservation clients
        $reservesParProduit = DB::table('stock_reservations')
            ->selectRaw('produit_id, COALESCE(SUM(quantite_reservee), 0) as total_reserve')
            ->groupBy('produit_id')
            ->pluck('total_reserve', 'produit_id');

        $produitsForReserv = DB::table('stock_produits');
        if ($usageFilter) {
            $produitsForReserv->where('usage', $usageFilter);
        }
        $this->quantiteRestanteApresReservation = (int) $produitsForReserv->get()->sum(function ($p) use ($reservesParProduit) {
            $reserve = (int) ($reservesParProduit[$p->id] ?? 0);
            return max(0, (int) $p->stock_actuel - $reserve);
        });

        $reserveTotaleQuery = DB::table('stock_reservations');
        if ($usageFilter) {
            $reserveTotaleQuery->join('stock_produits', 'stock_reservations.produit_id', '=', 'stock_produits.id')
                ->where('stock_produits.usage', $usageFilter);
        }
        $this->quantiteReserveeTotale = (int) $reserveTotaleQuery->sum('quantite_reservee');

        // Produits en alerte (top 10)
        $alerteQuery = StockProduit::with(['categorie', 'magasin'])
            ->whereColumn('stock_actuel', '<=', 'seuil_alerte');
        if ($usageFilter) {
            $alerteQuery->where('usage', $usageFilter);
        }
        $this->produitsAlerteDetails = $alerteQuery
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
        $user = auth()->user();
        $isClient = $user && $user->isClient();
        $isDemandeurInterne = $user && $user->isDemandeurInterne() && !$isClient;

        // ─── CLIENT ───
        $commandes = collect();
        $statsCommandes = ['soumis' => 0, 'en_cours_de_traitement' => 0, 'finalise' => 0, 'livre' => 0, 'rejetee' => 0];
        $statsStockReserve = ['lignes' => 0, 'quantite_reservee' => 0];
        $clientNotificationCount = 0;
        $delaiMoyenClientJours = null;
        $prochaineEtapeSuggeree = '';

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

            $clientId = $user->client_id;
            if ($clientId) {
                $reservations = StockReservation::where('client_id', $clientId)->get();
                $statsStockReserve['lignes'] = $reservations->count();
                $statsStockReserve['quantite_reservee'] = $reservations->sum('quantite_reservee');
            }

            $livreesClient = $commandes->where('statut', 'livre');
            $delaiMoyenClientJours = $livreesClient->isNotEmpty()
                ? round((float) $livreesClient->sum(fn ($c) => $c->created_at->diffInDays($c->updated_at)) / $livreesClient->count(), 1)
                : null;

            if ($statsCommandes['soumis'] > 0) {
                $prochaineEtapeSuggeree = $statsCommandes['soumis'] . ' commande(s) soumise(s) : elles seront traitées par la production sous peu.';
            } elseif ($statsCommandes['en_cours_de_traitement'] > 0) {
                $prochaineEtapeSuggeree = $statsCommandes['en_cours_de_traitement'] . ' commande(s) en cours de traitement.';
            } elseif ($statsCommandes['finalise'] > 0) {
                $prochaineEtapeSuggeree = $statsCommandes['finalise'] . ' commande(s) finalisée(s) : prête(s) à la livraison.';
            } elseif ($statsCommandes['livre'] > 0) {
                $prochaineEtapeSuggeree = 'Toutes vos commandes en cours ont été livrées.';
            }
        }

        // ─── DEMANDEUR INTERNE ───
        $demandes = collect();
        $statsDemandes = ['soumis' => 0, 'en_cours' => 0, 'approuve' => 0, 'rejete' => 0, 'servi' => 0];
        $demandeurNotificationCount = 0;
        $prochaineEtapeSuggereeDemandeur = '';

        if ($isDemandeurInterne) {
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

        // ─── DIRECTION PRODUCTION ───
        $isDirectionProduction = $user && $user->isDirectionProduction();
        $nouvellesCommandesCount = 0;
        $chartProduitClient = ['productLabels' => [], 'clientDatasets' => []];
        $chartDelaiTraitement = ['labels' => [], 'avgDays' => [], 'counts' => []];
        $cartesClients = [];
        $delaiMoyenJours = null;
        $plusAncienneSoumise = null;
        $plusAncienneSoumiseJours = null;
        $tauxLivraison30j = null;
        $nbLivrees30j = 0;
        $nbRejetees30j = 0;
        $repartitionStatuts = [];
        $tendanceDelai = null;

        if ($isDirectionProduction) {
            $nouvellesCommandesCount = CommandeClient::where('statut', 'soumis')->count();

            // Réservations (produits commande_carte uniquement)
            $reservations = StockReservation::with(['client:id,NomClient,logo', 'produit:id,libelle'])
                ->whereHas('produit', fn ($q) => $q->where('usage', StockProduit::USAGE_COMMANDE_CARTE))
                ->get();
            $byClient = $reservations->groupBy('client_id');

            $productOrder = StockReservation::query()
                ->whereHas('produit', fn ($q) => $q->where('usage', StockProduit::USAGE_COMMANDE_CARTE))
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

            // KPI Délai de traitement (commandes livrées, produits commande_carte)
            $nbSemaines = 12;
            $commandesLivrees = CommandeClient::where('statut', 'livre')
                ->whereHas('produit', fn ($q) => $q->where('usage', StockProduit::USAGE_COMMANDE_CARTE))
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

            // Délai moyen (30j, commandes livrées, produits commande_carte)
            $livrees30j = CommandeClient::where('statut', 'livre')
                ->whereHas('produit', fn ($q) => $q->where('usage', StockProduit::USAGE_COMMANDE_CARTE))
                ->where('updated_at', '>=', now()->subDays(30))
                ->get();
            if ($livrees30j->isNotEmpty()) {
                $totalJours = $livrees30j->sum(fn ($c) => $c->created_at->diffInDays($c->updated_at));
                $delaiMoyenJours = round($totalJours / $livrees30j->count(), 1);
            }

            // Plus ancienne commande en attente (produits commande_carte)
            $plusAncienneSoumise = CommandeClient::where('statut', 'soumis')
                ->whereHas('produit', fn ($q) => $q->where('usage', StockProduit::USAGE_COMMANDE_CARTE))
                ->orderBy('created_at')
                ->first();
            $plusAncienneSoumiseJours = $plusAncienneSoumise ? now()->diffInDays($plusAncienneSoumise->created_at, false) : null;

            // Taux livraison (30j, produits commande_carte)
            $nbLivrees30j = $livrees30j->count();
            $nbRejetees30j = CommandeClient::where('statut', 'rejetee')
                ->whereHas('produit', fn ($q) => $q->where('usage', StockProduit::USAGE_COMMANDE_CARTE))
                ->where('updated_at', '>=', now()->subDays(30))
                ->count();
            $totalTraitees30j = $nbLivrees30j + $nbRejetees30j;
            $tauxLivraison30j = $totalTraitees30j > 0
                ? round((float) $nbLivrees30j / $totalTraitees30j * 100, 1)
                : null;

            // Répartition par statut (produits commande_carte uniquement)
            $repartitionStatuts = CommandeClient::query()
                ->whereHas('produit', fn ($q) => $q->where('usage', StockProduit::USAGE_COMMANDE_CARTE))
                ->selectRaw('statut, count(*) as total')
                ->groupBy('statut')
                ->pluck('total', 'statut')
                ->toArray();

            // Tendance délai
            $avgDays = $chartDelaiTraitement['avgDays'] ?? [];
            if (count($avgDays) >= 2) {
                $dernier = $avgDays[array_key_last($avgDays)];
                $avant = $avgDays[array_key_last($avgDays) - 1];
                $tendanceDelai = $dernier < $avant ? 'En baisse' : ($dernier > $avant ? 'En hausse' : 'Stable');
            }
        }

        // ─── DIRECTION MOYENS GÉNÉRAUX ───
        $isDirectionMoyensGeneraux = $user && $user->isDirectionMoyensGeneraux();
        $demandesEnAttenteDmg = 0;
        $plusAncienneDemandeSoumise = null;
        $plusAncienneDemandeSoumiseJours = null;
        $repartitionStatutsDemandes = [];

        if ($isDirectionMoyensGeneraux) {
            $demandesEnAttenteDmg = DemandeApprovisionnement::whereIn('statut', ['soumis', 'en_cours'])->count();
            $plusAncienneDemandeSoumise = DemandeApprovisionnement::where('statut', 'soumis')
                ->orderBy('created_at')
                ->first();
            $plusAncienneDemandeSoumiseJours = $plusAncienneDemandeSoumise
                ? now()->diffInDays($plusAncienneDemandeSoumise->created_at, false)
                : null;
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
            'delaiMoyenClientJours' => $delaiMoyenClientJours,
            'prochaineEtapeSuggeree' => $prochaineEtapeSuggeree,
            'demandes' => $isDemandeurInterne ? $demandes->where('statut', '!=', 'servi')->values() : collect(),
            'statsDemandes' => $statsDemandes,
            'demandeurNotificationCount' => $demandeurNotificationCount,
            'prochaineEtapeSuggereeDemandeur' => $prochaineEtapeSuggereeDemandeur,
            'isDirectionProduction' => $isDirectionProduction,
            'nouvellesCommandesCount' => $nouvellesCommandesCount,
            'chartProduitClient' => $chartProduitClient,
            'chartDelaiTraitement' => $chartDelaiTraitement,
            'cartesClients' => $cartesClients,
            'delaiMoyenJours' => $delaiMoyenJours,
            'plusAncienneSoumise' => $plusAncienneSoumise,
            'plusAncienneSoumiseJours' => $plusAncienneSoumiseJours,
            'tauxLivraison30j' => $tauxLivraison30j,
            'nbLivrees30j' => $nbLivrees30j,
            'nbRejetees30j' => $nbRejetees30j,
            'repartitionStatuts' => $repartitionStatuts,
            'tendanceDelai' => $tendanceDelai,
            'isDirectionMoyensGeneraux' => $isDirectionMoyensGeneraux,
            'demandesEnAttenteDmg' => $demandesEnAttenteDmg,
            'plusAncienneDemandeSoumise' => $plusAncienneDemandeSoumise,
            'plusAncienneDemandeSoumiseJours' => $plusAncienneDemandeSoumiseJours,
            'repartitionStatutsDemandes' => $repartitionStatutsDemandes,
        ]);
    }
}
