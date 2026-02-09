<div class="p-6">
    <div class="mb-4">
        <x-back-to-dashboard />
    </div>
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <h2 class="text-2xl font-bold mb-0 text-gray-800">Mes commandes</h2>
        <a href="{{ route('client.dashboard') }}"
           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            Passer une commande
        </a>
    </div>

    {{-- Recherche : n° commande, produit ou statut --}}
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
                   placeholder="N° commande, produit ou statut...">
            @if($search !== '')
                <button type="button" wire:click="$set('search', '')" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600" title="Effacer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            @endif
        </div>
    </div>

    @if($filterActif)
        <div class="mb-4">
            <a href="{{ route('client.commande') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Afficher toutes les commandes</a>
        </div>
    @endif
    @if($filterStatut !== null)
        <div class="mb-4 p-3 bg-gray-50 rounded-lg border border-gray-200">
            <span class="text-sm text-gray-700">
                @if($filterStatut === 'livre')
                    Historique des livraisons
                @else
                    Filtre : {{ \App\Models\CommandeClient::getStatutLabel($filterStatut) }}
                @endif
            </span>
            <a href="{{ route('client.commande') }}" class="ml-2 text-sm text-blue-600 hover:text-blue-800">Afficher toutes les commandes</a>
        </div>
    @endif

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
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">N° commande</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Produit</th>
                    <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Quantité</th>
                    <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Statut</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Bon de livraison</th>
                    <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @forelse($commandes as $cmd)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $cmd->commande_numero ?? 'CMD-#' . $cmd->id }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $cmd->produit->libelle ?? '—' }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="text-sm font-bold text-indigo-600 block">{{ $cmd->quantite }}</span>
                            @if($cmd->quantite_modifiee_par_production)
                                <p class="text-xs text-amber-700 mt-1">Modifiée par la direction (demandée : {{ $cmd->quantite_demandee }})</p>
                            @endif
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
                                $class = $statusClasses[$cmd->statut] ?? 'bg-gray-100 text-gray-800';
                                $label = $statusLabels[$cmd->statut] ?? \App\Models\CommandeClient::getStatutLabel($cmd->statut);
                            @endphp
                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $class }}">
                                {{ $label }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            @if($cmd->bon_livraison_numero)
                                <span class="font-medium text-gray-900">{{ $cmd->bon_livraison_numero }}</span>
                                @if($cmd->bl_signe_path)
                                    <a href="{{ Storage::url($cmd->bl_signe_path) }}" target="_blank" rel="noopener" class="block text-xs text-indigo-600 hover:text-indigo-800 mt-0.5">Télécharger BL signé</a>
                                @endif
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-xs text-gray-500">
                            {{ $cmd->created_at ? $cmd->created_at->format('d/m/Y H:i') : '—' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('client.commande.show', $cmd) }}"
                               class="text-indigo-600 hover:text-indigo-900 p-1.5 hover:bg-indigo-50 rounded-lg transition-colors inline-block"
                               title="Voir le détail">
                                Voir détail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-10 text-center text-gray-500 text-sm">
                            @if($search !== '')
                                Aucune commande ne correspond à « {{ $search }} ».
                            @else
                                Aucune commande pour le moment.
                            @endif
                            <a href="{{ route('client.dashboard') }}" class="mt-2 inline-block text-sm text-indigo-600 hover:text-indigo-800">Passer une commande</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
