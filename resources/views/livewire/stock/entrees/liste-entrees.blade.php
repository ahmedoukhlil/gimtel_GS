<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-4">
            <x-back-to-dashboard />
        </div>
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Entrées de stock {{ $usageEntrees === 'commande_carte' ? '(commandes/cartes)' : '(produits appro)' }}</h1>
                    <p class="text-gray-500 mt-1">Historique des entrées · {{ $usageEntrees === 'commande_carte' ? 'produits commandes/cartes uniquement' : 'produits d\'approvisionnement uniquement' }}</p>
                </div>
                <a href="{{ route('stock.entrees.create') }}" 
                   class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Nouvelle entrée
                </a>
            </div>
        </div>

        @if (session()->has('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        <!-- Statistique du total -->
        <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow p-6 mb-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-green-100">Total entrées (période) · {{ $usageEntrees === 'commande_carte' ? 'commandes/cartes' : 'produits appro' }}</p>
                    <p class="text-4xl font-bold mt-2">{{ number_format($totalQuantite, 0, ',', ' ') }}</p>
                    <p class="text-sm text-green-100 mt-1">Du {{ \Carbon\Carbon::parse($dateDebut)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($dateFin)->format('d/m/Y') }}</p>
                </div>
                <div class="text-6xl">📥</div>
            </div>
        </div>

        <!-- Filtres -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Recherche</label>
                    <input type="text" 
                           wire:model.live.debounce.300ms="search" 
                           placeholder="Rechercher..."
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Produit</label>
                    <select wire:model.live="filterProduit" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        <option value="">Tous les produits</option>
                        @foreach($produits as $produit)
                            <option value="{{ $produit->id }}">{{ $produit->libelle }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fournisseur</label>
                    <select wire:model.live="filterFournisseur" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        <option value="">Tous les fournisseurs</option>
                        @foreach($fournisseurs as $fournisseur)
                            <option value="{{ $fournisseur->id }}">{{ $fournisseur->libelle }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date début</label>
                    <input type="date" wire:model.live="dateDebut" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date fin</label>
                    <input type="date" wire:model.live="dateFin" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>
            </div>
        </div>

        <!-- Liste -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produit</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fournisseur</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Référence</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Quantité</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Par</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($entrees as $entree)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $entree->date_entree->format('d/m/Y') }}</td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $entree->produit->libelle ?? '-' }}</div>
                                <div class="text-xs text-gray-500">{{ $entree->produit->categorie->libelle ?? '' }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $entree->fournisseur->libelle ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $entree->reference_commande ?? '-' }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-3 py-1 text-sm font-semibold bg-green-100 text-green-800 rounded-full">
                                    +{{ $entree->quantite }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $entree->nom_createur }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <span class="text-6xl mb-3">📥</span>
                                <p class="text-sm font-medium text-gray-500">Aucune entrée trouvée</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $entrees->links() }}</div>
    </div>
</div>
