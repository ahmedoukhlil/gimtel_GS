<div class="p-6 space-y-6">

    {{-- ================================================================
         HEADER
    ================================================================ --}}
    <div>
        <x-back-to-dashboard />
        <div class="flex flex-wrap items-end justify-between gap-4 mt-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Gestion des commandes</h1>
                <p class="text-sm text-gray-500 mt-1">Suivez et traitez les commandes clients en temps reel</p>
            </div>
            <a href="{{ route('production.commander-pour-client') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 shadow-sm transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Commander pour un client
            </a>
        </div>
    </div>

    {{-- ================================================================
         ALERTES SESSION
    ================================================================ --}}
    @if (session()->has('success'))
        <div class="flex items-center gap-3 p-4 text-sm font-medium text-green-800 bg-green-50 border border-green-200 rounded-lg">
            <svg class="w-5 h-5 flex-shrink-0 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
            {{ session('success') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="flex items-center gap-3 p-4 text-sm font-medium text-red-800 bg-red-50 border border-red-200 rounded-lg">
            <svg class="w-5 h-5 flex-shrink-0 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- ================================================================
         STATS PIPELINE (pills cliquables)
    ================================================================ --}}
    @php
        $stats = $this->stats;
        $pills = [
            ''                         => ['label' => 'Toutes',    'count' => $stats['all'],                    'color' => 'gray'],
            'soumis'                   => ['label' => 'Soumises',  'count' => $stats['soumis'],                 'color' => 'amber'],
            'en_cours_de_traitement'   => ['label' => 'En cours',  'count' => $stats['en_cours_de_traitement'], 'color' => 'blue'],
            'finalise'                 => ['label' => 'Finalisees','count' => $stats['finalise'],               'color' => 'emerald'],
            'livre'                    => ['label' => 'Livrees',   'count' => $stats['livre'],                  'color' => 'green'],
            'rejetee'                  => ['label' => 'Rejetees',  'count' => $stats['rejetee'],                'color' => 'red'],
        ];
    @endphp
    <div class="flex flex-wrap gap-2">
        @foreach($pills as $value => $pill)
            <button wire:click="$set('filterStatut', '{{ $value }}')"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-medium rounded-full border transition-all duration-150
                           {{ $filterStatut === $value
                               ? 'bg-' . $pill['color'] . '-100 text-' . $pill['color'] . '-800 border-' . $pill['color'] . '-300 shadow-sm'
                               : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50 hover:border-gray-300' }}">
                {{ $pill['label'] }}
                <span class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 text-xs font-bold rounded-full
                             {{ $filterStatut === $value
                                 ? 'bg-' . $pill['color'] . '-200 text-' . $pill['color'] . '-900'
                                 : 'bg-gray-100 text-gray-500' }}">
                    {{ $pill['count'] }}
                </span>
            </button>
        @endforeach
    </div>

    {{-- ================================================================
         BARRE DE RECHERCHE
    ================================================================ --}}
    <div class="relative max-w-md">
        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 pointer-events-none">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
        </span>
        <input type="text"
               wire:model.live.debounce.300ms="search"
               class="block w-full pl-10 pr-10 py-2.5 text-sm border border-gray-200 rounded-lg bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 placeholder-gray-400"
               placeholder="Rechercher par n° commande, client ou produit...">
        @if($search !== '')
            <button type="button" wire:click="$set('search', '')"
                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600" title="Effacer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        @endif
    </div>

    {{-- ================================================================
         TABLEAU DES COMMANDES
    ================================================================ --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr class="bg-gray-50/80">
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Commande</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Client</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Produit</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Qte</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Statut</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Bon de livraison</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-5 py-3.5 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($orders as $order)
                        @php
                            $statusConfig = [
                                'soumis'                   => ['label' => 'Soumis',    'bg' => 'bg-amber-50',   'text' => 'text-amber-700',   'dot' => 'bg-amber-400'],
                                'en_cours_de_traitement'   => ['label' => 'En cours',  'bg' => 'bg-blue-50',    'text' => 'text-blue-700',    'dot' => 'bg-blue-400'],
                                'finalise'                 => ['label' => 'Finalise',  'bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'dot' => 'bg-emerald-400'],
                                'livre'                    => ['label' => 'Livre',     'bg' => 'bg-green-50',   'text' => 'text-green-700',   'dot' => 'bg-green-400'],
                                'rejetee'                  => ['label' => 'Rejetee',   'bg' => 'bg-red-50',     'text' => 'text-red-700',     'dot' => 'bg-red-400'],
                            ];
                            $sc = $statusConfig[$order->statut] ?? ['label' => $order->statut, 'bg' => 'bg-gray-50', 'text' => 'text-gray-700', 'dot' => 'bg-gray-400'];
                        @endphp
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            {{-- Numero --}}
                            <td class="px-5 py-4">
                                <span class="text-sm font-semibold text-gray-900">{{ $order->commande_numero ?? '#' . $order->id }}</span>
                            </td>

                            {{-- Client --}}
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    @php
                                        $clientRecord = $order->client->client ?? null;
                                        $clientLogo = $clientRecord && $clientRecord->logo ? $clientRecord->logo : null;
                                        $initials = strtoupper(substr($order->client->users ?? '?', 0, 1));
                                    @endphp
                                    @if($clientLogo)
                                        <img src="{{ Storage::url($clientLogo) }}" alt="" class="h-9 w-9 rounded-full object-cover border border-gray-200 flex-shrink-0">
                                    @else
                                        <div class="h-9 w-9 rounded-full bg-gradient-to-br from-indigo-500 to-purple-500 text-white flex items-center justify-center text-xs font-bold flex-shrink-0">
                                            {{ $initials }}
                                        </div>
                                    @endif
                                    <span class="text-sm font-medium text-gray-900 truncate max-w-[140px]">{{ $order->client->users ?? '—' }}</span>
                                </div>
                            </td>

                            {{-- Produit --}}
                            <td class="px-5 py-4">
                                <span class="text-sm text-gray-700">{{ $order->produit->libelle ?? '—' }}</span>
                            </td>

                            {{-- Quantite --}}
                            <td class="px-5 py-4 text-center">
                                <span class="text-sm font-bold text-gray-900">{{ number_format($order->quantite, 0, ',', ' ') }}</span>
                                @if($order->quantite_demandee && $order->quantite_demandee != $order->quantite)
                                    <span class="block text-[11px] text-gray-400 line-through">{{ $order->quantite_demandee }}</span>
                                @endif
                            </td>

                            {{-- Statut --}}
                            <td class="px-5 py-4 text-center">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-full {{ $sc['bg'] }} {{ $sc['text'] }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $sc['dot'] }}"></span>
                                    {{ $sc['label'] }}
                                </span>
                            </td>

                            {{-- Bon de livraison --}}
                            <td class="px-5 py-4">
                                @if($order->bon_livraison_numero)
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        <div>
                                            <span class="text-sm font-medium text-gray-900">{{ $order->bon_livraison_numero }}</span>
                                            @if($order->bl_signe_path)
                                                <a href="{{ Storage::url($order->bl_signe_path) }}" target="_blank" rel="noopener"
                                                   class="block text-xs text-indigo-600 hover:text-indigo-800 hover:underline">
                                                    BL signe
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <span class="text-sm text-gray-300">—</span>
                                @endif
                            </td>

                            {{-- Date --}}
                            <td class="px-5 py-4 text-center">
                                <span class="text-sm text-gray-500">{{ $order->created_at->format('d/m/Y') }}</span>
                                <span class="block text-[11px] text-gray-400">{{ $order->created_at->format('H:i') }}</span>
                            </td>

                            {{-- Actions --}}
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    {{-- Voir detail (toujours) --}}
                                    <a href="{{ route('production.orders.show', $order->id) }}"
                                       class="inline-flex items-center justify-center w-8 h-8 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors"
                                       title="Voir le detail">
                                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>

                                    @if($order->statut === 'soumis')
                                        <button wire:click="openModalValider({{ $order->id }})"
                                                class="inline-flex items-center justify-center w-8 h-8 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-lg transition-colors"
                                                title="Valider">
                                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </button>
                                        <button wire:click="rejectOrder({{ $order->id }})"
                                                wire:confirm="Confirmer le rejet de cette commande ?"
                                                class="inline-flex items-center justify-center w-8 h-8 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                                title="Rejeter">
                                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    @elseif($order->statut === 'en_cours_de_traitement')
                                        <button wire:click="setFinalise({{ $order->id }})"
                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-lg transition-colors"
                                                title="Generer le BL et finaliser">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            Finaliser
                                        </button>
                                        <button wire:click="openModalLivrer({{ $order->id }})"
                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-indigo-700 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition-colors"
                                                title="Marquer livre">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                                            </svg>
                                            Livre
                                        </button>
                                    @elseif($order->statut === 'finalise')
                                        <button wire:click="openModalLivrer({{ $order->id }})"
                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-indigo-700 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition-colors"
                                                title="Marquer livre">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                                            </svg>
                                            Livre
                                        </button>
                                    @elseif($order->statut === 'livre')
                                        <span class="inline-flex items-center gap-1 px-2 py-1 text-xs text-green-600">
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                            </svg>
                                            Terminee
                                        </span>
                                    @elseif($order->statut === 'rejetee')
                                        <span class="inline-flex items-center gap-1 px-2 py-1 text-xs text-red-400">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                                            </svg>
                                            Rejetee
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-16 text-center">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                    @if($search !== '' || $filterStatut !== '')
                                        <p class="text-sm font-medium text-gray-500">Aucune commande trouvee</p>
                                        <p class="text-xs text-gray-400 mt-1">Essayez de modifier vos filtres ou votre recherche</p>
                                        <button wire:click="$set('search', ''); $set('filterStatut', '')"
                                                class="mt-3 text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                                            Reinitialiser les filtres
                                        </button>
                                    @else
                                        <p class="text-sm font-medium text-gray-500">Aucune commande pour le moment</p>
                                        <p class="text-xs text-gray-400 mt-1">Les commandes clients apparaitront ici</p>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($orders->hasPages())
            <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/50">
                {{ $orders->links() }}
            </div>
        @endif
    </div>

    {{-- ================================================================
         MODAL VALIDER
    ================================================================ --}}
    @if($showModalValider && $orderValider)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-valider-title" role="dialog" aria-modal="true">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" wire:click="closeModalValider"></div>
                <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden">
                    {{-- Header --}}
                    <div class="px-6 pt-6 pb-4">
                        <div class="flex items-center gap-3 mb-1">
                            <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 id="modal-valider-title" class="text-lg font-semibold text-gray-900">Valider la commande</h3>
                                <p class="text-sm text-gray-500">{{ $orderValider->commande_numero }}</p>
                            </div>
                        </div>
                    </div>
                    {{-- Body --}}
                    <div class="px-6 pb-2">
                        <div class="bg-gray-50 rounded-lg p-3 mb-4 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Client</span>
                                <span class="font-medium text-gray-900">{{ $orderValider->client->users ?? '—' }}</span>
                            </div>
                            <div class="flex justify-between mt-1">
                                <span class="text-gray-500">Produit</span>
                                <span class="font-medium text-gray-900">{{ $orderValider->produit->libelle ?? '—' }}</span>
                            </div>
                        </div>
                        <div>
                            <label for="quantite-valider" class="block text-sm font-medium text-gray-700 mb-1.5">Quantite a valider</label>
                            <input type="number" id="quantite-valider" wire:model="quantiteValider" min="1" step="1"
                                   class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            @error('quantiteValider')
                                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    {{-- Footer --}}
                    <div class="flex gap-3 justify-end px-6 py-4 bg-gray-50/50 border-t border-gray-100 mt-4">
                        <button type="button" wire:click="closeModalValider"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            Annuler
                        </button>
                        <button type="button" wire:click="submitValider" wire:loading.attr="disabled"
                                class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 disabled:opacity-50 transition-colors">
                            <span wire:loading.remove wire:target="submitValider">Valider la commande</span>
                            <span wire:loading wire:target="submitValider">Validation...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ================================================================
         MODAL LIVRER
    ================================================================ --}}
    @if($showModalLivrer && $orderLivrer)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-livrer-title" role="dialog" aria-modal="true">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" wire:click="closeModalLivrer"></div>
                <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden">
                    {{-- Header --}}
                    <div class="px-6 pt-6 pb-4">
                        <div class="flex items-center gap-3 mb-1">
                            <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 id="modal-livrer-title" class="text-lg font-semibold text-gray-900">Marquer comme livre</h3>
                                <p class="text-sm text-gray-500">{{ $orderLivrer->commande_numero }}</p>
                            </div>
                        </div>
                    </div>
                    {{-- Body --}}
                    <div class="px-6 pb-2">
                        <p class="text-sm text-gray-500 mb-4">
                            Archivez le bon de livraison signe et scanne.
                        </p>
                        <div>
                            <label for="bl-signe-file" class="block text-sm font-medium text-gray-700 mb-1.5">BL signe (PDF, JPG ou PNG, max 10 Mo)</label>
                            <div class="relative">
                                <input type="file" id="bl-signe-file" wire:model="blSigneFile"
                                       accept=".pdf,.jpg,.jpeg,.png"
                                       class="block w-full text-sm text-gray-500
                                              file:mr-4 file:py-2.5 file:px-4
                                              file:rounded-lg file:border-0
                                              file:text-sm file:font-medium
                                              file:bg-indigo-50 file:text-indigo-700
                                              hover:file:bg-indigo-100
                                              file:cursor-pointer file:transition-colors">
                            </div>
                            @error('blSigneFile')
                                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            @if($blSigneFile)
                                <div class="flex items-center gap-2 mt-2 p-2 bg-green-50 rounded-lg text-sm text-green-700">
                                    <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    Fichier selectionne. Pret a enregistrer.
                                </div>
                            @endif
                        </div>
                    </div>
                    {{-- Footer --}}
                    <div class="flex gap-3 justify-end px-6 py-4 bg-gray-50/50 border-t border-gray-100 mt-4">
                        <button type="button" wire:click="closeModalLivrer"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            Annuler
                        </button>
                        <button type="button" wire:click="submitLivrer" wire:loading.attr="disabled"
                                class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 disabled:opacity-50 transition-colors">
                            <span wire:loading.remove wire:target="submitLivrer">Enregistrer et marquer livre</span>
                            <span wire:loading wire:target="submitLivrer">Envoi en cours...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <script>
        document.addEventListener('livewire:init', function () {
            Livewire.on('download-bl', function (event) {
                if (event.url) window.open(event.url, '_blank');
            });
        });
    </script>
</div>
