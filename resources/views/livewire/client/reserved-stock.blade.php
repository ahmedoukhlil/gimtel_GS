<div class="p-6 space-y-6">

    {{-- HEADER --}}
    <div>
        <a href="{{ route('client.commande') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-indigo-600 transition-colors mb-4">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Retour aux commandes
        </a>
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Passer une commande</h1>
                <p class="text-sm text-gray-500 mt-1">Sélectionnez les produits et quantités à commander depuis votre stock réservé</p>
            </div>
            <a href="{{ route('client.commande') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 shadow-sm transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                Mes commandes
            </a>
        </div>
    </div>

    {{-- STATS --}}
    @if($reservations->isNotEmpty())
        @php
            $totalReserve = $reservations->sum('quantite_reservee');
            $totalDisponible = $reservations->sum('quantite_restante');
            $totalCommande = $reservations->sum('quantite_commandee');
            $percentUsed = $totalReserve > 0 ? min(100, round(($totalCommande / $totalReserve) * 100)) : 0;
        @endphp
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ $reservations->count() }}</p>
                        <p class="text-xs text-gray-500">Produits réservés</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($totalReserve, 0, ',', ' ') }}</p>
                        <p class="text-xs text-gray-500">Total réservé</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-green-600">{{ number_format($totalDisponible, 0, ',', ' ') }}</p>
                        <p class="text-xs text-gray-500">Disponible</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ $percentUsed }}%</p>
                        <p class="text-xs text-gray-500">Consommé</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

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

    {{-- CATALOGUE PRODUITS --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-gray-50/50 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"></path></svg>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-gray-900">Produits réservés</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Saisissez une quantité et cliquez sur « Commander »</p>
                </div>
            </div>
        </div>

        <div class="divide-y divide-gray-100">
            @forelse($reservations as $reservation)
                @php
                    $reserved = $reservation->quantite_reservee;
                    $ordered = $reservation->quantite_commandee;
                    $remaining = $reservation->quantite_restante;
                    $percent = $reserved > 0 ? min(100, round(($ordered / $reserved) * 100)) : 0;
                @endphp
                <div class="px-6 py-5 hover:bg-gray-50/50 transition-colors {{ $remaining <= 0 ? 'opacity-50' : '' }}" wire:key="reservation-{{ $reservation->id }}">
                    <div class="flex flex-wrap items-center gap-5">
                        {{-- Produit info --}}
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <p class="text-sm font-semibold text-gray-900">{{ $reservation->produit->libelle }}</p>
                                @if($remaining <= 0)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[10px] font-bold text-red-700 bg-red-100 rounded-full uppercase">Épuisé</span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-400 mt-0.5">{{ optional($reservation->produit->categorie)->libelle ?? 'Sans catégorie' }}</p>

                            {{-- Barre de progression --}}
                            <div class="flex items-center gap-3 mt-3">
                                <div class="flex-1 max-w-[220px]">
                                    <div class="w-full h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                        <div class="h-full rounded-full transition-all duration-500 {{ $percent >= 100 ? 'bg-red-500' : ($percent >= 75 ? 'bg-amber-500' : 'bg-green-500') }}"
                                             style="width: {{ $percent }}%"></div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 text-xs">
                                    <span class="text-gray-500">Réservé <strong class="text-gray-700">{{ $reserved }}</strong></span>
                                    <span class="text-gray-300">|</span>
                                    @if($ordered > 0)
                                        <span class="text-amber-600">Commandé <strong>{{ $ordered }}</strong></span>
                                        <span class="text-gray-300">|</span>
                                    @endif
                                    <span class="{{ $remaining > 0 ? 'text-green-600 font-semibold' : 'text-red-500 font-semibold' }}">
                                        Dispo {{ $remaining }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Action --}}
                        <div class="flex items-center gap-2.5 flex-shrink-0">
                            @if($remaining > 0)
                                <div>
                                    <input type="number"
                                           wire:model="quantities.{{ $reservation->id }}"
                                           min="1"
                                           max="{{ $remaining }}"
                                           placeholder="Qté"
                                           class="w-20 rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm text-center py-2">
                                </div>
                                <button type="button"
                                        wire:click="order({{ $reservation->id }})"
                                        wire:confirm="Confirmer la commande de ce produit ?"
                                        class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 shadow-sm transition-colors">
                                    <svg wire:loading.remove wire:target="order({{ $reservation->id }})" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"></path></svg>
                                    <svg wire:loading wire:target="order({{ $reservation->id }})" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                    Commander
                                </button>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-medium text-red-600 bg-red-50 rounded-lg border border-red-100">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
                                    Quota épuisé
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-6 py-16 text-center">
                    <div class="flex flex-col items-center">
                        <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                            <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        </div>
                        <p class="text-sm font-medium text-gray-500">Aucun stock réservé</p>
                        <p class="text-xs text-gray-400 mt-1">Contactez l'administration pour allouer du stock à votre compte</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>
