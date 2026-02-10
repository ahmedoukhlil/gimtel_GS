<div class="p-6 space-y-6">

    {{-- HEADER --}}
    <div>
        <x-back-to-dashboard />
        <div class="flex flex-wrap items-end justify-between gap-4 mt-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Réservations clients</h1>
                <p class="text-sm text-gray-500 mt-1">Gérez les quotas de stock réservés par client et par produit</p>
            </div>
            <button wire:click="openCreate" type="button"
                    class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 shadow-sm transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Nouvelle réservation
            </button>
        </div>
    </div>

    {{-- STATS CARDS --}}
    @php $stats = $this->stats; @endphp
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_reservations'] }}</p>
                    <p class="text-xs text-gray-500">Réservations</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_clients'] }}</p>
                    <p class="text-xs text-gray-500">Clients</p>
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
                    <p class="text-xs text-gray-500">Produits</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_reserve'], 0, ',', ' ') }}</p>
                    <p class="text-xs text-gray-500">Total réservé</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ALERTES --}}
    @if (session()->has('success'))
        <div class="flex items-center gap-3 p-4 text-sm font-medium text-green-800 bg-green-50 border border-green-200 rounded-lg" x-data x-init="setTimeout(() => $el.remove(), 4000)" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <svg class="w-5 h-5 flex-shrink-0 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
            {{ session('success') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="flex items-center gap-3 p-4 text-sm font-medium text-red-800 bg-red-50 border border-red-200 rounded-lg">
            <svg class="w-5 h-5 flex-shrink-0 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- FORMULAIRE INLINE --}}
    @if ($showForm)
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden"
             x-data x-init="$el.scrollIntoView({ behavior: 'smooth', block: 'center' })">
            <div class="px-6 py-4 bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg {{ $editingId ? 'bg-amber-100' : 'bg-indigo-100' }} flex items-center justify-center flex-shrink-0">
                        @if($editingId)
                            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        @else
                            <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        @endif
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">{{ $editingId ? 'Modifier la réservation' : 'Nouvelle réservation' }}</h3>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $editingId ? 'Ajustez la quantité réservée pour ce client' : 'Attribuez un quota de stock à un client pour un produit' }}</p>
                    </div>
                </div>
            </div>
            <form wire:submit="save" class="px-6 py-5 space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    {{-- Client --}}
                    <div>
                        <label for="client_id" class="block text-sm font-medium text-gray-700 mb-1.5">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                Client
                            </span>
                        </label>
                        <select wire:model="client_id" id="client_id" required
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">Choisir un client</option>
                            @foreach($clients as $c)
                                <option value="{{ $c->id }}">{{ $c->NomClient }}</option>
                            @endforeach
                        </select>
                        @error('client_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    {{-- Produit --}}
                    <div>
                        <label for="produit_id" class="block text-sm font-medium text-gray-700 mb-1.5">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                Produit
                            </span>
                        </label>
                        <select wire:model="produit_id" id="produit_id" required
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">Choisir un produit</option>
                            @foreach($produits as $p)
                                <option value="{{ $p->id }}">{{ $p->libelle }} ({{ optional($p->categorie)->libelle ?? '—' }})</option>
                            @endforeach
                        </select>
                        @error('produit_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    {{-- Quantité --}}
                    <div>
                        <label for="quantite_reservee" class="block text-sm font-medium text-gray-700 mb-1.5">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path></svg>
                                Quantité réservée
                            </span>
                        </label>
                        <input type="number" wire:model="quantite_reservee" id="quantite_reservee" min="0" required
                               placeholder="0"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        @error('quantite_reservee') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="flex items-center gap-3 pt-2 border-t border-gray-100">
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors shadow-sm">
                        <svg wire:loading.remove wire:target="save" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <svg wire:loading wire:target="save" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        Enregistrer
                    </button>
                    <button type="button" wire:click="cancelForm"
                            class="inline-flex items-center px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        Annuler
                    </button>
                </div>
            </form>
        </div>
    @endif

    {{-- BARRE RECHERCHE + FILTRE --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex flex-wrap items-center gap-3">
            {{-- Recherche --}}
            <div class="relative flex-1 min-w-[200px]">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <input type="text" wire:model.live.debounce.300ms="search"
                       placeholder="Rechercher par client ou produit..."
                       class="block w-full pl-10 pr-10 py-2.5 text-sm border-gray-300 rounded-lg focus:border-indigo-500 focus:ring-indigo-500">
                @if($search)
                    <button wire:click="$set('search', '')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                @endif
            </div>

            {{-- Filtre client --}}
            <div class="min-w-[180px]">
                <select wire:model.live="filterClient"
                        class="block w-full text-sm border-gray-300 rounded-lg focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Tous les clients</option>
                    @foreach($clients as $c)
                        <option value="{{ $c->id }}">{{ $c->NomClient }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Compteur --}}
            <span class="text-xs text-gray-500 whitespace-nowrap">
                {{ $reservations->total() }} résultat{{ $reservations->total() > 1 ? 's' : '' }}
            </span>
        </div>

        {{-- TABLE --}}
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr class="bg-gray-50/80">
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Client</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Produit</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Réservé</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Commandé</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Restant</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Consommation</th>
                        <th class="px-5 py-3.5 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($reservations as $r)
                        @php
                            $reserved = $r->quantite_reservee;
                            $ordered = $r->quantite_commandee;
                            $remaining = $r->quantite_restante;
                            $percent = $reserved > 0 ? min(100, round(($ordered / $reserved) * 100)) : 0;
                            $clientLogo = $r->client && $r->client->logo ? $r->client->logo : null;
                            $initials = strtoupper(substr($r->client->NomClient ?? '?', 0, 1));
                        @endphp
                        <tr class="hover:bg-gray-50/50 transition-colors group">
                            {{-- Client --}}
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    @if($clientLogo)
                                        <img src="{{ Storage::url($clientLogo) }}" alt="" class="h-9 w-9 rounded-full object-cover border border-gray-200 flex-shrink-0">
                                    @else
                                        <div class="h-9 w-9 rounded-full bg-gradient-to-br from-indigo-500 to-purple-500 text-white flex items-center justify-center text-xs font-bold flex-shrink-0">
                                            {{ $initials }}
                                        </div>
                                    @endif
                                    <span class="text-sm font-medium text-gray-900">{{ $r->client->NomClient ?? '—' }}</span>
                                </div>
                            </td>

                            {{-- Produit --}}
                            <td class="px-5 py-4">
                                <div>
                                    <span class="text-sm font-medium text-gray-700">{{ $r->produit->libelle ?? '—' }}</span>
                                    @if($r->produit && $r->produit->categorie)
                                        <span class="block text-[11px] text-gray-400 mt-0.5">{{ $r->produit->categorie->libelle }}</span>
                                    @endif
                                </div>
                            </td>

                            {{-- Réservé --}}
                            <td class="px-5 py-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-1 text-sm font-bold text-indigo-700 bg-indigo-50 rounded-full">
                                    {{ number_format($reserved, 0, ',', ' ') }}
                                </span>
                            </td>

                            {{-- Commandé --}}
                            <td class="px-5 py-4 text-center">
                                <span class="text-sm font-medium {{ $ordered > 0 ? 'text-amber-700' : 'text-gray-400' }}">
                                    {{ number_format($ordered, 0, ',', ' ') }}
                                </span>
                            </td>

                            {{-- Restant --}}
                            <td class="px-5 py-4 text-center">
                                <span class="inline-flex items-center gap-1 text-sm font-semibold {{ $remaining > 0 ? 'text-green-700' : 'text-red-500' }}">
                                    @if($remaining <= 0)
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
                                    @endif
                                    {{ number_format($remaining, 0, ',', ' ') }}
                                </span>
                            </td>

                            {{-- Barre de progression --}}
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2.5 justify-center">
                                    <div class="w-24 h-2 bg-gray-100 rounded-full overflow-hidden">
                                        <div class="h-full rounded-full transition-all duration-500 {{ $percent >= 100 ? 'bg-red-500' : ($percent >= 75 ? 'bg-amber-500' : 'bg-green-500') }}"
                                             style="width: {{ $percent }}%"></div>
                                    </div>
                                    <span class="text-xs font-medium {{ $percent >= 100 ? 'text-red-600' : ($percent >= 75 ? 'text-amber-600' : 'text-gray-500') }} tabular-nums w-10 text-right">
                                        {{ $percent }}%
                                    </span>
                                </div>
                            </td>

                            {{-- Actions --}}
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button wire:click="openEdit({{ $r->id }})" type="button"
                                            class="inline-flex items-center justify-center w-8 h-8 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors"
                                            title="Modifier">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>
                                    <button wire:click="delete({{ $r->id }})" wire:confirm="Supprimer cette réservation ?" type="button"
                                            class="inline-flex items-center justify-center w-8 h-8 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                            title="Supprimer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-16 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                                        <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    </div>
                                    @if($search || $filterClient)
                                        <p class="text-sm font-medium text-gray-500">Aucune réservation trouvée</p>
                                        <p class="text-xs text-gray-400 mt-1">Essayez de modifier vos critères de recherche</p>
                                        <button wire:click="$set('search', ''); $set('filterClient', '')"
                                                class="mt-3 inline-flex items-center gap-1.5 text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                            Réinitialiser les filtres
                                        </button>
                                    @else
                                        <p class="text-sm font-medium text-gray-500">Aucune réservation</p>
                                        <p class="text-xs text-gray-400 mt-1">Créez une réservation pour attribuer du stock à un client</p>
                                        @if(!$showForm)
                                            <button wire:click="openCreate"
                                                    class="mt-3 inline-flex items-center gap-1.5 text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                                Nouvelle réservation
                                            </button>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        @if($reservations->hasPages())
            <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/50">
                {{ $reservations->links() }}
            </div>
        @endif
    </div>
</div>
