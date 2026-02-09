<div class="py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        @php
            $isCommandesCartes = ($usageEntrees ?? 'appro') === 'commande_carte';
            $breadcrumbs = [
                ['label' => 'Tableau de bord', 'url' => route('dashboard')],
                ['label' => $isCommandesCartes ? 'Entrées (commandes/cartes)' : 'Entrées (produits appro)', 'url' => route('stock.entrees.index')],
                ['label' => 'Nouvelle entrée'],
            ];
        @endphp
        <x-breadcrumbs :items="$breadcrumbs" />
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Nouvelle entrée de stock {{ $isCommandesCartes ? '(commandes/cartes)' : '(produits appro)' }}</h1>
            <p class="text-gray-500 mt-1">Enregistrez une entrée · {{ $isCommandesCartes ? 'produits commandes/cartes uniquement' : 'produits d\'approvisionnement uniquement' }}</p>
        </div>

        @if (session()->has('error'))
            <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
                {{ session('error') }}
            </div>
        @endif

        <form wire:submit.prevent="save">
            <div class="bg-white rounded-lg shadow p-6 space-y-6">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="date_entree" class="block text-sm font-medium text-gray-700 mb-1">
                            Date d'entrée <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="date_entree" wire:model="date_entree" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg @error('date_entree') border-red-500 @enderror">
                        @error('date_entree') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="reference_commande" class="block text-sm font-medium text-gray-700 mb-1">Référence commande</label>
                        <input type="text" id="reference_commande" wire:model="reference_commande" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg" placeholder="BC-2026-001">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Produit <span class="text-red-500">*</span>
                    </label>
                    <livewire:components.searchable-select
                        wire:model.live="produit_id"
                        :options="$this->produitOptions"
                        placeholder="Sélectionner un produit"
                        search-placeholder="Rechercher un produit..."
                        no-results-text="Aucun produit trouvé"
                        :key="'produit-select'"
                    />
                    @error('produit_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror

                    @if($this->produitSelectionne)
                        <div class="mt-3 bg-gray-50 border border-gray-200 rounded-lg p-4">
                            <h4 class="text-sm font-semibold text-gray-900 mb-2">Informations du produit</h4>
                            <div class="grid grid-cols-2 gap-3 text-sm">
                                <div>
                                    <span class="text-gray-600">Stock actuel :</span>
                                    <span class="font-semibold text-gray-900 ml-2">{{ number_format($this->produitSelectionne->stock_actuel, 0, ',', ' ') }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Stock initial :</span>
                                    <span class="font-semibold text-gray-900 ml-2">
                                        @if($this->produitSelectionne->stock_initial == 0)
                                            <span class="text-orange-600">Non défini</span>
                                        @else
                                            {{ number_format($this->produitSelectionne->stock_initial, 0, ',', ' ') }}
                                        @endif
                                    </span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Seuil d'alerte :</span>
                                    <span class="font-semibold text-gray-900 ml-2">{{ number_format($this->produitSelectionne->seuil_alerte, 0, ',', ' ') }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Magasin :</span>
                                    <span class="font-semibold text-gray-900 ml-2">{{ $this->produitSelectionne->magasin->magasin ?? 'N/A' }}</span>
                                </div>
                            </div>
                            @if($this->produitSelectionne->stock_initial == 0)
                                <div class="mt-3 bg-blue-50 border border-blue-200 rounded p-2">
                                    <p class="text-xs text-blue-800">
                                        <strong>⚠️ Première entrée :</strong> Cette entrée définira le <strong>stock initial</strong> du produit.
                                    </p>
                                </div>
                            @endif
                            @if($this->produitSelectionne->en_alerte)
                                <div class="mt-2 bg-red-50 border border-red-200 rounded p-2">
                                    <p class="text-xs text-red-800">
                                        <strong>⚠️ Alerte :</strong> Le stock est en dessous du seuil d'alerte !
                                    </p>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Fournisseur <span class="text-red-500">*</span>
                    </label>
                    <livewire:components.searchable-select
                        wire:model.live="fournisseur_id"
                        :options="$this->fournisseurOptions"
                        placeholder="Sélectionner un fournisseur"
                        search-placeholder="Rechercher un fournisseur..."
                        no-results-text="Aucun fournisseur trouvé"
                        :key="'fournisseur-select'"
                    />
                    @error('fournisseur_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="quantite" class="block text-sm font-medium text-gray-700 mb-1">
                        Quantité <span class="text-red-500">*</span>
                    </label>
                    <input type="number" id="quantite" wire:model="quantite" min="1"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg @error('quantite') border-red-500 @enderror">
                    @error('quantite') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="observations" class="block text-sm font-medium text-gray-700 mb-1">Observations</label>
                    <textarea id="observations" wire:model="observations" rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg" placeholder="Notes..."></textarea>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-4 border-t">
                    <button type="button" wire:click="cancel" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">Annuler</button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 inline-flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Enregistrer l'entrée
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
