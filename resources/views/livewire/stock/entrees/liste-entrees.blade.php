<div class="p-6 space-y-6">

    {{-- HEADER --}}
    <div>
        <x-back-to-dashboard />
        <div class="flex flex-wrap items-end justify-between gap-4 mt-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    Entrées de stock
                    <span class="text-base font-medium text-gray-500 ml-1">
                        — {{ $usageEntrees === 'commande_carte' ? 'Commandes / Cartes' : 'Approvisionnement' }}
                    </span>
                </h1>
                <p class="text-sm text-gray-500 mt-1">
                    Historique des entrées pour les {{ $usageEntrees === 'commande_carte' ? 'produits commandes et cartes' : 'produits d\'approvisionnement' }}
                </p>
            </div>
            <a href="{{ route('stock.entrees.create', ['usage' => $usageEntrees]) }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 shadow-sm transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Nouvelle entrée
            </a>
        </div>
    </div>

    {{-- STATS CARDS --}}
    @php $stats = $this->stats; @endphp
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_quantite'], 0, ',', ' ') }}</p>
                    <p class="text-xs text-gray-500">Quantité totale</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_entrees'] }}</p>
                    <p class="text-xs text-gray-500">Entrées</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_produits'] }}</p>
                    <p class="text-xs text-gray-500">Produits distincts</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_fournisseurs'] }}</p>
                    <p class="text-xs text-gray-500">Fournisseurs</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ALERTES --}}
    @if (session()->has('success'))
        <div class="flex items-center gap-3 p-4 text-sm font-medium text-green-800 bg-green-50 border border-green-200 rounded-lg"
             x-data x-init="setTimeout(() => $el.remove(), 4000)"
             x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <svg class="w-5 h-5 flex-shrink-0 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- FILTRES --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">Filtres</h3>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Du {{ \Carbon\Carbon::parse($dateDebut)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($dateFin)->format('d/m/Y') }}
                        </p>
                    </div>
                </div>
                @if($search || $filterProduit || $filterFournisseur)
                    <button wire:click="resetFilters" class="inline-flex items-center gap-1.5 text-xs text-gray-500 hover:text-indigo-600 font-medium transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        Réinitialiser
                    </button>
                @endif
            </div>
        </div>
        <div class="px-5 py-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                {{-- Recherche --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1.5">Recherche</label>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        <input type="text"
                               wire:model.live.debounce.300ms="search"
                               placeholder="Produit, référence..."
                               class="block w-full pl-10 pr-3 py-2.5 text-sm border-gray-300 rounded-lg focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>

                {{-- Produit --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1.5">Produit</label>
                    <select wire:model.live="filterProduit" class="block w-full text-sm border-gray-300 rounded-lg focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Tous les produits</option>
                        @foreach($produits as $produit)
                            <option value="{{ $produit->id }}">{{ $produit->libelle }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Fournisseur --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1.5">Fournisseur</label>
                    <select wire:model.live="filterFournisseur" class="block w-full text-sm border-gray-300 rounded-lg focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Tous les fournisseurs</option>
                        @foreach($fournisseurs as $fournisseur)
                            <option value="{{ $fournisseur->id }}">{{ $fournisseur->libelle }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Date début --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1.5">Date début</label>
                    <input type="date" wire:model.live="dateDebut" class="block w-full text-sm border-gray-300 rounded-lg focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                {{-- Date fin --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1.5">Date fin</label>
                    <input type="date" wire:model.live="dateFin" class="block w-full text-sm border-gray-300 rounded-lg focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>
        </div>
    </div>

    {{-- TABLE --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr class="bg-gray-50/80">
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Produit</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Fournisseur</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Référence</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Quantité</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Créé par</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($entrees as $entree)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            {{-- Date --}}
                            <td class="px-5 py-4">
                                <div>
                                    <span class="text-sm font-medium text-gray-900">{{ $entree->date_entree->format('d/m/Y') }}</span>
                                    <span class="block text-[11px] text-gray-400 mt-0.5">{{ $entree->date_entree->translatedFormat('l') }}</span>
                                </div>
                            </td>

                            {{-- Produit --}}
                            <td class="px-5 py-4">
                                <div>
                                    <span class="text-sm font-medium text-gray-900">{{ $entree->produit->libelle ?? '—' }}</span>
                                    @if($entree->produit && $entree->produit->categorie)
                                        <span class="block text-[11px] text-gray-400 mt-0.5">{{ $entree->produit->categorie->libelle }}</span>
                                    @endif
                                </div>
                            </td>

                            {{-- Fournisseur --}}
                            <td class="px-5 py-4">
                                <span class="text-sm text-gray-700">{{ $entree->fournisseur->libelle ?? '—' }}</span>
                            </td>

                            {{-- Référence --}}
                            <td class="px-5 py-4">
                                @if($entree->reference_commande)
                                    <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium text-gray-700 bg-gray-100 rounded-md font-mono">
                                        {{ $entree->reference_commande }}
                                    </span>
                                @else
                                    <span class="text-sm text-gray-300">—</span>
                                @endif
                            </td>

                            {{-- Quantité --}}
                            <td class="px-5 py-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-1 text-sm font-bold text-green-700 bg-green-50 rounded-full">
                                    +{{ number_format($entree->quantite, 0, ',', ' ') }}
                                </span>
                            </td>

                            {{-- Créé par --}}
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-gray-200 flex items-center justify-center text-[10px] font-bold text-gray-600 flex-shrink-0">
                                        {{ strtoupper(substr($entree->nom_createur ?? '?', 0, 1)) }}
                                    </div>
                                    <span class="text-sm text-gray-600">{{ $entree->nom_createur ?? '—' }}</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-16 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                                        <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path></svg>
                                    </div>
                                    @if($search || $filterProduit || $filterFournisseur)
                                        <p class="text-sm font-medium text-gray-500">Aucune entrée trouvée</p>
                                        <p class="text-xs text-gray-400 mt-1">Essayez de modifier vos critères de recherche</p>
                                        <button wire:click="resetFilters"
                                                class="mt-3 inline-flex items-center gap-1.5 text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                            Réinitialiser les filtres
                                        </button>
                                    @else
                                        <p class="text-sm font-medium text-gray-500">Aucune entrée de stock</p>
                                        <p class="text-xs text-gray-400 mt-1">Commencez par enregistrer votre première entrée de stock</p>
                                        <a href="{{ route('stock.entrees.create', ['usage' => $usageEntrees]) }}"
                                           class="mt-3 inline-flex items-center gap-1.5 text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                            Nouvelle entrée
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        @if($entrees->hasPages())
            <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/50">
                {{ $entrees->links() }}
            </div>
        @endif
    </div>
</div>
