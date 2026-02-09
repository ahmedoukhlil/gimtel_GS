<div class="py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        @php
            $breadcrumbs = [
                ['label' => 'Tableau de bord', 'url' => route('dashboard')],
                ['label' => $usage === 'appro' ? 'Produits d\'approvisionnement' : 'Produits (commandes / cartes)', 'url' => $indexRoute],
                ['label' => $produit ? 'Modifier' : 'Nouveau'],
            ];
        @endphp
        <x-breadcrumbs :items="$breadcrumbs" />
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">
                {{ $produit ? 'Modifier le produit' : 'Nouveau produit' }}
                @if($usage === 'appro')
                    <span class="ml-2 text-sm font-normal text-amber-700 bg-amber-100 px-2 py-0.5 rounded">Approvisionnement</span>
                @else
                    <span class="ml-2 text-sm font-normal text-blue-700 bg-blue-100 px-2 py-0.5 rounded">Commande / Carte</span>
                @endif
            </h1>
            <p class="text-gray-500 mt-1">
                {{ $produit ? 'Modifiez les informations du produit' : 'Ajoutez un nouveau produit ' . ($usage === 'appro' ? 'd\'approvisionnement' : 'pour commandes / cartes') }}
            </p>
        </div>

        <form wire:submit.prevent="save">
            <div class="bg-white rounded-lg shadow p-6 space-y-6">
                
                <!-- Informations de base -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Libellé -->
                    <div class="md:col-span-2">
                        <label for="libelle" class="block text-sm font-medium text-gray-700 mb-1">
                            Libellé <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="libelle"
                               wire:model="libelle" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('libelle') border-red-500 @enderror"
                               placeholder="Ex: Ramettes A4">
                        @error('libelle')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Catégorie -->
                    <div>
                        <label for="categorie_id" class="block text-sm font-medium text-gray-700 mb-1">
                            Catégorie <span class="text-red-500">*</span>
                        </label>
                        <livewire:components.searchable-select
                            wire:model.live="categorie_id"
                            :options="$this->categorieOptions"
                            placeholder="Sélectionner une catégorie"
                            search-placeholder="Rechercher..."
                            no-results-text="Aucune catégorie trouvée"
                            :key="'categorie-select'"
                        />
                        @error('categorie_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Magasin -->
                    <div>
                        <label for="magasin_id" class="block text-sm font-medium text-gray-700 mb-1">
                            Magasin <span class="text-red-500">*</span>
                        </label>
                        <livewire:components.searchable-select
                            wire:model.live="magasin_id"
                            :options="$this->magasinOptions"
                            placeholder="Sélectionner un magasin"
                            search-placeholder="Rechercher..."
                            no-results-text="Aucun magasin trouvé"
                            :key="'magasin-select'"
                        />
                        @error('magasin_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Emplacement dans le magasin -->
                    <div class="md:col-span-2">
                        <label for="stockage" class="block text-sm font-medium text-gray-700 mb-1">
                            Emplacement dans le magasin
                        </label>
                        <input type="text" 
                               id="stockage"
                               wire:model="stockage" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Ex: Étagère A3, Rayon 2">
                        <p class="mt-1 text-sm text-gray-500">Précisez l'emplacement exact dans le magasin</p>
                    </div>
                </div>

                <!-- Gestion du stock -->
                <div class="border-t pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Gestion du stock</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Seuil d'alerte -->
                        <div>
                            <label for="seuil_alerte" class="block text-sm font-medium text-gray-700 mb-1">
                                Seuil d'alerte <span class="text-red-500">*</span>
                            </label>
                            <input type="number" 
                                   id="seuil_alerte"
                                   wire:model="seuil_alerte" 
                                   min="0"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('seuil_alerte') border-red-500 @enderror">
                            @error('seuil_alerte')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">Alerte si stock ≤ ce seuil</p>
                        </div>
                    </div>

                    @if(!$produit)
                        <div class="mt-4 bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-blue-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                </svg>
                                <div class="text-sm text-blue-800">
                                    <p class="font-semibold mb-1">Note importante :</p>
                                    <p>Le stock initial et le stock actuel seront définis à <strong>0</strong> lors de la création du produit.</p>
                                    <p class="mt-1">Vous pourrez ensuite ajouter le stock initial via une <strong>opération d'entrée de stock</strong>.</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Descriptif -->
                <div>
                    <label for="descriptif" class="block text-sm font-medium text-gray-700 mb-1">Descriptif</label>
                    <textarea id="descriptif"
                              wire:model="descriptif" 
                              rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                              placeholder="Description détaillée du produit..."></textarea>
                </div>

                <!-- Observations -->
                <div>
                    <label for="observations" class="block text-sm font-medium text-gray-700 mb-1">Observations</label>
                    <textarea id="observations"
                              wire:model="observations" 
                              rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                              placeholder="Notes supplémentaires..."></textarea>
                </div>

                <!-- Informations en mode édition -->
                @if($produit)
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <div class="text-xs font-semibold text-blue-600 uppercase">Total entrées</div>
                                <div class="text-2xl font-bold text-blue-900 mt-1">{{ number_format($produit->entrees()->sum('quantite'), 0, ',', ' ') }}</div>
                            </div>
                            <div>
                                <div class="text-xs font-semibold text-purple-600 uppercase">Total sorties</div>
                                <div class="text-2xl font-bold text-purple-900 mt-1">{{ number_format($produit->sorties()->sum('quantite'), 0, ',', ' ') }}</div>
                            </div>
                            <div>
                                <div class="text-xs font-semibold text-green-600 uppercase">Stock actuel</div>
                                <div class="text-2xl font-bold text-green-900 mt-1">{{ number_format($produit->stock_actuel, 0, ',', ' ') }}</div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Boutons d'action -->
                <div class="flex items-center justify-end space-x-3 pt-4 border-t">
                    <a href="{{ $indexRoute }}" 
                            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors inline-block">
                        Annuler
                    </a>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors inline-flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        {{ $produit ? 'Mettre à jour' : 'Créer le produit' }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
