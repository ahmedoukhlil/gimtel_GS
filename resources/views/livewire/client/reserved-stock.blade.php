<div class="p-6">
    <div class="mb-4">
        <a href="{{ route('client.commande') }}" class="text-sm text-gray-600 hover:text-gray-900 inline-flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            Mes commandes
        </a>
    </div>

    <h1 class="text-2xl font-bold text-gray-900 mb-6">Passer une commande</h1>

    @if (session()->has('success'))
        <div class="mb-6 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 rounded-r-lg">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-6 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded-r-lg">
            {{ session('error') }}
        </div>
    @endif

    <div class="space-y-6">
        <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Produits réservés</h2>
                <a href="{{ route('client.commande') }}" class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Voir mes commandes
                </a>
            </div>
            <p class="text-sm text-gray-500 mb-6">Choisissez les quantités à commander pour chaque produit réservé à votre compte.</p>

            @forelse($reservations as $reservation)
                <div class="flex flex-wrap items-end gap-3 sm:gap-4 p-4 rounded-lg bg-gray-50 border border-gray-100 mb-4 last:mb-0" wire:key="reservation-{{ $reservation->id }}">
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Produit</label>
                        <p class="text-sm font-semibold text-gray-900">{{ $reservation->produit->libelle }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">{{ optional($reservation->produit->categorie)->libelle ?? 'Sans catégorie' }}</p>
                    </div>
                    <div class="w-28">
                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Disponible</label>
                        <p class="text-sm font-bold text-indigo-600">{{ $reservation->quantite_restante }}</p>
                        @if($reservation->quantite_commandee > 0)
                            <p class="text-xs text-amber-700 mt-0.5">Déjà commandé : {{ $reservation->quantite_commandee }}</p>
                        @endif
                    </div>
                    <div class="w-24">
                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Quantité</label>
                        <input type="number"
                               wire:model="quantities.{{ $reservation->id }}"
                               min="0"
                               max="{{ $reservation->quantite_restante }}"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                               placeholder="0">
                    </div>
                    <button type="button"
                            wire:click="order({{ $reservation->id }})"
                            @if($reservation->quantite_restante <= 0) disabled @endif
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
                        Commander
                    </button>
                </div>
            @empty
                <div class="text-center py-12 rounded-lg border-2 border-dashed border-gray-200 bg-gray-50">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun stock réservé</h3>
                    <p class="mt-1 text-sm text-gray-500">Contactez l'administration pour allouer du stock à votre compte.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
