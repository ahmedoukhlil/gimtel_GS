<div class="p-6">
    <div class="mb-4">
        <x-back-to-dashboard />
    </div>
    <h2 class="text-2xl font-bold mb-6 text-gray-800">Gestion des Commandes Clients</h2>

    {{-- Recherche --}}
    <div class="mb-6">
        <label for="search-orders" class="sr-only">Rechercher</label>
        <div class="relative max-w-md">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 pointer-events-none">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </span>
            <input type="text"
                   id="search-orders"
                   wire:model.live.debounce.300ms="search"
                   class="block w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                   placeholder="N° commande, client, produit ou statut...">
            @if($search !== '')
                <button type="button" wire:click="$set('search', '')" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600" title="Effacer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            @endif
        </div>
    </div>

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

    <div class="bg-white shadow-xl rounded-2xl overflow-hidden border border-gray-100">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Client</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Produit</th>
                    <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Quantité</th>
                    <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Statut</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Bon de livraison</th>
                    <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @forelse($orders as $order)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                @php
                                    $clientRecord = $order->client->client ?? null;
                                    $clientLogo = $clientRecord && $clientRecord->logo ? $clientRecord->logo : null;
                                @endphp
                                @if($clientLogo)
                                    <img src="{{ Storage::url($clientLogo) }}" alt="" class="h-8 w-8 rounded-full object-cover border border-gray-200 mr-3 flex-shrink-0">
                                @else
                                    <div class="h-8 w-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xs mr-3 flex-shrink-0">
                                        {{ strtoupper(substr($order->client->users ?? '?', 0, 1)) }}
                                    </div>
                                @endif
                                <div class="text-sm font-medium text-gray-900">{{ $order->client->users ?? '—' }}</div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $order->produit->libelle }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold text-indigo-600">
                            {{ $order->quantite }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            @php
                                $statusClasses = [
                                    'soumis' => 'bg-amber-100 text-amber-800',
                                    'en_cours_de_traitement' => 'bg-blue-100 text-blue-800',
                                    'finalise' => 'bg-green-100 text-green-800',
                                    'livre' => 'bg-emerald-100 text-emerald-800',
                                    'rejetee' => 'bg-red-100 text-red-800',
                                ];
                                $statusLabels = [
                                    'soumis' => 'Soumis',
                                    'en_cours_de_traitement' => 'En cours',
                                    'finalise' => 'Finalisé',
                                    'livre' => 'Livré',
                                    'rejetee' => 'Rejetée',
                                ];
                                $class = $statusClasses[$order->statut] ?? 'bg-gray-100 text-gray-800';
                                $label = $statusLabels[$order->statut] ?? \App\Models\CommandeClient::getStatutLabel($order->statut);
                            @endphp
                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $class }}">
                                {{ $label }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            @if($order->bon_livraison_numero)
                                <span class="font-medium text-gray-900">{{ $order->bon_livraison_numero }}</span>
                                @if($order->bl_signe_path)
                                    <a href="{{ Storage::url($order->bl_signe_path) }}" target="_blank" rel="noopener" class="block text-xs text-indigo-600 hover:text-indigo-800 mt-0.5">Télécharger BL signé</a>
                                @endif
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-xs text-gray-500">
                            {{ $order->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-1">
                            <a href="{{ route('production.orders.show', $order->id) }}"
                               class="text-indigo-600 hover:text-indigo-900 p-1.5 hover:bg-indigo-50 rounded-lg transition-colors inline-block"
                               title="Voir le détail">
                                Voir détail
                            </a>
                            @if($order->statut === 'soumis')
                                <button wire:click="openModalValider({{ $order->id }})" 
                                        class="text-green-600 hover:text-green-900 p-1.5 hover:bg-green-50 rounded-lg transition-colors"
                                        title="Valider (modifier la quantité si besoin)">
                                    Valider
                                </button>
                                <button wire:click="rejectOrder({{ $order->id }})" 
                                        class="text-red-600 hover:text-red-900 p-1.5 hover:bg-red-50 rounded-lg transition-colors"
                                        title="Rejeter">
                                    <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            @elseif($order->statut === 'en_cours_de_traitement')
                                <button wire:click="setFinalise({{ $order->id }})" 
                                        class="text-green-600 hover:text-green-900 p-1.5 hover:bg-green-50 rounded-lg transition-colors"
                                        title="Générer le bon de livraison et finaliser">Finaliser</button>
                                <button wire:click="openModalLivrer({{ $order->id }})" 
                                        class="text-indigo-600 hover:text-indigo-900 p-1.5 hover:bg-indigo-50 rounded-lg transition-colors"
                                        title="Marquer livré et archiver le BL signé">Livré</button>
                            @elseif($order->statut === 'finalise')
                                <button wire:click="openModalLivrer({{ $order->id }})" 
                                        class="text-indigo-600 hover:text-indigo-900 p-1.5 hover:bg-indigo-50 rounded-lg transition-colors"
                                        title="Marquer livré et archiver le BL signé">Livré</button>
                            @else
                                <span class="text-gray-400 italic text-xs">{{ $order->statut === 'livre' ? 'Livrée' : '—' }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-10 text-center text-gray-500 text-sm">
                            @if($search !== '')
                                Aucune commande ne correspond à « {{ $search }} ».
                            @else
                                Aucune commande à afficher.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Modal Valider : modifier la quantité puis valider --}}
    @if($showModalValider && $orderValider)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-valider-title" role="dialog" aria-modal="true">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="fixed inset-0 bg-gray-500/75 transition-opacity" wire:click="closeModalValider" aria-hidden="true"></div>
                <div class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-md">
                    <div class="bg-white px-6 py-5">
                        <h3 id="modal-valider-title" class="text-lg font-semibold text-gray-900 mb-2">Valider la commande</h3>
                        <p class="text-sm text-gray-500 mb-4">
                            {{ $orderValider->commande_numero }} — {{ $orderValider->client->users ?? '—' }} / {{ $orderValider->produit->libelle ?? '—' }}
                        </p>
                        <div class="mb-4">
                            <label for="quantite-valider" class="block text-sm font-medium text-gray-700 mb-1">Quantité</label>
                            <input type="number" id="quantite-valider" wire:model="quantiteValider" min="1" step="1"
                                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            @error('quantiteValider')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="flex gap-3 justify-end">
                            <button type="button" wire:click="closeModalValider"
                                    class="px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Annuler
                            </button>
                            <button type="button" wire:click="submitValider" wire:loading.attr="disabled"
                                    class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 disabled:opacity-50">
                                <span wire:loading.remove wire:target="submitValider">Valider</span>
                                <span wire:loading wire:target="submitValider">Enregistrement…</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Livré : upload BL signé scanné --}}
    @if($showModalLivrer && $orderLivrer)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-livrer-title" role="dialog" aria-modal="true">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="fixed inset-0 bg-gray-500/75 transition-opacity" wire:click="closeModalLivrer" aria-hidden="true"></div>
                <div class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-md">
                    <div class="bg-white px-6 py-5">
                        <h3 id="modal-livrer-title" class="text-lg font-semibold text-gray-900 mb-2">Marquer comme livré</h3>
                        <p class="text-sm text-gray-500 mb-4">
                            {{ $orderLivrer->commande_numero }} — Enregistrez le bon de livraison signé et scanné pour l’archivage.
                        </p>
                        <div class="mb-4">
                            <label for="bl-signe-file" class="block text-sm font-medium text-gray-700 mb-1">BL signé (PDF, JPG ou PNG, max 10 Mo)</label>
                            <input type="file" id="bl-signe-file" wire:model="blSigneFile"
                                   accept=".pdf,.jpg,.jpeg,.png"
                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            @error('blSigneFile')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            @if($blSigneFile)
                                <p class="mt-1 text-xs text-gray-500">Fichier sélectionné. Cliquez sur Enregistrer.</p>
                            @endif
                        </div>
                        <div class="flex gap-3 justify-end">
                            <button type="button" wire:click="closeModalLivrer"
                                    class="px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Annuler
                            </button>
                            <button type="button" wire:click="submitLivrer" wire:loading.attr="disabled"
                                    class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 disabled:opacity-50">
                                <span wire:loading.remove wire:target="submitLivrer">Enregistrer et marquer livré</span>
                                <span wire:loading wire:target="submitLivrer">Envoi en cours…</span>
                            </button>
                        </div>
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
