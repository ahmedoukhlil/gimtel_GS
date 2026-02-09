<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @php
            $routePrefix = $produit->usage === 'appro' ? 'stock.produits-appro' : 'stock.produits';
            $breadcrumbs = [
                ['label' => 'Tableau de bord', 'url' => route('dashboard')],
                ['label' => $produit->usage === 'appro' ? 'Produits d\'approvisionnement' : 'Produits (commandes / cartes)', 'url' => route($routePrefix . '.index')],
                ['label' => $produit->libelle],
            ];
        @endphp
        <x-breadcrumbs :items="$breadcrumbs" />
        <!-- En-tête -->
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $produit->libelle }}</h1>
                        <p class="text-gray-500 mt-1">{{ $produit->categorie->libelle ?? 'Sans catégorie' }}</p>
                    <div class="flex flex-wrap gap-2 mt-2">
                        @if($produit->usage === 'appro')
                            <span class="inline-flex px-2 py-1 text-xs font-medium rounded bg-amber-100 text-amber-800">Produit approvisionnement</span>
                        @else
                            <span class="inline-flex px-2 py-1 text-xs font-medium rounded bg-blue-100 text-blue-800">Produit commande / carte</span>
                        @endif
                    </div>
                </div>
                @if(auth()->check() && auth()->user()->hasRole('admin'))
                    <a href="{{ route($routePrefix . '.edit', $produit->id) }}" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Modifier
                    </a>
                @endif
            </div>
        </div>

        <!-- Cartes statistiques -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <!-- Stock actuel -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Stock actuel</p>
                        <p class="text-3xl font-bold mt-2 {{ $produit->en_alerte ? 'text-red-600' : ($produit->stock_faible ? 'text-yellow-600' : 'text-green-600') }}">
                            {{ number_format($produit->stock_actuel, 0, ',', ' ') }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">sur {{ number_format($produit->stock_initial, 0, ',', ' ') }} initial</p>
                    </div>
                    <div class="text-4xl">
                        @if($produit->en_alerte)
                            🔴
                        @elseif($produit->stock_faible)
                            🟡
                        @else
                            🟢
                        @endif
                    </div>
                </div>
            </div>

            <!-- Seuil d'alerte -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Seuil d'alerte</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $produit->seuil_alerte }}</p>
                        <p class="text-xs {{ $produit->en_alerte ? 'text-red-600 font-semibold' : 'text-gray-500' }} mt-1">
                            {{ $produit->en_alerte ? '⚠️ Alerte active' : 'Stock suffisant' }}
                        </p>
                    </div>
                    <div class="text-4xl">⚠️</div>
                </div>
            </div>

            <!-- Total entrées -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total entrées</p>
                        <p class="text-3xl font-bold text-green-600 mt-2">{{ number_format($produit->total_entrees, 0, ',', ' ') }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $produit->entrees()->count() }} entrée(s)</p>
                    </div>
                    <div class="text-4xl">📥</div>
                </div>
            </div>

            <!-- Total sorties -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total sorties</p>
                        <p class="text-3xl font-bold text-purple-600 mt-2">{{ number_format($produit->total_sorties, 0, ',', ' ') }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $produit->sorties()->count() }} sortie(s)</p>
                    </div>
                    <div class="text-4xl">📤</div>
                </div>
            </div>
        </div>

        <!-- Informations détaillées -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Informations</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <p class="text-sm font-medium text-gray-500">Magasin</p>
                    <p class="text-sm text-gray-900 mt-1">🏪 {{ $produit->magasin->magasin ?? '-' }}</p>
                    <p class="text-xs text-gray-500">{{ $produit->magasin->localisation ?? '' }}</p>
                </div>
                @if($produit->stockage)
                    <div>
                        <p class="text-sm font-medium text-gray-500">Emplacement</p>
                        <p class="text-sm text-gray-900 mt-1">{{ $produit->stockage }}</p>
                    </div>
                @endif
                @if($produit->descriptif)
                    <div class="md:col-span-2">
                        <p class="text-sm font-medium text-gray-500">Descriptif</p>
                        <p class="text-sm text-gray-900 mt-1">{{ $produit->descriptif }}</p>
                    </div>
                @endif
                @if($produit->observations)
                    <div class="md:col-span-2">
                        <p class="text-sm font-medium text-gray-500">Observations</p>
                        <p class="text-sm text-gray-900 mt-1">{{ $produit->observations }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Onglets -->
        <div class="bg-white rounded-lg shadow mb-8">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex">
                    <button wire:click="setOnglet('info')" 
                            class="px-6 py-3 text-sm font-medium {{ $onglet === 'info' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        📋 Informations
                    </button>
                    <button wire:click="setOnglet('entrees')" 
                            class="px-6 py-3 text-sm font-medium {{ $onglet === 'entrees' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        📥 Entrées ({{ $produit->entrees()->count() }})
                    </button>
                    <button wire:click="setOnglet('sorties')" 
                            class="px-6 py-3 text-sm font-medium {{ $onglet === 'sorties' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        📤 Sorties ({{ $produit->sorties()->count() }})
                    </button>
                    <button wire:click="setOnglet('historique')" 
                            class="px-6 py-3 text-sm font-medium {{ $onglet === 'historique' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        📊 Historique complet
                    </button>
                    <button wire:click="setOnglet('reservations')" 
                            class="px-6 py-3 text-sm font-medium {{ $onglet === 'reservations' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        📋 Réservations par client ({{ $reservations->count() }})
                    </button>
                </nav>
            </div>

            <div class="p-6">
                <!-- Onglet Info -->
                @if($onglet === 'info')
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-xs font-semibold text-gray-600">Stock initial</p>
                                <p class="text-2xl font-bold text-gray-900 mt-1">{{ $produit->stock_initial }}</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-xs font-semibold text-gray-600">Stock actuel</p>
                                <p class="text-2xl font-bold text-gray-900 mt-1">{{ $produit->stock_actuel }}</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-xs font-semibold text-gray-600">Seuil alerte</p>
                                <p class="text-2xl font-bold text-gray-900 mt-1">{{ $produit->seuil_alerte }}</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-xs font-semibold text-gray-600">Pourcentage</p>
                                <p class="text-2xl font-bold text-gray-900 mt-1">{{ round($produit->pourcentage_stock, 1) }}%</p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Onglet Entrées -->
                @if($onglet === 'entrees')
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fournisseur</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Référence</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Quantité</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Par</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($entrees as $entree)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ $entree->date_entree->format('d/m/Y') }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ $entree->fournisseur->libelle ?? '-' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-500">{{ $entree->reference_commande ?? '-' }}</td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="px-3 py-1 text-sm font-semibold bg-green-100 text-green-800 rounded-full">
                                                +{{ $entree->quantite }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-500">{{ $entree->nom_createur }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">Aucune entrée enregistrée</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif

                <!-- Onglet Sorties -->
                @if($onglet === 'sorties')
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Demandeur</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Quantité</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Par</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Observations</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($sorties as $sortie)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ $sortie->date_sortie->format('d/m/Y') }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900">
                                            <div>{{ $sortie->demandeur->nom ?? '-' }}</div>
                                            <div class="text-xs text-gray-500">{{ $sortie->demandeur->poste_service ?? '' }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="px-3 py-1 text-sm font-semibold bg-purple-100 text-purple-800 rounded-full">
                                                -{{ $sortie->quantite }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-500">{{ $sortie->nom_createur }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-500">{{ $sortie->observations ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">Aucune sortie enregistrée</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif

                <!-- Onglet Historique complet -->
                @if($onglet === 'historique')
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tiers</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Quantité</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Par</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($historique as $mouvement)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ \Carbon\Carbon::parse($mouvement['date'])->format('d/m/Y') }}</td>
                                        <td class="px-4 py-3">
                                            @if($mouvement['type'] === 'entree')
                                                <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded">📥 Entrée</span>
                                            @else
                                                <span class="px-2 py-1 text-xs font-semibold bg-purple-100 text-purple-800 rounded">📤 Sortie</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ $mouvement['tiers'] }}</td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="text-sm font-semibold {{ $mouvement['type'] === 'entree' ? 'text-green-600' : 'text-purple-600' }}">
                                                {{ $mouvement['type'] === 'entree' ? '+' : '-' }}{{ $mouvement['quantite'] }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-500">{{ $mouvement['createur'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">Aucun mouvement enregistré</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif

                <!-- Onglet Réservations par client -->
                @if($onglet === 'reservations')
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Quantité réservée</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Déjà commandé</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Disponible</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($reservations as $r)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                            {{ $r->client->NomClient ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-center text-sm font-semibold text-indigo-600">
                                            {{ $r->quantite_reservee }}
                                        </td>
                                        <td class="px-4 py-3 text-center text-sm text-gray-600">
                                            {{ $r->quantite_commandee }}
                                        </td>
                                        <td class="px-4 py-3 text-center text-sm font-medium text-green-700">
                                            {{ $r->quantite_reservee }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">Aucune réservation pour ce produit.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
