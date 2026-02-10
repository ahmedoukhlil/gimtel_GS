<div class="p-6 space-y-6">

    {{-- HEADER --}}
    <div>
        <x-back-to-dashboard />
        <div class="flex flex-wrap items-end justify-between gap-4 mt-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Commander pour un client</h1>
                <p class="text-sm text-gray-500 mt-1">Passez une commande au nom d'un client à partir de ses réservations de stock</p>
            </div>
            <a href="{{ route('production.reservations') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 shadow-sm transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                Gérer les réservations
            </a>
        </div>
    </div>

    {{-- ÉTAPES VISUELLES --}}
    <div class="flex items-center gap-2 text-sm">
        {{-- Étape 1 --}}
        <div class="flex items-center gap-2 {{ $client_id !== '' ? 'text-green-600' : 'text-indigo-600 font-semibold' }}">
            <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0
                {{ $client_id !== '' ? 'bg-green-100 text-green-700' : 'bg-indigo-100 text-indigo-700' }}">
                @if($client_id !== '')
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                @else
                    1
                @endif
            </span>
            Choisir un client
        </div>
        <div class="w-8 h-px {{ $client_id !== '' ? 'bg-green-300' : 'bg-gray-200' }}"></div>
        {{-- Étape 2 --}}
        <div class="flex items-center gap-2 {{ $panierLignes->isNotEmpty() ? 'text-green-600' : ($client_id !== '' ? 'text-indigo-600 font-semibold' : 'text-gray-400') }}">
            <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0
                {{ $panierLignes->isNotEmpty() ? 'bg-green-100 text-green-700' : ($client_id !== '' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-400') }}">
                @if($panierLignes->isNotEmpty())
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                @else
                    2
                @endif
            </span>
            Ajouter des produits
        </div>
        <div class="w-8 h-px {{ $panierLignes->isNotEmpty() ? 'bg-green-300' : 'bg-gray-200' }}"></div>
        {{-- Étape 3 --}}
        <div class="flex items-center gap-2 {{ $panierLignes->isNotEmpty() ? 'text-indigo-600 font-semibold' : 'text-gray-400' }}">
            <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0
                {{ $panierLignes->isNotEmpty() ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-400' }}">
                3
            </span>
            Valider la commande
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
    @if (session()->has('error'))
        <div class="flex items-center gap-3 p-4 text-sm font-medium text-red-800 bg-red-50 border border-red-200 rounded-lg">
            <svg class="w-5 h-5 flex-shrink-0 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- SÉLECTION CLIENT --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">Sélectionnez le client</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Les réservations disponibles seront chargées automatiquement</p>
                </div>
            </div>
        </div>
        <div class="px-6 py-5">
            <div class="max-w-sm">
                <select id="client_id"
                        wire:model.live="client_id"
                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    <option value="">— Choisir un client —</option>
                    @foreach($clients as $c)
                        <option value="{{ $c->id }}">{{ $c->NomClient }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Fiche client --}}
            @if($clientSelectionne)
                <div class="mt-5 p-4 bg-gradient-to-r from-indigo-50/60 to-purple-50/40 border border-indigo-100/80 rounded-lg">
                    <div class="flex flex-wrap items-start gap-4">
                        <div class="flex-shrink-0">
                            @if($clientSelectionne->logo)
                                <img src="{{ Storage::url($clientSelectionne->logo) }}" alt="{{ $clientSelectionne->NomClient }}" class="h-14 w-14 rounded-xl object-contain border border-gray-200 bg-white">
                            @else
                                <div class="h-14 w-14 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center">
                                    <span class="text-xl font-bold text-white">{{ mb_strtoupper(mb_substr($clientSelectionne->NomClient ?? '?', 0, 1)) }}</span>
                                </div>
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-base font-semibold text-gray-900">{{ $clientSelectionne->NomClient }}</p>
                            <div class="flex flex-wrap gap-x-5 gap-y-1 mt-1.5 text-sm text-gray-600">
                                @if($clientSelectionne->NomPointFocal)
                                    <span class="flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                        {{ $clientSelectionne->NomPointFocal }}
                                    </span>
                                @endif
                                @if($clientSelectionne->NumTel)
                                    <span class="flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                        {{ $clientSelectionne->NumTel }}
                                    </span>
                                @endif
                                @if($clientSelectionne->adressmail)
                                    <a href="mailto:{{ $clientSelectionne->adressmail }}" class="flex items-center gap-1.5 text-indigo-600 hover:text-indigo-800">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                        {{ $clientSelectionne->adressmail }}
                                    </a>
                                @endif
                            </div>
                        </div>
                        {{-- Mini stats --}}
                        <div class="flex gap-4 flex-shrink-0">
                            <div class="text-center">
                                <p class="text-lg font-bold text-indigo-600">{{ $reservations->count() }}</p>
                                <p class="text-[10px] text-gray-500 uppercase tracking-wide">Produits</p>
                            </div>
                            <div class="text-center">
                                <p class="text-lg font-bold text-green-600">{{ $reservations->sum('quantite_restante') }}</p>
                                <p class="text-[10px] text-gray-500 uppercase tracking-wide">Disponible</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- CONTENU PRINCIPAL : CATALOGUE + PANIER --}}
    @if($client_id !== '')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- ======================== CATALOGUE ======================== --}}
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50/80 border-b border-gray-100 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900">Catalogue — Réservations du client</h3>
                                <p class="text-xs text-gray-500 mt-0.5">{{ $reservations->count() }} produit(s) réservé(s)</p>
                            </div>
                        </div>
                    </div>

                    <div class="divide-y divide-gray-100 max-h-[32rem] overflow-y-auto">
                        @forelse($reservations as $reservation)
                            @php
                                $reserved = $reservation->quantite_reservee;
                                $ordered = $reservation->quantite_commandee;
                                $remaining = $reservation->quantite_restante;
                                $percent = $reserved > 0 ? min(100, round(($ordered / $reserved) * 100)) : 0;
                                $inPanier = isset($panier[$reservation->id]);
                                $qtyPanier = $panier[$reservation->id] ?? 0;
                            @endphp
                            <div class="px-6 py-4 hover:bg-gray-50/50 transition-colors {{ $remaining <= 0 ? 'opacity-50' : '' }}">
                                <div class="flex flex-wrap items-center gap-4">
                                    {{-- Info produit --}}
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2">
                                            <p class="text-sm font-semibold text-gray-900">{{ $reservation->produit->libelle ?? '—' }}</p>
                                            @if($inPanier)
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[10px] font-bold text-indigo-700 bg-indigo-100 rounded-full uppercase tracking-wide">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"></path></svg>
                                                    Dans le panier ({{ $qtyPanier }})
                                                </span>
                                            @endif
                                        </div>
                                        <p class="text-xs text-gray-400 mt-0.5">{{ optional($reservation->produit->categorie)->libelle ?? 'Sans catégorie' }}</p>

                                        {{-- Barre de progression + chiffres --}}
                                        <div class="flex items-center gap-3 mt-2.5">
                                            <div class="flex-1 max-w-[200px]">
                                                <div class="w-full h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                                    <div class="h-full rounded-full transition-all duration-500 {{ $percent >= 100 ? 'bg-red-500' : ($percent >= 75 ? 'bg-amber-500' : 'bg-green-500') }}"
                                                         style="width: {{ $percent }}%"></div>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-3 text-xs">
                                                <span class="text-gray-500">Réservé <strong class="text-gray-700">{{ $reserved }}</strong></span>
                                                <span class="text-gray-300">|</span>
                                                <span class="text-gray-500">Commandé <strong class="text-amber-600">{{ $ordered }}</strong></span>
                                                <span class="text-gray-300">|</span>
                                                <span class="{{ $remaining > 0 ? 'text-green-600 font-semibold' : 'text-red-500 font-semibold' }}">
                                                    Dispo {{ $remaining }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Action: Ajouter au panier --}}
                                    <div class="flex items-center gap-2 flex-shrink-0">
                                        @if($remaining > 0)
                                            <div class="relative">
                                                <input type="number"
                                                       wire:model="quantities.{{ $reservation->id }}"
                                                       min="1"
                                                       max="{{ $remaining }}"
                                                       placeholder="Qté"
                                                       class="w-20 rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm text-center py-2">
                                            </div>
                                            <button type="button"
                                                    wire:click="addToPanier({{ $reservation->id }})"
                                                    class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 shadow-sm transition-colors">
                                                <svg wire:loading.remove wire:target="addToPanier({{ $reservation->id }})" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                                <svg wire:loading wire:target="addToPanier({{ $reservation->id }})" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                                Ajouter
                                            </button>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-medium text-red-600 bg-red-50 rounded-lg border border-red-100">
                                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
                                                Épuisé
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-14 h-14 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                                        <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                    </div>
                                    <p class="text-sm font-medium text-gray-500">Aucune réservation pour ce client</p>
                                    <p class="text-xs text-gray-400 mt-1">Vous devez d'abord attribuer des quotas de stock</p>
                                    <a href="{{ route('production.reservations') }}"
                                       class="mt-3 inline-flex items-center gap-1.5 text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                        Créer une réservation
                                    </a>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- ======================== PANIER ======================== --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden sticky top-6"
                     x-data="{ showConfirm: false }">
                    {{-- En-tête panier --}}
                    <div class="px-5 py-4 bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-indigo-100/50">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"></path></svg>
                                </div>
                                <h3 class="text-sm font-semibold text-gray-900">Panier</h3>
                            </div>
                            @if($panierLignes->isNotEmpty())
                                <span class="inline-flex items-center justify-center h-6 min-w-[1.5rem] px-2 rounded-full text-[11px] font-bold bg-indigo-600 text-white">
                                    {{ $panierLignes->count() }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="p-4">
                        @if($panierLignes->isEmpty())
                            {{-- Panier vide --}}
                            <div class="py-10 text-center">
                                <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"></path></svg>
                                </div>
                                <p class="text-sm font-medium text-gray-500">Panier vide</p>
                                <p class="text-xs text-gray-400 mt-1">Ajoutez des produits depuis le catalogue</p>
                            </div>
                        @else
                            {{-- Lignes du panier --}}
                            <ul class="space-y-2 max-h-72 overflow-y-auto pr-1">
                                @foreach($panierLignes as $ligne)
                                    <li class="bg-gray-50/80 rounded-lg p-3 group">
                                        <div class="flex items-start justify-between gap-2">
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-medium text-gray-900 truncate">{{ $ligne['produit']->libelle ?? '—' }}</p>
                                                @if($ligne['produit'] && $ligne['produit']->categorie)
                                                    <p class="text-[10px] text-gray-400 mt-0.5">{{ $ligne['produit']->categorie->libelle }}</p>
                                                @endif
                                            </div>
                                            <button type="button"
                                                    wire:click="removeFromPanier({{ $ligne['reservation_id'] }})"
                                                    class="p-1 text-gray-300 hover:text-red-500 hover:bg-red-50 rounded transition-colors opacity-0 group-hover:opacity-100 flex-shrink-0"
                                                    title="Retirer">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            </button>
                                        </div>
                                        <div class="flex items-center gap-2 mt-2">
                                            <label class="text-xs text-gray-500">Qté :</label>
                                            <input type="number"
                                                   wire:model.live.debounce.400ms="panier.{{ $ligne['reservation_id'] }}"
                                                   min="1"
                                                   max="{{ $ligne['quantite_restante'] }}"
                                                   class="w-16 rounded-md border-gray-300 text-sm text-center py-1 focus:ring-indigo-500 focus:border-indigo-500">
                                            <span class="text-[10px] text-gray-400">/ {{ $ligne['quantite_restante'] }} dispo</span>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>

                            {{-- Résumé panier --}}
                            <div class="mt-4 pt-3 border-t border-gray-200 space-y-2.5">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-500">Lignes</span>
                                    <span class="font-semibold text-gray-900">{{ $panierLignes->count() }} produit(s)</span>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-500">Quantité totale</span>
                                    <span class="font-bold text-indigo-600 text-base">{{ number_format($panierLignes->sum('quantite'), 0, ',', ' ') }}</span>
                                </div>

                                {{-- Bouton : Passer la commande → ouvre la confirmation --}}
                                <button type="button"
                                        @click="showConfirm = true"
                                        class="w-full mt-2 inline-flex justify-center items-center gap-2 px-4 py-3 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 shadow-sm transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    Passer la commande
                                </button>
                            </div>

                            {{-- Modal de confirmation --}}
                            <div x-show="showConfirm" x-cloak
                                 class="fixed inset-0 z-50 flex items-center justify-center p-4"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0"
                                 x-transition:enter-end="opacity-100"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100"
                                 x-transition:leave-end="opacity-0">
                                {{-- Overlay --}}
                                <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm" @click="showConfirm = false"></div>
                                {{-- Contenu --}}
                                <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden"
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave="transition ease-in duration-150"
                                     x-transition:leave-start="opacity-100 scale-100"
                                     x-transition:leave-end="opacity-0 scale-95">
                                    <div class="px-6 pt-6 pb-4 text-center">
                                        <div class="w-14 h-14 rounded-full bg-indigo-100 flex items-center justify-center mx-auto mb-4">
                                            <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        </div>
                                        <h3 class="text-lg font-bold text-gray-900">Confirmer la commande</h3>
                                        <p class="text-sm text-gray-500 mt-2">
                                            Vous allez créer <strong class="text-gray-700">{{ $panierLignes->count() }} commande(s)</strong>
                                            pour un total de <strong class="text-indigo-600">{{ number_format($panierLignes->sum('quantite'), 0, ',', ' ') }} unité(s)</strong>
                                            au nom de <strong class="text-gray-700">{{ $clientSelectionne->NomClient ?? 'ce client' }}</strong>.
                                        </p>
                                    </div>
                                    <div class="px-6 pb-6 flex gap-3">
                                        <button type="button" @click="showConfirm = false"
                                                class="flex-1 px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                            Annuler
                                        </button>
                                        <button type="button"
                                                wire:click="validerPanier"
                                                @click="showConfirm = false"
                                                class="flex-1 inline-flex justify-center items-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 shadow-sm transition-colors">
                                            <svg wire:loading.remove wire:target="validerPanier" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            <svg wire:loading wire:target="validerPanier" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                            Confirmer
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @else
        {{-- État initial : aucun client sélectionné --}}
        <div class="bg-white rounded-xl border-2 border-dashed border-gray-200 p-16 text-center">
            <div class="flex flex-col items-center">
                <div class="w-16 h-16 rounded-full bg-indigo-50 flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                </div>
                <p class="text-base font-semibold text-gray-700">Sélectionnez un client pour commencer</p>
                <p class="text-sm text-gray-400 mt-1 max-w-sm">Les réservations et le panier s'afficheront automatiquement pour passer la commande au nom du client.</p>
            </div>
        </div>
    @endif
</div>
