<div class="p-6">
    <div class="mb-4">
        <x-back-to-dashboard />
    </div>
    <h2 class="text-2xl font-bold text-gray-800 mb-2">Commander pour un client</h2>
    <p class="text-sm text-gray-500 mb-6">Choisissez un client, ajoutez des produits au panier puis validez la commande en une fois.</p>

    @if (session()->has('success'))
        <div class="mb-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 rounded-r-lg">
            {{ session('success') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="mb-4 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded-r-lg">
            {{ session('error') }}
        </div>
    @endif

    <div class="mb-6 max-w-md">
        <label for="client_id" class="block text-sm font-medium text-gray-700 mb-1">Client (Banque)</label>
        <select id="client_id"
                wire:model.live="client_id"
                class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
            <option value="">Choisir un client...</option>
            @foreach($clients as $c)
                <option value="{{ $c->id }}">{{ $c->NomClient }}</option>
            @endforeach
        </select>
    </div>

    @if($clientSelectionne)
        <div class="mb-6 bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
            <div class="p-5 flex flex-wrap items-start gap-5">
                <div class="flex-shrink-0">
                    @if($clientSelectionne->logo)
                        <img src="{{ Storage::url($clientSelectionne->logo) }}" alt="{{ $clientSelectionne->NomClient }}" class="h-20 w-20 rounded-xl object-contain border border-gray-200 bg-gray-50">
                    @else
                        <div class="h-20 w-20 rounded-xl bg-indigo-100 flex items-center justify-center border border-indigo-200">
                            <span class="text-2xl font-bold text-indigo-600">{{ mb_strtoupper(mb_substr($clientSelectionne->NomClient ?? '?', 0, 1)) }}</span>
                        </div>
                    @endif
                </div>
                <div class="min-w-0 flex-1 space-y-1">
                    <p class="text-lg font-semibold text-gray-900">{{ $clientSelectionne->NomClient }}</p>
                    @if($clientSelectionne->NomPointFocal)
                        <p class="text-sm text-gray-600"><span class="font-medium text-gray-500">Point focal :</span> {{ $clientSelectionne->NomPointFocal }}</p>
                    @endif
                    @if($clientSelectionne->contact)
                        <p class="text-sm text-gray-600"><span class="font-medium text-gray-500">Contact :</span> {{ $clientSelectionne->contact }}</p>
                    @endif
                    @if($clientSelectionne->NumTel)
                        <p class="text-sm text-gray-600"><span class="font-medium text-gray-500">Tél. :</span> {{ $clientSelectionne->NumTel }}</p>
                    @endif
                    @if($clientSelectionne->adressmail)
                        <p class="text-sm text-gray-600"><span class="font-medium text-gray-500">Email :</span> <a href="mailto:{{ $clientSelectionne->adressmail }}" class="text-indigo-600 hover:text-indigo-800">{{ $clientSelectionne->adressmail }}</a></p>
                    @endif
                </div>
            </div>
        </div>
    @endif

    @if($client_id !== '')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Catalogue : réservations du client --}}
            <div class="lg:col-span-2">
                <div class="bg-white shadow-xl rounded-2xl border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-900">Réservations du client</h3>
                        <p class="text-sm text-gray-500 mt-0.5">Saisissez une quantité et cliquez sur « Ajouter au panier ».</p>
                    </div>
                    <div class="divide-y divide-gray-100 max-h-[28rem] overflow-y-auto">
                        @forelse($reservations as $reservation)
                            <div class="px-6 py-4 flex flex-wrap items-center gap-4 hover:bg-gray-50/50">
                                <div class="min-w-0 flex-1">
                                    <p class="font-medium text-gray-900">{{ $reservation->produit->libelle ?? '—' }}</p>
                                    <p class="text-xs text-gray-500">{{ optional($reservation->produit->categorie)->libelle ?? 'Sans catégorie' }}</p>
                                    <div class="flex flex-wrap gap-2 mt-1">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700">Réservé : {{ $reservation->quantite_reservee }}</span>
                                        @if($reservation->quantite_commandee > 0)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800">Déjà commandé : {{ $reservation->quantite_commandee }}</span>
                                        @endif
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-blue-100 text-blue-800">Disponible : {{ $reservation->quantite_restante }}</span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <input type="number"
                                           wire:model="quantities.{{ $reservation->id }}"
                                           min="1"
                                           max="{{ $reservation->quantite_restante }}"
                                           class="w-20 rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                           placeholder="Qté">
                                    <button type="button"
                                            wire:click="addToPanier({{ $reservation->id }})"
                                            @if($reservation->quantite_restante <= 0) disabled @endif
                                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
                                        Ajouter au panier
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="px-6 py-12 text-center text-gray-500">
                                <p class="text-sm">Aucune réservation pour ce client.</p>
                                <p class="text-xs mt-1">Allez dans « Réservations clients » pour allouer du stock.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Panier --}}
            <div class="lg:col-span-1">
                <div class="bg-white shadow-xl rounded-2xl border border-gray-100 overflow-hidden sticky top-4">
                    <div class="px-6 py-4 bg-indigo-50 border-b border-indigo-100">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">Panier</h3>
                            @if($panierLignes->isNotEmpty())
                                <span class="inline-flex items-center justify-center h-7 min-w-[1.75rem] px-2 rounded-full text-xs font-bold bg-indigo-600 text-white">
                                    {{ $panierLignes->count() }} ligne(s)
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="p-4">
                        @if($panierLignes->isEmpty())
                            <p class="text-sm text-gray-500 text-center py-8">Panier vide. Ajoutez des produits à gauche.</p>
                        @else
                            <ul class="space-y-3 max-h-64 overflow-y-auto">
                                @foreach($panierLignes as $ligne)
                                    <li class="flex items-start justify-between gap-2 text-sm border-b border-gray-100 pb-3 last:border-0 last:pb-0">
                                        <div class="min-w-0 flex-1">
                                            <p class="font-medium text-gray-900 truncate">{{ $ligne['produit']->libelle ?? '—' }}</p>
                                            <div class="flex items-center gap-2 mt-1">
                                                <input type="number"
                                                       wire:model.live.debounce.300ms="panier.{{ $ligne['reservation_id'] }}"
                                                       min="1"
                                                       max="{{ $ligne['quantite_restante'] }}"
                                                       class="w-14 rounded border-gray-300 text-sm text-center py-1">
                                                <button type="button"
                                                        wire:click="removeFromPanier({{ $ligne['reservation_id'] }})"
                                                        class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg"
                                                        title="Retirer du panier">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <button type="button"
                                        wire:click="validerPanier()"
                                        class="w-full inline-flex justify-center items-center px-4 py-3 border border-transparent text-sm font-semibold rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Passer la commande ({{ $panierLignes->count() }} ligne(s))
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="bg-white rounded-xl border-2 border-dashed border-gray-200 p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
            </svg>
            <p class="mt-2 text-sm font-medium text-gray-900">Choisissez un client ci-dessus</p>
            <p class="mt-1 text-sm text-gray-500">Les réservations et le panier s'afficheront pour passer la commande à sa place.</p>
        </div>
    @endif
</div>
