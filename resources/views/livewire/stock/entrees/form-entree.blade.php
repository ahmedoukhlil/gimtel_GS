<div class="p-6 space-y-6">

    @php
        $isCommandesCartes = ($usageEntrees ?? 'appro') === 'commande_carte';
        $breadcrumbs = [
            ['label' => 'Tableau de bord', 'url' => route('dashboard')],
            ['label' => $isCommandesCartes ? 'Entrées (commandes/cartes)' : 'Entrées (approvisionnement)', 'url' => route('stock.entrees.index', ['usage' => $usageEntrees ?? 'appro'])],
            ['label' => 'Nouvelle entrée'],
        ];
    @endphp

    {{-- BREADCRUMBS --}}
    <x-breadcrumbs :items="$breadcrumbs" />

    {{-- HEADER --}}
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Nouvelle entrée de stock</h1>
            <p class="text-sm text-gray-500 mt-1">
                Enregistrez une entrée pour les {{ $isCommandesCartes ? 'produits commandes / cartes' : 'produits d\'approvisionnement' }}
            </p>
        </div>
    </div>

    {{-- ALERTES --}}
    @if (session()->has('error'))
        <div class="flex items-center gap-3 p-4 text-sm font-medium text-red-800 bg-red-50 border border-red-200 rounded-lg">
            <svg class="w-5 h-5 flex-shrink-0 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- FORMULAIRE --}}
    <form wire:submit.prevent="save">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">

            {{-- Section : Informations générales --}}
            <div class="px-6 py-4 bg-gray-50/50 border-b border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">Informations générales</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Date et référence de l'entrée</p>
                    </div>
                </div>
            </div>
            <div class="px-6 py-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label for="date_entree" class="block text-sm font-medium text-gray-700 mb-1.5">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                Date d'entrée <span class="text-red-500">*</span>
                            </span>
                        </label>
                        <input type="date" id="date_entree" wire:model="date_entree"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm @error('date_entree') border-red-300 ring-1 ring-red-300 @enderror">
                        @error('date_entree') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="reference_commande" class="block text-sm font-medium text-gray-700 mb-1.5">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path></svg>
                                Référence commande
                            </span>
                        </label>
                        <input type="text" id="reference_commande" wire:model="reference_commande"
                               placeholder="Ex: BC-2026-001"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>
                </div>
            </div>

            {{-- Section : Produit --}}
            <div class="px-6 py-4 bg-gray-50/50 border-b border-t border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">Produit & Fournisseur</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Sélectionnez le produit et le fournisseur</p>
                    </div>
                </div>
            </div>
            <div class="px-6 py-5 space-y-5">
                {{-- Produit --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                            Produit <span class="text-red-500">*</span>
                        </span>
                    </label>
                    <livewire:components.searchable-select
                        wire:model.live="produit_id"
                        :options="$this->produitOptions"
                        placeholder="Sélectionner un produit"
                        search-placeholder="Rechercher un produit..."
                        no-results-text="Aucun produit trouvé"
                        :key="'produit-select'"
                    />
                    @error('produit_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror

                    {{-- Info produit sélectionné --}}
                    @if($this->produitSelectionne)
                        <div class="mt-3 bg-gradient-to-r from-gray-50 to-indigo-50/30 border border-gray-200 rounded-lg p-4">
                            <div class="flex items-center gap-2 mb-3">
                                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <h4 class="text-sm font-semibold text-gray-900">Informations du produit</h4>
                            </div>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                <div class="bg-white rounded-lg px-3 py-2 border border-gray-100">
                                    <p class="text-[10px] text-gray-500 uppercase tracking-wide">Stock actuel</p>
                                    <p class="text-base font-bold text-gray-900 mt-0.5">{{ number_format($this->produitSelectionne->stock_actuel, 0, ',', ' ') }}</p>
                                </div>
                                <div class="bg-white rounded-lg px-3 py-2 border border-gray-100">
                                    <p class="text-[10px] text-gray-500 uppercase tracking-wide">Stock initial</p>
                                    <p class="text-base font-bold mt-0.5 {{ $this->produitSelectionne->stock_initial == 0 ? 'text-amber-600' : 'text-gray-900' }}">
                                        {{ $this->produitSelectionne->stock_initial == 0 ? 'Non défini' : number_format($this->produitSelectionne->stock_initial, 0, ',', ' ') }}
                                    </p>
                                </div>
                                <div class="bg-white rounded-lg px-3 py-2 border border-gray-100">
                                    <p class="text-[10px] text-gray-500 uppercase tracking-wide">Seuil d'alerte</p>
                                    <p class="text-base font-bold text-gray-900 mt-0.5">{{ number_format($this->produitSelectionne->seuil_alerte, 0, ',', ' ') }}</p>
                                </div>
                                <div class="bg-white rounded-lg px-3 py-2 border border-gray-100">
                                    <p class="text-[10px] text-gray-500 uppercase tracking-wide">Magasin</p>
                                    <p class="text-base font-bold text-gray-900 mt-0.5">{{ $this->produitSelectionne->magasin->magasin ?? 'N/A' }}</p>
                                </div>
                            </div>

                            @if($this->produitSelectionne->stock_initial == 0)
                                <div class="mt-3 flex items-center gap-2 p-2.5 bg-blue-50 border border-blue-200 rounded-lg text-xs text-blue-800">
                                    <svg class="w-4 h-4 text-blue-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                                    <span><strong>Première entrée :</strong> Cette entrée définira le stock initial du produit.</span>
                                </div>
                            @endif

                            @if($this->produitSelectionne->en_alerte)
                                <div class="mt-2 flex items-center gap-2 p-2.5 bg-red-50 border border-red-200 rounded-lg text-xs text-red-800">
                                    <svg class="w-4 h-4 text-red-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                                    <span><strong>Alerte :</strong> Le stock est en dessous du seuil d'alerte !</span>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Fournisseur --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                            Fournisseur <span class="text-red-500">*</span>
                        </span>
                    </label>
                    <livewire:components.searchable-select
                        wire:model.live="fournisseur_id"
                        :options="$this->fournisseurOptions"
                        placeholder="Sélectionner un fournisseur"
                        search-placeholder="Rechercher un fournisseur..."
                        no-results-text="Aucun fournisseur trouvé"
                        :key="'fournisseur-select'"
                    />
                    @error('fournisseur_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Section : Quantité & Observations --}}
            <div class="px-6 py-4 bg-gray-50/50 border-b border-t border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">Quantité & Observations</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Précisez la quantité reçue</p>
                    </div>
                </div>
            </div>
            <div class="px-6 py-5 space-y-5">
                <div class="max-w-xs">
                    <label for="quantite" class="block text-sm font-medium text-gray-700 mb-1.5">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path></svg>
                            Quantité <span class="text-red-500">*</span>
                        </span>
                    </label>
                    <input type="number" id="quantite" wire:model="quantite" min="1" placeholder="1"
                           class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm @error('quantite') border-red-300 ring-1 ring-red-300 @enderror">
                    @error('quantite') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="observations" class="block text-sm font-medium text-gray-700 mb-1.5">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path></svg>
                            Observations
                        </span>
                    </label>
                    <textarea id="observations" wire:model="observations" rows="3"
                              placeholder="Notes, remarques..."
                              class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"></textarea>
                </div>
            </div>

            {{-- Actions --}}
            <div class="px-6 py-4 bg-gray-50/50 border-t border-gray-100 flex items-center justify-end gap-3">
                <button type="button" wire:click="cancel"
                        class="inline-flex items-center px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    Annuler
                </button>
                <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 shadow-sm transition-colors">
                    <svg wire:loading.remove wire:target="save" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <svg wire:loading wire:target="save" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    Enregistrer l'entrée
                </button>
            </div>
        </div>
    </form>
</div>
